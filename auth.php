<?php
require_once __DIR__ . '/includes/security.php';
app_boot_session();

$configFile = __DIR__ . '/config.local.php';
if (file_exists($configFile)) {
    require_once $configFile;
}

defined('DB_HOST') || define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
defined('DB_PORT') || define('DB_PORT', getenv('DB_PORT') ?: '3306');
defined('DB_NAME') || define('DB_NAME', getenv('DB_NAME') ?: 'assets_ti');
defined('DB_USER') || define('DB_USER', getenv('DB_USER') ?: '');
defined('DB_PASS') || define('DB_PASS', getenv('DB_PASS') ?: '');
defined('DB_CHARSET') || define('DB_CHARSET', 'utf8mb4');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('<div style="font-family:monospace;background:#1a1a1a;color:#f87171;padding:24px;margin:24px;border-radius:8px;border:1px solid #7f1d1d">
                <b>Erro de conexao com MySQL</b><br><br>'
                . htmlspecialchars($e->getMessage())
                . '<br><br>Crie o arquivo <code>config.local.php</code> com suas credenciais de banco ou defina as variáveis de ambiente do banco.</div>');
        }
    }
    return $pdo;
}


function column_exists(PDO $pdo, $table, $column) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = :schema
          AND TABLE_NAME = :table
          AND COLUMN_NAME = :column
    ");
    $stmt->execute([
        ':schema' => DB_NAME,
        ':table' => $table,
        ':column' => $column,
    ]);
    return (bool) $stmt->fetchColumn();
}

function ensure_column_exists(PDO $pdo, $table, $column, $definition) {
    if (!column_exists($pdo, $table, $column)) {
        $pdo->exec('ALTER TABLE `' . str_replace('`', '``', $table) . '` ADD COLUMN `' . str_replace('`', '``', $column) . '` ' . $definition);
    }
}

