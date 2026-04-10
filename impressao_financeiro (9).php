<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireAccess('distribuicao');
if (($_SESSION['perfil'] ?? '') !== 'admin') {
    flash('Somente administradores podem gerenciar clientes da distribuição.', 'error');
    redirect('distribuicao_index.php');
}
$db = getDB();
$erros = [];
$editId = query_int('edit', 0, 0);
$editing = $editId > 0 ? distribuicao_fetch_cliente($editId) : null;

if (request_is_post()) {
    require_write_access('distribuicao', 'distribuicao_index.php');
    validate_csrf_or_fail($_POST['csrf_token'] ?? null);
    $id = (int) post('id', 0);
    $nome = trim((string) post('nome', ''));
    $cnpj = trim((string) post('cnpj', ''));
    $modeloCobranca = trim((string) post('modelo_cobranca', 'sem_franquia'));
    $observacoes = trim((string) post('observacoes', ''));
    $ativo = post('ativo', '1') === '1' ? 1 : 0;

    if ($nome === '') {
        $erros[] = 'Informe o nome do cliente.';
    }

    if (!in_array($modeloCobranca, ['sem_franquia', 'com_franquia'], true)) {
        $modeloCobranca = 'sem_franquia';
    }

    if (empty($erros)) {
        if ($id > 0) {
            $db->prepare("UPDATE distribuicao_clientes SET nome=:nome, cnpj=:cnpj, modelo_cobranca=:modelo_cobranca, observacoes=:observacoes, ativo=:ativo WHERE id=:id")
               ->execute([
                   ':nome' => $nome,
                   ':cnpj' => $cnpj ?: null,
                   ':modelo_cobranca' => $modeloCobranca,
                   ':observacoes' => $observacoes ?: null,
                   ':ativo' => $ativo,
                   ':id' => $id
               ]);
            audit_log('distribuicao_cliente_atualizado', 'distribuicao_clientes', $id, ['nome'=>$nome, 'modelo_cobranca'=>$modeloCobranca], current_user_id());
            flash('Cliente atualizado com sucesso.');
        } else {
            $db->prepare("INSERT INTO distribuicao_clientes (nome, cnpj, modelo_cobranca, observacoes, ativo) VALUES (:nome,:cnpj,:modelo_cobranca,:observacoes,:ativo)")
               ->execute([
                   ':nome' => $nome,
                   ':cnpj' => $cnpj ?: null,
                   ':modelo_cobranca' => $modeloCobranca,
                   ':observacoes' => $observacoes ?: null,
                   ':ativo' => $ativo
               ]);
            $newId = (int) $db->lastInsertId();
            audit_log('distribuicao_cliente_criado', 'distribuicao_clientes', $newId, ['nome'=>$nome, 'modelo_cobranca'=>$modeloCobranca], current_user_id());
            flash('Cliente cadastrado com sucesso.');
        }
        redirect('distribuicao_clientes.php');
    }
}

$clientes = $db->query("SELECT c.*, (SELECT COUNT(*) FROM distribuicao_equipamentos e WHERE e.cliente_id = c.id) AS total_equipamentos FROM distribuicao_clientes c ORDER BY c.nome ASC")->fetchAll();
include 'includes/header.php';
echo render_flash();
?>
<div class="page-head">
    <div class="page-head-copy">
        <h2>Clientes da distribuição</h2>
        <p>Cadastre os clientes do módulo, defina o modelo de cobrança e use esse vínculo para limitar o acesso dos usuários e calcular corretamente o financeiro de impressão.</p>
    </div>
