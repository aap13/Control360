<?php

function impressao_financeiro_ensure_tables(): void
{
    $db = getDB();

    $db->exec("CREATE TABLE IF NOT EXISTS impressao_financeiro_importacoes (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        cliente_id INT UNSIGNED NOT NULL,
        competencia VARCHAR(7) NOT NULL,
        arquivo_nome VARCHAR(255) NOT NULL,
        arquivo_base VARCHAR(190) NOT NULL,
        grupo_nome VARCHAR(190) DEFAULT NULL,
        empresa_principal VARCHAR(190) DEFAULT NULL,
        total_registros INT UNSIGNED NOT NULL DEFAULT 0,
        total_paginas BIGINT NOT NULL DEFAULT 0,
        total_fixo DECIMAL(14,4) NOT NULL DEFAULT 0,
        total_variavel DECIMAL(14,4) NOT NULL DEFAULT 0,
        total_geral DECIMAL(14,4) NOT NULL DEFAULT 0,
        uploaded_by INT UNSIGNED DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_financeiro_base (cliente_id, competencia, arquivo_base)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $db->exec("CREATE TABLE IF NOT EXISTS impressao_financeiro_equipamentos (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        importacao_id INT UNSIGNED NOT NULL,
        cliente_id INT UNSIGNED NOT NULL,
        competencia VARCHAR(7) NOT NULL,
        grupo_nome VARCHAR(190) DEFAULT NULL,
        empresa VARCHAR(255) DEFAULT NULL,
        contrato_numero VARCHAR(60) DEFAULT NULL,
        contrato_codigo VARCHAR(60) DEFAULT NULL,
        cliente_codigo VARCHAR(60) DEFAULT NULL,
        uf VARCHAR(10) DEFAULT NULL,
        municipio VARCHAR(150) DEFAULT NULL,
        centro_custo VARCHAR(120) DEFAULT NULL,
        local_inst VARCHAR(190) DEFAULT NULL,
        departamento VARCHAR(190) DEFAULT NULL,
        tipo VARCHAR(100) DEFAULT NULL,
        equipamento_codigo VARCHAR(80) DEFAULT NULL,
        modelo VARCHAR(150) DEFAULT NULL,
        serie VARCHAR(150) DEFAULT NULL,
        patrimonio VARCHAR(150) DEFAULT NULL,
        medidor VARCHAR(80) DEFAULT NULL,
        data_leitura DATE DEFAULT NULL,
        medidor_inicial DECIMAL(18,2) DEFAULT NULL,
        medidor_final DECIMAL(18,2) DEFAULT NULL,
        paginas_produzidas INT DEFAULT 0,
        paginas_franquia DECIMAL(18,6) DEFAULT NULL,
        paginas_excedente DECIMAL(18,6) DEFAULT NULL,
        modelo_cobranca VARCHAR(30) NOT NULL DEFAULT 'sem_franquia',
        valor_fixo DECIMAL(14,4) DEFAULT 0,
        valor_franquia DECIMAL(14,4) DEFAULT 0,
        valor_excedente DECIMAL(14,4) DEFAULT 0,
        valor_unitario DECIMAL(14,6) DEFAULT NULL,
        valor_variavel DECIMAL(14,4) DEFAULT 0,
        valor_total DECIMAL(14,4) DEFAULT 0,
        arquivo_origem VARCHAR(255) DEFAULT NULL,
        arquivo_base VARCHAR(190) DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_fin_cliente_comp (cliente_id, competencia),
        INDEX idx_fin_empresa (empresa),
        INDEX idx_fin_serie (serie),
        CONSTRAINT fk_fin_importacao FOREIGN KEY (importacao_id) REFERENCES impressao_financeiro_importacoes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    ensure_column_exists($db, 'impressao_financeiro_equipamentos', 'modelo_cobranca', "VARCHAR(30) NOT NULL DEFAULT 'sem_franquia' AFTER paginas_excedente");
    ensure_column_exists($db, 'impressao_financeiro_equipamentos', 'valor_franquia', 'DECIMAL(14,4) NOT NULL DEFAULT 0 AFTER valor_fixo');
    ensure_column_exists($db, 'impressao_financeiro_equipamentos', 'valor_excedente', 'DECIMAL(14,4) NOT NULL DEFAULT 0 AFTER valor_franquia');
    $db->exec("ALTER TABLE impressao_financeiro_equipamentos MODIFY paginas_franquia DECIMAL(18,6) DEFAULT NULL, MODIFY paginas_excedente DECIMAL(18,6) DEFAULT NULL");
    ensure_column_exists($db, 'impressao_financeiro_importacoes', 'total_paginas', 'BIGINT NOT NULL DEFAULT 0 AFTER total_registros');
    ensure_column_exists($db, 'impressao_financeiro_importacoes', 'total_fixo', 'DECIMAL(14,4) NOT NULL DEFAULT 0 AFTER total_paginas');
    ensure_column_exists($db, 'impressao_financeiro_importacoes', 'total_variavel', 'DECIMAL(14,4) NOT NULL DEFAULT 0 AFTER total_fixo');
    ensure_column_exists($db, 'impressao_financeiro_importacoes', 'total_geral', 'DECIMAL(14,4) NOT NULL DEFAULT 0 AFTER total_variavel');
}

function impressao_financeiro_normalize_header($value): string
{
    $value = (string) $value;
    $value = str_replace(["Â ", "â"], ' ', $value);
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    if (function_exists('mb_strtolower')) {
        $value = mb_strtolower($value, 'UTF-8');
    } else {
        $value = strtolower($value);
    }
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($converted !== false && $converted !== '') {
            $value = $converted;
        }
    }
    $map = [
        'º' => '', 'ª' => '', '°' => '',
        '(' => ' ', ')' => ' ', '/' => ' ', '\\' => ' ', '-' => ' ', '.' => ' ', ':' => ' ', '$' => '',
    ];
    $value = strtr($value, $map);
    $value = preg_replace('/\s+/u', '_', $value);
    $value = preg_replace('/[^a-z0-9_]/', '', $value);
    $value = preg_replace('/_+/', '_', $value);
    $value = trim($value, '_');

    $aliases = [
        'cliente_razao' => 'cliente_razao',
        'cliente_razo' => 'cliente_razao',
        'cliente_razao_s_a' => 'cliente_razao',
        'serie' => 'serie',
        'srie' => 'serie',
        'serial' => 'serie',
        'municipio' => 'municipio',
        'municpio' => 'municipio',
        'local_inst' => 'local_inst',
        'local_inst_' => 'local_inst',
        'pags_produzidas' => 'pags_produzidas',
        'paginas_produzidas' => 'pags_produzidas',
        'valor_fixo' => 'valor_fixo',
        'val_franquia_taxa_fixa' => 'valor_fixo',
        'valor_variavel' => 'valor_variavel',
        'val_excedido_produzido' => 'valor_variavel',
        'valor_total' => 'valor_total',
    ];

    return $aliases[$value] ?? $value;
}


function impressao_financeiro_detect_competencia(string $filename): string
{
    if (preg_match('/(20\d{2})[-_](0[1-9]|1[0-2])/', $filename, $m)) {
        return $m[1] . '-' . $m[2];
    }
    return date('Y-m');
}

function impressao_financeiro_base_name(string $filename): string
{
    $base = pathinfo($filename, PATHINFO_FILENAME);
    $base = preg_replace('/\.v\d+$/i', '', $base);
    return mb_strtolower(trim((string) $base), 'UTF-8');
}

function impressao_financeiro_parse_decimal($value): float
{
    if ($value === null || $value === '') {
        return 0.0;
    }
    if (is_numeric($value)) {
        return (float) $value;
    }
    $value = trim((string) $value);
    $value = str_replace(['R$', ' '], '', $value);
    if (substr_count($value, ',') === 1 && substr_count($value, '.') >= 1) {
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
    } elseif (substr_count($value, ',') === 1) {
        $value = str_replace(',', '.', $value);
    }
    return is_numeric($value) ? (float) $value : 0.0;
}

function impressao_financeiro_parse_int($value): int
{
    return (int) floor(impressao_financeiro_parse_decimal($value) + 0.0000001);
}

function impressao_financeiro_excel_date($value): ?string
{
    if ($value instanceof DateTimeInterface) {
        return $value->format('Y-m-d');
    }
    if (is_numeric($value)) {
        $unix = ((float) $value - 25569) * 86400;
        return gmdate('Y-m-d', (int) round($unix));
    }
    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }
    $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y-m-d H:i:s', 'd/m/Y H:i:s'];
    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $value);
        if ($dt instanceof DateTime) {
            return $dt->format('Y-m-d');
        }
    }
    $time = strtotime($value);
    return $time ? date('Y-m-d', $time) : null;
}

