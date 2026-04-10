<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/distribuicao_importacao.php';

require_login();
requireAccess('distribuicao');

$pageTitle = 'Importar base de distribuição';

$pdo = getDB();
distribuicao_setup_import_tables($pdo);

if (!function_exists('e')) {
    function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
$isAdmin = (($_SESSION['perfil'] ?? '') === 'admin');
$allowedClients = distribuicao_get_allowed_clients($pdo, $userId, $isAdmin);

$errors = [];
$success = null;
$preview = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_write_access('distribuicao', 'distribuicao_index.php');
    validate_csrf_or_fail($_POST['csrf_token'] ?? null);

    $clienteId = (int) ($_POST['cliente_id'] ?? 0);
    if ($clienteId <= 0) {
        $errors[] = 'Selecione um cliente.';
    } elseif (!$isAdmin && !in_array($clienteId, array_column($allowedClients, 'id'), true)) {
        $errors[] = 'Você não tem permissão para importar para este cliente.';
    }

    if (empty($_FILES['arquivo_csv']['tmp_name'])) {
        $errors[] = 'Selecione um arquivo CSV.';
    }

    if (!$errors) {
        try {
            $preview = distribuicao_parse_base_csv($_FILES['arquivo_csv']['tmp_name'], $clienteId);
            if (isset($_POST['confirmar_importacao']) && $_POST['confirmar_importacao'] === '1') {
                $result = distribuicao_importar_base_csv($pdo, $preview['rows'], $userId);
                $success = sprintf(
                    'Importação concluída. Inseridos: %d | Atualizados: %d | Ignorados: %d',
                    $result['inserted'],
                    $result['updated'],
                    $result['ignored']
                );
                $preview = null;
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="page-shell base-import-shell">
    <div class="page-hero">
        <div>
            <h1>Importar base de distribuição</h1>
            <p>Use um CSV exportado da planilha modelo. O monitoramento continua sendo atualizado separadamente pelo CSV do NDD.</p>
        </div>
        <a href="distribuicao_index.php" class="btn btn-secondary">Voltar</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <div class="base-import-card">
        <div class="base-import-head">
            <div>
                <h3>Arquivo base da distribuição</h3>
                <p>Importe a base inicial em <strong>XLSX</strong> ou <strong>CSV UTF-8</strong>. O monitoramento continua separado e é atualizado pelo CSV do NDD.</p>
            </div>
            <a href="modelo_importacao_distribuicao_base_atualizado.xlsx" class="btn btn-secondary">Baixar modelo</a>
        </div>
        <div class="base-import-body">
            <form method="post" enctype="multipart/form-data">
                <?= csrf_input() ?>
                <div class="base-import-grid">
                    <div class="base-field">
                        <label>Cliente da distribuição</label>
                        <select name="cliente_id" required>
                            <option value="">Selecione um cliente</option>
                            <?php foreach ($allowedClients as $client): ?>
                                <option value="<?= (int) $client['id'] ?>" <?= ((int)($_POST['cliente_id'] ?? 0) === (int)$client['id']) ? 'selected' : '' ?>>
                                    <?= e($client['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="base-field">
                        <label>Arquivo da base</label>
                        <div class="base-filebox">
                            <div>
                                <strong>Formatos aceitos</strong>
                                <span id="base-file-name">XLSX ou CSV</span>
                            </div>
                            <label class="base-filebtn" for="arquivo_csv">Escolher arquivo</label>
                            <input type="file" id="arquivo_csv" name="arquivo_csv" accept=".xlsx,.csv,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                        </div>
                    </div>
                </div>

                <div class="base-help">
                    <div class="base-tip"><small>Chave de atualização</small><strong>Cliente + Série</strong></div>
                    <div class="base-tip"><small>Não altera</small><strong>Monitoramento e histórico</strong></div>
                    <div class="base-tip"><small>Linhas sem série</small><strong>Serão ignoradas</strong></div>
                </div>

                <?php if ($preview): ?>
                    <input type="hidden" name="confirmar_importacao" value="1">
                <?php endif; ?>

                <div class="base-actions">
                    <button type="submit" class="btn btn-primary">
                        <?= $preview ? 'Confirmar importação' : 'Importar base' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
    (function(){
      var input=document.getElementById('arquivo_csv');
      var name=document.getElementById('base-file-name');
      if(input&&name){input.addEventListener('change',function(){name.textContent=this.files&&this.files[0]?this.files[0].name:'XLSX ou CSV';});}
    })();
    </script>

    <?php if ($preview): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Prévia</h3>
            </div>
            <div class="card-body">
                <div class="stats-row">
                    <div class="stat-box"><small>Total lido</small><strong><?= (int) $preview['summary']['total'] ?></strong></div>
                    <div class="stat-box"><small>Com série</small><strong><?= (int) $preview['summary']['with_serial'] ?></strong></div>
                    <div class="stat-box"><small>Sem série</small><strong><?= (int) $preview['summary']['without_serial'] ?></strong></div>
                </div>

                <div class="table-wrap mt-3">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Linha</th>
                                <th>Código local</th>
                                <th>Setor / unidade</th>
                                <th>Modelo</th>
                                <th>Série</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($preview['rows'], 0, 20) as $row): ?>
                                <tr>
                                    <td><?= (int) $row['_line'] ?></td>
                                    <td><?= e($row['codigo_local']) ?></td>
                                    <td><?= e($row['setor']) ?></td>
                                    <td><?= e($row['modelo']) ?></td>
                                    <td><?= e($row['serie']) ?></td>
                                    <td><?= e($row['status_operacional']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="mt-2 text-muted">Prévia limitada às primeiras 20 linhas.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
