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

                $redirect = $_GET['redirect'] ?? 'index.php';
                if (preg_match('#^(https?://|//)#i', $redirect) || strpos($redirect, '..') !== false) {
                    $redirect = 'index.php';
                }
                header('Location: ' . $redirect);
                exit();
            }
        } else {
            $message = 'Email ou mot de passe incorrect';
        }
    }
}

$pageNoIndex = true;
include 'includes/header.php';
?>

<div class="container mt-5">

<h2>Connexion</h2>

<?php if ($message): ?>
<div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST">
<?= csrfField() ?>

<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="mb-3">
<label>Mot de passe</label>
<input type="password" name="password" class="form-control" required>
</div>

<button class="btn btn-primary">Se connecter</button>

</form>

<p class="mt-3"><a href="mot-de-passe-oublie.php">Mot de passe oublie ?</a></p>

</div>

<?php include 'includes/footer.php'; ?>
