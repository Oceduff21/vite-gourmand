# Contenu à copier dans le modèle Word officiel Studi

> **Fichier modèle :** `modele dossier pro.docx` (OneDrive)  
> **Fichier pré-rempli (page de garde) :** `dossier-professionnel/DP_Duffour_Oceane.docx`  
> **Guide Studi :** rubriques 1 à 5, rédaction au **« je »**, **3 pages max par exemple**

---

## PAGE DE GARDE

| Champ | Texte |
|-------|-------|
| Nom de naissance | **DUFFOUR** |
| Nom d'usage | **DUFFOUR** |
| Prénom | **Océane Virginie Erica** |
| Adresse | *[À COMPLÉTER]* |
| Titre visé | Développeur Web et Web Mobile |
| Modalité | ☑ **Parcours de formation** |

---

## SOMMAIRE (à compléter en fin de rédaction)

| Exemple | Intitulé | Page |
|---------|----------|------|
| AT1 — Ex. 1 | Conception et développement du parcours commande client (wizard et filtres AJAX) | p. … |
| AT1 — Ex. 2 | *(optionnel — dupliquer une fiche si vous ajoutez un 2e exemple)* Maquettes et charte graphique du site traiteur | p. … |
| AT2 — Ex. 1 | Conception de la base de données et logique métier des commandes traiteur | p. … |
| AT2 — Ex. 2 | *(optionnel)* Statistiques admin MySQL / MongoDB | p. … |

---

# ACTIVITÉ-TYPE 1

## Développer la partie front-end d'une application web ou web mobile sécurisée

### Exemple n° 1 — Conception et développement du parcours commande client (wizard et filtres AJAX)

#### 1. Décrivez les tâches ou opérations que vous avez effectuées, et dans quelles conditions :

Dans le cadre de ma formation Studi, je développe le site e-commerce du traiteur fictif « Vite & Gourmand ». Pour la partie front-end, je commence par réaliser des wireframes desktop et mobile (accueil, catalogue menus, fiche menu). J'applique la charte graphique : rouge bordeaux #c0392b, or #c9a227, Bootstrap 5.

J'intègre ensuite les pages statiques en HTML5 sémantique : accueil, menus, CGV, politique de confidentialité et page accessibilité. J'ajoute un lien d'évitement « Aller au contenu principal » et des labels explicites sur tous les formulaires.

Je développe la partie dynamique : un assistant de commande en 6 étapes sur `menu.php` (invités, entrées, plats, desserts, boissons, récapitulatif). Le JavaScript gère la navigation entre étapes, le compteur d'invités restants et le focus clavier. J'implémente aussi les filtres AJAX sur `menus.php` (recherche, thème, régime) sans rechargement complet de la page.

Avant chaque livraison, je teste le responsive (375 px) et je corrige les alertes WAVE. Côté sécurité, j'associe un token CSRF à la soumission du wizard et je complète la validation JavaScript par une validation serveur PHP.

#### 2. Précisez les moyens utilisés :

HTML5, CSS3, Bootstrap 5.3, JavaScript (Fetch API), Font Awesome, VS Code/Cursor, Git/GitHub, extension WAVE, Chrome DevTools, charte graphique du projet, documentation MDN et Bootstrap.

#### 3. Avec qui avez-vous travaillé ?

Je travaille seule sur ce projet fil rouge. J'échange avec mon formateur Studi lors des corrections de livrables. Je consulte la documentation en ligne (MDN, PHP.net, OWASP) pour résoudre les difficultés techniques.

#### 4. Contexte

| | |
|--|--|
| **Organisme** | Studi — Formation Développeur Web et Web Mobile (projet Vite & Gourmand) |
| **Lieu / service** | Projet de formation — développement web traiteur en ligne |
| **Période** | Du : **01/03/2026** au : **18/07/2026** |

#### 5. Informations complémentaires (facultatif)

J'ai choisi cet exemple car il couvre l'ensemble de la compétence front-end : maquettes, intégration statique et JavaScript dynamique. Le wizard est la fonctionnalité la plus visible pour l'utilisateur. Le site est en ligne : https://vitegourmand.infinityfree.io/

