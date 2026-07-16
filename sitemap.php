<?php
require 'includes/db.php';
require 'includes/seo.php';

header('Content-Type: application/xml; charset=utf-8');

$base = rtrim(getSiteBaseUrl(), '/');
$today = date('Y-m-d');

$static = [
    ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
    ['loc' => '/menus.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => '/a-propos.php', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => '/avis.php', 'priority' => '0.7', 'changefreq' => 'weekly'],
    ['loc' => '/faq.php', 'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => '/contact.php', 'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => '/cgv.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ['loc' => '/mentions-legales.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ['loc' => '/politique-confidentialite.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($static as $page): ?>
<url>
    <loc><?= htmlspecialchars($base . $page['loc']) ?></loc>
    <lastmod><?= $today ?></lastmod>
    <changefreq><?= $page['changefreq'] ?></changefreq>
    <priority><?= $page['priority'] ?></priority>
</url>
<?php endforeach; ?>
<?php
try {
    $menus = $pdo->query('SELECT id FROM menus WHERE stock > 0 ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($menus as $menu) {
        $lastmod = $today;
        ?>
<url>
    <loc><?= htmlspecialchars($base . '/menu.php?id=' . (int)$menu['id']) ?></loc>
    <lastmod><?= $lastmod ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
</url>
        <?php
    }
} catch (Throwable $e) {
    // Ignore si table absente
}
?>
</urlset>
