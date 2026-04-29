<?php

function distribuicao_status_options(): array
{
    return [
        'Ativa',
        'Inativa',
        'Desinstalada',
        'Backup',
        'Troca técnica',
        'Recolhida',
    ];
}

function distribuicao_monitoramento_options(): array
{
    return [
        'Online',
        'Offline',
        'Pendente',
        'Sem comunicação',
    ];
}

function distribuicao_tipo_movimentacao_options(): array
{
    return [
        'instalacao'      => 'Instalação',
        'troca_tecnica'   => 'Troca técnica',
        'remanejamento'   => 'Remanejamento',
        'desinstalacao'   => 'Desinstalação',
        'inativacao'      => 'Inativação',
        'reativacao'      => 'Reativação',
        'atualizacao'     => 'Atualização cadastral',
    ];
}

function distribuicao_all_clients(): array
{
    $db = getDB();
    $stmt = $db->query("SELECT * FROM distribuicao_clientes WHERE ativo = 1 ORDER BY nome ASC");
    return $stmt->fetchAll() ?: [];
}

function distribuicao_normalize_empresa(?string $empresa): string
{
    $empresa = trim((string) $empresa);
    if ($empresa === '') {
        return '';
    }
    $empresa = preg_replace('/\s+/', ' ', $empresa);
    return mb_strtoupper($empresa, 'UTF-8');
}

function distribuicao_modelo_cobranca_label(?string $modelo): string
{
    $modelo = trim((string) $modelo);
    if ($modelo === 'com_franquia') {
        return 'Com franquia';
    }
    return 'Sem franquia';
}

function distribuicao_cliente_modelo_cobranca(int $clienteId): string
{
    static $cache = [];
    if (isset($cache[$clienteId])) {
        return $cache[$clienteId];
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT modelo_cobranca FROM distribuicao_clientes WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $clienteId]);
    $modelo = (string) ($stmt->fetchColumn() ?: 'sem_franquia');
    if ($modelo !== 'com_franquia') {
        $modelo = 'sem_franquia';
    }
    return $cache[$clienteId] = $modelo;
}


function distribuicao_client_permission_table(): ?string
{
    $db = getDB();
    if (table_exists($db, 'usuarios_clientes')) {
        return 'usuarios_clientes';
    }
    if (table_exists($db, 'distribuicao_usuarios_clientes')) {
        return 'distribuicao_usuarios_clientes';
    }
    return null;
}

function distribuicao_company_permission_table(): ?string
{
    $db = getDB();
    return table_exists($db, 'usuarios_clientes_empresas') ? 'usuarios_clientes_empresas' : null;
}

function distribuicao_user_client_permissions(int $userId): array
{
    static $cache = [];
    if (isset($cache[$userId])) {
        return $cache[$userId];
    }

    $table = distribuicao_client_permission_table();
    if (!$table) {
        return $cache[$userId] = [];
    }

    $db = getDB();
    if ($table === 'usuarios_clientes') {
        $stmt = $db->prepare(
            "SELECT uc.*, dc.nome AS cliente_nome,
                    uc.pode_ver AS pode_visualizar,
                    uc.pode_editar,
                    uc.pode_importar,
                    uc.pode_importar AS pode_cadastrar,
                    uc.pode_editar AS pode_movimentar
             FROM usuarios_clientes uc
             INNER JOIN distribuicao_clientes dc ON dc.id = uc.cliente_id
             WHERE uc.usuario_id = :usuario_id AND dc.ativo = 1
             ORDER BY dc.nome ASC"
        );
    } else {
        $stmt = $db->prepare(
            "SELECT duc.*, dc.nome AS cliente_nome
             FROM distribuicao_usuarios_clientes duc
             INNER JOIN distribuicao_clientes dc ON dc.id = duc.cliente_id
             WHERE duc.usuario_id = :usuario_id AND dc.ativo = 1
             ORDER BY dc.nome ASC"
        );
    }
    $stmt->execute([':usuario_id' => $userId]);
    $rows = $stmt->fetchAll() ?: [];
    $map = [];
    foreach ($rows as $row) {
        $map[(int) $row['cliente_id']] = $row;
    }
    return $cache[$userId] = $map;
}

