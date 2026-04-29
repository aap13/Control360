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
$ativosDisponiveis = $dispComp + $dispCel;
$ativosManutencao = $manutComp + $manutCel;

$chamadosResumo = ['total' => 0, 'abertos' => 0, 'hoje' => 0, 'fechadosMes' => 0];
$ultimosChamados = [];
$chamadosCategorias = [];
$maxCategoriaChamado = 1;
$setoresResumo = [];

try {
    if (function_exists('chamados_resumo_dashboard')) {
        $chamadosResumo = chamados_resumo_dashboard();
    }
    $ultimosChamados = $db->query("
        SELECT c.id, c.protocolo, c.assunto, c.status, c.prioridade, c.nome_solicitante, c.atualizado_em, cat.nome AS categoria_nome
        FROM hesk_chamados c
        LEFT JOIN hesk_categorias cat ON cat.id = c.categoria_id
        ORDER BY c.atualizado_em DESC, c.id DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $chamadosCategorias = $db->query("
        SELECT COALESCE(cat.nome, 'Sem categoria') AS nome, COUNT(*) AS total
        FROM hesk_chamados c
        LEFT JOIN hesk_categorias cat ON cat.id = c.categoria_id
        GROUP BY cat.nome
        ORDER BY total DESC, nome ASC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $maxCategoriaChamado = !empty($chamadosCategorias) ? max(array_map(static function ($row) { return (int) ($row['total'] ?? 0); }, $chamadosCategorias)) : 1;
} catch (Throwable $e) {
    $ultimosChamados = [];
    $chamadosCategorias = [];
}

$setorMap = [];
foreach ($setoresComp as $item) {
    $nome = trim((string) ($item['setor'] ?? '')) ?: 'Não informado';
    if (!isset($setorMap[$nome])) {
        $setorMap[$nome] = ['nome' => $nome, 'computadores' => 0, 'celulares' => 0, 'total' => 0];
    }
    $setorMap[$nome]['computadores'] += (int) ($item['n'] ?? 0);
    $setorMap[$nome]['total'] += (int) ($item['n'] ?? 0);
}
foreach ($setoresCel as $item) {
    $nome = trim((string) ($item['setor'] ?? '')) ?: 'Não informado';
    if (!isset($setorMap[$nome])) {
        $setorMap[$nome] = ['nome' => $nome, 'computadores' => 0, 'celulares' => 0, 'total' => 0];
    }
    $setorMap[$nome]['celulares'] += (int) ($item['n'] ?? 0);
    $setorMap[$nome]['total'] += (int) ($item['n'] ?? 0);
}
if (!empty($setorMap)) {
    uasort($setorMap, static function ($a, $b) { return ($b['total'] <=> $a['total']) ?: strcmp((string) $a['nome'], (string) $b['nome']); });
    $setoresResumo = array_slice(array_values($setorMap), 0, 6);
}

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

<?php
$statusLabelsDashboard = function_exists('chamados_status_labels') ? chamados_status_labels() : [];
$maxSetorResumo = !empty($setoresResumo) ? max(array_map(static function ($row) { return (int) ($row['total'] ?? 0); }, $setoresResumo)) : 1;
$maxMesFinDashboard = $maxMesFin > 0 ? (float) $maxMesFin : 1.0;
?>

<section class="dashboard-v39">
  <section class="dashboard-v39-hero">
    <div class="dashboard-v39-copy">
      <div class="dashboard-v39-kicker"><?= icon('dashboard') ?> Dashboard operacional</div>
      <h1 class="dashboard-v39-title">Uma visão mais clara do parque, chamados e custos.</h1>
      <p class="dashboard-v39-sub">A nova dashboard consolida inventário, atendimento e financeiro em uma leitura rápida, com o mesmo padrão visual dos chamados e sem aqueles cards azuis fora do contexto.</p>
      <div class="dashboard-v39-actions">
        <a href="computadores.php" class="btn btn-primary btn-sm"><?= icon('computer') ?> Computadores</a>
        <a href="celulares.php" class="btn btn-ghost btn-sm"><?= icon('phone') ?> Celulares</a>
        <a href="chamados_index.php" class="btn btn-ghost btn-sm"><?= icon('ticket') ?> Chamados</a>
        <a href="faturas.php?ano=<?= $anoAtual ?>&mes=<?= $mesAtual ?>" class="btn btn-ghost btn-sm"><?= icon('bill') ?> Financeiro</a>
      </div>
    </div>
    <div class="dashboard-v39-side">
      <div class="dashboard-v39-mini"><span class="k">Ativos totais</span><strong><?= number_format($totalAtivos, 0, ',', '.') ?></strong><small>Computadores + celulares cadastrados</small></div>
      <div class="dashboard-v39-mini"><span class="k">Chamados abertos</span><strong><?= number_format((int) ($chamadosResumo['abertos'] ?? 0), 0, ',', '.') ?></strong><small>Fila com acompanhamento pendente</small></div>
      <div class="dashboard-v39-mini"><span class="k">Gasto do mês</span><strong>R$<?= number_format($gastoMes, 0, ',', '.') ?></strong><small>Competência <?= $MESES[$mesAtual] ?></small></div>
      <div class="dashboard-v39-mini"><span class="k">Em uso</span><strong><?= number_format($ativosEmUso, 0, ',', '.') ?></strong><small>Ativos operacionais no momento</small></div>
    </div>
  </section>

  <section class="dashboard-v39-grid">
    <article class="dashboard-v39-card">
      <div class="label"><span>Total de ativos</span><span class="ni"><?= icon('box') ?></span></div>
      <div class="value"><?= number_format($totalAtivos, 0, ',', '.') ?></div>
      <div class="foot"><span class="chip"><?= number_format($totalComp, 0, ',', '.') ?> computadores</span><span class="chip"><?= number_format($totalCel, 0, ',', '.') ?> celulares</span></div>
    </article>
    <article class="dashboard-v39-card">
      <div class="label"><span>Ativos em uso</span><span class="ni"><?= icon('check') ?></span></div>
      <div class="value"><?= number_format($ativosEmUso, 0, ',', '.') ?></div>
      <div class="foot"><span class="chip"><?= number_format($ativosDisponiveis, 0, ',', '.') ?> disponíveis</span><span class="chip"><?= number_format($ativosManutencao, 0, ',', '.') ?> manutenção</span></div>
    </article>
    <article class="dashboard-v39-card">
      <div class="label"><span>Chamados</span><span class="ni"><?= icon('ticket') ?></span></div>
      <div class="value"><?= number_format((int) ($chamadosResumo['total'] ?? 0), 0, ',', '.') ?></div>
      <div class="foot"><span class="chip"><?= number_format((int) ($chamadosResumo['abertos'] ?? 0), 0, ',', '.') ?> em aberto</span><span class="chip"><?= number_format((int) ($chamadosResumo['hoje'] ?? 0), 0, ',', '.') ?> hoje</span></div>
    </article>
    <article class="dashboard-v39-card">
      <div class="label"><span>Financeiro do ano</span><span class="ni"><?= icon('bill') ?></span></div>
      <div class="value">R$<?= number_format($gastoAno, 0, ',', '.') ?></div>
      <div class="foot"><span class="chip"><?= number_format($pagamentosAno, 0, ',', '.') ?> lançamentos</span><span class="chip"><?= number_format($fornecedoresAtivos, 0, ',', '.') ?> fornecedores ativos</span></div>
    </article>
  </section>

  <section class="dashboard-v39-columns">
    <article class="card dashboard-v39-panel">
      <div class="panel-head">
        <div>
          <div class="panel-title"><?= icon('spark') ?> Panorama do inventário</div>
          <div class="panel-note">Distribuição atual entre operação, disponibilidade e manutenção.</div>
        </div>
      </div>
      <div class="panel-body dashboard-v39-stack">
        <div>
          <div class="dashboard-v39-row"><div class="meta"><strong>Computadores</strong><span><?= $emUsoComp ?> em uso · <?= $dispComp ?> disponíveis · <?= $manutComp ?> manutenção</span></div><div class="num"><?= $totalComp ?></div></div>
          <div class="dashboard-v39-progress"><span style="width:<?= $totalAtivos > 0 ? round(($totalComp / max($totalAtivos, 1)) * 100) : 0 ?>%"></span></div>
        </div>
        <div>
          <div class="dashboard-v39-row"><div class="meta"><strong>Celulares</strong><span><?= $emUsoCel ?> em uso · <?= $dispCel ?> disponíveis · <?= $manutCel ?> manutenção</span></div><div class="num"><?= $totalCel ?></div></div>
          <div class="dashboard-v39-progress"><span style="width:<?= $totalAtivos > 0 ? round(($totalCel / max($totalAtivos, 1)) * 100) : 0 ?>%"></span></div>
        </div>
        <div>
          <div class="dashboard-v39-row"><div class="meta"><strong>Capacidade operacional</strong><span>Percentual de ativos realmente em uso no parque</span></div><div class="num"><?= $totalAtivos > 0 ? round(($ativosEmUso / max($totalAtivos, 1)) * 100) : 0 ?>%</div></div>
          <div class="dashboard-v39-progress"><span style="width:<?= $totalAtivos > 0 ? round(($ativosEmUso / max($totalAtivos, 1)) * 100) : 0 ?>%"></span></div>
        </div>
        <?php if (canAccess('impressao_financeiro')): ?>
        <div>
          <div class="dashboard-v39-row"><div class="meta"><strong>Impressão consolidada</strong><span><?= number_format((float) ($printResumo['paginas'] ?? 0), 0, ',', '.') ?> páginas · <?= (int) ($printResumo['equipamentos'] ?? 0) ?> impressoras</span></div><div class="num">R$<?= number_format((float) ($printResumo['total'] ?? 0), 0, ',', '.') ?></div></div>
          <div class="dashboard-v39-progress"><span style="width:100%"></span></div>
        </div>
        <?php endif; ?>
      </div>
    </article>

    <article class="card dashboard-v39-panel">
      <div class="panel-head">
        <div>
          <div class="panel-title"><?= icon('bill') ?> Financeiro recente</div>
          <div class="panel-note">Últimos meses registrados em <?= $anoAtual ?>.</div>
        </div>
        <a href="faturas.php?ano=<?= $anoAtual ?>" class="btn btn-ghost btn-xs">Abrir financeiro</a>
      </div>
      <div class="panel-body">
        <?php if (empty($mesesFin)): ?>
          <div class="empty-state"><?= icon('bill') ?><p>Nenhum lançamento financeiro registrado em <?= $anoAtual ?>.</p></div>
        <?php else: ?>
        <div class="dashboard-v39-bars">
          <?php foreach ($mesesFin as $mf): $h = $maxMesFinDashboard > 0 ? max(12, round((((float) $mf['total']) / $maxMesFinDashboard) * 100)) : 12; ?>
          <div class="dashboard-v39-barcol">
            <div class="dashboard-v39-barval">R$<?= number_format((float) $mf['total'], 0, ',', '.') ?></div>
            <div class="dashboard-v39-barwrap"><div class="dashboard-v39-bar" style="height:<?= $h ?>%"></div></div>
            <div class="dashboard-v39-barlabel"><?= $MESES[(int) $mf['mes']] ?? '—' ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </article>

    <article class="card dashboard-v39-panel">
      <div class="panel-head">
        <div>
          <div class="panel-title"><?= icon('ticket') ?> Chamados recentes</div>
          <div class="panel-note">Atualizações mais recentes da operação.</div>
        </div>
        <a href="chamados_index.php" class="btn btn-ghost btn-xs">Ver fila</a>
      </div>
      <div class="panel-body">
        <?php if (empty($ultimosChamados)): ?>
          <div class="empty-state"><?= icon('ticket') ?><p>Nenhum chamado registrado ainda.</p></div>
        <?php else: ?>
        <div class="dashboard-v39-ticket">
          <?php foreach ($ultimosChamados as $item): $statusKey = (string) ($item['status'] ?? ''); ?>
          <div class="dashboard-v39-ticket-item">
            <div class="dashboard-v39-ticket-top">
              <div>
                <div class="dashboard-v39-ticket-code"><?= icon('ticket') ?> <a href="chamados_visualizar.php?id=<?= (int) ($item['id'] ?? 0) ?>"><?= e($item['protocolo'] ?? '—') ?></a></div>
                <div class="dashboard-v39-ticket-subject"><?= e($item['assunto'] ?? 'Sem assunto') ?></div>
              </div>
              <?php if ($statusKey !== ''): ?><span class="badge <?= e(chamados_status_badge_class($statusKey)) ?>"><?= e($statusLabelsDashboard[$statusKey] ?? $statusKey) ?></span><?php endif; ?>
            </div>
            <div class="dashboard-v39-ticket-meta">
              <?php if (!empty($item['categoria_nome'])): ?><span class="dashboard-v39-badge"><?= e($item['categoria_nome']) ?></span><?php endif; ?>
              <?php if (!empty($item['nome_solicitante'])): ?><span class="dashboard-v39-badge"><?= e($item['nome_solicitante']) ?></span><?php endif; ?>
              <span class="dashboard-v39-badge"><?= date('d/m/Y H:i', strtotime((string) ($item['atualizado_em'] ?? 'now'))) ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </article>
  </section>

  <section class="dashboard-v39-split">
    <article class="card dashboard-v39-panel">
      <div class="panel-head">
        <div>
          <div class="panel-title"><?= icon('users') ?> Setores com mais ativos</div>
          <div class="panel-note">Leitura rápida do volume por área.</div>
        </div>
      </div>
      <div class="panel-body">
        <?php if (empty($setoresResumo)): ?>
          <div class="empty-state"><?= icon('users') ?><p>Nenhum setor disponível no momento.</p></div>
        <?php else: ?>
        <div class="dashboard-v39-stack">
          <?php foreach ($setoresResumo as $setor): $pct = $maxSetorResumo > 0 ? round((((int) $setor['total']) / $maxSetorResumo) * 100) : 0; ?>
          <div>
            <div class="dashboard-v39-row">
              <div class="meta"><strong><?= e($setor['nome']) ?></strong><span><?= (int) $setor['computadores'] ?> computadores · <?= (int) $setor['celulares'] ?> celulares</span></div>
              <div class="num"><?= (int) $setor['total'] ?></div>
            </div>
            <div class="dashboard-v39-progress"><span style="width:<?= $pct ?>%"></span></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </article>

    <article class="card dashboard-v39-panel">
      <div class="panel-head">
        <div>
          <div class="panel-title"><?= icon('clock') ?> Últimos ativos cadastrados</div>
          <div class="panel-note">Ativos adicionados recentemente no sistema.</div>
        </div>
      </div>
      <div class="panel-body">
        <?php if (empty($recentes)): ?>
          <div class="empty-state"><?= icon('box') ?><p>Nenhum ativo cadastrado ainda.</p></div>
        <?php else: ?>
        <div class="table-wrap">
          <table class="dashboard-v39-table">
            <thead>
              <tr><th>Ativo</th><th>Usuário</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
              <?php $sm = ['Em uso' => 'b-green', 'Disponível' => 'b-neutral', 'Manutenção' => 'b-amber', 'Desativado' => 'b-gray']; foreach ($recentes as $r): ?>
              <tr>
                <td>
                  <span class="main"><?= e($r['nome_item'] ?: 'Sem nome') ?></span>
                  <span class="sub"><?= e(($r['cat'] === 'computador' ? 'Computador' : 'Celular') . ' · ' . ($r['marca'] ?: 'Marca não informada')) ?></span>
                </td>
                <td><?= e(title_case_safe((string) ($r['usuario_nome'] ?? ''))) ?></td>
                <td><span class="badge <?= $sm[$r['status']] ?? 'b-gray' ?>"><?= e($r['status'] ?: '—') ?></span></td>
                <td><a href="editar.php?tipo=<?= $r['cat'] ?>&id=<?= (int) $r['id'] ?>" class="btn-icon"><?= icon('edit') ?></a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </article>
  </section>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
