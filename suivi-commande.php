<?php
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    header('Location: detail-commande.php?id=' . $id . '#suivi');
    exit();
}
header('Location: espace-utilisateur.php?tab=commandes');
exit();
