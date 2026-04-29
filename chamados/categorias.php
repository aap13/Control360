<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
requireAccess('chamados');

$id = query_int('id', 0, 0);
$editing = $id > 0 ? chamados_categoria_buscar($id) : null;
if ($id > 0 && !$editing) {
    flash('Categoria não encontrada.', 'error');
    redirect('chamados_categorias.php');
}

if (request_is_post()) {
    validate_csrf_or_fail($_POST['csrf_token'] ?? null);

    if (post('action', '') === 'delete') {
        $deleteId = (int) post('delete_id', 0);
        if ($deleteId <= 0 || !chamados_categoria_buscar($deleteId)) {
            flash('Categoria não encontrada.', 'error');
        } elseif (!chamados_categoria_pode_excluir($deleteId)) {
            flash('Não é possível excluir uma categoria já utilizada em chamados.', 'error');
        } elseif (chamados_categoria_excluir($deleteId)) {
            flash('Categoria excluída com sucesso.', 'success');
        } else {
            flash('Não foi possível excluir a categoria.', 'error');
        }
        redirect('chamados_categorias.php');
    }

    $payload = [
        'nome' => trim((string) post('nome', '')),
        'descricao' => trim((string) post('descricao', '')),
        'ordem' => (int) post('ordem', 0),
        'sla_horas' => max(1, (int) post('sla_horas', 24)),
        'prioridade_padrao' => trim((string) post('prioridade_padrao', 'media')),
        'ativo' => (int) post('ativo', 1) === 1,
    ];

    if ($payload['nome'] === '') {
        flash('Informe o nome da categoria.', 'error');
    } else {
        chamados_categoria_salvar($payload, $id ?: null);
        flash($id ? 'Categoria atualizada com sucesso.' : 'Categoria criada com sucesso.', 'success');
        redirect('chamados_categorias.php');
    }
}

$categorias = chamados_categorias_todas();
$usoCounts = chamados_categoria_uso_counts();
$prioridades = chamados_prioridade_labels();
$pageTitle = 'Categorias e SLA';
include __DIR__ . '/../includes/header.php';
echo render_flash();
$form = $editing ?: ['nome' => '', 'descricao' => '', 'ordem' => count($categorias) + 1, 'sla_horas' => 24, 'prioridade_padrao' => 'media', 'ativo' => 1];
?>
<section class="page-head chamados-hero">
  <div class="page-head-copy">
    
    <h2>Categorias e SLA</h2>
    <p>Defina as categorias exibidas no portal e o prazo padrão de atendimento em horas para cada uma.</p>
  </div>
  <div class="page-head-actions">
    <a class="btn btn-ghost btn-sm" href="chamados_index.php">Voltar para a fila</a>
  </div>
</section>

<div class="category-admin-layout">
  <div class="card unified-card category-form-card">
    <div class="section-title" style="margin-bottom:16px"><h3><?= $editing ? 'Editar categoria' : 'Nova categoria' ?></h3></div>
    <form method="post" class="form-grid">
      <?= csrf_input() ?>
      <div class="form-group">
        <label>Nome</label>
        <input type="text" name="nome" value="<?= e(post('nome', $form['nome'])) ?>" required>
      </div>
      <div class="form-group">
        <label>Descrição</label>
        <textarea name="descricao" rows="4" placeholder="Opcional"><?= e(post('descricao', $form['descricao'])) ?></textarea>
      </div>
      <div class="grid-2 compact-grid" style="gap:14px">
        <div class="form-group">
          <label>Ordem</label>
          <input type="number" name="ordem" min="0" value="<?= (int) post('ordem', (string) $form['ordem']) ?>">
        </div>
        <div class="form-group">
          <label>SLA (horas)</label>
          <input type="number" name="sla_horas" min="1" value="<?= (int) post('sla_horas', (string) $form['sla_horas']) ?>" required>
        </div>
      </div>
      <div class="grid-2 compact-grid" style="gap:14px">
        <div class="form-group">
          <label>Prioridade padrão</label>
          <select name="prioridade_padrao">
            <?php foreach ($prioridades as $key => $label): ?>
              <option value="<?= e($key) ?>" <?= post('prioridade_padrao', $form['prioridade_padrao']) === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-group form-check-row">
        <label class="toggle-check"><input type="checkbox" name="ativo" value="1" <?= (int) post('ativo', (string) $form['ativo']) === 1 ? 'checked' : '' ?>> <span>Categoria ativa no portal</span></label>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn btn-primary" type="submit"><?= $editing ? 'Salvar alterações' : 'Criar categoria' ?></button>
        <?php if ($editing): ?><a class="btn btn-ghost" href="chamados_categorias.php">Cancelar</a><?php endif; ?>
      </div>
    </form>
  </div>

  <div class="card unified-card category-table-card" style="padding:0;overflow:hidden">
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Categoria</th><th>SLA</th><th>Prioridade</th><th>Chamados</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <?php if (!$categorias): ?>
            <tr><td colspan="6" style="text-align:center;color:var(--t3)">Nenhuma categoria cadastrada.</td></tr>
          <?php endif; ?>
          <?php foreach ($categorias as $categoria): ?>
            <tr>
              <td>
                <strong><?= e($categoria['nome']) ?></strong>
                <div style="color:var(--t3);font-size:12px;margin-top:4px"><?= e($categoria['descricao'] ?: 'Sem descrição') ?></div>
              </td>
              <td><?= (int) $categoria['sla_horas'] ?>h</td>
              <td><span class="badge <?= chamados_prioridade_badge_class($categoria['prioridade_padrao']) ?>"><?= e($prioridades[$categoria['prioridade_padrao']] ?? $categoria['prioridade_padrao']) ?></span></td>
              <td><span class="soft-pill soft-pill-muted"><?= (int) ($usoCounts[(int)$categoria['id']] ?? 0) ?></span></td>
              <td><span class="badge <?= (int) $categoria['ativo'] === 1 ? 'b-success' : 'b-neutral' ?>"><?= (int) $categoria['ativo'] === 1 ? 'Ativa' : 'Oculta' ?></span></td>
              <td>
                <div style="display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap">
                  <a class="btn btn-ghost btn-sm" href="chamados_categorias.php?id=<?= (int) $categoria['id'] ?>">Editar</a>
                  <form method="post" onsubmit="return confirm('Excluir esta categoria?');" style="display:inline">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="delete_id" value="<?= (int) $categoria['id'] ?>">
                    <button class="btn btn-danger btn-sm" type="submit">Excluir</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
