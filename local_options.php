<?php

function is_logged_in(): bool
{
    return isset($_SESSION['logado']) && $_SESSION['logado'] === true && !empty($_SESSION['user_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        redirect('login.php');
    }

    if (isset($_SESSION['login_time']) && (time() - (int) $_SESSION['login_time']) > 28800) {
        audit_log('timeout', 'sessao', current_user_id(), ['usuario' => $_SESSION['usuario'] ?? null]);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        redirect('login.php?timeout=1');
    }

    $_SESSION['login_time'] = time();
}

function logout_user(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }

    redirect('login.php?logout=1');
}

function canAccess(string $module): bool
{
    if (!is_logged_in()) {
        return false;
    }

    if (($_SESSION['perfil'] ?? '') === 'admin') {
        return true;
    }

    $perms = (array) ($_SESSION['permissoes'] ?? []);
    if (in_array($module, $perms, true)) {
        return true;
    }

    // Compatibilidade entre permissões antigas e novas do módulo de impressão.
    if ($module === 'impressao_financeiro') {
        if (in_array('distribuicao', $perms, true) || in_array('faturas', $perms, true)) {
            return true;
        }
    }

    return false;
}


function is_viewer(): bool
{
    return is_logged_in() && (($_SESSION['perfil'] ?? '') === 'viewer');
}

function can_write_module(string $module): bool
{
    return canAccess($module) && !is_viewer();
}

function require_write_access(string $module, ?string $redirectTo = null): void
{
    requireAccess($module);
    if (is_viewer()) {
        flash('Seu perfil é somente visualização.', 'error');
        redirect($redirectTo ?: 'index.php');
    }
}

function requireAccess(string $module): void
{
    require_login();

    if (!canAccess($module)) {
        flash('Você não tem permissão para acessar este módulo.', 'error');
        redirect('index.php?denied=1');
    }
}

function resolveTipoModule(?string $tipo): ?string
{
    $map = [
        'computador' => 'computadores',
        'celular' => 'celulares',
    ];

    return $map[$tipo ?? ''] ?? null;
}

function guard_current_page_access(): void
{
    $page = basename($_SERVER['PHP_SELF'] ?? '', '.php');
    $moduleMap = [
        'index' => 'dashboard',
        'computadores' => 'computadores',
        'celulares' => 'celulares',
        'cadastrar' => resolveTipoModule($_GET['tipo'] ?? $_POST['tipo_dispositivo'] ?? 'computador'),
        'editar' => resolveTipoModule($_GET['tipo'] ?? $_POST['tipo'] ?? ''),
        'excluir' => resolveTipoModule($_POST['tipo'] ?? $_GET['tipo'] ?? ''),
        'faturas' => 'faturas',
        'fornecedores' => 'fornecedores',
        'usuarios' => 'usuarios',
        'distribuicao_index' => 'distribuicao',
        'distribuicao_clientes' => 'distribuicao',
        'distribuicao_cadastrar' => 'distribuicao',
        'distribuicao_editar' => 'distribuicao',
        'distribuicao_movimentacoes' => 'distribuicao',
        'distribuicao_troca_tecnica' => 'distribuicao',
        'distribuicao_importar_monitoramento' => 'distribuicao',
        'distribuicao_importar_base' => 'distribuicao',
        'impressao_financeiro' => 'impressao_financeiro',
        'impressao_financeiro_importar' => 'impressao_financeiro',
        'perfil' => null,
    ];

    if (isset($moduleMap[$page]) && $page !== 'index' && $moduleMap[$page]) {
        requireAccess($moduleMap[$page]);
    }
}
