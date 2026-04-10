<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireAccess('distribuicao');

$db = getDB();
$canWrite = can_write_module('distribuicao');
$canImportDistribuicao = !is_viewer() && can_write_module('distribuicao');
$hasEmpresa = column_exists($db, 'distribuicao_equipamentos', 'empresa');
$hasRegional = column_exists($db, 'distribuicao_equipamentos', 'regional');
$hasLogradouro = column_exists($db, 'distribuicao_equipamentos', 'logradouro');
$hasBairro = column_exists($db, 'distribuicao_equipamentos', 'bairro');
$hasCep = column_exists($db, 'distribuicao_equipamentos', 'cep');
$busca = trim((string) get_query('q', ''));
$clienteId = query_int('cliente_id', 0, 0);
$status = trim((string) get_query('status', ''));
$monitoramento = trim((string) get_query('monitoramento', ''));
$uf = strtoupper(trim((string) get_query('uf', '')));
$municipio = trim((string) get_query('municipio', ''));
$regional = trim((string) get_query('regional', ''));
$empresa = trim((string) get_query('empresa', ''));
$page = query_int('page', 1, 1);
$perPage = 25;

$clientesDisponiveis = distribuicao_accessible_clients('visualizar');
$allowedIds = distribuicao_allowed_client_ids('visualizar');
if ($clienteId > 0) {
    distribuicao_require_cliente_access($clienteId, 'visualizar');
}

$where = ['1=1'];
$params = [];
$userCompanyMap = [];
$hasRestrictedCompanies = false;

if (($_SESSION['perfil'] ?? '') !== 'admin') {
    if (empty($allowedIds)) {
        $where[] = '1=0';
    } else {
        $where[] = 'e.cliente_id IN (' . implode(',', array_map('intval', $allowedIds)) . ')';
        if ($hasEmpresa) {
            $userCompanyMap = distribuicao_allowed_companies_map('visualizar');
            foreach ($userCompanyMap as $clientePermId => $companyList) {
                if (!empty($companyList)) {
                    $hasRestrictedCompanies = true;
                    break;
                }
            }
            if ($hasRestrictedCompanies) {
                $companyClauses = [];
                foreach ($allowedIds as $allowedClientId) {
                    $allowedClientId = (int) $allowedClientId;
                    if (empty($userCompanyMap[$allowedClientId])) {
                        $companyClauses[] = '(e.cliente_id = ' . $allowedClientId . ')';
                        continue;
                    }
                    $companyParams = [];
                    foreach (array_values(array_unique($userCompanyMap[$allowedClientId])) as $companyIndex => $companyName) {
                        $paramName = ':perm_empresa_' . $allowedClientId . '_' . $companyIndex;
                        $params[$paramName] = $companyName;
                        $companyParams[] = $paramName;
                    }
                    if (!empty($companyParams)) {
                        $companyClauses[] = '(e.cliente_id = ' . $allowedClientId . ' AND UPPER(TRIM(e.empresa)) IN (' . implode(',', $companyParams) . '))';
                    }
                }
                if (!empty($companyClauses)) {
                    $where[] = '(' . implode(' OR ', $companyClauses) . ')';
                }
            }
        }
    }
}
if ($clienteId > 0) {
    $where[] = 'e.cliente_id = :cliente_id';
    $params[':cliente_id'] = $clienteId;
}
if ($status !== '') {
    $where[] = 'e.status_operacional = :status';
    $params[':status'] = $status;
}
if ($monitoramento !== '') {
    $where[] = 'e.monitoramento = :monitoramento';
    $params[':monitoramento'] = $monitoramento;
}
if ($uf !== '') {
    $where[] = 'e.uf = :uf';
    $params[':uf'] = $uf;
}
if ($municipio !== '') {
    $where[] = 'e.municipio = :municipio';
    $params[':municipio'] = $municipio;
}
if ($regional !== '' && $hasRegional) {
    $where[] = 'e.regional = :regional';
    $params[':regional'] = $regional;
}
if ($empresa !== '' && $hasEmpresa) {
    $where[] = 'e.empresa = :empresa';
    $params[':empresa'] = $empresa;
}
if ($busca !== '') {
    $like = '%' . $busca . '%';
    $searchParts = [
        'c.nome LIKE :q_cliente',
        'e.serie LIKE :q_serie',
        'e.modelo LIKE :q_modelo',
        'e.setor LIKE :q_setor',
        'e.nome_impressora LIKE :q_impressora',
        'e.municipio LIKE :q_municipio',
        'e.uf LIKE :q_uf',
    ];
    if ($hasEmpresa) $searchParts[] = 'e.empresa LIKE :q_empresa_busca';
    if ($hasRegional) $searchParts[] = 'e.regional LIKE :q_regional';
    if ($hasLogradouro) $searchParts[] = 'e.logradouro LIKE :q_logradouro';
    if ($hasBairro) $searchParts[] = 'e.bairro LIKE :q_bairro';
    if ($hasCep) $searchParts[] = 'e.cep LIKE :q_cep';
    $where[] = '(' . implode(' OR ', $searchParts) . ')';
    $params[':q_cliente'] = $like;
    $params[':q_serie'] = $like;
    $params[':q_modelo'] = $like;
    $params[':q_setor'] = $like;
    $params[':q_impressora'] = $like;
    $params[':q_municipio'] = $like;
    $params[':q_uf'] = $like;
    if ($hasEmpresa) $params[':q_empresa_busca'] = $like;
    if ($hasRegional) $params[':q_regional'] = $like;
    if ($hasLogradouro) $params[':q_logradouro'] = $like;
    if ($hasBairro) $params[':q_bairro'] = $like;
    if ($hasCep) $params[':q_cep'] = $like;
}

