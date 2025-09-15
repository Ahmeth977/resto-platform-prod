<?php
// functions.php - Version corrigée pour Google App Engine

/**
 * Fonction pour vérifier les autorisations
 */
function checkAdminAccess() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== ROLE_ADMIN) {
        header("Location: " . BASE_URL . "login.php?error=access_denied");
        exit();
    }
}

/**
 * Fonction pour vérifier si l'utilisateur est admin
 */
function isAdmin() {
    return isset($_SESSION['user_id']) && $_SESSION['user_role'] === ROLE_ADMIN;
}

/**
 * Fonction pour les produits - OPTIMISÉE pour App Engine
 */
function getProductImage($productId, $imageUrl = null) {
    $baseUrl = BASE_URL . 'assets/img/products/';
    
    // 1. Vérifier l'image personnalisée d'abord
    if (!empty($imageUrl)) {
        // Si c'est une URL absolue, la retourner directement
        if (strpos($imageUrl, 'http') === 0 || strpos($imageUrl, 'data:image') === 0) {
            return $imageUrl;
        }
        // Sinon, construire le chemin relatif
        return $baseUrl . basename($imageUrl);
    }
    
    // 2. Vérifier si l'image existe par ID (seulement en mode développement)
    if (defined('DEV_MODE') && DEV_MODE) {
        $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        foreach ($extensions as $ext) {
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/products/' . $productId . '.' . $ext;
            if (file_exists($imagePath)) {
                return $baseUrl . $productId . '.' . $ext;
            }
        }
    } else {
        // En production, on suppose que l'image existe
        return $baseUrl . $productId . '.jpg';
    }
    
    // 3. Image par défaut
    return $baseUrl . 'default.jpg';
}

/**
 * Fonction pour les restaurants - OPTIMISÉE pour App Engine
 */
function getRestaurantImage($restaurantId, $imageUrl = null) {
    $baseUrl = BASE_URL . 'assets/img/restaurants/';
    
    // 1. Vérifier l'image personnalisée d'abord
    if (!empty($imageUrl)) {
        // Si c'est une URL absolue, la retourner directement
        if (strpos($imageUrl, 'http') === 0 || strpos($imageUrl, 'data:image') === 0) {
            return $imageUrl;
        }
        // Sinon, construire le chemin relatif
        return $baseUrl . basename($imageUrl);
    }
    
    // 2. Vérifier si l'image existe par ID (seulement en mode développement)
    if (defined('DEV_MODE') && DEV_MODE) {
        $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        foreach ($extensions as $ext) {
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/restaurants/' . $restaurantId . '.' . $ext;
            if (file_exists($imagePath)) {
                return $baseUrl . $restaurantId . '.' . $ext;
            }
        }
    } else {
        // En production, on suppose que l'image existe
        return $baseUrl . $restaurantId . '.jpg';
    }
    
    // 3. Image par défaut
    return $baseUrl . 'default.jpg';
}

/**
 * Fonction pour obtenir l'URL d'image d'un menu avec cache-buster
 */
function getMenuImageUrl($imageUrl, $updatedAt = null) {
    if (empty($imageUrl)) {
        return BASE_URL . 'assets/img/placeholder-food.jpg?v=' . time();
    }
    
    // Si c'est un chemin relatif
    if (strpos($imageUrl, 'http') === false) {
        $imageUrl = BASE_URL . ltrim($imageUrl, '/');
    }
    
    // Ajouter un timestamp pour éviter le cache
    $timestamp = $updatedAt ? strtotime($updatedAt) : time();
    return $imageUrl . (strpos($imageUrl, '?') === false ? '?' : '&') . 'v=' . $timestamp;
}

// Fonctions manquantes pour l'upload (à implémenter si nécessaire)
// Note: Sur App Engine, l'upload direct n'est pas recommandé, utilisez Cloud Storage
function handleProductImageUpload($fileInputName, $currentImage = null) {
    // Implémentation pour l'upload (désactivée sur App Engine)
    return null;
}

function handleRestaurantImageUpload($fileInputName, $currentImage = null) {
    // Implémentation pour l'upload (désactivée sur App Engine)
    return null;
}

function handleImageUpload($fileInputName, $type = 'products', $currentImage = null) {
    // Implémentation pour l'upload (désactivée sur App Engine)
    return null;
}

/**
 * Fonction de debug pour vérifier les chemins - OPTIMISÉE
 */
function debugImagePaths() {
    if (!defined('DEV_MODE') || !DEV_MODE) {
        return; // Seulement en mode développement
    }
    
    echo "<h3>Debug des chemins d'images</h3>";
    echo "BASE_URL: " . BASE_URL . "<br>";
    echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
}
?>
