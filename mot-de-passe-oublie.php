<?php
require 'includes/db.php';
require 'includes/helpers.php';

$message = '';
$type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $message = 'Session expiree. Rechargez la page.';
        $type = 'danger';
    } else {
    $email = trim($_POST['email'] ?? '');
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $pdo->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);
        $pdo->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)')->execute([$email, $token, $expires]);
        $link = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/reinitialiser-mot-de-passe.php?token=' . $token;
        sendMail($email, 'Reinitialisation mot de passe', "Bonjour,\n\nCliquez sur ce lien pour reinitialiser votre mot de passe :\n$link\n\nCe lien expire dans 1 heure.");
    }
    $message = 'Si cet email existe, un lien de reinitialisation a ete envoye.';
    $type = 'info';
    }
}

include 'includes/header.php';
?>
<div class="container py-5">
<h2>Mot de passe oublie</h2>
<?php if ($message): ?><div class="alert alert-<?= $type ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<form method="POST" class="col-md-6">
<?= csrfField() ?>
<label class="form-label">Votre email</label>
<input type="email" name="email" class="form-control mb-3" required>
<button class="btn btn-primary">Envoyer le lien</button>
</form>
<p class="mt-3"><a href="login.php">Retour connexion</a></p>
</div>
<?php include 'includes/footer.php'; ?>
