<?php
session_start();
if(($_SESSION['user_role'] ?? '') !== 'admin'){ header('Location: index.php'); exit(); }
require __DIR__ . '/partials/layout.php';
require '../includes/db.php';

/* AJOUT EMPLOYE */
if(isset($_POST["create"])){

    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
    INSERT INTO users (email, password, role, is_active)
    VALUES (?, ?, 'employe', 1)
    ");

    $stmt->execute([$email, $password]);

    /* EMAIL */
    mail($email, "Compte créé",
    "Un compte employé a été créé pour vous.\nContactez l'admin pour votre mot de passe.");

}

/* DESACTIVER */
if(isset($_GET["disable"])){
    $pdo->prepare("UPDATE users SET is_active = 0 WHERE id=?")
        ->execute([$_GET["disable"]]);
}

/* ACTIVER */
if(isset($_GET["enable"])){
    $pdo->prepare("UPDATE users SET is_active = 1 WHERE id=?")
        ->execute([$_GET["enable"]]);
}

$users = $pdo->query("SELECT * FROM users")->fetchAll();
?>

<h2>Gestion des utilisateurs</h2>

<div class="card-custom mb-4">
<form method="POST">

<h5>Créer un employé</h5>

<input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
<input type="password" name="password" class="form-control mb-2" placeholder="Mot de passe" required>

<button name="create" class="btn btn-success">Créer</button>

</form>
</div>

<div class="card-custom">
<table class="table">

<tr>
<th>Email</th>
<th>Role</th>
<th>Statut</th>
<th>Action</th>
</tr>

<?php foreach($users as $u): ?>

<tr>
<td><?= $u["email"] ?></td>
<td><?= $u["role"] ?></td>
<td>
<?= isset($u["is_active"]) && $u["is_active"] ? "Actif" : "Désactivé" ?></td>

<td>

<?php if($u["role"] !== "admin"): ?>

<?php if(isset($u["is_active"]) && $u["is_active"]): ?>
<a href="?disable=<?= $u["id"] ?>" class="btn btn-danger btn-sm">Désactiver</a>
<?php else: ?>
<a href="?enable=<?= $u["id"] ?>" class="btn btn-success btn-sm">Activer</a>
<?php endif; ?>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</table>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>