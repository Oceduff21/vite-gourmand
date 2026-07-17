<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';
sendSecurityHeaders();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $message = 'Session expiree. Rechargez la page.';
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $sql = 'SELECT * FROM users WHERE email = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $active = (int)($user['is_active'] ?? $user['actif'] ?? 1);
            if (!$active) {
                $message = 'Compte desactive';
            } else {
                secureSessionLogin((int)$user['id'], $user['role'], ['user_nom' => $user['nom']]);

                $redirect = sanitizeInternalRedirect($_GET['redirect'] ?? 'index.php');
                header('Location: ' . $redirect);
                exit();
            }
        } else {
            $message = 'Email ou mot de passe incorrect';
        }
    }
}

$pageNoIndex = true;
$redirect = sanitizeInternalRedirect($_GET['redirect'] ?? '', '');
$isOrderFlow = $redirect !== '' && (strpos($redirect, 'menu.php') !== false || strpos($redirect, 'commande.php') !== false);
include 'includes/header.php';
?>

<div class="container mt-5">

<h1 class="h2">Connexion</h1>

<?php if ($isOrderFlow): ?>
<div class="alert alert-info" role="status">
    Connectez-vous ou <a href="register.php?redirect=<?= urlencode($redirect) ?>" class="alert-link">creez un compte</a> pour finaliser votre commande. Votre selection de menu sera conservee.
</div>
<?php endif; ?>

<?php if ($message): ?>
<div class="alert alert-danger" role="alert"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST" aria-describedby="login-help">
<?= csrfField() ?>
<p id="login-help" class="visually-hidden">Connectez-vous avec votre email et mot de passe.</p>

<div class="mb-3">
<label class="form-label" for="login-email">Email <span class="text-danger" aria-hidden="true">*</span></label>
<input type="email" name="email" id="login-email" class="form-control" required autocomplete="email">
</div>

<div class="mb-3">
<label class="form-label" for="login-password">Mot de passe <span class="text-danger" aria-hidden="true">*</span></label>
<input type="password" name="password" id="login-password" class="form-control" required autocomplete="current-password">
</div>

<button type="submit" class="btn btn-primary">Se connecter</button>

</form>

<p class="mt-3">Pas encore de compte ? <a href="register.php<?= $redirect !== '' ? '?redirect=' . urlencode($redirect) : '' ?>">Creer un compte</a></p>
<p class="mt-2"><a href="mot-de-passe-oublie.php">Mot de passe oublie ?</a></p>

</div>

<?php include 'includes/footer.php'; ?>
