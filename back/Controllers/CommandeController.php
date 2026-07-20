<?php

declare(strict_types=1);

namespace ViteGourmand\Controllers;

use ViteGourmand\Models\Commande;
use ViteGourmand\Models\Menu;

/**
 * Controleur Commande — calculs metier via modeles POO.
 * Utilisable depuis valider-commande.php / admin (migration progressive).
 */
class CommandeController
{
    public function __construct(
        private readonly \PDO $pdo
    ) {
    }

    /**
     * Devis simple (prix unitaire × quantite).
     *
     * @return array{menu: Menu, reduction: float, livraison: float, total_menu: float, total: float}|null
     */
    public function quote(int $menuId, int $quantite, string $ville, string $codePostal): ?array
    {
        $menu = Menu::findById($this->pdo, $menuId);
        if (!$menu) {
            return null;
        }

        $totalMenu = $menu->prix() * max(0, $quantite);
        $fees = $this->applyFees($totalMenu, $quantite, $menu->minPersonnes(), $ville, $codePostal);

        return [
            'menu' => $menu,
            'total_menu' => $totalMenu,
            'reduction' => $fees['reduction'],
            'livraison' => $fees['livraison'],
            'total' => max(0, $totalMenu - $fees['reduction'] + $fees['livraison']),
        ];
    }

    /**
     * Reduction + livraison a partir d'un total menu deja calcule (enfants, etc.).
     *
     * @return array{reduction: float, livraison: float}
     */
    public function applyFees(float $totalMenu, int $quantite, int $minPersonnes, string $ville, string $codePostal): array
    {
        return [
            'reduction' => Commande::calculateReduction($totalMenu, $quantite, $minPersonnes),
            'livraison' => Commande::calculateLivraison($ville, $codePostal),
        ];
    }

    public function canTransition(string $from, string $to): bool
    {
        return Commande::isTransitionAllowed($from, $to);
    }
}
