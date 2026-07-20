# Checklist annexes — Dossier Projet

Cochez avant dépôt Studi.

## Obligatoires (compétences front-end)

- [ ] Maquettes interfaces (desktop + mobile) — `CHARTE_GRAPHIQUE.pdf` ou captures Figma/wireframes
- [ ] Captures écran interfaces finales (web + mobile)
- [ ] Extraits code HTML/CSS statique — voir section 4.1 du dossier
- [ ] Extraits code JavaScript dynamique — wizard `menu.php`, filtres `load-menus.php`

## Obligatoires (compétences back-end)

- [ ] Schéma BDD (conceptuel + physique) — `database/schema.sql` ou export visuel
- [ ] Extraits composants accès données — `includes/db.php`, `menu-helpers.php`
- [ ] Extraits composants métier — `helpers.php`, `valider-commande.php`

## Recommandées

- [ ] Capture dashboard admin (graphiques)
- [ ] Capture audit WAVE
- [ ] Jeu d'essai commande (tableau section 4.4)
- [ ] `MANUEL_UTILISATEUR.pdf`
- [ ] `DOCUMENTATION_TECHNIQUE.pdf`

## Fichiers déjà disponibles dans le projet

| Fichier source | Copier vers |
|----------------|-------------|
| `docs/CHARTE_GRAPHIQUE.pdf` | `dossier-projet/annexes/` |
| `docs/MANUEL_UTILISATEUR.pdf` | `dossier-projet/annexes/` |
| `docs/DOCUMENTATION_TECHNIQUE.pdf` | `dossier-projet/annexes/` |
| `database/schema.sql` | `dossier-projet/annexes/` |

## Captures à faire manuellement

1. https://vitegourmand.infinityfree.io/ (accueil)
2. https://vitegourmand.infinityfree.io/menus.php (filtres)
3. https://vitegourmand.infinityfree.io/menu.php?id=14 (wizard)
4. https://vitegourmand.infinityfree.io/admin/login.php → dashboard Jose
5. Vue mobile (F12 → mode responsive 375px)
