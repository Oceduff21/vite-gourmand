<?php

declare(strict_types=1);

namespace ViteGourmand\Models;

/**
 * Modele User — profil, favoris, notifications.
 */
class User
{
    public function __construct(
        private readonly array $data
    ) {
    }

    public function id(): int
    {
        return (int) ($this->data['id'] ?? 0);
    }

    public function email(): string
    {
        return (string) ($this->data['email'] ?? '');
    }

    public function role(): string
    {
        return (string) ($this->data['role'] ?? 'client');
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public static function findById(\PDO $pdo, int $id): ?self
    {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? new self($row) : null;
    }

    public function isMenuFavori(\PDO $pdo, int $menuId): bool
    {
        require_once __DIR__ . '/../../includes/user-helpers.php';
        return isMenuFavori($pdo, $this->id(), $menuId);
    }

    public function dashboardStats(\PDO $pdo): array
    {
        require_once __DIR__ . '/../../includes/user-helpers.php';
        return getUserDashboardStats($pdo, $this->id());
    }
}