function impressao_financeiro_normalize_company(?string $value): string
{
    $value = trim((string) $value);
    $value = preg_replace('/\s+/', ' ', $value);
    return $value;
}

function impressao_financeiro_xlsx_shared_strings(ZipArchive $zip): array
{
    $content = $zip->getFromName('xl/sharedStrings.xml');
    if ($content === false || $content === '') {
        return [];
    }
    $xml = simplexml_load_string($content);
    if (!$xml) {
        return [];
    }
    $xml->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    $out = [];
    foreach ($xml->xpath('//x:si') ?: [] as $si) {
        $parts = [];
        foreach ($si->xpath('.//*[local-name()="t"]') ?: [] as $t) {
            $parts[] = (string) $t;
        }
        $out[] = implode('', $parts);
    }
    return $out;
}

function impressao_financeiro_xlsx_sheet_path(ZipArchive $zip, string $wantedName): ?string
{
    $workbookXml = $zip->getFromName('xl/workbook.xml');
    $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
    if ($workbookXml === false || $relsXml === false) {
        return null;
    }

    $workbook = simplexml_load_string($workbookXml);
    $rels = simplexml_load_string($relsXml);
    if (!$workbook || !$rels) {
        return null;
    }

    $workbook->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    $rels->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');

    $relMap = [];
    foreach ($rels->xpath('//r:Relationship') ?: [] as $rel) {
        $id = (string) $rel['Id'];
        $target = (string) $rel['Target'];
        if ($id !== '' && $target !== '') {
            $relMap[$id] = 'xl/' . ltrim($target, '/');
        }
    }

    foreach ($workbook->xpath('//x:sheets/x:sheet') ?: [] as $sheet) {
        $name = trim((string) $sheet['name']);
        $rid = '';
        foreach ($sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships') as $attrName => $attrValue) {
            if ($attrName === 'id') {
                $rid = (string) $attrValue;
                break;
            }
        }
        if ($rid === '') {
            continue;
        }
        if (mb_strtolower($name, 'UTF-8') === mb_strtolower($wantedName, 'UTF-8')) {
            return $relMap[$rid] ?? null;
        }
    }

    // fallback: first sheet containing "cont"
    foreach ($workbook->xpath('//x:sheets/x:sheet') ?: [] as $sheet) {
        $name = trim((string) $sheet['name']);
        $rid = '';
        foreach ($sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships') as $attrName => $attrValue) {
            if ($attrName === 'id') {
                $rid = (string) $attrValue;
                break;
            }
        }
        if ($rid !== '' && stripos($name, 'cont') !== false) {
            return $relMap[$rid] ?? null;
        }
    }

    return null;
}

