<?php
require __DIR__ . '/partials/auth.php';
requireAdminAccess();

require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    http_response_code(405);
    die('Action non autorisee.');
}

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    $pdo->prepare('UPDATE avis SET is_validated = 1 WHERE id = ?')->execute([$id]);
}

header('Location: admin-avis.php');
exit();
