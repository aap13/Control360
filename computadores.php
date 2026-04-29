<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
requireAccess('computadores');
require_once __DIR__ . '/includes/setor_options.php';

$pageTitle = 'Computadores';
$db = getDB();
$canWrite = can_write_module('computadores');

$LOCAIS = ['CSF SLZ', 'CSF FOR'];
$STATUS_LIST = ['Em uso','Disponível','Manutenção','Desativado'];

$busca = trim((string) get_query('q', ''));
$filterBy = trim((string) get_query('filter_by', ''));
$filterValue = trim((string) get_query('filter_value', ''));
$page = query_int('page', 1, 1);
$perPage = 20;

$where = ['1=1'];
$params = [];
if ($busca !== '') {
    $where[] = '('
        . 'c.nome_dispositivo LIKE :q_nome OR '
        . 'c.marca LIKE :q_marca OR '
        . 'c.modelo LIKE :q_modelo OR '
        . 'c.usuario_responsavel LIKE :q_usuario OR '
        . 'c.numero_serie LIKE :q_serie OR '
        . 'c.setor LIKE :q_setor_busca OR '
        . 'c.processador LIKE :q_processador OR '
        . 'c.armazenamento LIKE :q_armazenamento OR '
        . 'c.sistema_operacional LIKE :q_so'
        . ')';
    $searchLike = '%' . $busca . '%';
    $params[':q_nome'] = $searchLike;
    $params[':q_marca'] = $searchLike;
    $params[':q_modelo'] = $searchLike;
    $params[':q_usuario'] = $searchLike;
    $params[':q_serie'] = $searchLike;
    $params[':q_setor_busca'] = $searchLike;
    $params[':q_processador'] = $searchLike;
    $params[':q_armazenamento'] = $searchLike;
    $params[':q_so'] = $searchLike;
}
if ($filterBy === 'status' && in_array($filterValue, $STATUS_LIST, true)) {
    $where[] = 'c.status = :status';
    $params[':status'] = $filterValue;
}
if ($filterBy === 'setor' && in_array($filterValue, $SETORES, true)) {
    $where[] = 'c.setor = :setor';
    $params[':setor'] = $filterValue;
}
if ($filterBy === 'local' && in_array($filterValue, $LOCAIS, true)) {
    $where[] = 'c.localizacao = :local';
    $params[':local'] = $filterValue;
}

$countStmt = $db->prepare('SELECT COUNT(*) FROM computadores c WHERE ' . implode(' AND ', $where));
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$pagination = paginate($totalRows, $page, $perPage);

$sql = 'SELECT c.* FROM computadores c WHERE ' . implode(' AND ', $where)
     . ' ORDER BY c.data_cadastro DESC, c.id DESC LIMIT :limit OFFSET :offset';
$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

$statsComputadores = [
    'total' => $totalRows,
    'em_uso' => 0,
    'disponivel' => 0,
    'manutencao' => 0,
    'unidades' => 0,
];
$__locaisResumo = [];
foreach ($rows as $__rowComp) {
    $statusAtual = (string)($__rowComp['status'] ?? '');
    if ($statusAtual === 'Em uso') $statsComputadores['em_uso']++;
    if ($statusAtual === 'Disponível') $statsComputadores['disponivel']++;
    if ($statusAtual === 'Manutenção') $statsComputadores['manutencao']++;
    if (!empty($__rowComp['localizacao'])) $__locaisResumo[(string)$__rowComp['localizacao']] = true;
}
$statsComputadores['unidades'] = count($__locaisResumo);

$filterLabelMap = ['status' => 'Status', 'setor' => 'Setor', 'local' => 'Unidade'];
$filterOptionsJson = json_encode([
    'status' => ['type' => 'select', 'placeholder' => 'Selecione o status', 'options' => array_map(function ($v) { return array('value' => $v, 'label' => $v); }, $STATUS_LIST)],
    'setor'  => ['type' => 'select', 'placeholder' => 'Selecione o setor', 'options' => array_map(function ($v) { return array('value' => $v, 'label' => $v); }, $SETORES)],
    'local'  => ['type' => 'select', 'placeholder' => 'Selecione a unidade', 'options' => array_map(function ($v) { return array('value' => $v, 'label' => $v); }, $LOCAIS)],
], JSON_UNESCAPED_UNICODE);


