<?php

declare(strict_types=1);

namespace ViteGourmand\Models;

/**
 * Modele Menu — couche metier catalogue (POO).
 * Encapsule les helpers proceduraux existants (menu-helpers.php).
 */
class Menu
{
    public function __construct(
        private readonly array $data
    ) {
    }

    public function id(): int
    {
        return (int) ($this->data['id'] ?? 0);
    }

    public function titre(): string
    {
        return (string) ($this->data['titre'] ?? '');
    }

    public function prix(): float
    {
        return (float) ($this->data['prix'] ?? 0);
    }

    public function minPersonnes(): int
    {
        return (int) ($this->data['min_personnes'] ?? 1);
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public static function findById(\PDO $pdo, int $id): ?self
    {
        $stmt = $pdo->prepare('SELECT * FROM menus WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? new self($row) : null;
    }

    /** @return list<self> */
    public static function findAll(\PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT * FROM menus ORDER BY id DESC');
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        return array_map(static fn(array $row): self => new self($row), $rows);
    }

    /**
     * Filtre + tri pour le listing (delegue aux helpers).
     *
     * @param list<self|array> $menus
     * @return list<self>
     */
    public static function filterAndSort(\PDO $pdo, array $menus, array $params): array
    {
        require_once __DIR__ . '/../../includes/menu-helpers.php';

        $asArrays = array_map(static function ($m): array {
            return $m instanceof self ? $m->toArray() : $m;
        }, $menus);

        $filtered = filterMenusForListing($asArrays, $params);
        $sorted = sortMenusForListing($filtered, (string) ($params['sort'] ?? ''), $pdo);

        return array_map(static fn(array $row): self => new self($row), $sorted);
    }

    public function renderCard(): string
    {
        require_once __DIR__ . '/../../includes/menu-helpers.php';
        return renderMenuCard($this->data);
    }

    /** Options plats groupees par type (entree/plat/dessert). */
    public function platsByType(\PDO $pdo): array
    {
        require_once __DIR__ . '/../../includes/menu-helpers.php';
        return getMenuPlatsByType($pdo, $this->id());
    }

    public function hasOptions(\PDO $pdo): bool
    {
        $group = $this->platsByType($pdo);
        return array_sum(array_map('count', $group)) > 0;
    }
}
