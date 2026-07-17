<?php
require __DIR__ . '/partials/auth.php';
requireAdminAccess(true);

require '../includes/db.php';

$pageTitle = 'Utilisateurs';
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$tab = $_GET['tab'] ?? 'employes';
if (!in_array($tab, ['employes', 'clients', 'tous'], true)) {
    $tab = 'employes';
}

$error = '';
$success = '';

if (!empty($_SESSION['admin_users_flash'])) {
    $success = $_SESSION['admin_users_flash'];
    unset($_SESSION['admin_users_flash']);
}

function usersRedirect(string $tab, array $query = []): void
{
    $query['tab'] = $tab;
    header('Location: admin-users.php?' . http_build_query($query));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        die('Token CSRF invalide.');
    }

    $postTab = $_POST['tab'] ?? $tab;

    if (isset($_POST['create'])) {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $gsm = trim($_POST['gsm'] ?? '');
        $plainPassword = $_POST['password'] ?? '';

        if ($nom === '' || $prenom === '') {
            $error = 'Le nom et le prenom sont obligatoires.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Adresse email invalide.';
        } else {
            $check = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $check->execute([$email]);
            if ($check->fetch()) {
                $error = 'Cet email est deja utilise.';
            } else {
                $pwdError = validatePassword($plainPassword);
                if ($pwdError) {
                    $error = $pwdError;
                } else {
                    $password = password_hash($plainPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('
                        INSERT INTO users (nom, prenom, email, gsm, telephone, password, role, is_active)
                        VALUES (?, ?, ?, ?, ?, ?, \'employe\', 1)
                    ');
                    $stmt->execute([$nom, $prenom, $email, $gsm ?: null, $gsm ?: null, $password]);
                    sendEmployeWelcomeEmail($email, $prenom);
                    $_SESSION['admin_users_flash'] = "Employe {$prenom} {$nom} cree. Un email de notification a ete envoye (sans mot de passe — communiquez-le lui en personne).";
                    usersRedirect('employes');
                }
            }
        }
        $tab = 'employes';
    } elseif (isset($_POST['toggle_active'])) {
        $userId = (int)($_POST['user_id'] ?? 0);
        $active = (int)($_POST['active'] ?? 0);

        if ($userId > 0 && $userId !== $currentUserId) {
            $stmt = $pdo->prepare('UPDATE users SET is_active = ? WHERE id = ? AND role <> \'admin\'');
            $stmt->execute([$active, $userId]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['admin_users_flash'] = $active ? 'Compte reactive.' : 'Compte desactive.';
            }
        }
        usersRedirect($postTab);
    } elseif (isset($_POST['promote_employe'])) {
        $userId = (int)($_POST['user_id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE users SET role = 'employe', is_active = 1 WHERE id = ? AND role = 'utilisateur'");
        $stmt->execute([$userId]);
        if ($stmt->rowCount() > 0) {
            $u = $pdo->prepare('SELECT email, prenom FROM users WHERE id = ?');
            $u->execute([$userId]);
            if ($row = $u->fetch(PDO::FETCH_ASSOC)) {
                sendEmployeWelcomeEmail($row['email'], $row['prenom']);
            }
            $_SESSION['admin_users_flash'] = 'Client promu en employe. Email de notification envoye (mot de passe : celui du compte client ou a redefinir).';
        }
        usersRedirect('clients');
    } elseif (isset($_POST['demote_client'])) {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId !== $currentUserId) {
            $stmt = $pdo->prepare("UPDATE users SET role = 'utilisateur' WHERE id = ? AND role = 'employe'");
            $stmt->execute([$userId]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['admin_users_flash'] = 'Employe repasse en compte client.';
            }
        }
        usersRedirect('employes');
    }
}

$filterStatus = $_GET['status'] ?? '';
$filterSearch = trim($_GET['q'] ?? '');

$sql = 'SELECT u.*, (SELECT COUNT(*) FROM commandes c WHERE c.user_id = u.id) AS nb_commandes FROM users u WHERE 1=1';
$params = [];

if ($tab === 'employes') {
    $sql .= " AND u.role = 'employe'";
} elseif ($tab === 'clients') {
    $sql .= " AND u.role = 'utilisateur'";
}

if ($filterStatus === 'active') {
    $sql .= ' AND u.is_active = 1';
} elseif ($filterStatus === 'inactive') {
    $sql .= ' AND (u.is_active = 0 OR u.is_active IS NULL)';
}
if ($filterSearch !== '') {
    $sql .= ' AND (u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ?)';
    $like = '%' . $filterSearch . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY FIELD(u.role, 'admin', 'employe', 'utilisateur'), u.nom, u.prenom";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stats = [
    'employes' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employe'")->fetchColumn(),
    'employes_actifs' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employe' AND is_active = 1")->fetchColumn(),
    'clients' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'utilisateur'")->fetchColumn(),
    'clients_actifs' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'utilisateur' AND is_active = 1")->fetchColumn(),
    'admins' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(),
];

function userRoleBadgeClass(string $role): string
{
    return match ($role) {
        'admin' => 'primary',
        'employe' => 'info',
        default => 'secondary',
    };
}

function userRoleLabel(string $role): string
{
    return match ($role) {
        'admin' => 'Administrateur',
        'employe' => 'Employe',
        default => 'Client',
    };
}

$queryBase = static function (array $extra = []) use ($tab, $filterStatus, $filterSearch): string {
    $q = array_merge([
        'tab' => $tab,
        'status' => $filterStatus,
        'q' => $filterSearch,
    ], $extra);
    $q = array_filter($q, static fn($v) => $v !== '' && $v !== null);
    return 'admin-users.php?' . http_build_query($q);
};

require __DIR__ . '/partials/layout.php';
?>

<h1 class="h2 mb-1">Gestion des utilisateurs</h1>
<p class="text-muted mb-4">Employes du back-office et comptes clients du site public.</p>

<?php if ($success): ?>
<div class="alert alert-success py-2"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'employes' ? 'active' : '' ?>" href="<?= $queryBase(['tab' => 'employes', 'status' => '', 'q' => '']) ?>">
            <i class="fa-solid fa-user-tie me-1"></i> Employes
            <span class="badge bg-light text-dark ms-1"><?= $stats['employes'] ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'clients' ? 'active' : '' ?>" href="<?= $queryBase(['tab' => 'clients', 'status' => '', 'q' => '']) ?>">
            <i class="fa-solid fa-user me-1"></i> Comptes clients
            <span class="badge bg-light text-dark ms-1"><?= $stats['clients'] ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'tous' ? 'active' : '' ?>" href="<?= $queryBase(['tab' => 'tous', 'status' => '', 'q' => '']) ?>">
            Tous les comptes
        </a>
    </li>
</ul>

<div class="row g-3 mb-4">
    <?php if ($tab === 'employes'): ?>
    <div class="col-md-4">
        <div class="card-custom text-center py-3">
            <div class="text-muted small">Employes</div>
            <strong class="fs-4 text-info"><?= $stats['employes'] ?></strong>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-custom text-center py-3">
            <div class="text-muted small">Employes actifs</div>
            <strong class="fs-4 text-success"><?= $stats['employes_actifs'] ?></strong>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-custom text-center py-3">
            <div class="text-muted small">Administrateurs</div>
            <strong class="fs-4 text-primary"><?= $stats['admins'] ?></strong>
        </div>
    </div>
    <?php elseif ($tab === 'clients'): ?>
    <div class="col-md-6">
        <div class="card-custom text-center py-3">
            <div class="text-muted small">Clients inscrits</div>
            <strong class="fs-4"><?= $stats['clients'] ?></strong>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-custom text-center py-3">
            <div class="text-muted small">Clients actifs</div>
            <strong class="fs-4 text-success"><?= $stats['clients_actifs'] ?></strong>
        </div>
    </div>
    <?php else: ?>
    <div class="col-md-3">
        <div class="card-custom text-center py-3">
            <div class="text-muted small">Employes</div>
            <strong class="fs-4 text-info"><?= $stats['employes'] ?></strong>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom text-center py-3">
            <div class="text-muted small">Clients</div>
            <strong class="fs-4"><?= $stats['clients'] ?></strong>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom text-center py-3">
            <div class="text-muted small">Admins</div>
            <strong class="fs-4 text-primary"><?= $stats['admins'] ?></strong>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom text-center py-3">
            <div class="text-muted small">Resultats</div>
            <strong class="fs-4"><?= count($users) ?></strong>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($tab === 'employes'): ?>
<div class="card-custom mb-4">
    <h5 class="mb-3"><i class="fa-solid fa-user-plus me-2 text-primary"></i>Creer un compte employe</h5>
    <p class="text-muted small mb-3">
        Seuls des comptes <strong>employe</strong> peuvent etre crees ici (pas d'administrateur depuis l'application).
        L'email sert d'identifiant. Le mot de passe est defini par vous et <strong>remis en personne</strong> a l'employe — il n'est jamais envoye par email.
    </p>
    <form method="POST" class="row g-3">
        <?= csrfField() ?>
        <input type="hidden" name="tab" value="employes">
        <div class="col-md-3">
            <label class="form-label small fw-semibold" for="create-emp-nom">Nom</label>
            <input type="text" name="nom" id="create-emp-nom" class="form-control" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required autocomplete="family-name">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold" for="create-emp-prenom">Prenom</label>
            <input type="text" name="prenom" id="create-emp-prenom" class="form-control" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required autocomplete="given-name">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold" for="create-emp-email">Email</label>
            <input type="email" name="email" id="create-emp-email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autocomplete="email">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold" for="create-emp-gsm">Mobile</label>
            <input type="text" name="gsm" id="create-emp-gsm" class="form-control" value="<?= htmlspecialchars($_POST['gsm'] ?? '') ?>" autocomplete="tel">
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold" for="create-employee-password">Mot de passe initial</label>
            <input type="password" name="password" id="create-employee-password" class="form-control" required minlength="10" autocomplete="new-password">
            <?= renderPasswordToggle('create-employee-password') ?>
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <button name="create" value="1" class="btn btn-primary w-100">
                <i class="fa-solid fa-check me-1"></i> Creer le compte employe
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="card-custom mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
        <div class="col-md-4">
            <label class="form-label small fw-semibold" for="filtre-user-status">Statut</label>
            <select name="status" id="filtre-user-status" class="form-select">
                <option value="">Tous</option>
                <option value="active" <?= $filterStatus === 'active' ? 'selected' : '' ?>>Actifs</option>
                <option value="inactive" <?= $filterStatus === 'inactive' ? 'selected' : '' ?>>Desactives</option>
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label small fw-semibold" for="filtre-user-q">Recherche</label>
            <input type="text" name="q" id="filtre-user-q" class="form-control" placeholder="Nom, prenom, email..." value="<?= htmlspecialchars($filterSearch) ?>">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary flex-grow-1">Filtrer</button>
            <?php if ($filterStatus || $filterSearch): ?>
            <a href="admin-users.php?tab=<?= urlencode($tab) ?>" class="btn btn-outline-secondary">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card-custom">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h5 class="mb-0">
            <?php if ($tab === 'employes'): ?>
            <i class="fa-solid fa-user-tie me-2 text-primary"></i>Liste des employes
            <?php elseif ($tab === 'clients'): ?>
            <i class="fa-solid fa-users me-2 text-primary"></i>Liste des comptes clients
            <?php else: ?>
            <i class="fa-solid fa-list me-2 text-primary"></i>Tous les comptes
            <?php endif; ?>
        </h5>
        <span class="badge bg-light text-dark border"><?= count($users) ?> resultat(s)</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <caption class="visually-hidden">Liste des comptes utilisateurs</caption>
            <thead class="table-light">
                <tr>
                    <th scope="col">Utilisateur</th>
                    <th scope="col">Email / Contact</th>
                    <?php if ($tab === 'tous'): ?><th scope="col">Role</th><?php endif; ?>
                    <th scope="col">Statut</th>
                    <?php if ($tab === 'clients' || $tab === 'tous'): ?><th scope="col">Commandes</th><?php endif; ?>
                    <th scope="col">Inscription</th>
                    <th scope="col" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Aucun compte trouve.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                <?php
                    $isActive = !empty($u['is_active']);
                    $isSelf = (int)$u['id'] === $currentUserId;
                    $isAdminRow = ($u['role'] ?? '') === 'admin';
                    $canManage = !$isAdminRow || $isSelf;
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars(trim(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? ''))) ?></strong>
                        <?php if ($isSelf): ?><span class="badge bg-warning text-dark ms-1">Vous</span><?php endif; ?>
                    </td>
                    <td>
                        <div><?= htmlspecialchars($u['email']) ?></div>
                        <?php if (!empty($u['gsm']) || !empty($u['telephone'])): ?>
                        <small class="text-muted"><?= htmlspecialchars($u['gsm'] ?: $u['telephone']) ?></small>
                        <?php endif; ?>
                    </td>
                    <?php if ($tab === 'tous'): ?>
                    <td>
                        <span class="badge bg-<?= userRoleBadgeClass($u['role']) ?>">
                            <?= htmlspecialchars(userRoleLabel($u['role'])) ?>
                        </span>
                    </td>
                    <?php endif; ?>
                    <td>
                        <span class="badge bg-<?= $isActive ? 'success' : 'secondary' ?>">
                            <?= $isActive ? 'Actif' : 'Desactive' ?>
                        </span>
                    </td>
                    <?php if ($tab === 'clients' || $tab === 'tous'): ?>
                    <td><?= (int)($u['nb_commandes'] ?? 0) ?></td>
                    <?php endif; ?>
                    <td class="text-muted small">
                        <?= !empty($u['created_at']) ? date('d/m/Y', strtotime($u['created_at'])) : '—' ?>
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                            <?php if ($canManage): ?>
                            <a href="edit-user.php?id=<?= (int)$u['id'] ?>&tab=<?= urlencode($tab) ?>" class="btn btn-sm btn-primary">Modifier</a>
                            <?php endif; ?>

                            <?php if (!$isAdminRow && !$isSelf): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirm('<?= $isActive ? 'Desactiver' : 'Reactiver' ?> ce compte ?')">
                                <?= csrfField() ?>
                                <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
                                <input type="hidden" name="toggle_active" value="1">
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <input type="hidden" name="active" value="<?= $isActive ? '0' : '1' ?>">
                                <button type="submit" class="btn btn-sm btn-outline-<?= $isActive ? 'danger' : 'success' ?>">
                                    <?= $isActive ? 'Desactiver' : 'Reactiver' ?>
                                </button>
                            </form>
                            <?php endif; ?>

                            <?php if ($tab === 'clients' && ($u['role'] ?? '') === 'utilisateur'): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Promouvoir ce client en employe ?')">
                                <?= csrfField() ?>
                                <input type="hidden" name="tab" value="clients">
                                <input type="hidden" name="promote_employe" value="1">
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-info" title="Donner acces back-office">→ Employe</button>
                            </form>
                            <?php endif; ?>

                            <?php if ($tab === 'employes' && ($u['role'] ?? '') === 'employe' && !$isSelf): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Retirer l\'acces back-office a cet employe ?')">
                                <?= csrfField() ?>
                                <input type="hidden" name="tab" value="employes">
                                <input type="hidden" name="demote_client" value="1">
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Repasser en client">→ Client</button>
                            </form>
                            <?php endif; ?>

                            <?php if ($isAdminRow && !$isSelf): ?>
                            <span class="text-muted small align-self-center">Protege</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