</div>
<?php if (!empty($erros)): ?><div class="alert alert-error"><?= icon('off') ?> <?= implode(' · ', array_map('e', $erros)) ?></div><?php endif; ?>
<div style="display:grid;grid-template-columns:1.45fr .95fr;gap:18px;align-items:start">
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 18px;border-bottom:1px solid var(--line)"><div class="stitle" style="margin:0"><?= icon('users') ?> Clientes cadastrados</div></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Cliente</th><th>CNPJ</th><th>Modelo cobrança</th><th>Equipamentos</th><th>Status</th><th style="width:120px">Ações</th></tr></thead>
                <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><strong><?= e($cliente['nome']) ?></strong></td>
                        <td class="mono"><?= e($cliente['cnpj'] ?: '—') ?></td>
                        <td><span class="badge <?= ($cliente['modelo_cobranca'] ?? 'sem_franquia') === 'com_franquia' ? 'b-blue' : 'b-gray' ?>"><?= e(distribuicao_modelo_cobranca_label($cliente['modelo_cobranca'] ?? 'sem_franquia')) ?></span></td>
                        <td><?= (int) $cliente['total_equipamentos'] ?></td>
                        <td><span class="badge <?= $cliente['ativo'] ? 'b-green' : 'b-gray' ?>"><?= $cliente['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                        <td><a class="btn-icon edit" href="distribuicao_clientes.php?edit=<?= (int) $cliente['id'] ?>"><?= icon('edit') ?></a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($clientes)): ?><tr><td colspan="6" style="padding:24px;text-align:center;color:var(--muted)">Nenhum cliente cadastrado.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="stitle"><?= $editing ? icon('edit').' Editar cliente' : icon('plus').' Novo cliente' ?></div>
        <form method="post">
            <?= csrf_input() ?>
            <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
            <div class="form-grid">
                <div class="form-group full"><label>Nome do cliente <span class="req">*</span></label><input type="text" name="nome" value="<?= e($editing['nome'] ?? ($_POST['nome'] ?? '')) ?>"></div>
                <div class="form-group"><label>CNPJ</label><input type="text" name="cnpj" value="<?= e($editing['cnpj'] ?? ($_POST['cnpj'] ?? '')) ?>"></div>
                <div class="form-group"><label>Status</label><select name="ativo"><option value="1" <?= ((string)($editing['ativo'] ?? ($_POST['ativo'] ?? '1')) === '1') ? 'selected' : '' ?>>Ativo</option><option value="0" <?= ((string)($editing['ativo'] ?? ($_POST['ativo'] ?? '1')) === '0') ? 'selected' : '' ?>>Inativo</option></select></div>
                <div class="form-group full">
                    <label>Modelo de cobrança</label>
                    <?php $modeloAtual = (string) ($editing['modelo_cobranca'] ?? ($_POST['modelo_cobranca'] ?? 'sem_franquia')); ?>
                    <select name="modelo_cobranca">
                        <option value="sem_franquia" <?= $modeloAtual === 'sem_franquia' ? 'selected' : '' ?>>Sem franquia (valor fixo + valor por páginas)</option>
                        <option value="com_franquia" <?= $modeloAtual === 'com_franquia' ? 'selected' : '' ?>>Com franquia (valor da franquia + valor excedente)</option>
                    </select>
                    <small style="display:block;margin-top:6px;color:var(--muted)">
                        Use <strong>Sem franquia</strong> para contratos como Equatorial.<br>
                        Use <strong>Com franquia</strong> para contratos como IFMA. Nesse modelo, o sistema considera o valor base do equipamento como
                        <strong>Val.Franquia/Taxa Fixa × Págs. Franquia</strong> e o excedente como <strong>Val.Excedido/Produzido</strong>.
                    </small>
                </div>
                <div class="form-group full"><label>Observações</label><textarea name="observacoes"><?= e($editing['observacoes'] ?? ($_POST['observacoes'] ?? '')) ?></textarea></div>
            </div>
            <div class="form-actions"><button type="submit" class="btn btn-primary"><?= icon('check') ?> Salvar cliente</button><?php if ($editing): ?><a href="distribuicao_clientes.php" class="btn btn-ghost">Cancelar</a><?php endif; ?></div>
        </form>
    </div>
</div>
