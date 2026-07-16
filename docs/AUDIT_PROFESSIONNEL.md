# Audit professionnel — Vite & Gourmand
> Phase 1 | Date : 16 juillet 2026 | Objectif : site traiteur premium production-ready

## Score global estime : 62/100

| Domaine | Score | Commentaire |
|---------|-------|-------------|
| Fonctionnel | 70% | Parcours commande OK, lacunes boissons/avis |
| UI/Design | 55% | Bootstrap correct, manque identite premium |
| UX | 58% | Wizard plats ameliore, login/cart fragile |
| Admin | 65% | CRUD present, layouts incoherents |
| Securite | 60% | PDO OK, CSRF partiel, GET destructifs |
| SEO | 45% | Meta faibles, pas de sitemap |
| Contenu | 40% | 4 menus demo, pages manquantes |
| Responsive | 70% | Globalement OK, sticky summary mobile |

---

## Bugs critiques

1. Panier perdu si non connecte avant `commande.php`
2. Fichiers legacy exposes (`create-menu.php` sans auth)
3. `gsm` collecte mais jamais sauvegarde
4. Boissons admin jamais proposees au client
5. Seed demo incomplet (3 plats/categorie requis admin)

## Fonctionnalites manquantes (brief client)

- Pages : A propos, FAQ, Politique confidentialite, Avis publics
- 12+ menus thematiques avec contenu riche
- Boissons par categorie avec supplement prix
- Galerie photos par menu
- Allergenes affiches
- Admin boissons dedie

## Plan de remediation (phases 2-9)

| Phase | Priorite | Actions |
|-------|----------|---------|
| 2 UI/UX | P0 | Design system, header, cartes premium |
| 3 Contenu | P0 | catalogue-complet.sql, 12 menus |
| 4 Menus | P0 | 3 options/regime par categorie |
| 5 Boissons | P1 | Selection client + admin CRUD | **En cours** — UI client + admin-boissons.php |
| 6 Front | P1 | Accueil, FAQ, A propos, filtres |
| 7 Espace user | P2 | Dashboard ameliore | **Fait** — onglets, favoris, notifications |
| 8 Admin | P1 | Layout unifie, boissons, stats | **En cours** — dashboard KPI + graphiques |
| 9 Qualite | P0 | Securite, CSRF, legacy supprime |

---

*Rapport genere avant modifications Phase 2+.*
