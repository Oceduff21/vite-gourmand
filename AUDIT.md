# Audit ECF — Vite & Gourmand

> Date : 15 juillet 2026 | Echeance livraison : 23 juillet 2026

## Resume executif

Projet PHP/MySQL traiteur avec base UI solide (Bootstrap, filtres AJAX, espace admin). **Correctifs P0 appliques le 15/07/2026** : schema SQL unifie, footer JS repare, flux commande reconnecte, endpoints securises, acces employe ouvert.

**Conformite estimee : ~55%** (apres correctifs P0). Reste : MongoDB, deploiement, RGAA, emails, statuts complets, livrables PDF.

---

## 1. Fonctionnalites realisees

| Domaine | Statut | Fichiers |
|---------|--------|----------|
| Page accueil (presentation, equipe, avis) | OK | index.php |
| Navigation + footer (CGV, mentions, horaires) | OK | includes/header.php, footer.php |
| Liste menus + filtres AJAX dynamiques | OK | menus.php, load-menus.php |
| Detail menu + plats | OK | menu.php |
| Inscription / connexion | OK | register.php, login.php |
| Commande complete (formulaire + validation) | OK (corrige) | commande.php, valider-commande.php |
| Espace utilisateur | OK | espace-utilisateur.php |
| Annulation / modification commande | OK | annuler-commande.php, modifier-commande.php |
| Suivi commande | Partiel | suivi-commande.php |
| Avis + moderation admin | OK | avis.php, admin/admin-avis.php |
| Admin (dashboard, commandes, menus, users, avis) | OK | admin/* |
| Acces employe | OK (corrige) | admin/partials/layout.php |
| PDO + prepared statements | OK | includes/db.php |

---

## 2. Elements manquants

### Critiques (avant 23 juillet)
- [ ] **Deploiement en ligne** (lien vide dans copie a rendre = rejet possible)
- [ ] **MongoDB** obligatoire (seul stats.json present)
- [ ] **Mot de passe oublie** + email reset
- [ ] **Email bienvenue** inscription
- [ ] **Contact** : formulaire inerte (contact.php)
- [ ] **Workflow statuts complet** (7 statuts CDC + historique)
- [ ] **RGAA** accessibilite

### Importants
- [ ] Galerie images par menu, allergenes affiches
- [ ] CA par menu avec filtres (admin)
- [ ] Validation mot de passe (10 car., complexite)
- [ ] Manuel PDF, charte graphique, doc technique
- [ ] Motif annulation employe avec mode contact

---

## 3. Bugs corriges (15/07/2026)

| Bug | Correction |
|-----|------------|
| footer.php JS `$menu` sur toutes pages | JS prix isole dans commande.php |
| admin-commandes.php footer dans boucle | Footer deplace hors foreach |
| commande.php INSERT incomplet | Formulaire commande complet |
| valider-commande.php orphelin | Relie au formulaire + statut en_attente |
| delete/update-statut.php sans auth | Redirection admin securisee |
| Statuts `en attente` vs `en_attente` | Migration SQL + code normalise |
| Employe bloque admin | layout.php autorise admin + employe |
| confirmation.php IDOR | Filtre user_id + login requis |
| admin/login.php HTML casse | Nettoyage + check is_active |

---

## 4. Risques perte de points

| Risque | Niveau | Action |
|--------|--------|--------|
| Lien deploiement vide (copie ODT) | CRITIQUE | Deployer + mettre a jour ODT |
| MongoDB absent | Eleve | Integrer MongoDB pour stats |
| Stack declaree (Netlify) vs PHP | Eleve | Corriger copie a rendre |
| RGAA non traite | Eleve | alt, aria, contrastes |
| Emails non fonctionnels (XAMPP) | Moyen | Configurer SMTP ou Mailtrap |
| Livrables PDF absents | Eleve | Manuel, charte, doc technique |

---

## 5. Roadmap priorisee (23 juillet)

### P1 — 16-18 juillet
1. Formulaire contact + envoi email
2. Mot de passe oublie + email bienvenue
3. Validation mot de passe inscription
4. Statuts commande complets + commande_historique
5. Suivi commande avec dates

### P2 — 19-20 juillet
6. MongoDB + graphiques CA par menu
7. RGAA (alt images, labels, navigation clavier)
8. CSRF tokens formulaires
9. Conditions menu + allergenes affiches

### P3 — 21-23 juillet
10. Deploiement (Railway/Fly.io/Render)
11. Mettre a jour copie ODT (lien deploy, stack reelle)
12. README + manuel PDF + charte graphique
13. Tests parcours complets

---

## Comptes de test

| Role | Email | Mot de passe |
|------|-------|--------------|
| Admin | admin@vite-gourmand.fr | Admin123! |
| Utilisateur | test@test.com | (existant en BDD) |

---

## Installation locale

1. XAMPP : Apache + MySQL
2. Importer `database/schema.sql` ou `database/migration.sql`
3. Acceder a http://localhost/vite-gourmand


## Correctifs P1 appliques (15/07/2026)

- contact.php : formulaire fonctionnel (titre, nom, email, description) + envoi email
- register.php : validation mot de passe CDC + email bienvenue
- mot-de-passe-oublie.php + reinitialiser-mot-de-passe.php : reset par token
- includes/helpers.php : sendMail, validatePassword, statuts, historique
- admin/admin-commandes.php : filtres statut/client + changement statut
- admin/annuler-commande.php : annulation avec motif et mode contact
- admin/update-statut.php : 8 statuts + emails terminee/materiel
- suivi-commande.php : timeline avec dates depuis commande_historique
