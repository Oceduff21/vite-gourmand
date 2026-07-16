<?php
session_start();
require '../includes/db.php';
require_once '../includes/helpers.php';
sendSecurityHeaders();

$pageTitle = 'Connexion Admin';

if (isset($_SESSION['user_id']) && in_array($_SESSION['user_role'] ?? '', ['admin', 'employe'], true)) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Session expiree. Rechargez la page.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if (!in_array($user['role'] ?? '', ['admin', 'employe'], true)) {
                $error = 'Acces reserve au personnel.';
            } elseif (isset($user['is_active']) && !$user['is_active']) {
                $error = 'Compte desactive.';
            } elseif (isset($user['actif']) && !$user['actif']) {
                $error = 'Compte desactive.';
            } else {
                secureSessionLogin((int)$user['id'], $user['role'], [
                    'user_prenom' => $user['prenom'] ?? '',
                    'user_nom' => $user['nom'] ?? '',
                ]);
                header('Location: index.php');
                exit();
            }
        } else {
            $error = 'Identifiants incorrects.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= htmlspecialchars($pageTitle) ?> — Vite & Gourmand</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="icon" href="../assets/images/favicon.svg" type="image/svg+xml">
<style>
body{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',sans-serif}
.admin-login-card{width:100%;max-width:400px;background:#fff;border-radius:16px;padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.admin-login-brand{text-align:center;margin-bottom:1.5rem}
.admin-login-brand i{font-size:2rem;color:#c9a227}
input:focus-visible,button:focus-visible,a:focus-visible{outline:3px solid #6366f1;outline-offset:2px}
</style>
</head>
<body>
<a href="#admin-login-form" class="visually-hidden-focusable position-absolute m-2 btn btn-sm btn-light">Aller au formulaire</a>
<div class="admin-login-card">
    <div class="admin-login-brand">
        <i class="fa-solid fa-utensils" aria-hidden="true"></i>
        <h1 class="h4 mt-2 mb-1">Espace personnel</h1>
        <p class="text-muted small mb-0">Administration &amp; employes — Vite &amp; Gourmand</p>
    </div>
    <?php if ($error): ?>
    <div class="alert alert-danger py-2" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" id="admin-login-form">
        <?= csrfField() ?>
        <div class="mb-3">
            <label class="form-label small fw-semibold" for="admin-email">Email</label>
            <input type="email" name="email" id="admin-email" class="form-control" placeholder="admin@..." required autofocus autocomplete="username">
        </div>
        <div class="mb-3">
            <label class="form-label small fw-semibold" for="admin-password">Mot de passe</label>
            <input type="password" name="password" id="admin-password" class="form-control" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary w-100">Se connecter</button>
    </form>
    <p class="text-center mt-3 mb-0"><a href="../index.php" class="small text-muted">&larr; Retour au site</a></p>
</div>
</body>
</html>