$baseFrom = ' FROM distribuicao_equipamentos e INNER JOIN distribuicao_clientes c ON c.id = e.cliente_id WHERE ' . implode(' AND ', $where);

$countStmt = $db->prepare('SELECT COUNT(*)' . $baseFrom);
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$pagination = paginate($totalRows, $page, $perPage);

$sql = 'SELECT e.*, c.nome AS cliente_nome' . $baseFrom . ' ORDER BY c.nome ASC, e.municipio ASC, e.setor ASC, e.id DESC LIMIT :limit OFFSET :offset';
$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$equipamentos = $stmt->fetchAll() ?: [];

$statsStmt = $db->prepare(
    'SELECT COUNT(*) AS total,
            SUM(CASE WHEN UPPER(TRIM(COALESCE(e.status_operacional, ""))) IN ("ATIVA","ATIVO") THEN 1 ELSE 0 END) AS ativas,
            SUM(CASE WHEN UPPER(TRIM(COALESCE(e.status_operacional, ""))) IN ("DESINSTALADA","RECOLHIDA") THEN 1 ELSE 0 END) AS desinstaladas,
            SUM(CASE
                    WHEN UPPER(TRIM(COALESCE(e.monitoramento, ""))) = "OFFLINE"
                     AND UPPER(TRIM(COALESCE(e.status_operacional, ""))) <> "BACKUP"
                    THEN 1 ELSE 0
                END) AS offline'
    . $baseFrom
);
$statsStmt->execute($params);
$stats = $statsStmt->fetch() ?: ['total'=>0,'ativas'=>0,'desinstaladas'=>0,'offline'=>0];

$trocaSql = 'SELECT COUNT(*) FROM distribuicao_movimentacoes m WHERE m.tipo_movimentacao = "troca_tecnica" AND DATE_FORMAT(m.data_movimentacao, "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")';
if (($_SESSION['perfil'] ?? '') !== 'admin' && !empty($allowedIds)) {
    $trocaSql .= ' AND m.cliente_id IN (' . implode(',', array_map('intval', $allowedIds)) . ')';
}
$trocasMes = (int) $db->query($trocaSql)->fetchColumn();

$analyticsWhere = $where;
$analyticsParams = $params;
$analyticsBaseFrom = $baseFrom;

$topCidadesStmt = $db->prepare("SELECT e.municipio, COUNT(*) AS total" . $analyticsBaseFrom . " AND e.municipio IS NOT NULL AND e.municipio <> '' GROUP BY e.municipio ORDER BY total DESC, e.municipio ASC LIMIT 5");
$topCidadesStmt->execute($analyticsParams);
$topCidades = $topCidadesStmt->fetchAll() ?: [];

