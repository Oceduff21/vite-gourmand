# Etape 3 — Initialiser la BDD (guide detaille)

> **Pourquoi ca bloque ?** Render ne propose **pas** de MySQL gratuit.
> Le fichier `render.yaml` initial avec `databases:` ne fonctionne pas en free.
> Solution : **MySQL sur db4free.net** + **site sur Render**.

---

## Partie A — Creer la BDD gratuite (db4free.net)

### 1. Inscription

1. Allez sur **https://db4free.net**
2. Cliquez **Sign up** (inscription gratuite)
3. Validez votre email

### 2. Creer la base

1. Connectez-vous sur db4free
2. **Create database** :
   - Database name : `vite_gourmand` (ou le nom propose)
3. Notez ces 4 infos (ecran d'accueil db4free) :

| Info | Exemple |
|------|---------|
| **Host** | `db4free.net` |
| **Database** | `vite_gourmand` |
| **Username** | `votre_user` |
| **Password** | `votre_mdp` |

### 3. Importer les tables (phpMyAdmin db4free)

1. Sur db4free, cliquez le lien **phpMyAdmin**
2. Connectez-vous avec user + password db4free
3. Cliquez la base `vite_gourmand` a gauche
4. Onglet **Importer** / **Import**
5. **Choisir un fichier** :
   ```
   C:\xampp\htdocs\vite-gourmand\database\schema-render.sql
   ```
   ⚠️ Utilisez **`schema-render.sql`** (PAS `schema.sql` qui contient CREATE DATABASE)
6. Cliquez **Executer** / **Go**

### 4. Verifier

Onglet **Structure** : vous devez voir ~12 tables (`users`, `menus`, `commandes`, etc.)

---

## Partie B — Relier Render a db4free

1. Render Dashboard → service **vite-gourmand** (Web)
2. Menu **Environment** (Variables d'environnement)
3. Ajoutez ou modifiez :

| Key | Value (vos infos db4free) |
|-----|---------------------------|
| `DB_HOST` | `db4free.net` |
| `DB_NAME` | `vite_gourmand` |
| `DB_USER` | votre username db4free |
| `DB_PASS` | votre password db4free |
| `MONGO_URI` | URI MongoDB Atlas (etape 1 DEPLOY.md) |

4. **Save Changes** → Render redéploie automatiquement (2-3 min)

---

## Partie C — Tester

1. Ouvrez l'URL Render : `https://vite-gourmand-xxxx.onrender.com`
2. Attendez ~1 min au premier chargement (plan free)
3. Testez `/admin/login.php` :
   - Email : `testadmin@test.com`
   - Mot de passe : `Admin123!`

---

## Erreurs frequentes

| Erreur | Solution |
|--------|----------|
| `#1044 - Access denied CREATE DATABASE` | Vous avez importe `schema.sql` au lieu de **`schema-render.sql`** |
| `Erreur connexion` sur le site | Verifiez DB_HOST, DB_USER, DB_PASS dans Render Environment |
| Site lent / timeout 1ere visite | Normal sur Render free (reveil ~60 sec) |
| Blueprint echoue sur `databases` | Utilisez le nouveau `render.yaml` (sans section databases) |

---

## Alternative plus simple : Railway

Si db4free bloque aussi :

1. https://railway.app → New Project → Deploy from GitHub
2. Repo `vite-gourmand`
3. **+ New** → **Database** → **MySQL** (integre, pas de config externe)
4. Variables auto-liees au service web
5. Console Railway → executer `schema-render.sql`
