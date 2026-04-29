<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
guard_current_page_access();

$db = getDB();
$statuses = chamados_status_labels();
$prioridades = chamados_prioridade_labels();

$q = trim((string) get_query('q', ''));
$status = trim((string) get_query('status', 'abertos'));
$categoriaId = (int) get_query('categoria_id', 0);
$tecnicoId = (int) get_query('tecnico_id', 0);
$page = query_int('page', 1, 1);
$perPage = 15;

$where = ['1=1'];
$params = [];
if ($q !== '') {
    $where[] = '(c.protocolo LIKE :q OR c.assunto LIKE :q OR c.nome_solicitante LIKE :q OR c.email_solicitante LIKE :q)';
    $params[':q'] = '%' . $q . '%';
}
if ($status === '' || $status === 'abertos') {
    $where[] = "c.status NOT IN ('resolvido','fechado')";
} elseif ($status === 'resolvidos') {
    $where[] = "c.status IN ('resolvido','fechado')";
} elseif (isset($statuses[$status])) {
    $where[] = 'c.status = :status';
    $params[':status'] = $status;
}
if ($categoriaId > 0) {
    $where[] = 'c.categoria_id = :categoria_id';
    $params[':categoria_id'] = $categoriaId;
}
if ($tecnicoId > 0) {
    $where[] = 'c.tecnico_id = :tecnico_id';
    $params[':tecnico_id'] = $tecnicoId;
}

$countStmt = $db->prepare('SELECT COUNT(*) FROM hesk_chamados c WHERE ' . implode(' AND ', $where));
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$pagination = paginate($totalRows, $page, $perPage);

$sql = "SELECT c.*, cat.nome AS categoria_nome, cat.sla_horas, u.nome AS tecnico_nome
    FROM hesk_chamados c
    LEFT JOIN hesk_categorias cat ON cat.id = c.categoria_id
    LEFT JOIN usuarios u ON u.id = c.tecnico_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY FIELD(c.status, 'aberto','em_andamento','aguardando_cliente','resolvido','fechado'), c.atualizado_em DESC
    LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$chamados = $stmt->fetchAll();

$resumo = chamados_resumo_dashboard();
$categorias = chamados_categorias_ativas();
$tecnicos = chamados_tecnicos_opcoes();
$pageTitle = 'Chamados';
include __DIR__ . '/../includes/header.php';
echo render_flash();
?>
<section class="page-head chamados-hero">
  <div class="page-head-copy">
    
    <h2>Fila de chamados</h2>
    <p></p>
  </div>
  <div class="page-head-actions">
    <a class="btn btn-ghost btn-sm" href="../hesk/index.php" target="_blank">Abrir portal público</a>
    <a class="btn btn-primary btn-sm" href="chamados_relatorios.php">Ver relatórios</a>
  </div>
</section>

<div class="stats-grid chamados-stats">
  <article class="stat-card chamados-stat chamados-stat-total">
    <div class="sc-label">Total de chamados</div>
    <div class="sc-value"><?= number_format($resumo['total'], 0, ',', '.') ?></div>
    <div class="sc-foot">Base consolidada do portal</div>
    <div class="sc-bar"></div>
  </article>
  <article class="stat-card chamados-stat chamados-stat-open">
    <div class="sc-label">Em aberto</div>
    <div class="sc-value"><?= number_format($resumo['abertos'], 0, ',', '.') ?></div>
    <div class="sc-foot">Precisam de acompanhamento</div>
    <div class="sc-bar"></div>
  </article>
  <article class="stat-card chamados-stat chamados-stat-today">
    <div class="sc-label">Entradas hoje</div>
    <div class="sc-value"><?= number_format($resumo['hoje'], 0, ',', '.') ?></div>
    <div class="sc-foot">Chamados criados no dia</div>
    <div class="sc-bar"></div>
  </article>
  <article class="stat-card chamados-stat chamados-stat-closed">
    <div class="sc-label">Fechados no mês</div>
    <div class="sc-value"><?= number_format($resumo['fechadosMes'], 0, ',', '.') ?></div>
    <div class="sc-foot">Entregas concluídas no período</div>
    <div class="sc-bar"></div>
  </article>
