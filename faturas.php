<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
requireAccess('faturas');
$canWrite = can_write_module('faturas');
$pageTitle = 'Faturas';
$db = getDB();

$ano = (int) get_query('ano', date('Y'));
if ($ano < 2000 || $ano > 2100) {
    $ano = (int) date('Y');
}
$mes = query_int('mes', 0, 0, 12);
$q = trim((string) get_query('q', ''));
$filterBy = trim((string) get_query('filter_by', ''));
$filterValue = trim((string) get_query('filter_value', ''));
$page = query_int('page', 1, 1);
$perPage = 15;

$erros = [];
if (request_is_post()) {
    validate_csrf_or_fail($_POST['csrf_token'] ?? null);
    $action = (string) post('action', 'save');

    if ($action === 'delete') {
        $fatId = (int) post('id', 0);
        $oldStmt = $db->prepare('SELECT * FROM faturas WHERE id = :id');
        $oldStmt->execute([':id' => $fatId]);
        $old = $oldStmt->fetch();

        $db->prepare('DELETE FROM faturas WHERE id = :id')->execute([':id' => $fatId]);
        if ($old) {
            audit_log('delete', 'faturas', $fatId, ['registro' => $old]);
        }
        flash('Fatura excluída.');
        redirect('faturas.php' . current_query([], ['page', 'edit']));
    }

    if ($action === 'save') {
        $fid = (int) post('id', 0);
        $fornId = (int) post('fornecedor_id', 0);
        $desc = trim((string) post('descricao', ''));
        $valor = normalize_decimal_br((string) post('valor', ''));
        $dataPag = trim((string) post('data_pagamento', ''));
        if ($dataPag !== '') {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dataPag)) {
                $dtPagamento = DateTime::createFromFormat('d/m/Y', $dataPag);
                $dataPag = $dtPagamento ? $dtPagamento->format('Y-m-d') : $dataPag;
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataPag)) {
                $dtPagamento = DateTime::createFromFormat('Y-m-d', $dataPag);
                $dataPag = $dtPagamento ? $dtPagamento->format('Y-m-d') : $dataPag;
            }
        }
        $mesR = (int) post('mes_referencia', 0);
        $anoR = (int) post('ano_referencia', date('Y'));
        $obs = trim((string) post('observacoes', ''));

        if (!$fornId) add_error($erros, 'Selecione um fornecedor.');
        if ($valor === null) add_error($erros, 'Valor inválido.');
        validate_date_optional($dataPag, 'Data de pagamento', $erros);
        if (!$dataPag) add_error($erros, 'Data de pagamento obrigatória.');
        if ($mesR < 1 || $mesR > 12) add_error($erros, 'Mês de referência obrigatório.');
        if ($anoR < 2000 || $anoR > 2100) add_error($erros, 'Ano de referência inválido.');

        if (empty($erros)) {
            $payload = ['fornecedor_id' => $fornId, 'descricao' => $desc ?: null, 'valor' => $valor, 'data_pagamento' => $dataPag, 'mes_referencia' => $mesR, 'ano_referencia' => $anoR, 'observacoes' => $obs ?: null];
            if ($fid > 0) {
                $oldStmt = $db->prepare('SELECT * FROM faturas WHERE id = :id');
                $oldStmt->execute([':id' => $fid]);
                $old = $oldStmt->fetch();
                $db->prepare('UPDATE faturas SET fornecedor_id=:f, descricao=:d, valor=:v, data_vencimento=:dv, data_pagamento=:dp, mes_referencia=:mr, ano_referencia=:ar, status=:status, observacoes=:o WHERE id=:id')
                   ->execute([':f'=>$fornId, ':d'=>$desc?:null, ':v'=>$valor, ':dv'=>$dataPag, ':dp'=>$dataPag, ':mr'=>$mesR, ':ar'=>$anoR, ':status'=>'Pago', ':o'=>$obs?:null, ':id'=>$fid]);
                audit_log('update', 'faturas', $fid, ['antes' => $old, 'depois' => $payload]);
                flash('Fatura atualizada!');
            } else {
                $db->prepare('INSERT INTO faturas (fornecedor_id, descricao, valor, data_vencimento, data_pagamento, mes_referencia, ano_referencia, status, observacoes) VALUES (:f,:d,:v,:dv,:dp,:mr,:ar,:status,:o)')
                   ->execute([':f'=>$fornId, ':d'=>$desc?:null, ':v'=>$valor, ':dv'=>$dataPag, ':dp'=>$dataPag, ':mr'=>$mesR, ':ar'=>$anoR, ':status'=>'Pago', ':o'=>$obs?:null]);
                $newId = (int) $db->lastInsertId();
                audit_log('create', 'faturas', $newId, $payload);
                flash('Pagamento registrado!');
            }
            redirect('faturas.php?ano=' . $anoR . '&mes=' . $mesR);
        }
    }
}

