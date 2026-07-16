<?php

const MENU_TYPES = ['entree', 'plat', 'dessert'];
const MENU_OPTIONS_REQUIRED = 3;

function getMenuPlatsByType(PDO $pdo, int $menuId): array
{
    $group = array_fill_keys(MENU_TYPES, []);
    $stmt = $pdo->prepare('
        SELECT mo.type, p.*
        FROM menu_options mo
        JOIN plats p ON p.id = mo.plat_id
        WHERE mo.menu_id = ?
        ORDER BY mo.type, p.nom
    ');
    $stmt->execute([$menuId]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $group[$row['type']][] = $row;
    }
    return $group;
}

function getMenuOptionIds(PDO $pdo, int $menuId): array
{
    $ids = array_fill_keys(MENU_TYPES, []);
    $stmt = $pdo->prepare('SELECT type, plat_id FROM menu_options WHERE menu_id = ?');
    $stmt->execute([$menuId]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $ids[$row['type']][] = (int)$row['plat_id'];
    }
    return $ids;
}

function validatePlatSelection(PDO $pdo, int $menuId, array $cart, int $nbPersonnes, int $minPersonnes): ?string
{
    if ($nbPersonnes < $minPersonnes) {
        return 'Minimum ' . $minPersonnes . ' personnes requis pour ce menu.';
    }

    $allowed = getMenuOptionIds($pdo, $menuId);
    foreach (MENU_TYPES as $type) {
        $items = $cart[$type] ?? [];
        if (!is_array($items)) {
            return 'Selection de plats incomplete (' . $type . ').';
        }

        $sum = 0;
        foreach ($items as $platId => $qty) {
            $qty = (int)$qty;
            $platId = (int)$platId;
            if ($qty <= 0) {
                continue;
            }
            if (!in_array($platId, $allowed[$type], true)) {
                return 'Plat invalide pour ce menu.';
            }
            $sum += $qty;
        }

        if ($sum !== $nbPersonnes) {
            return 'Repartissez exactement ' . $nbPersonnes . ' invites pour chaque categorie (' . ucfirst($type) . ' : ' . $sum . '/' . $nbPersonnes . ').';
        }
    }

    return null;
}

function boissonsTableHasCategorie(PDO $pdo): bool
{
    static $has = null;
    if ($has !== null) {
        return $has;
    }
    try {
        $pdo->query('SELECT categorie FROM boissons LIMIT 1');
        $has = true;
    } catch (Throwable $e) {
        $has = false;
    }
    return $has;
}

function getMenuBoissons(PDO $pdo, int $menuId): array
{
    $hasCategorie = boissonsTableHasCategorie($pdo);
    $orderBy = $hasCategorie ? 'COALESCE(b.categorie, "autre"), b.nom' : 'b.nom';

    $stmtMenu = $pdo->prepare('SELECT theme, titre FROM menus WHERE id = ?');
    $stmtMenu->execute([$menuId]);
    $menuRow = $stmtMenu->fetch(PDO::FETCH_ASSOC);
    $isEnfant = $menuRow && (
        stripos($menuRow['theme'] ?? '', 'enfant') !== false
        || stripos($menuRow['titre'] ?? '', 'enfant') !== false
    );

    $stmt = $pdo->prepare("
        SELECT b.*
        FROM menu_boissons mb
        JOIN boissons b ON b.id = mb.boisson_id
        WHERE mb.menu_id = ?
        ORDER BY {$orderBy}
    ");
    $stmt->execute([$menuId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        $sql = 'SELECT * FROM boissons';
        if ($isEnfant && $hasCategorie) {
            $sql .= ' WHERE COALESCE(categorie, "autre") IN ("soft","eau","jus","cocktail_sans_alcool")';
        }
        $sql .= ' ORDER BY ' . ($hasCategorie ? 'COALESCE(categorie, "autre"), nom' : 'nom');
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($isEnfant && $hasCategorie) {
        $sansAlcool = ['soft', 'eau', 'jus', 'cocktail_sans_alcool'];
        $rows = array_values(array_filter($rows, static function (array $row) use ($sansAlcool): bool {
            return in_array($row['categorie'] ?? 'autre', $sansAlcool, true);
        }));
    }

    $grouped = [];
    foreach ($rows as $row) {
        $cat = ($hasCategorie ? ($row['categorie'] ?? 'autre') : 'autre');
        $grouped[$cat][] = $row;
    }
    ksort($grouped);

    return $grouped;
}

function menuImageExists(string $filename): bool
{
    return $filename !== '' && is_file(__DIR__ . '/../assets/images/' . ltrim($filename, '/'));
}

/** @return string|null Nom de fichier ou null si generique / vide (presets auto). */
function normalizeMenuImageFilename(?string $filename): ?string
{
    $filename = trim((string)$filename);
    $generic = ['presentation.jpg', 'default.jpg', 'image.jpg', ''];
    if ($filename === '' || in_array($filename, $generic, true)) {
        return null;
    }

    $filename = ltrim(str_replace('\\', '/', $filename), '/');
    if (str_starts_with($filename, 'assets/images/')) {
        $filename = substr($filename, strlen('assets/images/'));
    }

    return $filename;
}

/**
 * URL image avec cache-bust (?v=timestamp) pour forcer le rechargement apres remplacement du fichier.
 */
function assetImageUrl(string $path): string
{
    $path = trim($path);
    if ($path === '') {
        return 'assets/images/presentation.jpg';
    }

    $filename = ltrim(str_replace('\\', '/', $path), '/');
    if (str_starts_with($filename, 'assets/images/')) {
        $filename = substr($filename, strlen('assets/images/'));
    }

    $fullPath = __DIR__ . '/../assets/images/' . $filename;
    $url = 'assets/images/' . $filename;
    if (is_file($fullPath)) {
        $url .= '?v=' . filemtime($fullPath);
    }

    return $url;
}

function menuCoverUrl(array $menu): string
{
    return assetImageUrl(getMenuCoverImage($menu));
}

function normalizeMenuString(string $value): string
{
    $value = mb_strtolower(trim($value), 'UTF-8');
    $from = ['à', 'â', 'ä', 'é', 'è', 'ê', 'ë', 'ï', 'î', 'ô', 'ù', 'û', 'ü', 'ç', 'œ'];
    $to   = ['a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'o', 'u', 'u', 'u', 'c', 'oe'];
    return str_replace($from, $to, $value);
}

function resolveMenuThemeKey(array $menu): string
{
    $theme = preg_replace('/[^a-z0-9_-]/u', '', normalizeMenuString($menu['theme'] ?? ''));
    $titre = normalizeMenuString($menu['titre'] ?? '');

    if (str_contains($titre, 'enfant')) {
        return 'enfant';
    }
    if (str_contains($titre, 'vegan') || ($theme === 'classique' && str_contains($titre, 'vegan'))) {
        return 'vegan';
    }
    if (str_contains($titre, 'noel')) {
        return 'noel';
    }
    if (str_contains($titre, 'mariage')) {
        return 'mariage';
    }
    if (str_contains($titre, 'paques')) {
        return 'paques';
    }
    if (str_contains($titre, 'brunch')) {
        return 'brunch';
    }
    if (str_contains($titre, 'entreprise') || str_contains($titre, 'business')) {
        return 'business';
    }
    if (str_contains($titre, 'barbecue') || str_contains($titre, 'bbq')) {
        return 'barbecue';
    }
    if (str_contains($titre, 'mediterr') || str_contains($titre, 'portugais')) {
        return 'oriental';
    }
    if (str_contains($titre, 'cocktail') || str_contains($titre, 'prestige') || str_contains($titre, 'gastro')) {
        return 'gastronomique';
    }
    if (str_contains($titre, 'bapteme') || str_contains($titre, 'anniversaire')) {
        return 'anniversaire';
    }

    $themeMap = [
        'noel' => 'noel',
        'mariage' => 'mariage',
        'paques' => 'paques',
        'anniversaire' => 'anniversaire',
        'gastronomique' => 'gastronomique',
        'oriental' => 'oriental',
        'brunch' => 'brunch',
        'business' => 'business',
        'enfant' => 'enfant',
        'vegan' => 'vegan',
        'classique' => 'vegan',
        'evenement' => 'anniversaire',
        'premium' => 'gastronomique',
    ];

    return $themeMap[$theme] ?? ($theme !== '' ? $theme : 'presentation');
}

function normalizeGallerySlide(array $item): array
{
    if (!empty($item['src'])) {
        return [
            'src' => (string)$item['src'],
            'label' => (string)($item['label'] ?? 'Photo'),
        ];
    }

    return [
        'src' => (string)($item[0] ?? ''),
        'label' => (string)($item[1] ?? 'Photo'),
    ];
}

/** @return array<int, array{src: string, label: string}> */
function getMenuThemeGalleryPreset(array $menu): array
{
    $key = resolveMenuThemeKey($menu);
    $titre = $menu['titre'] ?? 'Menu';

    $presets = [
        'noel' => [
            ['src' => 'menu-noel-entree.jpg', 'label' => 'Menu Noel'],
            ['src' => 'veloute.jpg', 'label' => 'Entree festive'],
            ['src' => 'foie-gras.jpg', 'label' => 'Foie gras maison'],
            ['src' => 'buche.jpg', 'label' => 'Buche de Noel'],
            ['src' => 'canard.jpg', 'label' => 'Canard confit'],
        ],
        'mariage' => [
            ['src' => 'menu-prestige.jpg', 'label' => 'Menu Mariage'],
            ['src' => 'foie-gras.jpg', 'label' => 'Entree raffinee'],
            ['src' => 'saumon.jpg', 'label' => 'Poisson noble'],
            ['src' => 'fondant.jpg', 'label' => 'Dessert d exception'],
        ],
        'paques' => [
            ['src' => 'menu-classique.jpg', 'label' => 'Menu Paques'],
            ['src' => 'volaille.jpg', 'label' => 'Volaille de saison'],
            ['src' => 'salade.jpg', 'label' => 'Entree printaniere'],
            ['src' => 'fruits.jpg', 'label' => 'Dessert frais'],
        ],
        'anniversaire' => [
            ['src' => 'menu-bapteme.jpg', 'label' => 'Menu Anniversaire'],
            ['src' => 'menu-cocktail.jpg', 'label' => 'Ambiance festive'],
            ['src' => 'tiramisu.jpg', 'label' => 'Dessert gourmand'],
            ['src' => 'boeuf.jpg', 'label' => 'Plats conviviaux'],
        ],
        'gastronomique' => [
            ['src' => 'menu-gastronomique.jpg', 'label' => 'Menu Gastronomique'],
            ['src' => 'foie-gras.jpg', 'label' => 'Entree premium'],
            ['src' => 'canard.jpg', 'label' => 'Plat signature'],
            ['src' => 'fondant.jpg', 'label' => 'Dessert chef'],
        ],
        'oriental' => [
            ['src' => 'menu-oriental.jpg', 'label' => 'Menu Oriental'],
            ['src' => 'salade.jpg', 'label' => 'Mezze et entrees'],
            ['src' => 'poulet.jpg', 'label' => 'Grillades epicees'],
            ['src' => 'saumon.jpg', 'label' => 'Poisson mediterraneen'],
        ],
        'brunch' => [
            ['src' => 'menu-brunch.jpg', 'label' => 'Menu Brunch'],
            ['src' => 'presentation.jpg', 'label' => 'Brunch sucre-sale'],
            ['src' => 'fruits.jpg', 'label' => 'Fruits frais'],
            ['src' => 'tiramisu.jpg', 'label' => 'Douceurs du matin'],
        ],
        'business' => [
            ['src' => 'menu-business.jpg', 'label' => 'Menu Business'],
            ['src' => 'salade.jpg', 'label' => 'Entree legere'],
            ['src' => 'volaille.jpg', 'label' => 'Plat du jour'],
            ['src' => 'tiramisu.jpg', 'label' => 'Dessert'],
        ],
        'enfant' => [
            ['src' => 'menu-enfant.jpg', 'label' => 'Menu Enfant'],
            ['src' => 'poulet.jpg', 'label' => 'Plats enfants'],
            ['src' => 'volaille.jpg', 'label' => 'Nuggets et accompagnements'],
            ['src' => 'fruits.jpg', 'label' => 'Desserts gourmands'],
        ],
        'vegan' => [
            ['src' => 'menu-vegan.jpg', 'label' => 'Menu Vegan'],
            ['src' => 'salade.jpg', 'label' => 'Entree vegetale'],
            ['src' => 'risotto.jpg', 'label' => 'Plat vegan'],
            ['src' => 'fondant.jpg', 'label' => 'Dessert vegan'],
        ],
        'barbecue' => [
            ['src' => 'menu-barbecue.jpg', 'label' => 'Menu Barbecue'],
            ['src' => 'merguez.jpg', 'label' => 'Grillades en plein air'],
            ['src' => 'boeuf.jpg', 'label' => 'Brochettes boeuf'],
            ['src' => 'poulet.jpg', 'label' => 'Volailles grillees'],
        ],
    ];

    if (isset($presets[$key])) {
        return $presets[$key];
    }

    $cover = 'menu-' . $key . '.jpg';
    if (menuImageExists($cover)) {
        return [['src' => $cover, 'label' => $titre]];
    }

    return [['src' => 'presentation.jpg', 'label' => $titre]];
}

function getMenuCoverImage(array $menu): string
{
    $generic = ['presentation.jpg', 'default.jpg', 'image.jpg', ''];

    // 1. Image choisie explicitement en admin (prioritaire)
    $custom = trim($menu['image_principale'] ?? $menu['image'] ?? '');
    if ($custom !== '' && !in_array($custom, $generic, true) && menuImageExists($custom)) {
        return 'assets/images/' . ltrim($custom, '/');
    }

    // 2. Preset theme (menu-noel.jpg, menu-enfant.jpg, etc.)
    foreach (getMenuThemeGalleryPreset($menu) as $item) {
        $slide = normalizeGallerySlide($item);
        if ($slide['src'] !== '' && menuImageExists($slide['src'])) {
            return 'assets/images/' . $slide['src'];
        }
    }

    // 3. Fallback theme
    $key = resolveMenuThemeKey($menu);
    $themeFile = 'menu-' . $key . '.jpg';
    if (menuImageExists($themeFile)) {
        return 'assets/images/' . $themeFile;
    }

    return 'assets/images/presentation.jpg';
}

/**
 * Galerie carousel : photos theme + plats uniques.
 *
 * @param array<string, array<int, array<string, mixed>>> $group
 * @return array<int, array{src: string, label: string}>
 */
function getMenuGalleryImages(array $menu, array $group, int $max = 10): array
{
    $images = [];
    $seen = [];

    $add = static function (string $src, string $label) use (&$images, &$seen, $max): void {
        $src = trim($src);
        if ($src === '' || $src === 'default.jpg' || isset($seen[$src]) || count($images) >= $max) {
            return;
        }
        if (!menuImageExists($src)) {
            return;
        }
        $seen[$src] = true;
        $images[] = ['src' => $src, 'label' => $label];
    };

    foreach (getMenuThemeGalleryPreset($menu) as $item) {
        $slide = normalizeGallerySlide($item);
        $add($slide['src'], $slide['label']);
    }

    foreach (MENU_TYPES as $type) {
        foreach ($group[$type] ?? [] as $item) {
            $img = trim($item['image'] ?? '');
            if ($img === '' || $img === 'presentation.jpg') {
                continue;
            }
            $add($img, $item['nom'] ?? ucfirst($type));
        }
    }

    if (empty($images)) {
        $add('presentation.jpg', $menu['titre'] ?? 'Menu');
    }

    return $images;
}

function renderMenuCarousel(array $galleryImages, string $carouselId = 'menuCarousel'): string
{
    if (empty($galleryImages)) {
        return '';
    }

    $count = count($galleryImages);
    $indicators = '';
    $slides = '';

    foreach ($galleryImages as $i => $img) {
        $slide = normalizeGallerySlide($img);
        $active = $i === 0 ? ' active' : '';
        $src = htmlspecialchars(assetImageUrl('assets/images/' . $slide['src']));
        $label = htmlspecialchars($slide['label']);
        if ($src === '') {
            continue;
        }
        $indicators .= '<button type="button" data-bs-target="#' . $carouselId . '" data-bs-slide-to="' . $i . '"'
            . ($i === 0 ? ' class="active" aria-current="true"' : '') . ' aria-label="Photo ' . ($i + 1) . ': ' . $label . '"></button>';
        $slides .= '
        <div class="carousel-item' . $active . '">
            <img src="' . $src . '" class="d-block w-100 menu-carousel-img" alt="' . $label . '" loading="' . ($i === 0 ? 'eager' : 'lazy') . '">
            <div class="carousel-caption menu-carousel-caption">
                <p class="mb-0">' . $label . '</p>
            </div>
        </div>';
    }

    $controls = '';
    if ($count > 1) {
        $controls = '
        <button class="carousel-control-prev" type="button" data-bs-target="#' . $carouselId . '" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Precedent</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#' . $carouselId . '" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Suivant</span>
        </button>';
    }

    return '
<div id="' . $carouselId . '" class="carousel slide menu-carousel mb-4" data-bs-ride="carousel" data-bs-interval="5000">
    ' . ($count > 1 ? '<div class="carousel-indicators">' . $indicators . '</div>' : '') . '
    <div class="carousel-inner rounded shadow-sm">' . $slides . '</div>
    ' . $controls . '
</div>';
}

function menusTableHasColumn(PDO $pdo, string $column): bool
{
    static $cache = [];
    if (array_key_exists($column, $cache)) {
        return $cache[$column];
    }
    try {
        $pdo->query('SELECT `' . str_replace('`', '', $column) . '` FROM menus LIMIT 1');
        $cache[$column] = true;
    } catch (Throwable $e) {
        $cache[$column] = false;
    }
    return $cache[$column];
}

function saveMenuImageField(PDO $pdo, int $menuId, string $filename): void
{
    $filename = trim($filename);
    if ($filename === '') {
        return;
    }
    if (menusTableHasColumn($pdo, 'image_principale')) {
        $pdo->prepare('UPDATE menus SET image_principale = ? WHERE id = ?')->execute([$filename, $menuId]);
    } elseif (menusTableHasColumn($pdo, 'image')) {
        $pdo->prepare('UPDATE menus SET image = ? WHERE id = ?')->execute([$filename, $menuId]);
    }
}

function insertMenuWithImage(PDO $pdo, array $fields, string $imageFilename = ''): int
{
    $hasImagePrincipale = menusTableHasColumn($pdo, 'image_principale');
    $hasImage = menusTableHasColumn($pdo, 'image');

    $normalizedImage = normalizeMenuImageFilename($imageFilename);

    $columns = ['titre', 'description', 'theme', 'regime', 'prix', 'min_personnes', 'stock', 'conditions', 'delai_jours'];
    $values = [
        $fields['titre'], $fields['description'], $fields['theme'], $fields['regime'],
        $fields['prix'], $fields['min_personnes'], $fields['stock'], $fields['conditions'], $fields['delai_jours'],
    ];

    if ($hasImagePrincipale) {
        array_splice($columns, 2, 0, ['image_principale']);
        array_splice($values, 2, 0, [$normalizedImage]);
    } elseif ($hasImage) {
        array_splice($columns, 2, 0, ['image']);
        array_splice($values, 2, 0, [$normalizedImage ?? 'default.jpg']);
    }

    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    $sql = 'INSERT INTO menus (' . implode(', ', $columns) . ') VALUES (' . $placeholders . ')';
    $pdo->prepare($sql)->execute($values);
    return (int)$pdo->lastInsertId();
}

function updateMenuWithImage(PDO $pdo, int $menuId, array $fields, string $imageFilename = ''): void
{
    $hasImagePrincipale = menusTableHasColumn($pdo, 'image_principale');
    $hasImage = menusTableHasColumn($pdo, 'image');

    $normalizedImage = normalizeMenuImageFilename($imageFilename);

    $sets = 'titre=?, description=?, theme=?, regime=?, prix=?, min_personnes=?, stock=?, conditions=?, delai_jours=?';
    $values = [
        $fields['titre'], $fields['description'], $fields['theme'], $fields['regime'],
        $fields['prix'], $fields['min_personnes'], $fields['stock'], $fields['conditions'], $fields['delai_jours'],
    ];

    if ($hasImagePrincipale) {
        $sets = 'titre=?, description=?, image_principale=?, theme=?, regime=?, prix=?, min_personnes=?, stock=?, conditions=?, delai_jours=?';
        $values = [
            $fields['titre'], $fields['description'], $normalizedImage,
            $fields['theme'], $fields['regime'], $fields['prix'], $fields['min_personnes'],
            $fields['stock'], $fields['conditions'], $fields['delai_jours'],
        ];
    } elseif ($hasImage) {
        $sets = 'titre=?, description=?, image=?, theme=?, regime=?, prix=?, min_personnes=?, stock=?, conditions=?, delai_jours=?';
        $values = [
            $fields['titre'], $fields['description'], $normalizedImage ?? 'default.jpg',
            $fields['theme'], $fields['regime'], $fields['prix'], $fields['min_personnes'],
            $fields['stock'], $fields['conditions'], $fields['delai_jours'],
        ];
    }

    $values[] = $menuId;
    $pdo->prepare('UPDATE menus SET ' . $sets . ' WHERE id=?')->execute($values);
}

function formatMenuPriceLabel(array $menu): string
{
    $prix = (float)($menu['prix'] ?? 0);
    $min = max(1, (int)($menu['min_personnes'] ?? 1));
    $totalMin = number_format($prix * $min, 2);
    $prixFmt = number_format($prix, 2);
    return $prixFmt . ' &euro;/pers. &mdash; des ' . $totalMin . ' &euro; pour ' . $min . ' pers. min.';
}

function formatMenuExcerpt(?string $text, int $max = 90): string
{
    $text = trim(strip_tags($text ?? ''));
    if ($text === '') {
        return '';
    }
    if (mb_strlen($text) <= $max) {
        return $text;
    }
    return mb_substr($text, 0, $max) . '...';
}

function renderMenuCard(array $menu): string
{
    $cover = htmlspecialchars(menuCoverUrl($menu));
    $fallback = htmlspecialchars(assetImageUrl('assets/images/presentation.jpg'));
    $titre = htmlspecialchars($menu['titre'] ?? '');
    $desc = htmlspecialchars(formatMenuExcerpt($menu['description'] ?? ''));
    $minPers = (int)($menu['min_personnes'] ?? 0);
    $prix = number_format((float)($menu['prix'] ?? 0), 2);
    $delai = (int)($menu['delai_jours'] ?? 7);
    $stock = (int)($menu['stock'] ?? 0);
    $theme = htmlspecialchars(ucfirst($menu['theme'] ?? ''));
    $id = (int)$menu['id'];
    $disponible = $stock > 0;

    $prixLabel = formatMenuPriceLabel($menu);

    $stockBadge = $disponible
        ? '<span class="badge bg-success menu-card-badge">Disponible</span>'
        : '<span class="badge bg-secondary menu-card-badge">Indisponible</span>';

    $btnClass = $disponible ? 'btn-danger' : 'btn-outline-secondary';
    $btnLabel = 'Voir le detail';

    return '
<div class="col-xl-3 col-lg-4 col-md-6">
    <article class="card menu-card h-100 shadow-sm">
        <div class="menu-card-img-wrap position-relative">
            <img src="' . $cover . '" class="card-img-top" alt="' . $titre . '" loading="lazy"
                onerror="this.src=\'' . $fallback . '\'">
            ' . $stockBadge . '
        </div>
        <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                <h5 class="fw-bold mb-0">' . $titre . '</h5>
                ' . ($theme !== '' ? '<span class="badge bg-light text-dark border">' . $theme . '</span>' : '') . '
            </div>
            ' . ($desc !== '' ? '<p class="text-muted small flex-grow-1 mb-2">' . $desc . '</p>' : '<p class="flex-grow-1 mb-2"></p>') . '
            <ul class="list-unstyled small text-muted mb-3">
                <li><i class="fa-solid fa-users me-1"></i> Min. ' . $minPers . ' pers.</li>
                <li><i class="fa-solid fa-clock me-1"></i> Delai ' . $delai . ' j.</li>
            </ul>
            <p class="text-danger fw-bold mb-3">' . $prixLabel . '</p>
            <a href="menu.php?id=' . $id . '" class="btn ' . $btnClass . ' w-100 mt-auto">' . $btnLabel . '</a>
        </div>
    </article>
</div>';
}

function calculateMenuPersonnesTotal(float $prixAdulte, float $prixEnfant, int $invites, int $enfants): float
{
    $enfants = min(max(0, $enfants), max(0, $invites));
    $adultes = max(0, $invites - $enfants);
    return round($adultes * $prixAdulte + $enfants * $prixEnfant, 2);
}

function getBoissonCategories(): array
{
    return [
        'soft' => 'Softs',
        'eau' => 'Eaux',
        'jus' => 'Jus',
        'cocktail_sans_alcool' => 'Cocktails sans alcool',
        'biere' => 'Bieres',
        'vin_rouge' => 'Vins rouges',
        'vin_blanc' => 'Vins blancs',
        'vin_rose' => 'Vins roses',
        'champagne' => 'Champagne',
        'spiritueux' => 'Spiritueux',
        'autre' => 'Autres',
    ];
}

function normalizeCartFromPost(?string $json): array
{
    $cart = json_decode($json ?? '', true);
    if (!is_array($cart)) {
        return [];
    }

    $normalized = [
        'invites' => max(0, (int)($cart['invites'] ?? 0)),
        'enfants' => max(0, (int)($cart['enfants'] ?? 0)),
    ];
    foreach (MENU_TYPES as $type) {
        $normalized[$type] = [];
        if (!empty($cart[$type]) && is_array($cart[$type])) {
            foreach ($cart[$type] as $platId => $qty) {
                $qty = (int)$qty;
                if ($qty > 0) {
                    $normalized[$type][(int)$platId] = $qty;
                }
            }
        }
    }

    $normalized['boissons'] = [];
    if (!empty($cart['boissons']) && is_array($cart['boissons'])) {
        foreach ($cart['boissons'] as $boissonId => $qty) {
            $qty = (int)$qty;
            if ($qty > 0) {
                $normalized['boissons'][(int)$boissonId] = $qty;
            }
        }
    }

    return $normalized;
}

function getAllowedBoissonIds(PDO $pdo, int $menuId): array
{
    $ids = [];
    foreach (getMenuBoissons($pdo, $menuId) as $items) {
        foreach ($items as $row) {
            $ids[] = (int)$row['id'];
        }
    }
    return $ids;
}

function calculateBoissonsTotal(PDO $pdo, int $menuId, array $cart): float
{
    $allowed = getAllowedBoissonIds($pdo, $menuId);
    if (empty($allowed) || empty($cart['boissons'])) {
        return 0.0;
    }

    $total = 0.0;
    $stmt = $pdo->prepare('SELECT id, prix FROM boissons WHERE id = ?');
    foreach ($cart['boissons'] as $boissonId => $qty) {
        $boissonId = (int)$boissonId;
        $qty = (int)$qty;
        if ($qty <= 0 || !in_array($boissonId, $allowed, true)) {
            continue;
        }
        $stmt->execute([$boissonId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $total += (float)$row['prix'] * $qty;
        }
    }

    return round($total, 2);
}

function cartHasValidSelection(array $cart, int $nbPersonnes): bool
{
    if ($nbPersonnes <= 0) {
        return false;
    }
    foreach (MENU_TYPES as $type) {
        $sum = array_sum($cart[$type] ?? []);
        if ($sum !== $nbPersonnes) {
            return false;
        }
    }
    return true;
}
