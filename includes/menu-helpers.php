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

function normalizeCartFromPost(?string $json): array
{
    $cart = json_decode($json ?? '', true);
    if (!is_array($cart)) {
        return [];
    }

    $normalized = ['invites' => max(0, (int)($cart['invites'] ?? 0))];
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

    return $normalized;
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
