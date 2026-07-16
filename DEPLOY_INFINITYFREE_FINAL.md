# Deploiement final — vitegourmand.infinityfree.io

## Fichier a uploader

```
C:\xampp\htdocs\UPLOAD-infinityfree.zip
```

## Etapes (15 min)

### 1. File Manager → entrer dans `htdocs`

Chemin affiche : `/htdocs`

### 2. Supprimer le contenu actuel (optionnel mais recommande)

Selectionnez tout dans htdocs → Delete (sauf si config.local.php a deja vos bons identifiants — notez-le avant)

### 3. Upload + decompression

1. Upload `UPLOAD-infinityfree.zip` dans **htdocs**
2. Clic droit sur le zip → **Extract** / Unzip
3. Verifiez : `index.php`, `admin/login.php`, `assets/images/` visibles directement dans htdocs
4. Supprimez le .zip

### 4. Creer config.local.php

Fichier : `htdocs/includes/config.local.php`

```php
<?php
return [
    'host'     => 'sql112.infinityfree.com',
    'dbname'   => 'if0_42418581_ViteEtGourmand',
    'user'     => 'if0_42418581',
    'password' => 'VOTRE_MOT_DE_PASSE',
];
```

### 5. Importer les donnees BDD

phpMyAdmin InfinityFree → base `if0_42418581_ViteEtGourmand` → Import :

- Exportez votre BDD XAMPP locale (phpMyAdmin → Export)
- Importez le .sql sur InfinityFree

### 6. Test diagnostic

Ouvrez : https://vitegourmand.infinityfree.io/check.php

Tout doit etre OK. Supprimez check.php apres.

### 7. Tests finaux

| URL |
|-----|
| https://vitegourmand.infinityfree.io/ |
| https://vitegourmand.infinityfree.io/menus.php |
| https://vitegourmand.infinityfree.io/admin/login.php |

Login : testadmin@test.com / Admin123!

### 8. Copie ECF

Lien de deploiement :

```
https://vitegourmand.infinityfree.io/
```