---

### Exemple n° 2 (OPTIONNEL — si vous dupliquez une fiche AT1)

**Intitulé :** Maquettes et charte graphique du site traiteur Vite & Gourmand

#### 1. Tâches :

Je conçois les wireframes desktop et mobile avant tout développement. Je définis la palette (#c0392b, #c9a227, #1a1a2e), la typographie Segoe UI/Bootstrap et les composants réutilisables (cartes menus, badges régime, navbar). Je valide la cohérence visuelle sur les maquettes accueil, menus et wizard.

#### 2. Moyens :

Wireframes papier/numérique, charte graphique PDF, Bootstrap 5, documentation RGAA pour les contrastes.

#### 3. Avec qui :

Seule, avec retours formateur Studi.

#### 4. Contexte :

Studi, mars 2026, projet Vite & Gourmand.

#### 5. Complément :

Document `docs/CHARTE_GRAPHIQUE.pdf` joint en annexe.

---

# ACTIVITÉ-TYPE 2

## Développer la partie back-end d'une application web ou web mobile sécurisée

### Exemple n° 1 — Conception de la base de données et logique métier des commandes traiteur

#### 1. Décrivez les tâches ou opérations que vous avez effectuées, et dans quelles conditions :

Je modélise la base MySQL : tables users, menus, plats, menu_options, commandes, commande_details, commande_historique, avis. J'écris les scripts SQL et les exécute sous phpMyAdmin (local XAMPP puis production InfinityFree).

Je développe la couche PDO avec requêtes préparées (`includes/db.php`, `menu-helpers.php`). En local, je synchronise les stats vers MongoDB ; en production, j'adapte le dashboard avec MySQL faute de MongoDB sur l'hébergeur gratuit.

J'implémente la logique métier : validation du délai de livraison, calcul frais de livraison (5 € + 0,59 €/km), réduction 10 %, workflow 8 statuts commande, modération avis, rôles admin/employé/client. J'applique bcrypt et CSRF sur les formulaires sensibles.

#### 2. Précisez les moyens utilisés :

PHP 8.3, MySQL 8, PDO, MongoDB (local), phpMyAdmin, XAMPP, Git, FileZilla, InfinityFree, OWASP, PHP.net.

#### 3. Avec qui avez-vous travaillé ?

Seule. Formateur Studi pour validation structure BDD. Tests avec comptes demo (jose@, julie@, client@vite-gourmand.fr).

#### 4. Contexte

| | |
|--|--|
| **Organisme** | Studi — projet Vite & Gourmand |
| **Lieu / service** | Back-end PHP/MySQL |
| **Période** | Du : **01/03/2026** au : **18/07/2026** |

#### 5. Informations complémentaires :

Cet exemple couvre les 3 compétences back-end sur un cas réel : commande menu traiteur avec contraintes métier (délai, minimum personnes, statuts).

---

## Titres, diplômes, CQP (facultatif)

| Intitulé | Organisme | Date |
|----------|-----------|------|
| Formation Développeur Web et Web Mobile | Studi | 2025 — 2026 |
| *[Autres — à compléter ou supprimer]* | | |

---

## Déclaration sur l'honneur

Je soussignée **Océane Virginie Erica DUFFOUR**, déclare sur l'honneur que les renseignements fournis dans ce dossier sont exacts et que je suis l'auteur(e) des réalisations jointes.

Fait à **[VILLE]**, le **18/07/2026**

**Signature manuscrite** (sur les 2 exemplaires imprimés)

---

## Documents illustrant la pratique (facultatif — max 2 par activité-type)

| Intitulé | Fichier |
|----------|---------|
| Capture wizard commande | `capture-03-wizard.png` *(à fournir)* |
| Capture dashboard admin | `capture-05-admin.png` *(à fournir)* |
| Schéma BDD | export phpMyAdmin ou `database/schema.sql` |

---

*Ne pas joindre : CV, rapport de stage, appréciation tuteur (interdit par le guide Studi).*