function impressao_financeiro_xlsx_rows(string $filePath, string $sheetName = 'Contadores'): array
{
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
        throw new RuntimeException('Não foi possível abrir o arquivo XLSX.');
    }

    $sheetPath = impressao_financeiro_xlsx_sheet_path($zip, $sheetName);
    if (!$sheetPath) {
        $zip->close();
        throw new RuntimeException('A aba Contadores não foi encontrada no arquivo XLSX.');
    }

    $sheetXmlContent = $zip->getFromName($sheetPath);
    if ($sheetXmlContent === false) {
        $zip->close();
        throw new RuntimeException('Não foi possível ler a aba Contadores do arquivo XLSX.');
    }

    $sharedStrings = impressao_financeiro_xlsx_shared_strings($zip);
    $zip->close();

    $dom = new DOMDocument();
    if (!@$dom->loadXML($sheetXmlContent)) {
        throw new RuntimeException('Falha ao interpretar o conteúdo da aba Contadores.');
    }

    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

    $rows = [];
    foreach ($xpath->query('//x:sheetData/x:row') as $rowNode) {
        $row = [];
        $currentIndex = 1;

        foreach ($xpath->query('./x:c', $rowNode) as $cell) {
            $ref = (string) $cell->getAttribute('r');
            $type = (string) $cell->getAttribute('t');

            $cellIndex = $currentIndex;
            if ($ref !== '' && preg_match('/([A-Z]+)/', $ref, $m)) {
                $cellIndex = 0;
                foreach (str_split($m[1]) as $char) {
                    $cellIndex = $cellIndex * 26 + (ord($char) - 64);
                }
            }

            while ($currentIndex < $cellIndex) {
                $row[] = '';
                $currentIndex++;
            }

            $value = '';
            $vNode = $xpath->query('./x:v', $cell)->item(0);
            if ($vNode) {
                $value = (string) $vNode->textContent;
            } else {
                $inlineTexts = [];
                foreach ($xpath->query('.//*[local-name()="t"]', $cell) as $textNode) {
                    $inlineTexts[] = (string) $textNode->textContent;
                }
                if ($inlineTexts) {
                    $value = implode('', $inlineTexts);
                }
            }

            if ($type === 's' && $value !== '' && ctype_digit((string) $value)) {
                $value = $sharedStrings[(int) $value] ?? $value;
            }

            $row[] = $value;
            $currentIndex++;
        }

        $rows[] = $row;
    }

    return $rows;
}

