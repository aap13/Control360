<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    audit_log('logout', 'usuarios', current_user_id(), ['usuario' => $_SESSION['usuario'] ?? null]);
}

logout_user();
