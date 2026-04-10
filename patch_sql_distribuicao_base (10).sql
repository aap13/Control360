<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
if (request_is_post()) {
    require_write_access(resolveTipoModule($_POST['tipo'] ?? $_GET['tipo'] ?? ''));
}
require_once __DIR__ . '/includes/setor_options.php';

$tipo = $_GET['tipo'] ?? '';
$id   = (int)($_GET['id'] ?? 0);
if (!in_array($tipo,['computador','celular'], true) || $id <= 0) {
    redirect('index.php');
}

$db    = getDB();
$table = $tipo==='computador' ? 'computadores' : 'celulares';
$stmt  = $db->prepare("SELECT * FROM $table WHERE id=:id");
$stmt->execute([':id'=>$id]);
$r = $stmt->fetch();

if (!$r) {
    flash('Registro não encontrado.', 'error');
    redirect($tipo==='computador' ? 'computadores.php' : 'celulares.php');
}

$pageTitle = ($tipo==='computador'?'Computador':'Celular').' #'.$id;
$erros = [];

if (request_is_post()) {
    validate_csrf_or_fail($_POST['csrf_token'] ?? null);
    $marca   = trim((string) post('marca', ''));
    $modelo  = trim((string) post('modelo', ''));
    $usuario = trim((string) post('usuario_responsavel', ''));
    $setor   = trim((string) post('setor', ''));
    $status  = trim((string) post('status', ''));
    $data_aq = trim((string) post('data_aquisicao', '')) ?: null;

    validate_required($marca, 'Marca', $erros);
    validate_required($modelo, 'Modelo', $erros);
    validate_required($usuario, 'Usuário responsável', $erros);
    validate_required($setor, 'Setor', $erros);
    validate_in_list($status, ['Em uso','Disponível','Manutenção','Desativado'], 'Status', $erros);
    validate_date_optional($data_aq, 'Data de aquisição', $erros);

    if ($tipo === 'celular') {
        validate_imei_optional((string) post('imei', ''), $erros);
    }

    if (empty($erros)) {
        if ($tipo==='computador') {
            $payload = [
                'nome_dispositivo' => trim((string) post('nome_dispositivo', '')) ?: null,
                'tipo' => (string) post('tipo_comp', 'Desktop'),
                'marca' => $marca,
                'modelo' => $modelo,
                'numero_serie' => trim((string) post('numero_serie', '')) ?: null,
                'patrimonio' => trim((string) post('patrimonio', '')) ?: null,
                'processador' => trim((string) post('processador', '')) ?: null,
                'ram' => trim((string) post('ram', '')) ?: null,
                'armazenamento' => trim((string) post('armazenamento', '')) ?: null,
                'sistema_operacional' => trim((string) post('sistema_operacional', '')) ?: null,
                'usuario_responsavel' => $usuario,
                'setor' => $setor,
                'localizacao' => trim((string) post('localizacao', '')) ?: null,
                'status' => $status,
                'data_aquisicao' => $data_aq,
                'observacoes' => trim((string) post('observacoes', '')) ?: null,
            ];

            $db->prepare("UPDATE computadores SET nome_dispositivo=:nome,tipo=:tipo,marca=:marca,modelo=:modelo,numero_serie=:ns,patrimonio=:pat,processador=:proc,ram=:ram,armazenamento=:arm,sistema_operacional=:so,usuario_responsavel=:usuario,setor=:setor,localizacao=:loc,status=:status,data_aquisicao=:da,observacoes=:obs WHERE id=:id")
               ->execute([':nome'=>$payload['nome_dispositivo'], ':tipo'=>$payload['tipo'], ':marca'=>$payload['marca'], ':modelo'=>$payload['modelo'], ':ns'=>$payload['numero_serie'], ':pat'=>$payload['patrimonio'], ':proc'=>$payload['processador'], ':ram'=>$payload['ram'], ':arm'=>$payload['armazenamento'], ':so'=>$payload['sistema_operacional'], ':usuario'=>$payload['usuario_responsavel'], ':setor'=>$payload['setor'], ':loc'=>$payload['localizacao'], ':status'=>$payload['status'], ':da'=>$payload['data_aquisicao'], ':obs'=>$payload['observacoes'], ':id'=>$id]);

            audit_log('update', 'computadores', $id, ['antes' => $r, 'depois' => $payload]);
            flash('Computador atualizado!');
            redirect('computadores.php');
        }

        $payload = [
            'tipo' => (string) post('tipo_cel', 'Smartphone'),
            'marca' => $marca,
            'modelo' => $modelo,
            'numero_serie' => trim((string) post('numero_serie', '')) ?: null,
            'imei' => trim((string) post('imei', '')) ?: null,
            'numero_chip' => trim((string) post('numero_chip', '')) ?: null,
            'operadora' => trim((string) post('operadora', '')) ?: null,
            'usuario_responsavel' => $usuario,
            'setor' => $setor,
            'mdm_ativo' => (int) post('mdm_ativo', 0),
            'status' => $status,
            'data_aquisicao' => $data_aq,
            'observacoes' => trim((string) post('observacoes', '')) ?: null,
        ];

        $db->prepare("UPDATE celulares SET tipo=:tipo,marca=:marca,modelo=:modelo,numero_serie=:ns,imei=:imei,numero_chip=:num,operadora=:op,usuario_responsavel=:usuario,setor=:setor,mdm_ativo=:mdm,status=:status,data_aquisicao=:da,observacoes=:obs WHERE id=:id")
           ->execute([':tipo'=>$payload['tipo'], ':marca'=>$payload['marca'], ':modelo'=>$payload['modelo'], ':ns'=>$payload['numero_serie'], ':imei'=>$payload['imei'], ':num'=>$payload['numero_chip'], ':op'=>$payload['operadora'], ':usuario'=>$payload['usuario_responsavel'], ':setor'=>$payload['setor'], ':mdm'=>$payload['mdm_ativo'], ':status'=>$payload['status'], ':da'=>$payload['data_aquisicao'], ':obs'=>$payload['observacoes'], ':id'=>$id]);

        audit_log('update', 'celulares', $id, ['antes' => $r, 'depois' => $payload]);
        flash('Celular atualizado!');
        redirect('celulares.php');
    }

    $r = array_merge($r, $_POST);
}

