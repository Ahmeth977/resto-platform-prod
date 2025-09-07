<?php
session_start();
require_once __DIR__.'/../../includes/config.php';

// Vérifier si l'utilisateur est connecté et est un gestionnaire
function is_manager_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'restaurateur';
}

// Rediriger si non connecté
if (!is_manager_logged_in()) {
    header("Location: /login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Récupérer l'ID du restaurant géré par cet utilisateur
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT r.* FROM restaurants r WHERE r.user_id = ?");
$stmt->execute([$user_id]);
$restaurant = $stmt->fetch();

if (!$restaurant) {
    // Cet utilisateur ne gère aucun restaurant
    session_destroy();
    header("Location: /login.php?error=no_restaurant");
    exit();
}

// Stocker les infos du restaurant en session pour usage facile
$_SESSION['restaurant_id'] = $restaurant['id'];
$_SESSION['restaurant_name'] = $restaurant['name'];
?>