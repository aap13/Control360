<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
require_write_access('impressao_financeiro', 'impressao_financeiro.php');
impressao_financeiro_ensure_tables();

$pageTitle = 'Importar financeiro de impressão';
$clientes = distribuicao_accessible_clients('editar');
$errors = [];
$successes = [];

if (request_is_post()) {
    validate_csrf_or_fail($_POST['csrf_token'] ?? null);
    $clienteId = (int) post('cliente_id', 0);
    if ($clienteId <= 0) {
        $errors[] = 'Selecione um cliente.';
    } else {
        distribuicao_require_cliente_access($clienteId, 'editar');
    }

    if (empty($_FILES['arquivos']['name']) || !is_array($_FILES['arquivos']['name'])) {
        $errors[] = 'Selecione um ou mais arquivos XLSX.';
    }

    if (!$errors) {
        $count = count($_FILES['arquivos']['name']);
        for ($i = 0; $i < $count; $i++) {
            $file = [
                'name' => $_FILES['arquivos']['name'][$i] ?? '',
                'type' => $_FILES['arquivos']['type'][$i] ?? '',
                'tmp_name' => $_FILES['arquivos']['tmp_name'][$i] ?? '',
                'error' => $_FILES['arquivos']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size' => $_FILES['arquivos']['size'][$i] ?? 0,
            ];
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            try {
                $result = impressao_financeiro_import_file($clienteId, $file, current_user_id() ?: 0);
                $successes[] = sprintf('%s importado (%d linhas).', $result['arquivo'], $result['linhas']);
            } catch (Throwable $e) {
                $errors[] = basename((string) $file['name']) . ': ' . $e->getMessage();
            }
        }
    }

    if (!$errors && $successes) {
        flash(implode(' ', $successes));
        redirect('impressao_financeiro.php?cliente_id=' . $clienteId);
    }
}

include __DIR__ . '/includes/header.php';
echo render_flash();
?>

<div class="page-shell">
  <div class="page-head">
    <div class="page-head-copy">
      <h2>Importar financeiro de impressão</h2>
      <p>Envie vários arquivos XLSX do mesmo mês. O sistema substitui automaticamente a versão anterior do mesmo nome-base, por exemplo <code>.v2</code> e <code>.v3</code>. O cálculo segue o modelo de cobrança cadastrado no cliente.</p>
    </div>
    <div class="page-head-actions"><a class="btn btn-ghost" href="impressao_financeiro.php"><?= icon('bill') ?> Voltar ao financeiro</a></div>
  </div>

  <div class="card pad-lg import-card">
    <div class="toolbar-head"><div><h3>Importação mensal consolidada</h3><p class="note">Aba esperada no XLSX: <strong>Contadores</strong>. Os valores por impressora são lidos direto dessa aba. Para clientes com franquia, o sistema calcula <strong>Valor da franquia = Val.Franquia/Taxa Fixa × Págs. Franquia</strong> e considera <strong>Val.Excedido/Produzido</strong> como valor excedente.</p></div></div>
    <?php if ($errors): ?>
      <div class="alert alert-error"><?= e(implode(' | ', $errors)) ?></div>
    <?php endif; ?>
    <?php if ($successes): ?>
      <div class="alert alert-success"><?= e(implode(' ', $successes)) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="form-grid" style="margin-top:12px">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <div class="form-group full">
        <label>Cliente da distribuição</label>
        <select name="cliente_id" required>
          <option value="">Selecione</option>
          <?php foreach ($clientes as $cliente): ?>
            <option value="<?= (int)$cliente['id'] ?>"><?= e($cliente['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group full">
        <label>Arquivos XLSX</label>
        <input type="file" name="arquivos[]" accept=".xlsx" multiple required>
      </div>
      <div class="form-group full">
        <div class="import-tip">Dica: você pode selecionar todos os arquivos do mês de uma vez. Se houver <code>CGB_PA.v2</code> e <code>CGB_PA.v3</code>, o sistema mantém só a versão mais recente importada. Para clientes com franquia, revise antes se o cadastro do cliente está marcado como <strong>Com franquia</strong>.</div>
      </div>
      <div class="form-actions"><button class="btn btn-primary" type="submit"><?= icon('plus') ?> Importar arquivos</button></div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php';
