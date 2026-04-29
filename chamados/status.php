<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
requireAccess('chamados');
if (!request_is_post()) { redirect('chamados_index.php'); }
validate_csrf_or_fail($_POST['csrf_token'] ?? null);
$id = (int) post('id', 0);
$status = trim((string) post('status', ''));
$statuses = chamados_status_labels();
if (!isset($statuses[$status])) {
    flash('Status inválido.', 'error');
    redirect('chamados_visualizar.php?id=' . $id);
}
$chamado = chamados_find_by_id($id);
if (!$chamado) {
    flash('Chamado não encontrado.', 'error');
    redirect('chamados_index.php');
}
chamados_alterar_status($id, $status);
audit_log('hesk_status_alterado', 'hesk_chamados', $id, ['status' => $status]);
flash('Status atualizado com sucesso.');
redirect('chamados_visualizar.php?id=' . $id);
