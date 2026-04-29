<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
requireAccess('chamados');
if (!request_is_post()) { redirect('chamados_index.php'); }
validate_csrf_or_fail($_POST['csrf_token'] ?? null);
$id = (int) post('id', 0);
$chamado = chamados_find_by_id($id);
if (!$chamado) {
    flash('Chamado não encontrado.', 'error');
    redirect('chamados_index.php');
}
$prioridade = trim((string) post('prioridade', 'media'));
$categoriaId = (int) post('categoria_id', 0);
$tecnicoId = (int) post('tecnico_id', 0);
$equipamentoTipo = trim((string) post('equipamento_tipo', ''));
$equipamentoId = (int) post('equipamento_id', 0);

$prioridades = chamados_prioridade_labels();
if (!isset($prioridades[$prioridade])) {
    $prioridade = 'media';
}
if (!in_array($equipamentoTipo, array('', 'computador', 'celular'), true)) {
    $equipamentoTipo = '';
    $equipamentoId = 0;
}
if ($equipamentoTipo === '') {
    $equipamentoId = 0;
}
chamados_atualizar_meta($id, array(
    'tecnico_id' => $tecnicoId > 0 ? $tecnicoId : null,
    'prioridade' => $prioridade,
    'categoria_id' => $categoriaId > 0 ? $categoriaId : null,
    'equipamento_tipo' => $equipamentoTipo ?: null,
    'equipamento_id' => $equipamentoId > 0 ? $equipamentoId : null,
));
audit_log('hesk_chamado_meta_atualizada', 'hesk_chamados', $id, array(
    'tecnico_id' => $tecnicoId ?: null,
    'prioridade' => $prioridade,
    'categoria_id' => $categoriaId ?: null,
    'equipamento_tipo' => $equipamentoTipo ?: null,
    'equipamento_id' => $equipamentoId ?: null,
));
flash('Dados do chamado atualizados com sucesso.');
redirect('chamados_visualizar.php?id=' . $id);
