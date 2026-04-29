<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
guard_current_page_access();

$id = (int) get_query('id', 0);
$chamado = chamados_find_by_id($id);
if (!$chamado) {
    flash('Chamado não encontrado.', 'error');
    redirect('chamados_index.php');
}
$mensagens = chamados_mensagens($id, true);
$anexos = chamados_anexos_por_chamado($id);
$statuses = chamados_status_labels();
$prioridades = chamados_prioridade_labels();
$tecnicos = chamados_tecnicos_opcoes();
$categorias = chamados_categorias_todas();
$ativosComputador = chamados_ativos_opcoes('computador');
$ativosCelular = chamados_ativos_opcoes('celular');
$ativoLabel = chamados_ativo_vinculado_label($chamado);
$ativoResumo = (!empty($chamado['equipamento_tipo']) && !empty($chamado['equipamento_id'])) ? ativo_buscar_resumo((string) $chamado['equipamento_tipo'], (int) $chamado['equipamento_id']) : null;
$pageTitle = 'Chamado ' . $chamado['protocolo'];
include __DIR__ . '/../includes/header.php';
echo render_flash();
?>
<section class="page-head chamados-hero chamados-hero-ticket chamados-hero-clean">
  <div class="page-head-copy">
    
    <h2><?= e($chamado['assunto']) ?></h2>
    <p>Protocolo <?= e($chamado['protocolo']) ?> · Aberto em <?= date('d/m/Y H:i', strtotime($chamado['criado_em'])) ?></p>
  </div>
  <div class="page-head-actions ticket-head-actions">
    <span class="ticket-head-chip">Status: <?= e($statuses[$chamado['status']] ?? $chamado['status']) ?></span>
    <a class="btn btn-ghost btn-sm" href="chamados_index.php">Voltar para a fila</a>
  </div>
</section>

