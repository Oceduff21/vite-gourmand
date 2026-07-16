<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';
require 'includes/menu-helpers.php';
require 'includes/mongo.php';
require 'includes/user-helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$menu_id = (int)($_POST['menu_id'] ?? 0);
$quantite = (int)($_POST['quantite'] ?? 0);
$rue = trim($_POST['rue'] ?? '');
$numero = trim($_POST['numero'] ?? '');
$complement = trim($_POST['complement'] ?? '');
$code_postal = trim($_POST['code_postal'] ?? '');
$ville = trim($_POST['ville'] ?? '');
$date = $_POST['date'] ?? '';
$heure = $_POST['heure'] ?? '';
$gsm = trim($_POST['gsm'] ?? '');

if (!verifyCsrf($_POST['csrf_token'] ?? '')) { die('Token CSRF invalide.'); }

if (!$menu_id || !$quantite || !$rue || !$numero || !$code_postal || !$ville || !$date || !$heure) {
    die('Tous les champs obligatoires doivent etre remplis.');
}

$stmt = $pdo->prepare('SELECT * FROM menus WHERE id = ?');
$stmt->execute([$menu_id]);
$menu = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$menu) {
    die('Menu invalide');
}

$min = (int)$menu['min_personnes'];
$prix = (float)$menu['prix'];
$stock = (int)($menu['stock'] ?? 0);

if ($quantite < $min) {
    die('Minimum ' . $min . ' personnes requis.');
}
if ($stock <= 0) {
    die('Stock insuffisant pour ce menu.');
}

$delaiJours = (int)($menu['delai_jours'] ?? 7);
$delaiError = validateDateLivraisonMenu($date, $delaiJours);
if ($delaiError) {
    $_SESSION['menu_cart_error'] = $delaiError;
    header('Location: commande.php?menu_id=' . $menu_id);
    exit();
}

$cart = normalizeCartFromPost($_POST['cart_json'] ?? '');
$cartError = validatePlatSelection($pdo, $menu_id, $cart, $quantite, $min);
if ($cartError) {
    $_SESSION['menu_cart_error'] = $cartError;
    header('Location: menu.php?id=' . $menu_id);
    exit();
}

if ((int)($cart['invites'] ?? 0) !== $quantite) {
    $_SESSION['menu_cart_error'] = 'Le nombre de personnes doit correspondre a la repartition des plats.';
    header('Location: menu.php?id=' . $menu_id);
    exit();
}

$nbEnfants = min($quantite, max(0, (int)($cart['enfants'] ?? 0)));
$prixEnfant = $prix;
$stmtEnf = $pdo->query("SELECT prix FROM menus WHERE LOWER(theme) = 'enfant' OR LOWER(titre) LIKE '%enfant%' ORDER BY id LIMIT 1");
if ($rowEnf = $stmtEnf->fetch(PDO::FETCH_ASSOC)) {
    $prixEnfant = (float)$rowEnf['prix'];
}

$totalMenu = calculateMenuPersonnesTotal($prix, $prixEnfant, $quantite, $nbEnfants);
$totalBoissons = calculateBoissonsTotal($pdo, $menu_id, $cart);
$livraison = calculateLivraisonPrice($ville, $code_postal);
$reduction = calculateCommandeReduction($totalMenu, $quantite, $min);
$total = round($totalMenu + $totalBoissons + $livraison - $reduction, 2);

$pdo->beginTransaction();

if ($gsm !== '') {
    $pdo->prepare('UPDATE users SET gsm = ?, telephone = COALESCE(telephone, ?) WHERE id = ?')
        ->execute([$gsm, $gsm, $user_id]);
}

