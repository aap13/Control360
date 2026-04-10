<?php
function setupAuditoriaDB(): void
{
    $pdo = getDB();
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS auditoria (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT UNSIGNED DEFAULT NULL,
            acao VARCHAR(100) NOT NULL,
            entidade VARCHAR(50) NOT NULL,
            entidade_id INT UNSIGNED DEFAULT NULL,
            detalhes TEXT DEFAULT NULL,
            ip VARCHAR(45) DEFAULT NULL,
            criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_auditoria_entidade (entidade, entidade_id),
            INDEX idx_auditoria_usuario (usuario_id, criado_em),
            CONSTRAINT fk_auditoria_usuario
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
                ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function audit_log(string $action, string $entity, ?int $entityId = null, array $details = [], ?int $userId = null): void
{
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            INSERT INTO auditoria (usuario_id, acao, entidade, entidade_id, detalhes, ip)
            VALUES (:usuario_id, :acao, :entidade, :entidade_id, :detalhes, :ip)
        ");
        $stmt->execute([
            ':usuario_id' => $userId ?? current_user_id(),
            ':acao'       => $action,
            ':entidade'   => $entity,
            ':entidade_id'=> $entityId,
            ':detalhes'   => $details ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            ':ip'         => client_ip(),
        ]);
    } catch (Throwable $e) {
        error_log('Falha ao gravar auditoria: ' . $e->getMessage());
    }
}
