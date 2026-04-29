<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$pageTitle = 'Meu Perfil';
$db = getDB();
$erros = [];

$user = $db->prepare("SELECT * FROM usuarios WHERE id=:id");
$user->execute([':id' => $_SESSION['user_id']]);
$user = $user->fetch();

$MODULOS = module_labels();

if (request_is_post()) {
    validate_csrf_or_fail($_POST['csrf_token'] ?? null);
    $nome         = trim((string) post('nome', ''));
    $senhaAtual   = trim((string) post('senha_atual', ''));
    $novaSenha    = trim((string) post('nova_senha', ''));
    $confirmSenha = trim((string) post('confirmar_senha', ''));

    validate_required($nome, 'Nome', $erros);
    validate_max_length($nome, 100, 'Nome', $erros);

    if ($novaSenha || $senhaAtual) {
        if (!password_verify($senhaAtual, $user['senha'])) {
            add_error($erros, 'Senha atual incorreta.');
        }
        if (strlen($novaSenha) < 6) {
            add_error($erros, 'Nova senha deve ter ao menos 6 caracteres.');
        }
        if ($novaSenha !== $confirmSenha) {
            add_error($erros, 'As senhas não coincidem.');
        }
    }

    if (empty($erros)) {
        $changes = ['nome_antes' => $user['nome'], 'nome_depois' => $nome, 'senha_alterada' => (bool) $novaSenha];

        if ($novaSenha) {
            $hash = password_hash($novaSenha, PASSWORD_BCRYPT);
            $db->prepare("UPDATE usuarios SET nome=:n, senha=:s WHERE id=:id")
               ->execute([':n'=>$nome, ':s'=>$hash, ':id'=>$_SESSION['user_id']]);
        } else {
            $db->prepare("UPDATE usuarios SET nome=:n WHERE id=:id")
               ->execute([':n'=>$nome, ':id'=>$_SESSION['user_id']]);
        }

        $_SESSION['nome'] = $nome;
        audit_log('update_profile', 'usuarios', (int) $_SESSION['user_id'], $changes);
        flash('Perfil atualizado!');
        redirect('perfil.php');
    }
}

$perms = $user['permissoes'] === 'all' ? array_keys($MODULOS) : (json_decode($user['permissoes']??'[]',true) ?: []);

include 'includes/header.php';
echo render_flash();
?>
<div class="page-head"><div class="page-head-copy"><h2>Meu perfil</h2><p>Confira o resumo da sua conta, permissões disponíveis e status de acesso em um layout mais organizado.</p></div></div>
<?php if (!empty($erros)): ?>
<div class="alert alert-error"><?= icon('off') ?> <?= implode(' · ', array_map('e',$erros)) ?></div>
<?php endif; ?>



<div class="profile-grid">
    <div class="profile-stack">
        <div class="card">
            <div class="stitle"><?= icon('users') ?> Resumo da Conta</div>
            <div class="profile-hero">
                <div class="profile-avatar"><?= strtoupper(substr($user['nome'] ?? '?',0,1)) ?></div>
                <div style="min-width:0">
                    <div class="profile-name"><?= e($user['nome']) ?></div>
                    <div class="profile-sub">Login: <span class="mono"><?= e($user['usuario']) ?></span></div>
                    <div class="profile-chips">
                        <span class="profile-chip"><?= icon('users') ?> <?= $user['perfil']==='admin'?'Administrador':'Viewer' ?></span>
                        <span class="profile-chip"><?= icon('check') ?> <?= $user['ativo'] ? 'Conta ativa' : 'Conta inativa' ?></span>
                    </div>
                </div>
            </div>

            <div class="profile-details">
                <div class="profile-item">
                    <div class="profile-label">Último acesso</div>
                    <div class="profile-value"><?= $user['ultimo_login'] ? date('d/m/Y H:i', strtotime($user['ultimo_login'])) : '—' ?></div>
                </div>
                <div class="profile-item">
                    <div class="profile-label">Permissões</div>
                    <div class="profile-value"><?= $user['permissoes'] === 'all' ? 'Acesso completo' : count($perms) . ' módulo(s) liberado(s)' ?></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="stitle"><?= icon('filter') ?> Módulos com Acesso</div>
            <div class="modules-list">
            <?php foreach ($MODULOS as $key => $label):
                $has = in_array($key, $perms, true) || $user['permissoes']==='all';
            ?>
                <div class="module-row">
                    <div class="left"><?= e($label) ?></div>
                    <?php if ($has): ?>
                        <span class="module-on"><?= icon('check') ?> Liberado</span>
                    <?php else: ?>
                        <span class="module-off">Sem acesso</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="stitle"><?= icon('edit') ?> Editar Perfil</div>
        <form method="post">
            <?= csrf_input() ?>
            <div class="form-group" style="margin-bottom:14px">
                <label>Nome completo <span class="req">*</span></label>
                <input type="text" name="nome" value="<?= e($_POST['nome'] ?? $user['nome']) ?>">
            </div>

            <div style="margin:18px 0 14px;padding-top:14px;border-top:1px solid var(--bdr)">
                <div style="font-size:12px;color:var(--t3);margin-bottom:12px;font-weight:600;text-transform:uppercase;letter-spacing:.8px">Alterar Senha</div>
                <div class="grid cols-2" style="gap:12px">
                    <div class="form-group">
                        <label>Senha atual</label>
                        <input type="password" name="senha_atual" placeholder="Digite sua senha atual" autocomplete="current-password">
                    </div>
                    <div class="form-group">
                        <label>Nova senha</label>
                        <input type="password" name="nova_senha" placeholder="Mínimo 6 caracteres" autocomplete="new-password">
                    </div>
                </div>
                <div class="form-group" style="margin-top:12px">
                    <label>Confirmar nova senha</label>
                    <input type="password" name="confirmar_senha" placeholder="Repita a nova senha" autocomplete="new-password">
                </div>
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <button type="submit" class="btn btn-primary"><?= icon('check') ?> Salvar Alterações</button>
                <a href="index.php" class="btn btn-ghost"><?= icon('dashboard') ?> Voltar ao painel</a>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
