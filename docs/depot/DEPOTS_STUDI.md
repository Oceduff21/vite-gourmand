# Dépos Studi — 3 documents DISTINCTS

> **Candidat :** Océane Virginie Erica Duffour  
> **ECF :** déjà envoyé ✓

---

## Vue d'ensemble

| Document | C'est quoi ? | Espace Studi | Dossier local |
|----------|--------------|--------------|---------------|
| **ECF** (copie à rendre + 3 PDF) | Examen final — cahier des charges rempli | Plateforme ECF / jury | `ECF_A_RENDRE/` ✓ **ENVOYÉ** |
| **Dossier Projet** | Récit **technique** du projet Vite & Gourmand | *« 1er dépôt du dossier projet »* | `dossier-projet/` |
| **Dossier Professionnel (DP)** | Preuves de **pratique** vs référentiel REAC (2 activités types) | *« Dossier Professionnel »* puis *« Dépôt final »* | `dossier-professionnel/` |

**Ne jamais mélanger les trois.** Chaque dépôt = un fichier (ou ZIP) séparé sur Studi.

---

## 1. Dossier Projet (Studi — entraînement, non noté)

**Source PDF Studi :** [806ddcce…pdf](https://ressources.studi.fr/contenus/pdf/806ddcce80b00a3aa8543120ed93337146b573eb.pdf)

### Plan obligatoire (projet en formation)

1. Liste des compétences mises en œuvre  
2. Expression des besoins → objectifs et limites  
3. Environnement technique  
4. Réalisations (front, back, sécurité, tests, veille)

### Contraintes

- **30 à 50 pages** (hors garde, sommaire, annexes)  
- **30 pages max** d'annexes  
- **6 compétences** référentiel couvertes (3 front + 3 back)

### Fichier à déposer

```
dossier-projet/DOSSIER_PROJET.pdf
```

(+ annexes séparées ou en fin de PDF)

### Commandes

```powershell
python scripts\generate-pdfs.py
# → regénère dossier-projet/DOSSIER_PROJET.pdf
```

---

## 2. Dossier Professionnel — DP (Studi — non noté en correction)

**Source PDF Studi :** [1142de50…pdf](https://ressources.studi.fr/contenus/pdf/1142de502c1a14a962f5ceef18b10d15cf8457aa.pdf)

### C'est quoi ?

Document **officiel REAC** (pas le même que le dossier projet) :

- 1 à 3 **exemples de pratique professionnelle** par activité type  
- Tableau titres / diplômes (optionnel)  
- **Déclaration sur l'honneur** signée  
- Annexes preuves (captures, extraits code)

Le jury le consulte **le jour de l'examen** → **2 exemplaires imprimés** au dépôt final.

### Format accepté

`.doc`, `.docx` ou `.pdf` **uniquement**

### Où le rédiger ?

1. Télécharger le **modèle Word** dans la médiathèque Studi :  
   *« Réussir mon TP Développeur Web et Web Mobile »* → modèle Dossier Professionnel  
2. Compléter avec l'aide : `dossier-professionnel/AIDE_REDACTION.md`  
3. Déposer pour **correction formateur** (premier envoi)  
4. Version finale → espace **« Dossier Professionnel – Dépôt final »**  
5. **Imprimer 2 copies** pour le jour J

---

## Calendrier recommandé

| Semaine | Action |
|---------|--------|
| **Maintenant** | Captures + dépôt Dossier Projet (entraînement Studi) |
| **Ensuite** | Compléter modèle Word DP avec `AIDE_REDACTION.md` |
| **Avant examen** | DP dépôt final + 2 impressions |
| **23 juillet** | Soutenance ECF + apporter DP imprimé |

---

## Résumé en une phrase

- **Dossier Projet** = *« Voici mon projet technique, comment je l'ai construit »*  
- **Dossier Professionnel** = *« Voici comment j'ai prouvé mes compétences REAC sur des situations concrètes »*  
- **ECF** = *« Voici ma copie d'examen + livrables jury »* (déjà fait)