function distribuicao_user_company_permissions(int $userId): array
{
    static $cache = [];
    if (isset($cache[$userId])) {
        return $cache[$userId];
    }
    if (!distribuicao_company_permission_table()) {
        return $cache[$userId] = [];
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM usuarios_clientes_empresas WHERE usuario_id = :usuario_id");
    $stmt->execute([':usuario_id' => $userId]);
    $rows = $stmt->fetchAll() ?: [];
    $map = [];
    foreach ($rows as $row) {
        $clienteId = (int) $row['cliente_id'];
        $empresa = distribuicao_normalize_empresa($row['empresa'] ?? '');
        if ($empresa === '') {
            continue;
        }
        $map[$clienteId][$empresa] = $row;
    }
    return $cache[$userId] = $map;
}

function distribuicao_allowed_client_ids(?string $action = 'visualizar'): array
{
    if (($_SESSION['perfil'] ?? '') === 'admin') {
        return array_map(static function ($row) {
            return (int) $row['id'];
        }, distribuicao_all_clients());
    }

    $userId = current_user_id();
    if (!$userId) {
        return [];
    }

    $permissions = distribuicao_user_client_permissions($userId);
    $ids = [];
    foreach ($permissions as $clienteId => $row) {
        $allowed = ($action === 'visualizar')
            ? !empty($row['pode_visualizar'])
            : (!empty($row['pode_editar']) || !empty($row['pode_importar']) || !empty($row['pode_cadastrar']) || !empty($row['pode_movimentar']));
        if ($allowed) {
            $ids[] = (int) $clienteId;
        }
    }
    return $ids;
}

function distribuicao_accessible_clients(?string $action = 'visualizar'): array
{
    $all = distribuicao_all_clients();
    if (($_SESSION['perfil'] ?? '') === 'admin') {
        return $all;
    }

    $allowed = array_flip(distribuicao_allowed_client_ids($action));
    return array_values(array_filter($all, static function ($row) use ($allowed) {
        return isset($allowed[(int) $row['id']]);
    }));
}

function distribuicao_allowed_companies_map(?string $action = 'visualizar'): array
{
    if (($_SESSION['perfil'] ?? '') === 'admin') {
        return [];
    }

    $userId = current_user_id();
    if (!$userId) {
        return [];
    }

    $rows = distribuicao_user_company_permissions($userId);
    $map = [];
    foreach ($rows as $clienteId => $items) {
        foreach ($items as $empresa => $row) {
            $allowed = ($action === 'visualizar')
                ? !empty($row['pode_ver'])
                : (!empty($row['pode_editar']) || !empty($row['pode_importar']));
            if ($allowed) {
                $map[(int) $clienteId][] = $empresa;
            }
        }
    }
    return $map;
}

function distribuicao_can_access_cliente(int $clienteId, string $action = 'visualizar'): bool
{
    if ($clienteId <= 0) {
        return false;
    }
    if (($_SESSION['perfil'] ?? '') === 'admin') {
        return true;
    }
    return in_array($clienteId, distribuicao_allowed_client_ids($action), true);
}

function distribuicao_can_access_empresa(int $clienteId, ?string $empresa, string $action = 'visualizar'): bool
{
    if (($_SESSION['perfil'] ?? '') === 'admin') {
        return true;
    }
    if (!distribuicao_can_access_cliente($clienteId, $action)) {
        return false;
    }
    $map = distribuicao_allowed_companies_map($action);
    if (empty($map[$clienteId])) {
        return true;
    }
    return in_array(distribuicao_normalize_empresa($empresa), $map[$clienteId], true);
}

function distribuicao_require_cliente_access(int $clienteId, string $action = 'visualizar'): void
{
    if (!distribuicao_can_access_cliente($clienteId, $action)) {
        flash('Você não tem permissão para acessar esse cliente na distribuição.', 'error');
        redirect('distribuicao_index.php');
    }
}

function distribuicao_company_options_by_client(): array
{
    $db = getDB();
    if (!table_exists($db, 'distribuicao_equipamentos') || !column_exists($db, 'distribuicao_equipamentos', 'empresa')) {
        return [];
    }
    $stmt = $db->query("SELECT cliente_id, empresa FROM distribuicao_equipamentos WHERE empresa IS NOT NULL AND empresa <> '' GROUP BY cliente_id, empresa ORDER BY empresa ASC");
    $out = [];
    foreach (($stmt->fetchAll() ?: []) as $row) {
        $out[(int) $row['cliente_id']][] = $row['empresa'];
    }
    return $out;
}

function distribuicao_parse_allowed_clients_from_post(): array
{
    $raw = $_POST['distribuicao_clientes'] ?? [];
    if (!is_array($raw)) {
        return [];
    }
    $result = [];
    foreach ($raw as $clienteId => $actions) {
        $clienteId = (int) $clienteId;
        if ($clienteId <= 0) {
            continue;
        }
        $actions = is_array($actions) ? $actions : [];
        $result[$clienteId] = [
            'visualizar' => in_array('visualizar', $actions, true) ? 1 : 0,
            'cadastrar'  => in_array('cadastrar', $actions, true) ? 1 : 0,
            'editar'     => in_array('editar', $actions, true) ? 1 : 0,
            'movimentar' => in_array('movimentar', $actions, true) ? 1 : 0,
        ];
    }
    return $result;
}

function distribuicao_parse_allowed_companies_from_post(): array
{
    $raw = $_POST['distribuicao_empresas'] ?? [];
    if (!is_array($raw)) {
        return [];
    }
    $result = [];
    foreach ($raw as $clienteId => $empresas) {
        $clienteId = (int) $clienteId;
        if ($clienteId <= 0 || !is_array($empresas)) {
            continue;
        }
        foreach ($empresas as $empresa) {
            $empresa = distribuicao_normalize_empresa($empresa);
            if ($empresa === '') {
                continue;
            }
            $result[$clienteId][] = $empresa;
        }
    }
    return $result;
}

function distribuicao_save_user_client_permissions(int $userId, array $permissions, array $companies = []): void
{
    $db = getDB();

    if (table_exists($db, 'usuarios_clientes')) {
        $db->prepare("DELETE FROM usuarios_clientes WHERE usuario_id = :usuario_id")
           ->execute([':usuario_id' => $userId]);
        $stmt = $db->prepare("INSERT INTO usuarios_clientes (usuario_id, cliente_id, pode_ver, pode_editar, pode_importar) VALUES (:usuario_id, :cliente_id, :pode_ver, :pode_editar, :pode_importar)");
        foreach ($permissions as $clienteId => $perm) {
            if (empty($perm['visualizar']) && empty($perm['editar']) && empty($perm['cadastrar']) && empty($perm['movimentar'])) {
                continue;
            }
            $stmt->execute([
                ':usuario_id' => $userId,
                ':cliente_id' => $clienteId,
                ':pode_ver' => !empty($perm['visualizar']) ? 1 : 0,
                ':pode_editar' => (!empty($perm['editar']) || !empty($perm['movimentar'])) ? 1 : 0,
                ':pode_importar' => !empty($perm['cadastrar']) ? 1 : 0,
            ]);
        }
    }

    if (table_exists($db, 'distribuicao_usuarios_clientes')) {
        $db->prepare("DELETE FROM distribuicao_usuarios_clientes WHERE usuario_id = :usuario_id")
           ->execute([':usuario_id' => $userId]);
        $stmt = $db->prepare("INSERT INTO distribuicao_usuarios_clientes (usuario_id, cliente_id, pode_visualizar, pode_cadastrar, pode_editar, pode_movimentar, created_at, updated_at) VALUES (:usuario_id, :cliente_id, :pode_visualizar, :pode_cadastrar, :pode_editar, :pode_movimentar, NOW(), NOW())");
        foreach ($permissions as $clienteId => $perm) {
            if (empty($perm['visualizar']) && empty($perm['editar']) && empty($perm['cadastrar']) && empty($perm['movimentar'])) {
                continue;
            }
            $stmt->execute([
                ':usuario_id' => $userId,
                ':cliente_id' => $clienteId,
                ':pode_visualizar' => !empty($perm['visualizar']) ? 1 : 0,
                ':pode_cadastrar' => !empty($perm['cadastrar']) ? 1 : 0,
                ':pode_editar' => !empty($perm['editar']) ? 1 : 0,
                ':pode_movimentar' => !empty($perm['movimentar']) ? 1 : 0,
            ]);
        }
    }

    if (table_exists($db, 'usuarios_clientes_empresas')) {
        $db->prepare("DELETE FROM usuarios_clientes_empresas WHERE usuario_id = :usuario_id")
           ->execute([':usuario_id' => $userId]);
        $stmt = $db->prepare("INSERT INTO usuarios_clientes_empresas (usuario_id, cliente_id, empresa, pode_ver, pode_editar, pode_importar) VALUES (:usuario_id, :cliente_id, :empresa, :pode_ver, :pode_editar, :pode_importar)");
        foreach ($companies as $clienteId => $list) {
            $perm = $permissions[$clienteId] ?? ['visualizar' => 1, 'editar' => 0, 'cadastrar' => 0, 'movimentar' => 0];
            foreach ($list as $empresa) {
                $stmt->execute([
                    ':usuario_id' => $userId,
                    ':cliente_id' => $clienteId,
                    ':empresa' => $empresa,
                    ':pode_ver' => !empty($perm['visualizar']) ? 1 : 0,
                    ':pode_editar' => (!empty($perm['editar']) || !empty($perm['movimentar'])) ? 1 : 0,
                    ':pode_importar' => !empty($perm['cadastrar']) ? 1 : 0,
                ]);
            }
        }
    }
}

function distribuicao_user_client_permissions_for_form(int $userId): array
{
    $rows = distribuicao_user_client_permissions($userId);
    $out = [];
    foreach ($rows as $clienteId => $row) {
        $out[(int) $clienteId] = [
            'visualizar' => !empty($row['pode_visualizar']),
            'cadastrar' => !empty($row['pode_cadastrar']) || !empty($row['pode_importar']),
            'editar' => !empty($row['pode_editar']),
            'movimentar' => !empty($row['pode_movimentar']) || !empty($row['pode_editar']),
        ];
    }
    return $out;
}

function distribuicao_user_company_permissions_for_form(int $userId): array
{
    $rows = distribuicao_user_company_permissions($userId);
    $out = [];
    foreach ($rows as $clienteId => $items) {
        $out[(int) $clienteId] = array_keys($items);
    }
    return $out;
}

function distribuicao_fetch_cliente(int $clienteId): ?array
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM distribuicao_clientes WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $clienteId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function distribuicao_fetch_equipamento(int $id): ?array
{
    $db = getDB();
    $stmt = $db->prepare(
        "SELECT e.*, c.nome AS cliente_nome
         FROM distribuicao_equipamentos e
         INNER JOIN distribuicao_clientes c ON c.id = e.cliente_id
         WHERE e.id = :id
         LIMIT 1"
    );
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function distribuicao_record_movimentacao(array $data): void
{
    $db = getDB();
    $stmt = $db->prepare(
        "INSERT INTO distribuicao_movimentacoes (
            equipamento_id, cliente_id, tipo_movimentacao, data_movimentacao,
            origem, destino, serie_antiga, serie_nova, modelo_antigo, modelo_novo,
            monitoramento_anterior, monitoramento_novo, status_anterior, status_novo,
            motivo, tecnico_responsavel, observacoes, usuario_id, created_at
        ) VALUES (
            :equipamento_id, :cliente_id, :tipo_movimentacao, :data_movimentacao,
            :origem, :destino, :serie_antiga, :serie_nova, :modelo_antigo, :modelo_novo,
            :monitoramento_anterior, :monitoramento_novo, :status_anterior, :status_novo,
            :motivo, :tecnico_responsavel, :observacoes, :usuario_id, NOW()
        )"
    );
    $stmt->execute([
        ':equipamento_id' => $data['equipamento_id'] ?: null,
        ':cliente_id' => $data['cliente_id'] ?: null,
        ':tipo_movimentacao' => $data['tipo_movimentacao'] ?? 'atualizacao',
        ':data_movimentacao' => $data['data_movimentacao'] ?: date('Y-m-d'),
        ':origem' => $data['origem'] ?: null,
        ':destino' => $data['destino'] ?: null,
        ':serie_antiga' => $data['serie_antiga'] ?: null,
        ':serie_nova' => $data['serie_nova'] ?: null,
        ':modelo_antigo' => $data['modelo_antigo'] ?: null,
        ':modelo_novo' => $data['modelo_novo'] ?: null,
        ':monitoramento_anterior' => $data['monitoramento_anterior'] ?: null,
        ':monitoramento_novo' => $data['monitoramento_novo'] ?: null,
        ':status_anterior' => $data['status_anterior'] ?: null,
        ':status_novo' => $data['status_novo'] ?: null,
        ':motivo' => $data['motivo'] ?: null,
        ':tecnico_responsavel' => $data['tecnico_responsavel'] ?: null,
        ':observacoes' => $data['observacoes'] ?: null,
        ':usuario_id' => current_user_id(),
    ]);
}
