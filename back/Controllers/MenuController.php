<?php

declare(strict_types=1);

namespace ViteGourmand\Controllers;

use ViteGourmand\Models\Menu;

/**
 * Controleur Menus (MVC).
 * Orchestre Modele Menu + vues front / reponses AJAX.
 */
class MenuController
{
    public function __construct(
        private readonly \PDO $pdo
    ) {
    }

    /**
     * API AJAX : fragment HTML des cartes (filtres).
     */
    public function ajaxList(array $params): void
    {
        header('Content-Type: text/html; charset=UTF-8');
        header('X-Content-Type-Options: nosniff');

        require_once __DIR__ . '/../../includes/menu-helpers.php';

        $menus = Menu::findAll($this->pdo);
        $menus = Menu::filterAndSort($this->pdo, $menus, $params);
        $count = count($menus);

        $countHtml = '<p class="menus-results-count col-12" role="status" aria-live="polite">'
            . ($count === 0
                ? 'Aucun menu ne correspond a vos criteres.'
                : $count . ' menu' . ($count > 1 ? 's' : '') . ' trouve' . ($count > 1 ? 's' : '') . '.')
            . '</p>';

        $badgesHtml = renderMenuFilterBadges($params);

        if ($count === 0) {
            echo $badgesHtml;
            echo $countHtml;
            echo '<div class="col-12"><p class="text-center text-muted py-4 mb-0">Essayez un autre mot-cle (ex. anniversaire, vegan) ou elargissez votre budget.</p></div>';
            return;
        }

        echo $badgesHtml;
        echo $countHtml;

        foreach ($menus as $menu) {
            echo $menu->renderCard();
        }
    }

    /**
     * Page listing : rend la vue HTML front + scripts.
     */
    public function indexPage(): void
    {
        $pageTitle = 'Nos menus traiteur — Vite & Gourmand';
        $pageDescription = 'Parcourez nos menus evenementiels : mariage, Noel, business, vegan, enfant. Filtrez par theme, regime et budget.';

        include __DIR__ . '/../../includes/header.php';
        require __DIR__ . '/../../front/views/menus-list.php';
        echo '<script src="front/js/menus-filter.js?v=20260720c"></script>';
        include __DIR__ . '/../../includes/footer.php';
    }

    public function find(int $id): ?Menu
    {
        return Menu::findById($this->pdo, $id);
    }
}
