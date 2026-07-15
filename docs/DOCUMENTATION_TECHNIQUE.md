# Documentation technique — Vite & Gourmand

## Architecture

- **Front** : PHP + Bootstrap 5 + JavaScript (filtres AJAX)
- **Back** : PHP 8.2, PDO, sessions
- **BDD relationnelle** : MySQL (commandes, users, menus)
- **NoSQL** : MongoDB (statistiques CA)
- **Deploiement** : Docker / Render / XAMPP

## Structure

```
/                 Site public
/admin/           Back-office
/includes/        db.php, auth, helpers, mongo
/database/        schema.sql, migration.sql
/scripts/         sync-mongo.php
```

## Base MySQL — tables principales

| Table | Role |
|-------|------|
| users | Clients, employes, admins |
| menus / plats / menu_options | Catalogue |
| commandes | Commandes |
| commande_details | Choix plats par commande |
| commande_historique | Timeline statuts |
| avis | Avis moderes |
| password_resets | Reset mot de passe |

## MongoDB

- Collection `commandes_stats`
- Sync a chaque commande + script `scripts/sync-mongo.php`
- Config : `includes/config.php`, `MONGO_URI`

## Securite

- PDO prepared statements
- CSRF sur formulaires sensibles
- Auth admin/employe sur back-office
- Validation mot de passe CDC (10 car., complexite)

## Deploiement

Voir [DEPLOY.md](../DEPLOY.md) et [render.yaml](../render.yaml).

Variables d'environnement : `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `MONGO_URI`.
