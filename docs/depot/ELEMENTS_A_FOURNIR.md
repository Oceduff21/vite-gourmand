# Éléments à fournir — Océane Virginie Erica Duffour

Les deux dossiers sont **rédigés**. Il manque uniquement des éléments **personnels** ou **visuels** que seul vous pouvez produire.

---

## Obligatoire avant dépôt Studi

| # | Élément | Pour quel dossier | Action |
|---|---------|-------------------|--------|
| 1 | **Adresse postale complète** | Dossier Professionnel (Word) | Page de garde `DP_Duffour_Oceane.docx` |
| 2 | **Ville** sur la déclaration sur l'honneur | Dossier Professionnel | Ex. « Fait à Bordeaux, le … » |
| 3 | **Signature manuscrite** | Dossier Professionnel | Sur les **2 exemplaires imprimés** |
| 4 | **Copier-coller** sections 1-5 dans Word | Dossier Professionnel | Depuis `CONTENU_DP_OFFICIEL.md` → cellules du modèle |

---

## Captures à faire (15 min)

Enregistrer dans `dossier-projet/annexes/captures/` :

| Fichier | Page |
|---------|------|
| `capture-01-accueil.png` | https://vitegourmand.infinityfree.io/ |
| `capture-02-menus.png` | `/menus.php` |
| `capture-03-wizard.png` | `/menu.php?id=14` |
| `capture-04-espace-client.png` | `/espace-utilisateur.php` (connectée client) |
| `capture-05-admin.png` | `/admin/dashboard.php` (Jose) |
| `capture-06-mobile.png` | Accueil en 375 px (F12) |
| `capture-07-wave.png` | Audit WAVE sur l'accueil |

---

## Optionnel

| Élément | Action |
|---------|--------|
| Autres diplômes / formations | Compléter le tableau dans le Dossier Professionnel |
| Modèle Word officiel Studi | Contenu dans `CONTENU_DP_OFFICIEL.md` → coller dans `DP_Duffour_Oceane.docx` |
| Export schéma BDD visuel | phpMyAdmin → onglet Designer → capture |

---

## Ce qui est déjà fait (ne pas refaire)

- [x] Dossier Projet PDF (`dossier-projet/DOSSIER_PROJET.pdf`)
- [x] Contenu DP officiel (`dossier-professionnel/CONTENU_DP_OFFICIEL.md`)
- [x] Word DP page de garde (`dossier-professionnel/DP_Duffour_Oceane.docx`)
- [x] 6 compétences référentiel couvertes (×2 dossiers)
- [x] Extraits code réels du projet
- [x] Jeu d'essai commande Menu Entreprise
- [x] Veille sécurité OWASP / RGPD / RGAA
- [x] Charte, manuel, doc technique en PDF dans `docs/`

---

## Regénérer les PDF après modification

```powershell
python scripts\generate-pdfs.py
powershell -ExecutionPolicy Bypass -File scripts\prepare-depots-studi.ps1
```

Fichiers produits :
- `dossier-projet/DOSSIER_PROJET.pdf`
- `dossier-professionnel/DOSSIER_PROFESSIONNEL.pdf`
- `DEPOT_DOSSIER_PROJET.zip`
- `DEPOT_DOSSIER_PROFESSIONNEL.zip`
