<?php
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function request_is_get(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET';
}

function post(string $key, $default = null)
{
    return $_POST[$key] ?? $default;
}

function get_query(string $key, $default = null)
{
    return $_GET[$key] ?? $default;
}

function query_int(string $key, int $default = 0, ?int $min = null, ?int $max = null): int
{
    $value = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);
    if ($value === false || $value === null) {
        $value = $default;
    }
    if ($min !== null && $value < $min) {
        $value = $min;
    }
    if ($max !== null && $value > $max) {
        $value = $max;
    }
    return (int) $value;
}

function pick_sort(string $value, array $allowed, string $default): string
{
    return array_key_exists($value, $allowed) ? $value : $default;
}

function pick_direction(string $value, string $default = 'asc'): string
{
    $value = strtolower($value);
    return in_array($value, ['asc', 'desc'], true) ? $value : $default;
}

function flash_set(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = [
        'msg'  => $message,
        'type' => $type,
    ];
}

function flash_get(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function flash($message, $type = 'success'): void
{
    flash_set((string)$message, (string)$type);
}

function getFlash(): ?array
{
    return flash_get();
}

function render_flash(): string
{
    $flash = flash_get();
    if (!$flash) {
        return '';
    }
    $type = $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'error');
    $icon = function_exists('icon')
        ? icon($type === 'success' ? 'check' : ($type === 'warning' ? 'filter' : 'off'))
        : '';
    return '<div class="alert alert-' . e($type) . '">' . $icon . ' ' . e($flash['msg']) . '</div>';
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function now_db(): string
{
    return date('Y-m-d H:i:s');
}

function current_query(array $overrides = [], array $exclude = []): string
{
    $query = $_GET;
    foreach ($exclude as $key) {
        unset($query[$key]);
    }
    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($query[$key]);
        } else {
            $query[$key] = $value;
        }
    }
    $qs = http_build_query($query);
    return $qs ? ('?' . $qs) : '';
}

function paginate(int $total, int $page, int $perPage): array
{
    $perPage = max(1, $perPage);
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($page, $totalPages));
    return [
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => $totalPages,
        'offset' => ($page - 1) * $perPage,
        'from' => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
        'to' => min($total, $page * $perPage),
    ];
}

function render_pagination(array $pagination): string
{
    if (($pagination['total_pages'] ?? 1) <= 1) {
        return '';
    }

    $page = (int) $pagination['page'];
    $totalPages = (int) $pagination['total_pages'];
    $start = max(1, $page - 2);
    $end = min($totalPages, $page + 2);

    $html = '<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:12px 18px;border-top:1px solid var(--bdr)">';
    $html .= '<div style="font-size:12px;color:var(--t3)">Mostrando ' . (int)$pagination['from'] . '–' . (int)$pagination['to'] . ' de ' . (int)$pagination['total'] . '</div>';
    $html .= '<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">';

    if ($page > 1) {
        $html .= '<a class="btn btn-ghost btn-sm" href="' . e(current_query(['page' => $page - 1])) . '">Anterior</a>';
    }

    for ($p = $start; $p <= $end; $p++) {
        $active = $p === $page;
        $html .= '<a class="btn btn-sm ' . ($active ? 'btn-primary' : 'btn-ghost') . '" href="' . e(current_query(['page' => $p])) . '">' . $p . '</a>';
    }

    if ($page < $totalPages) {
        $html .= '<a class="btn btn-ghost btn-sm" href="' . e(current_query(['page' => $page + 1])) . '">Próxima</a>';
    }

    $html .= '</div></div>';
    return $html;
}


function export_excel_xml(string $filename, array $headers, array $rows): void
{
    if (ob_get_length()) {
        @ob_end_clean();
    }

    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    echo "\xEF\xBB\xBF";
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<?mso-application progid="Excel.Sheet"?>';
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" ';
    echo 'xmlns:o="urn:schemas-microsoft-com:office:office" ';
    echo 'xmlns:x="urn:schemas-microsoft-com:office:excel" ';
    echo 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
    echo '<Worksheet ss:Name="Dados"><Table>';

    echo '<Row>';
    foreach ($headers as $header) {
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars((string)$header, ENT_QUOTES, 'UTF-8') . '</Data></Cell>';
    }
    echo '</Row>';

    foreach ($rows as $row) {
        echo '<Row>';
        foreach ($row as $value) {
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '</Data></Cell>';
        }
        echo '</Row>';
    }

    echo '</Table></Worksheet></Workbook>';
    exit;
}
