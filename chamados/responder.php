<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
requireAccess('chamados');
if (!request_is_post()) { redirect('chamados_index.php'); }
validate_csrf_or_fail($_POST['csrf_token'] ?? null);
$id = (int) post('id', 0);
$mensagem = trim((string) post('mensagem', ''));
$privado = (int) post('privado', 0) === 1;
$acaoResposta = (string) post('acao_resposta', 'responder');
$chamado = chamados_find_by_id($id);
if (!$chamado) {
    flash('Chamado não encontrado.', 'error');
    redirect('chamados_index.php');
}
if ($mensagem === '') {
    flash('Digite uma mensagem para responder.', 'error');
    redirect('chamados_visualizar.php?id=' . $id);
}
chamados_adicionar_mensagem($id, $mensagem, 'interno', [
    'usuario_id' => current_user_id(),
    'nome_autor' => $_SESSION['nome'] ?? 'Equipe interna',
    'email_autor' => null,
    'privado' => $privado,
]);
if (!$privado) {
    if ($acaoResposta === 'resolver') {
        chamados_alterar_status($id, 'resolvido');
    } else {
        chamados_alterar_status($id, 'aguardando_cliente');
    }
}
audit_log('hesk_resposta_interna', 'hesk_chamados', $id, ['privado' => $privado]);
flash('Resposta registrada com sucesso.');
redirect('chamados_visualizar.php?id=' . $id);
