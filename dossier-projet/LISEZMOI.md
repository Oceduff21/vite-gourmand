# Dossier Projet Studi — mode d'emploi

> **Document DISTINCT** du Dossier Professionnel (DP) et de l'ECF  
> **Espace Studi :** *« 1er dépôt du dossier projet »* (entraînement, non noté)  
> **Voir aussi :** `docs/depot/DEPOTS_STUDI.md` du projet  
> **Git :** sources `.md` + captures PNG trackees ; PDF/ZIP ignores (regenerables).

## Emplacement

```
C:\xampp\htdocs\vite-gourmand\dossier-projet\
├── DOSSIER_PROJET.md          ← document principal (à convertir en PDF)
├── LISEZMOI.md                ← ce fichier
└── annexes/
    └── LISTE_ANNEXES.md       ← checklist des pièces jointes
```

## Contraintes Studi (rappel)

| Élément | Limite |
|---------|--------|
| Corps du dossier (hors garde, sommaire, annexes) | **30 à 50 pages** max. |
| Annexes | **30 pages** max. |
| Compétences obligatoires | 6 compétences référentiel (3 front + 3 back) |

## Étapes pour déposer sur Studi

### 1. Compléter les annexes visuelles

Ajoutez dans `dossier-projet/annexes/` :

- Captures prod : accueil, menus, wizard, espace client, admin
- Capture WAVE (accessibilité)
- Export schéma BDD (phpMyAdmin ou diagramme)

Les PDF existants peuvent être copiés depuis `docs/` :

```powershell
Copy-Item docs\CHARTE_GRAPHIQUE.pdf dossier-projet\annexes\
Copy-Item docs\MANUEL_UTILISATEUR.pdf dossier-projet\annexes\
Copy-Item docs\DOCUMENTATION_TECHNIQUE.pdf dossier-projet\annexes\
```

### 2. Générer le PDF du dossier

```powershell
cd C:\xampp\htdocs\vite-gourmand
python scripts\generate-pdfs.py
```

Le fichier produit : `dossier-projet/DOSSIER_PROJET.pdf`

### 3. Assembler le dépôt Studi

**Option A — Un seul PDF** (corps + annexes en fin de document)  
**Option B — ZIP** :

```
Dossier_Projet_Duffour_Oceane.zip
├── DOSSIER_PROJET.pdf
└── annexes/
    ├── CHARTE_GRAPHIQUE.pdf
    ├── captures/
    └── ...
```

### 4. Support de présentation orale

Préparer un diaporama séparé pour la soutenance jury (non inclus dans ce dossier écrit).

## Liens utiles

- Site prod : https://vitegourmand.infinityfree.io/
- GitHub : https://github.com/Oceduff21/vite-gourmand