<div class="chamados-view-grid chamados-view-grid-redesign">
  <div class="chamados-main-stack chamados-main-stack-redesign">
    <section class="card unified-card chamados-history-card chamados-history-card-redesign">
      <div class="ticket-section-head">
        <div>
          <div class="stitle">Histórico do chamado</div>
          <p class="section-subtitle"></p>
        </div>
        <div class="message-legend">
          <span class="legend-pill requester">Solicitante</span>
          <span class="legend-pill analyst">Analista</span>
          <span class="legend-pill note">Nota interna</span>
        </div>
      </div>

      <div class="ticket-thread-list">
        <?php foreach ($mensagens as $m): ?>
          <?php
            $isPrivate = (int) $m['privado'] === 1;
            $role = $isPrivate ? 'Nota interna' : ($m['origem'] === 'interno' ? 'Analista' : 'Solicitante');
            $messageClass = $isPrivate ? 'note' : ($m['origem'] === 'interno' ? 'analyst' : 'requester');
            $author = $m['usuario_nome'] ?: $m['nome_autor'] ?: ucfirst($m['origem']);
            $initial = strtoupper(function_exists('mb_substr') ? mb_substr(trim((string) $author), 0, 1) : substr(trim((string) $author), 0, 1));
          ?>
          <article class="ticket-thread <?= $messageClass ?>">
            <div class="ticket-thread-avatar" aria-hidden="true"><?= e($initial ?: '•') ?></div>
            <div class="ticket-thread-content">
              <div class="ticket-thread-head">
                <div class="ticket-thread-meta">
                  <div class="ticket-thread-author-row">
                    <strong class="ticket-thread-author"><?= e($author) ?></strong>
                    <span class="ticket-thread-role <?= $messageClass ?>"><?= e($role) ?></span>
                  </div>
                  <?php if (!empty($m['email_autor']) && $m['origem'] !== 'interno' && !$isPrivate): ?>
                    <span class="ticket-thread-email"><?= e($m['email_autor']) ?></span>
                  <?php endif; ?>
                </div>
                <time class="ticket-thread-date"><?= date('d/m/Y H:i', strtotime($m['criado_em'])) ?></time>
              </div>
              <div class="ticket-thread-body"><?= nl2br(e($m['mensagem'])) ?></div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ($anexos): ?>
      <div class="ticket-files-block ticket-files-block-redesign">
        <div class="stitle">Anexos enviados</div>
        <div class="ticket-files-list">
          <?php foreach ($anexos as $a): ?>
            <a class="btn btn-ghost btn-sm" target="_blank" href="../hesk/arquivo.php?id=<?= (int)$a['id'] ?>"><?= e($a['nome_original']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </section>

    <section class="card unified-card chamados-reply-card-redesign">
      <div class="ticket-section-head ticket-section-head-compact">
        <div>
          <div class="stitle">Responder chamado</div>
          <p class="section-subtitle"></p>
        </div>
      </div>
      <form method="post" action="chamados_responder.php" class="reply-form refined-reply-form refined-reply-form-redesign">
        <?= csrf_input() ?>
        <input type="hidden" name="id" value="<?= (int)$chamado['id'] ?>">
        <div class="reply-type reply-type-redesign">
          <label class="reply-option active">
            <input type="radio" name="privado" value="0" checked>
            <span>Público</span>
          </label>
          <label class="reply-option">
            <input type="radio" name="privado" value="1">
            <span>Interno</span>
          </label>
        </div>
        <textarea name="mensagem" rows="7" placeholder="Digite a resposta do chamado" required></textarea>
        <div class="reply-actions-row reply-actions-row-dropdown">
          <div class="reply-actions-dropdown" id="replyActionsDropdown">
            <button type="button" class="btn btn-primary reply-main-toggle" id="replyActionToggle" aria-haspopup="true" aria-expanded="false">
              <span>Responder</span>
              <span class="reply-caret" aria-hidden="true">▾</span>
            </button>
            <div class="reply-actions-menu" id="replyActionMenu" role="menu" aria-labelledby="replyActionToggle" hidden>
              <button type="submit" name="acao_resposta" value="responder" class="reply-menu-item" role="menuitem">
                <span class="reply-menu-title">Enviar resposta</span>
                <small>Envia a mensagem e mantém o fluxo normal do chamado.</small>
              </button>
              <button type="submit" name="acao_resposta" value="resolver" class="reply-menu-item reply-menu-item-resolve" role="menuitem">
                <span class="reply-menu-title">Responder e resolver chamado</span>
                <small>Envia a resposta e já finaliza como resolvido.</small>
              </button>
            </div>
          </div>
        </div>
      </form>
    </section>
  </div>

  <aside class="chamados-side-stack chamados-side-stack-redesign">
    <section class="card unified-card ticket-summary-card-redesign">
      <div class="ticket-section-head ticket-section-head-compact">
        <div>
          <div class="stitle">Resumo do chamado</div>
          <p class="section-subtitle"></p>
        </div>
      </div>
      <div class="summary-list compact-summary-list compact-summary-list-redesign">
        <div><span>Status</span><strong><span class="badge <?= chamados_status_badge_class($chamado['status']) ?>"><?= e($statuses[$chamado['status']] ?? $chamado['status']) ?></span></strong></div>
        <div><span>Prioridade</span><strong><span class="badge <?= chamados_prioridade_badge_class($chamado['prioridade']) ?>"><?= e($prioridades[$chamado['prioridade']] ?? $chamado['prioridade']) ?></span></strong></div>
        <div><span>Categoria</span><strong><?= e($chamado['categoria_nome'] ?: 'Sem categoria') ?></strong></div>
        <div><span>Solicitante</span><strong><?= e($chamado['nome_solicitante']) ?></strong></div>
        <div><span>E-mail</span><strong><?= e($chamado['email_solicitante']) ?></strong></div>        
        <div><span>Ativo vinculado</span><strong><?= e($ativoLabel) ?></strong></div>
        <?php if ($ativoResumo): ?>
        <div><span>Histórico do ativo</span><strong><a class="table-link" href="../ativos_historico.php?tipo=<?= e($chamado['equipamento_tipo']) ?>&id=<?= (int)$chamado['equipamento_id'] ?>">Ver histórico do dispositivo</a></strong></div>
        <?php endif; ?>
      </div>
    </section>

    <section class="card unified-card ticket-management-card-redesign">
      <div class="ticket-section-head ticket-section-head-compact">
        <div>
          <div class="stitle">Gestão do chamado</div>
          <p class="section-subtitle"></p>
        </div>
      </div>

      <form method="post" action="chamados_atribuir.php" class="ticket-actions-form ticket-actions-form-redesign">
        <?= csrf_input() ?>
        <input type="hidden" name="id" value="<?= (int)$chamado['id'] ?>">

        <div class="action-group">
          <label>Atribuir técnico</label>
          <select name="tecnico_id"><option value="0">Não atribuído</option><?php foreach ($tecnicos as $t): ?><option value="<?= (int)$t['id'] ?>" <?= (int)$chamado['tecnico_id']===(int)$t['id']?'selected':'' ?>><?= e($t['nome']) ?></option><?php endforeach; ?></select>
        </div>

        <div class="action-group two-col-grid">
          <div>
            <label>Prioridade</label>
            <select name="prioridade"><?php foreach ($prioridades as $key => $label): ?><option value="<?= e($key) ?>" <?= $chamado['prioridade']===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select>
          </div>
          <div>
            <label>Categoria</label>
            <select name="categoria_id"><option value="0">Sem categoria</option><?php foreach ($categorias as $categoria): ?><option value="<?= (int)$categoria['id'] ?>" <?= (int)$chamado['categoria_id']===(int)$categoria['id']?'selected':'' ?>><?= e($categoria['nome']) ?></option><?php endforeach; ?></select>
          </div>
        </div>

        <div class="action-group two-col-grid two-col-grid-assets">
          <div>
            <label>Tipo de ativo</label>
            <select name="equipamento_tipo" id="equipamento_tipo_select">
              <option value="">Nenhum ativo</option>
              <option value="computador" <?= $chamado['equipamento_tipo']==='computador'?'selected':'' ?>>Computador</option>
              <option value="celular" <?= $chamado['equipamento_tipo']==='celular'?'selected':'' ?>>Celular</option>
            </select>
          </div>
          <div>
            <label>Ativo vinculado</label>
            <select name="equipamento_id">
              <option value="0">Selecione</option>
              <optgroup label="Computadores">
                <?php foreach ($ativosComputador as $ativo): ?><option value="<?= (int)$ativo['id'] ?>" <?= $chamado['equipamento_tipo']==='computador' && (int)$chamado['equipamento_id']===(int)$ativo['id']?'selected':'' ?>><?= e($ativo['nome']) ?></option><?php endforeach; ?>
              </optgroup>
              <optgroup label="Celulares">
                <?php foreach ($ativosCelular as $ativo): ?><option value="<?= (int)$ativo['id'] ?>" <?= $chamado['equipamento_tipo']==='celular' && (int)$chamado['equipamento_id']===(int)$ativo['id']?'selected':'' ?>><?= e($ativo['nome']) ?></option><?php endforeach; ?>
              </optgroup>
            </select>
          </div>
        </div>

        <button type="submit" class="btn btn-ghost">Salvar dados do chamado</button>
      </form>

      <form method="post" action="chamados_status.php" class="ticket-actions-form status-form-block ticket-actions-form-redesign">
        <?= csrf_input() ?>
        <input type="hidden" name="id" value="<?= (int)$chamado['id'] ?>">
        <div class="action-group">
          <label>Status do chamado</label>
          <select name="status"><?php foreach ($statuses as $key => $label): ?><option value="<?= e($key) ?>" <?= $chamado['status']===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select>
        </div>
        <button type="submit" class="btn btn-ghost">Atualizar status</button>
      </form>
    </section>
  </aside>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var wrap = document.getElementById('replyActionsDropdown');
  var toggle = document.getElementById('replyActionToggle');
  var menu = document.getElementById('replyActionMenu');

  if (!wrap || !toggle || !menu) {
    return;
  }

  function closeMenu() {
    wrap.classList.remove('is-open');
    menu.hidden = true;
    toggle.setAttribute('aria-expanded', 'false');
  }

  function openMenu() {
    wrap.classList.add('is-open');
    menu.hidden = false;
    toggle.setAttribute('aria-expanded', 'true');
  }

  toggle.addEventListener('click', function (event) {
    event.preventDefault();
    if (wrap.classList.contains('is-open')) {
      closeMenu();
    } else {
      openMenu();
    }
  });

  document.addEventListener('click', function (event) {
    if (!wrap.contains(event.target)) {
      closeMenu();
    }
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeMenu();
    }
  });
});
</script>