$stmt = $pdo->prepare('INSERT INTO commandes (user_id, menu_id, nb_personnes, prix_menu, prix_livraison, reduction, prix_total, rue, numero, complement, code_postal, ville, date_livraison, heure_livraison, statut, nb_enfants) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
try {
    $stmt->execute([
        $user_id, $menu_id, $quantite, $totalMenu, $livraison, $reduction, $total,
        $rue, $numero, $complement, $code_postal, $ville, $date, $heure, 'en_attente', $nbEnfants
    ]);
} catch (Throwable $e) {
    $stmt = $pdo->prepare('INSERT INTO commandes (user_id, menu_id, nb_personnes, prix_menu, prix_livraison, reduction, prix_total, rue, numero, complement, code_postal, ville, date_livraison, heure_livraison, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $user_id, $menu_id, $quantite, $totalMenu, $livraison, $reduction, $total,
        $rue, $numero, $complement, $code_postal, $ville, $date, $heure, 'en_attente'
    ]);
}
$commande_id = (int)$pdo->lastInsertId();

enregistrerHistorique($pdo, $commande_id, 'en_attente');

$cart = normalizeCartFromPost($_POST['cart_json'] ?? '');
if ($cart) {
    foreach (['entree', 'plat', 'dessert'] as $type) {
        if (empty($cart[$type]) || !is_array($cart[$type])) {
            continue;
        }
        foreach ($cart[$type] as $platId => $qty) {
            $qty = (int)$qty;
            $platId = (int)$platId;
            if ($platId > 0 && $qty > 0) {
                $stmt = $pdo->prepare('INSERT INTO commande_details (commande_id, plat_id, quantite, type) VALUES (?, ?, ?, ?)');
                $stmt->execute([$commande_id, $platId, $qty, $type]);
            }
        }
    }
}

if (!empty($cart['boissons']) && is_array($cart['boissons'])) {
    $allowedBoissons = getAllowedBoissonIds($pdo, $menu_id);
    $stmtBoisson = $pdo->prepare('SELECT prix FROM boissons WHERE id = ?');
    try {
        $insertBoisson = $pdo->prepare('INSERT INTO commande_boissons (commande_id, boisson_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)');
        foreach ($cart['boissons'] as $boissonId => $qty) {
            $boissonId = (int)$boissonId;
            $qty = (int)$qty;
            if ($qty <= 0 || !in_array($boissonId, $allowedBoissons, true)) {
                continue;
            }
            $stmtBoisson->execute([$boissonId]);
            $prixBoisson = (float)$stmtBoisson->fetchColumn();
            $insertBoisson->execute([$commande_id, $boissonId, $qty, $prixBoisson]);
        }
    } catch (Throwable $e) {
        // Table commande_boissons absente si migration non executee
    }
}

unset($_SESSION['menu_cart'], $_SESSION['menu_cart_menu_id']);

$pdo->prepare('UPDATE menus SET stock = stock - 1 WHERE id = ? AND stock > 0')->execute([$menu_id]);

$pdo->commit();

createUserNotification(
    $pdo,
    $user_id,
    'Commande confirmee #' . $commande_id,
    'Votre commande pour le menu ' . $menu['titre'] . ' est enregistree.',
    'suivi-commande.php?id=' . $commande_id,
    'success'
);

mongoInsertCommandeStat([
    'commande_id' => $commande_id,
    'menu_id' => $menu_id,
    'menu_titre' => $menu['titre'],
    'prix_total' => $total,
    'nb_personnes' => $quantite
]);

$stmt = $pdo->prepare('SELECT email, nom, prenom FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && !empty($user['email'])) {
    $subject = 'Confirmation de votre commande - Vite & Gourmand';
    $message = 'Bonjour ' . $user['prenom'] . ' ' . $user['nom'] . ",\n\nVotre commande #" . $commande_id . " est confirmee.\n\nMenu : " . $menu['titre'] . "\nPersonnes : " . $quantite;
    if ($nbEnfants > 0) {
        $message .= ' (dont ' . $nbEnfants . ' enfant(s))';
    }
    $message .= "\nDate : " . $date . ' a ' . $heure . "\nAdresse : " . $numero . ' ' . $rue . ', ' . $code_postal . ' ' . $ville . "\nTotal : " . $total . " EUR\n\nMerci de votre confiance !";
    @mail($user['email'], $subject, $message, 'From: contact@vite-gourmand.fr');
}

header('Location: confirmation.php?id=' . $commande_id);
exit();
