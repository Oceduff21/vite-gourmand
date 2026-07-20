# Back-end POO / MVC

Namespace : `ViteGourmand\`

```
back/
├── autoload.php              Autoload classes
├── Models/
│   ├── Menu.php              Catalogue menus
│   ├── Commande.php          Regles commandes
│   └── User.php              Utilisateur / favoris
└── Controllers/
    ├── MenuController.php    Listing + API AJAX
    └── CommandeController.php Devis / transitions
```

## Mapping MVC

| Couche | Role | Exemple |
|--------|------|---------|
| **Modele** | Donnees + regles metier | `Menu::findById()`, `Commande::calculateLivraison()` |
| **Controleur** | Orchestre requete → modele → vue/API | `MenuController::ajaxList()` |
| **Vue** | HTML front | `front/views/menus-list.php` |

Les modeles encapsulent les fonctions existantes de `includes/*-helpers.php` (migration progressive, pas de double logique).

## Points d'entree branches

| Fichier | Classes utilisees |
|---------|-------------------|
| `menus.php`, `api/load-menus.php` | `MenuController`, `Menu` |
| `menu.php` | `Menu` |
| `valider-commande.php`, `modifier-commande.php` | `CommandeController`, `Commande`, `Menu` |
| `admin/update-statut.php` | `CommandeController`, `Commande` |

## Utilisation

```php
require_once 'back/autoload.php';
use ViteGourmand\Controllers\MenuController;

$controller = new MenuController($pdo);
$controller->ajaxList($_GET);
```