function setupDB() {
    $pdo = getDB();

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS computadores (
            id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nome_dispositivo    VARCHAR(100)  NOT NULL DEFAULT '',
            tipo                VARCHAR(50)   NOT NULL,
            marca               VARCHAR(80)   NOT NULL,
            modelo              VARCHAR(100)  NOT NULL,
            numero_serie        VARCHAR(100)  DEFAULT NULL,
            patrimonio          VARCHAR(80)   DEFAULT NULL,
            processador         VARCHAR(150)  DEFAULT NULL,
            ram                 VARCHAR(60)   DEFAULT NULL,
            armazenamento       VARCHAR(80)   DEFAULT NULL,
            sistema_operacional VARCHAR(80)   DEFAULT NULL,
            usuario_responsavel VARCHAR(120)  NOT NULL,
            setor               VARCHAR(100)  NOT NULL,
            localizacao         VARCHAR(120)  DEFAULT NULL,
            status              VARCHAR(40)   NOT NULL DEFAULT 'Em uso',
            data_aquisicao      DATE          DEFAULT NULL,
            observacoes         TEXT          DEFAULT NULL,
            data_cadastro       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS celulares (
            id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            tipo                VARCHAR(50)   NOT NULL DEFAULT 'Smartphone',
            marca               VARCHAR(80)   NOT NULL,
            modelo              VARCHAR(100)  NOT NULL,
            numero_serie        VARCHAR(100)  DEFAULT NULL,
            imei                VARCHAR(20)   DEFAULT NULL,
            numero_chip         VARCHAR(30)   DEFAULT NULL,
            operadora           VARCHAR(60)   DEFAULT NULL,
            usuario_responsavel VARCHAR(120)  NOT NULL,
            setor               VARCHAR(100)  NOT NULL,
            mdm_ativo           TINYINT(1)    NOT NULL DEFAULT 0,
            status              VARCHAR(40)   NOT NULL DEFAULT 'Em uso',
            data_aquisicao      DATE          DEFAULT NULL,
            observacoes         TEXT          DEFAULT NULL,
            data_cadastro       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    ensure_column_exists($pdo, 'celulares', 'mdm_ativo', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER setor');
    ensure_column_exists($pdo, 'celulares', 'status', "VARCHAR(40) NOT NULL DEFAULT 'Em uso' AFTER mdm_ativo");
    ensure_column_exists($pdo, 'celulares', 'data_aquisicao', 'DATE DEFAULT NULL AFTER status');
    ensure_column_exists($pdo, 'celulares', 'observacoes', 'TEXT DEFAULT NULL AFTER data_aquisicao');
    ensure_column_exists($pdo, 'celulares', 'data_cadastro', 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER observacoes');
    ensure_column_exists($pdo, 'celulares', 'data_atualizacao', 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER data_cadastro');
}
setupDB();


function setupFaturasDB() {
    $pdo = getDB();

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS fornecedores (
            id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nome        VARCHAR(120) NOT NULL,
            categoria   VARCHAR(80)  DEFAULT NULL,
            cnpj        VARCHAR(20)  DEFAULT NULL,
            contato     VARCHAR(120) DEFAULT NULL,
            observacoes TEXT         DEFAULT NULL,
            ativo       TINYINT(1)   NOT NULL DEFAULT 1,
            data_cadastro DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS faturas (
            id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fornecedor_id   INT UNSIGNED NOT NULL,
            descricao       VARCHAR(200) DEFAULT NULL,
            valor           DECIMAL(10,2) NOT NULL,
            data_vencimento DATE          NOT NULL,
            data_pagamento  DATE          DEFAULT NULL,
            mes_referencia  TINYINT(2)    NOT NULL,
            ano_referencia  SMALLINT(4)   NOT NULL,
            status          VARCHAR(30)   NOT NULL DEFAULT 'Pendente',
            observacoes     TEXT          DEFAULT NULL,
            data_cadastro   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    ensure_column_exists($pdo, 'faturas', 'descricao', 'VARCHAR(200) DEFAULT NULL AFTER fornecedor_id');
    ensure_column_exists($pdo, 'faturas', 'valor', 'DECIMAL(10,2) NOT NULL AFTER descricao');
    ensure_column_exists($pdo, 'faturas', 'data_vencimento', 'DATE NOT NULL AFTER valor');
    ensure_column_exists($pdo, 'faturas', 'data_pagamento', 'DATE DEFAULT NULL AFTER data_vencimento');
    ensure_column_exists($pdo, 'faturas', 'mes_referencia', 'TINYINT(2) NOT NULL AFTER data_pagamento');
    ensure_column_exists($pdo, 'faturas', 'ano_referencia', 'SMALLINT(4) NOT NULL AFTER mes_referencia');
    ensure_column_exists($pdo, 'faturas', 'status', "VARCHAR(30) NOT NULL DEFAULT 'Pendente' AFTER ano_referencia");
    ensure_column_exists($pdo, 'faturas', 'observacoes', 'TEXT DEFAULT NULL AFTER status');
    ensure_column_exists($pdo, 'faturas', 'data_cadastro', 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER observacoes');
}
setupFaturasDB();

function setupUsuariosDB() {
    $pdo = getDB();
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nome         VARCHAR(100) NOT NULL,
            usuario      VARCHAR(50)  NOT NULL UNIQUE,
            senha        VARCHAR(255) NOT NULL,
            perfil       VARCHAR(20)  NOT NULL DEFAULT 'viewer',
            permissoes   TEXT         DEFAULT NULL,
            ativo        TINYINT(1)   NOT NULL DEFAULT 1,
            ultimo_login DATETIME     DEFAULT NULL,
            data_cadastro DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS login_tentativas (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            usuario VARCHAR(50) NOT NULL,
            ip VARCHAR(45) NOT NULL,
            sucesso TINYINT(1) NOT NULL DEFAULT 0,
            data_tentativa DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_login_usuario_data (usuario, data_tentativa),
            INDEX idx_login_ip_data (ip, data_tentativa)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}
setupUsuariosDB();


function setupDistribuicaoDB() {
    $pdo = getDB();

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS distribuicao_clientes (
            id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nome          VARCHAR(150) NOT NULL,
            cnpj          VARCHAR(20) DEFAULT NULL,
            observacoes   TEXT DEFAULT NULL,
            ativo         TINYINT(1) NOT NULL DEFAULT 1,
            created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS distribuicao_equipamentos (
            id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            cliente_id           INT UNSIGNED NOT NULL,
            codigo_local         VARCHAR(80) DEFAULT NULL,
            contrato             VARCHAR(80) DEFAULT NULL,
            pp                   VARCHAR(80) DEFAULT NULL,
            uf                   VARCHAR(2) DEFAULT NULL,
            municipio            VARCHAR(120) DEFAULT NULL,
            regional             VARCHAR(120) DEFAULT NULL,
            unidade              VARCHAR(150) DEFAULT NULL,
            setor                VARCHAR(150) DEFAULT NULL,
            endereco             VARCHAR(200) DEFAULT NULL,
            tipo_equipamento     VARCHAR(80) DEFAULT NULL,
            fabricante           VARCHAR(80) DEFAULT NULL,
            modelo               VARCHAR(120) DEFAULT NULL,
            serie                VARCHAR(120) DEFAULT NULL,
            nome_impressora      VARCHAR(120) DEFAULT NULL,
            monitoramento        VARCHAR(60) NOT NULL DEFAULT 'Offline',
            ultima_leitura_em      DATE DEFAULT NULL,
            origem_monitoramento   VARCHAR(80) DEFAULT NULL,
            ultima_sincronizacao_monitoramento_em DATETIME DEFAULT NULL,
            status_operacional   VARCHAR(60) NOT NULL DEFAULT 'Ativa',
            data_instalacao      DATE DEFAULT NULL,
            observacoes          TEXT DEFAULT NULL,
            created_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_dist_cliente (cliente_id),
            INDEX idx_dist_status (status_operacional),
            INDEX idx_dist_monitoramento (monitoramento),
            CONSTRAINT fk_distribuicao_cliente FOREIGN KEY (cliente_id) REFERENCES distribuicao_clientes(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS distribuicao_movimentacoes (
            id                       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            equipamento_id           INT UNSIGNED DEFAULT NULL,
            cliente_id               INT UNSIGNED DEFAULT NULL,
            tipo_movimentacao        VARCHAR(50) NOT NULL,
            data_movimentacao        DATE NOT NULL,
            origem                   VARCHAR(180) DEFAULT NULL,
            destino                  VARCHAR(180) DEFAULT NULL,
            serie_antiga             VARCHAR(120) DEFAULT NULL,
            serie_nova               VARCHAR(120) DEFAULT NULL,
            modelo_antigo            VARCHAR(120) DEFAULT NULL,
            modelo_novo              VARCHAR(120) DEFAULT NULL,
            monitoramento_anterior   VARCHAR(60) DEFAULT NULL,
            monitoramento_novo       VARCHAR(60) DEFAULT NULL,
            status_anterior          VARCHAR(60) DEFAULT NULL,
            status_novo              VARCHAR(60) DEFAULT NULL,
            motivo                   VARCHAR(180) DEFAULT NULL,
            tecnico_responsavel      VARCHAR(120) DEFAULT NULL,
            observacoes              TEXT DEFAULT NULL,
            usuario_id               INT UNSIGNED DEFAULT NULL,
            created_at               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_dist_mov_cliente (cliente_id),
            INDEX idx_dist_mov_tipo (tipo_movimentacao),
            INDEX idx_dist_mov_data (data_movimentacao),
            CONSTRAINT fk_dist_mov_cliente FOREIGN KEY (cliente_id) REFERENCES distribuicao_clientes(id) ON DELETE SET NULL,
            CONSTRAINT fk_dist_mov_equip FOREIGN KEY (equipamento_id) REFERENCES distribuicao_equipamentos(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS distribuicao_usuarios_clientes (
            id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            usuario_id        INT UNSIGNED NOT NULL,
            cliente_id        INT UNSIGNED NOT NULL,
            pode_visualizar   TINYINT(1) NOT NULL DEFAULT 1,
            pode_cadastrar    TINYINT(1) NOT NULL DEFAULT 0,
            pode_editar       TINYINT(1) NOT NULL DEFAULT 0,
            pode_movimentar   TINYINT(1) NOT NULL DEFAULT 0,
            created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_dist_usuario_cliente (usuario_id, cliente_id),
            CONSTRAINT fk_dist_uc_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            CONSTRAINT fk_dist_uc_cliente FOREIGN KEY (cliente_id) REFERENCES distribuicao_clientes(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    ensure_column_exists($pdo, 'distribuicao_equipamentos', 'monitoramento', "VARCHAR(60) NOT NULL DEFAULT 'Offline' AFTER nome_impressora");
    ensure_column_exists($pdo, 'distribuicao_equipamentos', 'ultima_leitura_em', 'DATE DEFAULT NULL AFTER monitoramento');
    ensure_column_exists($pdo, 'distribuicao_equipamentos', 'origem_monitoramento', 'VARCHAR(80) DEFAULT NULL AFTER ultima_leitura_em');
    ensure_column_exists($pdo, 'distribuicao_equipamentos', 'ultima_sincronizacao_monitoramento_em', 'DATETIME DEFAULT NULL AFTER origem_monitoramento');
    ensure_column_exists($pdo, 'distribuicao_equipamentos', 'status_operacional', "VARCHAR(60) NOT NULL DEFAULT 'Ativa' AFTER ultima_sincronizacao_monitoramento_em");
}
setupDistribuicaoDB();

require_once __DIR__ . '/includes/audit.php';
setupAuditoriaDB();