include 'includes/header.php';
echo render_flash();
?>
<div class="page-head"><div class="page-head-copy"><h2><?= $tipo==='computador' ? 'Editar computador' : 'Editar celular' ?></h2><p>Atualize o cadastro mantendo o mesmo padrão visual do restante do sistema.</p></div></div>
<?php if (!empty($erros)): ?>
<div class="alert alert-error"><?= icon('off') ?> <?= implode(' · ', array_map('e',$erros)) ?></div>
<?php endif; ?>

<div style="margin-bottom:18px">
    <a href="<?= $tipo==='computador'?'computadores.php':'celulares.php' ?>" class="btn btn-ghost btn-sm"><?= icon('back') ?> Voltar</a>
</div>

<div class="card">
<form method="post">
<?= csrf_input() ?>

<?php if ($tipo==='computador'): ?>
<div class="stitle"><?= icon('computer') ?> Identificação</div>
<div class="form-grid" style="margin-bottom:22px">
    <div class="form-group full"><label>Nome do Dispositivo</label><input type="text" name="nome_dispositivo" value="<?= e($r['nome_dispositivo']??'') ?>"></div>
    <div class="form-group"><label>Tipo</label><select name="tipo_comp"><?php foreach(['Desktop','Notebook','All-in-One','Workstation','Servidor'] as $t): ?><option <?= ($r['tipo']??'')===$t?'selected':'' ?>><?= $t ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Status</label><select name="status"><?php foreach(['Em uso','Disponível','Manutenção','Desativado'] as $s): ?><option <?= ($r['status']??'')===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Marca <span class="req">*</span></label><input type="text" name="marca" value="<?= e($r['marca']??'') ?>"></div>
    <div class="form-group"><label>Modelo <span class="req">*</span></label><input type="text" name="modelo" value="<?= e($r['modelo']??'') ?>"></div>
    <div class="form-group"><label>Número de Série</label><input type="text" name="numero_serie" value="<?= e($r['numero_serie']??'') ?>"></div>
    <div class="form-group"><label>Nº Patrimônio</label><input type="text" name="patrimonio" value="<?= e($r['patrimonio']??'') ?>"></div>
    <div class="form-group"><label>Data de Aquisição</label><input type="date" name="data_aquisicao" value="<?= e($r['data_aquisicao']??'') ?>"></div>
</div>
<div class="stitle"><?= icon('chip') ?> Hardware</div>
<div class="form-grid" style="margin-bottom:22px">
    <div class="form-group full"><label>Processador</label><input type="text" name="processador" value="<?= e($r['processador']??'') ?>"></div>
    <div class="form-group"><label>RAM</label><input type="text" name="ram" value="<?= e($r['ram']??'') ?>"></div>
    <div class="form-group"><label>Armazenamento</label><input type="text" name="armazenamento" value="<?= e($r['armazenamento']??'') ?>"></div>
    <div class="form-group full"><label>Sistema Operacional</label><select name="sistema_operacional"><option value="">— Selecione —</option><?php foreach(['Windows 10','Windows 11','Windows 7','Ubuntu','macOS','Linux Mint','Chrome OS','Outro'] as $so): ?><option <?= ($r['sistema_operacional']??'')===$so?'selected':'' ?>><?= $so ?></option><?php endforeach; ?></select></div>
