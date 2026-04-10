<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireAccess('distribuicao');

$db = getDB();
$busca = trim((string) get_query('q', ''));
$clienteId = query_int('cliente_id', 0, 0);
$status = trim((string) get_query('status', ''));
$monitoramento = trim((string) get_query('monitoramento', ''));
$page = query_int('page', 1, 1);
$perPage = 20;

$clientesDisponiveis = distribuicao_accessible_clients('visualizar');
$allowedIds = distribuicao_allowed_client_ids('visualizar');
if ($clienteId > 0) {
    distribuicao_require_cliente_access($clienteId, 'visualizar');
}

$where = ['1=1'];
$params = [];

if (($_SESSION['perfil'] ?? '') !== 'admin') {
    if (empty($allowedIds)) {
        $where[] = '1=0';
    } else {
        $where[] = 'e.cliente_id IN (' . implode(',', array_map('intval', $allowedIds)) . ')';
    }
}
if ($clienteId > 0) {
    $where[] = 'e.cliente_id = :cliente_id';
    $params[':cliente_id'] = $clienteId;
}
if ($status !== '') {
    $where[] = 'e.status_operacional = :status';
    $params[':status'] = $status;
}
if ($monitoramento !== '') {
    $where[] = 'e.monitoramento = :monitoramento';
    $params[':monitoramento'] = $monitoramento;
}
if ($busca !== '') {
    $where[] = '(c.nome LIKE :q_cliente OR e.serie LIKE :q_serie OR e.modelo LIKE :q_modelo OR e.unidade LIKE :q_unidade OR e.setor LIKE :q_setor OR e.regional LIKE :q_regional OR e.nome_impressora LIKE :q_impressora)';
    $like = '%' . $busca . '%';
    $params[':q_cliente'] = $like;
    $params[':q_serie'] = $like;
    $params[':q_modelo'] = $like;
    $params[':q_unidade'] = $like;
    $params[':q_setor'] = $like;
    $params[':q_regional'] = $like;
    $params[':q_impressora'] = $like;
}

$countStmt = $db->prepare('SELECT COUNT(*) FROM distribuicao_equipamentos e INNER JOIN distribuicao_clientes c ON c.id = e.cliente_id WHERE ' . implode(' AND ', $where));
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$pagination = paginate($totalRows, $page, $perPage);

$sql = 'SELECT e.*, c.nome AS cliente_nome FROM distribuicao_equipamentos e INNER JOIN distribuicao_clientes c ON c.id = e.cliente_id WHERE ' . implode(' AND ', $where) . ' ORDER BY c.nome ASC, e.unidade ASC, e.setor ASC, e.id DESC LIMIT :limit OFFSET :offset';
$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$equipamentos = $stmt->fetchAll() ?: [];

$statsStmt = $db->prepare('SELECT COUNT(*) AS total, SUM(CASE WHEN e.status_operacional = "Ativa" THEN 1 ELSE 0 END) AS ativas, SUM(CASE WHEN e.status_operacional = "Desinstalada" THEN 1 ELSE 0 END) AS desinstaladas, SUM(CASE WHEN e.monitoramento = "Offline" THEN 1 ELSE 0 END) AS offline FROM distribuicao_equipamentos e INNER JOIN distribuicao_clientes c ON c.id = e.cliente_id WHERE ' . implode(' AND ', $where));
$statsStmt->execute($params);
$stats = $statsStmt->fetch() ?: ['total'=>0,'ativas'=>0,'desinstaladas'=>0,'offline'=>0];
$trocaSql = 'SELECT COUNT(*) FROM distribuicao_movimentacoes m WHERE m.tipo_movimentacao = "troca_tecnica" AND DATE_FORMAT(m.data_movimentacao, "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")';
if (($_SESSION['perfil'] ?? '') !== 'admin' && !empty($allowedIds)) {
    $trocaSql .= ' AND m.cliente_id IN (' . implode(',', array_map('intval', $allowedIds)) . ')';
}
$trocasMes = (int) $db->query($trocaSql)->fetchColumn();

include 'includes/header.php';
echo render_flash();
?>
<div class="page-head">
    <div class="page-head-copy"><h2>Distribuição de impressoras</h2><p>Controle multi-cliente do parque instalado, monitoramento, status operacional e trocas técnicas.</p></div>
    <div class="page-head-actions">
        <?php if (!empty($clientesDisponiveis) && (($_SESSION['perfil'] ?? '') === 'admin' || !empty(distribuicao_allowed_client_ids('cadastrar')))): ?><a href="distribuicao_cadastrar.php" class="btn btn-primary"><?= icon('plus') ?> Novo equipamento</a><?php endif; ?>
        <a href="distribuicao_importar_monitoramento.php" class="btn btn-ghost"><?= icon('filter') ?> Importar monitoramento</a>
        <a href="distribuicao_movimentacoes.php" class="btn btn-ghost"><?= icon('clock') ?> Movimentações</a>
        <?php if (($_SESSION['perfil'] ?? '') === 'admin'): ?><a href="distribuicao_clientes.php" class="btn btn-ghost"><?= icon('users') ?> Clientes</a><?php endif; ?>
    </div>
