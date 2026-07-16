<?php
require_once __DIR__ . '/config.php';

function getMongoManager() {
    if (!class_exists('MongoDB\Driver\Manager')) {
        return null;
    }
    try {
        return new MongoDB\Driver\Manager(MONGO_URI);
    } catch (Throwable $e) {
        return null;
    }
}

function mongoInsertCommandeStat(array $data) {
    $manager = getMongoManager();
    if (!$manager) {
        return false;
    }
    try {
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->insert([
            'commande_id' => (int)$data['commande_id'],
            'menu_id' => (int)$data['menu_id'],
            'menu_titre' => $data['menu_titre'],
            'prix_total' => (float)$data['prix_total'],
            'nb_personnes' => (int)$data['nb_personnes'],
            'date' => new MongoDB\BSON\UTCDateTime()
        ]);
        $manager->executeBulkWrite(MONGO_DB . '.' . MONGO_COLLECTION, $bulk);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function mongoGetCAparMenu($menuId = null, $dateDebut = null, $dateFin = null) {
    $manager = getMongoManager();
    if (!$manager) {
        return [];
    }
    $filter = [];
    if ($menuId) {
        $filter['menu_id'] = (int)$menuId;
    }
    if ($dateDebut || $dateFin) {
        $filter['date'] = [];
        if ($dateDebut) {
            $filter['date']['$gte'] = new MongoDB\BSON\UTCDateTime(strtotime($dateDebut) * 1000);
        }
        if ($dateFin) {
            $filter['date']['$lte'] = new MongoDB\BSON\UTCDateTime(strtotime($dateFin . ' 23:59:59') * 1000);
        }
    }
    try {
        $query = new MongoDB\Driver\Query($filter);
        $cursor = $manager->executeQuery(MONGO_DB . '.' . MONGO_COLLECTION, $query);
        $result = [];
        foreach ($cursor as $doc) {
            $mid = $doc->menu_id ?? 0;
            $titre = $doc->menu_titre ?? 'Inconnu';
            if (!isset($result[$mid])) {
                $result[$mid] = ['menu_id' => $mid, 'titre' => $titre, 'total' => 0, 'count' => 0];
            }
            $result[$mid]['total'] += (float)($doc->prix_total ?? 0);
            $result[$mid]['count']++;
        }
        return array_values($result);
    } catch (Exception $e) {
        return [];
    }
}

function mongoIsAvailable() {
    return getMongoManager() !== null;
}
