# Copie a rendre ECF — elements a remplir

> Fichier ODT : Copie a rendre_TP – Developpeur Web et Web Mobile

## Lien de deploiement (OBLIGATOIRE)

Coller cette URL dans la copie ODT :

```
https://vitegourmand.infinityfree.io/
```

Pages de demonstration :

| Page | URL |
|------|-----|
| Accueil | https://vitegourmand.infinityfree.io/ |
| Menus | https://vitegourmand.infinityfree.io/menus.php |
| Connexion | https://vitegourmand.infinityfree.io/login.php |
| Admin | https://vitegourmand.infinityfree.io/admin/login.php |

## Stack technique (a declarer dans la copie ODT)

| Element | Valeur reelle |
|---------|---------------|
| Langages | HTML, CSS, JavaScript, **PHP 8.3** |
| BDD relationnelle | **MySQL** (InfinityFree — `sql112.infinityfree.com`) |
| BDD NoSQL | **MongoDB** (statistiques — **en local** Docker/XAMPP ; fallback MySQL en production) |
| Hebergement production | **InfinityFree** (https://vitegourmand.infinityfree.io/) |
| Git | https://github.com/Oceduff21/vite-gourmand |
| Environnement local | XAMPP (Apache + MySQL + extension MongoDB) |

> **Note soutenance :** MongoDB n'est pas disponible sur l'hebergement gratuit InfinityFree. Les statistiques NoSQL sont demontrees en local ; le dashboard admin utilise MySQL en production.

## Comptes de demonstration

| Role | Email | Mot de passe |
|------|-------|--------------|
| Admin (Jose) | jose@vite-gourmand.fr | Admin123! |
| Employe (Julie) | julie@vite-gourmand.fr | Employe123! |
| Client | client@vite-gourmand.fr | Client123! |

## Livrables PDF a joindre

1. **Manuel utilisateur** — parcours client + admin/employe
2. **Charte graphique** — couleurs (#c0392b, Bootstrap), typo, logo
3. **Documentation technique** — architecture, BDD, deploiement

Sources Markdown a convertir en PDF : dossier `docs/`

## Checklist avant depot (23 juillet)

- [x] Lien de deploiement fonctionnel — https://vitegourmand.infinityfree.io/
- [x] CGV completes (delais, 10%, livraison, allergenes, materiel 600€)
- [x] Validation delai livraison cote serveur (valider-commande + modifier-commande)
- [x] Modification commande client (adresse, GSM, personnes — menu verrouille)
- [x] Email client sur refus/annulation admin
- [x] Galerie images sur fiche menu
- [x] Inscription avec adresse structuree (rue, CP, ville)
- [x] Documentation livrables MD (docs/GESTION_PROJET.md, UML, wireframes)
- [x] GitHub public a jour
- [x] Copie ODT completee (URL + stack ci-dessus) — voir docs/Copie_a_rendre_TP_Vite_Gourmand.odt
- [x] 3 PDF livrables (docs/MANUEL_UTILISATEUR.pdf, CHARTE_GRAPHIQUE.pdf, DOCUMENTATION_TECHNIQUE.pdf)
- [ ] Upload prod derniere version — `deploy/vite-gourmand-prod.zip` (regenere 17/07, ~105 Mo)
- [ ] SQL prod — `patch-menu-entreprise-prix.sql`, `patch-site-settings.sql`
- [ ] Test parcours complet — voir `docs/DOSSIER_ECF_FINAL.md` et `docs/TESTS_PROD_ECF.md`
- [ ] Verifier ODT + joindre les 3 PDF au dossier papier
- [x] Mentions legales : hebergeur InfinityFree

## Texte type pour la copie ODT (stack)

> Application web PHP/MySQL de commande de prestations traiteur. Front Bootstrap 5, back-office admin/employe, authentification, commandes, avis moderes. Base MySQL hebergee sur InfinityFree. Statistiques MongoDB en environnement local (Docker/XAMPP). Depot GitHub : https://github.com/Oceduff21/vite-gourmand
