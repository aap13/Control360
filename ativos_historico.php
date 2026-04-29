<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$tipo = (string) get_query('tipo', 'computador');
$id = (int) get_query('id', 0);
if (!in_array($tipo, ['computador', 'celular'], true)) {
    $tipo = 'computador';
}
$module = $tipo === 'celular' ? 'celulares' : 'computadores';
requireAccess($module);

$ativo = $id > 0 ? ativo_buscar_resumo($tipo, $id) : null;
if ($ativo) {
    ativo_usuario_historico_bootstrap($tipo, (int) $ativo['id']);
}
$resumo = $ativo ? ativos_resumo($tipo, (int) $ativo['id']) : ['total_chamados'=>0,'abertos'=>0,'resolvidos'=>0,'sla_estourado'=>0,'ultimo_chamado_em'=>null];
$usuariosHistorico = $ativo ? ativo_usuario_historico_listar($tipo, (int) $ativo['id']) : [];
$chamados = $ativo ? ativos_chamados_relacionados($tipo, (int) $ativo['id'], 100) : [];
$eventos = $ativo ? ativos_eventos_tecnicos($tipo, (int) $ativo['id'], 40) : [];
$options = $tipo === 'celular' ? chamados_ativos_opcoes('celular') : chamados_ativos_opcoes('computador');
$pageTitle = 'Histórico de ativos';
include __DIR__ . '/includes/header.php';
echo render_flash();
?>
<section class="page-head chamados-hero chamados-hero-clean">
  <div class="page-head-copy">
    <h2>Histórico do dispositivo</h2>
    <p>Consulte o histórico de usuários e todos os chamados vinculados a este dispositivo.</p>
  </div>
</section>