if (get_query('export') === 'excel') {
    $exportStmt = $db->prepare('SELECT c.* FROM computadores c WHERE ' . implode(' AND ', $where) . ' ORDER BY c.data_cadastro DESC, c.id DESC');
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
    export_excel_xml('computadores.xls', $exportHeaders, $exportRows);
}

include 'includes/header.php';
echo render_flash();
?>
<div class="page-head"><div class="page-head-copy"><h2>Inventário de computadores</h2><p></p></div><?php if ($canWrite): ?><div class="page-head-actions"><a href="cadastrar.php?tipo=computador" class="btn btn-primary"><?= icon('plus') ?> Novo computador</a></div><?php endif; ?></div>

<div class="stats-grid chamados-stats inventory-stats">
    <article class="stat-card chamados-stat chamados-stat-total">
        <div class="sc-label">Total de computadores</div>
        <div class="sc-value"><?= number_format((float) $statsComputadores['total'], 0, ',', '.') ?></div>
        <div class="sc-foot">Computadores listados</div>
        <div class="sc-bar"></div>
    </article>
    <article class="stat-card chamados-stat chamados-stat-success">
        <div class="sc-label">Em uso</div>
        <div class="sc-value"><?= number_format((float) $statsComputadores['em_uso'], 0, ',', '.') ?></div>
        <div class="sc-foot">Status operacional</div>
        <div class="sc-bar"></div>
    </article>
    <article class="stat-card chamados-stat chamados-stat-open">
        <div class="sc-label">Disponíveis</div>
        <div class="sc-value"><?= number_format((float) $statsComputadores['disponivel'], 0, ',', '.') ?></div>
        <div class="sc-foot">Prontos para uso</div>
        <div class="sc-bar"></div>
    </article>
    <article class="stat-card chamados-stat chamados-stat-neutral">
        <div class="sc-label">Unidades</div>
        <div class="sc-value"><?= number_format((float) $statsComputadores['unidades'], 0, ',', '.') ?></div>
        <div class="sc-foot">Locais com ativos</div>
        <div class="sc-bar"></div>
    </article>
</div>

<div class="card inventory-card" style="padding:0;overflow:hidden">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--bdr);gap:12px;flex-wrap:wrap">
        <div class="stitle" style="margin:0;flex:1"><?= icon('computer') ?> Computadores
            <span style="font-size:12px;color:var(--t3);font-weight:400;margin-left:6px"><?= $totalRows ?> registro<?= $totalRows !== 1 ? 's' : '' ?></span>
        </div>
