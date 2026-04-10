<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
if (request_is_post()) {
    require_write_access(resolveTipoModule($_POST['tipo_dispositivo'] ?? $_GET['tipo'] ?? 'computador'));
}
require_once __DIR__ . '/includes/setor_options.php';

$tipo = $_GET['tipo'] ?? 'computador';
if (!in_array($tipo, ['computador','celular'], true)) $tipo = 'computador';
$pageTitle = 'Novo ' . ($tipo==='computador' ? 'Computador' : 'Celular');
guard_current_page_access();
$erros = [];

if (request_is_post()) {
    validate_csrf_or_fail($_POST['csrf_token'] ?? null);
    $db   = getDB();
    $tipo = $_POST['tipo_dispositivo'] ?? $tipo;
    $marca   = trim((string) post('marca', ''));
    $modelo  = trim((string) post('modelo', ''));
    $usuario = trim((string) post('usuario_responsavel', ''));
    $setor   = trim((string) post('setor', ''));
    $status  = trim((string) post('status', 'Em uso'));
    $data_aq = trim((string) post('data_aquisicao', '')) ?: null;
    $obs     = trim((string) post('observacoes', '')) ?: null;
    $n_serie = trim((string) post('numero_serie', '')) ?: null;

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
        if ($tipo === 'computador') {
            $payload = [
                'nome_dispositivo' => trim((string) post('nome_dispositivo', '')) ?: null,
                'tipo' => (string) post('tipo_comp', 'Desktop'),
                'marca' => $marca,
                'modelo' => $modelo,
                'numero_serie' => $n_serie,
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
                'observacoes' => $obs,
            ];

            $st = $db->prepare("INSERT INTO computadores (nome_dispositivo,tipo,marca,modelo,numero_serie,patrimonio,processador,ram,armazenamento,sistema_operacional,usuario_responsavel,setor,localizacao,status,data_aquisicao,observacoes) VALUES (:nome,:tipo,:marca,:modelo,:ns,:pat,:proc,:ram,:arm,:so,:usuario,:setor,:loc,:status,:data_aq,:obs)");
            $st->execute([':nome'=>$payload['nome_dispositivo'], ':tipo'=>$payload['tipo'], ':marca'=>$payload['marca'], ':modelo'=>$payload['modelo'], ':ns'=>$payload['numero_serie'], ':pat'=>$payload['patrimonio'], ':proc'=>$payload['processador'], ':ram'=>$payload['ram'], ':arm'=>$payload['armazenamento'], ':so'=>$payload['sistema_operacional'], ':usuario'=>$payload['usuario_responsavel'], ':setor'=>$payload['setor'], ':loc'=>$payload['localizacao'], ':status'=>$payload['status'], ':data_aq'=>$payload['data_aquisicao'], ':obs'=>$payload['observacoes']]);

            $newId = (int) $db->lastInsertId();
            audit_log('create', 'computadores', $newId, $payload);
            flash('Computador cadastrado com sucesso!');
            redirect('computadores.php');
        }

        $payload = [
            'tipo' => (string) post('tipo_cel', 'Smartphone'),
            'marca' => $marca,
            'modelo' => $modelo,
            'numero_serie' => $n_serie,
            'imei' => trim((string) post('imei', '')) ?: null,
            'numero_chip' => trim((string) post('numero_chip', '')) ?: null,
            'operadora' => trim((string) post('operadora', '')) ?: null,
            'usuario_responsavel' => $usuario,
            'setor' => $setor,
            'mdm_ativo' => (int) post('mdm_ativo', 0),
            'status' => $status,
            'data_aquisicao' => $data_aq,
            'observacoes' => $obs,
        ];

        $st = $db->prepare("INSERT INTO celulares (tipo,marca,modelo,numero_serie,imei,numero_chip,operadora,usuario_responsavel,setor,mdm_ativo,status,data_aquisicao,observacoes) VALUES (:tipo,:marca,:modelo,:ns,:imei,:num,:op,:usuario,:setor,:mdm,:status,:data_aq,:obs)");
        $st->execute([':tipo'=>$payload['tipo'], ':marca'=>$payload['marca'], ':modelo'=>$payload['modelo'], ':ns'=>$payload['numero_serie'], ':imei'=>$payload['imei'], ':num'=>$payload['numero_chip'], ':op'=>$payload['operadora'], ':usuario'=>$payload['usuario_responsavel'], ':setor'=>$payload['setor'], ':mdm'=>$payload['mdm_ativo'], ':status'=>$payload['status'], ':data_aq'=>$payload['data_aquisicao'], ':obs'=>$payload['observacoes']]);

        $newId = (int) $db->lastInsertId();
        audit_log('create', 'celulares', $newId, $payload);
        flash('Celular cadastrado com sucesso!');
        redirect('celulares.php');
    }
}

