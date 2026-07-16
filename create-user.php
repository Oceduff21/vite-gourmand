<?php
session_start();
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: login.php?redirect=' . urlencode('admin/admin-users.php'));
    exit();
}
header('Location: admin/admin-users.php');
exit();
