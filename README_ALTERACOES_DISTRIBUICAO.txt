<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    redirect('index.php');
}

$erro = '';
$msg  = '';

if (isset($_GET['logout']))  $msg = 'Sessão encerrada com sucesso.';
if (isset($_GET['timeout'])) $msg = 'Sua sessão expirou. Faça login novamente.';

if (request_is_post()) {
    validate_csrf_or_fail($_POST['csrf_token'] ?? null);

    $user = trim((string) post('usuario', ''));
    $pass = trim((string) post('senha', ''));
    $ip = client_ip();

    if ($user === '' || $pass === '') {
        $erro = 'Informe usuário e senha.';
    } else {
        $db = getDB();
        $limitWindowMinutes = 10;
        $maxAttempts = 5;

        $block = $db->prepare("
            SELECT COUNT(*)
            FROM login_tentativas
            WHERE sucesso = 0
              AND (usuario = :usuario OR ip = :ip)
              AND data_tentativa >= (NOW() - INTERVAL {$limitWindowMinutes} MINUTE)
        ");
        $block->execute([':usuario' => $user, ':ip' => $ip]);
        $attempts = (int) $block->fetchColumn();

        if ($attempts >= $maxAttempts) {
            audit_log('bloqueio_login', 'usuarios', null, ['usuario_informado' => $user]);
            $erro = 'Muitas tentativas de login. Aguarde 10 minutos e tente novamente.';
        } else {
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE usuario = :u AND ativo = 1 LIMIT 1");
            $stmt->execute([':u' => $user]);
            $row = $stmt->fetch();

            if ($row && password_verify($pass, $row['senha'])) {
                session_regenerate_id(true);
                $_SESSION['logado']     = true;
                $_SESSION['user_id']    = (int) $row['id'];
                $_SESSION['usuario']    = $row['usuario'];
                $_SESSION['nome']       = $row['nome'];
                $_SESSION['perfil']     = $row['perfil'];
                $_SESSION['permissoes'] = $row['permissoes'] === 'all'
                    ? all_module_keys()
                    : (json_decode($row['permissoes'] ?? '[]', true) ?: []);

                // Compatibilidade de sessão para usuários antigos:
                // se tiver Distribuição ou Faturas, também habilita Financeiro impressão.
                if (in_array('distribuicao', (array) $_SESSION['permissoes'], true) || in_array('faturas', (array) $_SESSION['permissoes'], true)) {
                    if (!in_array('impressao_financeiro', (array) $_SESSION['permissoes'], true)) {
                        $_SESSION['permissoes'][] = 'impressao_financeiro';
                    }
                }

                $_SESSION['login_time'] = time();

                $db->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id")
                   ->execute([':id' => $row['id']]);

                $db->prepare("INSERT INTO login_tentativas (usuario, ip, sucesso) VALUES (:usuario, :ip, 1)")
                   ->execute([':usuario' => $user, ':ip' => $ip]);

                audit_log('login_sucesso', 'usuarios', (int) $row['id'], [
                    'usuario' => $row['usuario'],
                    'perfil'  => $row['perfil'],
                ], (int) $row['id']);

                redirect('index.php');
            }

            $db->prepare("INSERT INTO login_tentativas (usuario, ip, sucesso) VALUES (:usuario, :ip, 0)")
               ->execute([':usuario' => $user, ':ip' => $ip]);

            audit_log('login_falha', 'usuarios', null, ['usuario_informado' => $user]);

            $remaining = max(0, $maxAttempts - ($attempts + 1));
            $erro = $remaining > 0
                ? "Usuário ou senha inválidos. {$remaining} tentativa(s) restante(s) nesta janela."
                : 'Muitas tentativas de login. Aguarde 10 minutos e tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CSF Digital — Acesso</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/app.css?v=<?= @filemtime(__DIR__ . '/assets/css/app.css') ?: time() ?>">

</head>
<body class="login-page">
<div class="login-shell">
<div class="box">
    <div class="logo-area">
        <?php if (file_exists(__DIR__.'/logo.png')): ?>
            <img src="logo.png" alt="CSF Digital">
        <?php else: ?>
        <div class="fb"><div class="lm">🖥️</div><div class="lt">CSF Digital</div></div>
        <?php endif; ?>
        <span class="sub">Gestão de TI</span>
    </div>

    <?php if ($erro): ?><div class="err"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
    <?php if ($msg):  ?><div class="info"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <form method="post" autocomplete="off">
        <?= csrf_input() ?>
        <div class="fg">
            <label>Usuário</label>
            <input type="text" name="usuario" value="<?= htmlspecialchars($_POST['usuario']??'') ?>" placeholder="Digite seu usuário" autocomplete="username" autofocus>
        </div>
        <div class="fg">
            <label>Senha</label>
            <div class="iw">
                <input type="password" id="pwd" name="senha" placeholder="••••••••" autocomplete="current-password">
                <button type="button" class="eye" onclick="togglePwd()">
                    <svg id="eyeico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>
        </div>
        <button type="submit" class="btn-in">Entrar</button>
    </form>
    <div class="ft">© <?= date('Y') ?> CSF Digital</div>
</div>
</div>
<script>
function togglePwd(){
    const i=document.getElementById('pwd'),e=document.getElementById('eyeico');
    if(i.type==='password'){i.type='text';e.innerHTML='<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';}
    else{i.type='password';e.innerHTML='<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';}
}
</script>
</body>
</html>
