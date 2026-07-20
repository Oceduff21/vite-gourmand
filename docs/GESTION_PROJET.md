# Gestion de projet — Vite & Gourmand (ECF)

## Contexte

- **Projet** : Site e-commerce traiteur « Vite & Gourmand » (Bordeaux)
- **Periode** : Fevrier — Juillet 2026
- **Equipe** : Developpeur unique (projet ECF)
- **Depot Git** : https://github.com/Oceduff21/vite-gourmand
- **Production** : https://vitegourmand.infinityfree.io/

## Methodologie

Approche **iterative** en sprints courts, alignee sur le cahier des charges ECF :

| Phase | Objectif | Statut |
|-------|----------|--------|
| 1 | Maquettes + charte graphique | Termine |
| 2 | BDD MySQL + catalogue menus | Termine |
| 3 | Parcours client (inscription, commande) | Termine |
| 4 | Back-office admin / employe | Termine |
| 5 | Avis, SEO, securite (CSRF) | Termine |
| 6 | Deploiement InfinityFree | Termine |
| 7 | Livrables documentation | Termine |

## Outil de suivi (Trello)

Tableau recommande : **Vite & Gourmand ECF**

| Colonne | Exemples de cartes |
|---------|-------------------|
| A faire | Galerie menu, CGV completes |
| En cours | Espace employe, validation delais |
| Termine | Auth admin, commandes, avis |
| Bloque | MongoDB sur hebergeur gratuit |

Liens utiles a coller dans la copie ODT si demande :
- Trello : tableau personnel du candidat
- GitHub Issues : suivi bugs / ameliorations sur le depot

## Jalons cles

| Date | Jalon |
|------|-------|
| Mars 2026 | Schema BDD + pages publiques |
| Avril 2026 | Wizard commande + espace utilisateur |
| Mai 2026 | Back-office admin |
| Juin 2026 | Espace employe, emails statut |
| Juillet 2026 | Deploiement prod + livrables PDF |
| **23 juillet 2026** | **Soutenance ECF** |

## Risques et mitigations

| Risque | Mitigation |
|--------|------------|
| MongoDB indisponible en prod | Fallback MySQL pour stats ; demo NoSQL en local |
| Delais commande non respectes | Validation serveur (`validateDateLivraisonMenu`) |
| Securite formulaires | Tokens CSRF, PDO prepare, roles admin/employe |
| Hebergeur gratuit limite | Tests manuels post-upload FileZilla |

## Livrables attendus ECF

1. Application deployee (URL production)
2. Depot Git public
3. Manuel utilisateur (PDF)
4. Charte graphique + maquettes (PDF)
5. Documentation technique (PDF)
6. Copie ODT completee

Voir `docs/depot/COPIE_ECF.md` pour la checklist detaillee.
