<?php
require 'includes/db.php';
require 'includes/helpers.php';

$message = '';
$type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { die('Token CSRF invalide.'); }
    $titre = trim($_POST['titre'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!$titre || !$nom || !$email || !$description) {
        $message = 'Tous les champs sont obligatoires.';
        $type = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Adresse email invalide.';
        $type = 'danger';
    } else {
        $corps = "Nouvelle demande de contact\n\nTitre : $titre\nNom : $nom\nEmail : $email\n\nMessage :\n$description";
        if (sendMail('contact@vite-gourmand.fr', '[Contact] ' . $titre, $corps)) {
            $message = 'Votre message a ete envoye. Nous vous repondrons rapidement.';
            $type = 'success';
        } else {
            $message = 'Votre demande a ete enregistree. (Email simule en local)';
            $type = 'info';
        }
    }
}

include 'includes/header.php';
?>

<div class="container py-5">
<h1 class="mb-4">Contact</h1>

<?php if ($message): ?>
<div class="alert alert-<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="row">
<div class="col-md-6">
<form method="POST">
<?= csrfField() ?>
<div class="mb-3">
<label class="form-label">Titre de la demande</label>
<input type="text" name="titre" class="form-control" required value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>">
</div>
<div class="mb-3">
<label class="form-label">Nom</label>
<input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
</div>
<div class="mb-3">
<label class="form-label">Email</label>
<input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
</div>
<div class="mb-3">
<label class="form-label">Description</label>
<textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
</div>
<button type="submit" class="btn btn-primary">Envoyer</button>
</form>
</div>

<div class="col-md-6">
<h4>Informations</h4>
<p>📍 11 Rue Verteuil, 33000 Bordeaux</p>
<p>📞 04 12 34 56 78</p>
<p>📧 contact@vite-gourmand.fr</p>
</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
