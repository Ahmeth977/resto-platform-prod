<?php
// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la configuration de la base de données
require_once __DIR__.'/../includes/config.php';

// Redirection si non connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../acceuil.php?error=access_denied");
    exit();
}

// Redirection si pas administrateur
if ($_SESSION['user_role'] !== ROLE_ADMIN) {
    header("Location: ../acceuil.php?error=access_denied");
    exit();
}

// Vérification supplémentaire : l'utilisateur existe-t-il toujours en base?
try {
    $stmt = $db->prepare("SELECT id, is_active FROM users WHERE id = ? AND role = ?");
    $stmt->execute([$_SESSION['user_id'], ROLE_ADMIN]);
    $user = $stmt->fetch();

    if (!$user || !$user['is_active']) {
        session_destroy();
        header("Location: ../acceuil.php?error=account_inactive");
        exit();
    }
} catch(PDOException $e) {
    error_log("Erreur de vérification admin: " . $e->getMessage());
    session_destroy();
    header("Location: ../acceuil.php?error=server_error");
    exit();
}

// Régénération périodique de l'ID de session pour prévenir le fixation
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} else if (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Protection contre le clickjacking
header('X-Frame-Options: SAMEORIGIN');

// Protection contre le MIME-type sniffing
header('X-Content-Type-Options: nosniff');

// Protection XSS
header('X-XSS-Protection: 1; mode=block');
?>