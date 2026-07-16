<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/helpers.php';

function requireAdminAccess(bool $adminOnly = false): void
{
    if (!isset($_SESSION['user_id']) || !isAdminUser()) {
        $redirect = 'login.php';
        if (!empty($_SERVER['REQUEST_URI'])) {
            $redirect .= '?redirect=' . urlencode($_SERVER['REQUEST_URI']);
        }
        header('Location: ' . $redirect);
        exit();
    }

    if ($adminOnly && !isSuperAdmin()) {
        http_response_code(403);
        die('Acces reserve aux administrateurs.');
    }
}

sendSecurityHeaders();
