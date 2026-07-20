# Manuel utilisateur — Vite & Gourmand

**Site de production :** https://vitegourmand.infinityfree.io/

---

## Site public

### Consulter les menus

1. Accueil → **Menus**
2. Filtrer par thème, régime alimentaire ou recherche textuelle (filtres combinables)
3. Cliquer **Voir le menu** pour accéder à la fiche détaillée et au wizard de commande

### Commander

1. Se connecter ou s'inscrire (`/login.php`, `/register.php`)
2. Sur la fiche menu, indiquer le nombre d'invités puis répartir les convives par entrée, plat et dessert
3. Valider les étapes du wizard (boissons optionnelles, récapitulatif)
4. Cliquer **Continuer la commande** puis renseigner adresse, date et heure de livraison
5. Confirmer — un e-mail de confirmation est envoyé si la messagerie serveur est configurée

### Suivi et avis

- **Mon espace** (`/espace-utilisateur.php`) : historique des commandes, modification limitée (adresse, GSM, personnes)
- **Suivi commande** : timeline des statuts
- **Avis** : déposer un avis après prestation terminée (modération admin)

---

## Espace admin / employé

**URL :** `/admin/login.php`

### Rôles

| Rôle | Accès |
|------|-------|
| **Administrateur** | Toutes les fonctionnalités (commandes, menus, plats, utilisateurs, avis, statistiques) |
| **Employée** | Gestion des commandes et modération des avis (sans gestion des utilisateurs) |

### Commandes

- Filtrer par statut ou client
- Faire évoluer le statut (workflow complet : en attente → acceptée → préparation → livraison → terminée)
- Annuler avec motif et mode de contact client

### Menus et plats

- CRUD menus, plats, options
- Modération des avis clients

### Statistiques

- Dashboard avec KPI et graphiques (Chart.js)
- Données agrégées depuis **MySQL** en production ; démonstration **MongoDB** possible en local (XAMPP/Docker)

---

## Comptes de démonstration (production)

| Rôle | E-mail | Mot de passe | Connexion |
|------|--------|--------------|-----------|
| Administrateur (José) | jose@vite-gourmand.fr | Admin123! | `/admin/login.php` |
| Employée (Julie) | julie@vite-gourmand.fr | Employe123! | `/admin/login.php` |
| Client | client@vite-gourmand.fr | Client123! | `/login.php` |

> Ces comptes sont configurés via le script SQL `database/patch-cleanup-users.sql` sur la base MySQL hébergée (InfinityFree).

---

## Accessibilité et données personnelles

- Page **Accessibilité** : `/accessibilite.php` (mesures RGAA)
- **Politique de confidentialité** : `/politique-confidentialite.php` (RGPD, cookies)
- Bandeau cookies en bas de page (acceptation / refus)

---

*Manuel utilisateur — Vite & Gourmand — ECF 2026*