<div class="card chamados-board" style="margin-bottom:16px">
  <form method="get" class="toolbar-shell" style="padding:16px;display:grid;grid-template-columns:180px minmax(0,1fr) auto;gap:12px;align-items:end">
    <div>
      <label style="display:block;font-size:12px;color:var(--t3);margin-bottom:6px">Tipo de ativo</label>
      <select name="tipo" onchange="this.form.submit()">
        <option value="computador" <?= $tipo==='computador' ? 'selected' : '' ?>>Computador</option>
        <option value="celular" <?= $tipo==='celular' ? 'selected' : '' ?>>Celular</option>
      </select>
    </div>
    <div>
      <label style="display:block;font-size:12px;color:var(--t3);margin-bottom:6px">Ativo</label>
      <select name="id">
        <option value="0">Selecione o dispositivo</option>
        <?php foreach ($options as $opt): ?>
          <option value="<?= (int) $opt['id'] ?>" <?= $id === (int) $opt['id'] ? 'selected' : '' ?>><?= e($opt['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div><button type="submit" class="btn btn-primary">Ver histórico</button></div>
  </form>
</div>

<?php if (!$ativo): ?>
<div class="card chamados-board" style="padding:22px">
  <div class="empty-state" style="min-height:180px"><p>Selecione um dispositivo para visualizar o histórico de usuários e os chamados vinculados.</p></div>
</div>
<?php else: ?>
<div class="stats-grid chamados-stats compact-stats" style="margin-bottom:16px">
  <article class="stat-card chamados-stat chamados-stat-total"><div class="sc-label">Dispositivo</div><div class="sc-value" style="font-size:20px"><?= e($ativo['nome']) ?></div><div class="sc-foot"><?= e($ativo['subtitulo'] ?: 'Sem detalhes complementares') ?></div><div class="sc-bar"></div></article>
  <article class="stat-card chamados-stat chamados-stat-open"><div class="sc-label">Chamados</div><div class="sc-value"><?= (int) $resumo['total_chamados'] ?></div><div class="sc-foot">Total de ocorrências vinculadas</div><div class="sc-bar"></div></article>
  <article class="stat-card chamados-stat chamados-stat-today"><div class="sc-label">Em aberto</div><div class="sc-value"><?= (int) $resumo['abertos'] ?></div><div class="sc-foot">Ainda em tratamento</div><div class="sc-bar"></div></article>
  <article class="stat-card chamados-stat chamados-stat-closed"><div class="sc-label">SLA estourado</div><div class="sc-value"><?= (int) $resumo['sla_estourado'] ?></div><div class="sc-foot">Chamados fora do prazo</div><div class="sc-bar"></div></article>
</div>

<div class="chamados-view-grid chamados-view-grid-redesign" style="align-items:start">
  <div class="chamados-main-stack chamados-main-stack-redesign">
    <section class="card unified-card chamados-history-card chamados-history-card-redesign" style="margin-bottom:16px">
      <div class="ticket-section-head">
        <div>
          <div class="stitle">Histórico de usuários do dispositivo</div>
          <p class="section-subtitle">Mostra quem já foi o responsável por este dispositivo, setor e período de uso.</p>
        </div>
      </div>
      <div class="table-wrap chamados-table-wrap" style="margin-top:8px">
        <table class="chamados-table">
          <thead>
            <tr>
              <th>Usuário</th>
              <th>Setor</th>
              <th>Início</th>
              <th>Fim</th>
              <th>Observação</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$usuariosHistorico): ?>
            <tr><td colspan="5" class="muted">Nenhum histórico de usuário registrado.</td></tr>
          <?php else: ?>
            <?php foreach ($usuariosHistorico as $item): ?>
            <tr>
              <td><strong><?= e($item['nome_usuario']) ?></strong><?= empty($item['data_fim']) ? ' <span class="badge b-success">Atual</span>' : '' ?></td>
              <td><?= e($item['setor'] ?: '—') ?></td>
              <td><?= !empty($item['data_inicio']) ? date('d/m/Y H:i', strtotime($item['data_inicio'])) : '—' ?></td>
              <td><?= !empty($item['data_fim']) ? date('d/m/Y H:i', strtotime($item['data_fim'])) : 'Atual' ?></td>
              <td><?= e($item['observacao'] ?: '—') ?></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="card unified-card ticket-management-card-redesign">
      <div class="ticket-section-head ticket-section-head-compact">
        <div>
          <div class="stitle">Todos os chamados vinculados</div>
          <p class="section-subtitle">Histórico completo de ocorrências associadas a este dispositivo.</p>
        </div>
      </div>
      <div class="table-wrap chamados-table-wrap" style="margin-top:8px">
        <table class="chamados-table">
          <thead>
            <tr>
              <th>Protocolo</th>
              <th>Categoria</th>
              <th>Status</th>
              <th>SLA</th>
              <th>Abertura</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$chamados): ?>
            <tr><td colspan="5" class="muted">Nenhum chamado vinculado a este dispositivo.</td></tr>
          <?php else: ?>
            <?php foreach ($chamados as $c): $slaStatus = chamados_sla_status($c); ?>
            <tr>
              <td>
                <a class="table-link" href="chamados_visualizar.php?id=<?= (int) $c['id'] ?>"><strong><?= e($c['protocolo']) ?></strong></a>
                <div class="mini-meta"><?= e($c['assunto']) ?></div>
              </td>
              <td><?= e($c['categoria_nome'] ?: '—') ?></td>
              <td><span class="badge <?= chamados_status_badge_class($c['status']) ?>"><?= e(chamados_status_labels()[$c['status']] ?? $c['status']) ?></span></td>
              <td><span class="badge <?= chamados_sla_badge_class($slaStatus) ?>"><?= e(chamados_sla_label($slaStatus)) ?></span></td>
              <td><?= !empty($c['criado_em']) ? date('d/m/Y H:i', strtotime($c['criado_em'])) : '—' ?></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>

  <aside class="chamados-side-stack chamados-side-stack-redesign">
    <section class="card unified-card ticket-summary-card-redesign" style="margin-bottom:16px">
      <div class="ticket-section-head ticket-section-head-compact">
        <div>
          <div class="stitle">Resumo do dispositivo</div>
          <p class="section-subtitle">Visão rápida do responsável atual e situação do ativo.</p>
        </div>
      </div>
      <div class="summary-list compact-summary-list compact-summary-list-redesign">
        <div><span>Tipo</span><strong><?= e(ucfirst($ativo['tipo'])) ?></strong></div>
        <div><span>Responsável atual</span><strong><?= e($ativo['nome']) ?></strong></div>
        <div><span>Setor</span><strong><?= e($ativo['setor'] ?: '—') ?></strong></div>
        <div><span>Status</span><strong><?= e($ativo['status'] ?: '—') ?></strong></div>
        <div><span>Resolvidos</span><strong><?= (int) $resumo['resolvidos'] ?></strong></div>
        <div><span>Último chamado</span><strong><?= !empty($resumo['ultimo_chamado_em']) ? date('d/m/Y H:i', strtotime($resumo['ultimo_chamado_em'])) : '—' ?></strong></div>
      </div>
    </section>

    <section class="card unified-card chamados-history-card chamados-history-card-redesign">
      <div class="ticket-section-head ticket-section-head-compact">
        <div>
          <div class="stitle">Eventos técnicos recentes</div>
          <p class="section-subtitle">Respostas, notas internas e mudanças importantes ligadas ao dispositivo.</p>
        </div>
      </div>
      <div class="ticket-thread-list">
        <?php if (!$eventos): ?>
          <div class="empty-state" style="min-height:160px"><p>Nenhum evento técnico registrado para este ativo.</p></div>
        <?php else: ?>
          <?php foreach ($eventos as $evento): ?>
            <?php
              $classe = 'requester';
              if (in_array($evento['tipo_evento'], ['mensagem','status','vinculo','desvinculo','chamado_aberto'], true)) {
                  $classe = 'analyst';
              }
              if ($evento['tipo_evento'] === 'nota_interna') {
                  $classe = 'note';
              }
              $autor = $evento['usuario_nome'] ?: 'Sistema';
              $inicial = strtoupper(function_exists('mb_substr') ? mb_substr(trim((string) $autor), 0, 1) : substr(trim((string) $autor), 0, 1));
            ?>
            <article class="ticket-thread <?= $classe ?>">
              <div class="ticket-thread-avatar" aria-hidden="true"><?= e($inicial ?: 'S') ?></div>
              <div class="ticket-thread-content">
                <div class="ticket-thread-head">
                  <div class="ticket-thread-meta">
                    <div class="ticket-thread-author-row">
                      <strong class="ticket-thread-author"><?= e($evento['titulo']) ?></strong>
                      <span class="ticket-thread-role <?= $classe ?>"><?= e(str_replace('_', ' ', $evento['tipo_evento'])) ?></span>
                    </div>
                    <span class="ticket-thread-email"><?= e($autor) ?><?php if (!empty($evento['protocolo'])): ?> · <a class="table-link" href="chamados_visualizar.php?id=<?= (int) $evento['chamado_id'] ?>"><?= e($evento['protocolo']) ?></a><?php endif; ?></span>
                  </div>
                  <time class="ticket-thread-date"><?= date('d/m/Y H:i', strtotime($evento['criado_em'])) ?></time>
                </div>
                <?php if (!empty($evento['descricao'])): ?>
                  <div class="ticket-thread-body"><?= nl2br(e($evento['descricao'])) ?></div>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </aside>
</div>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