</div>

    <form method="get" class="toolbar-shell" id="computadores-filter-form">
        <div class="toolbar-grow">
            <input type="text" name="q" placeholder="Buscar por nome, usuário, serial, processador, SSD..." value="<?= e($busca) ?>">
        </div>
        <div class="toolbar-field">
            <select name="filter_by" id="computadores-filter-by">
                <option value="">Filtro complementar</option>
                <option value="status" <?= $filterBy==='status'?'selected':'' ?>>Status</option>
                <option value="setor" <?= $filterBy==='setor'?'selected':'' ?>>Setor</option>
                <option value="local" <?= $filterBy==='local'?'selected':'' ?>>Unidade</option>
            </select>
        </div>
        <div class="toolbar-field" id="computadores-filter-value-slot"></div>
        <div class="toolbar-actions">
            <button type="submit" class="btn btn-ghost btn-sm"><?= icon('filter') ?> Filtrar</button>
            <a href="computadores.php" class="btn btn-ghost btn-sm">Limpar</a>
            <a href="computadores.php<?= e(current_query(['export' => 'excel'], ['page'])) ?>" class="btn btn-sm btn-export">Exportar Excel</a>
        </div>
    </form>
    <?php if ($filterBy && $filterValue !== ''): ?>
    <div class="filter-note" style="padding:0 18px 14px">Filtro aplicado: <strong><?= e($filterLabelMap[$filterBy] ?? $filterBy) ?></strong> = <?= e($filterValue) ?></div>
    <?php endif; ?>

    <?php if (empty($rows)): ?>
    <div class="empty-state">
        <?= icon('computer') ?>
        <p>Nenhum computador encontrado.<?php if ($canWrite): ?><br><a href="cadastrar.php?tipo=computador">Cadastrar novo</a><?php endif; ?></p>
    </div>
    <?php else: ?>
    <div class="table-wrap inventory-table-wrap">
    <table class="inventory-table inventory-table-computadores">
        <thead><tr>
            <th style="width:52px">#</th>
            <th>Ativo</th>
            <th style="width:320px">Contexto</th>
            <th style="width:300px">Especificações</th>
            <th style="width:120px">Status</th>
            <th style="width:84px">Ações</th>
        </tr></thead>
        <tbody>
        <?php $sm = ['Em uso'=>'b-green','Disponível'=>'b-neutral','Manutenção'=>'b-amber','Desativado'=>'b-gray']; foreach ($rows as $c): ?>
        <?php $displayName = trim((string) ($c['nome_dispositivo'] ?: ($c['marca'] . ' ' . $c['modelo']))); ?>
        <tr>
            <td class="mono" style="color:var(--t3)"><?= (int)$c['id'] ?></td>
            <td>
                <div class="asset-premium-main">
                    <div class="asset-premium-title">
                        <span class="asset-name"><?= e($displayName ?: 'Computador sem nome') ?></span>
                        <span class="asset-sub"><?= e($c['marca']) ?> · <?= e($c['modelo']) ?><?= $c['tipo'] ? ' · ' . e($c['tipo']) : '' ?></span>
                    </div>
                    <div class="asset-premium-row">
                        <span class="asset-inline-chip"><?= icon('search') ?>Série: <?= e($c['numero_serie'] ?: '—') ?></span>
                        <?php if ($c['nome_dispositivo']): ?><span class="asset-inline-chip"><?= icon('computer') ?>Hostname: <?= e($c['nome_dispositivo']) ?></span><?php endif; ?>
                    </div>
                </div>
            </td>
            <td>
                <div class="asset-inline-info">
                    <span><?= icon('user') ?> <?= e($c['usuario_responsavel'] ?: 'Não vinculado') ?></span>
                    <span><?= icon('box') ?> <?= e($c['setor'] ?: '—') ?></span>
                    <span><?= icon('computer') ?> <?= e($c['localizacao'] ?: '—') ?></span>
                </div>
            </td>
            <td>
                <div class="asset-spec-inline">
                    <span><?= e($c['sistema_operacional'] ?: '—') ?></span>
                    <span><?= e($c['processador'] ?: '—') ?></span>
                    <span><?= e($c['ram'] ?: '—') ?></span>
                    <span><?= e($c['armazenamento'] ?: '—') ?></span>
                </div>
            </td>
            <td><span class="badge <?= $sm[$c['status']] ?? 'b-gray' ?>"><?= e($c['status']) ?></span></td>
            <td><?php if ($canWrite): ?><div class="act-btns"><a href="editar.php?tipo=computador&id=<?= (int)$c['id'] ?>" class="btn-icon edit" title="Editar"><?= icon('edit') ?></a><form method="post" action="excluir.php" style="display:inline" onsubmit="return confirm('Excluir este computador?')"><?= csrf_input() ?><input type="hidden" name="tipo" value="computador"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><button type="submit" class="btn-icon del" title="Excluir" style="border:none;background:none;padding:0"><?= icon('trash') ?></button></form></div><?php else: ?><span class="sub">Somente leitura</span><?php endif; ?></td>
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
    const select = document.getElementById('computadores-filter-by');
    const slot = document.getElementById('computadores-filter-value-slot');
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
