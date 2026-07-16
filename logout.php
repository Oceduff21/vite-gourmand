<?php
session_start();

$wasAdmin = in_array($_SESSION['user_role'] ?? '', ['admin', 'employe'], true);

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

header('Location: ' . ($wasAdmin ? 'admin/login.php' : 'login.php'));
exit();
