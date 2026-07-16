# Deploiement via File Manager (InfinityFree)

> Preferer le File Manager au FTP : pas d extraction ZIP, limite 10 Mo/fichier.

## Fichiers modifies (session espace user + admin stats)

### Espace utilisateur
- `espace-utilisateur.php` — refonte complete (5 onglets)
- `includes/user-helpers.php` — **nouveau**
- `toggle-favori.php` — **nouveau**
- `mark-notifications.php` — **nouveau**
- `update-user.php` — CSRF + flash
- `menu.php` — bouton favori
- `includes/header.php` — badge notifications
- `valider-commande.php` — notification commande
- `admin/update-statut.php` — notifications statut

### Admin statistiques
- `admin/index.php` — KPI, graphiques CA/statuts/top menus
- `admin/partials/layout.php` — support Chart.js

### Base de donnees
- `database/migration-user-space.sql` — **nouveau** (favoris + notifications)

## Ordre SQL complet sur InfinityFree

1. `migration-v2-infinityfree.sql`
2. `catalogue-complet.sql`
3. `migration-user-space.sql` — **nouveau, obligatoire pour favoris/notifications**

## Fichiers modifies (session actuelle)

### Racine
- `commande.php` — panier conserve avant login, affichage boissons
- `menu.php` — selection boissons, badges regimes
- `valider-commande.php` — gsm sauvegarde, total boissons
- `a-propos.php` — **nouveau**
- `faq.php` — **nouveau**
- `index.php` — section chiffres cles
- `includes/menu-helpers.php` — helpers boissons
- `includes/header.php` — navigation enrichie
- `assets/css/style.css` — design system

### Admin
- `admin/admin-boissons.php` — **nouveau**
- `admin/partials/layout.php` — lien Boissons

### Base de donnees
- `database/migration-v2-infinityfree.sql` — **nouveau**
- `database/catalogue-complet.sql` — **nouveau**

### Supprimes (ne pas re-uploader)
- `create-menu.php`
- `toggle-user.php`
- `admin/test.php`

## Upload File Manager — methode

1. Connexion InfinityFree → **File Manager** → dossier `htdocs`
2. Naviguer vers le sous-dossier cible (`admin/`, `includes/`, etc.)
3. **Upload** → choisir **un fichier a la fois** (limite 10 Mo)
4. Ecraser l ancien fichier si demande
5. Repeter pour chaque fichier de la liste

## Reimport SQL

1. **phpMyAdmin** → base `if0_42418581_ViteEtGourmand`
2. Onglet **SQL** → coller le contenu de `migration-v2-infinityfree.sql` → Executer
3. Onglet **Importer** → `catalogue-complet.sql` → Executer

## Depannage rapide

| Probleme | Cause | Solution |
|----------|-------|----------|
| Page blanche `/admin/index.php` | Erreur SQL PHP | Uploader le nouveau `admin/index.php` + `includes/helpers.php` |
| Graphiques qui s etirent au scroll | Chart.js sans conteneur fixe | Uploader `admin/index.php` + `admin/partials/layout.php` |
| Favori ne fonctionne pas | Table `user_favoris` absente | Importer `migration-user-space.sql` |
| Message « Session expiree » | CSRF / session | Recharger la page menu puis re-cliquer |
| « Requete invalide » | Ancien `update-user.php` | Uploader le nouveau `update-user.php` |

| URL | Attendu |
|-----|---------|
| `/menus.php` | 14 menus |
| `/menu.php?id=1` | Section boissons visible |
| `/a-propos.php` | Page complete |
| `/faq.php` | Accordion FAQ |
| `/admin/admin-boissons.php` | CRUD boissons |

## Compte admin

- Email : `testadmin@test.com` / `Admin123!`