$modelosStmt = $db->prepare("SELECT e.modelo, COUNT(*) AS total" . $analyticsBaseFrom . " AND e.modelo IS NOT NULL AND e.modelo <> '' GROUP BY e.modelo ORDER BY total DESC, e.modelo ASC LIMIT 10");
$modelosStmt->execute($analyticsParams);
$modelosDistribuicao = $modelosStmt->fetchAll() ?: [];

$onlineOffline = [
    'online' => 0,
    'offline' => 0,
    'outros' => 0,
];
$monitoramentoStmt = $db->prepare("SELECT COALESCE(e.monitoramento, '') AS monitoramento, COUNT(*) AS total" . $analyticsBaseFrom . " GROUP BY COALESCE(e.monitoramento, '')");
$monitoramentoStmt->execute($analyticsParams);
foreach (($monitoramentoStmt->fetchAll() ?: []) as $item) {
    $label = strtoupper(trim((string)($item['monitoramento'] ?? '')));
    $count = (int)($item['total'] ?? 0);
    if ($label === 'ONLINE') {
        $onlineOffline['online'] += $count;
    } elseif ($label === 'OFFLINE') {
        $onlineOffline['offline'] += $count;
    } else {
        $onlineOffline['outros'] += $count;
    }
}

$overviewCounts = [
    'ativas' => 0,
    'backup' => 0,
    'offline' => (int)($stats['offline'] ?? 0),
    'desinstaladas' => 0,
    'outros' => 0,
];
$overviewStmt = $db->prepare('SELECT e.status_operacional, COUNT(*) AS total' . $analyticsBaseFrom . ' GROUP BY e.status_operacional');
$overviewStmt->execute($analyticsParams);
foreach (($overviewStmt->fetchAll() ?: []) as $item) {
    $label = strtoupper(trim((string)($item['status_operacional'] ?? '')));
    $count = (int)($item['total'] ?? 0);

    if (in_array($label, ['ATIVA', 'ATIVO'], true)) {
        $overviewCounts['ativas'] += $count;
    } elseif ($label === 'BACKUP') {
        $overviewCounts['backup'] += $count;
    } elseif (in_array($label, ['DESINSTALADA', 'RECOLHIDA'], true)) {
        $overviewCounts['desinstaladas'] += $count;
    } elseif ($label !== '') {
        $overviewCounts['outros'] += $count;
    }
}
$stats['ativas'] = $overviewCounts['ativas'];
$stats['desinstaladas'] = $overviewCounts['desinstaladas'];

$chartModelLabels = array_map(function ($row) { return (string) $row['modelo']; }, $modelosDistribuicao);
$chartModelData   = array_map(function ($row) { return (int) $row['total']; }, $modelosDistribuicao);
$chartCityLabels  = array_map(function ($row) { return (string) $row['municipio']; }, $topCidades);
$chartCityData    = array_map(function ($row) { return (int) $row['total']; }, $topCidades);

