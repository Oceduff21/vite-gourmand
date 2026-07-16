<?php
require 'includes/db.php';
require 'includes/helpers.php';

$message = '';
$type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { die('Token CSRF invalide.'); }
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
                sendMail($email, 'Bienvenue chez Vite & Gourmand', "Bonjour $prenom $nom,\n\nVotre compte a ete cree avec succes.\n\nA bientot sur Vite & Gourmand !");
                $message = 'Compte cree avec succes. Un email de bienvenue vous a ete envoye.';
                $type = 'success';
            } else {
                $message = 'Erreur lors de la creation du compte.';
                $type = 'danger';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
<h2>Creer un compte</h2>
<p class="text-muted">Mot de passe : 10 caracteres min., majuscule, minuscule, chiffre et caractere special.</p>

<?php if ($message): ?>
<div class="alert alert-<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST">
<?= csrfField() ?>
<div class="mb-3"><label class="form-label">Nom</label><input type="text" name="nom" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Prenom</label><input type="text" name="prenom" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
<div class="mb-3"><label class="form-label">GSM</label><input type="tel" name="gsm" class="form-control" required></div>
<div class="row g-2">
    <div class="col-md-8 mb-3">
        <label class="form-label">Rue</label>
        <input type="text" name="rue" class="form-control" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">N°</label>
        <input type="text" name="numero" class="form-control" required>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Complement (optionnel)</label>
        <input type="text" name="complement" class="form-control">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Code postal</label>
        <input type="text" name="code_postal" class="form-control" required>
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label">Ville</label>
        <input type="text" name="ville" class="form-control" required>
    </div>
</div>
<div class="mb-3"><label class="form-label">Mot de passe</label><input type="password" name="password" class="form-control" required minlength="10"></div>
<button class="btn btn-primary">Creer le compte</button>
</form>
</div>

<?php include 'includes/footer.php'; ?>
