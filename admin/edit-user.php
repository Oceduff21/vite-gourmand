<?php
require __DIR__ . '/partials/auth.php';
requireAdminAccess(true);

require '../includes/db.php';

$userId = (int)($_GET['id'] ?? 0);
if ($userId <= 0) {
    header('Location: admin-users.php');
    exit();
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['admin_users_flash'] = 'Utilisateur introuvable.';
    header('Location: admin-users.php');
    exit();
}

$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$isAdminAccount = ($user['role'] ?? '') === 'admin';
$isSelf = $userId === $currentUserId;

if ($isAdminAccount && !$isSelf) {
    $_SESSION['admin_users_flash'] = 'Les comptes administrateur ne peuvent pas etre modifies.';
    header('Location: admin-users.php');
    exit();
}

$pageTitle = 'Modifier le compte';
$error = '';
$returnTab = in_array($_GET['tab'] ?? '', ['employes', 'clients', 'tous'], true) ? $_GET['tab'] : (($user['role'] ?? '') === 'employe' ? 'employes' : 'clients');

$ordersCount = 0;
if (($user['role'] ?? '') === 'utilisateur') {
    $oc = $pdo->prepare('SELECT COUNT(*) FROM commandes WHERE user_id = ?');
    $oc->execute([$userId]);
    $ordersCount = (int)$oc->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        die('Token CSRF invalide.');
    }

    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gsm = trim($_POST['gsm'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $role = $_POST['role'] ?? $user['role'];

    if ($nom === '' || $prenom === '') {
        $error = 'Le nom et le prenom sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        $emailCheck = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
        $emailCheck->execute([$email, $userId]);
        if ($emailCheck->fetch()) {
            $error = 'Cet email est deja utilise par un autre compte.';
        } else {
            if ($newPassword !== '') {
                $pwdError = validatePassword($newPassword);
                if ($pwdError) {
                    $error = $pwdError;
                }
            }

            if (!$error && $isAdminAccount) {
                $role = 'admin';
                $isActive = 1;
            } elseif (!$error && !in_array($role, ['employe', 'utilisateur'], true)) {
                $error = 'Role invalide.';
            }

            if (!$error) {
                if ($newPassword !== '') {
                    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $update = $pdo->prepare('
                        UPDATE users SET nom = ?, prenom = ?, email = ?, gsm = ?, telephone = ?,
                        role = ?, is_active = ?, password = ?
                        WHERE id = ?
                    ');
                    $update->execute([$nom, $prenom, $email, $gsm ?: null, $telephone ?: null, $role, $isActive, $hash, $userId]);
                } else {
                    $update = $pdo->prepare('
                        UPDATE users SET nom = ?, prenom = ?, email = ?, gsm = ?, telephone = ?,
                        role = ?, is_active = ?
                        WHERE id = ?
                    ');
                    $update->execute([$nom, $prenom, $email, $gsm ?: null, $telephone ?: null, $role, $isActive, $userId]);
                }

                $_SESSION['admin_users_flash'] = 'Compte mis a jour avec succes.';
                header('Location: admin-users.php?tab=' . urlencode($returnTab));
                exit();
            }
        }
    }
} else {
    $_POST = $user;
    $_POST['is_active'] = !empty($user['is_active']);
}

require __DIR__ . '/partials/layout.php';
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h2 mb-1">Modifier le compte</h1>
        <p class="text-muted mb-0">
            <?= htmlspecialchars(trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''))) ?>
            — <?= htmlspecialchars($user['email']) ?>
        </p>
    </div>
    <a href="admin-users.php?tab=<?= urlencode($returnTab) ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Retour
    </a>
</div>

<?php if ($error): ?>
<div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-custom">
            <form method="POST" class="row g-3">
                <?= csrfField() ?>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold" for="edit-user-nom">Nom</label>
                    <input type="text" name="nom" id="edit-user-nom" class="form-control" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required autocomplete="family-name">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold" for="edit-user-prenom">Prenom</label>
                    <input type="text" name="prenom" id="edit-user-prenom" class="form-control" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required autocomplete="given-name">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold" for="edit-user-email">Email</label>
                    <input type="email" name="email" id="edit-user-email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autocomplete="email">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold" for="edit-user-telephone">Telephone</label>
                    <input type="text" name="telephone" id="edit-user-telephone" class="form-control" value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>" autocomplete="tel">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold" for="edit-user-gsm">Mobile</label>
                    <input type="text" name="gsm" id="edit-user-gsm" class="form-control" value="<?= htmlspecialchars($_POST['gsm'] ?? '') ?>" autocomplete="tel">
                </div>

                <?php if (!$isAdminAccount): ?>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold" for="edit-user-role">Type de compte</label>
                    <select name="role" id="edit-user-role" class="form-select">
                        <option value="employe" <?= ($_POST['role'] ?? '') === 'employe' ? 'selected' : '' ?>>Employe (acces back-office)</option>
                        <option value="utilisateur" <?= ($_POST['role'] ?? '') === 'utilisateur' ? 'selected' : '' ?>>Client (site public)</option>
                    </select>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
                            <?= !empty($_POST['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Compte actif (peut se connecter)</label>
                    </div>
                </div>
                <?php endif; ?>

                <div class="col-12">
                    <label class="form-label small fw-semibold" for="edit-user-new-password">Nouveau mot de passe</label>
                    <input type="password" name="new_password" id="edit-user-new-password" class="form-control" autocomplete="new-password" minlength="10"
                        placeholder="Laisser vide pour conserver l'actuel">
                    <?= renderPasswordToggle('edit-user-new-password') ?>
                    <div class="form-text">10 caracteres min., majuscule, minuscule, chiffre et caractere special.</div>
                </div>

                <div class="col-12 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Enregistrer
                    </button>
                    <a href="admin-users.php?tab=<?= urlencode($returnTab) ?>" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-custom mb-4">
            <h6 class="text-muted mb-3">Informations</h6>
            <p class="mb-2"><strong>ID :</strong> #<?= (int)$user['id'] ?></p>
            <p class="mb-2"><strong>Role actuel :</strong>
                <?php if ($isAdminAccount): ?>
                <span class="badge bg-primary">Administrateur</span>
                <?php elseif (($user['role'] ?? '') === 'employe'): ?>
                <span class="badge bg-info">Employe</span>
                <?php else: ?>
                <span class="badge bg-secondary">Client</span>
                <?php endif; ?>
            </p>
            <p class="mb-2"><strong>Inscription :</strong>
                <?= !empty($user['created_at']) ? date('d/m/Y H:i', strtotime($user['created_at'])) : '—' ?>
            </p>
            <?php if (($user['role'] ?? '') === 'utilisateur'): ?>
            <p class="mb-0"><strong>Commandes :</strong> <?= $ordersCount ?></p>
            <?php endif; ?>
        </div>

        <?php if (!$isAdminAccount && !$isSelf): ?>
        <div class="card-custom border-warning">
            <h6 class="text-warning mb-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> Acces back-office</h6>
            <p class="small text-muted mb-0">
                Un compte <strong>Employe</strong> se connecte via <code>/admin/login.php</code>.
                Un compte <strong>Client</strong> utilise le site public uniquement.
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
