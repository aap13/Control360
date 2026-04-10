<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
requireAccess('fornecedores');
$canWrite = can_write_module('fornecedores');
$pageTitle = 'Fornecedores';
$db = getDB();
$erros = [];

if (request_is_post()) {
    validate_csrf_or_fail($_POST['csrf_token'] ?? null);
    $action = (string) post('action', 'save');

    if ($action === 'delete') {
        $delId = (int) post('id', 0);
        $used = $db->prepare('SELECT COUNT(*) FROM faturas WHERE fornecedor_id = :id');
        $used->execute([':id' => $delId]);

        if ($used->fetchColumn() > 0) {
            flash('Não é possível excluir: há faturas vinculadas.', 'error');
        } else {
            $oldStmt = $db->prepare('SELECT * FROM fornecedores WHERE id = :id');
            $oldStmt->execute([':id' => $delId]);
            $old = $oldStmt->fetch();

            $db->prepare('DELETE FROM fornecedores WHERE id = :id')->execute([':id' => $delId]);
            if ($old) {
                audit_log('delete', 'fornecedores', $delId, ['registro' => $old]);
            }
            flash('Fornecedor excluído.');
        }
        redirect('fornecedores.php');
    }

    $id    = (int) post('id', 0);
    $nome  = trim((string) post('nome', ''));
    $cat   = trim((string) post('categoria', ''));
    $cnpj  = trim((string) post('cnpj', ''));
    $obs   = trim((string) post('observacoes', ''));
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    validate_required($nome, 'Nome', $erros);
    validate_max_length($nome, 120, 'Nome', $erros);
    validate_max_length($cat, 80, 'Categoria', $erros);
    validate_max_length($obs, 1000, 'Observações', $erros);
    validate_cnpj_optional($cnpj, $erros);

    if (empty($erros)) {
        $cnpjSave = $cnpj !== '' ? $cnpj : null;
        $catSave = $cat !== '' ? $cat : null;
        $obsSave = $obs !== '' ? $obs : null;

        if ($id > 0) {
            $oldStmt = $db->prepare('SELECT * FROM fornecedores WHERE id = :id');
            $oldStmt->execute([':id' => $id]);
            $old = $oldStmt->fetch();

            $db->prepare('UPDATE fornecedores SET nome=:n, categoria=:c, cnpj=:cnpj, observacoes=:o, ativo=:a WHERE id=:id')
               ->execute([':n'=>$nome, ':c'=>$catSave, ':cnpj'=>$cnpjSave, ':o'=>$obsSave, ':a'=>$ativo, ':id'=>$id]);

            audit_log('update', 'fornecedores', $id, [
                'antes' => $old,
                'depois' => [
                    'nome' => $nome,
                    'categoria' => $catSave,
                    'cnpj' => $cnpjSave,
                    'observacoes' => $obsSave,
                    'ativo' => $ativo,
                ],
            ]);
            flash('Fornecedor atualizado!');
        } else {
            $db->prepare('INSERT INTO fornecedores (nome, categoria, cnpj, observacoes, ativo) VALUES (:n, :c, :cnpj, :o, :a)')
               ->execute([':n'=>$nome, ':c'=>$catSave, ':cnpj'=>$cnpjSave, ':o'=>$obsSave, ':a'=>$ativo]);

            $newId = (int) $db->lastInsertId();
            audit_log('create', 'fornecedores', $newId, [
                'nome' => $nome,
                'categoria' => $catSave,
                'cnpj' => $cnpjSave,
                'observacoes' => $obsSave,
                'ativo' => $ativo,
            ]);
            flash('Fornecedor cadastrado!');
        }
        redirect('fornecedores.php');
    }
}

$editing = null;
if (isset($_GET['edit'])) {
    $s = $db->prepare('SELECT * FROM fornecedores WHERE id=:id');
    $s->execute([':id'=>(int)$_GET['edit']]);
    $editing = $s->fetch();
}

$categorias = $db->query('SELECT DISTINCT categoria FROM fornecedores WHERE categoria IS NOT NULL AND categoria <> "" ORDER BY categoria')->fetchAll(PDO::FETCH_COLUMN);
$busca = trim((string) get_query('q', ''));
$filterBy = trim((string) get_query('filter_by', ''));
$filterValue = trim((string) get_query('filter_value', ''));
$page = query_int('page', 1, 1);
$perPage = 15;

