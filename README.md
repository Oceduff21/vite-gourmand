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

- `/` — Site public
- `/admin/` — Back-office (admin + employe)
- `/includes/` — Header, footer, DB, auth
- `/database/` — SQL schema + migration

Voir [AUDIT.md](AUDIT.md) pour l'etat d'avancement ECF.


## MongoDB (NoSQL)

Les statistiques CA par menu sont stockees dans MongoDB (collection `commandes_stats`).
- Config : `includes/config.php`
- Sync : `php scripts/sync-mongo.php`
- Dashboard admin : graphiques MongoDB avec filtres menu/dates

## Docker

```bash
docker-compose up -d --build
```
Voir [DEPLOY.md](DEPLOY.md) pour le deploiement complet.

## Git workflow

- `main` : version stable livrable ECF
- `develop` : branche d'integration
- `feature/*` : developpements (ex. `feature/admin`)

Workflow : feature → merge dans `develop` → merge dans `main`.
