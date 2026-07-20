# Architecture Front / Back — Vite & Gourmand

> Separation claire **presentation (HTML/JS)** vs **metier serveur (PHP/MySQL)** avec echanges **AJAX**.  
> Carte du depot + guide debug : [STRUCTURE.md](../STRUCTURE.md).

## Schema

```
┌──────────────────────────── FRONT ────────────────────────────┐
│  front/views/*.php     HTML (Vue MVC)                         │
│  front/js/*.js         JavaScript (UI, filtres, wizard)       │
│  assets/css/style.css  Styles                                 │
└──────────────────────┬────────────────────────────────────────┘
                       │  fetch() AJAX
                       ▼
┌──────────────────────────── BACK (MVC + POO) ─────────────────┐
│  *.php / api/*.php     Controleurs d'entree                   │
│  back/Controllers/     Classes controleur (Menu, Commande)    │
│  back/Models/          Classes metier POO                     │
│  includes/*.php        Helpers + securite (legacy encapsule)│
│  admin/*.php           Back-office                            │
│  database/*.sql        Schema MySQL                           │
└───────────────────────────────────────────────────────────────┘
```

## Arborescence

```
front/                          ← FRONT (HTML + JS = Vue)
├── js/site.js, menus-filter.js, menu-wizard.js, scroll-jump.js
└── views/menus-list.php

back/                           ← BACK POO / MVC
├── autoload.php
├── Models/Menu.php, Commande.php, User.php
└── Controllers/MenuController.php, CommandeController.php

api/                            ← Endpoints AJAX (deleguent aux Controllers)
└── load-menus.php

includes/                       ← Helpers + layout (encapsules par Models)
menus.php, menu.php             ← Points d'entree pages
admin/                          ← Back-office
```

## Flux AJAX + MVC (catalogue)

1. `menus.php` → `MenuController::indexPage()` → vue `front/views/menus-list.php` + JS.
2. JS `menus-filter.js` → `GET api/load-menus.php`.
3. API → `MenuController::ajaxList()` → `Menu::findAll()` / `filterAndSort()` / `renderCard()`.
4. Fragment HTML renvoye au navigateur.

```
[Vue JS] --fetch--> [MenuController] --POO--> [Menu Model] --PDO--> [MySQL]
```

## Flux commande (POO)

1. `menu.php` → `Menu::findById()` (modele).
2. `valider-commande.php` / `modifier-commande.php` → `CommandeController::applyFees()` + `Commande::validateDateLivraison()`.
3. `admin/update-statut.php` → `CommandeController::canTransition()` + `Commande::getStatuts()`.

## Classes POO (back/)

| Classe | Role |
|--------|------|
| `ViteGourmand\Models\Menu` | findById, findAll, filterAndSort, renderCard, platsByType |
| `ViteGourmand\Models\Commande` | reduction, livraison, statuts, transitions |
| `ViteGourmand\Models\User` | profil, favoris, stats espace client |
| `ViteGourmand\Controllers\MenuController` | page listing + API AJAX |
| `ViteGourmand\Controllers\CommandeController` | devis commande |

## Regle de separation

| Couche | Contient | Ne contient pas |
|--------|----------|-----------------|
| **Front JS** | DOM, events, `fetch`, validation UI | Requetes SQL, sessions |
| **Front views** | Markup HTML | INSERT commande, calcul livraison |
| **Models (POO)** | Acces donnees + regles metier | Markup HTML de page complete |
| **Controllers** | Orchestration requete → modele → vue | Styles CSS |
| **includes helpers** | Fonctions legacy (appelees par Models) | — |

## Pourquoi ce choix (ECF)

- Distinction visible Git : `front/` vs `back/` + `api/`
- Architecture AJAX + MVC/POO demontable en soutenance
- Migration progressive : helpers conserves, encapsules dans les classes
- Compat InfinityFree : pas de Composer obligatoire (`back/autoload.php`)
