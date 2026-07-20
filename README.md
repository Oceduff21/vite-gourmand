# Vite & Gourmand

Application web PHP/MySQL de gestion de menus et commandes pour un service traiteur.

## Installation

1. Placer le projet dans `C:\xampp\htdocs\vite-gourmand`
2. Demarrer Apache et MySQL (XAMPP)
3. Importer la base :
   - Nouvelle install : `database/schema.sql`
   - Base existante : `database/migration.sql`
4. Acceder : http://localhost/vite-gourmand

## Comptes de demo

| Role | Email | Mot de passe |
|------|-------|--------------|
| Admin (Jose) | jose@vite-gourmand.fr | Admin123! |
| Employe (Julie) | julie@vite-gourmand.fr | Employe123! |
| Client | client@vite-gourmand.fr | Client123! |

Tri des comptes en BDD : executer `database/patch-cleanup-users.sql` dans phpMyAdmin.

## Stack

- PHP 8 + PDO
- MySQL / MariaDB
- Bootstrap 5.3
- JavaScript (filtres AJAX)

## Structure

Carte complete + guide debug : **[STRUCTURE.md](STRUCTURE.md)**  
Architecture Front/Back + MVC/POO + AJAX : **[docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)**

```
front/           Vues HTML + JavaScript (FRONT)
back/            Models + Controllers POO (BACK MVC)
api/             Endpoints AJAX
admin/           Back-office
includes/        Config, helpers, layout
assets/          CSS + images
database/        Schema SQL + patches
docs/            Documentation (.md) + docs/depot/ (meta Studi)
scripts/         Outils (PDF, ZIP, sync)
```

Meta depots Studi : [docs/depot/](docs/depot/) (paquets ZIP/PDF ignores par Git).


## MongoDB (NoSQL)

Les statistiques CA par menu sont stockees dans MongoDB (collection `commandes_stats`).
- Config : `includes/config.php`
- Sync : `php scripts/sync-mongo.php`
- Dashboard admin : graphiques MongoDB avec filtres menu/dates

## Docker

```bash
docker-compose up -d --build
```

Deploiement InfinityFree (FileZilla) : **[docs/DEPLOY.md](docs/DEPLOY.md)**

## Git workflow

- `main` : version stable livrable ECF
- `develop` : branche d'integration
- `feature/*` : developpements (ex. `feature/admin`)

Workflow : feature → merge dans `develop` → merge dans `main`.
