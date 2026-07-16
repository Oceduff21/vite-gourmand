# Charte graphique — Vite & Gourmand

## Identite

- **Nom** : Vite & Gourmand
- **Secteur** : Traiteur haut de gamme, Bordeaux
- **Ton** : Elegant, chaleureux, professionnel

## Couleurs

| Usage | Couleur | Code |
|-------|---------|------|
| Primaire / CTA | Rouge bordeaux | `#c0392b` |
| Accent premium | Or | `#c9a227` |
| Fond sombre | Bleu nuit | `#1a1a2e` |
| Fond admin | Gris clair | `#f5f7fb` |
| Texte | Gris fonce | `#333333` |
| Succes | Vert Bootstrap | `#198754` |

## Typographie

- **Titres / UI** : Segoe UI, system-ui, sans-serif
- **Corps** : Bootstrap default (16px)

## Composants

- Framework : **Bootstrap 5.3**
- Boutons principaux : `btn-primary`, `btn-success`, `btn-hero` (or)
- Cartes menus : coins arrondis 16px, ombre legere
- Navbar : fond clair, lien Admin en rouge avec icone bouclier
- Galerie menu : grille responsive (image couverture + plats)

## Images

- Hero accueil : carousel 1920×800 px
- Plats / menus : ratio 4:3, `object-fit: cover`
- Galerie fiche menu : 1 image principale (8 cols) + 2 vignettes plats
- Alt text descriptif sur toutes les images (RGAA)

## Logo

Texte stylise « Vite & Gourmand » dans la navbar (pas de logo vectoriel separe).

---

## Maquettes fil de l'eau (wireframes)

> Representations fonctionnelles pour le livrable ECF (3 desktop + 3 mobile).

### Desktop 1 — Page d'accueil

```
+------------------------------------------------------------------+
| [Logo Vite & Gourmand]     Accueil  Menus  Avis  Connexion  [Admin]|
+------------------------------------------------------------------+
|                                                                    |
|              [ CAROUSEL HERO — photo traiteur ]                   |
|              "L'excellence a votre table"                        |
|                    [ Decouvrir nos menus ]                       |
|                                                                    |
+------------------------------------------------------------------+
|  NOS SERVICES          |  CHIFFRES CLES                          |
|  [icon] Livraison      |  500+ evenements                        |
|  [icon] Sur mesure     |  4.8/5 satisfaction                     |
+------------------------------------------------------------------+
|  MENUS POPULAIRES (cartes 3 colonnes avec image + prix)          |
+------------------------------------------------------------------+
|  FOOTER — mentions legales, CGV, contact                         |
+------------------------------------------------------------------+
```

### Desktop 2 — Catalogue menus

```
+------------------------------------------------------------------+
| NAVBAR                                                           |
+------------------------------------------------------------------+
|  Nos menus traiteur                                              |
|  [Filtre theme v] [Filtre regime v] [Recherche...........]       |
+------------------------------------------------------------------+
|  +-------------+  +-------------+  +-------------+               |
|  | [image]     |  | [image]     |  | [image]     |               |
|  | Menu Noel   |  | Brunch      |  | Business    |               |
|  | 45€/pers    |  | 32€/pers    |  | 28€/pers    |               |
|  | [Commander] |  | [Commander] |  | [Commander] |               |
|  +-------------+  +-------------+  +-------------+               |
+------------------------------------------------------------------+
```

### Desktop 3 — Back-office admin

```
+------------------------------------------------------------------+
| SIDEBAR ADMIN          |  TABLEAU DE BORD                        |
| Dashboard              |  +--------+ +--------+ +--------+       |
| Commandes              |  | CA mois| | Cmd att| | Avis   |       |
| Menus                  |  +--------+ +--------+ +--------+       |
| Avis                   |  [ Graphique CA 6 mois ]                |
| Utilisateurs           |  Dernieres commandes (tableau)          |
| Deconnexion            |                                         |
+------------------------+-----------------------------------------+
```

### Mobile 1 — Accueil

```
+---------------------------+
| [=]  Vite & Gourmand      |
+---------------------------+
|                           |
|   [ HERO image pleine ]   |
|   Excellence traiteur     |
|   [ Voir menus ]          |
|                           |
+---------------------------+
| Services (empile)         |
| Menu populaire (1 carte)  |
+---------------------------+
| Footer                    |
+---------------------------+
```

### Mobile 2 — Fiche menu / commande

```
+---------------------------+
| <- Retour    Menu Noel    |
+---------------------------+
| [Galerie image principale]|
| [vignette] [vignette]     |
+---------------------------+
| Etape 2/4 — Entrees       |
| (o) Foie gras             |
| ( ) Veloute               |
|        [ Suivant -> ]     |
+---------------------------+
| [Recap sticky bas ecran]  |
+---------------------------+
```

### Mobile 3 — Espace utilisateur

```
+---------------------------+
| Mon espace                |
+---------------------------+
| [Avatar] Bonjour Marie    |
| Commandes | Profil | Avis |
+---------------------------+
| Commande #12              |
| En attente | 19/03/2026   |
| [Modifier] [Annuler]      |
+---------------------------+
| Commande #8 — Livree      |
+---------------------------+
```

---

## Export PDF

Convertir ce fichier + captures d'ecran reelles du site en PDF pour le depot ECF.
Outils possibles : Pandoc, VS Code « Markdown PDF », ou impression navigateur.
