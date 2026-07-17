<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';

$message = '';
$type = '';
$redirect = sanitizeInternalRedirect($_GET['redirect'] ?? '', '');
$isOrderFlow = $redirect !== '' && (strpos($redirect, 'menu.php') !== false || strpos($redirect, 'commande.php') !== false);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { die('Token CSRF invalide.'); }
    $redirect = sanitizeInternalRedirect($_POST['redirect'] ?? $redirect, '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gsm = trim($_POST['gsm'] ?? '');
    $rue = trim($_POST['rue'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $complement = trim($_POST['complement'] ?? '');
    $codePostal = trim($_POST['code_postal'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $password = $_POST['password'] ?? '';

    $adresse = trim("$numero $rue, $codePostal $ville" . ($complement ? " ($complement)" : ''));

    $pwdError = validatePassword($password);
    if ($pwdError) {
        $message = $pwdError;
        $type = 'danger';
    } elseif (!$nom || !$prenom || !$email || !$gsm || !$rue || !$numero || !$codePostal || !$ville) {
        $message = 'Veuillez remplir tous les champs obligatoires.';
        $type = 'danger';
    } else {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $message = 'Cet email est deja utilise.';
            $type = 'danger';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('
                INSERT INTO users (nom, prenom, email, gsm, telephone, rue, numero, complement, code_postal, ville, adresse, password, role)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            if ($stmt->execute([$nom, $prenom, $email, $gsm, $gsm, $rue, $numero, $complement, $codePostal, $ville, $adresse, $hash, 'utilisateur'])) {
                $userId = (int)$pdo->lastInsertId();
                sendMail($email, 'Bienvenue chez Vite & Gourmand', "Bonjour $prenom $nom,\n\nVotre compte a ete cree avec succes.\n\nA bientot sur Vite & Gourmand !");
                secureSessionLogin($userId, 'utilisateur', ['user_nom' => $nom, 'user_prenom' => $prenom]);
                if ($redirect !== '') {
                    header('Location: ' . $redirect);
                    exit();
                }
                $message = 'Compte cree avec succes. Un email de bienvenue vous a ete envoye.';
                $type = 'success';
            } else {
                $message = 'Erreur lors de la creation du compte.';
                $type = 'danger';
            }
        }
    }
}

$pageNoIndex = true;
include 'includes/header.php';
?>

<div class="container mt-5">
<h1 class="h2">Creer un compte</h1>
<p class="text-muted" id="register-help">Mot de passe : 10 caracteres min., majuscule, minuscule, chiffre et caractere special.</p>

<?php if ($isOrderFlow): ?>
<div class="alert alert-info" role="status">
    Apres la creation de votre compte, vous serez redirige vers votre menu pour finaliser la commande.
    Deja inscrit ? <a href="login.php?redirect=<?= urlencode($redirect) ?>" class="alert-link">Se connecter</a>.
</div>
<?php endif; ?>

<?php if ($message): ?>
<div class="alert alert-<?= htmlspecialchars($type) ?>" role="alert"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST" aria-describedby="register-help">
<?= csrfField() ?>
<?php if ($redirect !== ''): ?>
<input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
<?php endif; ?>
<div class="mb-3"><label class="form-label" for="reg-nom">Nom <span class="text-danger" aria-hidden="true">*</span></label><input type="text" name="nom" id="reg-nom" class="form-control" required autocomplete="family-name"></div>
<div class="mb-3"><label class="form-label" for="reg-prenom">Prenom <span class="text-danger" aria-hidden="true">*</span></label><input type="text" name="prenom" id="reg-prenom" class="form-control" required autocomplete="given-name"></div>
<div class="mb-3"><label class="form-label" for="reg-email">Email <span class="text-danger" aria-hidden="true">*</span></label><input type="email" name="email" id="reg-email" class="form-control" required autocomplete="email"></div>
<div class="mb-3"><label class="form-label" for="reg-gsm">Telephone <span class="text-danger" aria-hidden="true">*</span></label><input type="tel" name="gsm" id="reg-gsm" class="form-control" required autocomplete="tel"></div>
<div class="row g-2">
    <div class="col-md-8 mb-3">
        <label class="form-label" for="reg-rue">Rue <span class="text-danger" aria-hidden="true">*</span></label>
        <input type="text" name="rue" id="reg-rue" class="form-control" required autocomplete="street-address">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label" for="reg-numero">Numero <span class="text-danger" aria-hidden="true">*</span></label>
        <input type="text" name="numero" id="reg-numero" class="form-control" required>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label" for="reg-complement">Complement (optionnel)</label>
        <input type="text" name="complement" id="reg-complement" class="form-control" autocomplete="address-line2">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label" for="reg-cp">Code postal <span class="text-danger" aria-hidden="true">*</span></label>
        <input type="text" name="code_postal" id="reg-cp" class="form-control" required autocomplete="postal-code">
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label" for="reg-ville">Ville <span class="text-danger" aria-hidden="true">*</span></label>
        <input type="text" name="ville" id="reg-ville" class="form-control" required autocomplete="address-level2">
    </div>
</div>
<div class="mb-3"><label class="form-label" for="reg-password">Mot de passe <span class="text-danger" aria-hidden="true">*</span></label><input type="password" name="password" id="reg-password" class="form-control" required minlength="10" autocomplete="new-password" aria-describedby="register-help"><?= renderPasswordToggle('reg-password') ?></div>
<button type="submit" class="btn btn-primary">Creer le compte</button>
</form>

<p class="mt-3">Deja inscrit ? <a href="login.php<?= $redirect !== '' ? '?redirect=' . urlencode($redirect) : '' ?>">Se connecter</a></p>
</div>

<?php include 'includes/footer.php'; ?>