include 'includes/header.php';
$v = $_POST;
echo render_flash();
?>
<div class="page-head"><div class="page-head-copy"><h2><?= $tipo==='computador' ? 'Cadastrar computador' : 'Cadastrar celular' ?></h2><p>Preencha os dados do ativo em blocos padronizados de identificação, vínculo e especificações técnicas.</p></div></div>
<?php if (!empty($erros)): ?>
<div class="alert alert-error"><?= icon('off') ?> <?= implode(' · ', array_map('e',$erros)) ?></div>
<?php endif; ?>

<!-- Type switcher -->
<div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
    <a href="cadastrar.php?tipo=computador" class="btn <?= $tipo==='computador' ? 'btn-primary' : 'btn-ghost' ?> btn-sm"><?= icon('computer') ?> Computador</a>
    <a href="cadastrar.php?tipo=celular" class="btn <?= $tipo==='celular' ? 'btn-primary' : 'btn-ghost' ?> btn-sm"><?= icon('phone') ?> Celular</a>
</div>

<div class="card">
<form method="post">
<?= csrf_input() ?>
<input type="hidden" name="tipo_dispositivo" value="<?= e($tipo) ?>">

<?php if ($tipo==='computador'): ?>
<div class="stitle"><?= icon('computer') ?> Identificação</div>
<div class="form-grid" style="margin-bottom:22px">
    <div class="form-group full"><label>Nome do Dispositivo</label><input type="text" name="nome_dispositivo" value="<?= e($v['nome_dispositivo']??'') ?>"></div>
    <div class="form-group"><label>Tipo</label><select name="tipo_comp"><?php foreach(['Desktop','Notebook','All-in-One','Workstation','Servidor'] as $t): ?><option <?= ($v['tipo_comp']??'Desktop')===$t?'selected':'' ?>><?= $t ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Status</label><select name="status"><?php foreach(['Em uso','Disponível','Manutenção','Desativado'] as $s): ?><option <?= ($v['status']??'Em uso')===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Marca <span class="req">*</span></label><input type="text" name="marca" value="<?= e($v['marca']??'') ?>"></div>
    <div class="form-group"><label>Modelo <span class="req">*</span></label><input type="text" name="modelo" value="<?= e($v['modelo']??'') ?>"></div>
    <div class="form-group"><label>Número de Série</label><input type="text" name="numero_serie" value="<?= e($v['numero_serie']??'') ?>"></div>
    <div class="form-group"><label>Nº Patrimônio</label><input type="text" name="patrimonio" value="<?= e($v['patrimonio']??'') ?>"></div>
    <div class="form-group"><label>Data de Aquisição</label><input type="date" name="data_aquisicao" value="<?= e($v['data_aquisicao']??'') ?>"></div>
</div>
<div class="stitle"><?= icon('chip') ?> Hardware</div>
<div class="form-grid" style="margin-bottom:22px">
    <div class="form-group full"><label>Processador</label><input type="text" name="processador" value="<?= e($v['processador']??'') ?>"></div>
    <div class="form-group"><label>RAM</label><input type="text" name="ram" value="<?= e($v['ram']??'') ?>"></div>
    <div class="form-group"><label>Armazenamento</label><input type="text" name="armazenamento" value="<?= e($v['armazenamento']??'') ?>"></div>
    <div class="form-group full"><label>Sistema Operacional</label>
        <select name="sistema_operacional"><option value="">— Selecione —</option><?php foreach(['Windows 10','Windows 11','Windows 7','Ubuntu','macOS','Linux Mint','Chrome OS','Outro'] as $so): ?><option <?= ($v['sistema_operacional']??'')===$so?'selected':'' ?>><?= $so ?></option><?php endforeach; ?></select></div>