function impressao_financeiro_header_map(array $headerRow): array
{
    $map = [];
    foreach ($headerRow as $index => $header) {
        $normalized = impressao_financeiro_normalize_header($header);
        if ($normalized !== '') {
            $map[$normalized] = $index;
        }
    }
    return $map;
}

function impressao_financeiro_contadores_fallback_map(array $headerRow): array
{
    $count = count($headerRow);
    if ($count < 24) {
        return [];
    }

    $fallback = [
        'contrato' => 0,
        'nr_contrato' => 1,
        'cliente' => 2,
        'cliente_razao' => 3,
        'uf' => 4,
        'municipio' => 5,
        'centro_custo' => 6,
        'local_inst' => 7,
        'departamento' => 8,
    ];

    $temTipo = isset($headerRow[9]) && stripos((string) $headerRow[9], 'Tipo') !== false;

    if ($temTipo) {
        $fallback['tipo'] = 9;
        $fallback['equipamento'] = 10;
        $fallback['modelo'] = 11;
        $fallback['serie'] = 12;
        $fallback['patrimonio'] = 13;
        $fallback['medidor'] = 14;
        $fallback['data_leitura'] = 16;
        $fallback['medidor_inicial'] = 17;
        $fallback['medidor_final'] = 18;
        $fallback['pags_produzidas'] = 19;
        $fallback['pags_franquia'] = 20;
        $fallback['pags_excedente'] = 21;
        $fallback['valor_fixo'] = 22;
        $fallback['valor_unitario'] = 23;
        $fallback['valor_variavel'] = 24;
    } else {
        $fallback['equipamento'] = 9;
        $fallback['modelo'] = 10;
        $fallback['serie'] = 11;
        $fallback['patrimonio'] = 12;
        $fallback['medidor'] = 13;
        $fallback['data_leitura'] = 15;
        $fallback['medidor_inicial'] = 16;
        $fallback['medidor_final'] = 17;
        $fallback['pags_produzidas'] = 18;
        $fallback['pags_franquia'] = 19;
        $fallback['pags_excedente'] = 20;
        $fallback['valor_fixo'] = 21;
        $fallback['valor_unitario'] = 22;
        $fallback['valor_variavel'] = 23;
        if ($count >= 25) {
            $fallback['valor_total'] = 24;
        }
    }

    return $fallback;
}

function impressao_financeiro_apply_header_fallbacks(array $map, array $headerRow): array
{
    $fallback = impressao_financeiro_contadores_fallback_map($headerRow);
    foreach ($fallback as $key => $index) {
        if (!isset($map[$key]) && array_key_exists($index, $headerRow)) {
            $map[$key] = $index;
        }
    }
    return $map;
}

function impressao_financeiro_has_alias(array $map, array $aliases): bool
{
    foreach ($aliases as $alias) {
        $key = impressao_financeiro_normalize_header($alias);
        if (isset($map[$key])) {
            return true;
        }
    }
    return false;
}

