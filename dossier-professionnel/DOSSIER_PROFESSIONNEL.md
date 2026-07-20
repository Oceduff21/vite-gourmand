# DOSSIER PROFESSIONNEL (DP)

---

**Nom de naissance :** DUFFOUR  
**Prénom :** Océane Virginie Erica  
**Adresse :** *[À COMPLÉTER — rue, code postal, ville]*  
**Téléphone :** *[À COMPLÉTER]*  
**E-mail :** *[À COMPLÉTER]*  

**Titre professionnel visé :** Développeur web et web mobile  

**Modalité d'accès :** ☑ Parcours de formation ☐ VAE  

**Date :** 18 juillet 2026  

---

## Présentation du dossier

Le dossier professionnel (DP) constitue un élément du système de validation du titre professionnel délivré par le Ministère chargé de l'emploi. Il appartient au candidat, qui le conserve et le présente à chaque session d'examen.

Le jury dispose du DP pour apprécier les **preuves de pratique professionnelle** du candidat, en complément des épreuves (mise en situation, entretien, dossier projet, ECF).

**Contexte de ce dossier :** candidat en formation Studi, sans expérience salariée significative en développement web. Les exemples de pratique s'appuient sur le **projet fil rouge de formation « Vite & Gourmand »** (traiteur en ligne), déployé en production sur https://vitegourmand.infinityfree.io/ et versionné sur GitHub.

---

# ACTIVITÉ TYPE 1

## Développer la partie front-end d'une application web ou web mobile en intégrant les recommandations de sécurité

---

### Exemple de pratique professionnelle n° 1

**Compétence :** Maquetter des interfaces utilisateur web ou web mobile  

