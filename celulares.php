<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
requireAccess('celulares');
require_once __DIR__ . '/includes/setor_options.php';

$pageTitle = 'Celulares';
$db = getDB();
$canWrite = can_write_module('celulares');

$STATUS_LIST = ['Em uso','Disponível','Manutenção','Desativado'];
$MDM_LIST = ['1' => 'Ativado', '0' => 'Desativado'];
$OPERADORAS = array_values(array_filter($db->query("SELECT DISTINCT operadora FROM celulares WHERE operadora IS NOT NULL AND operadora <> '' ORDER BY operadora")->fetchAll(PDO::FETCH_COLUMN)));
$TIPOS = array_values(array_filter($db->query("SELECT DISTINCT tipo FROM celulares WHERE tipo IS NOT NULL AND tipo <> '' ORDER BY tipo")->fetchAll(PDO::FETCH_COLUMN)));

$busca = trim((string) get_query('q', ''));
$filterBy = trim((string) get_query('filter_by', ''));
$filterValue = trim((string) get_query('filter_value', ''));
$page = query_int('page', 1, 1);
$perPage = 20;

$where = ['1=1'];
$params = [];
if ($busca !== '') {
    $where[] = '('
        . 'c.marca LIKE :q_marca OR '
        . 'c.modelo LIKE :q_modelo OR '
        . 'c.usuario_responsavel LIKE :q_usuario OR '
        . 'c.imei LIKE :q_imei OR '
        . 'c.numero_chip LIKE :q_chip OR '
        . 'c.setor LIKE :q_setor_busca OR '
        . 'c.numero_serie LIKE :q_serie OR '
        . 'c.operadora LIKE :q_operadora_busca'
        . ')';
    $searchLike = '%' . $busca . '%';
    $params[':q_marca'] = $searchLike;
    $params[':q_modelo'] = $searchLike;
    $params[':q_usuario'] = $searchLike;
    $params[':q_imei'] = $searchLike;
    $params[':q_chip'] = $searchLike;
    $params[':q_setor_busca'] = $searchLike;
    $params[':q_serie'] = $searchLike;
    $params[':q_operadora_busca'] = $searchLike;
}
if ($filterBy === 'status' && in_array($filterValue, $STATUS_LIST, true)) {
    $where[] = 'c.status = :status';
    $params[':status'] = $filterValue;
}
if ($filterBy === 'setor' && in_array($filterValue, $SETORES, true)) {
    $where[] = 'c.setor = :setor';
    $params[':setor'] = $filterValue;
}
if ($filterBy === 'operadora' && in_array($filterValue, $OPERADORAS, true)) {
    $where[] = 'c.operadora = :operadora';
    $params[':operadora'] = $filterValue;
}
if ($filterBy === 'tipo' && in_array($filterValue, $TIPOS, true)) {
    $where[] = 'c.tipo = :tipo';
    $params[':tipo'] = $filterValue;
}
if ($filterBy === 'mdm' && array_key_exists($filterValue, $MDM_LIST)) {
    $where[] = 'c.mdm_ativo = :mdm';
    $params[':mdm'] = (int) $filterValue;
}

$countStmt = $db->prepare('SELECT COUNT(*) FROM celulares c WHERE ' . implode(' AND ', $where));
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$pagination = paginate($totalRows, $page, $perPage);

$sql = 'SELECT c.* FROM celulares c WHERE ' . implode(' AND ', $where)
     . ' ORDER BY c.data_cadastro DESC, c.id DESC LIMIT :limit OFFSET :offset';
$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

$statsCelulares = [
    'total' => $totalRows,
    'em_uso' => 0,
    'disponivel' => 0,
    'manutencao' => 0,
    'mdm_ativo' => 0,
];
foreach ($rows as $__rowCel) {
    $statusAtual = (string)($__rowCel['status'] ?? '');
    if ($statusAtual === 'Em uso') $statsCelulares['em_uso']++;
    if ($statusAtual === 'Disponível') $statsCelulares['disponivel']++;
    if ($statusAtual === 'Manutenção') $statsCelulares['manutencao']++;
    if (!empty($__rowCel['mdm_ativo'])) $statsCelulares['mdm_ativo']++;
}

$filterLabelMap = ['status' => 'Status', 'setor' => 'Setor', 'operadora' => 'Operadora', 'tipo' => 'Tipo', 'mdm' => 'MDM'];
$filterOptionsJson = json_encode([
    'status'    => ['type' => 'select', 'placeholder' => 'Selecione o status', 'options' => array_map(function ($v) { return array('value' => $v, 'label' => $v); }, $STATUS_LIST)],
    'setor'     => ['type' => 'select', 'placeholder' => 'Selecione o setor', 'options' => array_map(function ($v) { return array('value' => $v, 'label' => $v); }, $SETORES)],
    'operadora' => ['type' => 'select', 'placeholder' => 'Selecione a operadora', 'options' => array_map(function ($v) { return array('value' => $v, 'label' => $v); }, $OPERADORAS)],
    'tipo'      => ['type' => 'select', 'placeholder' => 'Selecione o tipo', 'options' => array_map(function ($v) { return array('value' => $v, 'label' => $v); }, $TIPOS)],
    'mdm'       => ['type' => 'select', 'placeholder' => 'Selecione o status do MDM', 'options' => [ ['value' => '1', 'label' => 'Ativado'], ['value' => '0', 'label' => 'Desativado'] ]],
], JSON_UNESCAPED_UNICODE);


