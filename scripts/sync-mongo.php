<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/mongo.php';

$rows = $pdo->query('
    SELECT c.id, c.menu_id, m.titre, c.prix_total, c.nb_personnes, c.statut
    FROM commandes c
    JOIN menus m ON c.menu_id = m.id
    WHERE c.statut <> \'annulee\'
')->fetchAll(PDO::FETCH_ASSOC);

$count = 0;
foreach ($rows as $r) {
    if (mongoInsertCommandeStat([
        'commande_id' => $r['id'],
        'menu_id' => $r['menu_id'],
        'menu_titre' => $r['titre'],
        'prix_total' => $r['prix_total'],
        'nb_personnes' => $r['nb_personnes'],
        'statut' => $r['statut'] ?? 'en_attente',
    ])) {
        $count++;
    }
}
echo "Synchronise : $count commandes vers MongoDB\n";