$where = ['1=1'];
$params = [];
if ($busca !== '') {
    $where[] = '('
        . 'f.nome LIKE :q_nome OR '
        . 'f.cnpj LIKE :q_cnpj OR '
        . 'f.categoria LIKE :q_categoria OR '
        . 'f.contato LIKE :q_contato OR '
        . 'f.observacoes LIKE :q_observacoes'
        . ')';
    $searchLike = '%' . $busca . '%';
    $params[':q_nome'] = $searchLike;
    $params[':q_cnpj'] = $searchLike;
    $params[':q_categoria'] = $searchLike;
    $params[':q_contato'] = $searchLike;
    $params[':q_observacoes'] = $searchLike;
}
if ($filterBy === 'ativo' && in_array($filterValue, ['1', '0'], true)) {
    $where[] = 'f.ativo = :ativo';
    $params[':ativo'] = (int) $filterValue;
}
if ($filterBy === 'categoria' && in_array($filterValue, $categorias, true)) {
    $where[] = 'f.categoria = :categoria';
    $params[':categoria'] = $filterValue;
}

$countStmt = $db->prepare('SELECT COUNT(*) FROM fornecedores f WHERE ' . implode(' AND ', $where));
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$pagination = paginate($totalRows, $page, $perPage);

$sql = 'SELECT f.*, (SELECT COUNT(*) FROM faturas WHERE fornecedor_id=f.id) AS total_faturas FROM fornecedores f WHERE '
     . implode(' AND ', $where)
     . ' ORDER BY f.nome ASC, f.id DESC LIMIT :limit OFFSET :offset';
$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$fornecedores = $stmt->fetchAll();

$filterLabelMap = ['ativo' => 'Situação', 'categoria' => 'Categoria'];
$filterValueLabel = $filterValue;
if ($filterBy === 'ativo') {
    $filterValueLabel = $filterValue === '1' ? 'Ativo' : ($filterValue === '0' ? 'Inativo' : '');
}
$filterOptionsJson = json_encode([
    'ativo' => [
        'type' => 'select',
        'placeholder' => 'Selecione a situação',
        'options' => [['value' => '1', 'label' => 'Ativo'], ['value' => '0', 'label' => 'Inativo']],
    ],
    'categoria' => [
        'type' => 'select',
        'placeholder' => 'Selecione a categoria',
        'options' => array_map(function ($v) { return array('value' => $v, 'label' => $v); }, $categorias),
    ],
], JSON_UNESCAPED_UNICODE);