$filterWhere = ['1=1'];
$filterParams = [];
if (($_SESSION['perfil'] ?? '') !== 'admin') {
    if (empty($allowedIds)) {
        $filterWhere[] = '1=0';
    } else {
        $filterWhere[] = 'e.cliente_id IN (' . implode(',', array_map('intval', $allowedIds)) . ')';
        if ($hasEmpresa && $hasRestrictedCompanies) {
            $companyClauses = [];
            foreach ($allowedIds as $allowedClientId) {
                $allowedClientId = (int) $allowedClientId;
                if (empty($userCompanyMap[$allowedClientId])) {
                    $companyClauses[] = '(e.cliente_id = ' . $allowedClientId . ')';
                    continue;
                }
                $companyParams = [];
                foreach (array_values(array_unique($userCompanyMap[$allowedClientId])) as $companyIndex => $companyName) {
                    $paramName = ':f_perm_empresa_' . $allowedClientId . '_' . $companyIndex;
                    $filterParams[$paramName] = $companyName;
                    $companyParams[] = $paramName;
                }
                if (!empty($companyParams)) {
                    $companyClauses[] = '(e.cliente_id = ' . $allowedClientId . ' AND UPPER(TRIM(e.empresa)) IN (' . implode(',', $companyParams) . '))';
                }
            }
            if (!empty($companyClauses)) {
                $filterWhere[] = '(' . implode(' OR ', $companyClauses) . ')';
            }
        }
    }
}
if ($clienteId > 0) {
    $filterWhere[] = 'e.cliente_id = ' . (int) $clienteId;
}
$filterBase = ' FROM distribuicao_equipamentos e WHERE ' . implode(' AND ', $filterWhere);
$fetchDistinctFilterValues = static function (string $sql) use ($db, $filterParams) {
    $stmt = $db->prepare($sql);
    foreach ($filterParams as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
};
$ufs        = $fetchDistinctFilterValues('SELECT DISTINCT e.uf'        . $filterBase . ' AND e.uf IS NOT NULL AND e.uf <> "" ORDER BY e.uf ASC');
$municipios = $fetchDistinctFilterValues('SELECT DISTINCT e.municipio' . $filterBase . ' AND e.municipio IS NOT NULL AND e.municipio <> "" ORDER BY e.municipio ASC');
$regionais  = $hasRegional ? $fetchDistinctFilterValues('SELECT DISTINCT e.regional' . $filterBase . ' AND e.regional IS NOT NULL AND e.regional <> "" ORDER BY e.regional ASC') : [];
$empresas   = $hasEmpresa ? $fetchDistinctFilterValues('SELECT DISTINCT e.empresa' . $filterBase . ' AND e.empresa IS NOT NULL AND e.empresa <> "" ORDER BY e.empresa ASC') : [];


if (get_query('export') === 'excel') {
    $exportSql = 'SELECT e.*, c.nome AS cliente_nome' . $baseFrom . ' ORDER BY c.nome ASC, e.municipio ASC, e.setor ASC, e.id DESC';
    $exportStmt = $db->prepare($exportSql);
    foreach ($params as $key => $value) {
        $exportStmt->bindValue($key, $value);
    }
    $exportStmt->execute();
    $exportData = $exportStmt->fetchAll() ?: [];

    $headers = [
        'Grupo cliente',
        'Empresa',
        'Regional',
        'Setor / Unidade',
        'Município',
        'UF',
        'Logradouro',
        'Bairro',
        'CEP',
        'Modelo',
        'Série',
        'Nome na rede',
        'PP',
        'CC',
        'Monitoramento',
        'Status',
        'Data instalação',
        'Última leitura'
    ];

    $rows = [];
    foreach ($exportData as $item) {
        $rows[] = [
            $item['cliente_nome'] ?? '',
            $item['empresa'] ?? '',
            $item['regional'] ?? '',
            $item['setor'] ?? '',
            $item['municipio'] ?? '',
            $item['uf'] ?? '',
            $item['logradouro'] ?? '',
            $item['bairro'] ?? '',
            $item['cep'] ?? '',
            $item['modelo'] ?? '',
            $item['serie'] ?? '',
            $item['nome_impressora'] ?? '',
            $item['pp'] ?? '',
            $item['cc'] ?? '',
            $item['monitoramento'] ?? '',
            $item['status_operacional'] ?? '',
            $item['data_instalacao'] ?? '',
            $item['ultima_leitura_em'] ?? '',
        ];
    }

    if (!$rows) {
        $rows[] = ['','','','','','','','','','','','','','','','','','Nenhum registro encontrado'];
    }

    export_excel_xml('distribuicao_export.xls', $headers, $rows);
}

include 'includes/header.php';
echo render_flash();

function dist_status_class(string $s): string {
    return match($s) {
        'Ativa'         => 'b-green',
        'Desinstalada',
        'Recolhida'     => 'b-red',
        'Troca técnica' => 'b-amber',
        'Backup'        => 'b-blue',
        default         => 'b-neutral',
    };
}
function dist_mon_class(string $m): string {
    return match($m) {
        'Online'  => 'b-green',
        'Offline' => 'b-red',
        'Pendente'=> 'b-amber',
        default   => 'b-neutral',
    };
}
function dist_row_accent(string $status, string $mon): string {
    if ($mon === 'Offline' && $status !== 'Backup') return 'row-offline';
    if ($status === 'Troca técnica')                return 'row-troca';
    if ($status === 'Desinstalada')                 return 'row-desinstalada';
    return '';
}
?>


<div class="page-head">
    <div class="page-head-copy">
        <h2>Distribuição de impressoras</h2>
        <p>Controle multi-cliente do parque instalado, monitoramento, status operacional e trocas técnicas.</p>
    </div>
    <div class="page-head-actions">
        <?php if (!empty($clientesDisponiveis) && (($_SESSION['perfil'] ?? '') === 'admin' || !empty(distribuicao_allowed_client_ids('cadastrar')))): ?>
            <a href="distribuicao_cadastrar.php" class="btn btn-primary"><?= icon('plus') ?> Novo equipamento</a>
        <?php endif; ?>
        <?php if ($canImportDistribuicao): ?>
        <a href="distribuicao_importar_base.php"          class="btn btn-ghost"><?= icon('upload') ?> Importar base</a>
        <a href="distribuicao_importar_monitoramento.php" class="btn btn-ghost"><?= icon('filter') ?> Importar monitoramento</a>
        <?php endif; ?>
        <a href="distribuicao_movimentacoes.php"          class="btn btn-ghost"><?= icon('clock') ?> Movimentações</a>
        <a href="distribuicao_index.php<?= e(current_query(['export' => 'excel'], ['page'])) ?>" class="btn btn-export">Exportar Excel</a>
        <?php if (($_SESSION['perfil'] ?? '') === 'admin'): ?>
            <a href="distribuicao_clientes.php" class="btn btn-ghost"><?= icon('users') ?> Clientes</a>
        <?php endif; ?>
    </div>
</div>

<!-- Visão geral -->
<div class="overview-card">
    <div class="head" style="margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:12px">
        <div>
            <h3 style="font-size:16px;margin:0 0 4px">Visão geral do parque</h3>
            <span style="font-size:11px;color:var(--muted2);text-transform:uppercase;letter-spacing:.08em;font-weight:700">Ativas, backup, offline e desinstaladas</span>
        </div>
        <span class="badge b-neutral">Total: <?= (int) $stats['total'] ?></span>
    </div>
    <div class="overview-grid">
        <div class="chart-wrap tall"><canvas id="chartOverview"></canvas></div>
        <div class="overview-meta">
            <div class="metric-chip"><div class="label">Ativas</div><div class="value"><?= (int) $overviewCounts['ativas'] ?></div><div class="sub">Em operação</div></div>
            <div class="metric-chip"><div class="label">Backup</div><div class="value"><?= (int) $overviewCounts['backup'] ?></div><div class="sub">Reserva técnica</div></div>
            <div class="metric-chip"><div class="label">Offline</div><div class="value"><?= (int) $overviewCounts['offline'] ?></div><div class="sub">Sem leitura recente</div></div>
            <div class="metric-chip"><div class="label">Desinstaladas</div><div class="value"><?= (int) $overviewCounts['desinstaladas'] ?></div><div class="sub">Fora do parque</div></div>
        </div>
    </div>
</div>

<!-- Analytics -->
<div class="analytics-grid">
    <div class="analytics-card">
        <div class="head">
            <h3>Top 5 cidades</h3>
            <span>Impressoras</span>
        </div>
        <div class="city-rank">
            <?php if (!empty($topCidades)): ?>
                <?php foreach ($topCidades as $idx => $cidade): ?>
                    <?php $cityMax = max(1, (int)($topCidades[0]['total'] ?? 1)); $pct = (int) round(((int)$cidade['total'] / $cityMax) * 100); ?>
                    <div class="city-row">
                        <div class="rank">#<?= (int) $idx + 1 ?></div>
                        <div class="meta">
                            <div class="name"><?= e($cidade['municipio']) ?></div>
                            <div class="bar"><div class="fill" style="width: <?= $pct ?>%"></div></div>
                        </div>
                        <div class="count"><?= (int) $cidade['total'] ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state" style="padding:22px 8px">
                    <p>Sem dados suficientes para montar o ranking.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="analytics-card">
        <div class="head">
            <h3>Distribuição por modelo</h3>
            <span>Top 10</span>
        </div>
        <div class="chart-wrap"><canvas id="chartModelos"></canvas></div>
    </div>

    <div class="analytics-card">
        <div class="head">
            <h3>Online x Offline</h3>
            <span>Monitoramento</span>
        </div>
        <div class="chart-wrap short"><canvas id="chartMonitoramento"></canvas></div>
        <div class="chart-legend-inline">
            <span class="badge b-green">Online: <?= (int) $onlineOffline['online'] ?></span>
            <span class="badge b-red">Offline: <?= (int) $onlineOffline['offline'] ?></span>
            <?php if ((int) $onlineOffline['outros'] > 0): ?>
                <span class="badge b-neutral">Outros: <?= (int) $onlineOffline['outros'] ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Card principal -->
<div class="card" style="padding:0;overflow:hidden">

    <!-- cabeçalho do card -->
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid var(--line)">
        <div class="stitle" style="margin:0">
            <?= icon('printer') ?> Parque instalado
            <span style="font-size:12px;font-weight:500;color:var(--muted2);margin-left:4px">
                <?= number_format($totalRows) ?> registro<?= $totalRows !== 1 ? 's' : '' ?>
            </span>
        </div>
        <?php $hasFilter = $busca !== '' || $status !== '' || $monitoramento !== '' || $uf !== '' || $municipio !== '' || $regional !== '' || $empresa !== '' || $clienteId > 0; ?>
        <?php if ($hasFilter): ?>
            <a href="distribuicao_index.php" class="btn btn-ghost btn-sm"><?= icon('close') ?> Limpar filtros</a>
        <?php endif; ?>
    </div>

    <!-- filtros -->
    <form method="get" class="dist-filters">
        <div class="fg wide">
            <label>Busca geral</label>
            <input type="text" name="q" placeholder="Cliente, série, modelo, setor, endereço, CEP…" value="<?= e($busca) ?>">
        </div>
        <div class="fg">
            <label>Cliente</label>
            <select name="cliente_id">
                <option value="0">Todos</option>
                <?php foreach ($clientesDisponiveis as $cli): ?>
                    <option value="<?= (int) $cli['id'] ?>" <?= $clienteId === (int) $cli['id'] ? 'selected' : '' ?>><?= e($cli['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fg">
            <label>UF</label>
            <select name="uf">
                <option value="">Todas</option>
                <?php foreach ($ufs as $opt): ?><option value="<?= e($opt) ?>" <?= $uf === (string)$opt ? 'selected' : '' ?>><?= e($opt) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="fg">
            <label>Cidade</label>
            <select name="municipio">
                <option value="">Todas</option>
                <?php foreach ($municipios as $opt): ?><option value="<?= e($opt) ?>" <?= $municipio === (string)$opt ? 'selected' : '' ?>><?= e($opt) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="fg">
            <label>Regional</label>
            <select name="regional">
                <option value="">Todas</option>
                <?php foreach ($regionais as $opt): ?><option value="<?= e($opt) ?>" <?= $regional === (string)$opt ? 'selected' : '' ?>><?= e($opt) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="fg">
            <label>Empresa</label>
            <select name="empresa">
                <option value="">Todas</option>
                <?php foreach ($empresas as $opt): ?><option value="<?= e($opt) ?>" <?= $empresa === (string)$opt ? 'selected' : '' ?>><?= e($opt) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="fg">
            <label>Status</label>
            <select name="status">
                <option value="">Todos</option>
                <?php foreach (distribuicao_status_options() as $opt): ?><option value="<?= e($opt) ?>" <?= $status === $opt ? 'selected' : '' ?>><?= e($opt) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="fg">
            <label>Monitoramento</label>
            <select name="monitoramento">
                <option value="">Todos</option>
                <?php foreach (distribuicao_monitoramento_options() as $opt): ?><option value="<?= e($opt) ?>" <?= $monitoramento === $opt ? 'selected' : '' ?>><?= e($opt) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="factions">
            <button type="submit" class="btn btn-ghost btn-sm"><?= icon('filter') ?> Filtrar</button>
        </div>
    </form>

    <!-- tabela -->
    <div class="table-wrap wide-table">
        <table class="dtbl">
            <thead>
                <tr>
                    <th>Empresa / Grupo</th>
                    <th>Regional</th>
                    <th>Setor / Unidade</th>
                    <th>Localização</th>
                    <th>Modelo</th>
                    <th>Série</th>
                    <th>Nome na rede</th>
                    <th>PP / CC</th>
                    <th>Monitoramento</th>
                    <th>Status</th>
                    <th>Instalação</th>
                    <th style="min-width:150px">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($equipamentos as $eq):
                $rowClass = dist_row_accent(
                    (string)($eq['status_operacional'] ?? ''),
                    (string)($eq['monitoramento'] ?? '')
                );
                $leitura = !empty($eq['ultima_leitura_em'])
                    ? date('d/m/Y', strtotime($eq['ultima_leitura_em']))
                    : null;
                $canEdit = distribuicao_can_access_cliente((int) $eq['cliente_id'], 'editar');
                $canMov  = distribuicao_can_access_cliente((int) $eq['cliente_id'], 'movimentar');
            ?>
                <tr class="<?= $rowClass ?>">

                    <!-- Empresa / Grupo -->
                    <td>
                        <div class="dp company-highlight"><?= e($eq['empresa'] ?: '—') ?></div>
                        <div class="ds">Grupo: <?= e($eq['cliente_nome'] ?: '—') ?></div>
                        <?php if (!empty($eq['cnpj'])): ?>
                            <div class="dm"><?= e($eq['cnpj']) ?></div>
                        <?php endif; ?>
                    </td>

                    <!-- Regional -->
                    <td>
                        <div class="ds" style="color:var(--text)"><?= e($eq['regional'] ?: '—') ?></div>
                    </td>

                    <!-- Setor / Unidade -->
                    <td>
                        <div class="dp"><?= e($eq['setor'] ?: '—') ?></div>
                    </td>

                    <!-- Localização -->
                    <td>
                        <div class="dp">
                            <?= e($eq['municipio'] ?: '—') ?>
                            <?php if (!empty($eq['uf'])): ?><span style="color:var(--muted2)"> / <?= e($eq['uf']) ?></span><?php endif; ?>
                        </div>
                        <?php if (!empty($eq['logradouro'])): ?>
                            <div class="ds"><?= e($eq['logradouro']) ?><?= !empty($eq['bairro']) ? ', ' . e($eq['bairro']) : '' ?></div>
                        <?php endif; ?>
                        <?php if (!empty($eq['cep'])): ?>
                            <div class="dm"><?= e($eq['cep']) ?></div>
                        <?php endif; ?>
                    </td>

                    <!-- Modelo -->
                    <td>
                        <div class="dp"><?= e($eq['modelo'] ?: '—') ?></div>
                        <?php if (!empty($eq['fabricante'])): ?>
                            <div class="ds"><?= e($eq['fabricante']) ?></div>
                        <?php endif; ?>
                    </td>

                    <!-- Série -->
                    <td><div class="dm"><?= e($eq['serie'] ?: '—') ?></div></td>

                    <!-- Nome na rede -->
                    <td><div class="dm"><?= e($eq['nome_impressora'] ?: '—') ?></div></td>

                    <!-- PP / CC -->
                    <td>
                        <?php if (!empty($eq['pp'])): ?>
                            <div class="ds"><span class="dlb">PP</span><?= e($eq['pp']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($eq['centro_custo'])): ?>
                            <div class="ds"><span class="dlb">CC</span><?= e($eq['centro_custo']) ?></div>
                        <?php endif; ?>
                        <?php if (empty($eq['pp']) && empty($eq['centro_custo'])): ?>
                            <span style="color:var(--muted2)">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Monitoramento -->
                    <td>
                        <span class="badge <?= dist_mon_class((string)($eq['monitoramento'] ?? '')) ?>">
                            <?= e($eq['monitoramento'] ?: 'N/A') ?>
                        </span>
                        <?php if ($leitura): ?>
                            <div class="mon-last">Últ. <?= $leitura ?></div>
                        <?php endif; ?>
                    </td>

                    <!-- Status -->
                    <td>
                        <span class="badge <?= dist_status_class((string)($eq['status_operacional'] ?? '')) ?>">
                            <?= e($eq['status_operacional'] ?: 'N/A') ?>
                        </span>
                    </td>

                    <!-- Instalação -->
                    <td class="dm" style="white-space:nowrap">
                        <?= $eq['data_instalacao'] ? date('d/m/Y', strtotime($eq['data_instalacao'])) : '—' ?>
                    </td>

                    <!-- Ações -->
                    <td>
                        <div class="d-actions">
                            <?php if ($canEdit): ?>
                                <a href="distribuicao_editar.php?id=<?= (int) $eq['id'] ?>" class="dbt-edit">
                                    <?= icon('edit') ?> Editar
                                </a>
                            <?php endif; ?>
                            <?php if ($canMov): ?>
                                <a href="distribuicao_troca_tecnica.php?id=<?= (int) $eq['id'] ?>" class="dbt-icon swap" title="Troca técnica">
                                    <?= icon('swap') ?>
                                </a>
                            <?php endif; ?>
                            <a href="distribuicao_movimentacoes.php?equipamento_id=<?= (int) $eq['id'] ?>" class="dbt-icon hist" title="Histórico">
                                <?= icon('clock') ?>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($equipamentos)): ?>
                <tr>
                    <td colspan="12" style="padding:32px;text-align:center;color:var(--muted)">
                        Nenhum equipamento encontrado para os filtros atuais.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?= render_pagination($pagination) ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(() => {
    const modelLabels = <?= json_encode(array_values($chartModelLabels), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const modelData   = <?= json_encode(array_values($chartModelData), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const monitorData = <?= json_encode([(int)$onlineOffline['online'], (int)$onlineOffline['offline'], (int)$onlineOffline['outros']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const overviewData = <?= json_encode([(int)$overviewCounts['ativas'], (int)$overviewCounts['backup'], (int)$overviewCounts['offline'], (int)$overviewCounts['desinstaladas'], (int)$overviewCounts['outros']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    if (typeof Chart === 'undefined') return;

    const defaultTickColor = '#9fb0c8';
    const defaultGridColor = 'rgba(148,160,180,.12)';
    const overviewColors = ['#22c55e', '#60a5fa', '#ef4444', '#94a3b8', '#f59e0b'];
    const monitorColors = ['#22c55e', '#ff5a5f', '#64748b'];

    const modelCanvas = document.getElementById('chartModelos');
    if (modelCanvas && modelLabels.length) {
        new Chart(modelCanvas, {
            type: 'bar',
            data: {
                labels: modelLabels,
                datasets: [{
                    label: 'Quantidade',
                    data: modelData,
                    backgroundColor: 'rgba(163,29,30,.78)',
                    borderColor: 'rgba(255,135,135,.85)',
                    borderWidth: 1,
                    borderRadius: 10,
                    maxBarThickness: 42
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: defaultTickColor, maxRotation: 35, minRotation: 0 }, grid: { display: false } },
                    y: { beginAtZero: true, ticks: { color: defaultTickColor, precision: 0 }, grid: { color: defaultGridColor } }
                }
            }
        });
    }

    const monitorCanvas = document.getElementById('chartMonitoramento');
    if (monitorCanvas && monitorData.some(v => v > 0)) {
        new Chart(monitorCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Online', 'Offline', 'Outros'],
                datasets: [{ data: monitorData, backgroundColor: monitorColors, borderColor: '#131a24', borderWidth: 4, hoverOffset: 8 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: defaultTickColor, boxWidth: 12, usePointStyle: true, pointStyle: 'circle' } } },
                cutout: '68%'
            }
        });
    }

    const overviewCanvas = document.getElementById('chartOverview');
    if (overviewCanvas && overviewData.some(v => v > 0)) {
        new Chart(overviewCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Ativas', 'Backup', 'Offline', 'Desinstaladas', 'Outros'],
                datasets: [{ data: overviewData, backgroundColor: overviewColors, borderColor: '#131a24', borderWidth: 4, hoverOffset: 8 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: defaultTickColor, boxWidth: 12, usePointStyle: true, pointStyle: 'circle' } },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.formattedValue}` } }
                },
                cutout: '66%'
            }
        });
    }
})();
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>