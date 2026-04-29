<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
guard_current_page_access();

$db = getDB();
$statuses = chamados_status_labels();
$pageTitle = 'Relatórios de chamados';

function fmt_minutes($minutes)
{
    if ($minutes === null || $minutes === '') {
        return '-';
    }
    $minutes = (int) round((float) $minutes);
    if ($minutes <= 0) return '0 min';
    $days = floor($minutes / 1440);
    $hours = floor(($minutes % 1440) / 60);
    $mins = $minutes % 60;
    $parts = array();
    if ($days > 0) {
        $parts[] = $days . 'd';
    }
    if ($hours > 0) {
        $parts[] = $hours . 'h';
    }
    if ($mins > 0 || !$parts) {
        $parts[] = $mins . 'min';
    }
    return implode(' ', $parts);
}

function csv_value($value)
{
    if ($value === null || $value === '') {
        return '-';
    }
    return $value;
}

function sla_excedido_minutos(array $row): ?int
{
    return chamados_sla_excedido_minutos($row);
}

function chart_donut($a, $b, $colorA, $colorB)
{
    $total = max(1, $a + $b);
    $degA = round(($a / $total) * 360, 2);
    return 'background:conic-gradient(' . $colorA . ' 0deg ' . $degA . 'deg, ' . $colorB . ' ' . $degA . 'deg 360deg);';
}

$mesSelecionado = get_query('mes', '');
if (!preg_match('/^\d{4}-\d{2}$/', $mesSelecionado)) {
    $mesSelecionado = '';
}

$baseWhere = array();
$params = array();
if ($mesSelecionado !== '') {
    $baseWhere[] = "DATE_FORMAT(c.criado_em, '%Y-%m') = :mes";
    $params[':mes'] = $mesSelecionado;
}
$baseWhereSql = $baseWhere ? ('WHERE ' . implode(' AND ', $baseWhere)) : '';

