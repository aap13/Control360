<?php
function app_log_error(string $message): void
{
    $dir = __DIR__ . '/../logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    @file_put_contents($dir . '/app.log', $line, FILE_APPEND);
}

set_exception_handler(function (Throwable $e): void {
    app_log_error('EXCEPTION ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    exit('Ocorreu um erro interno ao processar a solicitação.');
});

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    app_log_error('ERROR ' . $message . ' in ' . $file . ':' . $line);
    return false;
});
