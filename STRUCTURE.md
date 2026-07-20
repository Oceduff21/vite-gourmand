# Structure du depot — Vite & Gourmand

Carte pour naviguer et deboguer rapidement. L’application tourne depuis la racine (`http://localhost/vite-gourmand`).

## Vue d’ensemble

```
vite-gourmand/                 ← DOCUMENT ROOT (ne pas deplacer les *.php)
│
├── front/                     FRONT — vues HTML + JavaScript
│   ├── js/                    site.js, menus-filter, menu-wizard…
│   └── views/                 fragments PHP (ex. menus-list.php)
│
├── back/                      BACK — POO / MVC
│   ├── autoload.php           namespace ViteGourmand\
│   ├── Models/                Menu, Commande, User
│   └── Controllers/           MenuController, CommandeController
│
├── api/                       Endpoints AJAX (JSON/HTML fragments)
├── admin/                     Back-office
├── includes/                  Config, helpers, layout, securite
├── assets/                    CSS + images produit
├── database/                  schema.sql, migrations, patches
├── scripts/                   Outils (PDF, ZIP prod, sync Mongo…)
│
├── docs/                      Documentation source (.md) + meta ECF
├── dossier-projet/            Sources dossier projet Studi
├── dossier-professionnel/     Sources dossier professionnel Studi
│
├── *.php                      Pages d’entree publiques (garder a la racine)
├── README.md                  Installation / comptes demo
├── STRUCTURE.md               ← ce fichier
└── docs/ARCHITECTURE.md       Separation Front / Back / AJAX / MVC
```

## Ou chercher un bug ?

| Symptome | Regarder d’abord |
|----------|------------------|
| Catalogue / filtres menus | `front/js/menus-filter.js` → `api/load-menus.php` → `back/Controllers/MenuController.php` → `back/Models/Menu.php` |
| Fiche menu / wizard | `menu.php` + `front/js/menu-wizard.js` + `includes/menu-helpers.php` |
| Validation commande / prix | `valider-commande.php` → `back/Controllers/CommandeController.php` → `back/Models/Commande.php` |
| Statuts admin | `admin/update-statut.php` → `CommandeController::canTransition()` |
| Connexion BDD | `includes/db.php`, `includes/config.php`, `includes/config.local.php` (local, ignore) |
| Styles | `assets/css/style.css` |
| SQL / catalogue | `database/` (patches nommes `patch-*.sql`) |

## Ce qui n’est PAS dans Git (volontaire)

| Ignoré | Pourquoi |
|--------|----------|
| `deploy/`, `*.zip` | Archives de deploiement regenerables |
| `PRET_A_ENVOYER/`, `ECF_A_RENDRE/` | Paquets Studi regenerables (`scripts/prepare-*.ps1`) |
| `*.pdf`, `*.odt`, `*.docx` | Exports generes depuis les `.md` |
| `.env`, `includes/config.local.php` | Secrets locaux |
| `dossier-professionnel/_*.txt` | Dumps temporaires |

Regenerer : `scripts/generate-pdfs.py`, `scripts/prepare-pret-a-envoyer.py`, `scripts/build-deploy-zip.ps1`.

Meta Studi (checklist, elements a fournir) : voir `docs/depot/`.

## Branches

- `main` — version stable
- `develop` — integration (si utilisee)
- `feature/*` — travaux en cours
