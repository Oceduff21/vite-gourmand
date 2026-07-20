<?php

declare(strict_types=1);

namespace ViteGourmand\Models;

/**
 * Modele Commande — regles metier (statuts, livraison, reductions).
 */
class Commande
{
    public function __construct(
        private readonly array $data = []
    ) {
    }

    public function id(): int
    {
        return (int) ($this->data['id'] ?? 0);
    }

    public function statut(): string
    {
        return (string) ($this->data['statut'] ?? '');
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public static function findByIdForUser(\PDO $pdo, int $userId, int $orderId): ?self
    {
        require_once __DIR__ . '/../../includes/user-helpers.php';
        $row = fetchCommandeForUser($pdo, $userId, $orderId);
        return $row ? new self($row) : null;
    }

    /** @return list<string> */
    public static function getStatuts(): array
    {
        require_once __DIR__ . '/../../includes/helpers.php';
        return getStatutsCommande();
    }

    public static function calculateReduction(float $totalMenu, int $quantite, int $minPersonnes): float
    {
        require_once __DIR__ . '/../../includes/helpers.php';
        return calculateCommandeReduction($totalMenu, $quantite, $minPersonnes);
    }

    public static function calculateLivraison(string $ville, string $codePostal): float
    {
        require_once __DIR__ . '/../../includes/helpers.php';
        return calculateLivraisonPrice($ville, $codePostal);
    }

    public static function isTransitionAllowed(string $from, string $to): bool
    {
        require_once __DIR__ . '/../../includes/helpers.php';
        return isStatutTransitionAllowed($from, $to);
    }

    public static function validateDateLivraison(string $date, int $delaiJours, ?string $fromDate = null): ?string
    {
        require_once __DIR__ . '/../../includes/helpers.php';
        return validateDateLivraisonMenu($date, $delaiJours, $fromDate);
    }
}