</div>



<div class="card chamados-board" style="padding:0;overflow:hidden">

  <form method="get" class="toolbar-shell chamados-toolbar">
    <div class="toolbar-grow"><input type="text" name="q" placeholder="Buscar por protocolo, assunto, solicitante ou e-mail" value="<?= e($q) ?>"></div>
    <div class="toolbar-field"><select name="status"><option value="abertos" <?= $status==='abertos'?'selected':'' ?>>Somente abertos</option><option value="resolvidos" <?= $status==='resolvidos'?'selected':'' ?>>Somente resolvidos</option><option value="" <?= $status===''?'selected':'' ?>>Todos status</option><?php foreach ($statuses as $key => $label): ?><option value="<?= e($key) ?>" <?= $status===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
    <div class="toolbar-field"><select name="categoria_id"><option value="0">Todas categorias</option><?php foreach ($categorias as $cat): ?><option value="<?= (int)$cat['id'] ?>" <?= $categoriaId===(int)$cat['id']?'selected':'' ?>><?= e($cat['nome']) ?></option><?php endforeach; ?></select></div>
    <div class="toolbar-field"><select name="tecnico_id"><option value="0">Todos técnicos</option><?php foreach ($tecnicos as $tecnico): ?><option value="<?= (int)$tecnico['id'] ?>" <?= $tecnicoId===(int)$tecnico['id']?'selected':'' ?>><?= e($tecnico['nome']) ?></option><?php endforeach; ?></select></div>
    <div class="toolbar-actions"><button type="submit" class="btn btn-primary btn-sm">Filtrar</button><a href="chamados_index.php" class="btn btn-ghost btn-sm">Limpar</a></div>
  </form>
  <div class="table-wrap chamados-table-wrap">

    <table class="chamados-table">
      
      <thead><tr><th>Protocolo</th><th>Assunto</th><th>Categoria</th><th>SLA</th><th>Status</th><th>Técnico</th><th>Atualização</th><th></th></tr></thead>
      <tbody>
      <?php if (!$chamados): ?>
        <tr><td colspan="8" style="text-align:center;color:var(--t3)">Nenhum chamado encontrado.</td></tr>
      <?php endif; ?>
      <?php foreach ($chamados as $c): ?>
        <?php $slaStatus = chamados_sla_status($c); ?>
        <tr>
          <td><a class="table-link" href="chamados_visualizar.php?id=<?= (int)$c['id'] ?>"><strong><?= e($c['protocolo']) ?></strong></a></td>
          <td><a class="table-link" href="chamados_visualizar.php?id=<?= (int)$c['id'] ?>"><?= e($c['assunto']) ?></a></td>
          <td><span class="soft-pill"><?= e($c['categoria_nome'] ?: 'Sem categoria') ?></span></td>
          <td><span class="badge <?= chamados_sla_badge_class($slaStatus) ?>"><?= e(chamados_sla_label($slaStatus)) ?></span></td>
          <td><span class="badge <?= chamados_status_badge_class($c['status']) ?>"><?= e($statuses[$c['status']] ?? $c['status']) ?></span></td>
          <td><span class="soft-pill soft-pill-muted"><?= e($c['tecnico_nome'] ?: 'Não atribuído') ?></span></td>
          <td><div class="cell-date"><strong><?= date('d/m/Y', strtotime($c['atualizado_em'])) ?></strong><span><?= date('H:i', strtotime($c['atualizado_em'])) ?></span></div></td>
          <td><a class="btn btn-ghost btn-sm" href="chamados_visualizar.php?id=<?= (int)$c['id'] ?>">Abrir</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?= render_pagination($pagination) ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