include 'includes/header.php';
echo render_flash();
?>
<div class="page-head"><div class="page-head-copy"><h2>Fornecedores</h2><p>Organize parceiros, categorias e situação cadastral com o mesmo padrão visual das demais áreas operacionais.</p></div></div>
<?php if (!empty($erros)): ?>
<div class="alert alert-error"><?= icon('off') ?> <?= implode(' · ', array_map('e', $erros)) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 320px;gap:18px;align-items:start">
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 18px;border-bottom:1px solid var(--bdr)">
            <div class="stitle" style="margin:0"><?= icon('box') ?> Fornecedores <span style="font-size:12px;color:var(--t3);font-weight:400;margin-left:6px"><?= $totalRows ?> cadastro<?= $totalRows != 1 ? 's' : '' ?></span></div>
        </div>

        <form method="get" class="toolbar-shell" id="fornecedores-filter-form">
            <div class="toolbar-grow">
                <input type="text" name="q" placeholder="Buscar por nome, CNPJ ou categoria..." value="<?= e($busca) ?>">
            </div>
            <div class="toolbar-field">
                <select name="filter_by" id="fornecedores-filter-by">
                    <option value="">Filtro complementar</option>
                    <option value="ativo" <?= $filterBy==='ativo'?'selected':'' ?>>Situação</option>
                    <option value="categoria" <?= $filterBy==='categoria'?'selected':'' ?>>Categoria</option>
                </select>
            </div>
            <div class="toolbar-field" id="fornecedores-filter-value-slot"></div>
            <div class="toolbar-actions">
                <button type="submit" class="btn btn-ghost btn-sm"><?= icon('filter') ?> Filtrar</button>
                <a href="fornecedores.php" class="btn btn-ghost btn-sm">Limpar</a>
            </div>
        </form>
        <?php if ($filterBy && $filterValueLabel !== ''): ?>
        <div class="filter-note" style="padding:0 18px 14px">Filtro aplicado: <strong><?= e($filterLabelMap[$filterBy] ?? $filterBy) ?></strong> = <?= e($filterValueLabel) ?></div>
        <?php endif; ?>

        <?php if (empty($fornecedores)): ?>
        <div class="empty-state"><?= icon('box') ?><p>Nenhum fornecedor.<br>Use o formulário ao lado.</p></div>
        <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Nome</th><th>Categoria</th><th>CNPJ</th><th>Faturas</th><th>Situação</th><th style="width:90px">Ações</th></tr></thead>
            <tbody>
            <?php foreach ($fornecedores as $f): ?>
            <tr>
                <td><strong><?= e($f['nome']) ?></strong><?php if ($f['observacoes']): ?><div class="sub"><?= e($f['observacoes']) ?></div><?php endif; ?></td>
                <td><?= e($f['categoria'] ?: '—') ?></td>
                <td class="mono"><?= e($f['cnpj'] ?: '—') ?></td>
                <td><span class="badge b-neutral"><?= (int)$f['total_faturas'] ?></span></td>
                <td><span class="badge <?= $f['ativo'] ? 'b-green' : 'b-gray' ?>"><?= $f['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                <td><?php if ($canWrite): ?><div class="act-btns"><a href="fornecedores.php?edit=<?= (int)$f['id'] ?>" class="btn-icon edit"><?= icon('edit') ?></a><form method="post" style="display:inline" onsubmit="return confirm('Excluir?')"><?= csrf_input() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$f['id'] ?>"><button type="submit" class="btn-icon del" style="border:none;background:none;padding:0"><?= icon('trash') ?></button></form></div><?php else: ?><span class="sub">Somente leitura</span><?php endif; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?= render_pagination($pagination) ?>
        <?php endif; ?>
    </div>

    <?php if ($canWrite): ?><div class="card">
        <div class="stitle"><?= $editing ? icon('edit').' Editar' : icon('plus').' Novo Fornecedor' ?></div>
        <form method="post">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= $editing['id'] ?? 0 ?>">
            <div class="form-group" style="margin-bottom:13px">
                <label>Nome <span class="req">*</span></label>
                <input type="text" name="nome" placeholder="Nome do fornecedor" value="<?= e($editing['nome'] ?? ($_POST['nome']??'')) ?>">
            </div>
            <div class="form-group" style="margin-bottom:13px">
                <label>Categoria</label>
                <input type="text" name="categoria" placeholder="Ex: Software, Internet, Insumos..." value="<?= e($editing['categoria'] ?? ($_POST['categoria']??'')) ?>">
            </div>
            <div class="form-group" style="margin-bottom:13px">
                <label>CNPJ</label>
                <input type="text" name="cnpj" placeholder="00.000.000/0000-00" value="<?= e($editing['cnpj'] ?? ($_POST['cnpj']??'')) ?>">
            </div>
            <div class="form-group" style="margin-bottom:16px">
                <label>Observações</label>
                <textarea name="observacoes" placeholder="Anotações sobre o fornecedor..." style="min-height:90px"><?= e($editing['observacoes'] ?? ($_POST['observacoes']??'')) ?></textarea>
            </div>
            <div class="form-group" style="margin-bottom:16px">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500"><input type="checkbox" name="ativo" value="1" <?= ($editing['ativo'] ?? ($_POST['ativo'] ?? 1)) ? 'checked' : '' ?> style="width:auto;margin:0"> Fornecedor ativo</label>
            </div>
            <div style="display:flex;gap:9px;flex-wrap:wrap"><button type="submit" class="btn btn-primary btn-sm"><?= icon('check') ?> <?= $editing ? 'Salvar' : 'Cadastrar' ?></button><?php if ($editing): ?><a href="fornecedores.php" class="btn btn-ghost btn-sm"><?= icon('back') ?> Cancelar</a><?php endif; ?></div>
        </form>
    </div><?php endif; ?>
</div>
<script>
(function(){
    const config = <?= $filterOptionsJson ?>;
    const select = document.getElementById('fornecedores-filter-by');
    const slot = document.getElementById('fornecedores-filter-value-slot');
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
