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
<div class="alert alert-<?= htmlspecialchars($type) ?>" role="alert"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="row">
<div class="col-md-6">
<form method="POST">
<?= csrfField() ?>
<div class="mb-3">
<label class="form-label" for="contact-titre">Titre de la demande <span class="text-danger" aria-hidden="true">*</span></label>
<input type="text" name="titre" id="contact-titre" class="form-control" required value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>">
</div>
<div class="mb-3">
<label class="form-label" for="contact-nom">Nom <span class="text-danger" aria-hidden="true">*</span></label>
<input type="text" name="nom" id="contact-nom" class="form-control" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" autocomplete="name">
</div>
<div class="mb-3">
<label class="form-label" for="contact-email">Email <span class="text-danger" aria-hidden="true">*</span></label>
<input type="email" name="email" id="contact-email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autocomplete="email">
</div>
<div class="mb-3">
<label class="form-label" for="contact-description">Description <span class="text-danger" aria-hidden="true">*</span></label>
<textarea name="description" id="contact-description" class="form-control" rows="5" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
</div>
<button type="submit" class="btn btn-primary">Envoyer</button>
</form>
</div>

<div class="col-md-6">
<h2 class="h4">Informations</h2>
<p><span class="visually-hidden">Adresse : </span>11 Rue Verteuil, 33000 Bordeaux</p>
<p><span class="visually-hidden">Telephone : </span><a href="tel:+33412345678">04 12 34 56 78</a></p>
<p><span class="visually-hidden">Email : </span><a href="mailto:contact@vite-gourmand.fr">contact@vite-gourmand.fr</a></p>
</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
