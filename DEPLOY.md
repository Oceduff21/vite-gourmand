# Deploiement Vite & Gourmand

## Option 1 : Docker (recommande pour ECF)

```bash
docker-compose up -d --build
```

- Site : http://localhost:8080
- MySQL : port 3307
- MongoDB : port 27017

Synchroniser les commandes existantes vers MongoDB :
```bash
docker-compose exec web php scripts/sync-mongo.php
```

## Option 2 : XAMPP local

1. Importer `database/migration.sql` dans phpMyAdmin
2. Activer l'extension PHP `mongodb` dans php.ini
3. Installer MongoDB Community : https://www.mongodb.com/try/download/community
4. Lancer `php scripts/sync-mongo.php`

## Option 3 : Railway / Render

1. Creer un projet avec Dockerfile
2. Ajouter services MySQL et MongoDB (addons)
3. Variables d'environnement :
   - `MONGO_URI=mongodb+srv://...`
4. Deploy depuis GitHub

## Comptes test

- Admin : testadmin@test.com / Admin123!
- Admin (copie ECF) : admin@vite-gourmand.fr / Admin123!