</div>
<div class="stitle"><?= icon('search') ?> Responsável</div>
<div class="form-grid" style="margin-bottom:22px">
    <div class="form-group"><label>Usuário Responsável <span class="req">*</span></label><input type="text" name="usuario_responsavel" value="<?= e($r['usuario_responsavel']??'') ?>"></div>
    <div class="form-group"><label>Setor <span class="req">*</span></label><select name="setor"><option value="">— Selecione —</option><?php foreach($SETORES as $s): ?><option value="<?= e($s) ?>" <?= ($r['setor']??'')===$s?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Localização</label><select name="localizacao"><option value="">— Selecione —</option><option value="CSF SLZ" <?= ($r['localizacao']??'')==='CSF SLZ'?'selected':'' ?>>CSF SLZ</option><option value="CSF FOR" <?= ($r['localizacao']??'')==='CSF FOR'?'selected':'' ?>>CSF FOR</option></select></div>
    <div class="form-group full"><label>Observações</label><textarea name="observacoes"><?= e($r['observacoes']??'') ?></textarea></div>
</div>
<?php else: ?>
<div class="stitle"><?= icon('phone') ?> Dispositivo</div>
<div class="form-grid" style="margin-bottom:22px">
    <div class="form-group"><label>Tipo</label><select name="tipo_cel"><option <?= ($r['tipo']??'')==='Smartphone'?'selected':'' ?>>Smartphone</option><option <?= ($r['tipo']??'')==='Tablet'?'selected':'' ?>>Tablet</option></select></div>
    <div class="form-group"><label>Status</label><select name="status"><?php foreach(['Em uso','Disponível','Manutenção','Desativado'] as $s): ?><option <?= ($r['status']??'')===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Marca <span class="req">*</span></label><input type="text" name="marca" value="<?= e($r['marca']??'') ?>"></div>
    <div class="form-group"><label>Modelo <span class="req">*</span></label><input type="text" name="modelo" value="<?= e($r['modelo']??'') ?>"></div>
    <div class="form-group"><label>Número de Série</label><input type="text" name="numero_serie" value="<?= e($r['numero_serie']??'') ?>"></div>
    <div class="form-group"><label>IMEI</label><input type="text" name="imei" value="<?= e($r['imei']??'') ?>"></div>
    <div class="form-group"><label>Número do Chip</label><input type="text" name="numero_chip" value="<?= e($r['numero_chip']??'') ?>"></div>
    <div class="form-group"><label>Operadora</label><select name="operadora"><option value="">— Selecione —</option><?php foreach(['Claro','Vivo','TIM','Oi','Algar','Nextel','Sem chip','Outra'] as $op): ?><option <?= ($r['operadora']??'')===$op?'selected':'' ?>><?= $op ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>MDM</label><select name="mdm_ativo"><option value="0" <?= (($r['mdm_ativo']??'0')=='0')?'selected':'' ?>>Desativado</option><option value="1" <?= (($r['mdm_ativo']??'0')=='1')?'selected':'' ?>>Ativado</option></select></div>
    <div class="form-group"><label>Data de Aquisição</label><input type="date" name="data_aquisicao" value="<?= e($r['data_aquisicao']??'') ?>"></div>
</div>
<div class="stitle"><?= icon('search') ?> Responsável</div>
<div class="form-grid" style="margin-bottom:22px">
    <div class="form-group"><label>Usuário Responsável <span class="req">*</span></label><input type="text" name="usuario_responsavel" value="<?= e($r['usuario_responsavel']??'') ?>"></div>
    <div class="form-group"><label>Setor <span class="req">*</span></label><select name="setor"><option value="">— Selecione —</option><?php foreach($SETORES as $s): ?><option value="<?= e($s) ?>" <?= ($r['setor']??'')===$s?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?></select></div>
    <div class="form-group full"><label>Observações</label><textarea name="observacoes"><?= e($r['observacoes']??'') ?></textarea></div>
</div>
<?php endif; ?>

<div style="display:flex;gap:11px;flex-wrap:wrap">
    <button type="submit" class="btn btn-primary"><?= icon('check') ?> Salvar Alterações</button>
    <form method="post" action="excluir.php" style="display:inline" onsubmit="return confirm('Excluir este registro?')">
        <?= csrf_input() ?>
        <input type="hidden" name="tipo" value="<?= e($tipo) ?>">
        <input type="hidden" name="id" value="<?= (int)$id ?>">
        <button type="submit" class="btn btn-danger"><?= icon('trash') ?> Excluir</button>
    </form>
    <a href="<?= $tipo==='computador'?'computadores.php':'celulares.php' ?>" class="btn btn-ghost"><?= icon('back') ?> Cancelar</a>
</div>
</form>
</div>

<div style="margin-top:14px;padding:11px 16px;background:var(--bg2);border:1px solid var(--bdr);border-radius:var(--radius);font-size:12px;color:var(--t3);font-family:'JetBrains Mono',monospace">
    Cadastrado em: <?= e($r['data_cadastro']??'—') ?> · Atualizado em: <?= e($r['data_atualizacao']??'—') ?>
</div>
<?php include 'includes/footer.php'; ?>
