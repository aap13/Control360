<?php

function distribuicao_setup_import_tables(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS distribuicao_clientes (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(150) NOT NULL,
            cnpj VARCHAR(30) DEFAULT NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            observacoes TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS distribuicao_equipamentos (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT UNSIGNED NOT NULL,
            codigo_local VARCHAR(100) DEFAULT NULL,
            pp VARCHAR(80) DEFAULT NULL,
            uf VARCHAR(10) DEFAULT NULL,
            municipio VARCHAR(150) DEFAULT NULL,
            regional VARCHAR(150) DEFAULT NULL,
            setor VARCHAR(150) DEFAULT NULL,
            logradouro VARCHAR(255) DEFAULT NULL,
            bairro VARCHAR(150) DEFAULT NULL,
            cep VARCHAR(20) DEFAULT NULL,
            cnpj VARCHAR(30) DEFAULT NULL,
            centro_custo VARCHAR(100) DEFAULT NULL,
            tipo VARCHAR(100) DEFAULT NULL,
            fabricante VARCHAR(100) DEFAULT NULL,
            modelo VARCHAR(150) DEFAULT NULL,
            serie VARCHAR(150) DEFAULT NULL,
            nome_impressora VARCHAR(150) DEFAULT NULL,
            monitoramento VARCHAR(30) NOT NULL DEFAULT 'Offline',
            status_operacional VARCHAR(50) NOT NULL DEFAULT 'Ativa',
            data_instalacao DATE DEFAULT NULL,
            ultima_leitura_em DATE DEFAULT NULL,
            observacoes TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_distribuicao_cliente (cliente_id),
            INDEX idx_distribuicao_serie (serie)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS distribuicao_usuarios_clientes (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT UNSIGNED NOT NULL,
            cliente_id INT UNSIGNED NOT NULL,
            pode_visualizar TINYINT(1) NOT NULL DEFAULT 1,
            pode_cadastrar TINYINT(1) NOT NULL DEFAULT 1,
            pode_editar TINYINT(1) NOT NULL DEFAULT 1,
            pode_excluir TINYINT(1) NOT NULL DEFAULT 0,
            pode_movimentar TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_user_client (usuario_id, cliente_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    distribuicao_ensure_column($pdo, 'distribuicao_equipamentos', 'bairro', "VARCHAR(150) DEFAULT NULL AFTER logradouro");
    distribuicao_ensure_column($pdo, 'distribuicao_equipamentos', 'cep', "VARCHAR(20) DEFAULT NULL AFTER bairro");
    distribuicao_ensure_column($pdo, 'distribuicao_equipamentos', 'centro_custo', "VARCHAR(100) DEFAULT NULL AFTER cnpj");
}

function distribuicao_ensure_column(PDO $pdo, string $table, string $column, string $definition): void
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = :schema
          AND TABLE_NAME = :table_name
          AND COLUMN_NAME = :column_name
    ");
    $stmt->execute([
        ':schema' => DB_NAME,
        ':table_name' => $table,
        ':column_name' => $column,
    ]);

    if (!(int) $stmt->fetchColumn()) {
        $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
    }
}

function distribuicao_get_allowed_clients(PDO $pdo, int $userId, bool $isAdmin): array
{
    if ($isAdmin) {
        return $pdo->query("SELECT id, nome FROM distribuicao_clientes WHERE status = 1 ORDER BY nome")->fetchAll();
    }

    $stmt = $pdo->prepare("
        SELECT c.id, c.nome
        FROM distribuicao_clientes c
        INNER JOIN distribuicao_usuarios_clientes uc ON uc.cliente_id = c.id
        WHERE c.status = 1
          AND uc.usuario_id = :usuario_id
          AND uc.pode_visualizar = 1
        ORDER BY c.nome
    ");
    $stmt->execute([':usuario_id' => $userId]);
    return $stmt->fetchAll();
}

function distribuicao_parse_base_csv(string $filePath, int $clienteId): array
{
    if (!is_readable($filePath)) {
        throw new RuntimeException('Não foi possível ler o arquivo enviado.');
    }

    $raw = file_get_contents($filePath);
    if ($raw === false) {
        throw new RuntimeException('Falha ao ler o conteúdo do CSV.');
    }

    if (substr($raw, 0, 3) === "\xEF\xBB\xBF") {
        $raw = substr($raw, 3);
    }

    if (!mb_check_encoding($raw, 'UTF-8')) {
        $raw = mb_convert_encoding($raw, 'UTF-8', 'ISO-8859-1,Windows-1252,UTF-8');
    }

    $delimiter = distribuicao_detect_csv_delimiter($raw);
    $lines = preg_split("/\r\n|\n|\r/", trim($raw));
    if (!$lines || count($lines) < 2) {
        throw new RuntimeException('O CSV está vazio ou sem dados.');
    }

    $headers = str_getcsv(array_shift($lines), $delimiter);
    $headers = array_map('distribuicao_normalize_header', $headers);

    $required = ['cliente_nome','codigo_local','pp','uf','municipio','regional','setor_unidade','logradouro','bairro','cep','cnpj','centro_custo','tipo_equipamento','fabricante','modelo','serie','nome_impressora','status_operacional','data_instalacao','observacoes'];
    $missing = array_diff($required, $headers);
    if ($missing) {
        throw new RuntimeException('CSV inválido. Colunas ausentes: ' . implode(', ', $missing));
    }

    $rows = [];
    $withSerial = 0;
    $withoutSerial = 0;
    $lineNumber = 1;

    foreach ($lines as $line) {
        $lineNumber++;
        if (trim($line) === '') {
            continue;
        }
        $cols = str_getcsv($line, $delimiter);
        $data = [];
        foreach ($headers as $index => $header) {
            $data[$header] = isset($cols[$index]) ? trim((string) $cols[$index]) : '';
        }

        $serie = $data['serie'];
        if ($serie !== '') {
            $withSerial++;
        } else {
            $withoutSerial++;
        }

        $rows[] = [
            '_line' => $lineNumber,
            'cliente_id' => $clienteId,
            'codigo_local' => $data['codigo_local'],
            'pp' => $data['pp'],
            'uf' => $data['uf'],
            'municipio' => $data['municipio'],
            'regional' => $data['regional'],
            'setor' => $data['setor_unidade'],
            'logradouro' => $data['logradouro'],
            'bairro' => $data['bairro'],
            'cep' => $data['cep'],
            'cnpj' => $data['cnpj'],
            'centro_custo' => $data['centro_custo'],
            'tipo' => $data['tipo_equipamento'],
            'fabricante' => $data['fabricante'],
            'modelo' => $data['modelo'],
            'serie' => $serie,
            'nome_impressora' => $data['nome_impressora'],
            'status_operacional' => $data['status_operacional'] ?: 'Ativa',
            'data_instalacao' => distribuicao_parse_date($data['data_instalacao']),
            'observacoes' => $data['observacoes'],
        ];
    }

    return [
        'rows' => $rows,
        'summary' => [
            'total' => count($rows),
            'with_serial' => $withSerial,
            'without_serial' => $withoutSerial,
        ],
    ];
}

function distribuicao_importar_base_csv(PDO $pdo, array $rows, int $userId): array
{
    $inserted = 0;
    $updated = 0;
    $ignored = 0;

    $select = $pdo->prepare("
        SELECT id
        FROM distribuicao_equipamentos
        WHERE cliente_id = :cliente_id
          AND serie = :serie
        LIMIT 1
    ");

    $insert = $pdo->prepare("
        INSERT INTO distribuicao_equipamentos (
            cliente_id, codigo_local, pp, uf, municipio, regional, setor,
            logradouro, bairro, cep, cnpj, centro_custo, tipo, fabricante,
            modelo, serie, nome_impressora, status_operacional, data_instalacao,
            observacoes, monitoramento
        ) VALUES (
            :cliente_id, :codigo_local, :pp, :uf, :municipio, :regional, :setor,
            :logradouro, :bairro, :cep, :cnpj, :centro_custo, :tipo, :fabricante,
            :modelo, :serie, :nome_impressora, :status_operacional, :data_instalacao,
            :observacoes, 'Offline'
        )
    ");

    $update = $pdo->prepare("
        UPDATE distribuicao_equipamentos
           SET codigo_local = :codigo_local,
               pp = :pp,
               uf = :uf,
               municipio = :municipio,
               regional = :regional,
               setor = :setor,
               logradouro = :logradouro,
               bairro = :bairro,
               cep = :cep,
               cnpj = :cnpj,
               centro_custo = :centro_custo,
               tipo = :tipo,
               fabricante = :fabricante,
               modelo = :modelo,
               nome_impressora = :nome_impressora,
               status_operacional = :status_operacional,
               data_instalacao = :data_instalacao,
               observacoes = :observacoes
         WHERE id = :id
    ");

    $pdo->beginTransaction();
    try {
        foreach ($rows as $row) {
            if ($row['serie'] === '') {
                $ignored++;
                continue;
            }

            $select->execute([
                ':cliente_id' => $row['cliente_id'],
                ':serie' => $row['serie'],
            ]);
            $existingId = (int) $select->fetchColumn();

            if ($existingId > 0) {
                $params = $row;
                $params['id'] = $existingId;
                $update->execute($params);
                $updated++;
            } else {
                $insert->execute($row);
                $inserted++;
            }
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    return [
        'inserted' => $inserted,
        'updated' => $updated,
        'ignored' => $ignored,
    ];
}

function distribuicao_detect_csv_delimiter(string $raw): string
{
    $sample = strtok($raw, "\n");
    $delimiters = [';', ',', "\t"];
    $best = ';';
    $count = -1;
    foreach ($delimiters as $candidate) {
        $parts = str_getcsv($sample, $candidate);
        if (count($parts) > $count) {
            $count = count($parts);
            $best = $candidate;
        }
    }
    return $best;
}

function distribuicao_normalize_header(string $header): string
{
    $header = trim($header);
    $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
    $header = mb_strtolower($header, 'UTF-8');
    $header = strtr($header, [
        'á'=>'a','à'=>'a','ã'=>'a','â'=>'a',
        'é'=>'e','ê'=>'e',
        'í'=>'i',
        'ó'=>'o','ô'=>'o','õ'=>'o',
        'ú'=>'u',
        'ç'=>'c',
        ' '=>'_','-'=>'_','/'=>'_'
    ]);
    $header = preg_replace('/_+/', '_', $header);
    return trim($header, '_');
}

function distribuicao_parse_date(string $value): ?string
{
    $value = trim($value);
    if ($value === '') {
        return null;
    }

    $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y'];
    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $value);
        if ($dt instanceof DateTime) {
            return $dt->format('Y-m-d');
        }
    }

    return null;
}
