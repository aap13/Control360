<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$pageTitle = 'Dashboard';
$db = getDB();

$anoAtual = (int) date('Y');
$mesAtual = (int) date('n');
$MESES = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

$flash = getFlash();

$defaults = [
    'totalComp' => 0, 'emUsoComp' => 0, 'dispComp' => 0, 'manutComp' => 0,
    'totalCel' => 0, 'emUsoCel' => 0, 'dispCel' => 0, 'manutCel' => 0,
    'gastoMes' => 0.0, 'gastoAno' => 0.0, 'pagamentosAno' => 0, 'fornecedoresAtivos' => 0,
    'mesesFin' => [], 'maxMesFin' => 1, 'topForn' => [], 'maxForn' => 1,
    'setoresComp' => [], 'setoresCel' => [], 'recentes' => [],
    'printResumo' => ['paginas' => 0, 'fixo' => 0.0, 'impressao' => 0.0, 'total' => 0.0, 'equipamentos' => 0, 'competencias' => 0],
    'printEmpresas' => [], 'printMaxTotal' => 1.0,
];
extract($defaults);

try {
    $totalComp = (int) $db->query("SELECT COUNT(*) FROM computadores")->fetchColumn();
    $emUsoComp = (int) $db->query("SELECT COUNT(*) FROM computadores WHERE status='Em uso'")->fetchColumn();
    $dispComp  = (int) $db->query("SELECT COUNT(*) FROM computadores WHERE status='Disponível'")->fetchColumn();
    $manutComp = (int) $db->query("SELECT COUNT(*) FROM computadores WHERE status='Manutenção'")->fetchColumn();

    $totalCel = (int) $db->query("SELECT COUNT(*) FROM celulares")->fetchColumn();
    $emUsoCel = (int) $db->query("SELECT COUNT(*) FROM celulares WHERE status='Em uso'")->fetchColumn();
    $dispCel  = (int) $db->query("SELECT COUNT(*) FROM celulares WHERE status='Disponível'")->fetchColumn();
    $manutCel = (int) $db->query("SELECT COUNT(*) FROM celulares WHERE status='Manutenção'")->fetchColumn();

    $stmt = $db->prepare("SELECT COALESCE(SUM(valor),0) FROM faturas WHERE mes_referencia=:m AND ano_referencia=:a");
    $stmt->execute([':m' => $mesAtual, ':a' => $anoAtual]);
    $gastoMes = (float) $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COALESCE(SUM(valor),0) FROM faturas WHERE ano_referencia=:a");
    $stmt->execute([':a' => $anoAtual]);
    $gastoAno = (float) $stmt->fetchColumn();

    $pagamentosAno = (int) $db->query("SELECT COUNT(*) FROM faturas WHERE ano_referencia={$anoAtual}")->fetchColumn();
    $fornecedoresAtivos = (int) $db->query("SELECT COUNT(*) FROM fornecedores WHERE ativo=1")->fetchColumn();

    $ultMeses = $db->prepare("
        SELECT mes_referencia AS mes, SUM(valor) AS total
        FROM faturas
        WHERE ano_referencia=:a
        GROUP BY mes_referencia
        ORDER BY mes_referencia DESC
        LIMIT 6
    ");
    $ultMeses->execute([':a' => $anoAtual]);
    $mesesFin = array_reverse($ultMeses->fetchAll(PDO::FETCH_ASSOC));
    $maxMesFin = $mesesFin ? max(array_column($mesesFin, 'total')) : 1;

    $topFornStmt = $db->prepare("
        SELECT fo.nome, SUM(f.valor) AS total
        FROM faturas f
        JOIN fornecedores fo ON fo.id = f.fornecedor_id
        WHERE f.ano_referencia=:a
        GROUP BY fo.id, fo.nome
        ORDER BY total DESC
        LIMIT 5
    ");
    $topFornStmt->execute([':a' => $anoAtual]);
    $topForn = $topFornStmt->fetchAll(PDO::FETCH_ASSOC);
    $maxForn = $topForn ? max(array_column($topForn, 'total')) : 1;

    $setoresComp = $db->query("SELECT COALESCE(setor,'Não informado') AS setor, COUNT(*) AS n FROM computadores GROUP BY setor ORDER BY n DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
    $setoresCel  = $db->query("SELECT COALESCE(setor,'Não informado') AS setor, COUNT(*) AS n FROM celulares GROUP BY setor ORDER BY n DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);

    try {
        $printResumoStmt = $db->query("
            SELECT
                COALESCE(SUM(paginas_produzidas), 0) AS paginas,
                COALESCE(SUM(valor_fixo), 0) AS fixo,
                COALESCE(SUM(valor_variavel), 0) AS impressao,
                COALESCE(SUM(valor_total), 0) AS total,
                COUNT(DISTINCT CASE
                    WHEN COALESCE(TRIM(serie), '') <> '' THEN TRIM(serie)
                    ELSE CONCAT('ROW||', id)
                END) AS equipamentos,
                COUNT(DISTINCT competencia) AS competencias
            FROM impressao_financeiro_equipamentos
        ");
        $printResumo = $printResumoStmt->fetch(PDO::FETCH_ASSOC) ?: $printResumo;

        $printEmpresasStmt = $db->query("
            SELECT
                empresa,
                COALESCE(SUM(paginas_produzidas), 0) AS paginas,
                COALESCE(SUM(valor_fixo), 0) AS fixo,
                COALESCE(SUM(valor_variavel), 0) AS impressao,
                COALESCE(SUM(valor_total), 0) AS total
            FROM impressao_financeiro_equipamentos
            GROUP BY empresa
            ORDER BY total DESC, empresa ASC
            LIMIT 6
        ");
        $printEmpresas = $printEmpresasStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $printMaxTotal = !empty($printEmpresas) ? max(array_map(function ($row) { return (float) ($row['total'] ?? 0); }, $printEmpresas)) : 1.0;
    } catch (Throwable $e) {
        $printEmpresas = [];
    }

    try {
        $recentes = $db->query("
            SELECT 'computador' AS cat, id, nome_dispositivo AS nome_item, marca, modelo, usuario_responsavel AS usuario_nome, setor, status, data_cadastro
            FROM computadores
            UNION ALL
            SELECT 'celular' AS cat, id, CONCAT(COALESCE(marca,''), CASE WHEN modelo IS NOT NULL AND modelo <> '' THEN CONCAT(' ', modelo) ELSE '' END) AS nome_item, marca, modelo, usuario_responsavel AS usuario_nome, setor, status, data_cadastro
            FROM celulares
            ORDER BY data_cadastro DESC
            LIMIT 6
        ")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $recentes = [];
    }
} catch (Throwable $e) {
    // alerta genérico removido; métricas indisponíveis ficam zeradas
    $flash = $flash;
}

$ativosEmUso = $emUsoComp + $emUsoCel;
$totalAtivos = $totalComp + $totalCel;

include __DIR__ . '/includes/header.php';

function title_case_safe(string $value): string {
    $value = trim($value);
    if ($value === '') return 'Não vinculado';
    if (function_exists('mb_convert_case')) {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }
    return ucwords(strtolower($value));
}
?>


<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type']==='success' ? 'success' : 'error' ?>">
    <?= icon($flash['type']==='success' ? 'check' : 'off') ?>
    <span><?= htmlspecialchars($flash['msg']) ?></span>
</div>
<?php endif; ?>

<section class="dashboard-hero">
    <div class="hero-copy">
        <div class="hero-kicker"><?= icon('dashboard') ?> Painel operacional</div>
        <h2 class="hero-title">Visão central do inventário e do financeiro.</h2>
        <p class="hero-subtitle">Acompanhe rapidamente a saúde do parque, o volume de ativos em uso e a movimentação financeira do período sem precisar navegar entre telas.</p>
    </div>
    <div class="hero-quick">
        <div class="hero-chip"><strong><?= $totalAtivos ?></strong><span>Total de ativos cadastrados</span></div>
        <div class="hero-chip"><strong><?= $ativosEmUso ?></strong><span>Equipamentos em uso</span></div>
        <div class="hero-chip"><strong>R$<?= number_format($gastoAno,0,',','.') ?></strong><span>Acumulado de <?= $anoAtual ?></span></div>
        <?php if (canAccess('impressao_financeiro')): ?><div class="hero-chip"><strong><?= number_format((float) ($printResumo['paginas'] ?? 0), 0, ',', '.') ?></strong><span>Páginas de impressão no consolidado</span></div><?php endif; ?>
    </div>
</section>

<section class="kpi-grid">
    <a href="computadores.php" class="kpi-card">
        <div class="kpi-head"><div class="kpi-label">Computadores</div><div class="kpi-icon"><?= icon('computer') ?></div></div>
        <div class="kpi-value"><?= $totalComp ?></div>
        <div class="kpi-sub"><span class="ok"><?= $emUsoComp ?> em uso</span><?php if ($dispComp): ?><span class="soft"><?= $dispComp ?> disponíveis</span><?php endif; ?><?php if ($manutComp): ?><span class="warn"><?= $manutComp ?> manutenção</span><?php endif; ?></div>
        <div class="kpi-strip"></div>
    </a>
    <a href="celulares.php" class="kpi-card">
        <div class="kpi-head"><div class="kpi-label">Celulares</div><div class="kpi-icon"><?= icon('phone') ?></div></div>
        <div class="kpi-value"><?= $totalCel ?></div>
        <div class="kpi-sub"><span class="ok"><?= $emUsoCel ?> em uso</span><?php if ($dispCel): ?><span class="soft"><?= $dispCel ?> disponíveis</span><?php endif; ?><?php if ($manutCel): ?><span class="warn"><?= $manutCel ?> manutenção</span><?php endif; ?></div>
        <div class="kpi-strip"></div>
    </a>
    <a href="faturas.php?ano=<?= $anoAtual ?>&mes=<?= $mesAtual ?>" class="kpi-card">
        <div class="kpi-head"><div class="kpi-label">Mês atual</div><div class="kpi-icon"><?= icon('bill') ?></div></div>
        <div class="kpi-value" style="font-size:24px">R$<?= number_format($gastoMes,2,',','.') ?></div>
        <div class="kpi-sub"><span class="soft">Referência <?= $MESES[$mesAtual] ?></span><span class="soft">Financeiro atual</span></div>
        <div class="kpi-strip"></div>
    </a>
    <a href="fornecedores.php" class="kpi-card">
        <div class="kpi-head"><div class="kpi-label">Fornecedores</div><div class="kpi-icon"><?= icon('box') ?></div></div>
        <div class="kpi-value"><?= $fornecedoresAtivos ?></div>
        <div class="kpi-sub"><span class="soft"><?= $pagamentosAno ?> pagamentos em <?= $anoAtual ?></span></div>
        <div class="kpi-strip"></div>
    </a>
    <?php if (canAccess('impressao_financeiro')): ?>
    <a href="impressao_financeiro.php" class="kpi-card kpi-card-print">
        <div class="kpi-head"><div class="kpi-label">Impressão</div><div class="kpi-icon"><?= icon('printer') ?></div></div>
        <div class="kpi-value" style="font-size:24px"><?= number_format((float) ($printResumo['paginas'] ?? 0), 0, ',', '.') ?></div>
        <div class="kpi-sub">
            <span class="soft"><?= (int) ($printResumo['equipamentos'] ?? 0) ?> impressoras</span>
            <span class="soft">R$<?= number_format((float) ($printResumo['total'] ?? 0), 2, ',', '.') ?></span>
        </div>
        <div class="kpi-strip"></div>
    </a>
    <?php endif; ?>
</section>

<section class="dashboard-panels">

    <div class="card">
        <div class="panel-header">
            <div>
                <div class="panel-title"><?= icon('bill') ?> Gastos por mês</div>
                <div class="panel-note">Últimos meses registrados em <?= $anoAtual ?>.</div>
            </div>
            <a href="faturas.php?ano=<?= $anoAtual ?>" class="btn btn-ghost btn-sm">Ver financeiro</a>
        </div>
        <?php if (empty($mesesFin)): ?>
            <div class="empty-state"><?= icon('bill') ?><p>Nenhum lançamento financeiro registrado em <?= $anoAtual ?>.</p></div>
        <?php else: ?>
        <div class="mini-chart">
            <?php foreach ($mesesFin as $mf): $h = $maxMesFin > 0 ? max(14, round(((float)$mf['total'] / $maxMesFin) * 100)) : 14; ?>
            <div class="mini-chart-col">
                <div class="mini-chart-value">R$<?= number_format((float)$mf['total'],0,'.','.') ?></div>
                <div class="mini-chart-barwrap"><div class="mini-chart-bar" style="height:<?= $h ?>%"></div></div>
                <div class="mini-chart-label"><?= $MESES[(int)$mf['mes']] ?? '—' ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="card">
        <div class="panel-header">
            <div>
                <div class="panel-title"><?= icon('box') ?> Top fornecedores</div>
                <div class="panel-note">Ranking por valor acumulado no ano.</div>
            </div>
        </div>
        <?php if (empty($topForn)): ?>
            <div class="empty-state"><?= icon('box') ?><p>Sem dados de fornecedores para exibir.</p></div>
        <?php else: ?>
        <div class="list-metric">
            <?php foreach ($topForn as $tf): $pct = $maxForn > 0 ? round((((float)$tf['total']) / $maxForn) * 100) : 0; ?>
            <div class="list-row">
                <div class="list-top">
                    <div class="list-name" title="<?= htmlspecialchars($tf['nome']) ?>"><?= htmlspecialchars($tf['nome']) ?></div>
                    <div class="list-num mono">R$<?= number_format((float)$tf['total'],0,'.','.') ?></div>
                </div>
                <div class="progress"><span style="width:<?= $pct ?>%"></span></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="dashboard-panels-2">
    <div class="card">
        <div class="panel-header">
            <div>
                <div class="panel-title"><?= icon('computer') ?> Computadores por setor</div>
                <div class="panel-note">Distribuição dos dispositivos por área.</div>
            </div>
        </div>
        <?php if (empty($setoresComp)): ?>
            <div class="empty-state"><?= icon('computer') ?><p>Nenhum dado disponível.</p></div>
        <?php else: ?>
        <div class="setor-list">
            <?php $mx = max(array_column($setoresComp, 'n')); foreach ($setoresComp as $s): $pct = $mx > 0 ? round((((int)$s['n']) / $mx) * 100) : 0; ?>
            <div class="list-row">
                <div class="list-top"><div class="list-name"><?= htmlspecialchars($s['setor'] ?: 'Não informado') ?></div><div class="list-num mono"><?= (int)$s['n'] ?></div></div>
                <div class="progress"><span style="width:<?= $pct ?>%"></span></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="card">
        <div class="panel-header">
            <div>
                <div class="panel-title"><?= icon('phone') ?> Celulares por setor</div>
                <div class="panel-note">Uso da linha móvel por equipe.</div>
            </div>
        </div>
        <?php if (empty($setoresCel)): ?>
            <div class="empty-state"><?= icon('phone') ?><p>Nenhum dado disponível.</p></div>
        <?php else: ?>
        <div class="setor-list">
            <?php $mx = max(array_column($setoresCel, 'n')); foreach ($setoresCel as $s): $pct = $mx > 0 ? round((((int)$s['n']) / $mx) * 100) : 0; ?>
            <div class="list-row">
                <div class="list-top"><div class="list-name"><?= htmlspecialchars($s['setor'] ?: 'Não informado') ?></div><div class="list-num mono"><?= (int)$s['n'] ?></div></div>
                <div class="progress"><span style="width:<?= $pct ?>%"></span></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php if (canAccess('impressao_financeiro')): ?>
<section class="card print-summary-card">
    <div class="panel-header">
        <div>
            <div class="panel-title"><?= icon('printer') ?> Resumo geral de páginas e custos de impressão</div>
            <div class="panel-note">Consolidação de todas as competências importadas no financeiro de impressão.</div>
        </div>
        <a href="impressao_financeiro.php" class="btn btn-ghost btn-sm"><?= icon('bill') ?> Abrir financeiro impressão</a>
    </div>

    <div class="print-summary-grid">
        <div class="print-summary-metrics">
            <div class="print-summary-kpi">
                <span class="k">Páginas totais</span>
                <strong><?= number_format((float) ($printResumo['paginas'] ?? 0), 0, ',', '.') ?></strong>
                <small>Produção consolidada</small>
            </div>
            <div class="print-summary-kpi">
                <span class="k">Valor fixo</span>
                <strong>R$<?= number_format((float) ($printResumo['fixo'] ?? 0), 2, ',', '.') ?></strong>
                <small>Base contratual</small>
            </div>
            <div class="print-summary-kpi">
                <span class="k">Valor por páginas</span>
                <strong>R$<?= number_format((float) ($printResumo['impressao'] ?? 0), 2, ',', '.') ?></strong>
                <small>Produção faturada</small>
            </div>
            <div class="print-summary-kpi">
                <span class="k">Total pago</span>
                <strong>R$<?= number_format((float) ($printResumo['total'] ?? 0), 2, ',', '.') ?></strong>
                <small><?= (int) ($printResumo['competencias'] ?? 0) ?> competência(s)</small>
            </div>
        </div>

        <div class="print-company-list">
            <?php if (empty($printEmpresas)): ?>
                <div class="empty-state"><?= icon('printer') ?><p>Nenhum dado de impressão importado ainda.</p></div>
            <?php else: ?>
                <?php foreach ($printEmpresas as $idx => $row): $pct = $printMaxTotal > 0 ? round((((float) $row['total']) / $printMaxTotal) * 100) : 0; ?>
                <div class="print-company-row">
                    <div class="print-company-rank">#<?= $idx + 1 ?></div>
                    <div class="print-company-main">
                        <div class="print-company-top">
                            <div class="print-company-name" title="<?= htmlspecialchars((string) ($row['empresa'] ?? 'Sem empresa')) ?>"><?= htmlspecialchars((string) ($row['empresa'] ?? 'Sem empresa')) ?></div>
                            <div class="print-company-total">R$<?= number_format((float) ($row['total'] ?? 0), 0, ',', '.') ?></div>
                        </div>
                        <div class="progress"><span style="width:<?= $pct ?>%"></span></div>
                        <div class="print-company-sub">
                            <span><?= number_format((float) ($row['paginas'] ?? 0), 0, ',', '.') ?> páginas</span>
                            <span>Fixo: R$<?= number_format((float) ($row['fixo'] ?? 0), 0, ',', '.') ?></span>
                            <span>Impressão: R$<?= number_format((float) ($row['impressao'] ?? 0), 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="card recent-card">
    <div class="recent-head">
        <div>
            <div class="panel-title"><?= icon('clock') ?> Últimos cadastros</div>
            <div class="panel-note">Ativos adicionados recentemente no sistema.</div>
        </div>
        <div class="recent-actions">
            <a href="computadores.php" class="btn btn-ghost btn-xs"><?= icon('computer') ?> Ver PCs</a>
            <a href="celulares.php" class="btn btn-ghost btn-xs"><?= icon('phone') ?> Ver celulares</a>
        </div>
    </div>
    <?php if (empty($recentes)): ?>
        <div class="empty-state"><?= icon('box') ?><p>Nenhum ativo cadastrado ainda.</p></div>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Nome</th>
                    <th>Marca / Modelo</th>
                    <th>Usuário</th>
                    <th>Setor</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php $sm = ['Em uso' => 'b-green', 'Disponível' => 'b-neutral', 'Manutenção' => 'b-amber', 'Desativado' => 'b-gray']; foreach ($recentes as $r): ?>
                <tr>
                    <td><span class="badge b-neutral"><?= icon($r['cat'] === 'computador' ? 'computer' : 'phone') ?> <?= $r['cat'] === 'computador' ? 'PC' : 'Celular' ?></span></td>
                    <td><?= $r['nome_item'] ? '<span class="device-tag">'.htmlspecialchars($r['nome_item']).'</span>' : '<span style="color:var(--muted2)">—</span>' ?></td>
                    <td><strong><?= htmlspecialchars($r['marca'] ?: '—') ?></strong><div class="sub"><?= htmlspecialchars($r['modelo'] ?: '—') ?></div></td>
                    <td><?= htmlspecialchars(title_case_safe((string)($r['usuario_nome'] ?? ''))) ?></td>
                    <td><span class="setor-tag"><?= htmlspecialchars($r['setor'] ?: 'Não informado') ?></span></td>
                    <td><span class="badge <?= $sm[$r['status']] ?? 'b-gray' ?>"><?= htmlspecialchars($r['status'] ?: '—') ?></span></td>
                    <td><a href="editar.php?tipo=<?= $r['cat'] ?>&id=<?= (int)$r['id'] ?>" class="btn-icon"><?= icon('edit') ?></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
