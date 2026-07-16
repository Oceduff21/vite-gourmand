<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';
require 'includes/menu-helpers.php';
require 'includes/user-helpers.php';
require 'includes/flash.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_favori') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('Session expiree. Rechargez la page et reessayez.');
        header('Location: espace-utilisateur.php?tab=favoris');
        exit();
    }
    $menuId = (int)($_POST['menu_id'] ?? 0);
    if ($menuId > 0) {
        $result = toggleUserFavori($pdo, $user_id, $menuId);
        if ($result === null) {
            setFlash('Favoris indisponibles : importez migration-user-space.sql dans phpMyAdmin.');
        } elseif ($result) {
            setFlash('Menu ajoute a vos favoris.');
        } else {
            setFlash('Menu retire de vos favoris.');
        }
    }
    header('Location: espace-utilisateur.php?tab=favoris');
    exit();
}

$tab = $_GET['tab'] ?? 'dashboard';
$allowedTabs = ['dashboard', 'commandes', 'favoris', 'profil', 'notifications'];
if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'dashboard';
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$stats = getUserDashboardStats($pdo, $user_id);
$statuts = getStatutsCommande();

$stmt = $pdo->prepare('
    SELECT c.*, m.titre, m.theme
    FROM commandes c
    JOIN menus m ON c.menu_id = m.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
');
$stmt->execute([$user_id]);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$favoris = getUserFavoris($pdo, $user_id);
$notifications = getUserNotifications($pdo, $user_id, 30);
$unreadCount = countUnreadNotifications($pdo, $user_id);
$pendingReviews = getOrdersPendingReview($pdo, $user_id);

$pageTitle = 'Mon espace';
include 'includes/header.php';
?>

<div class="container py-5 user-space">

<?php showFlash(); ?>

<div class="row g-4">
    <div class="col-lg-3">
        <div class="user-sidebar card border-0 shadow-sm p-3">
            <div class="text-center mb-4 pb-3 border-bottom">
                <div class="user-avatar mx-auto mb-2">
                    <i class="fa-solid fa-user"></i>
                </div>
                <h5 class="mb-0"><?= htmlspecialchars(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?></h5>
                <small class="text-muted"><?= htmlspecialchars($user['email'] ?? '') ?></small>
            </div>
            <nav class="nav flex-column user-nav">
                <a class="nav-link <?= $tab === 'dashboard' ? 'active' : '' ?>" href="?tab=dashboard"><i class="fa-solid fa-gauge me-2"></i> Tableau de bord</a>
                <a class="nav-link <?= $tab === 'commandes' ? 'active' : '' ?>" href="?tab=commandes"><i class="fa-solid fa-receipt me-2"></i> Mes commandes</a>
                <a class="nav-link <?= $tab === 'favoris' ? 'active' : '' ?>" href="?tab=favoris"><i class="fa-solid fa-heart me-2"></i> Favoris <?= $stats['favoris'] ? '<span class="badge bg-danger">' . $stats['favoris'] . '</span>' : '' ?></a>
                <a class="nav-link <?= $tab === 'notifications' ? 'active' : '' ?>" href="?tab=notifications"><i class="fa-solid fa-bell me-2"></i> Notifications <?= $unreadCount ? '<span class="badge bg-warning text-dark">' . $unreadCount . '</span>' : '' ?></a>
                <a class="nav-link <?= $tab === 'profil' ? 'active' : '' ?>" href="?tab=profil"><i class="fa-solid fa-id-card me-2"></i> Mon profil</a>
            </nav>
            <hr>
            <a href="menus.php" class="btn btn-warning w-100 btn-sm">Commander un menu</a>
        </div>
    </div>

    <div class="col-lg-9">

        <?php if ($tab === 'dashboard'): ?>
        <h2 class="mb-4">Tableau de bord</h2>
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card p-3 text-center">
                    <div class="stat-number"><?= $stats['total_commandes'] ?></div>
                    <div class="small text-muted">Commandes</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card p-3 text-center">
                    <div class="stat-number"><?= $stats['en_cours'] ?></div>
                    <div class="small text-muted">En cours</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card p-3 text-center">
                    <div class="stat-number"><?= number_format($stats['total_depense'], 0) ?> &euro;</div>
                    <div class="small text-muted">Total depense</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card p-3 text-center">
                    <div class="stat-number"><?= $stats['favoris'] ?></div>
                    <div class="small text-muted">Favoris</div>
                </div>
            </div>
        </div>

        <?php if ($unreadCount > 0): ?>
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-bell me-2"></i> Vous avez <?= $unreadCount ?> notification(s) non lue(s).</span>
            <a href="?tab=notifications" class="btn btn-sm btn-outline-primary">Voir</a>
        </div>
        <?php endif; ?>

        <?php if (!empty($pendingReviews)): ?>
        <div class="alert alert-success d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="fa-solid fa-star me-2"></i> <?= count($pendingReviews) ?> commande(s) en attente de votre avis.</span>
            <a href="avis.php" class="btn btn-sm btn-success">Laisser un avis</a>
        </div>
        <?php endif; ?>

        <h5 class="mb-3">Dernieres commandes</h5>
        <?php if (empty($commandes)): ?>
        <div class="card p-4 text-center text-muted">
            <p class="mb-3">Aucune commande pour le moment.</p>
            <a href="menus.php" class="btn btn-dark">Parcourir les menus</a>
        </div>
        <?php else: ?>
        <div class="table-responsive card shadow-sm">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light"><tr><th>Menu</th><th>Date</th><th>Total</th><th>Statut</th><th></th></tr></thead>
                <tbody>
                <?php foreach (array_slice($commandes, 0, 5) as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['titre']) ?></td>
                    <td><?= date('d/m/Y', strtotime($c['date_livraison'])) ?></td>
                    <td><?= number_format((float)$c['prix_total'], 2) ?> &euro;</td>
                    <td><span class="badge bg-<?= getStatutBadgeClass($c['statut']) ?>"><?= htmlspecialchars(getStatutLabel($c['statut'])) ?></span></td>
                    <td><a href="?tab=commandes" class="btn btn-sm btn-outline-dark">Details</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php elseif ($tab === 'commandes'): ?>
        <h2 class="mb-4">Mes commandes</h2>
        <?php if (empty($commandes)): ?>
        <p class="text-muted">Aucune commande.</p>
        <?php endif; ?>
        <?php foreach ($commandes as $c): ?>
        <div class="card mb-3 shadow-sm border-0 order-card">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                    <h5 class="mb-0">#<?= (int)$c['id'] ?> — <?= htmlspecialchars($c['titre']) ?></h5>
                    <span class="badge bg-<?= getStatutBadgeClass($c['statut']) ?>"><?= htmlspecialchars(getStatutLabel($c['statut'])) ?></span>
                </div>
                <div class="row small text-muted g-2 mb-3">
                    <div class="col-md-4"><i class="fa-regular fa-calendar me-1"></i> <?= htmlspecialchars($c['date_livraison']) ?> a <?= htmlspecialchars(substr($c['heure_livraison'], 0, 5)) ?></div>
                    <div class="col-md-4"><i class="fa-solid fa-users me-1"></i> <?= (int)$c['nb_personnes'] ?> personnes</div>
                    <div class="col-md-4"><i class="fa-solid fa-euro-sign me-1"></i> <?= number_format((float)$c['prix_total'], 2) ?> EUR</div>
                    <div class="col-12"><i class="fa-solid fa-location-dot me-1"></i> <?= htmlspecialchars($c['numero'] . ' ' . $c['rue'] . ', ' . $c['code_postal'] . ' ' . $c['ville']) ?></div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <?php if ($c['statut'] === 'en_attente'): ?>
                    <a href="modifier-commande.php?id=<?= (int)$c['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                    <form method="POST" action="annuler-commande.php" class="d-inline" onsubmit="return confirm('Annuler cette commande ?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Annuler</button>
                    </form>
                    <?php endif; ?>
                    <?php if (!in_array($c['statut'], ['annulee'], true)): ?>
                    <a href="suivi-commande.php?id=<?= (int)$c['id'] ?>" class="btn btn-info btn-sm">Suivi</a>
                    <?php endif; ?>
                    <?php if (in_array($c['statut'], getReviewEligibleStatuts(), true)): ?>
                        <?php if (userHasReviewForOrder($pdo, $user_id, (int)$c['id'])): ?>
                        <span class="badge bg-secondary align-self-center">Avis envoye</span>
                        <?php else: ?>
                        <a href="avis.php?commande_id=<?= (int)$c['id'] ?>" class="btn btn-success btn-sm"><i class="fa-solid fa-star me-1"></i> Donner un avis</a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <a href="menu.php?id=<?= (int)$c['menu_id'] ?>" class="btn btn-outline-dark btn-sm">Commander a nouveau</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php elseif ($tab === 'favoris'): ?>
        <h2 class="mb-4">Mes menus favoris</h2>
        <?php if (empty($favoris)): ?>
        <div class="card p-5 text-center text-muted">
            <i class="fa-regular fa-heart fa-3x mb-3"></i>
            <p>Ajoutez des menus en favori depuis la page detail d un menu.</p>
            <a href="menus.php" class="btn btn-dark">Decouvrir les menus</a>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($favoris as $m): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    <img src="<?= htmlspecialchars(menuCoverUrl($m)) ?>" class="card-img-top" alt="<?= htmlspecialchars($m['titre'] ?? '') ?>" style="height:160px;object-fit:cover" loading="lazy">
                    <div class="card-body d-flex flex-column">
                        <h5><?= htmlspecialchars($m['titre']) ?></h5>
                        <p class="small text-muted flex-grow-1"><?= htmlspecialchars(substr($m['description'] ?? '', 0, 90)) ?>...</p>
                        <p class="fw-bold text-danger mb-2"><?= number_format((float)$m['prix'], 2) ?> EUR/pers.</p>
                        <div class="d-flex gap-2">
                            <a href="menu.php?id=<?= (int)$m['id'] ?>" class="btn btn-dark btn-sm flex-grow-1">Commander</a>
                            <form method="POST" action="espace-utilisateur.php?tab=favoris" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="toggle_favori">
                                <input type="hidden" name="menu_id" value="<?= (int)$m['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Retirer"><i class="fa-solid fa-heart-crack"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php elseif ($tab === 'notifications'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h2 class="mb-0">Notifications</h2>
            <?php if ($unreadCount > 0): ?>
            <form method="POST" action="mark-notifications.php">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="0">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Tout marquer comme lu</button>
            </form>
            <?php endif; ?>
        </div>
        <?php if (empty($notifications)): ?>
        <p class="text-muted">Aucune notification.</p>
        <?php else: ?>
        <div class="list-group shadow-sm">
            <?php foreach ($notifications as $n): ?>
            <div class="list-group-item list-group-item-action <?= !$n['is_read'] ? 'notification-unread' : '' ?>">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <strong><?= htmlspecialchars($n['titre']) ?></strong>
                        <?php if (!empty($n['message'])): ?>
                        <p class="mb-1 small text-muted"><?= htmlspecialchars($n['message']) ?></p>
                        <?php endif; ?>
                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($n['created_at'])) ?></small>
                        <?php if (!empty($n['lien'])): ?>
                        <div class="mt-1"><a href="<?= htmlspecialchars($n['lien']) ?>" class="small">Voir le detail</a></div>
                        <?php endif; ?>
                    </div>
                    <?php if (!$n['is_read']): ?>
                    <form method="POST" action="mark-notifications.php">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-light">Lu</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php elseif ($tab === 'profil'): ?>
        <h2 class="mb-4">Mon profil</h2>
        <div class="card border-0 shadow-sm p-4">
            <form method="POST" action="update-user.php">
                <?= csrfField() ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Prenom</label>
                        <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Telephone</label>
                        <input type="tel" name="telephone" class="form-control" value="<?= htmlspecialchars($user['telephone'] ?? $user['gsm'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de naissance</label>
                        <input type="date" name="date_naissance" class="form-control" value="<?= htmlspecialchars($user['date_naissance'] ?? '') ?>">
                    </div>
                </div>
                <hr>
                <h5>Adresse</h5>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Rue</label><input type="text" name="rue" class="form-control" value="<?= htmlspecialchars($user['rue'] ?? '') ?>" required></div>
                    <div class="col-md-2"><label class="form-label">Numero</label><input type="text" name="numero" class="form-control" value="<?= htmlspecialchars($user['numero'] ?? '') ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Complement</label><input type="text" name="complement" class="form-control" value="<?= htmlspecialchars($user['complement'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Code postal</label><input type="text" name="code_postal" class="form-control" value="<?= htmlspecialchars($user['code_postal'] ?? '') ?>" required></div>
                    <div class="col-md-8"><label class="form-label">Ville</label><input type="text" name="ville" class="form-control" value="<?= htmlspecialchars($user['ville'] ?? '') ?>" required></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary">Enregistrer</button></div>
                </div>
            </form>
        </div>
        <?php endif; ?>

    </div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
