<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
requireAccess('impressao_financeiro');
impressao_financeiro_ensure_tables();

$pageTitle = 'Financeiro impressão';
$db = getDB();
$canImport = can_write_module('impressao_financeiro');

$clientes = distribuicao_accessible_clients('visualizar');
$clienteId = query_int('cliente_id', 0, 0);
if ($clienteId > 0) {
    distribuicao_require_cliente_access($clienteId, 'visualizar');
}
$modeloCobrancaAtual = $clienteId > 0 ? distribuicao_cliente_modelo_cobranca($clienteId) : 'sem_franquia';
$rotuloBaseValor = $modeloCobrancaAtual === 'com_franquia' ? 'Valor franquia' : 'Valor fixo';
$rotuloVariavelValor = $modeloCobrancaAtual === 'com_franquia' ? 'Valor excedente' : 'Valor por páginas';
$rotuloKpiTotal = $modeloCobrancaAtual === 'com_franquia' ? 'Franquia + excedente' : 'Aluguel + impressão';
$descricaoResumoEmpresas = $modeloCobrancaAtual === 'com_franquia'
    ? 'Comparativo completo por empresa com páginas produzidas, valor da franquia, valor excedente e total pago.'
    : 'Comparativo completo por empresa com páginas produzidas, valor fixo, valor por páginas e total pago.';

$competencia = trim((string) get_query('competencia', ''));
$empresa = trim((string) get_query('empresa', ''));
$serie = trim((string) get_query('serie', ''));
$resumoVisao = trim((string) get_query('resumo_visao', 'grupo'));
if (!in_array($resumoVisao, ['grupo', 'empresa'], true)) {
    $resumoVisao = 'grupo';
}
$tab = trim((string) get_query('tab', 'geral'));
$allowedTabs = ['geral', 'volumetria', 'valor_pago', 'modelo_custo_volume', 'paginas_impressora', 'contador_mensal', 'detalhe', 'importacoes'];
if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'geral';
}

$page = query_int('page', 1, 1);
$perPage = 50;

$where = ['1=1'];
$params = [];

if ($clienteId > 0) {
    $where[] = 'e.cliente_id = :cliente_id';
    $params[':cliente_id'] = $clienteId;
}
if ($competencia !== '') {
    $where[] = 'e.competencia = :competencia';
    $params[':competencia'] = $competencia;
}
if ($empresa !== '') {
    $where[] = 'e.empresa = :empresa';
    $params[':empresa'] = $empresa;
}
if ($serie !== '') {
    $where[] = 'e.serie LIKE :serie';
    $params[':serie'] = '%' . $serie . '%';
}

$allowedCompanies = distribuicao_allowed_companies_map('visualizar');
if (($_SESSION['perfil'] ?? '') !== 'admin' && $clienteId > 0 && !empty($allowedCompanies[$clienteId])) {
    $companyClauses = [];
    foreach ($allowedCompanies[$clienteId] as $idx => $companyName) {
        $key = ':acomp_' . $idx;
        $companyClauses[] = 'UPPER(TRIM(e.empresa)) = ' . $key;
        $params[$key] = mb_strtoupper(trim((string) $companyName), 'UTF-8');
    }
    if ($companyClauses) {
        $where[] = '(' . implode(' OR ', $companyClauses) . ')';
    }
}

$whereSql = implode(' AND ', $where);


$baseOptionWhere = ['1=1'];
$baseOptionParams = [];
if ($clienteId > 0) {
    $baseOptionWhere[] = 'e.cliente_id = :cliente_id';
    $baseOptionParams[':cliente_id'] = $clienteId;
}
if (($_SESSION['perfil'] ?? '') !== 'admin' && $clienteId > 0 && !empty($allowedCompanies[$clienteId])) {
    $companyClausesBase = [];
    foreach ($allowedCompanies[$clienteId] as $idx => $companyName) {
        $key = ':bcomp_' . $idx;
        $companyClausesBase[] = 'UPPER(TRIM(e.empresa)) = ' . $key;
        $baseOptionParams[$key] = mb_strtoupper(trim((string) $companyName), 'UTF-8');
    }
    if ($companyClausesBase) {
        $baseOptionWhere[] = '(' . implode(' OR ', $companyClausesBase) . ')';
    }
}
$baseOptionWhereSql = implode(' AND ', $baseOptionWhere);

function fin_bind_all(PDOStatement $stmt, array $params): void
{
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
}

function fin_competencia_label(string $competencia): string
{
    if (!preg_match('/^(\d{4})-(\d{2})$/', $competencia, $m)) {
        return $competencia;
    }

    $meses = [
        '01' => 'Jan',
        '02' => 'Fev',
        '03' => 'Mar',
        '04' => 'Abr',
        '05' => 'Mai',
        '06' => 'Jun',
        '07' => 'Jul',
        '08' => 'Ago',
        '09' => 'Set',
        '10' => 'Out',
        '11' => 'Nov',
        '12' => 'Dez',
    ];

    return ($meses[$m[2]] ?? $m[2]) . '/' . $m[1];
}

function fin_build_url(array $extra = []): string
{
    $query = array_merge($_GET, $extra);
    foreach ($query as $k => $v) {
        if ($v === '' || $v === null) {
            unset($query[$k]);
        }
    }
    return 'impressao_financeiro.php' . ($query ? '?' . http_build_query($query) : '');
}

function fin_money($value): string
{
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
}

function fin_num($value): string
{
    return number_format((float) $value, 0, ',', '.');
}


function fin_should_use_import_totals(int $clienteIdFiltro, string $empresaFiltro, string $serieFiltro): bool
{
    return $clienteIdFiltro > 0 && $empresaFiltro === '' && $serieFiltro === '';
}


function fin_competencias_colspan(array $competencias, int $base = 1): int
{
    return max($base, count($competencias) + $base);
}

$competenciasStmt = $db->prepare("
    SELECT competencia
    FROM (
        SELECT DISTINCT e.competencia AS competencia
        FROM impressao_financeiro_equipamentos e
        WHERE {$baseOptionWhereSql}

        UNION

        SELECT DISTINCT i.competencia AS competencia
        FROM impressao_financeiro_importacoes i
        WHERE 1=1
          " . ($clienteId > 0 ? " AND i.cliente_id = :cliente_id_import_opt" : "") . "
    ) t
    WHERE COALESCE(t.competencia, '') <> ''
    ORDER BY competencia ASC
");
fin_bind_all($competenciasStmt, $baseOptionParams);
if ($clienteId > 0) {
    $competenciasStmt->bindValue(':cliente_id_import_opt', $clienteId, PDO::PARAM_INT);
}
$competenciasStmt->execute();
$competencias = array_column($competenciasStmt->fetchAll() ?: [], 'competencia');

$competencias = array_values(array_filter($competencias, function ($v) { return (string) $v !== ''; }));
$competenciasOrdenadas = $competencias;
sort($competenciasOrdenadas);
$ultimaCompetencia = $competenciasOrdenadas ? $competenciasOrdenadas[count($competenciasOrdenadas) - 1] : null;
$competenciaAnterior = count($competenciasOrdenadas) > 1 ? $competenciasOrdenadas[count($competenciasOrdenadas) - 2] : null;

function fin_delta_meta($atual, $anterior): array
{
    $atual = (float) $atual;
    $anterior = (float) $anterior;
    $delta = $atual - $anterior;
    $pct = $anterior != 0.0 ? (($delta / $anterior) * 100) : ($atual != 0.0 ? 100.0 : 0.0);
    $status = $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'neutral');
    return ['delta' => $delta, 'pct' => $pct, 'status' => $status];
}