| Champ | Contenu |
|-------|---------|
| **Entreprise / contexte** | Projet de formation Studi — entreprise fictive « Vite & Gourmand », traiteur événementiel à Bordeaux |
| **Période** | Mars 2026 |
| **Lieu** | Formation à distance / environnement local XAMPP |
| **Description de la mission** | Concevoir les wireframes des écrans principaux avant développement : page d'accueil (hero + cartes menus), catalogue menus avec panneau de filtres, fiche menu avec assistant de commande en plusieurs étapes. Adapter la navigation pour desktop et mobile (menu hamburger, cartes empilées). |
| **Moyens utilisés** | Charte graphique (#c0392b bordeaux, #c9a227 or, Bootstrap 5), wireframes ASCII/Figma, retours formateur |
| **Résultats** | Maquettes validées ; base visuelle pour l'intégration HTML/CSS ; cohérence sur 14 menus thématiques |
| **Documents / preuves** | `docs/CHARTE_GRAPHIQUE.pdf` — wireframes sections desktop/mobile ; captures prod en annexe |

**Choix techniques et sécurité :** contrastes couleurs vérifiés pour lisibilité (RGAA) ; zones cliquables dimensionnées pour le tactile mobile ; pas de données sensibles sur les maquettes.

---

### Exemple de pratique professionnelle n° 2

**Compétence :** Réaliser des interfaces utilisateur statiques web ou web mobile  

| Champ | Contenu |
|-------|---------|
| **Entreprise / contexte** | Même projet — site public Vite & Gourmand |
| **Période** | Mars — Avril 2026 |
| **Description de la mission** | Intégrer les pages statiques et semi-statiques : accueil (`index.php`), catalogue (`menus.php`), pages légales (CGV, mentions légales, politique de confidentialité, accessibilité). Utiliser HTML5 sémantique, Bootstrap 5.3, CSS personnalisé (`assets/css/style.css`). Assurer le responsive et les textes alternatifs images. |
| **Moyens utilisés** | HTML5, CSS3, Bootstrap 5, PHP pour includes (`header.php`, `footer.php`), Font Awesome |
| **Résultats** | Site public navigable ; page `accessibilite.php` listant les mesures RGAA ; bandeau cookies RGPD |
| **Documents / preuves** | Fichiers `index.php`, `accessibilite.php`, `assets/css/style.css` ; captures desktop + mobile (375 px) |

**Extrait de code — accessibilité (skip link) :**

```html
<a class="visually-hidden-focusable skip-link" href="#main-content">
    Aller au contenu principal
</a>
<nav class="navbar custom-navbar" aria-label="Navigation principale">
```

**Sécurité front-end :** échappement systématique via `htmlspecialchars()` ; validation HTML5 sur formulaires ; pas de scripts inline non contrôlés.

---

### Exemple de pratique professionnelle n° 3

**Compétence :** Développer la partie dynamique des interfaces utilisateur web ou web mobile  

| Champ | Contenu |
|-------|---------|
| **Entreprise / contexte** | Parcours commande client — fonctionnalité cœur du site |
| **Période** | Avril — Mai 2026 |
| **Description de la mission** | Développer le wizard JavaScript 6 étapes sur `menu.php` (invités, entrées, plats, desserts, boissons, récapitulatif). Implémenter les filtres AJAX sur `menus.php` (fetch vers `load-menus.php`). Gérer les états UI (compteurs invités, boutons Suivant/Précédent, ARIA). Intégrer graphiques Chart.js au dashboard admin avec alternative textuelle. |
| **Moyens utilisés** | JavaScript ES6+, Fetch API, Bootstrap modals, Chart.js, PHP endpoints AJAX |
| **Résultats** | Commande guidée sans rechargement intermédiaire ; filtres menus instantanés ; dashboard admin interactif |
| **Documents / preuves** | `menu.php`, `load-menus.php`, `includes/menu-helpers.php` ; captures wizard et filtres |

**Extrait — filtrage sémantique OR (recherche + thème + régime) :**

```php
function filterMenusForListing(array $menus, array $params): array
{
    $criteria = collectMenuSemanticCriteria($params);
    return array_values(array_filter($menus, static function (array $menu) use ($criteria): bool {
        foreach ($criteria as $criterion) {
            if (menuMatchesSemanticCriterion($menu, $criterion)) {
                return true;
            }
        }
        return false;
    }));
}
```

**Sécurité :** token CSRF sur soumission wizard ; validation serveur `validatePlatSelection()` en complément du JS client.

---

# ACTIVITÉ TYPE 2

## Développer la partie back-end d'une application web ou web mobile en intégrant les recommandations de sécurité

---

### Exemple de pratique professionnelle n° 1

**Compétence :** Mettre en place une base de données relationnelle  

| Champ | Contenu |
|-------|---------|
| **Entreprise / contexte** | Modélisation des données métier traiteur |
| **Période** | Mars 2026 |
| **Description de la mission** | Concevoir et implémenter le schéma MySQL : utilisateurs (rôles client/employé/admin), catalogue (menus, plats, options), commandes (détails, boissons, historique statuts), avis modérés. Définir clés étrangères, index, encodage UTF-8. Rédiger scripts de création et migrations incrémentales. |
| **Moyens utilisés** | MySQL 8, phpMyAdmin, fichiers SQL versionnés (`database/schema.sql`, `migration.sql`) |
| **Résultats** | Base normalisée ; 14 menus seedés ; relations cohérentes pour le parcours commande |
| **Documents / preuves** | `database/schema.sql`, export phpMyAdmin, diagramme ER (annexe) |

**Tables principales :** `users`, `menus`, `plats`, `menu_options`, `commandes`, `commande_details`, `commande_boissons`, `commande_historique`, `avis`.

---

### Exemple de pratique professionnelle n° 2

**Compétence :** Développer des composants d'accès aux données SQL et NoSQL  

| Champ | Contenu |
|-------|---------|
| **Entreprise / contexte** | Couche d'accès aux données — site + statistiques admin |
| **Période** | Mai — Juin 2026 |
| **Description de la mission** | Centraliser la connexion PDO (`includes/db.php`). Écrire des requêtes préparées pour le catalogue, les commandes et les avis. Implémenter la synchronisation MongoDB locale (`scripts/sync-mongo.php`) pour agrégats CA ; adapter le dashboard en fallback MySQL sur InfinityFree (pas de MongoDB en prod). |
| **Moyens utilisés** | PHP PDO, MongoDB PHP extension, Docker/XAMPP en local |
| **Résultats** | Aucune requête SQL concaténée ; stats admin fonctionnelles en local et production |
| **Documents / preuves** | `includes/db.php`, `includes/mongo.php`, `scripts/sync-mongo.php` |

**Extrait — connexion PDO sécurisée :**

```php
$pdo = new PDO(
    "mysql:host=$host;dbname=$dbname;charset=utf8",
    $user,
    $password,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$stmt = $pdo->prepare('SELECT mo.type, p.* FROM menu_options mo JOIN plats p ON p.id = mo.plat_id WHERE mo.menu_id = ?');
$stmt->execute([$menuId]);
```

---

### Exemple de pratique professionnelle n° 3

**Compétence :** Développer des composants métier côté serveur  

| Champ | Contenu |
|-------|---------|
| **Entreprise / contexte** | Règles métier traiteur et workflow commande |
| **Période** | Mai — Juillet 2026 |
| **Description de la mission** | Implémenter la logique serveur : validation délai livraison (`validateDateLivraisonMenu`), calcul frais livraison (`calculateLivraisonPrice`), réduction 10 % (`calculateCommandeReduction`), workflow 8 statuts commande, modération avis, rôles admin/employé (`requireAdminAccess`). Traiter l'inscription client avec hash bcrypt et CSRF. |
| **Moyens utilisés** | PHP 8.3, architecture `includes/helpers.php`, tests manuels parcours ECF |
| **Résultats** | Règles CDC respectées côté serveur ; commandes tracées dans `commande_historique` ; accès restreints par rôle |
| **Documents / preuves** | `valider-commande.php`, `admin/update-statut.php`, `register.php`, `includes/helpers.php` |

**Extrait — validation délai livraison :**

```php
function validateDateLivraisonMenu(string $dateLivraison, int $delaiJours, ?string $fromDate = null): ?string
{
    if ($dateLivraison === '') {
        return 'Date de livraison obligatoire.';
    }
    $delai = analyseDelaiLivraison($dateLivraison, $delaiJours, $fromDate);
    if (!$delai['delai_respecte']) {
        return 'Delai insuffisant : livraison possible a partir du ' . $delai['date_min_livraison'];
    }
    return null;
}
```

**Sécurité :** `password_hash()` / `password_verify()` ; tokens CSRF ; contrôle rôle avant actions admin.

---

## Titres, diplômes, certifications et attestations de formation

| Intitulé | Organisme | Date | Durée |
|----------|-----------|------|-------|
| Formation Développeur Web et Web Mobile | Studi | 2025 — 2026 | *[À COMPLÉTER si dates précises]* |
| *[Autres diplômes — à compléter ou supprimer les lignes inutiles]* | | | |

---

## Déclaration sur l'honneur

Je soussignée **Océane Virginie Erica DUFFOUR**, atteste sur l'honneur l'exactitude des informations renseignées dans ce dossier professionnel et des exemples de pratique professionnelle présentés.

Les productions et preuves jointes correspondent à mon travail personnel réalisé dans le cadre de ma formation, complété le cas échéant par des projets personnels documentés.

Fait à *[VILLE À COMPLÉTER]*, le *[DATE À COMPLÉTER]*

**Signature :**

_______________________________  
Océane Virginie Erica DUFFOUR

---

## Annexes (facultatif — recommandé)

| N° | Document |
|----|----------|
| 1 | Captures site prod (accueil, menus, wizard, admin) — *à insérer* |
| 2 | `docs/CHARTE_GRAPHIQUE.pdf` |
| 3 | Extrait code CSRF + validation commande |
| 4 | Schéma base de données |
| 5 | Capture audit WAVE — *à insérer* |

---

*Dossier Professionnel — Océane Virginie Erica Duffour — Juillet 2026*
