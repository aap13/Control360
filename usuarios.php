<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
requireAccess('usuarios');
$pageTitle = 'Usuários';
$db = getDB();
$erros = [];
$MODULOS = module_labels();
$distribuicaoClientes = distribuicao_all_clients();
$distribuicaoEmpresas = distribuicao_company_options_by_client();
$editingClientPerms = [];
$editingCompanyPerms = [];


if (!function_exists('usuarios_permission_keys')) {
    function usuarios_permission_keys($rawPerms, array $modulos): array
    {
        if ($rawPerms === 'all') {
            return array_keys($modulos);
        }

        if (is_string($rawPerms)) {
            $decoded = json_decode($rawPerms, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $rawPerms = $decoded;
            }
        }

        $keys = [];
        $visit = function ($node) use (&$visit, &$keys, $modulos) {
            if (is_string($node) || is_int($node)) {
                $key = (string) $node;
                $keys[$key] = $key;
                return;
            }

            if (!is_array($node)) {
                return;
            }

            foreach ($node as $key => $value) {
                if (is_string($value) || is_int($value)) {
                    $module = (string) $value;
                    $labels[$module] = $modulos[$module] ?? ucfirst(str_replace('_', ' ', $module));
                    continue;
                }

                if (is_array($value)) {
                    $moduleName = '';
                    foreach (['modulo', 'module', 'nome'] as $candidate) {
                        if (!empty($value[$candidate]) && is_string($value[$candidate])) {
                            $moduleName = (string) $value[$candidate];
                            break;
                        }
                    }

                    if ($moduleName !== '') {
                        $hasGrantedAction = false;
                        foreach ($value as $subKey => $subValue) {
                            if (in_array($subKey, ['modulo', 'module', 'nome'], true)) {
                                continue;
                            }
                            if (is_bool($subValue) || is_int($subValue) || is_string($subValue)) {
                                if ((bool) $subValue) {
                                    $hasGrantedAction = true;
                                    break;
                                }
                            }
                        }

                        if ($hasGrantedAction) {
                            if (isset($modulos[$moduleName])) { $keys[$moduleName] = $moduleName; }
                        }
                    }

                    $visit($value);
                    continue;
                }

                if ((is_bool($value) || is_int($value) || is_string($value)) && (bool) $value) {
                    $moduleKey = (string) $key;
                    if (isset($modulos[$moduleKey])) {
                        $keys[$moduleKey] = $moduleKey;
                    }
                }
            }
        };

        $visit($rawPerms);

        return array_values(array_unique(array_filter($keys)));
    }
}

if (!function_exists('usuarios_permission_labels')) {
    function usuarios_permission_labels($rawPerms, array $modulos): array
    {
        $keys = usuarios_permission_keys($rawPerms, $modulos);
        $labels = [];
        foreach ($keys as $key) {
            $labels[] = $modulos[$key] ?? ucfirst(str_replace('_', ' ', $key));
        }
        return $labels;
    }
}


