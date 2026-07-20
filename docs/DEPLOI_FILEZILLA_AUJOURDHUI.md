# Deploiement prod aujourd'hui — FileZilla

> Site : https://vitegourmand.infinityfree.io/  
> Source locale : `C:\xampp\htdocs\vite-gourmand`

---

## Etape 1 — Connexion FileZilla (5 min)

1. Ouvrez **FileZilla**
2. **Fichier → Gestionnaire de sites → Nouveau site**
3. Renseignez (InfinityFree → Control Panel → **FTP Details**) :

| Champ | Valeur |
|-------|--------|
| Protocole | FTP |
| Hote | `ftpupload.net` (ou celui affiche) |
| Port | `21` |
| Type d'authentification | Normal |
| Utilisateur | `if0_XXXXXXX` |
| Mot de passe | votre mot de passe FTP |

4. **Connexion** → ouvrez le dossier distant **`htdocs`** (racine du site)

---

## Etape 2 — Upload (30–60 min)

### Option A — ZIP pre-genere (recommande)

1. Decompressez localement :
   ```
   C:\xampp\htdocs\vite-gourmand\deploy\vite-gourmand-prod.zip
   ```
2. Dans FileZilla :
   - **Gauche** : dossier decompresse
   - **Droite** : `htdocs` sur le serveur
3. Selectionnez **tout** sauf `includes/config.local.php` si deja present sur le serveur
4. Clic droit → **Envoyer**

### Option B — Dossier direct

- **Gauche** : `C:\xampp\htdocs\vite-gourmand`
- **Droite** : `htdocs`
- Glissez-deposez les dossiers ci-dessous

---

## Dossiers et fichiers OBLIGATOIRES

```
htdocs/
  admin/              (tout le dossier)
  assets/
    css/style.css
    images/           (toutes les .jpg + favicon.svg + .htaccess)
  includes/
    auth.php, db.php, footer.php, header.php, helpers.php
    menu-helpers.php, mongo.php, seo.php, user-helpers.php
    config.php, config.local.example.php
    (NE PAS ecraser config.local.php si deja sur le serveur)
  *.php a la racine  (index, menu, menus, commande, etc.)
```

### Images prioritaires (nouvelles / corrigees)

Verifier que ces fichiers sont bien sur le serveur dans `assets/images/` :

- brochettes-poulet.jpg, salade-quinoa.jpg, veloute-potimarron.jpg
- carpaccio-boeuf.jpg, tartare-saumon.jpg, terrine-canard.jpg
- veloute-champignons.jpg, filet-boeuf.jpg, poulet-halal.jpg
- risotto-champignons.jpg, risotto-truffes.jpg, curry.jpg
- poulet-basquaise.jpg, mousse-chocolat.jpg
- menu-entreprise.jpg, menu-classique.jpg, favicon.svg

### Fichiers PHP critiques (derniers correctifs)

| Fichier | Pourquoi |
|---------|----------|
| `menu.php` | Wizard RGAA + 3 entrees |
| `includes/menu-helpers.php` | Images cache-bust + presets |
| `includes/header.php`, `footer.php` | RGAA, navigation |
| `assets/css/style.css` | Styles wizard + focus |
| `valider-commande.php`, `modifier-commande.php` | Delais serveur |
| `cgv.php`, `mentions-legales.php` | Pages legales |
| `admin/*` | Back-office complet |

---

## NE PAS envoyer

| Exclure | Raison |
|---------|--------|
| `.git/` | Inutile en prod |
| `includes/config.local.php` | Deja sur le serveur (secrets BDD) |
| `docs/*.pdf`, `docs/*.odt` | Livrables ECF, pas le site |
| `database/` | Optionnel (SQL via phpMyAdmin) |
| `scripts/` | Local / MongoDB sync |
| `deploy/` | Archive locale |

---

## Etape 3 — Verifier config.local.php (2 min)

Sur le serveur : `htdocs/includes/config.local.php` doit exister avec vos identifiants MySQL InfinityFree.

Si absent, creez-le (copiez `config.local.example.php`) :

```php
<?php
return [
    'host'     => 'sql112.infinityfree.com',
    'dbname'   => 'if0_XXXXX_ViteEtGourmand',
    'user'     => 'if0_XXXXX',
    'password' => 'VOTRE_MOT_DE_PASSE',
];
```

---

## Etape 4 — SQL phpMyAdmin (apres upload, 10 min)

Ordre d'execution dans phpMyAdmin (base InfinityFree) :

1. `database/patch-prod-ecf-20260720.sql` — **prioritaire** (prix Menu Entreprise + categories boissons)
2. Si menus incomplets (0 plats) : `database/patch-sync-catalogue-local.sql`
3. Optionnel images : `database/patch-plat-images-complet.sql`

Ignorer les erreurs « Duplicate column » sur `categorie` / `description`.

---

## Etape 5 — Tests rapides (10 min)

| URL | Attendu |
|-----|---------|
| https://vitegourmand.infinityfree.io/ | Accueil + carousel |
| /menus.php | 14 menus avec images |
| /menu.php?id=14 | **3 entrees** Menu Entreprise |
| /admin/login.php | Connexion admin |
| Ctrl+F5 | Forcer rechargement sans cache |

Comptes test :
- Admin Jose : `jose@vite-gourmand.fr` / `Admin123!`
- Employe Julie : `julie@vite-gourmand.fr` / `Employe123!`
- Client : `client@vite-gourmand.fr` / `Client123!`
- Fallback : `admin@vite-gourmand.fr` / `Admin123!`

---

## Problemes frequents

| Symptome | Solution |
|----------|----------|
| Page blanche | Verifier `config.local.php` |
| Images anciennes | Ctrl+F5 ; verifier upload `assets/images/` |
| 2 entrees seulement | Executer `patch-menu-entreprise.sql` |
| 500 admin | Re-uploader `admin/index.php` + `includes/helpers.php` |

---

## Regenerer le ZIP de deploy

```powershell
powershell -ExecutionPolicy Bypass -File scripts\build-deploy-zip.ps1
```

Sortie : `deploy/vite-gourmand-prod.zip`
