<?php
require __DIR__ . '/../includes/db.php';

$menuId = (int) ($argv[1] ?? 9);

$stmt = $pdo->prepare('SELECT id, titre, stock FROM menus WHERE id = ?');
$stmt->execute([$menuId]);
$menu = $stmt->fetch(PDO::FETCH_ASSOC);
echo 'Menu #' . $menuId . ': ' . json_encode($menu, JSON_UNESCAPED_UNICODE) . PHP_EOL;

foreach (['menu_options', 'menu_plats'] as $table) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE menu_id = ?");
        $stmt->execute([$menuId]);
        echo "{$table}: " . $stmt->fetchColumn() . PHP_EOL;
    } catch (Throwable $e) {
        echo "{$table}: table absente ou erreur — " . $e->getMessage() . PHP_EOL;
    }
}

try {
    $stmt = $pdo->prepare('
        SELECT mo.type, p.nom
        FROM menu_options mo
        JOIN plats p ON p.id = mo.plat_id
        WHERE mo.menu_id = ?
        LIMIT 15
    ');
    $stmt->execute([$menuId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo 'Plats via menu_options: ' . count($rows) . PHP_EOL;
    foreach ($rows as $row) {
        echo "  - {$row['type']}: {$row['nom']}" . PHP_EOL;
    }
} catch (Throwable $e) {
    echo 'menu_options join: ' . $e->getMessage() . PHP_EOL;
}

$total = $pdo->query('SELECT COUNT(*) FROM menu_options')->fetchColumn();
echo 'Total menu_options en BDD: ' . $total . PHP_EOL;

echo PHP_EOL . '=== Repartition menu_options ===' . PHP_EOL;
foreach ($pdo->query('SELECT menu_id, COUNT(*) c FROM menu_options GROUP BY menu_id ORDER BY menu_id') as $row) {
    echo "  menu {$row['menu_id']}: {$row['c']} plats" . PHP_EOL;
}

echo PHP_EOL . '=== Menus locaux (id + titre) ===' . PHP_EOL;
foreach ($pdo->query('SELECT id, titre FROM menus ORDER BY id') as $row) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM menu_options WHERE menu_id = ?');
    $stmt->execute([$row['id']]);
    $c = $stmt->fetchColumn();
    echo "  {$row['id']}: {$row['titre']} ({$c} plats)" . PHP_EOL;
}

echo PHP_EOL . 'Plats en BDD: ' . $pdo->query('SELECT COUNT(*) FROM plats')->fetchColumn() . PHP_EOL;
echo 'Boissons en BDD: ' . $pdo->query('SELECT COUNT(*) FROM boissons')->fetchColumn() . PHP_EOL;
