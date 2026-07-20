# Deploiement — InfinityFree (FileZilla)

> Guide **unique** de mise en prod.  
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

1. Regenerer si besoin :
   ```powershell
   powershell -ExecutionPolicy Bypass -File scripts\build-deploy-zip.ps1
   ```
2. Decompressez localement `deploy\vite-gourmand-prod.zip`
3. FileZilla : gauche = dossier decompresse, droite = `htdocs`
4. Envoyez **tout** sauf `includes/config.local.php` s'il existe deja sur le serveur

### Option B — Dossier direct

Glissez depuis `C:\xampp\htdocs\vite-gourmand` vers `htdocs` (meme exclusions).

---

## Contenu obligatoire

```
htdocs/
  front/              JS + views
  back/               Models + Controllers POO
  api/                Endpoints AJAX
  admin/
  assets/css/ + assets/images/
  includes/           (NE PAS ecraser config.local.php)
  *.php               Pages a la racine
```

## Ne pas envoyer

| Exclure | Raison |
|---------|--------|
| `.git/` | Inutile en prod |
| `includes/config.local.php` | Secrets deja sur le serveur |
| `docs/`, `dossier-*`, `PRET_A_ENVOYER/` | Livrables ECF / Studi |
| `database/` | SQL via phpMyAdmin |
| `scripts/`, `deploy/` | Outils locaux |

---

## Etape 3 — config.local.php

Sur le serveur, `htdocs/includes/config.local.php` doit contenir les identifiants MySQL InfinityFree (copier depuis `config.local.example.php` si besoin).

---

## Etape 4 — SQL phpMyAdmin

1. `database/patch-prod-ecf-20260720.sql` (prioritaire)
2. Si menus incomplets : `database/patch-sync-catalogue-local.sql`
3. Optionnel images : `database/patch-plat-images-complet.sql`

Ignorer « Duplicate column ».

---

## Etape 5 — Tests

| URL | Attendu |
|-----|---------|
| https://vitegourmand.infinityfree.io/ | Accueil |
| `/menus.php` | Catalogue + filtres AJAX |
| `/front/js/site.js` | 200 |
| `/api/load-menus.php` | Fragment menus |
| `/menu.php?id=14` | Wizard + 3 entrees |
| `/admin/login.php` | Connexion |

Ctrl+F5. Comptes demo : voir `README.md`.

---

## Problemes frequents

| Symptome | Solution |
|----------|----------|
| Page blanche | Verifier `config.local.php` |
| Menus / Auto / JS HS | Re-uploader `front/`, `back/`, `api/` |
| Images anciennes | Ctrl+F5 + `assets/images/` |
| Catalogue incomplet | Relancer patch-sync SQL |
