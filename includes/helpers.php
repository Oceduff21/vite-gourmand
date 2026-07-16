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

function getStatutLabel(string $statut): string
{
    $statuts = getStatutsCommande();
    return $statuts[$statut] ?? $statut;
}

function enregistrerHistorique($pdo, $commande_id, $statut, $note = null, $staffId = null){
    if ($staffId) {
        $suffix = '|staff:' . (int)$staffId;
        $note = ($note ?? '') === '' ? ltrim($suffix, '|') : ($note . $suffix);
    }
    $stmt = $pdo->prepare('INSERT INTO commande_historique (commande_id, statut, note) VALUES (?, ?, ?)');
    $stmt->execute([$commande_id, $statut, $note]);
}

function analyseDelaiLivraison(string $dateLivraison, int $delaiJours, ?string $createdAt = null): array
{
    $base = $createdAt ? new DateTime($createdAt) : new DateTime();
    $base->setTime(0, 0, 0);
    $minDate = (clone $base)->modify('+' . max(0, $delaiJours) . ' days');
    $livraison = new DateTime($dateLivraison);
    $livraison->setTime(0, 0, 0);
    $today = new DateTime('today');

    $delaiRespecte = $livraison >= $minDate;
    $joursMarge = (int)round(($livraison->getTimestamp() - $minDate->getTimestamp()) / 86400);
    $joursAvantLivraison = (int)round(($livraison->getTimestamp() - $today->getTimestamp()) / 86400);

    return [
        'delai_jours' => $delaiJours,
        'date_commande' => $base->format('d/m/Y'),
        'date_min_livraison' => $minDate->format('d/m/Y'),
        'date_livraison' => $livraison->format('d/m/Y'),
        'delai_respecte' => $delaiRespecte,
        'jours_marge' => $joursMarge,
        'jours_avant_livraison' => $joursAvantLivraison,
        'livraison_passee' => $livraison < $today,
        'urgence' => $joursAvantLivraison >= 0 && $joursAvantLivraison <= 3,
    ];
}

function getDelaiBadgeClass(array $delai): string
{
    if ($delai['livraison_passee']) {
        return 'danger';
    }
    if (!$delai['delai_respecte']) {
        return 'danger';
    }
    if ($delai['urgence']) {
        return 'warning';
    }
    return 'success';
}

function getDelaiBadgeLabel(array $delai): string
{
    if ($delai['livraison_passee']) {
        return 'Date passee';
    }
    if (!$delai['delai_respecte']) {
        return 'Delai insuffisant';
    }
    if ($delai['urgence']) {
        return 'Urgent (' . $delai['jours_avant_livraison'] . ' j)';
    }
    return 'Delai OK';
}

function getDateMinLivraison(int $delaiJours, ?string $fromDate = null): string
{
    $base = $fromDate ? new DateTime($fromDate) : new DateTime('today');
    $base->setTime(0, 0, 0);
    $base->modify('+' . max(0, $delaiJours) . ' days');
    return $base->format('Y-m-d');
}

function validateDateLivraisonMenu(string $dateLivraison, int $delaiJours, ?string $fromDate = null): ?string
{
    if ($dateLivraison === '') {
        return 'Date de livraison obligatoire.';
    }
    try {
        $delai = analyseDelaiLivraison($dateLivraison, $delaiJours, $fromDate);
    } catch (Throwable $e) {
        return 'Date de livraison invalide.';
    }
    if (!$delai['delai_respecte']) {
        return 'Delai de reservation insuffisant : livraison possible a partir du ' . $delai['date_min_livraison'] . ' (' . $delaiJours . ' jour(s) minimum).';
    }
    return null;
}

function calculateLivraisonPrice(string $ville, string $codePostal): float
{
    if (strtolower(trim($ville)) === 'bordeaux') {
        return 0.0;
    }
    $livraison = 5.0;
    $cp = trim($codePostal);
    if (str_starts_with($cp, '33')) {
        $livraison += 8 * 0.59;
    } elseif (str_starts_with($cp, '24') || str_starts_with($cp, '47')) {
        $livraison += 15 * 0.59;
    } else {
        $livraison += 25 * 0.59;
    }
    return round($livraison, 2);
}

function calculateCommandeReduction(float $totalMenu, int $quantite, int $minPersonnes): float
{
    if ($quantite >= ($minPersonnes + 5)) {
        return round($totalMenu * 0.10, 2);
    }
    return 0.0;
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

function getAllergenesReferentiel(): array
{
    return [
        'gluten' => 'Gluten',
        'lactose' => 'Lactose',
        'oeufs' => 'Oeufs',
        'poisson' => 'Poisson',
        'crustaces' => 'Crustaces',
        'fruits a coque' => 'Fruits a coque',
        'arachides' => 'Arachides',
        'soja' => 'Soja',
        'sesame' => 'Sesame',
        'sulfites' => 'Sulfites',
        'celeri' => 'Celeri',
        'moutarde' => 'Moutarde',
        'lupin' => 'Lupin',
        'mollusques' => 'Mollusques',
    ];
}

function parseAllergenes(?string $raw): array
{
    if ($raw === null || trim($raw) === '') {
        return [];
    }
    $parts = preg_split('/[,;]+/', strtolower($raw));
    return array_values(array_filter(array_map('trim', $parts ?: [])));
}

function formatAllergeneLabel(string $key): string
{
    $ref = getAllergenesReferentiel();
    return $ref[$key] ?? ucfirst($key);
}

function renderAllergenesBadges(?string $raw, bool $showEmpty = true): string
{
    $items = parseAllergenes($raw);
    if (empty($items)) {
        if (!$showEmpty) {
            return '';
        }
        return '<span class="badge allergen-none">Aucun allergene declare</span>';
    }
    $html = '';
    foreach ($items as $item) {
        $label = formatAllergeneLabel($item);
        $html .= '<span class="badge allergen-badge" title="Allergene : ' . htmlspecialchars($label) . '">'
            . htmlspecialchars($label) . '</span>';
    }
    return $html;
}

function sendSecurityHeaders(): void
{
    if (headers_sent()) {
        return;
    }
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-XSS-Protection: 1; mode=block');
}

function secureSessionLogin(int $userId, string $role, array $extra = []): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_role'] = $role;
    foreach ($extra as $key => $value) {
        $_SESSION[$key] = $value;
    }
}

function isAdminUser(): bool
{
    return in_array($_SESSION['user_role'] ?? '', ['admin', 'employe'], true);
}

function isSuperAdmin(): bool
{
    return ($_SESSION['user_role'] ?? '') === 'admin';
}

function isEmploye(): bool
{
    return ($_SESSION['user_role'] ?? '') === 'employe';
}

function canViewAdminFinancials(): bool
{
    return isSuperAdmin();
}

function getStaffDisplayName(): string
{
    $prenom = trim($_SESSION['user_prenom'] ?? '');
    $nom = trim($_SESSION['user_nom'] ?? '');
    $full = trim($prenom . ' ' . $nom);
    return $full !== '' ? $full : 'Employe';
}

function countStaffActions(PDO $pdo, int $staffId): int
{
    if ($staffId <= 0) {
        return 0;
    }
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM commande_historique WHERE note LIKE ?');
        $stmt->execute(['%|staff:' . $staffId . '%']);
        return (int)$stmt->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}