if (request_is_post()) {
    require_write_access('usuarios', 'index.php');
    validate_csrf_or_fail($_POST['csrf_token'] ?? null);
    $action = (string) post('action', 'save');

    if ($action === 'delete') {
        $delId = (int) post('id', 0);
        if ($delId === (int) $_SESSION['user_id']) {
            flash('Você não pode excluir sua própria conta.', 'error');
        } elseif ($delId > 0) {
            $userRow = $db->prepare('SELECT id, nome, usuario FROM usuarios WHERE id = :id');
            $userRow->execute([':id' => $delId]);
            $deleted = $userRow->fetch();
            $db->prepare('DELETE FROM usuarios WHERE id = :id')->execute([':id' => $delId]);
            if ($deleted) {
                audit_log('delete', 'usuarios', $delId, ['nome' => $deleted['nome'], 'usuario' => $deleted['usuario']]);
            }
            flash('Usuário excluído.');
        }
        redirect('usuarios.php');
    }

    if ($action === 'toggle') {
        $tid = (int) post('id', 0);
        if ($tid === (int) $_SESSION['user_id']) {
            flash('Você não pode desativar sua própria conta.', 'error');
        } elseif ($tid > 0) {
            $beforeStmt = $db->prepare('SELECT id, nome, usuario, ativo FROM usuarios WHERE id = :id');
            $beforeStmt->execute([':id' => $tid]);
            $before = $beforeStmt->fetch();
            $db->prepare('UPDATE usuarios SET ativo = 1 - ativo WHERE id = :id')->execute([':id' => $tid]);
            $afterStmt = $db->prepare('SELECT ativo FROM usuarios WHERE id = :id');
            $afterStmt->execute([':id' => $tid]);
            $after = $afterStmt->fetch();
            audit_log('toggle_status', 'usuarios', $tid, [
                'nome' => $before['nome'] ?? null,
                'usuario' => $before['usuario'] ?? null,
                'ativo_anterior' => isset($before['ativo']) ? (int)$before['ativo'] : null,
                'ativo_atual' => isset($after['ativo']) ? (int)$after['ativo'] : null,
            ]);
            flash('Status atualizado.');
        }
        redirect('usuarios.php');
    }

    $id = (int) post('id', 0);
    $nome = trim((string) post('nome', ''));
    $user = trim((string) post('usuario', ''));
    $senha = trim((string) post('senha', ''));
    $perfil = trim((string) post('perfil', 'custom'));
    $perms = $_POST['permissoes'] ?? [];
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    validate_required($nome, 'Nome', $erros);
    validate_required($user, 'Usuário', $erros);
    validate_max_length($nome, 100, 'Nome', $erros);
    validate_max_length($user, 50, 'Usuário', $erros);
    validate_in_list($perfil, ['admin', 'viewer', 'consulta', 'custom', 'personalizado'], 'Perfil', $erros);

    if ($id === 0 && $senha === '') {
        add_error($erros, 'Senha é obrigatória para novo usuário.');
    }
    if ($senha !== '' && strlen($senha) < 6) {
        add_error($erros, 'Senha deve ter ao menos 6 caracteres.');
    }

    if (!$erros) {
        $chk = $db->prepare('SELECT id FROM usuarios WHERE usuario = :u AND id != :id');
        $chk->execute([':u' => $user, ':id' => $id]);
        if ($chk->fetch()) {
            add_error($erros, 'Este nome de usuário já está em uso.');
        }
    }

    if (empty($erros)) {
        $permsValidas = array_values(array_intersect(array_keys($MODULOS), (array) $perms));
        $permsJson = $perfil === 'admin' ? 'all' : json_encode($permsValidas);
        $clientPerms = distribuicao_parse_allowed_clients_from_post();
        $companyPerms = distribuicao_parse_allowed_companies_from_post();

        if ($id > 0) {
            $oldStmt = $db->prepare('SELECT id, nome, usuario, perfil, permissoes, ativo FROM usuarios WHERE id = :id');
            $oldStmt->execute([':id' => $id]);
            $old = $oldStmt->fetch();
            if ($id === (int) $_SESSION['user_id']) {
                $ativo = 1;
            }
            if ($senha) {
                $hash = password_hash($senha, PASSWORD_BCRYPT);
                $db->prepare('UPDATE usuarios SET nome=:n, usuario=:u, senha=:s, perfil=:p, permissoes=:pm, ativo=:a WHERE id=:id')
                   ->execute([':n'=>$nome, ':u'=>$user, ':s'=>$hash, ':p'=>$perfil, ':pm'=>$permsJson, ':a'=>$ativo, ':id'=>$id]);
            } else {
                $db->prepare('UPDATE usuarios SET nome=:n, usuario=:u, perfil=:p, permissoes=:pm, ativo=:a WHERE id=:id')
                   ->execute([':n'=>$nome, ':u'=>$user, ':p'=>$perfil, ':pm'=>$permsJson, ':a'=>$ativo, ':id'=>$id]);
            }
            distribuicao_save_user_client_permissions($id, $perfil === 'admin' ? [] : $clientPerms, $perfil === 'admin' ? [] : $companyPerms);
            audit_log('update', 'usuarios', $id, ['antes' => $old, 'depois' => ['nome' => $nome, 'usuario' => $user, 'perfil' => $perfil, 'permissoes' => $perfil === 'admin' ? 'all' : $permsValidas, 'ativo' => $ativo, 'senha_alterada' => $senha !== '']]);
            flash('Usuário atualizado!');
        } else {
            $hash = password_hash($senha, PASSWORD_BCRYPT);
            $db->prepare('INSERT INTO usuarios (nome,usuario,senha,perfil,permissoes,ativo) VALUES (:n,:u,:s,:p,:pm,:a)')
               ->execute([':n'=>$nome, ':u'=>$user, ':s'=>$hash, ':p'=>$perfil, ':pm'=>$permsJson, ':a'=>$ativo]);
            $newId = (int) $db->lastInsertId();
            distribuicao_save_user_client_permissions($newId, $perfil === 'admin' ? [] : $clientPerms, $perfil === 'admin' ? [] : $companyPerms);
            audit_log('create', 'usuarios', $newId, ['nome' => $nome, 'usuario' => $user, 'perfil' => $perfil, 'permissoes' => $perfil === 'admin' ? 'all' : $permsValidas, 'ativo' => $ativo]);
            flash('Usuário criado!');
        }
        redirect('usuarios.php');
    }
}