</div>
<div class="kpi-grid" style="margin-bottom:18px"><div class="stat-card"><div class="sc-label">Total visível</div><div class="sc-value"><?= (int) $stats['total'] ?></div><div class="sc-icon"><?= icon('printer') ?></div><div class="sc-bar"></div></div><div class="stat-card"><div class="sc-label">Ativas</div><div class="sc-value"><?= (int) $stats['ativas'] ?></div><div class="sc-icon"><?= icon('check') ?></div><div class="sc-bar"></div></div><div class="stat-card"><div class="sc-label">Offline</div><div class="sc-value"><?= (int) $stats['offline'] ?></div><div class="sc-icon"><?= icon('off') ?></div><div class="sc-bar"></div></div><div class="stat-card"><div class="sc-label">Trocas no mês</div><div class="sc-value"><?= $trocasMes ?></div><div class="sc-icon"><?= icon('swap') ?></div><div class="sc-bar"></div></div></div>
<div class="card" style="padding:0;overflow:hidden">
    <div style="padding:14px 18px;border-bottom:1px solid var(--line)"><div class="stitle" style="margin:0"><?= icon('printer') ?> Parque instalado</div></div>
    <form method="get" class="toolbar-shell">
        <div class="toolbar-grow"><input type="text" name="q" placeholder="Buscar por cliente, série, modelo, unidade, setor, regional ou impressora" value="<?= e($busca) ?>"></div>
        <div class="toolbar-field"><select name="cliente_id"><option value="0">Todos os clientes</option><?php foreach ($clientesDisponiveis as $cliente): ?><option value="<?= (int) $cliente['id'] ?>" <?= $clienteId === (int) $cliente['id'] ? 'selected' : '' ?>><?= e($cliente['nome']) ?></option><?php endforeach; ?></select></div>
        <div class="toolbar-field"><select name="status"><option value="">Status operacional</option><?php foreach (distribuicao_status_options() as $opt): ?><option value="<?= e($opt) ?>" <?= $status === $opt ? 'selected' : '' ?>><?= e($opt) ?></option><?php endforeach; ?></select></div>
        <div class="toolbar-field"><select name="monitoramento"><option value="">Monitoramento</option><?php foreach (distribuicao_monitoramento_options() as $opt): ?><option value="<?= e($opt) ?>" <?= $monitoramento === $opt ? 'selected' : '' ?>><?= e($opt) ?></option><?php endforeach; ?></select></div>
        <div class="toolbar-actions"><button type="submit" class="btn btn-ghost btn-sm"><?= icon('filter') ?> Filtrar</button><a href="distribuicao_index.php" class="btn btn-ghost btn-sm">Limpar</a></div>
    </form>
    <div class="table-wrap"><table><thead><tr><th>Cliente</th><th>Local</th><th>Equipamento</th><th>Monitoramento</th><th>Status</th><th>Instalação</th><th style="width:170px">Ações</th></tr></thead><tbody>
    <?php foreach ($equipamentos as $equip): ?><tr><td><strong><?= e($equip['cliente_nome']) ?></strong><div class="sub"><?= e($equip['regional'] ?: 'Sem regional') ?></div></td><td><div><strong><?= e($equip['unidade'] ?: 'Sem unidade') ?></strong></div><div class="sub"><?= e($equip['setor'] ?: 'Sem setor') ?><?= $equip['municipio'] ? ' · '.e($equip['municipio']) : '' ?></div></td><td><div><strong><?= e($equip['modelo'] ?: 'Sem modelo') ?></strong></div><div class="sub">Série: <?= e($equip['serie'] ?: '—') ?><?= $equip['nome_impressora'] ? ' · '.e($equip['nome_impressora']) : '' ?></div></td><td><span class="badge <?= $equip['monitoramento'] === 'Online' ? 'b-green' : ($equip['monitoramento'] === 'Offline' ? 'b-red' : 'b-neutral') ?>"><?= e($equip['monitoramento']) ?></span><?php if (!empty($equip['ultima_leitura_em'])): ?><div class="sub">Últ. leitura: <?= date('d/m/Y', strtotime($equip['ultima_leitura_em'])) ?></div><?php endif; ?></td><td><span class="badge <?= $equip['status_operacional'] === 'Ativa' ? 'b-green' : ($equip['status_operacional'] === 'Desinstalada' ? 'b-red' : 'b-neutral') ?>"><?= e($equip['status_operacional']) ?></span></td><td class="mono"><?= $equip['data_instalacao'] ? date('d/m/Y', strtotime($equip['data_instalacao'])) : '—' ?></td><td><div class="act-btns"><?php if (distribuicao_can_access_cliente((int) $equip['cliente_id'], 'editar')): ?><a href="distribuicao_editar.php?id=<?= (int) $equip['id'] ?>" class="btn-icon edit"><?= icon('edit') ?></a><?php endif; ?><?php if (distribuicao_can_access_cliente((int) $equip['cliente_id'], 'movimentar')): ?><a href="distribuicao_troca_tecnica.php?id=<?= (int) $equip['id'] ?>" class="btn-icon"><?= icon('swap') ?></a><?php endif; ?><a href="distribuicao_movimentacoes.php?equipamento_id=<?= (int) $equip['id'] ?>" class="btn-icon"><?= icon('clock') ?></a></div></td></tr><?php endforeach; ?>
    <?php if (empty($equipamentos)): ?><tr><td colspan="7" style="padding:24px;text-align:center;color:var(--muted)">Nenhum equipamento encontrado para os filtros atuais.</td></tr><?php endif; ?>
    </tbody></table></div>
    <?= render_pagination($pagination) ?>
</div>
