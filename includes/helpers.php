<?php

function getBadge($theme){
    $themeLower = strtolower($theme);
    $badgeClass = match($themeLower){
        "vegan" => "badge-vegan",
        "mariage" => "badge-mariage",
        "noel" => "badge-noel",
        default => "badge-default"
    };
    $icon = match($themeLower){
        "vegan" => "🌱",
        "mariage" => "💍",
        "noel" => "🎄",
        default => "🍽️"
    };
    return ["class" => $badgeClass, "icon" => $icon];
}

function sendMail($to, $subject, $body){
    $headers = "From: contact@vite-gourmand.fr\r\nContent-Type: text/plain; charset=UTF-8";
    return @mail($to, $subject, $body, $headers);
}

function validatePassword($password){
    if(strlen($password) < 10) return "Le mot de passe doit contenir au moins 10 caracteres.";
    if(!preg_match('/[A-Z]/', $password)) return "Le mot de passe doit contenir une majuscule.";
    if(!preg_match('/[a-z]/', $password)) return "Le mot de passe doit contenir une minuscule.";
    if(!preg_match('/[0-9]/', $password)) return "Le mot de passe doit contenir un chiffre.";
    if(!preg_match('/[^A-Za-z0-9]/', $password)) return "Le mot de passe doit contenir un caractere special.";
    return null;
}

function getStatutsCommande(){
    return [
        'en_attente' => 'En attente',
        'acceptee' => 'Acceptee',
        'en_preparation' => 'En preparation',
        'en_livraison' => 'En cours de livraison',
        'livre' => 'Livre',
        'en_attente_materiel' => 'En attente retour materiel',
        'terminee' => 'Terminee',
        'annulee' => 'Annulee'
    ];
}

function getStatutBadgeClass($statut){
    return match($statut){
        'en_attente' => 'secondary',
        'acceptee' => 'primary',
        'en_preparation' => 'info',
        'en_livraison' => 'warning',
        'livre' => 'success',
        'en_attente_materiel' => 'dark',
        'terminee' => 'success',
        'annulee' => 'danger',
        default => 'secondary'
    };
}

function enregistrerHistorique($pdo, $commande_id, $statut, $note = null){
    $stmt = $pdo->prepare('INSERT INTO commande_historique (commande_id, statut, note) VALUES (?, ?, ?)');
    $stmt->execute([$commande_id, $statut, $note]);
}


function csrfToken() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

function verifyCsrf($token) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}
