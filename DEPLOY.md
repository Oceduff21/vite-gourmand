# Deploiement Vite & Gourmand

## Option 1 : Render.com (recommande ECF — en ligne)

### Etape 1 — MongoDB Atlas (gratuit)

1. Creer un compte sur https://www.mongodb.com/cloud/atlas
2. Cluster **M0 Free** → region proche (Frankfurt)
3. Database Access : utilisateur + mot de passe
4. Network Access : `0.0.0.0/0` (demo ECF)
5. Copier l'URI : `mongodb+srv://user:pass@cluster.mongodb.net/vite_gourmand`

### Etape 2 — Render

1. Compte sur https://render.com
2. **New > Blueprint**
3. Connecter GitHub → repo `Oceduff21/vite-gourmand` branche `main`
4. Render detecte `render.yaml` (Web + MySQL)
5. Variable manuelle : `MONGO_URI` = URI Atlas
6. Deploy (5-10 min)

### Etape 3 — Initialiser la BDD

1. Render Dashboard → base MySQL → onglet **Connect**
2. Importer `database/schema.sql` via client MySQL ou phpMyAdmin distant
3. Ou SSH/console : executer le SQL

### Etape 4 — URL publique

Render fournit : `https://vite-gourmand.onrender.com` (exemple)

**Coller ce lien dans la copie ODT ECF.**

### Comptes test apres deploy

- Admin : testadmin@test.com / Admin123!
- Creer via SQL INSERT ou migration.sql

---

## Option 2 : Docker local

```bash
docker-compose up -d --build
```

- Site : http://localhost:8080
- MySQL : port 3307
- MongoDB : port 27017

Variables web (docker-compose) :
- `DB_HOST=mysql`, `DB_USER=root`, `DB_PASS=root`, `DB_NAME=vite_gourmand`
- `MONGO_URI=mongodb://mongo:27017`

Sync MongoDB :
```bash
docker-compose exec web php scripts/sync-mongo.php
```

---

## Option 3 : XAMPP local

1. Importer `database/migration.sql` dans phpMyAdmin
2. Extension PHP `mongodb` dans php.ini
3. MongoDB Community local
4. http://localhost/vite-gourmand

---

## Variables d'environnement

| Variable | Description | Local |
|----------|-------------|-------|
| DB_HOST | Hote MySQL | localhost |
| DB_NAME | Nom BDD | vite_gourmand |
| DB_USER | Utilisateur | root |
| DB_PASS | Mot de passe | (vide) |
| MONGO_URI | URI MongoDB | mongodb://localhost:27017 |

Copier `.env.example` en `.env` pour la prod.

---

## Comptes test

- Admin : testadmin@test.com / Admin123!
- Admin (copie ECF) : admin@vite-gourmand.fr / Admin123!

---

## Livrables ECF

Voir [COPIE_ECF.md](COPIE_ECF.md) et dossier [docs/](docs/) pour la copie ODT et les PDF.
