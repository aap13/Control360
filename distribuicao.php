<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireAccess('distribuicao');

$db = getDB();
$erros = [];
$resumo = null;
$detalhes = [];
$clientesDisponiveis = distribuicao_accessible_clients('editar');
if (empty($clientesDisponiveis) && ($_SESSION['perfil'] ?? '') !== 'admin') {
    flash('Você não tem clientes liberados para sincronizar o monitoramento.', 'error');
    redirect('distribuicao_index.php');
}

function dist_mon_to_utf8($value): string
{
    if ($value === null) {
        return '';
    }
    $value = (string) $value;
    if ($value === '') {
        return '';
    }
    if (!mb_detect_encoding($value, 'UTF-8', true)) {
        $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252, ISO-8859-1, UTF-8');
    }
    return trim($value);
}

function dist_mon_normalize($value): string
{
    $value = dist_mon_to_utf8($value);
    $value = strtoupper($value);
    $value = preg_replace('/[^A-Z0-9]/', '', $value);
    return $value ?: '';
}

function dist_mon_parse_date($value): ?string
{
    $value = dist_mon_to_utf8($value);
    if ($value === '') {
        return null;
    }

    $formats = [
        'd/m/Y H:i:s',
        'd/m/Y H:i',
        'd/m/Y',
        'Y-m-d H:i:s',
        'Y-m-d H:i',
        'Y-m-d',
        'd-m-Y',
    ];

    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $value);
        if ($dt instanceof DateTime) {
            return $dt->format('Y-m-d');
        }
    }

    $timestamp = strtotime($value);
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }

    return null;
}

function dist_mon_detect_delimiter(string $headerLine): string
{
    $semicolon = substr_count($headerLine, ';');
    $comma = substr_count($headerLine, ',');
    return $semicolon >= $comma ? ';' : ',';
}

function dist_mon_is_current_month(?string $date): bool
{
    if (!$date) {
        return false;
    }
    return substr($date, 0, 7) === date('Y-m');
}

function dist_mon_build_maps(string $tmpFile): array
{
    $handle = fopen($tmpFile, 'rb');
    if (!$handle) {
        throw new RuntimeException('Não foi possível abrir o CSV enviado.');
    }

    $firstLine = fgets($handle);
    if ($firstLine === false) {
        fclose($handle);
        throw new RuntimeException('O CSV está vazio.');
    }

    $delimiter = dist_mon_detect_delimiter(dist_mon_to_utf8($firstLine));
    rewind($handle);

    $header = fgetcsv($handle, 0, $delimiter);
    if (!$header) {
        fclose($handle);
        throw new RuntimeException('Não foi possível ler o cabeçalho do CSV.');
    }

    $normalizedHeader = [];
    foreach ($header as $column) {
        $normalizedHeader[] = mb_strtolower(dist_mon_to_utf8($column));
    }

    $serialIdx = array_search('serial', $normalizedHeader, true);
    $printerIdx = array_search('printer', $normalizedHeader, true);
    $lastMeterIdx = array_search('last meter', $normalizedHeader, true);

    if ($serialIdx === false || $lastMeterIdx === false) {
        fclose($handle);
        throw new RuntimeException('O CSV precisa ter as colunas Serial e Last meter.');
    }

    $mapBySerial = [];
    $mapByPrinter = [];
    $linhas = 0;

    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        $linhas++;
        $serial = dist_mon_normalize($row[$serialIdx] ?? '');
        $printer = dist_mon_normalize($row[$printerIdx] ?? '');
        $lastMeter = dist_mon_parse_date($row[$lastMeterIdx] ?? '');

        if ($serial === '' && $printer === '') {
            continue;
        }

        $payload = [
            'serial' => $serial,
            'printer' => $printer,
            'last_meter' => $lastMeter,
            'raw_last_meter' => dist_mon_to_utf8($row[$lastMeterIdx] ?? ''),
        ];

        if ($serial !== '') {
            $mapBySerial[$serial] = $payload;
        }
        if ($printer !== '') {
            $mapByPrinter[$printer] = $payload;
        }
    }

    fclose($handle);

    return [
        'by_serial' => $mapBySerial,
        'by_printer' => $mapByPrinter,
        'linhas' => $linhas,
    ];
}

