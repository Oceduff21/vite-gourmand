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
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $message = 'Session expiree. Rechargez la page.';
        $type = 'danger';
    } else {
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
}

include 'includes/header.php';
?>
<div class="container py-5">
<h1 class="h2">Reinitialiser le mot de passe</h1>
<?php if (!$valid): ?>
<div class="alert alert-danger" role="alert">Lien invalide ou expire.</div>
<?php else: ?>
<?php if ($message): ?><div class="alert alert-<?= $type ?>" role="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<form method="POST" class="col-md-6">
<?= csrfField() ?>
<label class="form-label" for="reset-password">Nouveau mot de passe</label>
<input type="password" name="password" id="reset-password" class="form-control" required minlength="10" autocomplete="new-password">
<?= renderPasswordToggle('reset-password') ?>
<button class="btn btn-primary mt-3">Enregistrer</button>
</form>
<?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