$editing = null;
$editPerms = [];
if (isset($_GET['edit'])) {
    $s = $db->prepare('SELECT * FROM usuarios WHERE id=:id');
    $s->execute([':id'=>(int)$_GET['edit']]);
    $editing = $s->fetch();
    if ($editing) {
        $editPerms = usuarios_permission_keys($editing['permissoes'] ?? '[]', $MODULOS);
        $editingClientPerms = distribuicao_user_client_permissions_for_form((int) $editing['id']);
        $editingCompanyPerms = distribuicao_user_company_permissions_for_form((int) $editing['id']);
    }
}

$busca = trim((string) get_query('q', ''));
$filterBy = trim((string) get_query('filter_by', ''));
$filterValue = trim((string) get_query('filter_value', ''));
$page = query_int('page', 1, 1);
$perPage = 15;

$where = ['1=1'];
$params = [];
if ($busca !== '') {
    $where[] = '(u.nome LIKE :q_nome OR u.usuario LIKE :q_usuario)';
    $searchLike = '%' . $busca . '%';
    $params[':q_nome'] = $searchLike;
    $params[':q_usuario'] = $searchLike;
}
if ($filterBy === 'perfil' && in_array($filterValue, ['admin', 'viewer', 'consulta', 'custom', 'personalizado'], true)) {
    $where[] = 'u.perfil = :perfil';
    $params[':perfil'] = $filterValue;
}
if ($filterBy === 'ativo' && in_array($filterValue, ['1', '0'], true)) {
    $where[] = 'u.ativo = :ativo';
    $params[':ativo'] = (int) $filterValue;
}

$countStmt = $db->prepare('SELECT COUNT(*) FROM usuarios u WHERE ' . implode(' AND ', $where));
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$pagination = paginate($totalRows, $page, $perPage);

$sql = 'SELECT u.* FROM usuarios u WHERE ' . implode(' AND ', $where)
     . ' ORDER BY u.nome ASC, u.id DESC LIMIT :limit OFFSET :offset';
$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$usuarios = $stmt->fetchAll();

$filterLabelMap = ['perfil' => 'Perfil', 'ativo' => 'Status'];
$filterValueLabel = $filterValue;
if ($filterBy === 'perfil') {
    $filterValueLabel = $filterValue === 'admin' ? 'Admin' : (in_array($filterValue, ['viewer','consulta'], true) ? 'Consulta' : (in_array($filterValue, ['custom','personalizado'], true) ? 'Personalizado' : ''));
}
if ($filterBy === 'ativo') {
    $filterValueLabel = $filterValue === '1' ? 'Ativo' : ($filterValue === '0' ? 'Inativo' : '');
}
$filterOptionsJson = json_encode([
    'perfil' => [
        'type' => 'select',
        'placeholder' => 'Selecione o perfil',
        'options' => [['value' => 'admin', 'label' => 'Admin'], ['value' => 'custom', 'label' => 'Personalizado'], ['value' => 'consulta', 'label' => 'Consulta']],
    ],
    'ativo' => [
        'type' => 'select',
        'placeholder' => 'Selecione o status',
        'options' => [['value' => '1', 'label' => 'Ativo'], ['value' => '0', 'label' => 'Inativo']],
    ],
], JSON_UNESCAPED_UNICODE);

