<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
require_write_access(resolveTipoModule($_POST['tipo'] ?? $_GET['tipo'] ?? ''));

$pageTitle = 'Excluir registro';
guard_current_page_access();

if (!request_is_post()) {
    http_response_code(405);
    exit('Método não permitido.');
}

validate_csrf_or_fail($_POST['csrf_token'] ?? null);

$tipo = $_POST['tipo'] ?? '';
$id   = (int)($_POST['id'] ?? 0);

if (!in_array($tipo, ['computador','celular'], true) || $id <= 0) {
    redirect('index.php');
}

$db = getDB();
$table = $tipo === 'computador' ? 'computadores' : 'celulares';
$redirect = $tipo === 'computador' ? 'computadores.php' : 'celulares.php';

$oldStmt = $db->prepare("SELECT * FROM $table WHERE id = :id");
$oldStmt->execute([':id' => $id]);
$old = $oldStmt->fetch();

$stmt = $db->prepare("DELETE FROM $table WHERE id = :id");
$stmt->execute([':id' => $id]);

if ($stmt->rowCount() > 0) {
    audit_log('delete', $table, $id, ['registro' => $old]);
    flash('Registro excluído com sucesso!');
} else {
    flash('Registro não encontrado.', 'error');
}

redirect($redirect);
