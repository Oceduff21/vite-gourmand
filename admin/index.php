<?php
require_once __DIR__ . '/partials/auth.php';
requireAdminAccess();

require '../includes/db.php';

$canViewFinancials = canViewAdminFinancials();
$staffId = (int)($_SESSION['user_id'] ?? 0);
$pageTitle = $canViewFinancials ? 'Dashboard' : 'Espace employe';
$extraHead = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
$dashboardError = null;

$enAttente = 0;
$avisEnAttente = 0;
$statutsBreakdown = [];
$dates = [];
$totaux = [];
$prochainesLivraisons = [];
$enPreparation = 0;
$enLivraison = 0;
$livraisonsAujourdhui = 0;
$mesActions = countStaffActions($pdo, $staffId);

if (!$canViewFinancials && (empty($_SESSION['user_prenom']) && $staffId > 0)) {
    $u = $pdo->prepare('SELECT prenom, nom FROM users WHERE id = ?');
    $u->execute([$staffId]);
    if ($row = $u->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['user_prenom'] = $row['prenom'] ?? '';
        $_SESSION['user_nom'] = $row['nom'] ?? '';
    }
}

try {
    $enAttente = (int)$pdo->query("SELECT COUNT(*) FROM commandes WHERE statut = 'en_attente'")->fetchColumn();
    $enPreparation = (int)$pdo->query("SELECT COUNT(*) FROM commandes WHERE statut = 'en_preparation'")->fetchColumn();
    $enLivraison = (int)$pdo->query("SELECT COUNT(*) FROM commandes WHERE statut = 'en_livraison'")->fetchColumn();
    $livraisonsAujourdhui = (int)$pdo->query("
        SELECT COUNT(*) FROM commandes
        WHERE statut NOT IN ('annulee', 'terminee')
        AND DATE(date_livraison) = CURRENT_DATE()
    ")->fetchColumn();

    try {
        $avisEnAttente = (int)$pdo->query('SELECT COUNT(*) FROM avis WHERE is_validated = 0')->fetchColumn();
    } catch (Throwable $e) {
        $avisEnAttente = 0;
    }

    foreach ($pdo->query('SELECT statut, COUNT(*) AS nb FROM commandes GROUP BY statut')->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $statutsBreakdown[] = [
            'label' => getStatutLabel($row['statut']),
            'count' => (int)$row['nb'],
        ];
    }

    $data = $pdo->query('SELECT DATE(created_at) AS date, COUNT(*) AS total FROM commandes GROUP BY DATE(created_at) ORDER BY date ASC LIMIT 30')->fetchAll(PDO::FETCH_ASSOC);
    $dates = array_column($data, 'date');
    $totaux = array_map('intval', array_column($data, 'total'));

    $prochainesLivraisons = $pdo->query("
        SELECT c.id, c.date_livraison, c.heure_livraison, c.statut, u.nom, u.prenom, m.titre AS menu_titre
        FROM commandes c
        JOIN users u ON c.user_id = u.id
        JOIN menus m ON c.menu_id = m.id
        WHERE c.statut NOT IN ('annulee', 'terminee')
        AND c.date_livraison >= CURRENT_DATE()
        ORDER BY c.date_livraison ASC, c.heure_livraison ASC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $dashboardError = 'Erreur chargement statistiques : ' . $e->getMessage();
}

if ($canViewFinancials) {
    $mongoOk = false;
    try {
        if (is_file(__DIR__ . '/../includes/mongo.php')) {
            require_once __DIR__ . '/../includes/mongo.php';
            $mongoOk = function_exists('mongoIsAvailable') && mongoIsAvailable();
        }
    } catch (Throwable $e) {
        $mongoOk = false;
    }

    $filterMenu = (int)($_GET['menu_id'] ?? 0);
    $filterDebut = $_GET['date_debut'] ?? '';
    $filterFin = $_GET['date_fin'] ?? '';

    $totalCommandes = 0;
    $ca = 0.0;
    $users = 0;
    $menusCount = 0;
    $caMois = 0.0;
    $commandesMois = 0;
    $panierMoyen = 0.0;
    $menus = [];
    $topMenus = [];
    $caParMois = [];
    $chartLabels = [];
    $chartTotals = [];
    $chartCounts = [];
    $chartSource = 'MySQL';

    try {
        $totalCommandes = (int)$pdo->query('SELECT COUNT(*) FROM commandes')->fetchColumn();
        $ca = (float)$pdo->query("SELECT COALESCE(SUM(prix_total), 0) FROM commandes WHERE statut <> 'annulee'")->fetchColumn();
        $users = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $menusCount = (int)$pdo->query('SELECT COUNT(*) FROM menus')->fetchColumn();

        $caMois = (float)$pdo->query("
            SELECT COALESCE(SUM(prix_total), 0) FROM commandes
            WHERE statut <> 'annulee'
            AND MONTH(created_at) = MONTH(CURRENT_DATE())
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
        ")->fetchColumn();

        $commandesMois = (int)$pdo->query("
            SELECT COUNT(*) FROM commandes
            WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
        ")->fetchColumn();

        $nbActives = (int)$pdo->query("SELECT COUNT(*) FROM commandes WHERE statut <> 'annulee'")->fetchColumn();
        $panierMoyen = $nbActives > 0 ? round($ca / $nbActives, 2) : 0.0;

        $menus = $pdo->query('SELECT id, titre FROM menus ORDER BY titre')->fetchAll(PDO::FETCH_ASSOC);

        $topMenus = $pdo->query("
            SELECT m.titre, COUNT(c.id) AS nb, COALESCE(SUM(c.prix_total), 0) AS ca
            FROM menus m
            LEFT JOIN commandes c ON c.menu_id = m.id AND c.statut <> 'annulee'
            GROUP BY m.id, m.titre
            ORDER BY nb DESC, ca DESC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        $caParMois = $pdo->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') AS mois, COALESCE(SUM(prix_total), 0) AS total
            FROM commandes
            WHERE statut <> 'annulee'
            AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY mois ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        if ($mongoOk) {
            $chartSource = 'MongoDB';
            $mongoCA = mongoGetCAparMenu($filterMenu ?: null, $filterDebut ?: null, $filterFin ?: null);
            $chartLabels = array_column($mongoCA, 'titre');
            $chartTotals = array_column($mongoCA, 'total');
            $chartCounts = array_column($mongoCA, 'count');
        } else {
            $sql = "SELECT m.titre, COALESCE(SUM(c.prix_total), 0) AS total, COUNT(c.id) AS count
                    FROM commandes c
                    JOIN menus m ON c.menu_id = m.id
                    WHERE c.statut <> 'annulee'";
            $params = [];
            if ($filterMenu) {
                $sql .= ' AND c.menu_id = ?';
                $params[] = $filterMenu;
            }
            if ($filterDebut) {
                $sql .= ' AND DATE(c.created_at) >= ?';
                $params[] = $filterDebut;
            }
            if ($filterFin) {
                $sql .= ' AND DATE(c.created_at) <= ?';
                $params[] = $filterFin;
            }
            $sql .= ' GROUP BY m.id, m.titre ORDER BY total DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $chartLabels[] = $row['titre'];
                $chartTotals[] = (float)$row['total'];
                $chartCounts[] = (int)$row['count'];
            }
        }
    } catch (Throwable $e) {
        $dashboardError = 'Erreur chargement statistiques : ' . $e->getMessage();
    }
}

include 'partials/layout.php';

if ($canViewFinancials) {
    include 'partials/dashboard-admin.php';
} else {
    include 'partials/dashboard-employee.php';
}

include 'partials/footer.php';