include 'includes/header.php';
echo render_flash();
?>
<div class="page-head"><div class="page-head-copy"><h2>Usuários e acessos</h2><p>Gerencie contas, perfis e permissões com uma estrutura visual única, clara e mais fácil de manter.</p></div></div>
<?php if (!empty($erros)): ?>
<div class="alert alert-error"><?= icon('off') ?> <?= implode(' · ', array_map('e', $erros)) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:18px;align-items:start">
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 18px;border-bottom:1px solid var(--bdr)">
            <div class="stitle" style="margin:0"><?= icon('users') ?> Usuários <span style="font-size:12px;color:var(--t3);font-weight:400;margin-left:6px"><?= $totalRows ?> cadastro<?= $totalRows != 1 ? 's' : '' ?></span></div>
        </div>

        <form method="get" class="toolbar-shell" id="usuarios-filter-form">
            <div class="toolbar-grow">
                <input type="text" name="q" placeholder="Buscar por nome ou usuário" value="<?= e($busca) ?>">
            </div>
            <div class="toolbar-field">
                <select name="filter_by" id="usuarios-filter-by">
                    <option value="">Filtro complementar</option>
                    <option value="perfil" <?= $filterBy==='perfil'?'selected':'' ?>>Perfil</option>
                    <option value="ativo" <?= $filterBy==='ativo'?'selected':'' ?>>Status</option>
                </select>
            </div>
            <div class="toolbar-field" id="usuarios-filter-value-slot"></div>
            <div class="toolbar-actions">
                <button type="submit" class="btn btn-ghost btn-sm"><?= icon('filter') ?> Filtrar</button>
                <a href="usuarios.php" class="btn btn-ghost btn-sm">Limpar</a>
            </div>
        </form>
        <?php if ($filterBy && $filterValueLabel !== ''): ?>
        <div class="filter-note" style="padding:0 18px 14px">Filtro aplicado: <strong><?= e($filterLabelMap[$filterBy] ?? $filterBy) ?></strong> = <?= e($filterValueLabel) ?></div>
        <?php endif; ?>

        <div class="table-wrap">
        <table>
            <thead><tr><th>Nome</th><th>Usuário</th><th>Perfil</th><th>Acesso</th><th>Último Login</th><th>Status</th><th style="width:120px">Ações</th></tr></thead>
            <tbody>
            <?php foreach ($usuarios as $u): $permLabels = usuarios_permission_labels($u['permissoes'] ?? '[]', $MODULOS); $isMe = (int)$u['id'] === (int)$_SESSION['user_id']; ?>
            <tr>
                <td><strong><?= e($u['nome']) ?></strong><?php if ($isMe): ?><span style="font-size:11px;color:var(--t3);margin-left:6px">(você)</span><?php endif; ?></td>
                <td class="mono"><?= e($u['usuario']) ?></td>
                <td><span class="badge <?= $u['perfil']==='admin'?'b-red2':'b-neutral' ?>"><?= $u['perfil']==='admin' ? 'Admin' : (in_array($u['perfil'], ['viewer','consulta'], true) ? 'Consulta' : 'Personalizado') ?></span></td>
                <td style="max-width:200px"><?php if ($u['permissoes']==='all'): ?><span style="font-size:12px;color:var(--t2)">Acesso total</span><?php else: ?><div style="display:flex;flex-wrap:wrap;gap:4px"><?php foreach ($permLabels as $label): ?><span class="badge b-neutral" style="font-size:10.5px"><?= e($label) ?></span><?php endforeach; ?><?php if (empty($permLabels)): ?><span style="font-size:12px;color:var(--t3)">Configuração incompleta</span><?php endif; ?></div><?php endif; ?></td>
                <td class="mono" style="color:var(--t3);font-size:12px"><?= $u['ultimo_login'] ? date('d/m/y H:i', strtotime($u['ultimo_login'])) : '—' ?></td>
                <td><?php if (!$isMe): ?><form method="post" style="display:inline"><?= csrf_input() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button type="submit" style="background:none;border:none;padding:0;cursor:pointer"><span class="badge <?= $u['ativo']?'b-green':'b-gray' ?>"><?= $u['ativo']?'Ativo':'Inativo' ?></span></button></form><?php else: ?><span class="badge b-green">Ativo</span><?php endif; ?></td>
                <td><div class="act-btns"><a href="usuarios.php?edit=<?= (int)$u['id'] ?>" class="btn-icon edit"><?= icon('edit') ?></a><?php if (!$isMe): ?><form method="post" style="display:inline" onsubmit="return confirm('Excluir usuário <?= e($u['nome']) ?>?')"><?= csrf_input() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button type="submit" class="btn-icon del" style="border:none;background:none;padding:0"><?= icon('trash') ?></button></form><?php endif; ?></div></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($usuarios)): ?><tr><td colspan="7" style="padding:24px;text-align:center;color:var(--t3)">Nenhum usuário encontrado.</td></tr><?php endif; ?>
            </tbody>
        </table>
        </div>
        <?= render_pagination($pagination) ?>
    </div>

    <div class="card">
        <div class="stitle"><?= $editing ? icon('edit').' Editar Usuário' : icon('plus').' Novo Usuário' ?></div>
        <form method="post">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= $editing['id'] ?? 0 ?>">
            <div class="form-group" style="margin-bottom:13px"><label>Nome completo <span class="req">*</span></label><input type="text" name="nome" placeholder="Nome do usuário" value="<?= e($editing['nome'] ?? ($_POST['nome'] ?? '')) ?>"></div>
            <div class="form-group" style="margin-bottom:13px"><label>Login (usuário) <span class="req">*</span></label><input type="text" name="usuario" placeholder="login" autocomplete="off" value="<?= e($editing['usuario'] ?? ($_POST['usuario'] ?? '')) ?>"></div>
            <div class="form-group" style="margin-bottom:13px"><label>Senha <?= $editing ? '' : '<span class="req">*</span>' ?></label><input type="password" name="senha" placeholder="<?= $editing?'Deixe vazio para manter':'Mínimo 6 caracteres' ?>" autocomplete="new-password"></div>
            <div class="form-group" style="margin-bottom:16px"><label>Perfil</label><select name="perfil" onchange="togglePerms(this.value)"><option value="custom" <?= in_array(($editing['perfil'] ?? 'custom'), ['custom','personalizado'], true)?'selected':'' ?>>Personalizado — grava conforme permissões</option><option value="consulta" <?= in_array(($editing['perfil'] ?? ''), ['viewer','consulta'], true)?'selected':'' ?>>Consulta — somente visualização</option><option value="admin"  <?= ($editing['perfil'] ?? '')==='admin' ?'selected':'' ?>>Admin — acesso total</option></select></div>
            <div id="perms-section" style="margin-bottom:16px;<?= ($editing['perfil'] ?? 'custom')==='admin'?'display:none':'' ?>"><label style="display:block;margin-bottom:9px">Módulos permitidos</label><div style="display:flex;flex-direction:column;gap:7px"><?php foreach ($MODULOS as $key => $label): $checked = in_array($key, $editPerms, true) || (!$editing && in_array($key, ['dashboard','chamados'], true)); ?><label style="display:flex;align-items:center;gap:9px;cursor:pointer;font-weight:500;color:var(--t1)"><input type="checkbox" name="permissoes[]" value="<?= $key ?>" <?= $checked?'checked':'' ?> style="width:auto;margin:0;cursor:pointer;accent-color:var(--accent)"><?= e($label) ?></label><?php endforeach; ?></div></div>
            <?php if (!empty($distribuicaoClientes)): ?>
            <div id="clientes-section" style="margin-bottom:16px">
                <label style="display:block;margin-bottom:9px">Clientes permitidos na distribuição</label>
                <div style="display:flex;flex-direction:column;gap:10px;max-height:260px;overflow:auto;padding:12px;border:1px solid var(--line);border-radius:14px;background:#12171f">
                    <?php foreach ($distribuicaoClientes as $dc): $cp = $editingClientPerms[(int)$dc['id']] ?? ['visualizar'=>false,'cadastrar'=>false,'editar'=>false,'movimentar'=>false]; ?>
                    <div style="padding:10px 12px;border:1px solid var(--line2);border-radius:12px;background:#171d26">
                        <div style="font-weight:700;margin-bottom:8px"><?= e($dc['nome']) ?></div>
                        <div style="display:flex;gap:14px;flex-wrap:wrap;font-size:12px">
                            <?php foreach (['visualizar'=>'Visualizar','cadastrar'=>'Cadastrar','editar'=>'Editar','movimentar'=>'Movimentar'] as $k=>$lbl): ?>
                            <label style="display:flex;align-items:center;gap:7px;cursor:pointer">
                                <input type="checkbox" name="distribuicao_clientes[<?= (int)$dc['id'] ?>][]" value="<?= $k ?>" <?= !empty($cp[$k]) ? 'checked' : '' ?> style="width:auto;margin:0;accent-color:var(--accent)">
                                <?= $lbl ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php foreach ($distribuicaoClientes as $dc): $empresaOpts = $distribuicaoEmpresas[(int)$dc['id']] ?? []; $selEmp = $editingCompanyPerms[(int)$dc['id']] ?? []; if (empty($empresaOpts)) continue; ?>
                <div style="margin-top:10px;padding:10px 12px;border:1px dashed var(--line2);border-radius:12px;background:#10151c">
                    <div style="font-size:12px;font-weight:700;margin-bottom:8px">Empresas liberadas em <?= e($dc['nome']) ?></div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px">
                        <?php foreach ($empresaOpts as $empresaOpt): $normEmp = distribuicao_normalize_empresa($empresaOpt); ?>
                        <label style="display:flex;align-items:center;gap:7px;cursor:pointer;padding:6px 8px;border:1px solid var(--line2);border-radius:10px">
                            <input type="checkbox" name="distribuicao_empresas[<?= (int)$dc['id'] ?>][]" value="<?= e($empresaOpt) ?>" <?= in_array($normEmp, $selEmp, true) ? 'checked' : '' ?> style="width:auto;margin:0;accent-color:var(--accent)">
                            <?= e($empresaOpt) ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="sub">Se nenhuma empresa for marcada para este cliente, o usuário verá todas as empresas dele.</div>
                </div>
                <?php endforeach; ?>
                <div class="sub">Essas permissões só valem para o módulo Distribuição. Admin continua vendo todos os clientes.</div>
            </div>
            <?php endif; ?>
            <?php if ($editing): ?><div class="form-group" style="margin-bottom:16px"><label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500"><input type="checkbox" name="ativo" value="1" <?= ($editing['ativo']??1)?'checked':'' ?> style="width:auto;margin:0"> Usuário ativo</label></div><?php endif; ?>
            <div style="display:flex;gap:9px;flex-wrap:wrap"><button type="submit" class="btn btn-primary btn-sm"><?= icon('check') ?> <?= $editing?'Salvar':'Cadastrar' ?></button><?php if ($editing): ?><a href="usuarios.php" class="btn btn-ghost btn-sm"><?= icon('back') ?> Cancelar</a><?php endif; ?></div>
        </form>
    </div>
