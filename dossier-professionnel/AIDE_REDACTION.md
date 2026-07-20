# Aide rédaction — Dossier Professionnel

> **Candidat :** Océane Virginie Erica Duffour  
> **Projet de référence :** Vite & Gourmand (formation Studi, fév.–juil. 2026)  
> **Modalité :** ☑ Parcours de formation  

Copiez ces éléments dans les **fiches du modèle Word Studi** (une fiche = un exemple de pratique).

---

## Informations page de garde

| Champ | Valeur |
|-------|--------|
| Nom de naissance | DUFFOUR |
| Prénom | Océane Virginie Erica |
| Titre visé | Développeur web et web mobile |
| Modalité | Parcours de formation |

*(Compléter votre adresse personnelle dans le Word.)*

---

# ACTIVITÉ TYPE 1 — Front-end sécurisé

## Exemple 1 — Maquetter des interfaces utilisateur

| Champ modèle Word | Contenu suggéré |
|-------------------|-----------------|
| **Contexte** | Projet de formation ECF — site e-commerce traiteur « Vite & Gourmand » (Bordeaux). Travail individuel, cahier des charges Studi. |
| **Période** | Mars 2026 |
| **Description** | Conception de wireframes desktop et mobile (accueil, catalogue menus, fiche menu avec wizard de commande). Respect de la charte graphique : rouge #c0392b, or #c9a227, Bootstrap 5. |
| **Moyens / outils** | Papier/numérique, Figma ou wireframes Markdown, charte `docs/CHARTE_GRAPHIQUE.pdf` |
| **Preuves / documents** | Maquettes annexe A ; captures prod https://vitegourmand.infinityfree.io/ |

---

## Exemple 2 — Réaliser des interfaces statiques

| Champ | Contenu suggéré |
|-------|-----------------|
| **Contexte** | Même projet — pages publiques du site traiteur |
| **Période** | Mars–Avril 2026 |
| **Description** | Intégration HTML5 sémantique + Bootstrap 5 : accueil (carousel hero), `menus.php`, pages légales (CGV, RGPD, accessibilité). CSS custom `assets/css/style.css`. Responsive mobile-first. |
| **Moyens** | HTML, CSS3, Bootstrap 5.3, VS Code/Cursor |
| **Preuves** | `index.php`, `accessibilite.php`, `assets/css/style.css` ; capture desktop + mobile |

**Sécurité front :** validation HTML5, échappement `htmlspecialchars()`, labels formulaires (RGAA).

---

## Exemple 3 — Développer la partie dynamique des interfaces

| Champ | Contenu suggéré |
|-------|-----------------|
| **Contexte** | Parcours commande client — fonctionnalité centrale du site |
| **Période** | Avril–Mai 2026 |
| **Description** | Wizard JavaScript 6 étapes sur `menu.php` (invités, entrées, plats, desserts, boissons, récap). Filtres AJAX sur `menus.php` via `load-menus.php`. Attributs ARIA, navigation clavier. |
| **Moyens** | JavaScript ES6+, Fetch API, PHP ( endpoints AJAX ) |
| **Preuves** | `menu.php`, `load-menus.php`, `includes/menu-helpers.php` (filtres) ; capture wizard |

**Sécurité :** token CSRF sur soumission wizard ; validation serveur `validatePlatSelection()`.

---

# ACTIVITÉ TYPE 2 — Back-end sécurisé

## Exemple 1 — Mettre en place une base de données relationnelle

| Champ | Contenu suggéré |
|-------|-----------------|
| **Contexte** | Modélisation données traiteur (catalogue, commandes, utilisateurs) |
| **Période** | Mars 2026 |
| **Description** | Conception schéma MySQL : tables `users`, `menus`, `plats`, `menu_options`, `commandes`, `commande_details`, `commande_historique`, `avis`. Relations FK, index, encodage UTF-8. Scripts `database/schema.sql` + migrations. |
| **Moyens** | MySQL 8, phpMyAdmin, diagramme ER |
| **Preuves** | Export schéma, `database/schema.sql`, capture phpMyAdmin |

---

## Exemple 2 — Composants d'accès aux données SQL et NoSQL

| Champ | Contenu suggéré |
|-------|-----------------|
| **Contexte** | Accès données catalogue et statistiques admin |
| **Période** | Mai–Juin 2026 |
| **Description** | Couche PDO centralisée (`includes/db.php`). Requêtes préparées pour plats, commandes, avis. Sync MongoDB local (`scripts/sync-mongo.php`) pour stats CA ; fallback MySQL en production InfinityFree. |
| **Moyens** | PHP PDO, MongoDB (extension PHP, Docker/XAMPP) |
| **Preuves** | `includes/db.php`, `includes/mongo.php`, `scripts/sync-mongo.php` |

**Sécurité :** requêtes paramétrées exclusivement ; pas de concaténation SQL utilisateur.

---

## Exemple 3 — Composants métier côté serveur

| Champ | Contenu suggéré |
|-------|-----------------|
| **Contexte** | Règles métier traiteur (délais, tarifs, workflow commande) |
| **Période** | Mai–Juillet 2026 |
| **Description** | Logique dans `includes/helpers.php` et `valider-commande.php` : validation délai livraison, calcul livraison (5 € + 0,59 €/km), réduction 10 %, workflow 8 statuts commande, rôles admin/employé/client, modération avis. |
| **Moyens** | PHP 8.3, architecture includes, tests manuels |
| **Preuves** | `valider-commande.php`, `admin/update-statut.php`, `includes/helpers.php` |

**Sécurité :** CSRF, `password_hash()`, contrôle accès `requireAdminAccess()`.

---

## Déclaration sur l'honneur

Texte imposé par le modèle Studi — à recopier, compléter (date, lieu), **signer à la main** sur les 2 exemplaires imprimés.

---

## Annexes recommandées (facultatif dans le DP)

1. Captures prod (accueil, wizard, admin)
2. Extrait `includes/helpers.php` (CSRF + validation)
3. Extrait `includes/menu-helpers.php` (filtres + validation panier)
4. Schéma BDD
5. Capture audit WAVE

Copier depuis `docs/` et `dossier-projet/annexes/` si déjà préparées.

---

## Phrase de contexte formation (si le modèle le demande)

> *En l'absence de mission en entreprise, les exemples ci-dessus s'appuient sur le projet fil rouge de formation « Vite & Gourmand », déployé en production sur InfinityFree, conforme au cahier des charges ECF du titre Développeur Web et Web Mobile.*