if (get_query('export') === 'excel') {
    $exportStmt = $db->prepare('SELECT c.* FROM celulares c WHERE ' . implode(' AND ', $where) . ' ORDER BY c.data_cadastro DESC, c.id DESC');
    $exportStmt->execute($params);
    $exportRowsDb = $exportStmt->fetchAll() ?: [];
    $exportHeaders = !empty($exportRowsDb) ? array_keys($exportRowsDb[0]) : ['mensagem'];
    $exportRows = [];
    if ($exportRowsDb) {
        foreach ($exportRowsDb as $item) {
            $line = [];
            foreach ($exportHeaders as $header) {
                $line[] = $item[$header] ?? '';
            }
            $exportRows[] = $line;
        }
    } else {
        $exportRows[] = ['Nenhum registro encontrado'];
    }
    export_excel_xml('celulares.xls', $exportHeaders, $exportRows);
}

include 'includes/header.php';
echo render_flash();
?>
<div class="page-head"><div class="page-head-copy"><h2>Inventário de celulares</h2><p></p></div><?php if ($canWrite): ?><div class="page-head-actions"><a href="cadastrar.php?tipo=celular" class="btn btn-primary"><?= icon('plus') ?> Novo celular</a></div><?php endif; ?></div>

<div class="stats-grid chamados-stats inventory-stats">
    <article class="stat-card chamados-stat chamados-stat-total">
        <div class="sc-label">Total de celulares</div>
        <div class="sc-value"><?= number_format((float) $statsCelulares['total'], 0, ',', '.') ?></div>
        <div class="sc-foot">Celulares listados</div>
        <div class="sc-bar"></div>
    </article>
    <article class="stat-card chamados-stat chamados-stat-success">
        <div class="sc-label">Em uso</div>
        <div class="sc-value"><?= number_format((float) $statsCelulares['em_uso'], 0, ',', '.') ?></div>
        <div class="sc-foot">Status operacional</div>
        <div class="sc-bar"></div>
    </article>
    <article class="stat-card chamados-stat chamados-stat-open">
        <div class="sc-label">Disponíveis</div>
        <div class="sc-value"><?= number_format((float) $statsCelulares['disponivel'], 0, ',', '.') ?></div>
        <div class="sc-foot">Prontos para uso</div>
        <div class="sc-bar"></div>
    </article>
    <article class="stat-card chamados-stat chamados-stat-neutral">
        <div class="sc-label">MDM ativo</div>
        <div class="sc-value"><?= number_format((float) $statsCelulares['mdm_ativo'], 0, ',', '.') ?></div>
        <div class="sc-foot">Gerenciamento habilitado</div>
        <div class="sc-bar"></div>
    </article>
</div>

<div class="card inventory-card" style="padding:0;overflow:hidden">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--bdr);gap:12px;flex-wrap:wrap">
        <div class="stitle" style="margin:0;flex:1"><?= icon('phone') ?> Celulares <span style="font-size:12px;color:var(--t3);font-weight:400;margin-left:6px"><?= $totalRows ?> registro<?= $totalRows !== 1 ? 's' : '' ?></span></div>