if (request_is_post()) {
    validate_csrf_or_fail($_POST['csrf_token'] ?? null);

    $clienteId = (int) post('cliente_id', 0);
    if ($clienteId <= 0) {
        $erros[] = 'Selecione o cliente da distribuição.';
    } else {
        distribuicao_require_cliente_access($clienteId, 'editar');
    }

    if (!isset($_FILES['arquivo_csv']) || (int) ($_FILES['arquivo_csv']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $erros[] = 'Envie o arquivo CSV do NDD.';
    }

    if (empty($erros)) {
        try {
            $maps = dist_mon_build_maps($_FILES['arquivo_csv']['tmp_name']);

            $stmt = $db->prepare("
                SELECT id, cliente_id, unidade, setor, modelo, serie, nome_impressora,
                       monitoramento, status_operacional, ultima_leitura_em
                FROM distribuicao_equipamentos
                WHERE cliente_id = :cliente_id
                ORDER BY unidade ASC, setor ASC, id ASC
            ");
            $stmt->execute([':cliente_id' => $clienteId]);
            $equipamentos = $stmt->fetchAll() ?: [];

            $ignoredStatuses = ['desinstalada', 'inativa', 'recolhida'];

            $resumo = [
                'cliente_id' => $clienteId,
                'arquivo' => $_FILES['arquivo_csv']['name'] ?? 'arquivo.csv',
                'linhas_csv' => (int) $maps['linhas'],
                'equipamentos' => count($equipamentos),
                'processados' => 0,
                'ignorados' => 0,
                'encontrados' => 0,
                'online' => 0,
                'offline_leitura_antiga' => 0,
                'offline_sem_match' => 0,
                'alterados' => 0,
            ];

            $cliente = distribuicao_fetch_cliente($clienteId);

            $db->beginTransaction();
            $update = $db->prepare("
                UPDATE distribuicao_equipamentos
                   SET monitoramento = :monitoramento,
                       ultima_leitura_em = :ultima_leitura_em,
                       origem_monitoramento = :origem_monitoramento,
                       ultima_sincronizacao_monitoramento_em = NOW()
                 WHERE id = :id
            ");

            foreach ($equipamentos as $equipamento) {
                $statusAtual = mb_strtolower(trim((string) ($equipamento['status_operacional'] ?? '')));
                if (in_array($statusAtual, $ignoredStatuses, true)) {
                    $resumo['ignorados']++;
                    continue;
                }

                $resumo['processados']++;

                $serial = dist_mon_normalize($equipamento['serie'] ?? '');
                $printer = dist_mon_normalize($equipamento['nome_impressora'] ?? '');
                $match = null;

                if ($serial !== '' && isset($maps['by_serial'][$serial])) {
                    $match = $maps['by_serial'][$serial];
                } elseif ($printer !== '' && isset($maps['by_printer'][$printer])) {
                    $match = $maps['by_printer'][$printer];
                }

                $novoMonitoramento = 'Offline';
                $novaUltimaLeitura = $equipamento['ultima_leitura_em'] ?: null;
                $motivo = 'Equipamento ausente no CSV do monitoramento.';

                if ($match) {
                    $resumo['encontrados']++;
                    $novaUltimaLeitura = $match['last_meter'] ?: $novaUltimaLeitura;

                    if (dist_mon_is_current_month($match['last_meter'])) {
                        $novoMonitoramento = 'Online';
                        $resumo['online']++;
                        $motivo = 'Leitura encontrada no mês atual.';
                    } else {
                        $novoMonitoramento = 'Offline';
                        $resumo['offline_leitura_antiga']++;
                        $motivo = $match['last_meter']
                            ? 'Última leitura fora do mês atual.'
                            : 'Registro encontrado sem data válida de leitura.';
                    }
                } else {
                    $resumo['offline_sem_match']++;
                }

                $monitoramentoAtual = (string) ($equipamento['monitoramento'] ?? '');
                $ultimaLeituraAtual = $equipamento['ultima_leitura_em'] ?: null;

                $houveAlteracao = ($monitoramentoAtual !== $novoMonitoramento) || ($ultimaLeituraAtual !== $novaUltimaLeitura);

                $update->execute([
                    ':monitoramento' => $novoMonitoramento,
                    ':ultima_leitura_em' => $novaUltimaLeitura,
                    ':origem_monitoramento' => 'NDD CSV',
                    ':id' => (int) $equipamento['id'],
                ]);

                if ($houveAlteracao) {
                    $resumo['alterados']++;
                }

                if (count($detalhes) < 120) {
                    $detalhes[] = [
                        'unidade' => $equipamento['unidade'] ?: 'Sem unidade',
                        'setor' => $equipamento['setor'] ?: 'Sem setor',
                        'modelo' => $equipamento['modelo'] ?: 'Sem modelo',
                        'serie' => $equipamento['serie'] ?: '—',
                        'nome_impressora' => $equipamento['nome_impressora'] ?: '—',
                        'monitoramento_anterior' => $monitoramentoAtual ?: '—',
                        'monitoramento_novo' => $novoMonitoramento,
                        'ultima_leitura' => $novaUltimaLeitura ? date('d/m/Y', strtotime($novaUltimaLeitura)) : '—',
                        'motivo' => $motivo,
                    ];
                }
            }

            $db->commit();

            audit_log(
                'distribuicao_monitoramento_importado',
                'distribuicao_equipamentos',
                $clienteId,
                [
                    'cliente' => $cliente['nome'] ?? null,
                    'arquivo' => $resumo['arquivo'],
                    'processados' => $resumo['processados'],
                    'alterados' => $resumo['alterados'],
                    'online' => $resumo['online'],
                    'offline_leitura_antiga' => $resumo['offline_leitura_antiga'],
                    'offline_sem_match' => $resumo['offline_sem_match'],
                    'ignorados' => $resumo['ignorados'],
                ],
                current_user_id()
            );

            flash('Monitoramento sincronizado com sucesso para o cliente selecionado.');
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $erros[] = 'Falha ao processar o CSV: ' . $e->getMessage();
        }
    }
}

include 'includes/header.php';
echo render_flash();
?>
<div class="page-head">
    <div class="page-head-copy">
        <h2>Importar monitoramento</h2>
        <p>Envie o CSV do NDD para atualizar o campo <strong>monitoramento</strong> e a <strong>última leitura</strong> dos equipamentos do cliente.</p>
    </div>
    <div class="page-head-actions">
        <a href="distribuicao_index.php" class="btn btn-ghost"><?= icon('printer') ?> Voltar para distribuição</a>
    </div>
</div>

<?php if (!empty($erros)): ?>
    <div class="alert alert-error"><?= icon('off') ?> <?= implode(' · ', array_map('e', $erros)) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1.1fr .9fr;gap:18px;align-items:start">
    <div class="card">
        <div class="stitle"><?= icon('filter') ?> Sincronização por CSV</div>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_input() ?>
            <div class="form-grid">
                <div class="form-group full">
                    <label>Cliente <span class="req">*</span></label>
                    <select name="cliente_id" required>
                        <option value="">Selecione</option>
                        <?php foreach ($clientesDisponiveis as $cliente): ?>
                            <option value="<?= (int) $cliente['id'] ?>" <?= (int) post('cliente_id', 0) === (int) $cliente['id'] ? 'selected' : '' ?>>
                                <?= e($cliente['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Arquivo CSV do NDD <span class="req">*</span></label>
                    <input type="file" name="arquivo_csv" accept=".csv,text/csv" required>
                    <small style="display:block;margin-top:8px;color:var(--muted)">
                        Regras aplicadas: leitura do mês atual = <strong>Online</strong>; leitura fora do mês atual = <strong>Offline</strong>; equipamento ausente no CSV = <strong>Offline</strong>; equipamentos <strong>desinstalados/inativos/recolhidos</strong> são ignorados.
                    </small>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= icon('check') ?> Sincronizar monitoramento</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="stitle"><?= icon('clock') ?> O que esse importador atualiza</div>
        <ul style="margin:0;padding-left:18px;color:var(--muted);line-height:1.7">
            <li>Campo <strong>monitoramento</strong> com status Online ou Offline.</li>
            <li>Campo <strong>ultima_leitura_em</strong> com a data da última leitura encontrada.</li>
            <li>Campo <strong>origem_monitoramento</strong> com o valor <strong>NDD CSV</strong>.</li>
            <li>Campo <strong>ultima_sincronizacao_monitoramento_em</strong> com data e hora da sincronização.</li>
        </ul>
        <div style="height:12px"></div>
        <div class="alert alert-warning"><?= icon('filter') ?> Os desativados não entram nessa rotina. Continue mantendo esses registros manualmente, como você pediu.</div>
    </div>
</div>

<?php if ($resumo): ?>
    <div class="kpi-grid" style="margin:18px 0">
        <div class="stat-card"><div class="sc-label">Processados</div><div class="sc-value"><?= (int) $resumo['processados'] ?></div><div class="sc-icon"><?= icon('printer') ?></div><div class="sc-bar"></div></div>
        <div class="stat-card"><div class="sc-label">Online</div><div class="sc-value"><?= (int) $resumo['online'] ?></div><div class="sc-icon"><?= icon('check') ?></div><div class="sc-bar"></div></div>
        <div class="stat-card"><div class="sc-label">Offline por leitura</div><div class="sc-value"><?= (int) $resumo['offline_leitura_antiga'] ?></div><div class="sc-icon"><?= icon('off') ?></div><div class="sc-bar"></div></div>
        <div class="stat-card"><div class="sc-label">Offline ausente no CSV</div><div class="sc-value"><?= (int) $resumo['offline_sem_match'] ?></div><div class="sc-icon"><?= icon('off') ?></div><div class="sc-bar"></div></div>
    </div>

    <div class="card" style="margin-bottom:18px">
        <div class="stitle"><?= icon('check') ?> Resumo da importação</div>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px 18px;color:var(--muted)">
            <div><strong>Arquivo:</strong> <?= e($resumo['arquivo']) ?></div>
            <div><strong>Linhas do CSV:</strong> <?= (int) $resumo['linhas_csv'] ?></div>
            <div><strong>Equipamentos do cliente:</strong> <?= (int) $resumo['equipamentos'] ?></div>
            <div><strong>Ignorados:</strong> <?= (int) $resumo['ignorados'] ?></div>
            <div><strong>Encontrados no CSV:</strong> <?= (int) $resumo['encontrados'] ?></div>
            <div><strong>Registros alterados:</strong> <?= (int) $resumo['alterados'] ?></div>
        </div>
    </div>

    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 18px;border-bottom:1px solid var(--line)">
            <div class="stitle" style="margin:0"><?= icon('clock') ?> Prévia das alterações</div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Local</th>
                        <th>Equipamento</th>
                        <th>Monitoramento</th>
                        <th>Última leitura</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalhes as $item): ?>
                        <tr>
                            <td>
                                <strong><?= e($item['unidade']) ?></strong>
                                <div class="sub"><?= e($item['setor']) ?></div>
                            </td>
                            <td>
                                <strong><?= e($item['modelo']) ?></strong>
                                <div class="sub">Série: <?= e($item['serie']) ?> · Impressora: <?= e($item['nome_impressora']) ?></div>
                            </td>
                            <td>
                                <div class="sub">Antes: <?= e($item['monitoramento_anterior']) ?></div>
                                <span class="badge <?= $item['monitoramento_novo'] === 'Online' ? 'b-green' : 'b-red' ?>"><?= e($item['monitoramento_novo']) ?></span>
                            </td>
                            <td class="mono"><?= e($item['ultima_leitura']) ?></td>
                            <td><?= e($item['motivo']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($detalhes)): ?>
                        <tr><td colspan="5" style="padding:24px;text-align:center;color:var(--muted)">Nenhum equipamento processado nesta sincronização.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
