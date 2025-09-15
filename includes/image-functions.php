<?php
// image-functions.php - Fonctions optimisées pour les images sur App Engine

/**
 * Fonction simplifiée pour les images de produits
 */
function getProductImage($productId, $imageUrl = null) {
    $baseUrl = BASE_URL . 'assets/img/products/';
    
    // 1. Si une URL d'image est fournie, l'utiliser
    if (!empty($imageUrl)) {
        // Si c'est une URL absolue, la retourner directement
        if (strpos($imageUrl, 'http') === 0 || strpos($imageUrl, 'data:image') === 0) {
            return $imageUrl;
        }
        // Sinon, construire le chemin relatif
        return $baseUrl . basename($imageUrl);
    }
    
    // 2. Sinon, utiliser l'ID avec extension .jpg
    return $baseUrl . $productId . '.jpg';
}

/**
 * Fonction simplifiée pour les images de restaurants
 */
function getRestaurantImage($restaurantId, $imageUrl = null) {
    $baseUrl = BASE_URL . 'assets/img/restaurants/';
    
    // 1. Si une URL d'image est fournie, l'utiliser
    if (!empty($imageUrl)) {
        // Si c'est une URL absolue, la retourner directement
        if (strpos($imageUrl, 'http') === 0 || strpos($imageUrl, 'data:image') === 0) {
            return $imageUrl;
        }
        // Sinon, construire le chemin relatif
        return $baseUrl . basename($imageUrl);
    }
    
    // 2. Sinon, utiliser l'ID avec extension .jpg
    return $baseUrl . $restaurantId . '.jpg';
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
?>
