# Dossier ECF — guide de depot final

> **Echeance : 23 juillet 2026**  
> **Projet :** Vite & Gourmand — traiteur en ligne  
> **Prod :** https://vitegourmand.infinityfree.io/  
> **Git :** https://github.com/Oceduff21/vite-gourmand

---

## 1. Contenu a envoyer au jury

| Piece | Fichier | Statut |
|-------|---------|--------|
| Copie a rendre (ODT) | `docs/Copie_a_rendre_TP_Vite_Gourmand.odt` | A verifier / imprimer |
| Manuel utilisateur (PDF) | `docs/MANUEL_UTILISATEUR.pdf` | Pret |
| Charte graphique (PDF) | `docs/CHARTE_GRAPHIQUE.pdf` | Pret |
| Documentation technique (PDF) | `docs/DOCUMENTATION_TECHNIQUE.pdf` | Pret |
| Lien deploiement (dans ODT) | https://vitegourmand.infinityfree.io/ | OK |

Sources Markdown (si re-PDF necessaire) : `docs/MANUEL_UTILISATEUR.md`, `CHARTE_GRAPHIQUE.md`, `DOCUMENTATION_TECHNIQUE.md`

---

## 2. Stack a declarer (copier dans l'ODT)

| Element | Valeur |
|---------|--------|
| Front | HTML5, CSS3, JavaScript, Bootstrap 5.3, Chart.js |
| Back-end | PHP 8.3, PDO, sessions, CSRF |
| BDD relationnelle | MySQL (InfinityFree) |
| BDD NoSQL | MongoDB (stats admin — local + fallback MySQL en prod) |
| Hebergement | **InfinityFree** |
| Local | XAMPP + GitHub |

**Phrase type :**  
*Application web PHP/MySQL de commande traiteur. Front Bootstrap 5, back-office admin/employe, authentification, commandes, avis moderes. MySQL en production sur InfinityFree. MongoDB pour les statistiques (demo locale). GitHub : github.com/Oceduff21/vite-gourmand*

---

## 3. Comptes de demonstration

| Role | Email | Mot de passe |
|------|-------|--------------|
| Admin (Jose) | jose@vite-gourmand.fr | Admin123! |
| Employe (Julie) | julie@vite-gourmand.fr | Employe123! |
| Client | client@vite-gourmand.fr | Client123! |

Script SQL : `database/patch-cleanup-users.sql`

---

## 4. Dernier deploiement prod (A FAIRE si pas fait aujourd'hui)

### 4.1 Regenerer le ZIP (deja fait le 17/07/2026)

```powershell
powershell -ExecutionPolicy Bypass -File scripts\build-deploy-zip.ps1
```

Fichier : `deploy/vite-gourmand-prod.zip` (~105 Mo, 196 fichiers)

### 4.2 Upload FileZilla

Guide detaille : `docs/DEPLOI_FILEZILLA_AUJOURDHUI.md`

1. Connexion FTP InfinityFree → dossier **`htdocs`**
2. Decompresser le ZIP localement
3. Envoyer **tout le contenu** vers `htdocs`
4. **Ne pas ecraser** `includes/config.local.php` sur le serveur
5. Mode transfert : **Binaire**
6. Ctrl+F5 sur le navigateur apres upload

### 4.3 SQL phpMyAdmin (si pas deja fait)

Executer dans l'ordre :

1. `database/patch-menu-entreprise-prix.sql` — prix Menu Entreprise
2. `database/patch-site-settings.sql` — horaires footer
3. `database/patch-plat-images-complet.sql` — images plats (si images manquantes)

---

## 5. Parcours test obligatoire (20 min)

Cochez apres verification sur **https://vitegourmand.infinityfree.io/**

### Site public
- [ ] Accueil : carousel, menus, avis, pas d'erreur WAVE bloquante
- [ ] `/menus.php` : filtres AJAX, images visibles
- [ ] `/menu.php?id=14` : 3 entrees, **prix affiche** (pas 0 EUR)
- [ ] `/register.php` : inscription avec adresse complete
- [ ] `/login.php` : connexion → « Mon espace »
- [X] Commande complete : wizard → recap → livraison → confirmation
- [ ] `/espace-utilisateur.php` : commande visible
- [ ] `/accessibilite.php` : page accessibilite

### Admin
- [ ] `/admin/login.php` : jose@vite-gourmand.fr / Admin123!
- [ ] Dashboard : graphiques + KPI
- [ ] Commandes : accepter → preparation → livraison → terminee
- [X] Avis : moderer un avis client
- [ ] (Optionnel) Creer compte employe Jose

### RGAA (capture pour dossier)
- [ ] Extension WAVE sur accueil : 0 erreur (contrastes corriges le 17/07)
- [ ] Navigation clavier Tab sur login + menu

Detail : `docs/TESTS_PROD_ECF.md`

---

## 6. Soutenance — points cles

| Sujet | Reponse preparee |
|-------|------------------|
| Hebergement | InfinityFree (gratuit PHP/MySQL) |
| MongoDB absent en prod | Fallback MySQL ; demo NoSQL en local avec `scripts/sync-mongo.php` |
| RGAA | ~90 %, page accessibilite.php, audit WAVE/axe interne |
| Securite | PDO prepared statements, CSRF, mots de passe hashes, roles admin/employe/client |
| Git | Branche main sur GitHub, historique de commits |

---

## 7. Checklist finale avant envoi (23 juillet)

- [ ] ZIP uploade sur InfinityFree
- [ ] SQL patches executes
- [ ] Parcours test valide (section 5)
- [ ] ODT complete (URL + stack + votre nom)
- [ ] 3 PDF joints au dossier papier / numerique
- [ ] GitHub public a jour (optionnel mais recommande)
- [ ] Capture ecran WAVE ou prod pour annexe (bonus)

---

## 8. Fichiers modifies recemment (priorite upload)

```
index.php, includes/header.php, includes/footer.php
includes/a11y-helpers.php, includes/helpers.php, assets/css/style.css
accessibilite.php, menu.php, espace-utilisateur.php
admin/* (layout, dashboards, commandes, avis, users)
```

---

*Derniere mise a jour : 17 juillet 2026*
