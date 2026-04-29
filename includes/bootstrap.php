<?php

require_once __DIR__ . '/security.php';
app_boot_session();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/modules.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/audit.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/email.php';
require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/distribuicao.php';

require_once __DIR__ . '/impressao_financeiro.php';
require_once __DIR__ . '/chamados.php';