function impressao_financeiro_row_value(array $row, array $map, array $aliases)
{
    foreach ($aliases as $alias) {
        $key = impressao_financeiro_normalize_header($alias);
        if (isset($map[$key])) {
            return $row[$map[$key]] ?? null;
        }
    }
    return null;
}

function impressao_financeiro_cliente_modelo_cobranca(int $clienteId): string
{
    $db = getDB();
    $stmt = $db->prepare("SELECT modelo_cobranca FROM distribuicao_clientes WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $clienteId]);
    $modelo = (string) ($stmt->fetchColumn() ?: 'sem_franquia');
    return $modelo === 'com_franquia' ? 'com_franquia' : 'sem_franquia';
}

function impressao_financeiro_parse_xlsx(string $filePath, string $originalName, int $clienteId): array
{
    $rows = impressao_financeiro_xlsx_rows($filePath, 'Contadores');
    if (count($rows) < 2) {
        throw new RuntimeException('A aba Contadores não possui dados para importação.');
    }

    $modeloCobranca = impressao_financeiro_cliente_modelo_cobranca($clienteId);
    $rowsHeader = array_shift($rows);
    $map = impressao_financeiro_header_map($rowsHeader);
    $map = impressao_financeiro_apply_header_fallbacks($map, $rowsHeader);
    $checks = [
        'cliente_razao' => ['cliente_razao', 'Cliente (Razão)', 'Cliente Razao', 'razao_social', 'cliente_razo'],
        'modelo' => ['Modelo'],
        'serie' => ['serie', 'Serie', 'Série', 'Serial'],
        'pags_produzidas' => ['pags_produzidas', 'Págs. Produzidas', 'Pags Produzidas', 'Paginas Produzidas'],
        'valor_fixo' => ['valor_fixo', 'Val.Franquia/Taxa Fixa', 'Val Franquia Taxa Fixa', 'Valor Fixo'],
    ];
    foreach ($checks as $name => $aliases) {
        if (!impressao_financeiro_has_alias($map, $aliases)) {
            throw new RuntimeException('A aba Contadores não contém a coluna esperada: ' . $name);
        }
    }

    $competencia = impressao_financeiro_detect_competencia($originalName);
    $arquivoBase = impressao_financeiro_base_name($originalName);
    $parsed = [];
    $firstCompany = null;
    $companies = [];
    $ultimoTotal = [
        'paginas' => 0,
        'fixo' => 0.0,
        'variavel' => 0.0,
        'geral' => 0.0,
    ];

    $clienteInfo = $clienteId > 0 ? distribuicao_fetch_cliente($clienteId) : null;
    $grupoNome = trim((string) ($clienteInfo['nome'] ?? ''));
    if ($grupoNome === '') {
        $grupoNome = 'Sem grupo';
    }

    foreach ($rows as $row) {
        $contratoCodigo = trim((string) impressao_financeiro_row_value($row, $map, ['Contrato']));
        $serie = trim((string) impressao_financeiro_row_value($row, $map, ['serie', 'Serie', 'Série', 'Serial']));
        $empresa = impressao_financeiro_normalize_company((string) impressao_financeiro_row_value(
            $row,
            $map,
            ['cliente_razao', 'Cliente (Razão)', 'Cliente Razao', 'razao_social', 'cliente_razo']
        ));

        $paginasProduzidas = impressao_financeiro_parse_int(impressao_financeiro_row_value($row, $map, ['pags_produzidas', 'Págs. Produzidas', 'Pags Produzidas', 'Paginas Produzidas']));
        $paginasFranquia = impressao_financeiro_parse_int(impressao_financeiro_row_value($row, $map, ['Págs. Franquia', 'Pags Franquia', 'Paginas Franquia']));
        $paginasExcedente = impressao_financeiro_parse_int(impressao_financeiro_row_value($row, $map, ['Págs. Excedente', 'Pags Excedente', 'Paginas Excedente']));
        $valorBaseUnitario = impressao_financeiro_parse_decimal(impressao_financeiro_row_value($row, $map, ['valor_fixo', 'Val.Franquia/Taxa Fixa', 'Val Franquia Taxa Fixa', 'Valor Fixo']));
        $valorExcedidoProduzido = impressao_financeiro_parse_decimal(impressao_financeiro_row_value($row, $map, ['valor_variavel', 'Val.Excedido/Produzido', 'Val Excedido Produzido', 'Valor Variavel']));
        $valorTotal = impressao_financeiro_parse_decimal(impressao_financeiro_row_value($row, $map, ['valor_total', 'Valor Total ($)', 'Valor Total']));

        $primeiraColuna = mb_strtolower(trim((string) $contratoCodigo), 'UTF-8');
        $isLinhaTotal = in_array($primeiraColuna, ['total', 'totais', 'resumo'], true)
            || (mb_strtolower(trim((string) $empresa), 'UTF-8') === 'total')
            || ($serie === '' && $empresa === '' && ($paginasProduzidas > 0 || $valorTotal > 0));

        if ($isLinhaTotal) {
            $ultimoTotal = [
                'paginas' => $paginasProduzidas,
                'fixo' => $valorBaseUnitario,
                'variavel' => $valorExcedidoProduzido,
                'geral' => $valorTotal,
            ];
            continue;
        }

        if ($serie === '' || $empresa === '') {
            continue;
        }

        if ($firstCompany === null) {
            $firstCompany = $empresa;
        }
        $companies[$empresa] = true;

        $valorFixo = $valorBaseUnitario;
        $valorFranquia = 0.0;
        $valorExcedente = $valorExcedidoProduzido;
        $valorVariavel = $valorExcedidoProduzido;

        if ($modeloCobranca === 'com_franquia') {
            $valorFranquia = $valorBaseUnitario * max(0, $paginasFranquia);
            $valorExcedente = $valorExcedidoProduzido;
            $valorFixo = $valorFranquia;
            $valorVariavel = $valorExcedente;
            if ($valorTotal == 0.0) {
                $valorTotal = $valorFranquia + $valorExcedente;
            }
        } else {
            if ($valorVariavel == 0.0 && $valorTotal && $valorFixo) {
                $valorVariavel = max(0.0, $valorTotal - $valorFixo);
            }
            if ($valorTotal == 0.0 && ($valorFixo != 0.0 || $valorVariavel != 0.0)) {
                $valorTotal = $valorFixo + $valorVariavel;
            }
        }

        $parsed[] = [
            'cliente_id' => $clienteId,
            'competencia' => $competencia,
            'arquivo_nome' => $originalName,
            'arquivo_base' => $arquivoBase,
            'grupo_nome' => $grupoNome,
            'empresa' => $empresa,
            'contrato_codigo' => $contratoCodigo,
            'contrato_numero' => trim((string) impressao_financeiro_row_value($row, $map, ['Nr. Contrato', 'Numero Contrato'])),
            'cliente_codigo' => trim((string) impressao_financeiro_row_value($row, $map, ['Cliente'])),
            'uf' => strtoupper(trim((string) impressao_financeiro_row_value($row, $map, ['UF']))),
            'municipio' => trim((string) impressao_financeiro_row_value($row, $map, ['Município', 'Municipio'])),
            'centro_custo' => trim((string) impressao_financeiro_row_value($row, $map, ['Centro Custo'])),
            'local_inst' => trim((string) impressao_financeiro_row_value($row, $map, ['Local Inst.', 'Local Inst', 'Local'])),
            'departamento' => trim((string) impressao_financeiro_row_value($row, $map, ['Departamento'])),
            'tipo' => trim((string) impressao_financeiro_row_value($row, $map, ['Tipo'])),
            'equipamento_codigo' => trim((string) impressao_financeiro_row_value($row, $map, ['Equipamento'])),
            'modelo' => trim((string) impressao_financeiro_row_value($row, $map, ['Modelo'])),
            'serie' => $serie,
            'patrimonio' => trim((string) impressao_financeiro_row_value($row, $map, ['Patrimonio', 'Patrimônio'])),
            'medidor' => trim((string) impressao_financeiro_row_value($row, $map, ['Medidor'])),
            'data_leitura' => impressao_financeiro_excel_date(impressao_financeiro_row_value($row, $map, ['Data Leitura'])),
            'medidor_inicial' => impressao_financeiro_parse_decimal(impressao_financeiro_row_value($row, $map, ['Medidor Inicial'])),
            'medidor_final' => impressao_financeiro_parse_decimal(impressao_financeiro_row_value($row, $map, ['Medidor Final'])),
            'paginas_produzidas' => $paginasProduzidas,
            'paginas_franquia' => $paginasFranquia,
            'paginas_excedente' => $paginasExcedente,
            'modelo_cobranca' => $modeloCobranca,
            'valor_fixo' => $valorFixo,
            'valor_franquia' => $valorFranquia,
            'valor_excedente' => $valorExcedente,
            'valor_unitario' => impressao_financeiro_parse_decimal(impressao_financeiro_row_value($row, $map, ['Excedente/Produção(mil)', 'Excedente Producao mil'])),
            'valor_variavel' => $valorVariavel,
            'valor_total' => $valorTotal,
        ];
    }

    if (!$parsed) {
        throw new RuntimeException('Nenhum registro financeiro válido foi encontrado na aba Contadores.');
    }

    if ($ultimoTotal['geral'] <= 0) {
        $ultimoTotal = [
            'paginas' => array_sum(array_map(static function ($item) { return (float) (isset($item['paginas_produzidas']) ? $item['paginas_produzidas'] : 0); }, $parsed)),
            'fixo' => array_sum(array_map(static function ($item) { return (float) (isset($item['valor_fixo']) ? $item['valor_fixo'] : 0); }, $parsed)),
            'variavel' => array_sum(array_map(static function ($item) { return (float) (isset($item['valor_variavel']) ? $item['valor_variavel'] : 0); }, $parsed)),
            'geral' => array_sum(array_map(static function ($item) { return (float) (isset($item['valor_total']) ? $item['valor_total'] : 0); }, $parsed)),
        ];
    }

    return [
        'competencia' => $competencia,
        'arquivo_base' => $arquivoBase,
        'arquivo_nome' => $originalName,
        'grupo_nome' => $grupoNome,
        'empresa_principal' => $firstCompany ?: (count($companies) === 1 ? impressao_financeiro_first_array_key($companies) : 'Múltiplas empresas'),
        'modelo_cobranca' => $modeloCobranca,
        'totais_arquivo' => $ultimoTotal,
        'rows' => $parsed,
    ];
}

function impressao_financeiro_first_array_key(array $items)
{
    foreach ($items as $key => $_value) {
        return $key;
    }

    return null;
}

function impressao_financeiro_import_file(int $clienteId, array $file, int $userId): array
{
    impressao_financeiro_ensure_tables();
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Falha no upload do arquivo: ' . ($file['name'] ?? 'arquivo desconhecido'));
    }
    $tmp = (string) ($file['tmp_name'] ?? '');
    $name = (string) ($file['name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        throw new RuntimeException('Arquivo temporário inválido para importação.');
    }
    if (!preg_match('/\.xlsx$/i', $name)) {
        throw new RuntimeException('Somente arquivos XLSX são aceitos nesta importação.');
    }

    $parsed = impressao_financeiro_parse_xlsx($tmp, $name, $clienteId);
    $db = getDB();
    $db->beginTransaction();
    try {
        $findImport = $db->prepare('SELECT id FROM impressao_financeiro_importacoes WHERE cliente_id = :cliente_id AND competencia = :competencia AND arquivo_base = :arquivo_base LIMIT 1');
        $findImport->execute([
            ':cliente_id' => $clienteId,
            ':competencia' => $parsed['competencia'],
            ':arquivo_base' => $parsed['arquivo_base'],
        ]);
        $existingId = (int) ($findImport->fetchColumn() ?: 0);
        if ($existingId > 0) {
            $db->prepare('DELETE FROM impressao_financeiro_importacoes WHERE id = :id')->execute([':id' => $existingId]);
        }

        $insertImport = $db->prepare('INSERT INTO impressao_financeiro_importacoes (cliente_id, competencia, arquivo_nome, arquivo_base, grupo_nome, empresa_principal, total_registros, total_paginas, total_fixo, total_variavel, total_geral, uploaded_by) VALUES (:cliente_id, :competencia, :arquivo_nome, :arquivo_base, :grupo_nome, :empresa_principal, :total_registros, :total_paginas, :total_fixo, :total_variavel, :total_geral, :uploaded_by)');
        $insertImport->execute([
            ':cliente_id' => $clienteId,
            ':competencia' => $parsed['competencia'],
            ':arquivo_nome' => $parsed['arquivo_nome'],
            ':arquivo_base' => $parsed['arquivo_base'],
            ':grupo_nome' => $parsed['grupo_nome'],
            ':empresa_principal' => $parsed['empresa_principal'],
            ':total_registros' => count($parsed['rows']),
            ':total_paginas' => (int) round((float) ($parsed['totais_arquivo']['paginas'] ?? 0)),
            ':total_fixo' => (float) ($parsed['totais_arquivo']['fixo'] ?? 0),
            ':total_variavel' => (float) ($parsed['totais_arquivo']['variavel'] ?? 0),
            ':total_geral' => (float) ($parsed['totais_arquivo']['geral'] ?? 0),
            ':uploaded_by' => $userId ?: null,
        ]);
        $importacaoId = (int) $db->lastInsertId();

        $insertRow = $db->prepare('INSERT INTO impressao_financeiro_equipamentos (
            importacao_id, cliente_id, competencia, grupo_nome, empresa, contrato_numero, contrato_codigo, cliente_codigo,
            uf, municipio, centro_custo, local_inst, departamento, tipo, equipamento_codigo, modelo, serie, patrimonio,
            medidor, data_leitura, medidor_inicial, medidor_final, paginas_produzidas, paginas_franquia, paginas_excedente,
            modelo_cobranca, valor_fixo, valor_franquia, valor_excedente, valor_unitario, valor_variavel, valor_total, arquivo_origem, arquivo_base
        ) VALUES (
            :importacao_id, :cliente_id, :competencia, :grupo_nome, :empresa, :contrato_numero, :contrato_codigo, :cliente_codigo,
            :uf, :municipio, :centro_custo, :local_inst, :departamento, :tipo, :equipamento_codigo, :modelo, :serie, :patrimonio,
            :medidor, :data_leitura, :medidor_inicial, :medidor_final, :paginas_produzidas, :paginas_franquia, :paginas_excedente,
            :modelo_cobranca, :valor_fixo, :valor_franquia, :valor_excedente, :valor_unitario, :valor_variavel, :valor_total, :arquivo_origem, :arquivo_base
        )');

        foreach ($parsed['rows'] as $row) {
            $row['importacao_id'] = $importacaoId;
            $insertRow->execute([
                ':importacao_id' => $importacaoId,
                ':cliente_id' => $row['cliente_id'],
                ':competencia' => $row['competencia'],
                ':grupo_nome' => $row['grupo_nome'],
                ':empresa' => $row['empresa'],
                ':contrato_numero' => $row['contrato_numero'],
                ':contrato_codigo' => $row['contrato_codigo'],
                ':cliente_codigo' => $row['cliente_codigo'],
                ':uf' => $row['uf'],
                ':municipio' => $row['municipio'],
                ':centro_custo' => $row['centro_custo'],
                ':local_inst' => $row['local_inst'],
                ':departamento' => $row['departamento'],
                ':tipo' => $row['tipo'],
                ':equipamento_codigo' => $row['equipamento_codigo'],
                ':modelo' => $row['modelo'],
                ':serie' => $row['serie'],
                ':patrimonio' => $row['patrimonio'],
                ':medidor' => $row['medidor'],
                ':data_leitura' => $row['data_leitura'],
                ':medidor_inicial' => $row['medidor_inicial'],
                ':medidor_final' => $row['medidor_final'],
                ':paginas_produzidas' => $row['paginas_produzidas'],
                ':paginas_franquia' => $row['paginas_franquia'],
                ':paginas_excedente' => $row['paginas_excedente'],
                ':modelo_cobranca' => $row['modelo_cobranca'],
                ':valor_fixo' => $row['valor_fixo'],
                ':valor_franquia' => $row['valor_franquia'],
                ':valor_excedente' => $row['valor_excedente'],
                ':valor_unitario' => $row['valor_unitario'],
                ':valor_variavel' => $row['valor_variavel'],
                ':valor_total' => $row['valor_total'],
                ':arquivo_origem' => $parsed['arquivo_nome'],
                ':arquivo_base' => $parsed['arquivo_base'],
            ]);
        }

        $db->commit();
        return [
            'arquivo' => $parsed['arquivo_nome'],
            'competencia' => $parsed['competencia'],
            'linhas' => count($parsed['rows']),
            'empresa_principal' => $parsed['empresa_principal'],
        ];
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}
