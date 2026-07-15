<?php
require 'includes/db.php';
require 'includes/helpers.php';

$token = $_GET['token'] ?? '';
$message = '';
$type = '';
$valid = false;

if ($token) {
    $stmt = $pdo->prepare('SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()');
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    $valid = (bool)$row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    $password = $_POST['password'] ?? '';
    $pwdError = validatePassword($password);
    if ($pwdError) {
        $message = $pwdError;
        $type = 'danger';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE users SET password = ? WHERE email = ?')->execute([$hash, $row['email']]);
        $pdo->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$row['email']]);
        header('Location: login.php');
        exit();
    }
}

include 'includes/header.php';
?>
<div class="container py-5">
<h2>Reinitialiser le mot de passe</h2>
<?php if (!$valid): ?>
<div class="alert alert-danger">Lien invalide ou expire.</div>
<?php else: ?>
<?php if ($message): ?><div class="alert alert-<?= $type ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<form method="POST" class="col-md-6">
<label class="form-label">Nouveau mot de passe</label>
<input type="password" name="password" class="form-control mb-3" required minlength="10">
<button class="btn btn-primary">Enregistrer</button>
</form>
<?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
