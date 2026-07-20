# Dossier Professionnel (DP) — mode d'emploi

> **Document OFFICIEL REAC** — distinct du Dossier Projet et de l'ECF  
> **Format :** modèle Word Studi → export PDF ou DOCX  
> **Jour d'examen :** apporter **2 exemplaires imprimés**  
> **Git :** sources `.md` trackees ; PDF/DOCX ignores (regenerables). Voir `docs/depot/`.

---

## Étape 1 — Télécharger le modèle Studi

Dans votre **médiathèque Studi** :

1. Module *« Réussir mon TP Développeur Web et Web Mobile »*
2. Télécharger le **modèle Dossier Professionnel** (Word `.docx`)
3. Enregistrer une copie ici :

```
dossier-professionnel/DP_Duffour_Oceane.docx
```

*(Studi fournit le modèle officiel — ne pas inventer la mise en page.)*

---

## Étape 2 — Remplir le modèle Word officiel Studi

**Modèle source (OneDrive) :**
```
C:\Users\ocean\OneDrive\Documents\Formation WEB DEVELOPPEUR FULL STACK\Dossier Professionel\modele dossier pro.docx
```

**Fichier pré-rempli (page de garde) :**
```
dossier-professionnel/DP_Duffour_Oceane.docx
```

**Contenu complet à copier-coller dans Word :**
```
dossier-professionnel/CONTENU_DP_OFFICIEL.md
```

Le guide Studi impose pour **chaque exemple** (max 3 pages) :
1. Tâches effectuées (au **« je »**)
2. Moyens utilisés
3. Avec qui vous avez travaillé
4. Contexte (organisme, période)
5. Informations complémentaires (facultatif)

Le modèle officiel contient **1 fiche par activité-type** (2 exemples). Vous pouvez dupliquer une fiche pour un 2e/3e exemple si besoin.

Regénérer le Word (page de garde) :
```powershell
python scripts\fill-dossier-professionnel.py
```

---

## Étape 3 — Pages obligatoires du modèle

- [ ] Page de garde (nom de naissance, prénom, adresse, titre visé)
- [ ] Présentation du dossier (laisser le texte officiel)
- [ ] **Activité type 1** — 1 à 3 fiches pratique
- [ ] **Activité type 2** — 1 à 3 fiches pratique
- [ ] Tableau titres / diplômes / formations (si applicable)
- [ ] **Déclaration sur l'honneur** — compléter, dater, **signer**
- [ ] Annexes (captures, extraits code) — optionnel mais recommandé

---

## Étape 4 — Déposer sur Studi (2 fois)

| Dépôt | Espace Studi | Objectif |
|-------|--------------|----------|
| **1er envoi** | Espace correction DP | Retour formateur (non noté) |
| **Final** | *« Dossier Professionnel – Dépôt final »* | Version examen |

**Formats acceptés :** `.doc`, `.docx`, `.pdf` — rien d'autre.

Forum questions : *« Préparer son Dossier Professionnel »*

---

## Étape 5 — Imprimer pour le jury

- **2 exemplaires** du DP final
- Reliure agrafes ou spirale
- À apporter le **jour de l'examen** (avec la soutenance)

---

## Fichiers dans ce dossier

| Fichier | Rôle |
|---------|------|
| `LISEZMOI.md` | Ce guide |
| `AIDE_REDACTION.md` | Contenus à copier dans le Word Studi |
| `DP_Duffour_Oceane.docx` | *(à créer)* votre modèle complété |

---

## Ne pas confondre

| | Dossier Projet | Dossier Professionnel |
|--|----------------|----------------------|
| Plan | 4 sections (formation) | Fiches REAC par activité type |
| Modèle | Rédaction libre (notre MD/PDF) | **Word officiel Studi** |
| Public | Formateur Studi (entraînement) | **Jury examen** (+ 2 prints) |
| Projet | Vite & Gourmand technique | Preuves de compétences REAC |
