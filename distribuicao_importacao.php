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
<div class="page-shell">
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

    <div class="card">
        <div class="card-header">
            <h3>Arquivo base (CSV)</h3>
        </div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <?= csrf_input() ?>
                <div class="grid-2">
                    <div class="field">
                        <label>Cliente</label>
                        <select name="cliente_id" required>
                            <option value="">Selecione</option>
                            <?php foreach ($allowedClients as $client): ?>
                                <option value="<?= (int) $client['id'] ?>" <?= ((int)($_POST['cliente_id'] ?? 0) === (int)$client['id']) ? 'selected' : '' ?>>
                                    <?= e($client['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>CSV exportado do Excel</label>
                        <input type="file" name="arquivo_csv" accept=".csv,text/csv" required>
                    </div>
                </div>

                <?php if ($preview): ?>
                    <input type="hidden" name="confirmar_importacao" value="1">
                <?php endif; ?>

                <div class="actions-row">
                    <button type="submit" class="btn btn-primary">
                        <?= $preview ? 'Confirmar importação' : 'Ler arquivo' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

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