$empresasStmt = $db->prepare("
    SELECT DISTINCT e.empresa
    FROM impressao_financeiro_equipamentos e
    WHERE {$baseOptionWhereSql}
      AND COALESCE(e.empresa, '') <> ''
    ORDER BY e.empresa ASC
");
fin_bind_all($empresasStmt, $baseOptionParams);
$empresasStmt->execute();
$empresas = array_column($empresasStmt->fetchAll() ?: [], 'empresa');

$totalsStmt = $db->prepare("
    SELECT
        COUNT(DISTINCT CASE
            WHEN COALESCE(TRIM(e.serie), '') <> '' THEN CONCAT(COALESCE(TRIM(e.empresa), ''), '||', TRIM(e.serie))
            ELSE CONCAT('ROW||', e.id)
        END) AS impressoras,
        COUNT(DISTINCT e.empresa) AS empresas,
        COUNT(DISTINCT e.competencia) AS competencias,
        COALESCE(SUM(e.paginas_produzidas), 0) AS paginas,
        COALESCE(SUM(e.valor_fixo), 0) AS aluguel,
        COALESCE(SUM(e.valor_variavel), 0) AS impressao,
        COALESCE(SUM(e.valor_total), 0) AS total
    FROM impressao_financeiro_equipamentos e
    LEFT JOIN distribuicao_clientes c ON c.id = e.cliente_id
    WHERE {$whereSql}
");
fin_bind_all($totalsStmt, $params);
$totalsStmt->execute();
$totais = $totalsStmt->fetch() ?: [
    'impressoras' => 0,
    'empresas' => 0,
    'competencias' => 0,
    'paginas' => 0,
    'aluguel' => 0,
    'impressao' => 0,
    'total' => 0,
];

$latestCompetenciaStmt = $db->prepare("
    SELECT MAX(e.competencia)
    FROM impressao_financeiro_equipamentos e
    WHERE {$whereSql}
");
fin_bind_all($latestCompetenciaStmt, $params);
$latestCompetenciaStmt->execute();
$latestCompetencia = (string) ($latestCompetenciaStmt->fetchColumn() ?: '');

if ($latestCompetencia !== '') {
    $latestImpStmt = $db->prepare("
        SELECT COUNT(DISTINCT CASE
            WHEN COALESCE(TRIM(e.serie), '') <> '' THEN CONCAT(COALESCE(TRIM(e.empresa), ''), '||', TRIM(e.serie))
            ELSE CONCAT('ROW||', e.id)
        END)
        FROM impressao_financeiro_equipamentos e
        WHERE {$whereSql}
          AND e.competencia = :latest_comp
    ");
    fin_bind_all($latestImpStmt, $params);
    $latestImpStmt->bindValue(':latest_comp', $latestCompetencia);
    $latestImpStmt->execute();
    $totais['impressoras'] = (int) ($latestImpStmt->fetchColumn() ?: 0);
} else {
    $totais['impressoras'] = 0;
}

$empresasCountStmt = $db->prepare("
    SELECT COUNT(DISTINCT e.empresa)
    FROM impressao_financeiro_equipamentos e
    WHERE {$whereSql}
");
fin_bind_all($empresasCountStmt, $params);
$empresasCountStmt->execute();
$totais['empresas'] = (int) ($empresasCountStmt->fetchColumn() ?: 0);


if (fin_should_use_import_totals($clienteId, $empresa, $serie)) {
    $importWhere = ['1=1'];
    $importParams = [];
    if ($clienteId > 0) {
        $importWhere[] = 'i.cliente_id = :cliente_id';
        $importParams[':cliente_id'] = $clienteId;
    }
    if ($competencia !== '') {
        $importWhere[] = 'i.competencia = :competencia';
        $importParams[':competencia'] = $competencia;
    }
    $importWhereSql = implode(' AND ', $importWhere);

    $importTotalsStmt = $db->prepare("
        SELECT
            COUNT(DISTINCT i.competencia) AS competencias,
            COALESCE(SUM(i.total_paginas), 0) AS paginas,
            COALESCE(SUM(i.total_fixo), 0) AS aluguel,
            COALESCE(SUM(i.total_variavel), 0) AS impressao,
            COALESCE(SUM(i.total_geral), 0) AS total
        FROM impressao_financeiro_importacoes i
        WHERE {$importWhereSql}
    ");
    fin_bind_all($importTotalsStmt, $importParams);
    $importTotalsStmt->execute();
    $importTotais = $importTotalsStmt->fetch() ?: [];
    if ($importTotais) {
        $totais['competencias'] = (int) ($importTotais['competencias'] ?? $totais['competencias']);
        $totais['paginas'] = (float) ($importTotais['paginas'] ?? $totais['paginas']);
        $totais['aluguel'] = (float) ($importTotais['aluguel'] ?? $totais['aluguel']);
        $totais['impressao'] = (float) ($importTotais['impressao'] ?? $totais['impressao']);
        $totais['total'] = (float) ($importTotais['total'] ?? $totais['total']);
    }
}


$historicoStmt = $db->prepare("
    SELECT
        e.competencia,
        COUNT(*) AS impressoras,
        COUNT(DISTINCT e.empresa) AS empresas,
        COALESCE(SUM(e.paginas_produzidas), 0) AS paginas,
        COALESCE(SUM(e.valor_fixo), 0) AS aluguel,
        COALESCE(SUM(e.valor_variavel), 0) AS impressao,
        COALESCE(SUM(e.valor_total), 0) AS total
    FROM impressao_financeiro_equipamentos e
    WHERE {$whereSql}
    GROUP BY e.competencia
    ORDER BY e.competencia ASC
");
fin_bind_all($historicoStmt, $params);
$historicoStmt->execute();
$historicoCompetencias = $historicoStmt->fetchAll() ?: [];


if (fin_should_use_import_totals($clienteId, $empresa, $serie) && $historicoCompetencias) {
    $importHistoricoStmt = $db->prepare("
        SELECT
            i.competencia,
            COALESCE(SUM(i.total_paginas), 0) AS paginas,
            COALESCE(SUM(i.total_fixo), 0) AS aluguel,
            COALESCE(SUM(i.total_variavel), 0) AS impressao,
            COALESCE(SUM(i.total_geral), 0) AS total
        FROM impressao_financeiro_importacoes i
        WHERE {$importWhereSql}
        GROUP BY i.competencia
        ORDER BY i.competencia ASC
    ");
    fin_bind_all($importHistoricoStmt, $importParams);
    $importHistoricoStmt->execute();
    $importHistorico = [];
    foreach ($importHistoricoStmt->fetchAll() ?: [] as $row) {
        $importHistorico[(string) $row['competencia']] = $row;
    }

    foreach ($historicoCompetencias as &$histRow) {
        $compKey = (string) ($histRow['competencia'] ?? '');
        if ($compKey !== '' && isset($importHistorico[$compKey])) {
            $histRow['paginas'] = (float) ($importHistorico[$compKey]['paginas'] ?? $histRow['paginas']);
            $histRow['aluguel'] = (float) ($importHistorico[$compKey]['aluguel'] ?? $histRow['aluguel']);
            $histRow['impressao'] = (float) ($importHistorico[$compKey]['impressao'] ?? $histRow['impressao']);
            $histRow['total'] = (float) ($importHistorico[$compKey]['total'] ?? $histRow['total']);
        }
    }
    unset($histRow);
}

$resumoStmt = $db->prepare("
    SELECT
        MAX(
            CASE
                WHEN COALESCE(TRIM(e.grupo_nome), '') = '' THEN COALESCE(c.nome, e.empresa, 'Sem grupo')
                WHEN e.cliente_id > 0 AND COALESCE(c.nome, '') <> '' AND COALESCE(TRIM(e.grupo_nome), '') = 'Grupo Equatorial' AND c.nome <> 'Grupo Equatorial' THEN c.nome
                ELSE e.grupo_nome
            END
        ) AS grupo_nome,
        e.empresa,
        (
            SELECT COUNT(DISTINCT CASE
                WHEN COALESCE(TRIM(e2.serie), '') <> '' THEN TRIM(e2.serie)
                ELSE CONCAT('ROW||', e2.id)
            END)
            FROM impressao_financeiro_equipamentos e2
            WHERE e2.empresa = e.empresa
              AND e2.cliente_id = e.cliente_id
              AND e2.competencia = (
                  SELECT MAX(e3.competencia)
                  FROM impressao_financeiro_equipamentos e3
                  WHERE e3.empresa = e.empresa
                    AND e3.cliente_id = e.cliente_id
                    AND 1=1" . ($clienteId > 0 ? " AND e3.cliente_id = :cliente_id_latest" : "") . ($empresa !== '' ? " AND e3.empresa = :empresa_latest" : "") . ($serie !== '' ? " AND e3.serie LIKE :serie_latest" : "") . "
              )
        ) AS impressoras,
        COALESCE(SUM(e.paginas_produzidas), 0) AS paginas,
        COALESCE(SUM(e.valor_fixo), 0) AS valor_fixo,
        COALESCE(SUM(e.valor_variavel), 0) AS valor_paginas,
        COALESCE(SUM(e.valor_total), 0) AS total
    FROM impressao_financeiro_equipamentos e
    LEFT JOIN distribuicao_clientes c ON c.id = e.cliente_id
    WHERE {$whereSql}
    GROUP BY e.empresa, e.cliente_id
    ORDER BY total DESC, e.empresa ASC
");
fin_bind_all($resumoStmt, $params);
if ($clienteId > 0) {
    $resumoStmt->bindValue(':cliente_id_latest', $clienteId, PDO::PARAM_INT);
}
if ($empresa !== '') {
    $resumoStmt->bindValue(':empresa_latest', $empresa);
}
if ($serie !== '') {
    $resumoStmt->bindValue(':serie_latest', '%' . $serie . '%');
}
$resumoStmt->execute();
$resumoEmpresas = $resumoStmt->fetchAll() ?: [];


if ($resumoVisao === 'grupo') {
    $resumoAgrupado = [];
    foreach ($resumoEmpresas as $item) {
        $grupoKey = trim((string) ($item['grupo_nome'] ?? ''));
        if ($grupoKey === '') {
            $grupoKey = 'Sem grupo';
        }
        if (!isset($resumoAgrupado[$grupoKey])) {
            $resumoAgrupado[$grupoKey] = [
                'grupo_nome' => $grupoKey,
                'empresa' => $grupoKey,
                'impressoras' => 0,
                'paginas' => 0.0,
                'valor_fixo' => 0.0,
                'valor_paginas' => 0.0,
                'total' => 0.0,
            ];
        }
        $resumoAgrupado[$grupoKey]['impressoras'] += (int) ($item['impressoras'] ?? 0);
        $resumoAgrupado[$grupoKey]['paginas'] += (float) ($item['paginas'] ?? 0);
        $resumoAgrupado[$grupoKey]['valor_fixo'] += (float) ($item['valor_fixo'] ?? 0);
        $resumoAgrupado[$grupoKey]['valor_paginas'] += (float) ($item['valor_paginas'] ?? 0);
        $resumoAgrupado[$grupoKey]['total'] += (float) ($item['total'] ?? 0);
    }
    $resumoEmpresas = array_values($resumoAgrupado);
    usort($resumoEmpresas, static function (array $a, array $b): int {
        $ta = (float) ($a['total'] ?? 0);
        $tb = (float) ($b['total'] ?? 0);
        if ($ta === $tb) {
            return strcmp((string) ($a['grupo_nome'] ?? ''), (string) ($b['grupo_nome'] ?? ''));
        }
        return $tb <=> $ta;
    });
}

$topTotalRows = array_slice($resumoEmpresas, 0, 5);

$topPaginasStmt = $db->prepare("
    SELECT e.empresa, COALESCE(SUM(e.paginas_produzidas), 0) AS paginas
    FROM impressao_financeiro_equipamentos e
    WHERE {$whereSql}
    GROUP BY e.empresa
    ORDER BY paginas DESC, e.empresa ASC
    LIMIT 5
");
fin_bind_all($topPaginasStmt, $params);
$topPaginasStmt->execute();
$topPaginasRows = $topPaginasStmt->fetchAll() ?: [];

$pivotVolStmt = $db->prepare("
    SELECT e.empresa, e.competencia, COALESCE(SUM(e.paginas_produzidas), 0) AS valor
    FROM impressao_financeiro_equipamentos e
    WHERE {$whereSql}
    GROUP BY e.empresa, e.competencia
    ORDER BY e.empresa ASC, e.competencia ASC
");
fin_bind_all($pivotVolStmt, $params);
$pivotVolStmt->execute();
$pivotVolRows = $pivotVolStmt->fetchAll() ?: [];

$volumetria = [];
foreach ($pivotVolRows as $row) {
    $empresaNome = (string) $row['empresa'];
    if (!isset($volumetria[$empresaNome])) {
        $volumetria[$empresaNome] = array_fill_keys($competencias, 0);
    }
    $volumetria[$empresaNome][$row['competencia']] = (float) $row['valor'];
}

$pivotValStmt = $db->prepare("
    SELECT e.empresa, e.competencia, COALESCE(SUM(e.valor_total), 0) AS valor
    FROM impressao_financeiro_equipamentos e
    WHERE {$whereSql}
    GROUP BY e.empresa, e.competencia
    ORDER BY e.empresa ASC, e.competencia ASC
");
fin_bind_all($pivotValStmt, $params);
$pivotValStmt->execute();
$pivotValRows = $pivotValStmt->fetchAll() ?: [];

$valorPago = [];
foreach ($pivotValRows as $row) {
    $empresaNome = (string) $row['empresa'];
    if (!isset($valorPago[$empresaNome])) {
        $valorPago[$empresaNome] = array_fill_keys($competencias, 0);
    }
    $valorPago[$empresaNome][$row['competencia']] = (float) $row['valor'];
}

$pivotModeloVolStmt = $db->prepare("
    SELECT
        COALESCE(NULLIF(TRIM(e.modelo), ''), 'Modelo não informado') AS modelo,
        e.competencia,
        COALESCE(SUM(e.paginas_produzidas), 0) AS paginas,
        COUNT(DISTINCT CASE
            WHEN COALESCE(TRIM(e.serie), '') <> '' THEN TRIM(e.serie)
            ELSE CONCAT('ROW||', e.id)
        END) AS equipamentos
    FROM impressao_financeiro_equipamentos e
    WHERE {$whereSql}
    GROUP BY COALESCE(NULLIF(TRIM(e.modelo), ''), 'Modelo não informado'), e.competencia
    ORDER BY modelo ASC, e.competencia ASC
");
fin_bind_all($pivotModeloVolStmt, $params);
$pivotModeloVolStmt->execute();
$pivotModeloVolRows = $pivotModeloVolStmt->fetchAll() ?: [];

$modeloVolume = [];
foreach ($pivotModeloVolRows as $row) {
    $modeloNome = (string) $row['modelo'];
    if (!isset($modeloVolume[$modeloNome])) {
        $modeloVolume[$modeloNome] = [
            'modelo' => $modeloNome,
            'equipamentos' => 0,
            'meses' => array_fill_keys($competencias, 0),
        ];
    }
    $modeloVolume[$modeloNome]['meses'][$row['competencia']] = (float) $row['paginas'];
    $modeloVolume[$modeloNome]['equipamentos'] = max((int) $modeloVolume[$modeloNome]['equipamentos'], (int) $row['equipamentos']);
}

$pivotModeloCustoStmt = $db->prepare("
    SELECT
        COALESCE(NULLIF(TRIM(e.modelo), ''), 'Modelo não informado') AS modelo,
        e.competencia,
        COALESCE(SUM(e.valor_total), 0) AS valor,
        COUNT(DISTINCT CASE
            WHEN COALESCE(TRIM(e.serie), '') <> '' THEN TRIM(e.serie)
            ELSE CONCAT('ROW||', e.id)
        END) AS equipamentos
    FROM impressao_financeiro_equipamentos e
    WHERE {$whereSql}
    GROUP BY COALESCE(NULLIF(TRIM(e.modelo), ''), 'Modelo não informado'), e.competencia
    ORDER BY modelo ASC, e.competencia ASC
");
fin_bind_all($pivotModeloCustoStmt, $params);
$pivotModeloCustoStmt->execute();
$pivotModeloCustoRows = $pivotModeloCustoStmt->fetchAll() ?: [];

$modeloCusto = [];
foreach ($pivotModeloCustoRows as $row) {
    $modeloNome = (string) $row['modelo'];
    if (!isset($modeloCusto[$modeloNome])) {
        $modeloCusto[$modeloNome] = [
            'modelo' => $modeloNome,
            'equipamentos' => 0,
            'meses' => array_fill_keys($competencias, 0),
        ];
    }
    $modeloCusto[$modeloNome]['meses'][$row['competencia']] = (float) $row['valor'];
    $modeloCusto[$modeloNome]['equipamentos'] = max((int) $modeloCusto[$modeloNome]['equipamentos'], (int) $row['equipamentos']);
}

$pivotPrinterPagesStmt = $db->prepare("
    SELECT e.empresa, e.serie, MAX(e.modelo) AS modelo, e.competencia, COALESCE(SUM(e.paginas_produzidas), 0) AS valor
    FROM impressao_financeiro_equipamentos e
    WHERE {$whereSql}
    GROUP BY e.empresa, e.serie, e.competencia
    ORDER BY e.empresa ASC, e.serie ASC, e.competencia ASC
");
fin_bind_all($pivotPrinterPagesStmt, $params);
$pivotPrinterPagesStmt->execute();
$pivotPrinterPagesRows = $pivotPrinterPagesStmt->fetchAll() ?: [];

$paginasImpressora = [];
foreach ($pivotPrinterPagesRows as $row) {
    $key = trim((string) $row['empresa']) . '||' . trim((string) $row['serie']);
    if (!isset($paginasImpressora[$key])) {
        $paginasImpressora[$key] = [
            'empresa' => (string) $row['empresa'],
            'serie' => (string) $row['serie'],
            'modelo' => (string) $row['modelo'],
            'meses' => array_fill_keys($competencias, 0),
        ];
    }
    $paginasImpressora[$key]['meses'][$row['competencia']] = (float) $row['valor'];
}

$pivotCounterStmt = $db->prepare("
    SELECT e.empresa, e.serie, MAX(e.modelo) AS modelo, e.competencia, MAX(COALESCE(e.medidor_final, 0)) AS valor
    FROM impressao_financeiro_equipamentos e
    WHERE {$whereSql}
    GROUP BY e.empresa, e.serie, e.competencia
    ORDER BY e.empresa ASC, e.serie ASC, e.competencia ASC
");
fin_bind_all($pivotCounterStmt, $params);
$pivotCounterStmt->execute();
$pivotCounterRows = $pivotCounterStmt->fetchAll() ?: [];

$contadorMensal = [];
foreach ($pivotCounterRows as $row) {
    $key = trim((string) $row['empresa']) . '||' . trim((string) $row['serie']);
    if (!isset($contadorMensal[$key])) {
        $contadorMensal[$key] = [
            'empresa' => (string) $row['empresa'],
            'serie' => (string) $row['serie'],
            'modelo' => (string) $row['modelo'],
            'meses' => array_fill_keys($competencias, 0),
        ];
    }
    $contadorMensal[$key]['meses'][$row['competencia']] = (float) $row['valor'];
}

$countStmt = $db->prepare("SELECT COUNT(*) FROM impressao_financeiro_equipamentos e WHERE {$whereSql}");
fin_bind_all($countStmt, $params);
$countStmt->execute();
$totalRows = (int) $countStmt->fetchColumn();
$pagination = paginate($totalRows, $page, $perPage);

$detailStmt = $db->prepare("
    SELECT e.*
    FROM impressao_financeiro_equipamentos e
    WHERE {$whereSql}
    ORDER BY e.competencia DESC, e.empresa ASC, e.local_inst ASC, e.serie ASC
    LIMIT :limit OFFSET :offset
");
fin_bind_all($detailStmt, $params);
$detailStmt->bindValue(':limit', $pagination['per_page'], PDO::PARAM_INT);
$detailStmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$detailStmt->execute();
$equipamentos = $detailStmt->fetchAll() ?: [];

$importacoesStmt = $db->prepare("
    SELECT i.*
    FROM impressao_financeiro_importacoes i
    WHERE 1=1
    " . ($clienteId > 0 ? ' AND i.cliente_id = :cliente_id' : '') . "
    ORDER BY i.competencia DESC, i.created_at DESC
    LIMIT 30
");
if ($clienteId > 0) {
    $importacoesStmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
}
$importacoesStmt->execute();
$ultimasImportacoes = $importacoesStmt->fetchAll() ?: [];


if (get_query('export') === 'excel') {
    $headers = [];
    $rows = [];
    $filename = 'financeiro_impressao_' . $tab . '.xls';

    if ($tab === 'geral') {
        $headers = ['Empresa', 'Impressoras (última competência)', 'Páginas', 'Total'];
        foreach ($resumoEmpresas as $item) {
            $rows[] = [
                $item['empresa'] ?? '',
                $item['impressoras'] ?? '',
                $item['paginas'] ?? '',
                $item['total'] ?? '',
            ];
        }
    } elseif ($tab === 'volumetria') {
        $headers = array_merge(['Empresa'], array_map('fin_competencia_label', $competencias));
        foreach ($volumetria as $empresaNome => $meses) {
            $linha = [$empresaNome];
            foreach ($competencias as $comp) {
                $linha[] = $meses[$comp] ?? 0;
            }
            $rows[] = $linha;
        }
    } elseif ($tab === 'valor_pago') {
        $headers = array_merge(['Empresa'], array_map('fin_competencia_label', $competencias));
        foreach ($valorPago as $empresaNome => $meses) {
            $linha = [$empresaNome];
            foreach ($competencias as $comp) {
                $linha[] = $meses[$comp] ?? 0;
            }
            $rows[] = $linha;
        }
    } elseif ($tab === 'modelo_custo_volume') {
        $headers = array_merge(['Modelo', 'Equipamentos'], array_map('fin_competencia_label', $competencias));
        foreach ($modeloVolume as $modeloNome => $info) {
            $linha = [$modeloNome, $info['equipamentos'] ?? 0];
            foreach ($competencias as $comp) {
                $linha[] = $info['meses'][$comp] ?? 0;
            }
            $rows[] = $linha;
        }
    } elseif ($tab === 'paginas_impressora') {
        $headers = array_merge(['Empresa', 'Série', 'Modelo'], array_map('fin_competencia_label', $competencias));
        foreach ($paginasImpressora as $info) {
            $linha = [$info['empresa'] ?? '', $info['serie'] ?? '', $info['modelo'] ?? ''];
            foreach ($competencias as $comp) {
                $linha[] = $info['meses'][$comp] ?? 0;
            }
            $rows[] = $linha;
        }
    } elseif ($tab === 'contador_mensal') {
        $headers = array_merge(['Empresa', 'Série', 'Modelo'], array_map('fin_competencia_label', $competencias));
        foreach ($contadorMensal as $info) {
            $linha = [$info['empresa'] ?? '', $info['serie'] ?? '', $info['modelo'] ?? ''];
            foreach ($competencias as $comp) {
                $linha[] = $info['meses'][$comp] ?? 0;
            }
            $rows[] = $linha;
        }
    } elseif ($tab === 'importacoes') {
        $headers = ['ID', 'Cliente', 'Competência', 'Arquivo', 'Importado em'];
        foreach ($ultimasImportacoes as $item) {
            $rows[] = [
                $item['id'] ?? '',
                $item['cliente_id'] ?? '',
                $item['competencia'] ?? '',
                $item['arquivo_nome'] ?? '',
                $item['created_at'] ?? '',
            ];
        }
    } else {
        $headers = ['ID', 'Competência', 'Empresa', 'Local', 'Modelo', 'Série', 'Páginas', $rotuloBaseValor, $rotuloVariavelValor, 'Valor total', 'Medidor final'];
        $exportDetailStmt = $db->prepare("
            SELECT e.*
            FROM impressao_financeiro_equipamentos e
            WHERE {$whereSql}
            ORDER BY e.competencia DESC, e.empresa ASC, e.local_inst ASC, e.serie ASC
        ");
        fin_bind_all($exportDetailStmt, $params);
        $exportDetailStmt->execute();
        foreach (($exportDetailStmt->fetchAll() ?: []) as $item) {
            $rows[] = [
                $item['id'] ?? '',
                $item['competencia'] ?? '',
                $item['empresa'] ?? '',
                $item['local_inst'] ?? '',
                $item['modelo'] ?? '',
                $item['serie'] ?? '',
                $item['paginas_produzidas'] ?? '',
                $item['valor_fixo'] ?? '',
                $item['valor_variavel'] ?? '',
                $item['valor_total'] ?? '',
                $item['medidor_final'] ?? '',
            ];
        }
    }

    if (!$rows) {
        $rows[] = array_fill(0, max(1, count($headers)), 'Nenhum dado encontrado');
    }
    export_excel_xml($filename, $headers, $rows);
}

include __DIR__ . '/includes/header.php';
echo render_flash();
?>


<div class="page-shell fin-page">
    <div class="page-head">
        <div class="fin-hero">
            <div class="fin-hero-copy">
                <h2>Financeiro de impressão</h2>
                <p>Refeito com foco em largura total, abas mais visíveis e visões separadas para geral, volumetria, valor pago, páginas por impressora e contador final por mês.</p>
            </div>
            <div class="page-head-actions">
                <?php if ($canImport): ?>
                    <a class="btn btn-primary" href="impressao_financeiro_importar.php"><?= icon('plus') ?> Importar XLSX</a>
                <?php endif; ?>
                <a class="btn btn-export" href="<?= e(fin_build_url(['export' => 'excel', 'page' => null])) ?>">Exportar Excel</a>
            </div>
        </div>
    </div>

    <div class="card pad-lg fin-filters-card">
        <form method="get" class="fin-filters">
            <input type="hidden" name="tab" value="<?= e($tab) ?>">
            <div class="form-group">
                <label>Cliente</label>
                <select name="cliente_id">
                    <option value="0" <?= $clienteId === 0 ? 'selected' : '' ?>>Todos</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= (int) $cliente['id'] ?>" <?= (int) $cliente['id'] === $clienteId ? 'selected' : '' ?>><?= e($cliente['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Mês</label>
                <select name="competencia">
                    <option value="">Todas</option>
                    <?php foreach ($competencias as $item): ?>
                        <option value="<?= e($item) ?>" <?= $competencia === $item ? 'selected' : '' ?>><?= e(fin_competencia_label($item)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Empresa</label>
                <select name="empresa">
                    <option value="">Todas</option>
                    <?php foreach ($empresas as $item): ?>
                        <option value="<?= e($item) ?>" <?= $empresa === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Série</label>
                <input type="text" name="serie" value="<?= e($serie) ?>" placeholder="Buscar por série">
            </div>
            <div class="form-group">
                <label>Mostrar resumo</label>
                <select name="resumo_visao">
                    <option value="grupo" <?= $resumoVisao === 'grupo' ? 'selected' : '' ?>>Por grupo</option>
                    <option value="empresa" <?= $resumoVisao === 'empresa' ? 'selected' : '' ?>>Por empresa</option>
                </select>
            </div>
            <div class="fin-filter-actions">
                <button class="btn btn-primary" type="submit"><?= icon('filter') ?> Filtrar</button>
                <a class="btn btn-ghost" href="impressao_financeiro.php<?= $clienteId ? '?cliente_id=' . $clienteId . '&tab=' . urlencode($tab) . '&resumo_visao=' . urlencode($resumoVisao) : '?tab=' . urlencode($tab) . '&resumo_visao=' . urlencode($resumoVisao) ?>">Limpar</a>
            </div>
        </form>

        <div class="fin-note">
            O mês é gravado no formato <strong>AAAA-MM</strong>. Exemplo: janeiro/2026 = <strong>2026-01</strong>. As tabelas de volumetria, valor pago, páginas por impressora e contador final são montadas dinamicamente com base nos meses já importados. O mês pode ser trocado diretamente no filtro, sem precisar limpar a tela.
        </div>
    </div>

    <div class="fin-tabs">
        <a class="fin-tab <?= $tab === 'geral' ? 'active' : '' ?>" href="<?= e(fin_build_url(['tab' => 'geral', 'page' => null])) ?>">Geral</a>
        <a class="fin-tab <?= $tab === 'volumetria' ? 'active' : '' ?>" href="<?= e(fin_build_url(['tab' => 'volumetria', 'page' => null])) ?>">Volumetria</a>
        <a class="fin-tab <?= $tab === 'valor_pago' ? 'active' : '' ?>" href="<?= e(fin_build_url(['tab' => 'valor_pago', 'page' => null])) ?>">Valor pago</a>
        <a class="fin-tab <?= $tab === 'modelo_custo_volume' ? 'active' : '' ?>" href="<?= e(fin_build_url(['tab' => 'modelo_custo_volume', 'page' => null])) ?>">Modelo: volume e custo</a>
        <a class="fin-tab <?= $tab === 'paginas_impressora' ? 'active' : '' ?>" href="<?= e(fin_build_url(['tab' => 'paginas_impressora', 'page' => null])) ?>">Páginas por impressora</a>
        <a class="fin-tab <?= $tab === 'contador_mensal' ? 'active' : '' ?>" href="<?= e(fin_build_url(['tab' => 'contador_mensal', 'page' => null])) ?>">Contador final por mês</a>
        <a class="fin-tab <?= $tab === 'detalhe' ? 'active' : '' ?>" href="<?= e(fin_build_url(['tab' => 'detalhe', 'page' => 1])) ?>">Detalhe</a>
        <a class="fin-tab <?= $tab === 'importacoes' ? 'active' : '' ?>" href="<?= e(fin_build_url(['tab' => 'importacoes', 'page' => null])) ?>">Importações</a>
    </div>

    <div class="fin-kpis">
        <div class="fin-kpi fin-kpi-competencias"><div class="lbl">Competências</div><div class="val"><?= (int) $totais['competencias'] ?></div><div class="sub">Meses importados</div></div>
        <div class="fin-kpi fin-kpi-empresas"><div class="lbl">Empresas</div><div class="val"><?= (int) $totais['empresas'] ?></div><div class="sub">Empresas no filtro</div></div>
        <div class="fin-kpi fin-kpi-impressoras"><div class="lbl">Imp. último mês</div><div class="val"><?= fin_num($totais['impressoras']) ?></div><div class="sub">Quantidade da última fatura</div></div>
        <div class="fin-kpi fin-kpi-paginas"><div class="lbl">Páginas</div><div class="val"><?= fin_num($totais['paginas']) ?></div><div class="sub">Produção total</div></div>
        <div class="fin-kpi fin-kpi-total"><div class="lbl">Total faturado</div><div class="val"><?= fin_money($totais['total']) ?></div><div class="sub"><?= e($rotuloKpiTotal) ?></div></div>
    </div>

    <div class="fin-note" style="margin-top:0">
    No módulo <strong>Financeiro impressão</strong>, usuários com acesso ao módulo visualizam o consolidado geral. O filtro por cliente é opcional. Quando o cliente usa <strong>franquia</strong>, o sistema considera <strong>Valor da franquia = Val.Franquia/Taxa Fixa × Págs. Franquia</strong> e usa <strong>Val.Excedido/Produzido</strong> como valor excedente.
</div>

<?php if ($tab === 'geral'): ?>
        <?php
            $topEmpresa = $resumoEmpresas[0] ?? null;
            $historicoLabels = [];
            $historicoPaginasSerie = [];
            $historicoFaturamentoSerie = [];
            $historicoImpressorasSerie = [];
            foreach ($historicoCompetencias as $__hist) {
                $historicoLabels[] = fin_competencia_label((string) $__hist['competencia']);
                $historicoPaginasSerie[] = (float) ($__hist['paginas'] ?? 0);
                $historicoFaturamentoSerie[] = round((float) ($__hist['total'] ?? 0), 2);
                $historicoImpressorasSerie[] = (int) ($__hist['impressoras'] ?? 0);
            }
            $geralAtual = $historicoCompetencias ? $historicoCompetencias[count($historicoCompetencias) - 1] : null;
            $geralAnterior = count($historicoCompetencias) > 1 ? $historicoCompetencias[count($historicoCompetencias) - 2] : null;
            $metaPaginasGeral = fin_delta_meta((float) ($geralAtual['paginas'] ?? 0), (float) ($geralAnterior['paginas'] ?? 0));
            $metaTotalGeralResumo = fin_delta_meta((float) ($geralAtual['total'] ?? 0), (float) ($geralAnterior['total'] ?? 0));
            $metaImpressorasGeral = fin_delta_meta((float) ($geralAtual['impressoras'] ?? 0), (float) ($geralAnterior['impressoras'] ?? 0));
            $historicoCustoPaginaSerie = [];
            $melhorCompetenciaOperacao = null;
            $melhorCompetenciaPaginas = 0.0;
            foreach ($historicoCompetencias as $__histAnalise) {
                $__paginasAnalise = (float) ($__histAnalise['paginas'] ?? 0);
                $__totalAnalise = (float) ($__histAnalise['total'] ?? 0);
                $historicoCustoPaginaSerie[] = $__paginasAnalise > 0 ? round($__totalAnalise / $__paginasAnalise, 4) : 0.0;
                if ($__paginasAnalise >= $melhorCompetenciaPaginas) {
                    $melhorCompetenciaPaginas = $__paginasAnalise;
                    $melhorCompetenciaOperacao = $__histAnalise;
                }
            }
            $custoPaginaAtual = ((float) ($geralAtual['paginas'] ?? 0)) > 0 ? ((float) ($geralAtual['total'] ?? 0) / (float) ($geralAtual['paginas'] ?? 0)) : 0.0;
            $custoPaginaAnterior = ((float) ($geralAnterior['paginas'] ?? 0)) > 0 ? ((float) ($geralAnterior['total'] ?? 0) / (float) ($geralAnterior['paginas'] ?? 0)) : 0.0;
            $metaCustoPaginaGeral = fin_delta_meta($custoPaginaAtual, $custoPaginaAnterior);
            $leituraAutomaticaGeral = 'Sem histórico suficiente para gerar leitura automática.';
            if ($geralAtual && $geralAnterior) {
                if ($metaPaginasGeral['delta'] > 0 && $metaTotalGeralResumo['delta'] > 0) {
                    $leituraAutomaticaGeral = 'Produção e faturamento cresceram juntos na última competência, indicando aumento real de volume na operação.';
                } elseif ($metaPaginasGeral['delta'] < 0 && $metaTotalGeralResumo['delta'] > 0) {
                    $leituraAutomaticaGeral = 'As páginas caíram, mas o faturamento subiu. Vale revisar o peso da taxa fixa e do mix de equipamentos.';
                } elseif ($metaPaginasGeral['delta'] > 0 && $metaTotalGeralResumo['delta'] < 0) {
                    $leituraAutomaticaGeral = 'A produção subiu, porém o faturamento caiu. Isso pode indicar redução do custo médio por página ou reajustes diferentes no mix.';
                } else {
                    $leituraAutomaticaGeral = 'Houve retração simultânea de páginas e faturamento na última competência em relação ao mês anterior.';
                }
            }
            $picoOperacaoResumo = $melhorCompetenciaOperacao
                ? fin_competencia_label((string) $melhorCompetenciaOperacao['competencia']) . ' · ' . fin_num((float) ($melhorCompetenciaOperacao['paginas'] ?? 0)) . ' páginas'
                : 'Sem histórico';
        ?>
        <div class="fin-geral-shell fin-geral-dashboard">
            <div class="fin-geral-top-grid">
                <div class="card pad-lg fin-card">
                    <div class="fin-card-head">
                        <div>
                            <h3>Visão geral da operação</h3>
                            <p>Leitura rápida da competência mais recente em comparação com o mês anterior.</p>
                        </div>
                        <div class="fin-badge"><?= $ultimaCompetencia ? e(fin_competencia_label($ultimaCompetencia)) : 'Sem competência' ?></div>
                    </div>

                    <div class="fin-inline-stats fin-inline-stats-4 fin-geral-summary-grid">
                        <div class="fin-inline-stat <?= 'status-' . $metaPaginasGeral['status'] ?>">
                            <span class="s-label">Páginas no mês</span>
                            <strong class="s-value"><?= fin_num((float) ($geralAtual['paginas'] ?? 0)) ?></strong>
                            <span class="s-sub">Dif.: <?= ($metaPaginasGeral['delta'] > 0 ? '+' : '') . fin_num($metaPaginasGeral['delta']) ?> · Var.: <?= ($metaPaginasGeral['pct'] > 0 ? '+' : '') . number_format($metaPaginasGeral['pct'], 1, ',', '.') ?>%</span>
                        </div>
                        <div class="fin-inline-stat <?= 'status-' . $metaTotalGeralResumo['status'] ?>">
                            <span class="s-label">Faturamento no mês</span>
                            <strong class="s-value"><?= fin_money((float) ($geralAtual['total'] ?? 0)) ?></strong>
                            <span class="s-sub">Dif.: <?= ($metaTotalGeralResumo['delta'] > 0 ? '+' : '') . fin_money($metaTotalGeralResumo['delta']) ?> · Var.: <?= ($metaTotalGeralResumo['pct'] > 0 ? '+' : '') . number_format($metaTotalGeralResumo['pct'], 1, ',', '.') ?>%</span>
                        </div>
                        <div class="fin-inline-stat <?= 'status-' . $metaImpressorasGeral['status'] ?>">
                            <span class="s-label">Impressoras ativas</span>
                            <strong class="s-value"><?= fin_num((float) ($geralAtual['impressoras'] ?? 0)) ?></strong>
                            <span class="s-sub">Última competência importada</span>
                        </div>
                        <div class="fin-inline-stat">
                            <span class="s-label">Maior faturamento</span>
                            <strong class="s-value"><?= $topEmpresa ? e($topEmpresa['empresa']) : '-' ?></strong>
                            <span class="s-sub"><?= $topEmpresa ? fin_money((float) $topEmpresa['total']) : 'Sem dados' ?></span>
                        </div>
                    </div>
                </div>

                <div class="card pad-lg fin-card fin-geral-competencias-card">
                    <div class="fin-card-head">
                        <div>
                            <h3>Últimas competências</h3>
                            <p>Resumo rápido dos meses mais recentes para facilitar comparação.</p>
                        </div>
                    </div>
                    <?php if ($historicoCompetencias): ?>
                        <div class="fin-mini-history">
                            <?php foreach (array_reverse(array_slice($historicoCompetencias, -4)) as $hist): ?>
                                <div class="mini-history-item">
                                    <div class="mh-top">
                                        <strong><?= e(fin_competencia_label((string) $hist['competencia'])) ?></strong>
                                        <span><?= fin_money((float) ($hist['total'] ?? 0)) ?></span>
                                    </div>
                                    <div class="mh-sub">
                                        <span><?= fin_num((float) ($hist['paginas'] ?? 0)) ?> páginas</span>
                                        <span><?= fin_num((float) ($hist['impressoras'] ?? 0)) ?> impressoras</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="fin-empty">Sem competências para exibir.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card pad-lg fin-card fin-geral-chart-card">
                <div class="fin-card-head">
                    <div>
                        <h3>Evolução mensal</h3>
                        <p>O gráfico principal resume faturamento e páginas sem ocupar a tela toda. Ao lado, o custo médio por página complementa a leitura da eficiência ao longo do tempo.</p>
                    </div>
                    <div class="fin-badge"><?= count($historicoCompetencias) ?> competência(s)</div>
                </div>
                <?php if ($historicoCompetencias): ?>
                    <div class="fin-geral-chart-grid">
                        <div class="fin-chart-box tall main fin-chart-box-main-compact">
                            <div class="fin-chart-center-wrap">
                                <canvas id="finGeralEvolucaoChart"></canvas>
                            </div>
                        </div>
                        <div class="fin-geral-chart-side-stack">
                            <div class="fin-chart-box side fin-chart-box-side-compact fin-chart-box-side-tall">
                                <div class="fin-chart-mini-head">
                                    <span>Custo médio por página</span>
                                    <strong><?= fin_money($custoPaginaAtual) ?></strong>
                                </div>
                                <canvas id="finGeralCustoPaginaChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="fin-geral-insights fin-geral-insights-2">
                        <div class="insight-item">
                            <span class="insight-label">Custo por página</span>
                            <strong class="insight-value"><?= fin_money($custoPaginaAtual) ?> / página</strong>
                            <span class="insight-foot <?= 'status-' . $metaCustoPaginaGeral['status'] ?>">Dif.: <?= ($metaCustoPaginaGeral['delta'] > 0 ? '+' : '') . fin_money($metaCustoPaginaGeral['delta']) ?> · Var.: <?= ($metaCustoPaginaGeral['pct'] > 0 ? '+' : '') . number_format($metaCustoPaginaGeral['pct'], 1, ',', '.') ?>%</span>
                        </div>
                        <div class="insight-item">
                            <span class="insight-label">Pico de produção</span>
                            <strong class="insight-value"><?= e($picoOperacaoResumo) ?></strong>
                            <span class="insight-foot">Esse é o melhor mês para usar como referência de sazonalidade e capacidade da operação.</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="fin-empty">Ainda não há histórico suficiente para montar o gráfico.</div>
                <?php endif; ?>
            </div>

            <div class="card pad-lg fin-card fin-geral-ranking-card">
                <div class="fin-card-head">
                    <div>
                        <h3><?= $resumoVisao === 'grupo' ? 'Resumo por grupo' : 'Resumo por empresa' ?></h3>
                        <p><?= $resumoVisao === 'grupo' ? 'Comparativo consolidado por grupo do cliente.' : e($descricaoResumoEmpresas) ?></p>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                        <div class="fin-badge"><?= $resumoVisao === 'grupo' ? 'Visualização por grupo' : 'Visualização por empresa' ?></div>
                        <div class="fin-badge"><?= count($resumoEmpresas) ?> <?= $resumoVisao === 'grupo' ? 'grupo(s)' : 'empresa(s)' ?></div>
                    </div>
                </div>

                <?php if ($resumoEmpresas): ?>
                    <div class="fin-resumo-empresa-full">
                        <div class="fin-ranking-chart-wrap fin-ranking-chart-wrap-full">
                            <canvas id="finResumoEmpresasChart"></canvas>
                        </div>

                        <div class="fin-resumo-empresa-totais">
                            <div class="fin-mini-kpi">
                                <span class="lbl">Páginas</span>
                                <strong><?= fin_num(array_sum(array_map(function ($row) { return (float) ($row['paginas'] ?? 0); }, $resumoEmpresas))) ?></strong>
                            </div>
                            <div class="fin-mini-kpi">
                                <span class="lbl"><?= e($rotuloBaseValor) ?></span>
                                <strong><?= fin_money(array_sum(array_map(function ($row) { return (float) ($row['valor_fixo'] ?? 0); }, $resumoEmpresas))) ?></strong>
                            </div>
                            <div class="fin-mini-kpi">
                                <span class="lbl"><?= e($rotuloVariavelValor) ?></span>
                                <strong><?= fin_money(array_sum(array_map(function ($row) { return (float) ($row['valor_paginas'] ?? 0); }, $resumoEmpresas))) ?></strong>
                            </div>
                            <div class="fin-mini-kpi">
                                <span class="lbl">Total pago</span>
                                <strong><?= fin_money(array_sum(array_map(function ($row) { return (float) ($row['total'] ?? 0); }, $resumoEmpresas))) ?></strong>
                            </div>
                        </div>

                        <div class="table-wrap-full fin-resumo-empresa-table">
                            <table class="fin-table">
                                <thead>
                                    <tr>
                                        <th><?= $resumoVisao === 'grupo' ? 'Grupo' : 'Empresa' ?></th>
                                        <th>Impressoras</th>
                                        <th>Páginas</th>
                                        <th><?= e($rotuloBaseValor) ?></th>
                                        <th><?= e($rotuloVariavelValor) ?></th>
                                        <th>Total pago</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resumoEmpresas as $empresaItem): ?>
                                        <tr>
                                            <td title="<?= htmlspecialchars((string) ($empresaItem['empresa'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars((string) ($empresaItem['empresa'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td><?= fin_num((float) ($empresaItem['impressoras'] ?? 0)) ?></td>
                                            <td><?= fin_num((float) ($empresaItem['paginas'] ?? 0)) ?></td>
                                            <td><?= fin_money((float) ($empresaItem['valor_fixo'] ?? 0)) ?></td>
                                            <td><?= fin_money((float) ($empresaItem['valor_paginas'] ?? 0)) ?></td>
                                            <td><strong><?= fin_money((float) ($empresaItem['total'] ?? 0)) ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="fin-empty">Nenhum dado encontrado.</div>
                <?php endif; ?>
            </div>
        </div>

<?php elseif ($tab === 'volumetria'): ?>
<div class="card pad-lg fin-card">
    <div class="fin-card-head">
        <div><h3>Volumetria</h3><p>Histórico completo por empresa, considerando todas as competências disponíveis no filtro atual.</p></div>
        <div class="fin-badge"><?= count($volumetria) ?> empresa(s)</div>
    </div>
    <div class="fin-inline-stats per-tab">
        <?php
            $volAtualCard = 0.0; $volAnteriorCard = 0.0;
            foreach ($volumetria as $__empresaNome => $__meses) {
                $volAtualCard += (float) ($ultimaCompetencia ? ($__meses[$ultimaCompetencia] ?? 0) : 0);
                $volAnteriorCard += (float) ($competenciaAnterior ? ($__meses[$competenciaAnterior] ?? 0) : 0);
            }
            $volMetaCard = fin_delta_meta($volAtualCard, $volAnteriorCard);
        ?>
        <div class="fin-inline-stat">
            <span class="s-label">Atual</span>
            <strong class="s-value"><?= fin_num($volAtualCard) ?></strong>
            <span class="s-sub"><?= $ultimaCompetencia ? e(fin_competencia_label($ultimaCompetencia)) : 'Atual' ?></span>
        </div>
        <div class="fin-inline-stat">
            <span class="s-label">Anterior</span>
            <strong class="s-value"><?= fin_num($volAnteriorCard) ?></strong>
            <span class="s-sub"><?= $competenciaAnterior ? e(fin_competencia_label($competenciaAnterior)) : 'Anterior' ?></span>
        </div>
        <div class="fin-inline-stat <?= 'status-' . $volMetaCard['status'] ?>">
            <span class="s-label">Variação</span>
            <strong class="s-value"><?= ($volMetaCard['pct'] > 0 ? '+' : '') . number_format($volMetaCard['pct'], 1, ',', '.') ?>%</strong>
            <span class="s-sub"><?= ($volMetaCard['delta'] > 0 ? '+' : '') . fin_num($volMetaCard['delta']) ?></span>
        </div>
    </div>
    <?php if ($volumetria): ?>
        <div class="fin-table-wrap">
            <table class="fin-table table-pivot fin-detail-table fin-sticky-first fin-compare-table">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <?php foreach ($competencias as $comp): ?>
                            <th><?= e(fin_competencia_label($comp)) ?></th>
                        <?php endforeach; ?>
                        <th>Total</th>
                        <th>Diferença</th>
                        <th>Variação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $totaisPorCompetenciaVol = array_fill_keys($competencias, 0.0);
                        $totalGeralVol = 0.0;
                        $totalAtualGeral = 0.0;
                        $totalAnteriorGeral = 0.0;
                    ?>
                    <?php foreach ($volumetria as $empresaNome => $meses): ?>
                        <?php
                            $totalLinha = 0.0;
                            $atualLinha = (float) ($ultimaCompetencia ? ($meses[$ultimaCompetencia] ?? 0) : 0);
                            $anteriorLinha = (float) ($competenciaAnterior ? ($meses[$competenciaAnterior] ?? 0) : 0);
                            $meta = fin_delta_meta($atualLinha, $anteriorLinha);
                            $totalAtualGeral += $atualLinha;
                            $totalAnteriorGeral += $anteriorLinha;
                        ?>
                        <tr>
                            <td><strong><?= e($empresaNome) ?></strong><span class="fin-row-sub">Histórico completo por competência</span></td>
                            <?php foreach ($competencias as $comp): ?>
                                <?php
                                    $valorLinha = (float) ($meses[$comp] ?? 0);
                                    $totaisPorCompetenciaVol[$comp] += $valorLinha;
                                    $totalLinha += $valorLinha;
                                ?>
                                <td><?= fin_num($valorLinha) ?></td>
                            <?php endforeach; ?>
                            <?php $totalGeralVol += $totalLinha; ?>
                            <td><strong><?= fin_num($totalLinha) ?></strong></td>
                            <td class="delta <?= $meta['status'] ?>"><?= ($meta['delta'] > 0 ? '+' : '') . fin_num($meta['delta']) ?></td>
                            <td class="delta <?= $meta['status'] ?>"><?= ($meta['pct'] > 0 ? '+' : '') . number_format($meta['pct'], 1, ',', '.') ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                    <?php $metaTotalGeral = fin_delta_meta($totalAtualGeral, $totalAnteriorGeral); ?>
                    <tr class="fin-total-row">
                        <td><strong>Total geral</strong></td>
                        <?php foreach ($competencias as $comp): ?>
                            <td><?= fin_num($totaisPorCompetenciaVol[$comp] ?? 0) ?></td>
                        <?php endforeach; ?>
                        <td><strong><?= fin_num($totalGeralVol) ?></strong></td>
                        <td class="delta <?= $metaTotalGeral['status'] ?>"><?= ($metaTotalGeral['delta'] > 0 ? '+' : '') . fin_num($metaTotalGeral['delta']) ?></td>
                        <td class="delta <?= $metaTotalGeral['status'] ?>"><?= ($metaTotalGeral['pct'] > 0 ? '+' : '') . number_format($metaTotalGeral['pct'], 1, ',', '.') ?>%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="fin-empty">Nenhum dado de volumetria encontrado.</div>
    <?php endif; ?>
</div>

<?php elseif ($tab === 'valor_pago'): ?>
<div class="card pad-lg fin-card">
    <div class="fin-card-head">
        <div><h3>Valor pago</h3><p>Histórico completo por empresa, considerando todas as competências disponíveis no filtro atual.</p></div>
        <div class="fin-badge"><?= count($valorPago) ?> empresa(s)</div>
    </div>
    <div class="fin-inline-stats per-tab">
        <?php
            $valorAtualCard = 0.0; $valorAnteriorCard = 0.0;
            foreach ($valorPago as $__empresaNome => $__meses) {
                $valorAtualCard += (float) ($ultimaCompetencia ? ($__meses[$ultimaCompetencia] ?? 0) : 0);
                $valorAnteriorCard += (float) ($competenciaAnterior ? ($__meses[$competenciaAnterior] ?? 0) : 0);
            }
            $valorMetaCard = fin_delta_meta($valorAtualCard, $valorAnteriorCard);
        ?>
        <div class="fin-inline-stat">
            <span class="s-label">Atual</span>
            <strong class="s-value"><?= fin_money($valorAtualCard) ?></strong>
            <span class="s-sub"><?= $ultimaCompetencia ? e(fin_competencia_label($ultimaCompetencia)) : 'Atual' ?></span>
        </div>
        <div class="fin-inline-stat">
            <span class="s-label">Anterior</span>
            <strong class="s-value"><?= fin_money($valorAnteriorCard) ?></strong>
            <span class="s-sub"><?= $competenciaAnterior ? e(fin_competencia_label($competenciaAnterior)) : 'Anterior' ?></span>
        </div>
        <div class="fin-inline-stat <?= 'status-' . $valorMetaCard['status'] ?>">
            <span class="s-label">Variação</span>
            <strong class="s-value"><?= ($valorMetaCard['pct'] > 0 ? '+' : '') . number_format($valorMetaCard['pct'], 1, ',', '.') ?>%</strong>
            <span class="s-sub"><?= ($valorMetaCard['delta'] > 0 ? '+' : '') . fin_money($valorMetaCard['delta']) ?></span>
        </div>
    </div>
    <?php if ($valorPago): ?>
        <div class="fin-table-wrap">
            <table class="fin-table table-pivot fin-detail-table fin-sticky-first fin-compare-table money">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <?php foreach ($competencias as $comp): ?>
                            <th><?= e(fin_competencia_label($comp)) ?></th>
                        <?php endforeach; ?>
                        <th>Total</th>
                        <th>Diferença</th>
                        <th>Variação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $totaisPorCompetenciaValor = array_fill_keys($competencias, 0.0);
                        $totalGeralValor = 0.0;
                        $totalAtualValor = 0.0;
                        $totalAnteriorValor = 0.0;
                    ?>
                    <?php foreach ($valorPago as $empresaNome => $meses): ?>
                        <?php
                            $totalLinha = 0.0;
                            $atualLinha = (float) ($ultimaCompetencia ? ($meses[$ultimaCompetencia] ?? 0) : 0);
                            $anteriorLinha = (float) ($competenciaAnterior ? ($meses[$competenciaAnterior] ?? 0) : 0);
                            $meta = fin_delta_meta($atualLinha, $anteriorLinha);
                            $totalAtualValor += $atualLinha;
                            $totalAnteriorValor += $anteriorLinha;
                        ?>
                        <tr>
                            <td><strong><?= e($empresaNome) ?></strong><span class="fin-row-sub">Histórico completo por competência</span></td>
                            <?php foreach ($competencias as $comp): ?>
                                <?php
                                    $valorLinha = (float) ($meses[$comp] ?? 0);
                                    $totaisPorCompetenciaValor[$comp] += $valorLinha;
                                    $totalLinha += $valorLinha;
                                ?>
                                <td><?= fin_money($valorLinha) ?></td>
                            <?php endforeach; ?>
                            <?php $totalGeralValor += $totalLinha; ?>
                            <td><strong><?= fin_money($totalLinha) ?></strong></td>
                            <td class="delta <?= $meta['status'] ?>"><?= ($meta['delta'] > 0 ? '+' : '') . fin_money($meta['delta']) ?></td>
                            <td class="delta <?= $meta['status'] ?>"><?= ($meta['pct'] > 0 ? '+' : '') . number_format($meta['pct'], 1, ',', '.') ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                    <?php $metaTotalValor = fin_delta_meta($totalAtualValor, $totalAnteriorValor); ?>
                    <tr class="fin-total-row">
                        <td><strong>Total geral</strong></td>
                        <?php foreach ($competencias as $comp): ?>
                            <td><?= fin_money($totaisPorCompetenciaValor[$comp] ?? 0) ?></td>
                        <?php endforeach; ?>
                        <td><strong><?= fin_money($totalGeralValor) ?></strong></td>
                        <td class="delta <?= $metaTotalValor['status'] ?>"><?= ($metaTotalValor['delta'] > 0 ? '+' : '') . fin_money($metaTotalValor['delta']) ?></td>
                        <td class="delta <?= $metaTotalValor['status'] ?>"><?= ($metaTotalValor['pct'] > 0 ? '+' : '') . number_format($metaTotalValor['pct'], 1, ',', '.') ?>%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="fin-empty">Nenhum dado financeiro encontrado.</div>
    <?php endif; ?>
</div>

<?php elseif ($tab === 'modelo_custo_volume'): ?>
<div class="card pad-lg fin-card">
    <div class="fin-card-head">
        <div><h3>Volume e custo por modelo</h3><p>Visão consolidada por modelo em uma única tabela, com histórico mensal completo de páginas e valor pago.</p></div>
        <div class="fin-badge"><?= max(count($modeloVolume), count($modeloCusto)) ?> modelo(s)</div>
    </div>

    <?php
        $modeloAtualPag = 0.0; $modeloAnteriorPag = 0.0;
        foreach ($modeloVolume as $__row) {
            $modeloAtualPag += (float) ($ultimaCompetencia ? ($__row['meses'][$ultimaCompetencia] ?? 0) : 0);
            $modeloAnteriorPag += (float) ($competenciaAnterior ? ($__row['meses'][$competenciaAnterior] ?? 0) : 0);
        }
        $modeloMetaPag = fin_delta_meta($modeloAtualPag, $modeloAnteriorPag);

        $modeloAtualValor = 0.0; $modeloAnteriorValor = 0.0;
        foreach ($modeloCusto as $__row) {
            $modeloAtualValor += (float) ($ultimaCompetencia ? ($__row['meses'][$ultimaCompetencia] ?? 0) : 0);
            $modeloAnteriorValor += (float) ($competenciaAnterior ? ($__row['meses'][$competenciaAnterior] ?? 0) : 0);
        }
        $modeloMetaValor = fin_delta_meta($modeloAtualValor, $modeloAnteriorValor);

        $modeloUnificado = [];
        foreach ($modeloVolume as $__row) {
            $key = (string) $__row['modelo'];
            if (!isset($modeloUnificado[$key])) {
                $modeloUnificado[$key] = [
                    'modelo' => $__row['modelo'],
                    'equipamentos' => 0,
                    'paginas' => [],
                    'valores' => [],
                ];
            }
            $modeloUnificado[$key]['equipamentos'] = max((int) $modeloUnificado[$key]['equipamentos'], (int) ($__row['equipamentos'] ?? 0));
            $modeloUnificado[$key]['paginas'] = $__row['meses'] ?? [];
        }
        foreach ($modeloCusto as $__row) {
            $key = (string) $__row['modelo'];
            if (!isset($modeloUnificado[$key])) {
                $modeloUnificado[$key] = [
                    'modelo' => $__row['modelo'],
                    'equipamentos' => 0,
                    'paginas' => [],
                    'valores' => [],
                ];
            }
            $modeloUnificado[$key]['equipamentos'] = max((int) $modeloUnificado[$key]['equipamentos'], (int) ($__row['equipamentos'] ?? 0));
            $modeloUnificado[$key]['valores'] = $__row['meses'] ?? [];
        }
        ksort($modeloUnificado, SORT_NATURAL | SORT_FLAG_CASE);
    ?>
    <div class="fin-inline-stats per-tab fin-inline-stats-4">
        <div class="fin-inline-stat">
            <span class="s-label">Páginas atuais</span>
            <strong class="s-value"><?= fin_num($modeloAtualPag) ?></strong>
            <span class="s-sub"><?= $ultimaCompetencia ? e(fin_competencia_label($ultimaCompetencia)) : 'Atual' ?></span>
        </div>
        <div class="fin-inline-stat <?= 'status-' . $modeloMetaPag['status'] ?>">
            <span class="s-label">Variação de páginas</span>
            <strong class="s-value"><?= ($modeloMetaPag['pct'] > 0 ? '+' : '') . number_format($modeloMetaPag['pct'], 1, ',', '.') ?>%</strong>
            <span class="s-sub"><?= ($modeloMetaPag['delta'] > 0 ? '+' : '') . fin_num($modeloMetaPag['delta']) ?></span>
        </div>
        <div class="fin-inline-stat">
            <span class="s-label">Custo atual</span>
            <strong class="s-value"><?= fin_money($modeloAtualValor) ?></strong>
            <span class="s-sub"><?= $ultimaCompetencia ? e(fin_competencia_label($ultimaCompetencia)) : 'Atual' ?></span>
        </div>
        <div class="fin-inline-stat <?= 'status-' . $modeloMetaValor['status'] ?>">
            <span class="s-label">Variação de custo</span>
            <strong class="s-value"><?= ($modeloMetaValor['pct'] > 0 ? '+' : '') . number_format($modeloMetaValor['pct'], 1, ',', '.') ?>%</strong>
            <span class="s-sub"><?= ($modeloMetaValor['delta'] > 0 ? '+' : '') . fin_money($modeloMetaValor['delta']) ?></span>
        </div>
    </div>

    <?php if ($modeloUnificado): ?>
        <div class="fin-model-grid">
            <?php foreach ($modeloUnificado as $row): ?>
                <?php
                    $totalLinhaPag = 0.0;
                    $totalLinhaValor = 0.0;
                    foreach ($competencias as $comp) {
                        $totalLinhaPag += (float) ($row['paginas'][$comp] ?? 0);
                        $totalLinhaValor += (float) ($row['valores'][$comp] ?? 0);
                    }
                    $atualLinhaPag = (float) ($ultimaCompetencia ? ($row['paginas'][$ultimaCompetencia] ?? 0) : 0);
                    $anteriorLinhaPag = (float) ($competenciaAnterior ? ($row['paginas'][$competenciaAnterior] ?? 0) : 0);
                    $metaPag = fin_delta_meta($atualLinhaPag, $anteriorLinhaPag);
                    $atualLinhaValor = (float) ($ultimaCompetencia ? ($row['valores'][$ultimaCompetencia] ?? 0) : 0);
                    $anteriorLinhaValor = (float) ($competenciaAnterior ? ($row['valores'][$competenciaAnterior] ?? 0) : 0);
                    $metaValor = fin_delta_meta($atualLinhaValor, $anteriorLinhaValor);
                ?>
                <section class="fin-model-card">
                    <div class="fin-model-head">
                        <div>
                            <h4><?= e($row['modelo']) ?></h4>
                            <p><?= (int) $row['equipamentos'] ?> equipamento(s)</p>
                        </div>
                        <div class="fin-model-totals">
                            <div class="fin-mini-kpi">
                                <span>Total volume</span>
                                <strong><?= fin_num($totalLinhaPag) ?></strong>
                            </div>
                            <div class="fin-mini-kpi">
                                <span>Total custo</span>
                                <strong><?= fin_money($totalLinhaValor) ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="fin-model-stats">
                        <div class="fin-model-stat <?= 'status-' . $metaPag['status'] ?>">
                            <span class="label">Páginas</span>
                            <strong><?= ($metaPag['delta'] > 0 ? '+' : '') . fin_num($metaPag['delta']) ?></strong>
                            <small><?= ($metaPag['pct'] > 0 ? '+' : '') . number_format($metaPag['pct'], 1, ',', '.') ?>%</small>
                        </div>
                        <div class="fin-model-stat <?= 'status-' . $metaValor['status'] ?>">
                            <span class="label">Custo</span>
                            <strong><?= ($metaValor['delta'] > 0 ? '+' : '') . fin_money($metaValor['delta']) ?></strong>
                            <small><?= ($metaValor['pct'] > 0 ? '+' : '') . number_format($metaValor['pct'], 1, ',', '.') ?>%</small>
                        </div>
                    </div>

                    <div class="fin-table-wrap fin-model-table-wrap">
                        <table class="fin-table fin-model-table">
                            <thead>
                                <tr>
                                    <th>Indicador</th>
                                    <?php foreach ($competencias as $comp): ?>
                                        <th><?= e(fin_competencia_label($comp)) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Volume</strong><span class="muted">Páginas impressas</span></td>
                                    <?php foreach ($competencias as $comp): ?>
                                        <td><?= fin_num((float) ($row['paginas'][$comp] ?? 0)) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td><strong>Custo</strong><span class="muted">Valor pago</span></td>
                                    <?php foreach ($competencias as $comp): ?>
                                        <td><?= fin_money((float) ($row['valores'][$comp] ?? 0)) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="fin-empty">Nenhum dado por modelo encontrado.</div>
    <?php endif; ?>
</div>

<?php elseif ($tab === 'paginas_impressora'): ?>
<div class="card pad-lg fin-card">
    <div class="fin-card-head">
        <div><h3>Páginas por impressora</h3><p>Histórico completo por série, exibindo todas as competências disponíveis no filtro atual.</p></div>
        <div class="fin-badge"><?= count($paginasImpressora) ?> série(s)</div>
    </div>
    <div class="fin-inline-stats per-tab">
        <?php
            $pagAtualCard = 0.0; $pagAnteriorCard = 0.0;
            foreach ($paginasImpressora as $__row) {
                $pagAtualCard += (float) ($ultimaCompetencia ? ($__row['meses'][$ultimaCompetencia] ?? 0) : 0);
                $pagAnteriorCard += (float) ($competenciaAnterior ? ($__row['meses'][$competenciaAnterior] ?? 0) : 0);
            }
            $pagMetaCard = fin_delta_meta($pagAtualCard, $pagAnteriorCard);
        ?>
        <div class="fin-inline-stat">
            <span class="s-label">Atual</span>
            <strong class="s-value"><?= fin_num($pagAtualCard) ?></strong>
            <span class="s-sub"><?= $ultimaCompetencia ? e(fin_competencia_label($ultimaCompetencia)) : 'Atual' ?></span>
        </div>
        <div class="fin-inline-stat">
            <span class="s-label">Anterior</span>
            <strong class="s-value"><?= fin_num($pagAnteriorCard) ?></strong>
            <span class="s-sub"><?= $competenciaAnterior ? e(fin_competencia_label($competenciaAnterior)) : 'Anterior' ?></span>
        </div>
        <div class="fin-inline-stat <?= 'status-' . $pagMetaCard['status'] ?>">
            <span class="s-label">Variação</span>
            <strong class="s-value"><?= ($pagMetaCard['pct'] > 0 ? '+' : '') . number_format($pagMetaCard['pct'], 1, ',', '.') ?>%</strong>
            <span class="s-sub"><?= ($pagMetaCard['delta'] > 0 ? '+' : '') . fin_num($pagMetaCard['delta']) ?></span>
        </div>
    </div>
    <?php if ($paginasImpressora): ?>
        <div class="fin-table-wrap">
            <table class="fin-table table-wide fin-detail-table fin-sticky-first">
                <thead>
                    <tr>
                        <th>Série / Empresa</th>
                        <?php foreach ($competencias as $comp): ?>
                            <th><?= e(fin_competencia_label($comp)) ?></th>
                        <?php endforeach; ?>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $totaisPorCompetenciaPaginas = array_fill_keys($competencias, 0.0); $totalGeralPaginas = 0.0; ?>
                    <?php foreach ($paginasImpressora as $row): ?>
                        <?php $totalLinha = 0.0; ?>
                        <tr>
                            <td><strong><?= e($row['serie']) ?></strong><span class="fin-row-sub"><?= e($row['empresa']) ?> • <?= e($row['modelo'] ?: '-') ?></span></td>
                            <?php foreach ($competencias as $comp): ?>
                                <?php
                                    $valorLinha = (float) ($row['meses'][$comp] ?? 0);
                                    $totaisPorCompetenciaPaginas[$comp] += $valorLinha;
                                    $totalLinha += $valorLinha;
                                ?>
                                <td><?= fin_num($valorLinha) ?></td>
                            <?php endforeach; ?>
                            <?php $totalGeralPaginas += $totalLinha; ?>
                            <td><strong><?= fin_num($totalLinha) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="fin-total-row">
                        <td><strong>Total geral</strong></td>
                        <?php foreach ($competencias as $comp): ?>
                            <td><?= fin_num($totaisPorCompetenciaPaginas[$comp] ?? 0) ?></td>
                        <?php endforeach; ?>
                        <td><strong><?= fin_num($totalGeralPaginas) ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="fin-empty">Nenhuma impressora encontrada no período.</div>
    <?php endif; ?>
</div>

<?php elseif ($tab === 'contador_mensal'): ?>
<div class="card pad-lg fin-card">
    <div class="fin-card-head">
        <div><h3>Contador final por mês</h3><p>Histórico completo do contador final por série, exibindo todas as competências disponíveis no filtro atual.</p></div>
        <div class="fin-badge"><?= count($contadorMensal) ?> série(s)</div>
    </div>
    <div class="fin-inline-stats per-tab">
        <?php
            $contAtualCard = 0.0; $contAnteriorCard = 0.0;
            foreach ($contadorMensal as $__row) {
                $contAtualCard += (float) ($ultimaCompetencia ? ($__row['meses'][$ultimaCompetencia] ?? 0) : 0);
                $contAnteriorCard += (float) ($competenciaAnterior ? ($__row['meses'][$competenciaAnterior] ?? 0) : 0);
            }
            $contMetaCard = fin_delta_meta($contAtualCard, $contAnteriorCard);
        ?>
        <div class="fin-inline-stat">
            <span class="s-label">Atual</span>
            <strong class="s-value"><?= number_format($contAtualCard, 2, ',', '.') ?></strong>
            <span class="s-sub"><?= $ultimaCompetencia ? e(fin_competencia_label($ultimaCompetencia)) : 'Atual' ?></span>
        </div>
        <div class="fin-inline-stat">
            <span class="s-label">Anterior</span>
            <strong class="s-value"><?= number_format($contAnteriorCard, 2, ',', '.') ?></strong>
            <span class="s-sub"><?= $competenciaAnterior ? e(fin_competencia_label($competenciaAnterior)) : 'Anterior' ?></span>
        </div>
        <div class="fin-inline-stat <?= 'status-' . $contMetaCard['status'] ?>">
            <span class="s-label">Variação</span>
            <strong class="s-value"><?= ($contMetaCard['pct'] > 0 ? '+' : '') . number_format($contMetaCard['pct'], 1, ',', '.') ?>%</strong>
            <span class="s-sub"><?= ($contMetaCard['delta'] > 0 ? '+' : '') . number_format($contMetaCard['delta'], 2, ',', '.') ?></span>
        </div>
    </div>
    <?php if ($contadorMensal): ?>
        <div class="fin-table-wrap">
            <table class="fin-table table-wide fin-detail-table fin-sticky-first">
                <thead>
                    <tr>
                        <th>Série / Empresa</th>
                        <?php foreach ($competencias as $comp): ?>
                            <th><?= e(fin_competencia_label($comp)) ?></th>
                        <?php endforeach; ?>
                        <th>Último contador</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $totaisPorCompetenciaContador = array_fill_keys($competencias, 0.0); ?>
                    <?php foreach ($contadorMensal as $row): ?>
                        <?php $ultimoLinha = 0.0; ?>
                        <tr>
                            <td><strong><?= e($row['serie']) ?></strong><span class="fin-row-sub"><?= e($row['empresa']) ?> • <?= e($row['modelo'] ?: '-') ?></span></td>
                            <?php foreach ($competencias as $comp): ?>
                                <?php
                                    $valorLinha = (float) ($row['meses'][$comp] ?? 0);
                                    $totaisPorCompetenciaContador[$comp] += $valorLinha;
                                    $ultimoLinha = $valorLinha > 0 ? $valorLinha : $ultimoLinha;
                                ?>
                                <td><?= number_format($valorLinha, 2, ',', '.') ?></td>
                            <?php endforeach; ?>
                            <td><strong><?= number_format((float) ($ultimaCompetencia ? ($row['meses'][$ultimaCompetencia] ?? $ultimoLinha) : $ultimoLinha), 2, ',', '.') ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="fin-total-row">
                        <td><strong>Total geral</strong></td>
                        <?php foreach ($competencias as $comp): ?>
                            <td><?= number_format($totaisPorCompetenciaContador[$comp] ?? 0, 2, ',', '.') ?></td>
                        <?php endforeach; ?>
                        <td><strong><?= number_format((float) ($ultimaCompetencia ? ($totaisPorCompetenciaContador[$ultimaCompetencia] ?? 0) : 0), 2, ',', '.') ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="fin-empty">Nenhum contador encontrado para o filtro atual.</div>
    <?php endif; ?>
</div>

<?php elseif ($tab === 'detalhe'): ?>
<div class="card pad-lg fin-card">
    <div class="fin-card-head">
        <div><h3>Detalhe por impressora</h3><p>Tabela operacional completa da base, mantendo o padrão do módulo.</p></div>
        <div class="fin-badge"><?= number_format($pagination['total']) ?> linha(s)</div>
    </div>
    <?php if ($equipamentos): ?>
        <div class="fin-table-wrap">
            <table class="fin-table table-wide fin-detail-table fin-sticky-first">
                <thead>
                    <tr>
                        <th>Competência</th><th>Empresa</th><th>Contrato</th><th>UF</th><th>Cidade</th><th>Centro custo</th><th>Local</th><th>Departamento</th><th>Tipo</th><th>Equipamento</th><th>Modelo</th><th>Série</th><th>Patrimônio</th><th>Medidor</th><th>Data leitura</th><th>Medidor inicial</th><th>Medidor final</th><th>Páginas</th><th>Págs. franquia</th><th>Págs. excedente</th><th><?= e($rotuloBaseValor) ?></th><th>Valor unit.</th><th><?= e($rotuloVariavelValor) ?></th><th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $sumMedIni = 0.0; $sumMedFim = 0.0; $sumPag = 0.0; $sumFranq = 0.0; $sumExc = 0.0; $sumFixo = 0.0; $sumUnit = 0.0; $sumVar = 0.0; $sumTotal = 0.0;
                    ?>
                    <?php foreach ($equipamentos as $row): ?>
                        <?php
                            $sumMedIni += (float) $row['medidor_inicial'];
                            $sumMedFim += (float) $row['medidor_final'];
                            $sumPag += (float) $row['paginas_produzidas'];
                            $sumFranq += (float) ($row['paginas_franquia'] ?? 0);
                            $sumExc += (float) ($row['paginas_excedente'] ?? 0);
                            $sumFixo += (float) $row['valor_fixo'];
                            $sumUnit += (float) $row['valor_unitario'];
                            $sumVar += (float) $row['valor_variavel'];
                            $sumTotal += (float) $row['valor_total'];
                        ?>
                        <tr>
                            <td><?= e(fin_competencia_label((string) $row['competencia'])) ?></td>
                            <td><strong><?= e($row['empresa'] ?: '-') ?></strong></td>
                            <td><?= e($row['contrato_numero'] ?: $row['contrato_codigo'] ?: '-') ?></td>
                            <td><?= e($row['uf'] ?: '-') ?></td>
                            <td><?= e($row['municipio'] ?: '-') ?></td>
                            <td><?= e($row['centro_custo'] ?: '-') ?></td>
                            <td><?= e($row['local_inst'] ?: '-') ?></td>
                            <td><?= e($row['departamento'] ?: '-') ?></td>
                            <td><?= e($row['tipo'] ?: '-') ?></td>
                            <td><?= e($row['equipamento_codigo'] ?: '-') ?></td>
                            <td><?= e($row['modelo'] ?: '-') ?></td>
                            <td><?= e($row['serie'] ?: '-') ?></td>
                            <td><?= e($row['patrimonio'] ?: '-') ?></td>
                            <td><?= e($row['medidor'] ?: '-') ?></td>
                            <td><?= e($row['data_leitura'] ?: '-') ?></td>
                            <td><?= number_format((float) $row['medidor_inicial'], 2, ',', '.') ?></td>
                            <td><?= number_format((float) $row['medidor_final'], 2, ',', '.') ?></td>
                            <td><?= fin_num((float) $row['paginas_produzidas']) ?></td>
                            <td><?= fin_num((float) ($row['paginas_franquia'] ?? 0)) ?></td>
                            <td><?= fin_num((float) ($row['paginas_excedente'] ?? 0)) ?></td>
                            <td><?= fin_money((float) $row['valor_fixo']) ?></td>
                            <td><?= fin_money((float) $row['valor_unitario']) ?></td>
                            <td><?= fin_money((float) $row['valor_variavel']) ?></td>
                            <td><strong><?= fin_money((float) $row['valor_total']) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="fin-total-row">
                        <td colspan="15"><strong>Total geral</strong></td>
                        <td><?= number_format($sumMedIni, 2, ',', '.') ?></td>
                        <td><?= number_format($sumMedFim, 2, ',', '.') ?></td>
                        <td><?= fin_num($sumPag) ?></td>
                        <td><?= fin_num($sumFranq) ?></td>
                        <td><?= fin_num($sumExc) ?></td>
                        <td><?= fin_money($sumFixo) ?></td>
                        <td><?= fin_money($sumUnit) ?></td>
                        <td><?= fin_money($sumVar) ?></td>
                        <td><strong><?= fin_money($sumTotal) ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php if ($pagination['pages'] > 1): ?>
            <div class="fin-pagination">
                <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
                    <a class="page-link <?= $p === $pagination['page'] ? 'active' : '' ?>" href="<?= e(fin_build_url(['page' => $p])) ?>"><?= $p ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="fin-empty">Nenhum registro encontrado para os filtros selecionados.</div>
    <?php endif; ?>
</div>


    <?php else: ?>
        <div class="card pad-lg fin-card">
            <div class="fin-card-head">
                <div><h3>Importações</h3><p>Arquivos importados por competência.</p></div>
            </div>
            <?php if ($ultimasImportacoes): ?>
                <div class="fin-table-wrap">
                    <table class="fin-table">
                        <thead><tr><th>Competência</th><th>Arquivo</th><th>Empresa principal</th><th>Grupo</th><th>Linhas</th><th>Criado em</th></tr></thead>
                        <tbody>
                            <?php foreach ($ultimasImportacoes as $item): ?>
                                <tr>
                                    <td><strong><?= e(fin_competencia_label((string) $item['competencia'])) ?></strong><span class="muted"><?= e($item['competencia']) ?></span></td>
                                    <td><strong><?= e($item['arquivo_nome']) ?></strong></td>
                                    <td><?= e($item['empresa_principal'] ?: 'Múltiplas empresas') ?></td>
                                    <td><?= e($item['grupo_nome'] ?: '-') ?></td>
                                    <td><?= fin_num($item['total_registros']) ?></td>
                                    <td><?= e($item['created_at'] ?: '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?><div class="fin-empty">Ainda não há importações registradas.</div><?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php if ($tab === 'geral' && $historicoCompetencias): ?>
<script>
(function(){
    const canvas = document.getElementById('finGeralEvolucaoChart');
    if (!canvas || typeof Chart === 'undefined') return;

    const labels = <?= json_encode($historicoLabels, JSON_UNESCAPED_UNICODE) ?>;
    const faturamento = <?= json_encode($historicoFaturamentoSerie, JSON_UNESCAPED_UNICODE) ?>;
    const paginas = <?= json_encode($historicoPaginasSerie, JSON_UNESCAPED_UNICODE) ?>;
    const custoPagina = <?= json_encode($historicoCustoPaginaSerie, JSON_UNESCAPED_UNICODE) ?>;

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Faturamento',
                    data: faturamento,
                    yAxisID: 'yValor',
                    order: 2,
                    backgroundColor: 'rgba(163, 29, 30, 0.76)',
                    borderRadius: 10,
                    borderSkipped: false,
                    categoryPercentage: 0.58,
                    barPercentage: 0.64,
                    maxBarThickness: 54
                },
                {
                    type: 'line',
                    label: 'Páginas',
                    data: paginas,
                    yAxisID: 'yPaginas',
                    order: 1,
                    borderColor: '#7dd3fc',
                    backgroundColor: 'rgba(125, 211, 252, 0.18)',
                    tension: 0.34,
                    fill: false,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#7dd3fc',
                    pointBorderColor: '#7dd3fc',
                    pointBorderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            layout: {
                padding: {
                    left: 20,
                    right: 20,
                    top: 28,
                    bottom: 10
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'start',
                    fullSize: true,
                    labels: {
                        color: '#dbe7f3',
                        usePointStyle: true,
                        boxWidth: 10,
                        boxHeight: 10,
                        padding: 22
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx){
                            if (ctx.dataset.yAxisID === 'yValor') {
                                return ctx.dataset.label + ': ' +
                                    new Intl.NumberFormat('pt-BR', {
                                        style: 'currency',
                                        currency: 'BRL'
                                    }).format(ctx.raw || 0);
                            }

                            return ctx.dataset.label + ': ' +
                                new Intl.NumberFormat('pt-BR').format(ctx.raw || 0) +
                                ' páginas';
                        }
                    }
                }
            },
            scales: {
                x: {
                    offset: true,
                    ticks: {
                        color: '#9fb0c4',
                        maxRotation: 0,
                        autoSkip: false,
                        padding: 8
                    },
                    grid: {
                        display: false
                    }
                },
                yValor: {
                    position: 'left',
                    beginAtZero: true,
                    grace: '10%',
                    ticks: {
                        color: '#f0b4b4',
                        padding: 10,
                        callback: function(value){
                            return new Intl.NumberFormat('pt-BR', {
                                style: 'currency',
                                currency: 'BRL',
                                maximumFractionDigits: 0
                            }).format(value);
                        }
                    },
                    grid: {
                        color: 'rgba(255,255,255,0.06)',
                        drawBorder: false
                    }
                },
                yPaginas: {
                    position: 'right',
                    beginAtZero: true,
                    grace: '10%',
                    ticks: {
                        color: '#98dfff',
                        padding: 10,
                        callback: function(value){
                            return new Intl.NumberFormat('pt-BR', {
                                maximumFractionDigits: 0
                            }).format(value);
                        }
                    },
                    grid: {
                        drawOnChartArea: false,
                        drawBorder: false
                    }
                }
            }
        }
    });

    const canvasCpp = document.getElementById('finGeralCustoPaginaChart');
    if (canvasCpp) {
        new Chart(canvasCpp, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Custo por página',
                    data: custoPagina,
                    borderColor: '#34d399',
                    backgroundColor: 'rgba(52, 211, 153, 0.18)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointHoverRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 8,
                        right: 8,
                        top: 0,
                        bottom: 0
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx){
                                return 'Custo/página: ' +
                                    new Intl.NumberFormat('pt-BR', {
                                        style: 'currency',
                                        currency: 'BRL',
                                        minimumFractionDigits: 3,
                                        maximumFractionDigits: 3
                                    }).format(ctx.raw || 0);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        offset: true,
                        ticks: {
                            color: '#9fb0c4',
                            maxRotation: 0,
                            autoSkip: true,
                            padding: 6
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grace: '12%',
                        ticks: {
                            color: '#91f0c7',
                            padding: 8,
                            callback: function(value){
                                return new Intl.NumberFormat('pt-BR', {
                                    style: 'currency',
                                    currency: 'BRL',
                                    minimumFractionDigits: 3,
                                    maximumFractionDigits: 3
                                }).format(value);
                            }
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.05)',
                            drawBorder: false
                        }
                    }
                }
            }
        });
    }
})();
</script>



<?php endif; ?>


<?php if ($tab === 'geral' && $resumoEmpresas): ?>
<script>
(function(){
    if (typeof Chart === 'undefined') return;
    const canvas = document.getElementById('finResumoEmpresasChart');
    if (!canvas) return;

    const fullLabels = <?= json_encode(array_map(function ($row) { return (string) ($row['empresa'] ?? ''); }, $resumoEmpresas), JSON_UNESCAPED_UNICODE) ?>;
    const labels = fullLabels.map(function(label){
        const clean = String(label || '').trim();
        return clean.length > 28 ? clean.substring(0, 28).trim() + '…' : clean;
    });
    const paginas = <?= json_encode(array_map(function ($row) { return round((float) ($row['paginas'] ?? 0), 0); }, $resumoEmpresas), JSON_UNESCAPED_UNICODE) ?>;
    const valorFixo = <?= json_encode(array_map(function ($row) { return round((float) ($row['valor_fixo'] ?? 0), 2); }, $resumoEmpresas), JSON_UNESCAPED_UNICODE) ?>;
    const valorPaginas = <?= json_encode(array_map(function ($row) { return round((float) ($row['valor_paginas'] ?? 0), 2); }, $resumoEmpresas), JSON_UNESCAPED_UNICODE) ?>;
    const totalPago = <?= json_encode(array_map(function ($row) { return round((float) ($row['total'] ?? 0), 2); }, $resumoEmpresas), JSON_UNESCAPED_UNICODE) ?>;

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: <?= json_encode($rotuloBaseValor, JSON_UNESCAPED_UNICODE) ?>,
                    data: valorFixo,
                    stack: 'valor',
                    yAxisID: 'yValor',
                    backgroundColor: 'rgba(163, 29, 30, 0.90)',
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 28
                },
                {
                    label: <?= json_encode($rotuloVariavelValor, JSON_UNESCAPED_UNICODE) ?>,
                    data: valorPaginas,
                    stack: 'valor',
                    yAxisID: 'yValor',
                    backgroundColor: 'rgba(249, 115, 22, 0.88)',
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 28
                },
                {
                    type: 'line',
                    label: 'Páginas',
                    data: paginas,
                    yAxisID: 'yPaginas',
                    borderColor: '#7dd3fc',
                    backgroundColor: 'rgba(125, 211, 252, 0.18)',
                    tension: 0.30,
                    fill: false,
                    borderWidth: 3,
                    pointRadius: 3,
                    pointHoverRadius: 4,
                    pointBackgroundColor: '#7dd3fc',
                    pointBorderColor: '#7dd3fc'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            layout: {
                padding: { top: 8, right: 10, bottom: 0, left: 8 }
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'start',
                    labels: {
                        color: '#dbe7f3',
                        usePointStyle: true,
                        boxWidth: 10,
                        boxHeight: 10,
                        padding: 16
                    }
                },
                tooltip: {
                    callbacks: {
                        title: function(items){
                            const idx = items && items.length ? items[0].dataIndex : 0;
                            return fullLabels[idx] || '';
                        },
                        label: function(ctx){
                            const label = ctx.dataset.label || '';
                            if (ctx.dataset.yAxisID === 'yPaginas') {
                                return label + ': ' + new Intl.NumberFormat('pt-BR').format(ctx.raw || 0);
                            }
                            return label + ': ' + new Intl.NumberFormat('pt-BR', {
                                style: 'currency',
                                currency: 'BRL'
                            }).format(ctx.raw || 0);
                        },
                        afterBody: function(items){
                            const idx = items && items.length ? items[0].dataIndex : -1;
                            if (idx < 0) return '';
                            return 'Total pago: ' + new Intl.NumberFormat('pt-BR', {
                                style: 'currency',
                                currency: 'BRL'
                            }).format(totalPago[idx] || 0);
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    ticks: {
                        color: '#9fb0c4',
                        maxRotation: 35,
                        minRotation: 35
                    },
                    grid: { display: false }
                },
                yValor: {
                    position: 'left',
                    stacked: true,
                    beginAtZero: true,
                    grace: '10%',
                    ticks: {
                        color: '#f0b4b4',
                        callback: function(value){
                            return new Intl.NumberFormat('pt-BR', {
                                style: 'currency',
                                currency: 'BRL',
                                maximumFractionDigits: 0
                            }).format(value);
                        }
                    },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                },
                yPaginas: {
                    position: 'right',
                    beginAtZero: true,
                    grace: '10%',
                    ticks: {
                        color: '#98dfff',
                        callback: function(value){
                            return new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 0 }).format(value);
                        }
                    },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>