$editing = null;
if (isset($_GET['edit'])) {
    $s = $db->prepare('SELECT * FROM faturas WHERE id=:id');
    $s->execute([':id'=>(int)$_GET['edit']]);
    $editing = $s->fetch();
}

$fornList = $db->query('SELECT id,nome,categoria FROM fornecedores WHERE ativo=1 ORDER BY nome')->fetchAll();
$fornAll  = $db->query('SELECT id,nome FROM fornecedores ORDER BY nome')->fetchAll();
$MESES = ['','Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];

$hasDescricao = column_exists($db, 'faturas', 'descricao');
$hasObservacoes = column_exists($db, 'faturas', 'observacoes');
$hasStatus = column_exists($db, 'faturas', 'status');

$where = ['f.ano_referencia = :ano'];
$params = [':ano' => $ano];
if ($mes) { $where[] = 'f.mes_referencia = :mes'; $params[':mes'] = $mes; }
if ($q !== '') {
    $searchParts = ['fo.nome LIKE :q_fornecedor'];
    $params[':q_fornecedor'] = '%' . $q . '%';

    if ($hasDescricao) {
        $searchParts[] = 'f.descricao LIKE :q_descricao';
        $params[':q_descricao'] = '%' . $q . '%';
    }
    if ($hasObservacoes) {
        $searchParts[] = 'f.observacoes LIKE :q_observacoes';
        $params[':q_observacoes'] = '%' . $q . '%';
    }

    $where[] = '(' . implode(' OR ', $searchParts) . ')';
}
if ($filterBy === 'fornecedor' && ctype_digit($filterValue) && (int)$filterValue > 0) {
    $where[] = 'f.fornecedor_id = :forn';
    $params[':forn'] = (int) $filterValue;
}
if ($hasStatus && $filterBy === 'status' && in_array($filterValue, ['Pago', 'Pendente'], true)) {
    $where[] = 'f.status = :status';
    $params[':status'] = $filterValue;
}

$countStmt = $db->prepare('SELECT COUNT(*) FROM faturas f JOIN fornecedores fo ON fo.id=f.fornecedor_id WHERE ' . implode(' AND ', $where));
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$pagination = paginate($totalRows, $page, $perPage);

$sql = 'SELECT f.*, fo.nome AS fornecedor_nome, fo.categoria AS fornecedor_cat FROM faturas f JOIN fornecedores fo ON fo.id=f.fornecedor_id WHERE '
     . implode(' AND ', $where)
     . ' ORDER BY f.data_pagamento DESC, f.id DESC LIMIT :limit OFFSET :offset';
$stmt = $db->prepare($sql);
foreach ($params as $key => $value) $stmt->bindValue($key, $value);
$stmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$faturas = $stmt->fetchAll();

$totalStmt = $db->prepare('SELECT COALESCE(SUM(f.valor),0) FROM faturas f JOIN fornecedores fo ON fo.id=f.fornecedor_id WHERE ' . implode(' AND ', $where));
$totalStmt->execute($params);
$totalGeral = (float) $totalStmt->fetchColumn();

$mesesQ = $db->prepare('SELECT mes_referencia, SUM(valor) as total FROM faturas WHERE ano_referencia=:ano GROUP BY mes_referencia');
$mesesQ->execute([':ano'=>$ano]); $mesesMap = [];
foreach ($mesesQ->fetchAll() as $m) $mesesMap[$m['mes_referencia']] = $m['total'];
$totalAnoStmt = $db->prepare('SELECT COALESCE(SUM(valor),0) FROM faturas WHERE ano_referencia=:ano');
$totalAnoStmt->execute([':ano'=>$ano]); $totalAno = (float)$totalAnoStmt->fetchColumn();
$totalPeriodo = $totalGeral;
$mesSelecionadoLabel = $mes ? $MESES[$mes] . '/' . $ano : 'Ano inteiro';
$mediaMensal = $totalAno > 0 ? $totalAno / 12 : 0;
$mesTop = 0; $mesTopValor = 0.0;
foreach ($mesesMap as $mesNum => $mesValor) { if ((float)$mesValor > $mesTopValor) { $mesTop = (int)$mesNum; $mesTopValor = (float)$mesValor; } }

$filterLabelMap = ['fornecedor' => 'Fornecedor', 'status' => 'Status'];
$filterValueLabel = $filterValue;
if ($filterBy === 'fornecedor') {
    foreach ($fornAll as $fornItem) {
        if ((string) $fornItem['id'] === (string) $filterValue) {
            $filterValueLabel = $fornItem['nome'];
            break;
        }
    }
}
$filterOptionsJson = json_encode([
    'fornecedor' => [
        'type' => 'select',
        'placeholder' => 'Selecione o fornecedor',
        'options' => array_map(function ($v) { return array('value' => (string) $v['id'], 'label' => $v['nome']); }, $fornAll),
    ],
    'status' => [
        'type' => 'select',
        'placeholder' => 'Selecione o status',
        'options' => [['value' => 'Pago', 'label' => 'Pago'], ['value' => 'Pendente', 'label' => 'Pendente']],
    ],
], JSON_UNESCAPED_UNICODE);
if (!$hasStatus) {
    $filterOptionsJson = json_encode([
        'fornecedor' => [
            'type' => 'select',
            'placeholder' => 'Selecione o fornecedor',
            'options' => array_map(function ($v) { return array('value' => (string) $v['id'], 'label' => $v['nome']); }, $fornAll),
        ],
    ], JSON_UNESCAPED_UNICODE);
}


if (get_query('export') === 'excel') {
    $exportSql = 'SELECT f.id, fo.nome AS fornecedor, fo.categoria AS categoria, f.descricao, f.mes_referencia, f.ano_referencia, f.data_pagamento, f.valor, f.status, f.observacoes FROM faturas f JOIN fornecedores fo ON fo.id=f.fornecedor_id WHERE '
         . implode(' AND ', $where)
         . ' ORDER BY f.data_pagamento DESC, f.id DESC';
    $exportStmt = $db->prepare($exportSql);
    foreach ($params as $key => $value) {
        $exportStmt->bindValue($key, $value);
    }
    $exportStmt->execute();
    $exportData = $exportStmt->fetchAll() ?: [];
    $headers = ['ID', 'Fornecedor', 'Categoria', 'Descrição', 'Mês', 'Ano', 'Data pagamento', 'Valor', 'Status', 'Observações'];
    $rows = [];
    foreach ($exportData as $item) {
        $rows[] = [
            $item['id'] ?? '',
            $item['fornecedor'] ?? '',
            $item['categoria'] ?? '',
            $item['descricao'] ?? '',
            $item['mes_referencia'] ?? '',
            $item['ano_referencia'] ?? '',
            $item['data_pagamento'] ?? '',
            $item['valor'] ?? '',
            $item['status'] ?? '',
            $item['observacoes'] ?? '',
        ];
    }
    if (!$rows) {
        $rows[] = ['','','','','','','','','','Nenhum registro encontrado'];
    }
    export_excel_xml('faturas_export.xls', $headers, $rows);
}

include 'includes/header.php';
echo render_flash();
?>

<div class="page-head">
    <div class="page-head-copy">
        <h2>Financeiro e faturas</h2>
        
    </div>
    <div class="page-head-actions">
        <?php if ($canWrite): ?><button onclick="document.getElementById('modal-fat').style.display='flex'" class="btn btn-primary btn-sm"><?= icon('plus') ?> Registrar Pagamento</button><?php endif; ?><a href="faturas.php<?= e(current_query(['export' => 'excel'], ['page'])) ?>" class="btn btn-sm btn-export">Exportar Excel</a>
    </div>
</div>
<?php if (!empty($erros)): ?><div class="alert alert-error"><?= icon('off') ?> <?= implode(' · ', array_map('e',$erros)) ?></div><?php endif; ?>

<div class="faturas-hero">
    <div class="card faturas-chart-card">
        <div class="faturas-chart-head">
            <div>
                <h3><?= icon('bill') ?> Evolução mensal de pagamentos</h3>
                <p>Clique em um mês para filtrar a listagem abaixo. <?= $mes ? 'Visualizando atualmente <strong>' . e($MESES[$mes] . '/' . $ano) . '</strong>.' : 'Visualizando o consolidado do ano.' ?></p>
            </div>
            <div class="faturas-total-box">
                <span class="label">Total <?= e($mesSelecionadoLabel) ?></span>
                <span class="value">R$<?= number_format($totalPeriodo,2,',','.') ?></span>
            </div>
        </div>
        <div class="faturas-chart">
            <?php $maxVal = $mesesMap ? max(array_values($mesesMap)) : 1; for ($m=1; $m<=12; $m++): $val = (float)($mesesMap[$m] ?? 0); $barH = $maxVal > 0 ? max(10, round(($val/$maxVal)*170)) : 10; $active = $mes === $m; $muted = $val <= 0; ?>
            <a class="faturas-bar-col <?= $active ? 'is-active' : '' ?> <?= $muted ? 'is-muted' : '' ?>" href="faturas.php<?= e(current_query(['mes' => $m===$mes ? 0 : $m, 'page' => null], [])) ?>">
                <div class="faturas-bar-wrap"><div class="faturas-bar" style="height:<?= $barH ?>px"></div></div>
                <div class="faturas-bar-label"><?= $MESES[$m] ?></div>
                <div class="faturas-bar-value"><?= $val > 0 ? 'R$'.number_format($val,0,',','.') : '—' ?></div>
            </a>
            <?php endfor; ?>
        </div>
    </div>

    <div class="card faturas-side-card">
        <div class="faturas-side-top">
            <div>
                <h3 class="faturas-side-title">Resumo do período</h3>
               
            </div>
            <form method="get" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end">
                <?php if ($mes): ?><input type="hidden" name="mes" value="<?= $mes ?>"><?php endif; ?>
                <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
                <?php if ($filterBy !== ''): ?><input type="hidden" name="filter_by" value="<?= e($filterBy) ?>"><?php endif; ?>
                <?php if ($filterValue !== ''): ?><input type="hidden" name="filter_value" value="<?= e($filterValue) ?>"><?php endif; ?>
                <select name="ano" class="toolbar-select" style="width:110px">
                    <?php for($y=date('Y')-3;$y<=date('Y')+1;$y++): ?>
                    <option value="<?= $y ?>" <?= $ano===$y?'selected':'' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <button class="btn btn-ghost btn-sm" type="submit">Trocar ano</button>
            </form>
        </div>
        <div class="faturas-kpis">
            <div class="faturas-kpi">
                <div class="k">Total do ano</div>
                <div class="v">R$<?= number_format($totalAno,2,',','.') ?></div>
                <div class="s">Soma de todos os pagamentos em <?= $ano ?>.</div>
            </div>
            <div class="faturas-kpi">
                <div class="k">Média mensal</div>
                <div class="v">R$<?= number_format($mediaMensal,2,',','.') ?></div>
                <div class="s">Referência média considerando os 12 meses do ano.</div>
            </div>
            <div class="faturas-kpi">
                <div class="k">Maior mês</div>
                <div class="v"><?= $mesTop ? e($MESES[$mesTop]) : '—' ?></div>
                <div class="s"><?= $mesTop ? 'R$' . number_format($mesTopValor,2,',','.') : 'Sem pagamentos lançados.' ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card" style="padding:0;overflow:hidden">
    <form method="get" class="toolbar-shell" id="faturas-filter-form">
        <input type="hidden" name="ano" value="<?= $ano ?>">
        <?php if ($mes): ?><input type="hidden" name="mes" value="<?= $mes ?>"><?php endif; ?>
        <div class="toolbar-grow">
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Buscar por fornecedor ou descrição">
        </div>
        <div class="toolbar-field">
            <select name="filter_by" id="faturas-filter-by">
                <option value="">Filtro complementar</option>
                <option value="fornecedor" <?= $filterBy==='fornecedor'?'selected':'' ?>>Fornecedor</option>
                <?php if ($hasStatus): ?><option value="status" <?= $filterBy==='status'?'selected':'' ?>>Status</option><?php endif; ?>
            </select>
        </div>
        <div class="toolbar-field" id="faturas-filter-value-slot"></div>
        <div class="toolbar-actions">
            <button type="submit" class="btn btn-ghost btn-sm"><?= icon('filter') ?> Filtrar</button>
            <a href="faturas.php?ano=<?= $ano ?><?= $mes?"&mes=$mes":'' ?>" class="btn btn-ghost btn-sm">Limpar</a>
        </div>
    </form>
    <div class="table-footer-meta" style="border-top:none;border-bottom:1px solid var(--line)">
        <div class="summary"><?php if ($filterBy && $filterValueLabel !== ''): ?>Filtro aplicado: <strong><?= e($filterLabelMap[$filterBy] ?? $filterBy) ?></strong> = <?= e($filterValueLabel) ?><?php else: ?>&nbsp;<?php endif; ?></div>
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap"><span style="font-size:12px;color:var(--muted2)"><?= $totalRows ?> registro<?= $totalRows!=1?'s':'' ?></span>
        </div>
    </div>

    <?php if (empty($faturas)): ?>
    <div class="empty-state"><?= icon('bill') ?><p>Nenhum pagamento registrado<?= $mes ? ' em '.$MESES[$mes].'/'.$ano : ' em '.$ano ?>.<?php if ($canWrite): ?><br><button onclick="document.getElementById('modal-fat').style.display='flex'" style="background:none;border:none;color:var(--accent);cursor:pointer;font-size:14px;padding:0;text-decoration:underline">Registrar agora</button><?php endif; ?></p></div>
    <?php else: ?>
    <div class="table-wrap wide-table"><table><thead><tr><th>#</th><th>Fornecedor</th><th>Descrição</th><th>Referência</th><th>Data Pagamento</th><th>Valor</th><th style="width:72px">Ações</th></tr></thead><tbody><?php foreach ($faturas as $f): ?><tr><td class="mono" style="color:var(--muted2)"><?= (int)$f['id'] ?></td><td><strong><?= e($f['fornecedor_nome']) ?></strong><?php if ($f['fornecedor_cat']): ?><div class="sub"><?= e($f['fornecedor_cat']) ?></div><?php endif; ?></td><td style="color:var(--accent)"><?= e($f['descricao']?:'—') ?></td><td class="mono" style="color:var(--accent)"><?= $MESES[$f['mes_referencia']].'/'.$f['ano_referencia'] ?></td><td class="mono" style="color:var(--accent)"><?= date('d/m/Y', strtotime($f['data_pagamento'])) ?></td><td style="font-weight:700;font-size:15px;color:var(--text)">R$<?= number_format($f['valor'],2,',','.') ?></td><td><?php if ($canWrite): ?><div class="act-btns"><a href="faturas.php<?= e(current_query(['edit' => (int)$f['id']], ['page'])) ?>" class="btn-icon edit"><?= icon('edit') ?></a><form method="post" style="display:inline" onsubmit="return confirm('Excluir este registro?')"><?= csrf_input() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$f['id'] ?>"><button type="submit" class="btn-icon del" style="border:none;background:none;padding:0"><?= icon('trash') ?></button></form></div><?php else: ?><span class="sub">Somente leitura</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
    <?= render_pagination($pagination) ?>
    <?php endif; ?>
</div>

<?php if ($canWrite): ?><div id="modal-fat" style="display:<?= ($editing||!empty($erros))?'flex':'none' ?>;position:fixed;inset:0;background:rgba(0,0,0,.78);z-index:500;align-items:center;justify-content:center;backdrop-filter:blur(4px)" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:#171a21;border:1px solid var(--bdr2);border-radius:14px;padding:26px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto;margin:16px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px"><div class="stitle" style="margin:0"><?= icon('bill') ?> <?= $editing ? 'Editar Pagamento' : 'Registrar Pagamento' ?></div><button onclick="document.getElementById('modal-fat').style.display='none'" style="background:#252a36;border:1px solid var(--bdr2);color:var(--accent);width:28px;height:28px;border-radius:6px;cursor:pointer;font-size:18px;line-height:1;display:flex;align-items:center;justify-content:center">×</button></div>
        <form method="post">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= $editing['id'] ?? 0 ?>">
            <div class="form-grid">
                <div class="form-group full"><label>Fornecedor <span class="req">*</span></label><select name="fornecedor_id"><option value="">— Selecione —</option><?php foreach ($fornList as $f): $sel = ($editing['fornecedor_id']??$_POST['fornecedor_id']??'')==$f['id']?'selected':''; ?><option value="<?= $f['id'] ?>" <?= $sel ?>><?= e($f['nome']) ?><?= $f['categoria']?' · '.e($f['categoria']):'' ?></option><?php endforeach; ?></select><?php if (empty($fornList)): ?><div style="font-size:12.5px;color:var(--clr-amber);margin-top:4px">Nenhum fornecedor ativo. <a href="fornecedores.php" style="color:var(--clr-amber)">Cadastre um primeiro.</a></div><?php endif; ?></div>
                <div class="form-group full"><label>Descrição</label><input type="text" name="descricao" placeholder="Ex: Mensalidade março, Licença anual..." value="<?= e($editing['descricao']??$_POST['descricao']??'') ?>"></div>
                <div class="form-group"><label>Valor (R$) <span class="req">*</span></label><input type="text" name="valor" placeholder="0,00" value="<?= $editing ? number_format($editing['valor'],2,',','.') : e($_POST['valor']??'') ?>"></div>
                <div class="form-group"><label>Data de Pagamento <span class="req">*</span></label><input type="date" name="data_pagamento" value="<?= e($editing['data_pagamento']??$_POST['data_pagamento']??date('Y-m-d')) ?>"></div>
                <div class="form-group"><label>Mês de Referência <span class="req">*</span></label><select name="mes_referencia"><option value="">— Mês —</option><?php for($m=1;$m<=12;$m++): $sel=($editing['mes_referencia']??($mes?:date('n')))==$m?'selected':''; ?><option value="<?= $m ?>" <?= $sel ?>><?= $MESES[$m] ?></option><?php endfor; ?></select></div>
                <div class="form-group"><label>Ano de Referência <span class="req">*</span></label><select name="ano_referencia" class="toolbar-select"><?php for($y=date('Y')-2;$y<=date('Y')+1;$y++): $sel=($editing['ano_referencia']??$ano)===$y?'selected':''; ?><option value="<?= $y ?>" <?= $sel ?>><?= $y ?></option><?php endfor; ?></select></div>
                <div class="form-group full"><label>Observações</label><textarea name="observacoes" placeholder="Notas adicionais..." style="min-height:70px"><?= e($editing['observacoes']??$_POST['observacoes']??'') ?></textarea></div>
            </div>
            <div style="display:flex;gap:9px;margin-top:20px"><button type="submit" class="btn btn-primary"><?= icon('check') ?> <?= $editing?'Salvar':'Registrar' ?></button><button type="button" onclick="document.getElementById('modal-fat').style.display='none'" class="btn btn-ghost"><?= icon('back') ?> Cancelar</button></div>
        </form>
    </div>
</div><?php endif; ?>
<script>
(function(){
    const config = <?= $filterOptionsJson ?>;
    const select = document.getElementById('faturas-filter-by');
    const slot = document.getElementById('faturas-filter-value-slot');
    const currentValue = <?= json_encode($filterValue, JSON_UNESCAPED_UNICODE) ?>;
    function esc(value){ return String(value).replace(/"/g, '&quot;'); }
    function renderField(){
        const key = select.value;
        if (!key || !config[key]) {
            slot.innerHTML = '<input type="text" value="" placeholder="Valor do filtro" disabled>';
            return;
        }
        const item = config[key];
        let html = '<select name="filter_value">';
        html += '<option value="">' + item.placeholder + '</option>';
        item.options.forEach(function(opt){
            const selected = String(opt.value) === String(currentValue) ? ' selected' : '';
            html += '<option value="' + esc(opt.value) + '"' + selected + '>' + opt.label + '</option>';
        });
        html += '</select>';
        slot.innerHTML = html;
    }
    renderField();
    select.addEventListener('change', renderField);
})();
</script>
<?php include 'includes/footer.php'; ?>
