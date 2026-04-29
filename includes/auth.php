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

function permission_normalize_key(string $value): string
{
    return strtolower(trim($value));
}

function permission_entry_search($node, string $module)
{
    if (!is_array($node)) {
        return null;
    }

    if (array_key_exists($module, $node)) {
        return $node[$module];
    }

    foreach ($node as $key => $value) {
        if (is_string($value) && permission_normalize_key($value) === permission_normalize_key($module)) {
            return true;
        }

        if (is_array($value)) {
            $valueModule = permission_normalize_key((string) ($value['modulo'] ?? $value['module'] ?? $value['nome'] ?? ''));
            if ($valueModule === permission_normalize_key($module)) {
                return $value;
            }

            $found = permission_entry_search($value, $module);
            if ($found !== null) {
                return $found;
            }
        }

        if (permission_normalize_key((string) $key) === permission_normalize_key($module)) {
            return $value;
        }
    }

    return null;
}

function permission_entry(string $module)
{
    $perms = $_SESSION['permissoes'] ?? [];
    if ($perms === 'all') {
        return true;
    }
    if (!is_array($perms)) {
        return null;
    }

    return permission_entry_search($perms, $module);
}

function permission_action_allowed(string $module, array $names): bool
{
    $entry = permission_entry($module);
    if ($entry === null) {
        return false;
    }

    if (is_bool($entry) || is_int($entry) || is_string($entry)) {
        return (bool) $entry;
    }

    if (!is_array($entry)) {
        return false;
    }

    $normalized = array_map('permission_normalize_key', $names);
    foreach ($entry as $key => $value) {
        $keyNorm = permission_normalize_key((string) $key);
        if (in_array($keyNorm, $normalized, true) && (bool) $value) {
            return true;
        }

        if (is_array($value)) {
            foreach ($value as $subKey => $subValue) {
                if (in_array(permission_normalize_key((string) $subKey), $normalized, true) && (bool) $subValue) {
                    return true;
                }
            }
        }
    }

    return false;
}

function is_read_only_profile(): bool
{
    $perfil = strtolower((string) ($_SESSION['perfil'] ?? ''));
    return in_array($perfil, ['viewer', 'consulta', 'readonly', 'somente_leitura'], true);
}

function canAccess(string $module): bool
{
    if (!is_logged_in()) {
        return false;
    }

    if (($_SESSION['perfil'] ?? '') === 'admin') {
        return true;
    }

    $perms = $_SESSION['permissoes'] ?? [];
    if (is_array($perms) && in_array($module, $perms, true)) {
        return true;
    }

    if (permission_action_allowed($module, ['ver', 'visualizar', 'view', 'listar', 'acessar'])) {
        return true;
    }

    // Compatibilidade: permissões de ação sem flag explícita de visualização.
    if (permission_action_allowed($module, ['criar', 'cadastrar', 'create', 'editar', 'update', 'excluir', 'delete', 'movimentar'])) {
        return true;
    }

    if ($module === 'impressao_financeiro') {
        if ((is_array($perms) && (in_array('distribuicao', $perms, true) || in_array('faturas', $perms, true)))
            || permission_action_allowed('distribuicao', ['ver', 'visualizar', 'view', 'listar', 'acessar'])
            || permission_action_allowed('faturas', ['ver', 'visualizar', 'view', 'listar', 'acessar'])) {
            return true;
        }
    }

    return false;
}


function default_landing_page(): string
{
    $perms = (array) ($_SESSION['permissoes'] ?? []);
    $priority = [
        'dashboard' => 'index.php',
        'chamados' => 'chamados_index.php',
        'computadores' => 'computadores.php',
        'celulares' => 'celulares.php',
        'faturas' => 'faturas.php',
        'fornecedores' => 'fornecedores.php',
        'distribuicao' => 'distribuicao_index.php',
        'impressao_financeiro' => 'impressao_financeiro.php',
        'usuarios' => 'usuarios.php',
    ];

    foreach ($priority as $module => $target) {
        if (canAccess($module)) {
            return $target;
        }
    }

    return 'perfil.php';
}

function is_viewer(): bool
{
    return is_logged_in() && is_read_only_profile();
}

function can_write_module(string $module): bool
{
    if (!canAccess($module)) {
        return false;
    }

    if (($_SESSION['perfil'] ?? '') === 'admin') {
        return true;
    }

    if (is_read_only_profile()) {
        return false;
    }

    $entry = permission_entry($module);
    if ($entry === null) {
        return false;
    }

    if (is_bool($entry) || is_int($entry) || is_string($entry)) {
        return (bool) $entry;
    }

    if (is_array($entry)) {
        if (permission_action_allowed($module, ['criar', 'cadastrar', 'create', 'novo', 'editar', 'update', 'alterar', 'excluir', 'delete', 'remover', 'movimentar'])) {
            return true;
        }

        // Compatibilidade: listas antigas de módulos ou blocos sem ações explícitas.
        $actionKeys = ['ver','visualizar','view','listar','acessar','criar','cadastrar','create','novo','editar','update','alterar','excluir','delete','remover','movimentar'];
        foreach ($entry as $key => $value) {
            if (is_string($value) && permission_normalize_key($value) === permission_normalize_key($module)) {
                return true;
            }
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    if (in_array(permission_normalize_key((string) $subKey), $actionKeys, true) && (bool) $subValue) {
                        return true;
                    }
                }
            }
        }

        return true;
    }

    return false;
}

function denied_redirect_target(?string $module = null, ?string $fallback = null): string
{
    if ($fallback && $fallback !== 'index.php' && $fallback !== './index.php') {
        return $fallback;
    }

    $map = [
        'dashboard' => 'index.php',
        'chamados' => 'chamados_index.php',
        'computadores' => 'computadores.php',
        'celulares' => 'celulares.php',
        'faturas' => 'faturas.php',
        'fornecedores' => 'fornecedores.php',
        'distribuicao' => 'distribuicao_index.php',
        'impressao_financeiro' => 'impressao_financeiro.php',
        'usuarios' => 'usuarios.php',
    ];

    if ($module && canAccess($module) && isset($map[$module])) {
        return $map[$module];
    }

    return default_landing_page();
}

function require_write_access(string $module, ?string $redirectTo = null): void
{
    requireAccess($module);
    if (!can_write_module($module)) {
        flash('Você não tem permissão para esta ação.', 'error');
        redirect(denied_redirect_target($module, $redirectTo));
    }
}

function requireAccess(string $module): void
{
    require_login();

    if (!canAccess($module)) {
        flash('Você não tem permissão para acessar este módulo.', 'error');
        redirect(denied_redirect_target($module));
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
        'ativos_historico' => resolveTipoModule($_GET['tipo'] ?? 'computador'),
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
        'chamados_index' => 'chamados',
        'chamados_visualizar' => 'chamados',
        'chamados_responder' => 'chamados',
        'chamados_atribuir' => 'chamados',
        'chamados_status' => 'chamados',
        'chamados_relatorios' => 'chamados',
        'chamados_categorias' => 'chamados',
        'perfil' => null,
    ];

    if (isset($moduleMap[$page]) && $page !== 'index' && $moduleMap[$page]) {
        requireAccess($moduleMap[$page]);
    }
}
