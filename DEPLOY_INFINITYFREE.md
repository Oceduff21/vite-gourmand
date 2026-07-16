# InfinityFree — Etapes 4 et 5 (apres import SQL)

> Vous avez deja : compte, sous-domaine, base MySQL, import `schema-render.sql`

---

## Etape 4 — Envoyer les fichiers du site

### Methode A : Gestionnaire de fichiers (plus simple)

1. Connectez-vous sur **https://dash.infinityfree.com** (ou votre panel)
2. Cliquez **Control Panel** (VistaPanel) pour votre site
3. Ouvrez **Online File Manager** / **Gestionnaire de fichiers**
4. Allez dans le dossier **`htdocs`** (c'est la racine web = votre site)
5. **Supprimez** les fichiers par defaut (`index2.html`, etc.) s'il y en a
6. **Upload** tout le contenu de :
   ```
   C:\xampp\htdocs\vite-gourmand
   ```
   Fichiers et dossiers a envoyer :
   - `index.php`, `menu.php`, `commande.php`, etc. (tous les .php a la racine)
   - dossiers : `admin/`, `assets/`, `includes/`, `database/`, `scripts/`, `docs/`
   - **Ne pas envoyer** : `.git/`, `.env`, `includes/config.local.php` (a creer sur le serveur)

7. Structure finale dans `htdocs` :
   ```
   htdocs/
     index.php
     admin/
     assets/
     includes/
     ...
   ```

### Methode B : FileZilla (FTP)

1. Telechargez **FileZilla** : https://filezilla-project.org
2. Dans InfinityFree Control Panel → **FTP Details** / **Comptes FTP**
3. Notez :
   - **Host** : `ftpupload.net` (ou celui affiche)
   - **Username** : `if0_XXXXXXX`
   - **Password** : votre mot de passe FTP
   - **Port** : `21`
4. FileZilla → Fichier → Gestionnaire de sites → Nouveau site
5. Connectez-vous → ouvrez **`htdocs`**
6. A gauche (PC) : `C:\xampp\htdocs\vite-gourmand`
7. A droite (serveur) : `htdocs`
8. Glissez-deposez tous les fichiers (sauf `.git`)

---

## Etape 5 — Configurer la connexion MySQL

1. Control Panel → **MySQL Databases**
2. Notez ces 4 valeurs :

| InfinityFree | Exemple |
|--------------|---------|
| MySQL Hostname | `sql201.infinityfree.com` |
| MySQL Database Name | `if0_36291234_vite` |
| MySQL Username | `if0_36291234` |
| MySQL Password | (celui que vous avez choisi) |

3. Sur le serveur, dans **`htdocs/includes/`**, creez le fichier **`config.local.php`**

   Via File Manager : **New File** → nom : `config.local.php`

4. Collez ce contenu (avec VOS valeurs) :

```php
<?php
return [
    'host'     => 'sql201.infinityfree.com',
    'dbname'   => 'if0_36291234_vite',
    'user'     => 'if0_36291234',
    'password' => 'VotreMotDePasseMySQL',
];
```

5. **Save**

---

## Etape 6 — Tester

1. Ouvrez votre site : `https://votre-site.rf.gd` (ou .epizy.com)
2. Page d'accueil doit s'afficher
3. Admin : `https://votre-site.rf.gd/admin/login.php`
   - Email : `testadmin@test.com`
   - Mot de passe : `Admin123!`

---

## Problemes frequents

| Probleme | Solution |
|----------|----------|
| Page blanche | Verifiez `config.local.php` (host, nom BDD, user, pass) |
| 404 sur toutes pages | Fichiers pas dans `htdocs` mais dans un sous-dossier |
| Erreur connexion BDD | Host = `sqlXXX.infinityfree.com`, pas `localhost` |
| Images cassees | Dossier `assets/images/` bien uploade |
| MongoDB / stats admin vides | Normal sur InfinityFree (extension MongoDB absente) — le reste fonctionne |

---

## Lien pour la copie ECF

Collez dans l'ODT :
`https://votre-sous-domaine.rf.gd`

(exactement l'URL affichee dans InfinityFree → Account Details)