</div>
<script>
function togglePerms(value){
    var section=document.getElementById('perms-section');
    if(section){ section.style.display = value==='admin' ? 'none' : 'block'; }
}
(function(){
    const config = <?= $filterOptionsJson ?>;
    const select = document.getElementById('usuarios-filter-by');
    const slot = document.getElementById('usuarios-filter-value-slot');
    const currentValue = <?= json_encode($filterValue, JSON_UNESCAPED_UNICODE) ?>;
    function renderField(){
        const key = select.value;
        if (!key || !config[key]) {
            slot.innerHTML = '<input type="text" class="form-control" value="" placeholder="Valor do filtro" disabled>';
            return;
        }
        const item = config[key];
        if (item.type === 'select') {
            let html = '<select name="filter_value">';
            html += '<option value="">' + item.placeholder + '</option>';
            item.options.forEach(function(opt){
                const selected = String(opt.value) === String(currentValue) ? ' selected' : '';
                html += '<option value="' + opt.value.replace(/"/g, '&quot;') + '"' + selected + '>' + opt.label + '</option>';
            });
            html += '</select>';
            slot.innerHTML = html;
        }
    }
    renderField();
    select.addEventListener('change', function(){
        slot.innerHTML = '';
        renderField();
    });
})();
</script>
<?php include 'includes/footer.php'; ?>
