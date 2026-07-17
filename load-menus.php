<?php
require 'includes/db.php';
require 'includes/menu-helpers.php';

$params = [
    'search' => $_GET['search'] ?? '',
    'theme' => $_GET['theme'] ?? '',
    'regime' => $_GET['regime'] ?? '',
    'prix_min' => $_GET['prix_min'] ?? '',
    'prix_max' => $_GET['prix_max'] ?? '',
    'personnes' => $_GET['personnes'] ?? '',
    'sort' => $_GET['sort'] ?? '',
];

$stmt = $pdo->query('SELECT * FROM menus');
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
$menus = filterMenusForListing($menus, $params);
$menus = sortMenusForListing($menus, (string)$params['sort'], $pdo);

$count = count($menus);
$countHtml = '<p class="menus-results-count col-12" role="status" aria-live="polite">'
    . ($count === 0
        ? 'Aucun menu ne correspond a vos criteres.'
        : $count . ' menu' . ($count > 1 ? 's' : '') . ' trouve' . ($count > 1 ? 's' : '') . '.')
    . '</p>';

$badgesHtml = renderMenuFilterBadges($params);

if ($count === 0) {
    echo $badgesHtml;
    echo $countHtml;
    echo '<div class="col-12"><p class="text-center text-muted py-4 mb-0">Essayez un autre mot-cle (ex. anniversaire, vegan) ou elargissez votre budget.</p></div>';
    exit;
}

echo $badgesHtml;
echo $countHtml;

foreach ($menus as $menu) {
    echo renderMenuCard($menu);
}
