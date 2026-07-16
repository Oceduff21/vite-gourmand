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

function getMenuBoissons(PDO $pdo, int $menuId): array
{
    $stmtMenu = $pdo->prepare('SELECT theme, titre FROM menus WHERE id = ?');
    $stmtMenu->execute([$menuId]);
    $menuRow = $stmtMenu->fetch(PDO::FETCH_ASSOC);
    $isEnfant = $menuRow && (
        stripos($menuRow['theme'] ?? '', 'enfant') !== false
        || stripos($menuRow['titre'] ?? '', 'enfant') !== false
    );

    $stmt = $pdo->prepare('
        SELECT b.*
        FROM menu_boissons mb
        JOIN boissons b ON b.id = mb.boisson_id
        WHERE mb.menu_id = ?
        ORDER BY COALESCE(b.categorie, "autre"), b.nom
    ');
    $stmt->execute([$menuId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        $sql = 'SELECT * FROM boissons';
        if ($isEnfant) {
            $sql .= ' WHERE COALESCE(categorie, "autre") IN ("soft","eau","jus","cocktail_sans_alcool")';
        }
        $sql .= ' ORDER BY COALESCE(categorie, "autre"), nom';
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($isEnfant) {
        $sansAlcool = ['soft', 'eau', 'jus', 'cocktail_sans_alcool'];
        $rows = array_values(array_filter($rows, static function (array $row) use ($sansAlcool): bool {
            return in_array($row['categorie'] ?? 'autre', $sansAlcool, true);
        }));
    }

    $grouped = [];
    foreach ($rows as $row) {
        $cat = $row['categorie'] ?? 'autre';
        $grouped[$cat][] = $row;
    }
    ksort($grouped);

    return $grouped;
}

function getMenuCoverImage(array $menu): string
{
    $theme = strtolower(preg_replace('/[^a-z0-9_-]/', '', $menu['theme'] ?? 'default'));
    $candidates = [
        'assets/images/menu-' . $theme . '.jpg',
        'assets/images/' . strtolower($menu['image'] ?? ''),
    ];
    foreach ($candidates as $path) {
        if ($path && is_file(__DIR__ . '/../' . $path)) {
            return $path;
        }
    }
    return 'assets/images/presentation.jpg';
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