</div>

    <form method="get" class="toolbar-shell" id="celulares-filter-form">
        <div class="toolbar-grow">
            <input type="text" name="q" placeholder="Buscar por responsável, modelo, IMEI, chip ou operadora..." value="<?= e($busca) ?>">
        </div>
        <div class="toolbar-field">
            <select name="filter_by" id="celulares-filter-by">
                <option value="">Filtro complementar</option>
                <option value="status" <?= $filterBy==='status'?'selected':'' ?>>Status</option>
                <option value="setor" <?= $filterBy==='setor'?'selected':'' ?>>Setor</option>
                <option value="operadora" <?= $filterBy==='operadora'?'selected':'' ?>>Operadora</option>
                <option value="tipo" <?= $filterBy==='tipo'?'selected':'' ?>>Tipo</option>
                <option value="mdm" <?= $filterBy==='mdm'?'selected':'' ?>>MDM</option>
            </select>
        </div>
        <div class="toolbar-field" id="celulares-filter-value-slot"></div>
        <div class="toolbar-actions">
            <button type="submit" class="btn btn-ghost btn-sm"><?= icon('filter') ?> Filtrar</button>
            <a href="celulares.php" class="btn btn-ghost btn-sm">Limpar</a>
            <a href="celulares.php<?= e(current_query(['export' => 'excel'], ['page'])) ?>" class="btn btn-sm btn-export">Exportar Excel</a>
        </div>
    </form>
    <?php if ($filterBy && $filterValue !== ''): ?>
    <div class="filter-note" style="padding:0 18px 14px">Filtro aplicado: <strong><?= e($filterLabelMap[$filterBy] ?? $filterBy) ?></strong> = <?= e($MDM_LIST[$filterValue] ?? $filterValue) ?></div>
    <?php endif; ?>

    <?php if (empty($rows)): ?>
    <div class="empty-state"><?= icon('phone') ?><p>Nenhum celular encontrado.<?php if ($canWrite): ?><br><a href="cadastrar.php?tipo=celular">Cadastrar novo</a><?php endif; ?></p></div>
    <?php else: ?>
    <div class="table-wrap inventory-table-wrap">
    <table class="inventory-table inventory-table-celulares">
        <thead><tr>
            <th style="width:52px">#</th>
            <th>Ativo</th>
            <th style="width:320px">Contexto</th>
            <th style="width:300px">Conectividade / Identificação</th>
            <th style="width:110px">MDM</th>
            <th style="width:120px">Status</th>
            <th style="width:84px">Ações</th>
        </tr></thead>
        <tbody>
        <?php $sm = ['Em uso'=>'b-green','Disponível'=>'b-neutral','Manutenção'=>'b-amber','Desativado'=>'b-gray']; foreach ($rows as $c): ?>
        <?php $displayName = trim((string) (($c['marca'] ?: '') . ' ' . ($c['modelo'] ?: ''))); ?>
        <tr>
            <td class="mono" style="color:var(--t3)"><?= (int)$c['id'] ?></td>
            <td>
                <div class="asset-premium-main">
                    <div class="asset-premium-title">
                        <span class="asset-name"><?= e($displayName ?: 'Celular sem modelo') ?></span>
                        <span class="asset-sub"><?= e($c['tipo'] ?: 'Smartphone') ?></span>
                    </div>
                    <div class="asset-premium-row">
                        <?php if ($c['numero_serie']): ?><span class="asset-inline-chip"><?= icon('search') ?>Série: <?= e($c['numero_serie']) ?></span><?php endif; ?>
                        <?php if ($c['imei']): ?><span class="asset-inline-chip"><?= icon('search') ?>IMEI: <?= e($c['imei']) ?></span><?php endif; ?>
                    </div>
                </div>
            </td>
            <td>
                <div class="asset-inline-info">
                    <span><?= icon('user') ?> <?= e($c['usuario_responsavel'] ?: 'Não vinculado') ?></span>
                    <span><?= icon('box') ?> <?= e($c['setor'] ?: '—') ?></span>
                    <span><?= icon('phone') ?> <?= e($c['operadora'] ?: '—') ?></span>
                </div>
            </td>
            <td>
                <div class="asset-spec-inline">
                    <span><?= e($c['operadora'] ?: '—') ?></span>
                    <span><?= e($c['numero_chip'] ?: '—') ?></span>
                    <span><?= e($c['imei'] ?: '—') ?></span>
                    <span><?= e($c['tipo'] ?: '—') ?></span>
                </div>
            </td>
            <td><span class="badge <?= !empty($c['mdm_ativo']) ? 'badge-mdm-on' : 'badge-mdm-off' ?>"><?= !empty($c['mdm_ativo']) ? 'Ativado' : 'Desativado' ?></span></td>
            <td><span class="badge <?= $sm[$c['status']] ?? 'b-gray' ?>"><?= e($c['status']) ?></span></td>
            <td><?php if ($canWrite): ?><div class="act-btns"><a href="editar.php?tipo=celular&id=<?= (int)$c['id'] ?>" class="btn-icon edit" title="Editar"><?= icon('edit') ?></a><form method="post" action="excluir.php" style="display:inline" onsubmit="return confirm('Excluir este celular?')"><?= csrf_input() ?><input type="hidden" name="tipo" value="celular"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><button type="submit" class="btn-icon del" title="Excluir" style="border:none;background:none;padding:0"><?= icon('trash') ?></button></form></div><?php else: ?><span class="sub">Somente leitura</span><?php endif; ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?= render_pagination($pagination) ?>
    <?php endif; ?>
</div>
<script>
(function(){
    const config = <?= $filterOptionsJson ?>;
    const select = document.getElementById('celulares-filter-by');
    const slot = document.getElementById('celulares-filter-value-slot');
    const currentValue = <?= json_encode($filterValue, JSON_UNESCAPED_UNICODE) ?>;
    function esc(value){ return String(value).replace(/"/g, '&quot;'); }
    function renderField(){
        const key = select.value;
        if (!key || !config[key]) {
            slot.innerHTML = '<input type="text" value="" placeholder="Valor do filtro" disabled>';
            return;
        }
        const item = config[key];
        let html = '<select name="filter_value">';
        html += '<option value="">' + item.placeholder + '</option>';
        item.options.forEach(function(opt){
            const selected = String(opt.value) === String(currentValue) ? ' selected' : '';
            html += '<option value="' + esc(opt.value) + '"' + selected + '>' + opt.label + '</option>';
        });
        html += '</select>';
        slot.innerHTML = html;
    }
    renderField();
    select.addEventListener('change', renderField);
})();
</script>
<?php include 'includes/footer.php'; ?>