$mesesDisponiveis = $db->query("SELECT DATE_FORMAT(criado_em, '%Y-%m') AS periodo
                                FROM hesk_chamados
                                GROUP BY DATE_FORMAT(criado_em, '%Y-%m')
                                ORDER BY periodo DESC")->fetchAll();

$metricasSql = "SELECT c.id, c.protocolo, c.nome_solicitante, c.email_solicitante, c.assunto,
                       COALESCE(cat.nome, 'Sem categoria') AS categoria_nome,
                       c.prioridade, c.status, c.origem, c.ultimo_autor,
                       COALESCE(u.nome, 'Não atribuído') AS tecnico_nome,
                       c.criado_em, c.atualizado_em, c.fechado_em, cat.sla_horas,
                       COALESCE(msg.total_interacoes, 0) AS total_interacoes,
                       COALESCE(msg.total_interacoes_publicas, 0) AS total_interacoes_publicas,
                       COALESCE(msg.total_notas_internas, 0) AS total_notas_internas,
                       first_reply.primeira_resposta_em,
                       first_assign.primeira_atribuicao_em,
                       TIMESTAMPDIFF(MINUTE, c.criado_em, first_reply.primeira_resposta_em) AS tempo_primeira_resposta_min,
                       TIMESTAMPDIFF(MINUTE, c.criado_em, first_assign.primeira_atribuicao_em) AS tempo_primeira_atribuicao_min,
                       CASE
                           WHEN c.status IN ('resolvido','fechado') THEN TIMESTAMPDIFF(MINUTE, c.criado_em, COALESCE(c.fechado_em, c.atualizado_em))
                           ELSE NULL
                       END AS tempo_resolucao_min
                FROM hesk_chamados c
                LEFT JOIN hesk_categorias cat ON cat.id = c.categoria_id
                LEFT JOIN usuarios u ON u.id = c.tecnico_id
                LEFT JOIN (
                    SELECT chamado_id,
                           COUNT(*) AS total_interacoes,
                           SUM(CASE WHEN privado = 0 THEN 1 ELSE 0 END) AS total_interacoes_publicas,
                           SUM(CASE WHEN privado = 1 THEN 1 ELSE 0 END) AS total_notas_internas
                    FROM hesk_mensagens
                    GROUP BY chamado_id
                ) msg ON msg.chamado_id = c.id
                LEFT JOIN (
                    SELECT chamado_id, MIN(criado_em) AS primeira_resposta_em
                    FROM hesk_mensagens
                    WHERE origem = 'interno' AND privado = 0
                    GROUP BY chamado_id
                ) first_reply ON first_reply.chamado_id = c.id
                LEFT JOIN (
                    SELECT id, MIN(atualizado_em) AS primeira_atribuicao_em
                    FROM hesk_chamados
                    WHERE tecnico_id IS NOT NULL
                    GROUP BY id
                ) first_assign ON first_assign.id = c.id
                $baseWhereSql";

if (get_query('export', '') === 'csv' || get_query('export', '') === 'atendimentos') {
    $exportTipo = get_query('export', 'csv');
    $sql = $metricasSql . ' ORDER BY c.atualizado_em DESC';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    $sufixo = $mesSelecionado !== '' ? ('_' . $mesSelecionado) : '_geral';
    header('Content-Disposition: attachment; filename=' . ($exportTipo === 'atendimentos' ? 'chamados_atendimento' : 'chamados') . $sufixo . '.csv');
    $out = fopen('php://output', 'w');

    if ($exportTipo === 'atendimentos') {
        fputcsv($out, array(
            'Protocolo','Assunto','Solicitante','Email','Categoria','Prioridade','Status','Tecnico','Origem',
            'Criado em','Primeira atribuicao em','Tempo ate atribuicao','Primeira resposta em','Tempo primeira resposta',
            'Resolvido em','Tempo resolucao','Interacoes totais','Interacoes publicas','Notas internas','SLA horas','SLA status','Tempo SLA estourado','Ultimo autor'
        ), ';');
        foreach ($rows as $row) {
            $slaStatus = chamados_sla_status($row);
            $slaExcedidoMin = sla_excedido_minutos($row);
            fputcsv($out, array(
                $row['protocolo'],
                $row['assunto'],
                $row['nome_solicitante'],
                $row['email_solicitante'],
                $row['categoria_nome'],
                chamados_prioridade_labels()[$row['prioridade']] ?? $row['prioridade'],
                $statuses[$row['status']] ?? $row['status'],
                $row['tecnico_nome'],
                $row['origem'],
                $row['criado_em'],
                csv_value($row['primeira_atribuicao_em']),
                fmt_minutes($row['tempo_primeira_atribuicao_min']),
                csv_value($row['primeira_resposta_em']),
                fmt_minutes($row['tempo_primeira_resposta_min']),
                csv_value($row['fechado_em'] ?: ($row['status'] === 'resolvido' ? $row['atualizado_em'] : null)),
                fmt_minutes($row['tempo_resolucao_min']),
                (int) $row['total_interacoes'],
                (int) $row['total_interacoes_publicas'],
                (int) $row['total_notas_internas'],
                (int) $row['sla_horas'],
                chamados_sla_label($slaStatus),
                fmt_minutes($slaExcedidoMin),
                $row['ultimo_autor'],
            ), ';');
        }
    } else {
        fputcsv($out, array('Protocolo','Assunto','Categoria','Prioridade','SLA','Status','Tecnico','Criado em','Atualizado em','Primeira resposta','Resolucao','Interacoes'), ';');
        foreach ($rows as $row) {
            $slaStatus = chamados_sla_status($row);
            fputcsv($out, array(
                $row['protocolo'],
                $row['assunto'],
                $row['categoria_nome'],
                chamados_prioridade_labels()[$row['prioridade']] ?? $row['prioridade'],
                chamados_sla_label($slaStatus),
                $statuses[$row['status']] ?? $row['status'],
                $row['tecnico_nome'],
                $row['criado_em'],
                $row['atualizado_em'],
                fmt_minutes($row['tempo_primeira_resposta_min']),
                fmt_minutes($row['tempo_resolucao_min']),
                (int) $row['total_interacoes'],
            ), ';');
        }
    }
    fclose($out);
    exit;
}

$resumo = chamados_resumo_dashboard();

$stmt = $db->prepare("SELECT COUNT(*) FROM hesk_chamados c $baseWhereSql" . ($baseWhere ? '' : ''));
$stmt->execute($params);
$totalPeriodo = (int) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM hesk_chamados c " . ($baseWhere ? ($baseWhereSql . " AND c.status NOT IN ('resolvido','fechado')") : "WHERE c.status NOT IN ('resolvido','fechado')"));
$stmt->execute($params);
$abertos = (int) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM hesk_chamados c " . ($baseWhere ? ($baseWhereSql . " AND c.status IN ('resolvido','fechado')") : "WHERE c.status IN ('resolvido','fechado')"));
$stmt->execute($params);
$resolvidos = (int) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT status, COUNT(*) AS total
                      FROM hesk_chamados c
                      " . ($baseWhere ? ($baseWhereSql . " AND c.status NOT IN ('resolvido','fechado')") : "WHERE c.status NOT IN ('resolvido','fechado')") . "
                      GROUP BY status
                      ORDER BY total DESC");
$stmt->execute($params);
$porStatusAbertos = $stmt->fetchAll();

$porMes = $db->query("SELECT DATE_FORMAT(criado_em, '%Y-%m') AS periodo, COUNT(*) AS total
                      FROM hesk_chamados
                      GROUP BY DATE_FORMAT(criado_em, '%Y-%m')
                      ORDER BY periodo ASC")->fetchAll();

$stmt = $db->prepare("SELECT COALESCE(cat.nome, 'Sem categoria') AS nome, COUNT(*) AS total
                      FROM hesk_chamados c
                      LEFT JOIN hesk_categorias cat ON cat.id = c.categoria_id
                      $baseWhereSql
                      GROUP BY COALESCE(cat.nome, 'Sem categoria')
                      ORDER BY total DESC");
$stmt->execute($params);
$porCategoria = $stmt->fetchAll();

$stmt = $db->prepare("SELECT COALESCE(u.nome, 'Não atribuído') AS nome, COUNT(*) AS total
                      FROM hesk_chamados c
                      LEFT JOIN usuarios u ON u.id = c.tecnico_id
                      " . ($baseWhere ? ($baseWhereSql . " AND c.status IN ('resolvido','fechado')") : "WHERE c.status IN ('resolvido','fechado')") . "
                      GROUP BY COALESCE(u.nome, 'Não atribuído')
                      ORDER BY total DESC");
$stmt->execute($params);
$rankingAnalistas = $stmt->fetchAll();

$stmt = $db->prepare("SELECT AVG(metricas.tempo_primeira_resposta_min) AS media,
                             AVG(metricas.tempo_resolucao_min) AS media_resolucao,
                             AVG(metricas.total_interacoes) AS media_interacoes,
                             SUM(CASE WHEN metricas.status IN ('resolvido','fechado') THEN 1 ELSE 0 END) AS concluidos,
                             SUM(CASE WHEN metricas.status IN ('resolvido','fechado') AND chamados_sla = 'no_prazo' THEN 1 ELSE 0 END) AS no_prazo
                      FROM (
                          SELECT c.status,
                                 COALESCE(msg.total_interacoes, 0) AS total_interacoes,
                                 TIMESTAMPDIFF(MINUTE, c.criado_em, first_reply.primeira_resposta_em) AS tempo_primeira_resposta_min,
                                 CASE
                                     WHEN c.status IN ('resolvido','fechado') THEN TIMESTAMPDIFF(MINUTE, c.criado_em, COALESCE(c.fechado_em, c.atualizado_em))
                                     ELSE NULL
                                 END AS tempo_resolucao_min,
                                 CASE
                                     WHEN cat.sla_horas IS NULL OR cat.sla_horas = 0 THEN 'sem_sla'
                                     WHEN c.status IN ('resolvido','fechado') AND COALESCE(c.fechado_em, c.atualizado_em) <= DATE_ADD(c.criado_em, INTERVAL cat.sla_horas HOUR) THEN 'no_prazo'
                                     WHEN c.status IN ('resolvido','fechado') THEN 'atrasado'
                                     ELSE 'em_aberto'
                                 END AS chamados_sla
                          FROM hesk_chamados c
                          LEFT JOIN hesk_categorias cat ON cat.id = c.categoria_id
                          LEFT JOIN (
                              SELECT chamado_id, COUNT(*) AS total_interacoes
                              FROM hesk_mensagens
                              GROUP BY chamado_id
                          ) msg ON msg.chamado_id = c.id
                          LEFT JOIN (
                              SELECT chamado_id, MIN(criado_em) AS primeira_resposta_em
                              FROM hesk_mensagens
                              WHERE origem = 'interno' AND privado = 0
                              GROUP BY chamado_id
                          ) first_reply ON first_reply.chamado_id = c.id
                          $baseWhereSql
                      ) metricas");
$stmt->execute($params);
$metricasResumo = $stmt->fetch();

$primeiraRespostaMin = (float) ($metricasResumo['media'] ?? 0);
$solucaoMin = (float) ($metricasResumo['media_resolucao'] ?? 0);
$mediaInteracoes = (float) ($metricasResumo['media_interacoes'] ?? 0);
$concluidosPeriodo = (int) ($metricasResumo['concluidos'] ?? 0);
$noPrazoPeriodo = (int) ($metricasResumo['no_prazo'] ?? 0);
$pctNoPrazo = $concluidosPeriodo > 0 ? round(($noPrazoPeriodo / $concluidosPeriodo) * 100) : 0;

$stmt = $db->prepare($metricasSql . ' ORDER BY c.atualizado_em DESC LIMIT 12');
$stmt->execute($params);
$ultimos = $stmt->fetchAll();

$slaCounts = array('no_prazo' => 0, 'atrasado' => 0, 'sem_sla' => 0);
foreach ($ultimos as $row) {
    if (!in_array($row['status'], array('resolvido', 'fechado'), true)) {
        continue;
    }
    $slaCounts[chamados_sla_status($row)]++;
}

$prioridades = chamados_prioridade_labels();
include __DIR__ . '/../includes/header.php';
?>
<section class="page-head chamados-hero chamados-hero-report">
  <div class="page-head-copy">
    
    <h2>Relatórios de chamados</h2>
    <p>Resumo de volume, SLA, produtividade da equipe e métricas por atendimento.</p>
  </div>
  <div class="page-head-actions relatorios-actions" style="gap:8px;flex-wrap:wrap">
    <a class="btn btn-ghost btn-sm" href="chamados_index.php">Fila</a>
    <a class="btn btn-ghost btn-sm" href="chamados_categorias.php">Categorias e SLA</a>
    <a class="btn btn-ghost btn-sm" href="chamados_relatorios.php?<?= http_build_query(array_filter(array('mes' => $mesSelecionado))) ?>&export=csv">Exportar resumo</a>
    <a class="btn btn-primary btn-sm" href="chamados_relatorios.php?<?= http_build_query(array_filter(array('mes' => $mesSelecionado))) ?>&export=atendimentos">Exportar por atendimento</a>
  </div>
</section>

<div class="card chamados-board" style="margin-bottom:16px">
  <form method="get" class="filters-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;align-items:end">
    <div>
      <label class="form-label">Mês de referência</label>
      <select name="mes" class="form-control">
        <option value="">Todos os meses</option>
        <?php foreach ($mesesDisponiveis as $mesRow): ?>
          <option value="<?= e($mesRow['periodo']) ?>" <?= $mesSelecionado === $mesRow['periodo'] ? 'selected' : '' ?>><?= e($mesRow['periodo']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <button type="submit" class="btn btn-primary">Aplicar filtro</button>
      <a class="btn btn-ghost" href="chamados_relatorios.php">Limpar</a>
    </div>
  </form>
</div>

<div class="stats-grid chamados-stats compact-stats" style="margin-bottom:16px">
  <article class="stat-card chamados-stat chamados-stat-closed"><div class="sc-label">Total do período</div><div class="sc-value"><?= (int) $totalPeriodo ?></div><div class="sc-foot">Chamados registrados</div><div class="sc-bar"></div></article>
  <article class="stat-card chamados-stat"><div class="sc-label">SLA no prazo</div><div class="sc-value"><?= (int) $pctNoPrazo ?>%</div><div class="sc-foot"><?= (int) $noPrazoPeriodo ?> de <?= (int) $concluidosPeriodo ?> concluídos</div><div class="sc-bar"></div></article>
  <article class="stat-card chamados-stat chamados-stat-open"><div class="sc-label">Primeira resposta</div><div class="sc-value"><?= e(fmt_minutes($primeiraRespostaMin)) ?></div><div class="sc-foot">Tempo médio até a equipe responder</div><div class="sc-bar"></div></article>
  <article class="stat-card chamados-stat chamados-stat-today"><div class="sc-label">Tempo de resolução</div><div class="sc-value"><?= e(fmt_minutes($solucaoMin)) ?></div><div class="sc-foot">Tempo médio do ticket até solução</div><div class="sc-bar"></div></article>
</div>



<div class="report-grid-2 compact-report-grid report-grid-essential">
  <div class="card report-card">
    <div class="stitle">Tickets abertos x resolvidos</div>
    <p class="report-sub">Distribuição geral da operação no período.</p>
    <div class="report-flex">
      <div class="donut" style="<?= chart_donut($abertos, $resolvidos, '#a31d1e', '#16a34a') ?>">
        <div class="donut-hole"><strong><?= $abertos + $resolvidos ?></strong><span>Total</span></div>
      </div>
      <div class="legend-list">
        <div class="legend-item"><span class="dot red"></span><span>Abertos</span><strong><?= $abertos ?></strong></div>
        <div class="legend-item"><span class="dot green"></span><span>Resolvidos</span><strong><?= $resolvidos ?></strong></div>
      </div>
    </div>
  </div>

  <div class="card report-card">
    <div class="stitle">SLA do período</div>
    <p class="report-sub">Consolidado por prazo das categorias.</p>
    <div class="report-flex">
      <div class="donut" style="background:conic-gradient(#16a34a 0deg <?= round(($slaCounts['no_prazo']/max(1,array_sum($slaCounts)))*360,2) ?>deg, #d97706 <?= round(($slaCounts['no_prazo']/max(1,array_sum($slaCounts)))*360,2) ?>deg <?= round((($slaCounts['no_prazo']+$slaCounts['atrasado'])/max(1,array_sum($slaCounts)))*360,2) ?>deg, #6b7280 <?= round((($slaCounts['no_prazo']+$slaCounts['atrasado'])/max(1,array_sum($slaCounts)))*360,2) ?>deg 360deg);">
        <div class="donut-hole"><strong><?= array_sum($slaCounts) ?></strong><span>Tickets</span></div>
      </div>
      <div class="legend-list">
        <div class="legend-item"><span class="dot green"></span><span>No prazo</span><strong><?= $slaCounts['no_prazo'] ?></strong></div>
        <div class="legend-item"><span class="dot orange"></span><span>Atrasado</span><strong><?= $slaCounts['atrasado'] ?></strong></div>
        <div class="legend-item"><span class="dot gray"></span><span>Sem SLA</span><strong><?= $slaCounts['sem_sla'] ?></strong></div>
      </div>
    </div>
  </div>

</div>

<div class="card chamados-board" style="margin-top:18px">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
    <div>
      <div class="stitle">Últimos chamados do período</div>
      <div class="report-sub">Visão rápida dos chamados mais recentes. O detalhamento completo fica na exportação.</div>
    </div>
    <div class="soft-pill"><?= $mesSelecionado !== '' ? 'Filtro: ' . e($mesSelecionado) : 'Todos os meses' ?></div>
  </div>
  <div class="table-wrap chamados-table-wrap" style="margin-top:12px">
    <table class="chamados-table">
      <thead>
<tr>
  <th>Protocolo</th>
  <th>Assunto</th>
  <th>Categoria</th>
  <th>Status</th>
  <th>Técnico</th>
  <th>1ª resposta</th>
  <th>Resolução</th>
  <th>Interações</th>
  <th>SLA</th>
  <th>SLA estourado</th>
  <th></th>
</tr>
</thead>
      <tbody>
        <?php if (!$ultimos): ?>
          <tr><td colspan="8" style="text-align:center;color:var(--t3)">Nenhum chamado encontrado.</td></tr>
        <?php endif; ?>
        <?php foreach ($ultimos as $row): ?>
          <?php $slaStatus = chamados_sla_status($row); ?>
          <?php $slaExcedidoMin = sla_excedido_minutos($row); ?>
          <tr>
            <td><a class="table-link" href="chamados_visualizar.php?id=<?= (int)$row['id'] ?>"><?= e($row['protocolo']) ?></a></td>
            <td><?= e($row['assunto']) ?></td>
            <td><span class="soft-pill"><?= e($row['categoria_nome']) ?></span></td>
            <td><span class="badge <?= chamados_status_badge_class($row['status']) ?>"><?= e($statuses[$row['status']] ?? $row['status']) ?></span></td>
            <td><span class="soft-pill soft-pill-muted"><?= e($row['tecnico_nome']) ?></span></td>
            <td><div class="cell-date"><strong><?= e(fmt_minutes($row['tempo_primeira_resposta_min'])) ?></strong><span><?= e(csv_value($row['primeira_resposta_em'])) ?></span></div></td>
            <td><div class="cell-date"><strong><?= e(fmt_minutes($row['tempo_resolucao_min'])) ?></strong><span><?= e(csv_value($row['fechado_em'] ?: ($row['status'] === 'resolvido' ? $row['atualizado_em'] : null))) ?></span></div></td>
            <td><span class="soft-pill soft-pill-muted"><?= (int) $row['total_interacoes'] ?></span></td>
            <td><span class="badge <?= chamados_sla_badge_class($slaStatus) ?>"><?= e(chamados_sla_label($slaStatus)) ?></span></td>
            <td><span class="soft-pill soft-pill-muted"><?= e(fmt_minutes($slaExcedidoMin)) ?></span></td>
            <td><a class="btn btn-ghost btn-sm" href="chamados_visualizar.php?id=<?= (int)$row['id'] ?>">Abrir</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
