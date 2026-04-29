<?php

function chamados_table_has_column(string $table, string $column): bool
{
    $stmt = getDB()->prepare("SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = :table
          AND COLUMN_NAME = :column
        LIMIT 1");
    $stmt->execute([
        ':table' => $table,
        ':column' => $column,
    ]);
    return (bool) $stmt->fetchColumn();
}

function setupChamadosDB(): void
{
    $pdo = getDB();

    $pdo->exec("CREATE TABLE IF NOT EXISTS hesk_categorias (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        descricao VARCHAR(190) DEFAULT NULL,
        ativo TINYINT(1) NOT NULL DEFAULT 1,
        ordem INT NOT NULL DEFAULT 0,
        criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS hesk_chamados (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        protocolo VARCHAR(30) NOT NULL UNIQUE,
        nome_solicitante VARCHAR(120) NOT NULL,
        email_solicitante VARCHAR(150) NOT NULL,
        telefone_solicitante VARCHAR(30) DEFAULT NULL,
        categoria_id INT UNSIGNED DEFAULT NULL,
        assunto VARCHAR(150) NOT NULL,
        descricao TEXT NOT NULL,
        prioridade VARCHAR(20) NOT NULL DEFAULT 'media',
        status VARCHAR(30) NOT NULL DEFAULT 'aberto',
        tecnico_id INT UNSIGNED DEFAULT NULL,
        equipamento_tipo VARCHAR(30) DEFAULT NULL,
        equipamento_id INT UNSIGNED DEFAULT NULL,
        origem VARCHAR(30) NOT NULL DEFAULT 'portal_publico',
        ultimo_autor VARCHAR(30) NOT NULL DEFAULT 'cliente',
        ultima_interacao_em DATETIME DEFAULT NULL,
        criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        fechado_em DATETIME DEFAULT NULL,
        INDEX idx_hesk_status (status),
        INDEX idx_hesk_email (email_solicitante),
        INDEX idx_hesk_categoria (categoria_id),
        INDEX idx_hesk_tecnico (tecnico_id),
        INDEX idx_hesk_criado (criado_em),
        CONSTRAINT fk_hesk_categoria FOREIGN KEY (categoria_id) REFERENCES hesk_categorias(id) ON DELETE SET NULL,
        CONSTRAINT fk_hesk_tecnico FOREIGN KEY (tecnico_id) REFERENCES usuarios(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS hesk_mensagens (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        chamado_id INT UNSIGNED NOT NULL,
        usuario_id INT UNSIGNED DEFAULT NULL,
        nome_autor VARCHAR(120) DEFAULT NULL,
        email_autor VARCHAR(150) DEFAULT NULL,
        mensagem TEXT NOT NULL,
        origem VARCHAR(20) NOT NULL DEFAULT 'cliente',
        privado TINYINT(1) NOT NULL DEFAULT 0,
        criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_hesk_mensagem_chamado (chamado_id),
        INDEX idx_hesk_mensagem_usuario (usuario_id),
        CONSTRAINT fk_hesk_mensagem_chamado FOREIGN KEY (chamado_id) REFERENCES hesk_chamados(id) ON DELETE CASCADE,
        CONSTRAINT fk_hesk_mensagem_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS hesk_anexos (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        chamado_id INT UNSIGNED NOT NULL,
        mensagem_id INT UNSIGNED DEFAULT NULL,
        nome_original VARCHAR(255) NOT NULL,
        nome_arquivo VARCHAR(255) NOT NULL,
        caminho VARCHAR(255) NOT NULL,
        tamanho INT UNSIGNED DEFAULT NULL,
        mime VARCHAR(120) DEFAULT NULL,
        criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_hesk_anexos_chamado (chamado_id),
        CONSTRAINT fk_hesk_anexo_chamado FOREIGN KEY (chamado_id) REFERENCES hesk_chamados(id) ON DELETE CASCADE,
        CONSTRAINT fk_hesk_anexo_mensagem FOREIGN KEY (mensagem_id) REFERENCES hesk_mensagens(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS ativos_historico (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        ativo_tipo VARCHAR(30) NOT NULL,
        ativo_id INT UNSIGNED NOT NULL,
        chamado_id INT UNSIGNED DEFAULT NULL,
        tipo_evento VARCHAR(40) NOT NULL,
        titulo VARCHAR(160) NOT NULL,
        descricao TEXT DEFAULT NULL,
        usuario_id INT UNSIGNED DEFAULT NULL,
        criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ativo_evento (ativo_tipo, ativo_id, criado_em),
        INDEX idx_ativo_chamado (chamado_id),
        CONSTRAINT fk_ativos_hist_chamado FOREIGN KEY (chamado_id) REFERENCES hesk_chamados(id) ON DELETE SET NULL,
        CONSTRAINT fk_ativos_hist_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS ativo_usuario_historico (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        ativo_tipo VARCHAR(30) NOT NULL,
        ativo_id INT UNSIGNED NOT NULL,
        nome_usuario VARCHAR(150) NOT NULL,
        setor VARCHAR(150) DEFAULT NULL,
        data_inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        data_fim DATETIME DEFAULT NULL,
        observacao VARCHAR(190) DEFAULT NULL,
        criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ativo_usuario_aberto (ativo_tipo, ativo_id, data_fim),
        INDEX idx_ativo_usuario_hist (ativo_tipo, ativo_id, data_inicio)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    if (!chamados_table_has_column('hesk_categorias', 'sla_horas')) {
        $pdo->exec('ALTER TABLE hesk_categorias ADD COLUMN sla_horas INT UNSIGNED NOT NULL DEFAULT 24 AFTER ordem');
    }
    if (!chamados_table_has_column('hesk_categorias', 'prioridade_padrao')) {
        $pdo->exec("ALTER TABLE hesk_categorias ADD COLUMN prioridade_padrao VARCHAR(20) NOT NULL DEFAULT 'media' AFTER sla_horas");
    }
    $pdo->exec("UPDATE hesk_categorias SET sla_horas = 24 WHERE sla_horas IS NULL OR sla_horas <= 0");
    $pdo->exec("UPDATE hesk_categorias SET prioridade_padrao = 'media' WHERE prioridade_padrao IS NULL OR prioridade_padrao = '' OR prioridade_padrao NOT IN ('baixa','media','alta','critica')");

    $count = (int) $pdo->query('SELECT COUNT(*) FROM hesk_categorias')->fetchColumn();
    if ($count === 0) {
        $seed = [
            ['Computador', 'Desktop, notebook e periféricos', 1, 24, 'media'],
            ['Celular', 'Linha móvel e smartphones', 2, 24, 'media'],
            ['Impressora', 'Impressão, tonner e monitoramento', 3, 24, 'media'],
            ['Rede/Internet', 'Conectividade e acesso', 4, 8, 'alta'],
            ['Acesso/Sistema', 'Usuário, senha e permissões', 5, 8, 'alta'],
            ['Outros', 'Demandas gerais', 99, 24, 'media'],
        ];
        $stmt = $pdo->prepare('INSERT INTO hesk_categorias (nome, descricao, ordem, sla_horas, prioridade_padrao) VALUES (:nome, :descricao, :ordem, :sla_horas, :prioridade_padrao)');
        foreach ($seed as [$nome, $descricao, $ordem, $slaHoras, $prioridadePadrao]) {
            $stmt->execute([':nome' => $nome, ':descricao' => $descricao, ':ordem' => $ordem, ':sla_horas' => $slaHoras, ':prioridade_padrao' => $prioridadePadrao]);
        }
    }
}
setupChamadosDB();

function chamados_categorias_ativas(): array
{
    return getDB()->query('SELECT * FROM hesk_categorias WHERE ativo = 1 ORDER BY ordem ASC, nome ASC')->fetchAll();
}

function chamados_categorias_todas(): array
{
    return getDB()->query('SELECT * FROM hesk_categorias ORDER BY ativo DESC, ordem ASC, nome ASC')->fetchAll();
}

function chamados_categoria_salvar(array $data, ?int $id = null): int
{
    $pdo = getDB();
    if ($id) {
        $stmt = $pdo->prepare('UPDATE hesk_categorias SET nome = :nome, descricao = :descricao, ordem = :ordem, sla_horas = :sla_horas, prioridade_padrao = :prioridade_padrao, ativo = :ativo WHERE id = :id');
        $stmt->execute([
            ':nome' => $data['nome'],
            ':descricao' => $data['descricao'] ?: null,
            ':ordem' => (int) $data['ordem'],
            ':sla_horas' => max(1, (int) $data['sla_horas']),
            ':prioridade_padrao' => chamados_normalizar_prioridade((string) ($data['prioridade_padrao'] ?? 'media')),
            ':prioridade_padrao' => chamados_normalizar_prioridade((string) ($data['prioridade_padrao'] ?? 'media')),
        ':ativo' => !empty($data['ativo']) ? 1 : 0,
            ':id' => $id,
        ]);
        return $id;
    }

    $stmt = $pdo->prepare('INSERT INTO hesk_categorias (nome, descricao, ordem, sla_horas, prioridade_padrao, ativo) VALUES (:nome, :descricao, :ordem, :sla_horas, :prioridade_padrao, :ativo)');
    $stmt->execute([
        ':nome' => $data['nome'],
        ':descricao' => $data['descricao'] ?: null,
        ':ordem' => (int) $data['ordem'],
        ':sla_horas' => max(1, (int) $data['sla_horas']),
        ':ativo' => !empty($data['ativo']) ? 1 : 0,
    ]);
    return (int) $pdo->lastInsertId();
}

function chamados_categoria_buscar(int $id): ?array
{
    $stmt = getDB()->prepare('SELECT * FROM hesk_categorias WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function chamados_categoria_pode_excluir(int $id): bool
{
    $stmt = getDB()->prepare('SELECT COUNT(*) FROM hesk_chamados WHERE categoria_id = :id');
    $stmt->execute([':id' => $id]);
    return ((int) $stmt->fetchColumn()) === 0;
}

function chamados_categoria_excluir(int $id): bool
{
    if (!chamados_categoria_pode_excluir($id)) {
        return false;
    }
    $stmt = getDB()->prepare('DELETE FROM hesk_categorias WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    return $stmt->rowCount() > 0;
}

function chamados_sla_status(array $chamado): string
{
    $prazo = chamados_prazo_timestamp($chamado);
    if ($prazo === null) {
        return 'sem_sla';
    }

    $fim = chamados_marco_final_timestamp($chamado);
    if ($fim !== null) {
        return $fim <= $prazo ? 'no_prazo' : 'atrasado';
    }

    return time() <= $prazo ? 'no_prazo' : 'atrasado';
}

function chamados_sla_label(string $status): string
{
    switch ($status) {
        case 'no_prazo':
            return 'No prazo';
        case 'atrasado':
            return 'Atrasado';
        default:
            return 'Sem SLA';
    }
}

function chamados_sla_badge_class(string $status): string
{
    switch ($status) {
        case 'no_prazo':
            return 'b-success';
        case 'atrasado':
            return 'b-danger';
        default:
            return 'b-neutral';
    }
}

function chamados_status_labels(): array
{
    return [
        'aberto' => 'Aberto',
        'em_andamento' => 'Em andamento',
        'aguardando_cliente' => 'Aguardando cliente',
        'resolvido' => 'Resolvido',
        'fechado' => 'Fechado',
    ];
}

function chamados_prioridade_labels(): array
{
    return [
        'baixa' => 'Baixa',
        'media' => 'Média',
        'alta' => 'Alta',
        'critica' => 'Crítica',
    ];
}

function chamados_normalizar_prioridade(string $prioridade): string
{
    $prioridade = strtolower(trim($prioridade));
    $labels = chamados_prioridade_labels();
    return isset($labels[$prioridade]) ? $prioridade : 'media';
}

function chamados_categoria_prioridade(int $categoriaId): string
{
    if ($categoriaId <= 0) {
        return 'media';
    }
    $categoria = chamados_categoria_buscar($categoriaId);
    if (!$categoria) {
        return 'media';
    }
    return chamados_normalizar_prioridade((string) ($categoria['prioridade_padrao'] ?? 'media'));
}

function chamados_prazo_timestamp(array $chamado): ?int
{
    $slaHoras = isset($chamado['sla_horas']) ? (int) $chamado['sla_horas'] : 0;
    if ($slaHoras <= 0 || empty($chamado['criado_em'])) {
        return null;
    }
    $base = strtotime((string) $chamado['criado_em']);
    if ($base === false) {
        return null;
    }
    return $base + ($slaHoras * 3600);
}

function chamados_marco_final_timestamp(array $chamado): ?int
{
    if (!empty($chamado['fechado_em'])) {
        $fim = strtotime((string) $chamado['fechado_em']);
        return $fim === false ? null : $fim;
    }
    if (!empty($chamado['status']) && in_array($chamado['status'], array('resolvido', 'fechado'), true) && !empty($chamado['atualizado_em'])) {
        $fim = strtotime((string) $chamado['atualizado_em']);
        return $fim === false ? null : $fim;
    }
    return null;
}

function chamados_sla_excedido_minutos(array $chamado): ?int
{
    $prazo = chamados_prazo_timestamp($chamado);
    if ($prazo === null) {
        return null;
    }
    $fim = chamados_marco_final_timestamp($chamado);
    if ($fim === null) {
        $fim = time();
    }
    if ($fim <= $prazo) {
        return 0;
    }
    return (int) floor(($fim - $prazo) / 60);
}

function chamados_status_badge_class(string $status): string
{
    switch ($status) {
        case 'aberto':
            return 'b-danger';
        case 'em_andamento':
            return 'b-warning';
        case 'aguardando_cliente':
            return 'b-info';
        case 'resolvido':
            return 'b-success';
        case 'fechado':
            return 'b-neutral';
        default:
            return 'b-neutral';
    }
}

function chamados_prioridade_badge_class(string $prioridade): string
{
    switch ($prioridade) {
        case 'critica':
            return 'b-danger';
        case 'alta':
            return 'b-warning';
        case 'media':
            return 'b-info';
        default:
            return 'b-neutral';
    }
}

function chamados_gerar_protocolo(): string
{
    $prefixo = 'CHK-' . date('Ymd') . '-';
    do {
        $codigo = strtoupper(bin2hex(random_bytes(3)));
        $protocolo = $prefixo . $codigo;
        $stmt = getDB()->prepare('SELECT COUNT(*) FROM hesk_chamados WHERE protocolo = :protocolo');
        $stmt->execute([':protocolo' => $protocolo]);
    } while ((int) $stmt->fetchColumn() > 0);

    return $protocolo;
}

function chamados_find_by_id(int $id): ?array
{
    $stmt = getDB()->prepare("SELECT c.*, cat.nome AS categoria_nome, u.nome AS tecnico_nome
        FROM hesk_chamados c
        LEFT JOIN hesk_categorias cat ON cat.id = c.categoria_id
        LEFT JOIN usuarios u ON u.id = c.tecnico_id
        WHERE c.id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function chamados_find_publico(string $protocolo, string $email): ?array
{
    $stmt = getDB()->prepare("SELECT c.*, cat.nome AS categoria_nome, u.nome AS tecnico_nome
        FROM hesk_chamados c
        LEFT JOIN hesk_categorias cat ON cat.id = c.categoria_id
        LEFT JOIN usuarios u ON u.id = c.tecnico_id
        WHERE c.protocolo = :protocolo AND LOWER(c.email_solicitante) = LOWER(:email) LIMIT 1");
    $stmt->execute([':protocolo' => trim($protocolo), ':email' => trim($email)]);
    return $stmt->fetch() ?: null;
}

function chamados_mensagens(int $chamadoId, bool $includePrivado = true): array
{
    $sql = 'SELECT m.*, u.nome AS usuario_nome FROM hesk_mensagens m LEFT JOIN usuarios u ON u.id = m.usuario_id WHERE m.chamado_id = :id';
    if (!$includePrivado) {
        $sql .= ' AND m.privado = 0';
    }
    $sql .= ' ORDER BY m.criado_em ASC, m.id ASC';
    $stmt = getDB()->prepare($sql);
    $stmt->execute([':id' => $chamadoId]);
    return $stmt->fetchAll();
}

function chamados_anexos_por_chamado(int $chamadoId): array
{
    $stmt = getDB()->prepare('SELECT * FROM hesk_anexos WHERE chamado_id = :id ORDER BY id ASC');
    $stmt->execute([':id' => $chamadoId]);
    return $stmt->fetchAll();
}

function chamados_criar(array $data): int
{
    $pdo = getDB();
    $protocolo = chamados_gerar_protocolo();
    $stmt = $pdo->prepare('INSERT INTO hesk_chamados (protocolo, nome_solicitante, email_solicitante, telefone_solicitante, categoria_id, assunto, descricao, prioridade, status, equipamento_tipo, equipamento_id, origem, ultimo_autor, ultima_interacao_em) VALUES (:protocolo, :nome, :email, :telefone, :categoria_id, :assunto, :descricao, :prioridade, :status, :equipamento_tipo, :equipamento_id, :origem, :ultimo_autor, NOW())');
    $stmt->execute([
        ':protocolo' => $protocolo,
        ':nome' => $data['nome_solicitante'],
        ':email' => $data['email_solicitante'],
        ':telefone' => $data['telefone_solicitante'] ?: null,
        ':categoria_id' => $data['categoria_id'] ?: null,
        ':assunto' => $data['assunto'],
        ':descricao' => $data['descricao'],
        ':prioridade' => chamados_normalizar_prioridade((string) ($data['prioridade'] ?? chamados_categoria_prioridade((int) ($data['categoria_id'] ?? 0)))),
        ':status' => 'aberto',
        ':equipamento_tipo' => $data['equipamento_tipo'] ?: null,
        ':equipamento_id' => $data['equipamento_id'] ?: null,
        ':origem' => $data['origem'] ?? 'portal_publico',
        ':ultimo_autor' => 'cliente',
    ]);
    $id = (int) $pdo->lastInsertId();

    $pdo->prepare('INSERT INTO hesk_mensagens (chamado_id, nome_autor, email_autor, mensagem, origem, privado) VALUES (:chamado_id, :nome, :email, :mensagem, :origem, 0)')
        ->execute([
            ':chamado_id' => $id,
            ':nome' => $data['nome_solicitante'],
            ':email' => $data['email_solicitante'],
            ':mensagem' => $data['descricao'],
            ':origem' => 'cliente',
        ]);

    audit_log('hesk_chamado_criado', 'hesk_chamados', $id, [
        'protocolo' => $protocolo,
        'email' => $data['email_solicitante'],
        'origem' => $data['origem'] ?? 'portal_publico',
    ], current_user_id());

    if (!empty($data['equipamento_tipo']) && !empty($data['equipamento_id'])) {
        ativos_historico_registrar(
            (string) $data['equipamento_tipo'],
            (int) $data['equipamento_id'],
            'chamado_aberto',
            'Chamado vinculado ao ativo',
            'Protocolo ' . $protocolo . ' · ' . $data['assunto'],
            $id,
            current_user_id()
        );
    }

    if (function_exists('chamados_notificar_abertura')) {
        $chamado = chamados_find_by_id($id);
        if ($chamado) {
            try {
                chamados_notificar_abertura($chamado);
            } catch (Throwable $e) {
                if (function_exists('chamados_email_log')) {
                    chamados_email_log('Falha ao notificar abertura do chamado #' . $id . ': ' . $e->getMessage());
                }
            }
        }
    }

    return $id;
}

function chamados_adicionar_mensagem(int $chamadoId, string $mensagem, string $origem, array $meta = []): int
{
    $pdo = getDB();
    $stmt = $pdo->prepare('INSERT INTO hesk_mensagens (chamado_id, usuario_id, nome_autor, email_autor, mensagem, origem, privado) VALUES (:chamado_id, :usuario_id, :nome_autor, :email_autor, :mensagem, :origem, :privado)');
    $stmt->execute([
        ':chamado_id' => $chamadoId,
        ':usuario_id' => $meta['usuario_id'] ?? null,
        ':nome_autor' => $meta['nome_autor'] ?? null,
        ':email_autor' => $meta['email_autor'] ?? null,
        ':mensagem' => $mensagem,
        ':origem' => $origem,
        ':privado' => !empty($meta['privado']) ? 1 : 0,
    ]);
    $id = (int) $pdo->lastInsertId();
    $pdo->prepare('UPDATE hesk_chamados SET ultimo_autor = :origem, ultima_interacao_em = NOW(), atualizado_em = NOW() WHERE id = :id')
        ->execute([':origem' => $origem, ':id' => $chamadoId]);

    $ativo = chamado_ativo_vinculado($chamadoId);
    if ($ativo) {
        $rotuloEvento = !empty($meta['privado']) ? 'Nota interna registrada' : ($origem === 'interno' ? 'Interação da equipe' : 'Atualização do solicitante');
        $trecho = trim((string) preg_replace('/\s+/', ' ', $mensagem));
        if (function_exists('mb_substr')) {
            $trecho = mb_substr($trecho, 0, 180);
        } else {
            $trecho = substr($trecho, 0, 180);
        }
        ativos_historico_registrar((string) $ativo['equipamento_tipo'], (int) $ativo['equipamento_id'], !empty($meta['privado']) ? 'nota_interna' : 'mensagem', $rotuloEvento, $trecho, $chamadoId, $meta['usuario_id'] ?? current_user_id());
    }

    if ($origem === 'interno' && empty($meta['privado']) && function_exists('chamados_notificar_resposta')) {
        $chamado = chamados_find_by_id($chamadoId);
        if ($chamado) {
            chamados_notificar_resposta($chamado, $mensagem);
        }
    }

    return $id;
}

function chamados_tecnico_automatico_id(): ?int
{
    $usuarioId = current_user_id();
    return $usuarioId ? (int) $usuarioId : null;
}

function chamados_atribuir_tecnico_automaticamente(int $chamadoId): void
{
    $tecnicoId = chamados_tecnico_automatico_id();
    if (!$tecnicoId) {
        return;
    }

    $chamado = chamados_find_by_id($chamadoId);
    if (!$chamado || (int) ($chamado['tecnico_id'] ?? 0) === $tecnicoId) {
        return;
    }

    chamados_atribuir($chamadoId, $tecnicoId);
}

function chamados_alterar_status(int $chamadoId, string $status): void
{
    $anterior = chamados_find_by_id($chamadoId);
    $statusAnterior = $anterior ? (string) $anterior['status'] : '';

    $params = [':status' => $status, ':id' => $chamadoId];
    $sql = 'UPDATE hesk_chamados SET status = :status, atualizado_em = NOW()';
    if (in_array($status, ['resolvido', 'fechado'], true)) {
        $sql .= ', fechado_em = NOW()';
    }
    $sql .= ' WHERE id = :id';
    getDB()->prepare($sql)->execute($params);
    chamados_atribuir_tecnico_automaticamente($chamadoId);
    $ativo = chamado_ativo_vinculado($chamadoId);
    if ($ativo) {
        $labels = chamados_status_labels();
        ativos_historico_registrar((string) $ativo['equipamento_tipo'], (int) $ativo['equipamento_id'], 'status', 'Status do chamado atualizado', 'Protocolo ' . $ativo['protocolo'] . ' · ' . ($labels[$status] ?? $status), $chamadoId, current_user_id());
    }

    if ($statusAnterior !== $status && in_array($status, array('resolvido', 'fechado'), true) && function_exists('chamados_notificar_resolucao')) {
        $chamado = chamados_find_by_id($chamadoId);
        if ($chamado) {
            chamados_notificar_resolucao($chamado);
        }
    }
}

function chamados_atribuir(int $chamadoId, ?int $tecnicoId): void
{
    getDB()->prepare('UPDATE hesk_chamados SET tecnico_id = :tecnico_id, atualizado_em = NOW() WHERE id = :id')
        ->execute([':tecnico_id' => $tecnicoId ?: null, ':id' => $chamadoId]);
}


function chamados_atualizar_meta(int $chamadoId, array $data): void
{
    $antes = chamado_ativo_vinculado($chamadoId);
    $stmt = getDB()->prepare('UPDATE hesk_chamados
        SET tecnico_id = :tecnico_id,
            prioridade = :prioridade,
            categoria_id = :categoria_id,
            equipamento_tipo = :equipamento_tipo,
            equipamento_id = :equipamento_id,
            atualizado_em = NOW()
        WHERE id = :id');
    $stmt->execute([
        ':tecnico_id' => !empty($data['tecnico_id']) ? (int) $data['tecnico_id'] : null,
        ':prioridade' => chamados_normalizar_prioridade((string) ($data['prioridade'] ?? 'media')),
        ':categoria_id' => !empty($data['categoria_id']) ? (int) $data['categoria_id'] : null,
        ':equipamento_tipo' => !empty($data['equipamento_tipo']) ? (string) $data['equipamento_tipo'] : null,
        ':equipamento_id' => !empty($data['equipamento_id']) ? (int) $data['equipamento_id'] : null,
        ':id' => $chamadoId,
    ]);
    $depois = chamado_ativo_vinculado($chamadoId);
    if ($antes && (!$depois || $antes['equipamento_tipo'] !== $depois['equipamento_tipo'] || (int) $antes['equipamento_id'] !== (int) $depois['equipamento_id'])) {
        ativos_historico_registrar((string) $antes['equipamento_tipo'], (int) $antes['equipamento_id'], 'desvinculo', 'Ativo desvinculado do chamado', 'Protocolo ' . $antes['protocolo'] . ' · ' . $antes['assunto'], $chamadoId, current_user_id());
    }
    if ($depois && (!$antes || $antes['equipamento_tipo'] !== $depois['equipamento_tipo'] || (int) $antes['equipamento_id'] !== (int) $depois['equipamento_id'])) {
        ativos_historico_registrar((string) $depois['equipamento_tipo'], (int) $depois['equipamento_id'], 'vinculo', 'Ativo vinculado ao chamado', 'Protocolo ' . $depois['protocolo'] . ' · ' . $depois['assunto'], $chamadoId, current_user_id());
    }
}

function chamados_ativos_opcoes(string $tipo): array
{
    $pdo = getDB();
    if ($tipo === 'computador') {
        $sql = "SELECT id,
                       CONCAT(
                           'Computador · ',
                           COALESCE(NULLIF(usuario_responsavel, ''), NULLIF(nome_dispositivo, ''), 'Sem responsável')
                       ) AS nome
                FROM computadores
                ORDER BY usuario_responsavel ASC, nome_dispositivo ASC, id DESC
                LIMIT 300";
        return $pdo->query($sql)->fetchAll();
    }
    if ($tipo === 'celular') {
        $sql = "SELECT id,
                       CONCAT(
                           'Celular · ',
                           COALESCE(NULLIF(usuario_responsavel, ''), CONCAT(COALESCE(marca, ''), ' ', COALESCE(modelo, '')), 'Sem responsável')
                       ) AS nome
                FROM celulares
                ORDER BY usuario_responsavel ASC, marca ASC, modelo ASC, id DESC
                LIMIT 300";
        return $pdo->query($sql)->fetchAll();
    }
    return array();
}

function chamados_ativo_vinculado_label(array $chamado): string
{
    if (empty($chamado['equipamento_tipo']) || empty($chamado['equipamento_id'])) {
        return 'Nenhum ativo vinculado';
    }

    $pdo = getDB();
    if ($chamado['equipamento_tipo'] === 'computador') {
        $stmt = $pdo->prepare("SELECT CONCAT('Computador · ', COALESCE(NULLIF(usuario_responsavel, ''), NULLIF(nome_dispositivo, ''), 'Sem responsável')) FROM computadores WHERE id = :id LIMIT 1");
    } elseif ($chamado['equipamento_tipo'] === 'celular') {
        $stmt = $pdo->prepare("SELECT CONCAT('Celular · ', COALESCE(NULLIF(usuario_responsavel, ''), CONCAT(COALESCE(marca, ''), ' ', COALESCE(modelo, '')), 'Sem responsável')) FROM celulares WHERE id = :id LIMIT 1");
    } else {
        return 'Nenhum ativo vinculado';
    }
    $stmt->execute(array(':id' => (int) $chamado['equipamento_id']));
    $nome = $stmt->fetchColumn();
    if (!$nome) {
        return 'Ativo não encontrado';
    }
    return trim((string) $nome);
}

function chamados_categoria_uso_counts(): array
{
    $rows = getDB()->query('SELECT categoria_id, COUNT(*) AS total FROM hesk_chamados WHERE categoria_id IS NOT NULL GROUP BY categoria_id')->fetchAll();
    $out = array();
    foreach ($rows as $row) {
        $out[(int) $row['categoria_id']] = (int) $row['total'];
    }
    return $out;
}

function chamados_resumo_dashboard(): array
{
    $pdo = getDB();
    $total = (int) $pdo->query('SELECT COUNT(*) FROM hesk_chamados')->fetchColumn();
    $abertos = (int) $pdo->query("SELECT COUNT(*) FROM hesk_chamados WHERE status IN ('aberto','em_andamento','aguardando_cliente')")->fetchColumn();
    $hoje = (int) $pdo->query('SELECT COUNT(*) FROM hesk_chamados WHERE DATE(criado_em) = CURDATE()')->fetchColumn();
    $fechadosMes = (int) $pdo->query("SELECT COUNT(*) FROM hesk_chamados WHERE status IN ('resolvido','fechado') AND DATE_FORMAT(COALESCE(fechado_em, atualizado_em), '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')")->fetchColumn();
    return compact('total', 'abertos', 'hoje', 'fechadosMes');
}

function chamados_tecnicos_opcoes(): array
{
    $stmt = getDB()->query("SELECT id, nome FROM usuarios WHERE ativo = 1 ORDER BY nome ASC");
    return $stmt->fetchAll();
}


function ativo_tipo_table(string $tipo): ?string
{
    if ($tipo === 'computador') {
        return 'computadores';
    }
    if ($tipo === 'celular') {
        return 'celulares';
    }
    return null;
}

function ativo_buscar_resumo(string $tipo, int $id): ?array
{
    $pdo = getDB();
    if ($tipo === 'computador') {
        $stmt = $pdo->prepare("SELECT id, nome_dispositivo, usuario_responsavel, setor, marca, modelo, numero_serie, patrimonio, status, data_cadastro, data_atualizacao FROM computadores WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $nome = trim((string) ($row['usuario_responsavel'] ?: $row['nome_dispositivo'] ?: 'Computador'));
        $sub = trim((string) implode(' · ', array_filter([$row['nome_dispositivo'] ?: null, trim(($row['marca'] ?? '') . ' ' . ($row['modelo'] ?? '')) ?: null, $row['numero_serie'] ?: null])));
        return [
            'tipo' => 'computador',
            'id' => (int) $row['id'],
            'nome' => $nome,
            'subtitulo' => $sub,
            'setor' => (string) ($row['setor'] ?? ''),
            'status' => (string) ($row['status'] ?? ''),
        ];
    }
    if ($tipo === 'celular') {
        $stmt = $pdo->prepare("SELECT id, usuario_responsavel, setor, marca, modelo, numero_serie, imei, operadora, status, data_cadastro, data_atualizacao FROM celulares WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $nome = trim((string) ($row['usuario_responsavel'] ?: trim(($row['marca'] ?? '') . ' ' . ($row['modelo'] ?? '')) ?: 'Celular'));
        $sub = trim((string) implode(' · ', array_filter([trim(($row['marca'] ?? '') . ' ' . ($row['modelo'] ?? '')) ?: null, $row['imei'] ?: null, $row['numero_serie'] ?: null])));
        return [
            'tipo' => 'celular',
            'id' => (int) $row['id'],
            'nome' => $nome,
            'subtitulo' => $sub,
            'setor' => (string) ($row['setor'] ?? ''),
            'status' => (string) ($row['status'] ?? ''),
        ];
    }
    return null;
}


function ativo_usuario_historico_atual(string $tipo, int $id): ?array
{
    $stmt = getDB()->prepare('SELECT * FROM ativo_usuario_historico WHERE ativo_tipo = :tipo AND ativo_id = :id AND data_fim IS NULL ORDER BY data_inicio DESC, id DESC LIMIT 1');
    $stmt->execute([':tipo' => $tipo, ':id' => $id]);
    return $stmt->fetch() ?: null;
}

function ativo_usuario_historico_registrar(string $tipo, int $id, string $nomeUsuario, ?string $setor = null, ?string $observacao = null, ?string $inicio = null): void
{
    $nomeUsuario = trim((string) $nomeUsuario);
    if (!$tipo || !$id || $nomeUsuario === '') {
        return;
    }

    $pdo = getDB();
    $aberto = ativo_usuario_historico_atual($tipo, $id);
    $setor = trim((string) ($setor ?? '')) ?: null;

    if ($aberto && trim((string) $aberto['nome_usuario']) === $nomeUsuario && (string) ($aberto['setor'] ?? '') === (string) ($setor ?? '')) {
        return;
    }

    if ($aberto) {
        $stmt = $pdo->prepare('UPDATE ativo_usuario_historico SET data_fim = NOW() WHERE id = :id');
        $stmt->execute([':id' => $aberto['id']]);
    }

    $stmt = $pdo->prepare('INSERT INTO ativo_usuario_historico (ativo_tipo, ativo_id, nome_usuario, setor, data_inicio, observacao) VALUES (:tipo, :id, :nome_usuario, :setor, :data_inicio, :observacao)');
    $stmt->execute([
        ':tipo' => $tipo,
        ':id' => $id,
        ':nome_usuario' => $nomeUsuario,
        ':setor' => $setor,
        ':data_inicio' => $inicio ?: date('Y-m-d H:i:s'),
        ':observacao' => $observacao,
    ]);
}

function ativo_usuario_historico_bootstrap(string $tipo, int $id): void
{
    $stmt = getDB()->prepare('SELECT COUNT(*) FROM ativo_usuario_historico WHERE ativo_tipo = :tipo AND ativo_id = :id');
    $stmt->execute([':tipo' => $tipo, ':id' => $id]);
    if ((int) $stmt->fetchColumn() > 0) {
        return;
    }

    $ativo = ativo_buscar_resumo($tipo, $id);
    if (!$ativo || trim((string) $ativo['nome']) === '') {
        return;
    }

    ativo_usuario_historico_registrar($tipo, $id, (string) $ativo['nome'], (string) ($ativo['setor'] ?? ''), 'Responsável atual importado para o histórico');
}

function ativo_usuario_historico_listar(string $tipo, int $id, int $limit = 100): array
{
    $limit = max(1, min($limit, 500));
    $stmt = getDB()->prepare("SELECT * FROM ativo_usuario_historico WHERE ativo_tipo = :tipo AND ativo_id = :id ORDER BY data_inicio DESC, id DESC LIMIT {$limit}");
    $stmt->execute([':tipo' => $tipo, ':id' => $id]);
    return $stmt->fetchAll() ?: [];
}

function ativos_eventos_tecnicos(string $tipo, int $id, int $limit = 50): array
{
    $limit = max(1, min($limit, 200));
    $stmt = getDB()->prepare("SELECT h.*, c.protocolo, c.assunto, u.nome AS usuario_nome FROM ativos_historico h LEFT JOIN hesk_chamados c ON c.id = h.chamado_id LEFT JOIN usuarios u ON u.id = h.usuario_id WHERE h.ativo_tipo = :tipo AND h.ativo_id = :id AND h.tipo_evento IN ('mensagem', 'nota_interna', 'status', 'vinculo', 'desvinculo', 'chamado_aberto') ORDER BY h.criado_em DESC, h.id DESC LIMIT {$limit}");
    $stmt->execute([':tipo' => $tipo, ':id' => $id]);
    return $stmt->fetchAll() ?: [];
}

function ativos_historico_registrar(string $ativoTipo, int $ativoId, string $tipoEvento, string $titulo, ?string $descricao = null, ?int $chamadoId = null, ?int $usuarioId = null): void
{
    if (!$ativoTipo || !$ativoId) {
        return;
    }

    $pdo = getDB();
    $stmt = $pdo->prepare('INSERT INTO ativos_historico (ativo_tipo, ativo_id, chamado_id, tipo_evento, titulo, descricao, usuario_id) VALUES (:ativo_tipo, :ativo_id, :chamado_id, :tipo_evento, :titulo, :descricao, :usuario_id)');
    $stmt->execute([
        ':ativo_tipo' => $ativoTipo,
        ':ativo_id' => $ativoId,
        ':chamado_id' => $chamadoId ?: null,
        ':tipo_evento' => $tipoEvento,
        ':titulo' => $titulo,
        ':descricao' => $descricao,
        ':usuario_id' => $usuarioId ?: null,
    ]);
}

function chamado_ativo_vinculado(int $chamadoId): ?array
{
    $stmt = getDB()->prepare('SELECT equipamento_tipo, equipamento_id, protocolo, assunto, status FROM hesk_chamados WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $chamadoId]);
    $row = $stmt->fetch();
    if (!$row || empty($row['equipamento_tipo']) || empty($row['equipamento_id'])) {
        return null;
    }
    return $row;
}

function ativos_historico_por_ativo(string $tipo, int $id, int $limit = 100): array
{
    $limit = max(1, min($limit, 500));
    $stmt = getDB()->prepare("SELECT h.*, c.protocolo, c.assunto, u.nome AS usuario_nome FROM ativos_historico h LEFT JOIN hesk_chamados c ON c.id = h.chamado_id LEFT JOIN usuarios u ON u.id = h.usuario_id WHERE h.ativo_tipo = :tipo AND h.ativo_id = :id ORDER BY h.criado_em DESC, h.id DESC LIMIT {$limit}");
    $stmt->execute([':tipo' => $tipo, ':id' => $id]);
    return $stmt->fetchAll() ?: [];
}

function ativos_resumo(string $tipo, int $id): array
{
    $pdo = getDB();
    $base = ['total_chamados' => 0, 'abertos' => 0, 'resolvidos' => 0, 'sla_estourado' => 0, 'ultimo_chamado_em' => null];
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_chamados, SUM(CASE WHEN status IN ('aberto','em_andamento','aguardando_cliente') THEN 1 ELSE 0 END) AS abertos, SUM(CASE WHEN status IN ('resolvido','fechado') THEN 1 ELSE 0 END) AS resolvidos, MAX(criado_em) AS ultimo_chamado_em FROM hesk_chamados WHERE equipamento_tipo = :tipo AND equipamento_id = :id");
    $stmt->execute([':tipo' => $tipo, ':id' => $id]);
    $row = $stmt->fetch() ?: [];
    $base = array_merge($base, $row ?: []);
    $tickets = $pdo->prepare("SELECT c.*, cat.sla_horas FROM hesk_chamados c LEFT JOIN hesk_categorias cat ON cat.id = c.categoria_id WHERE c.equipamento_tipo = :tipo AND c.equipamento_id = :id");
    $tickets->execute([':tipo' => $tipo, ':id' => $id]);
    $sla = 0;
    foreach (($tickets->fetchAll() ?: []) as $item) {
        if (chamados_sla_status($item) === 'atrasado') {
            $sla++;
        }
    }
    $base['sla_estourado'] = $sla;
    return $base;
}

function ativos_chamados_relacionados(string $tipo, int $id, int $limit = 20): array
{
    $limit = max(1, min($limit, 100));
    $stmt = getDB()->prepare("SELECT c.*, cat.nome AS categoria_nome FROM hesk_chamados c LEFT JOIN hesk_categorias cat ON cat.id = c.categoria_id WHERE c.equipamento_tipo = :tipo AND c.equipamento_id = :id ORDER BY c.criado_em DESC LIMIT {$limit}");
    $stmt->execute([':tipo' => $tipo, ':id' => $id]);
    return $stmt->fetchAll() ?: [];
}
