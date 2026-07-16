<?php
session_start();
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit();
}

require '../includes/db.php';

if (isset($_POST['create'])) {
    $nom = trim($_POST['nom'] ?? 'Employe');
    $prenom = trim($_POST['prenom'] ?? 'Nouveau');
    $email = trim($_POST['email'] ?? '');
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (nom, prenom, email, password, role, is_active)
        VALUES (?, ?, ?, ?, 'employe', 1)
    ");
    $stmt->execute([$nom, $prenom, $email, $password]);

    @mail($email, 'Compte cree', "Un compte employe a ete cree pour vous.\nContactez l'admin pour votre mot de passe.");
}

if (isset($_GET['disable'])) {
    $pdo->prepare('UPDATE users SET is_active = 0 WHERE id = ?')->execute([(int)$_GET['disable']]);
}

if (isset($_GET['enable'])) {
    $pdo->prepare('UPDATE users SET is_active = 1 WHERE id = ?')->execute([(int)$_GET['enable']]);
}

$users = $pdo->query('SELECT * FROM users')->fetchAll();

require __DIR__ . '/partials/layout.php';
?>

<h2>Gestion des utilisateurs</h2>

<div class="card-custom mb-4">
<form method="POST">
<h5>Creer un employe</h5>
<div class="row g-2">
<div class="col-md-3"><input type="text" name="nom" class="form-control" placeholder="Nom" required></div>
<div class="col-md-3"><input type="text" name="prenom" class="form-control" placeholder="Prenom" required></div>
<div class="col-md-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
<div class="col-md-3"><input type="password" name="password" class="form-control" placeholder="Mot de passe" required></div>
</div>
<button name="create" class="btn btn-success mt-2">Creer</button>
</form>
</div>

<div class="card-custom">
<table class="table">
<tr><th>Email</th><th>Role</th><th>Statut</th><th>Action</th></tr>
<?php foreach ($users as $u): ?>
<tr>
<td><?= htmlspecialchars($u['email']) ?></td>
<td><?= htmlspecialchars($u['role']) ?></td>
<td><?= !empty($u['is_active']) ? 'Actif' : 'Desactive' ?></td>
<td>
<?php if ($u['role'] !== 'admin'): ?>
<?php if (!empty($u['is_active'])): ?>
<a href="?disable=<?= (int)$u['id'] ?>" class="btn btn-danger btn-sm">Desactiver</a>
<?php else: ?>
<a href="?enable=<?= (int)$u['id'] ?>" class="btn btn-success btn-sm">Activer</a>
<?php endif; ?>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