</div>
<div class="stitle"><?= icon('search') ?> Responsável</div>
<div class="form-grid" style="margin-bottom:22px">
    <div class="form-group"><label>Usuário Responsável <span class="req">*</span></label><input type="text" name="usuario_responsavel" value="<?= e($v['usuario_responsavel']??'') ?>"></div>
    <div class="form-group"><label>Setor <span class="req">*</span></label>
        <select name="setor"><option value="">— Selecione —</option><?php foreach($SETORES as $s): ?><option value="<?= e($s) ?>" <?= ($v['setor']??'')===$s?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Localização</label><select name="localizacao"><option value="">— Selecione —</option><option value="CSF SLZ" <?= ($v['localizacao']??'')==='CSF SLZ'?'selected':'' ?>>CSF SLZ</option><option value="CSF FOR" <?= ($v['localizacao']??'')==='CSF FOR'?'selected':'' ?>>CSF FOR</option></select></div>
    <div class="form-group full"><label>Observações</label><textarea name="observacoes"><?= e($v['observacoes']??'') ?></textarea></div>
</div>

<?php else: ?>
<div class="stitle"><?= icon('phone') ?> Dispositivo</div>
<div class="form-grid" style="margin-bottom:22px">
    <div class="form-group"><label>Tipo</label><select name="tipo_cel"><option <?= ($v['tipo_cel']??'Smartphone')==='Smartphone'?'selected':'' ?>>Smartphone</option><option <?= ($v['tipo_cel']??'')==='Tablet'?'selected':'' ?>>Tablet</option></select></div>
    <div class="form-group"><label>Status</label><select name="status"><?php foreach(['Em uso','Disponível','Manutenção','Desativado'] as $s): ?><option <?= ($v['status']??'Em uso')===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Marca <span class="req">*</span></label><input type="text" name="marca" value="<?= e($v['marca']??'') ?>"></div>
    <div class="form-group"><label>Modelo <span class="req">*</span></label><input type="text" name="modelo" value="<?= e($v['modelo']??'') ?>"></div>
    <div class="form-group"><label>Número de Série</label><input type="text" name="numero_serie" value="<?= e($v['numero_serie']??'') ?>"></div>
    <div class="form-group"><label>IMEI</label><input type="text" name="imei" value="<?= e($v['imei']??'') ?>"></div>
    <div class="form-group"><label>Número do Chip</label><input type="text" name="numero_chip" value="<?= e($v['numero_chip']??'') ?>"></div>
    <div class="form-group"><label>Operadora</label><select name="operadora"><option value="">— Selecione —</option><?php foreach(['Claro','Vivo','TIM','Oi','Algar','Nextel','Sem chip','Outra'] as $op): ?><option <?= ($v['operadora']??'')===$op?'selected':'' ?>><?= $op ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>MDM</label><select name="mdm_ativo"><option value="0" <?= (($v['mdm_ativo']??'0')=='0')?'selected':'' ?>>Desativado</option><option value="1" <?= (($v['mdm_ativo']??'0')=='1')?'selected':'' ?>>Ativado</option></select></div>
    <div class="form-group"><label>Data de Aquisição</label><input type="date" name="data_aquisicao" value="<?= e($v['data_aquisicao']??'') ?>"></div>
</div>
<div class="stitle"><?= icon('search') ?> Responsável</div>
<div class="form-grid" style="margin-bottom:22px">
    <div class="form-group"><label>Usuário Responsável <span class="req">*</span></label><input type="text" name="usuario_responsavel" value="<?= e($v['usuario_responsavel']??'') ?>"></div>
    <div class="form-group"><label>Setor <span class="req">*</span></label><select name="setor"><option value="">— Selecione —</option><?php foreach($SETORES as $s): ?><option value="<?= e($s) ?>" <?= ($v['setor']??'')===$s?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?></select></div>
    <div class="form-group full"><label>Observações</label><textarea name="observacoes"><?= e($v['observacoes']??'') ?></textarea></div>
</div>
<?php endif; ?>

<div style="display:flex;gap:11px;flex-wrap:wrap">
    <button type="submit" class="btn btn-primary"><?= icon('check') ?> Cadastrar</button>
    <a href="<?= $tipo==='computador'?'computadores.php':'celulares.php' ?>" class="btn btn-ghost"><?= icon('back') ?> Cancelar</a>
</div>
</form>
</div>
<?php include 'includes/footer.php'; ?>
