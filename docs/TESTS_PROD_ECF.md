# Tests prod ECF — Vite & Gourmand

> Site : https://vitegourmand.infinityfree.io/  
> Date : 17 juillet 2026

## Resultats automatiques

| Test | Statut | Notes |
|------|--------|-------|
| Accueil | OK | Carousel, menus phares, avis |
| `/menus.php` | OK | 14 menus, filtres AJAX |
| `/menu.php?id=14` | Partiel | 3 entrees OK ; **prix 0 EUR** sur fiche (liste = 34 EUR) |
| Images assets | OK | brochettes, quinoa, curry, CSS 18 753 o |
| RGAA | OK | Skip link, fil d'Ariane, wizard 6 etapes |
| `/a-propos.php`, `/faq.php`, `/cgv.php` | OK | Pages accessibles |
| Admin dashboard | OK | Session active, KPI, graphiques, 2 commandes en attente |
| Admin login | OK | `/admin/login.php` |

## A faire manuellement (avec vous)

### 1. Inscription client
- [ ] https://vitegourmand.infinityfree.io/register.php
- Champs pre-remplis en test : `test.ecf.prod.20260717@gmail.com` / mot de passe a saisir
- Attendu : message « Compte cree avec succes »

### 2. Connexion client
- [ ] https://vitegourmand.infinityfree.io/login.php
- Attendu : lien « Mon espace » dans la navbar

### 3. Commande complete
- [ ] Menu Entreprise (id 14) → wizard → « Tout remplir auto » → Continuer
- [ ] Remplir date livraison (+5 jours min), adresse Bordeaux
- [ ] Valider → page confirmation + email

### 4. Suivi commande
- [ ] Mon espace → voir la commande
- [ ] `/suivi-commande.php?id=X` → timeline statuts

### 5. Admin — traiter commande
- [ ] `/admin/login.php` — `admin@vite-gourmand.fr` / `Admin123!`
- [ ] Commandes → accepter → avancer statuts jusqu'a « terminee »

### 6. Avis
- [ ] Apres commande terminee → laisser un avis
- [ ] Admin → Avis → valider

### 7. Corriger prix Menu Entreprise (si toujours 0 EUR)
Executer dans phpMyAdmin :
```sql
UPDATE menus SET prix = 34.00, min_personnes = 10 WHERE id = 14;
```
Puis Ctrl+F5 sur `/menu.php?id=14` → doit afficher **34,00 EUR/pers.**

## Comptes test

| Role | Email | Mot de passe |
|------|-------|--------------|
| Admin | admin@vite-gourmand.fr | Admin123! |
| Admin | testadmin@test.com | Admin123! |
| Client test | test.ecf.prod.20260717@gmail.com | (a definir a l'inscription) |
