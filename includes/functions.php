<?php
// functions.php - Version corrigée pour le déploiement

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
 * Fonction pour les produits - CORRIGÉE
 */
function getProductImage($productId, $imageUrl = null) {
    $basePath = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/products/';
    $baseUrl = BASE_URL . 'assets/img/products/';
    
    // 1. Vérifier l'image personnalisée d'abord
    if ($imageUrl && !empty($imageUrl)) {
        // Si c'est une URL complète, la retourner directement
        if (strpos($imageUrl, 'http') === 0 || strpos($imageUrl, 'data:image') === 0) {
            return $imageUrl;
        }
        
        // Si c'est un chemin relatif, vérifier s'il existe
        $cleanPath = ltrim($imageUrl, '/');
        
        // Vérifier si le fichier existe dans le dossier products
        $testPath = $basePath . basename($cleanPath);
        if (file_exists($testPath)) {
            return $baseUrl . basename($cleanPath);
        }
        
        // Vérifier si le fichier existe à l'emplacement exact
        $exactPath = $_SERVER['DOCUMENT_ROOT'] . $imageUrl;
        if (file_exists($exactPath)) {
            return BASE_URL . ltrim($imageUrl, '/');
        }
    }
    
    // 2. Vérifier si l'image existe par ID
    $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    foreach ($extensions as $ext) {
        $imagePath = $basePath . $productId . '.' . $ext;
        if (file_exists($imagePath)) {
            return $baseUrl . $productId . '.' . $ext;
        }
    }
    
    // 3. Image par défaut
    $defaultImage = $baseUrl . 'default.jpg';
    $defaultPath = $basePath . 'default.jpg';
    
    if (file_exists($defaultPath)) {
        return $defaultImage;
    }
    
    return 'https://via.placeholder.com/400x300/4ECDC4/ffffff?text=Produit+Non+Disponible';
}

/**
 * Fonction pour les restaurants - CORRIGÉE
 */
function getRestaurantImage($restaurantId, $imageUrl = null) {
    $basePath = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/restaurants/';
    $baseUrl = BASE_URL . 'assets/img/restaurants/';
    
    // 1. Vérifier l'image personnalisée d'abord
    if ($imageUrl && !empty($imageUrl)) {
        // Si c'est une URL complète, la retourner directement
        if (strpos($imageUrl, 'http') === 0 || strpos($imageUrl, 'data:image') === 0) {
            return $imageUrl;
        }
        
        // Si c'est un chemin relatif, vérifier s'il existe
        $cleanPath = ltrim($imageUrl, '/');
        
        // Vérifier si le fichier existe dans le dossier restaurants
        $testPath = $basePath . basename($cleanPath);
        if (file_exists($testPath)) {
            return $baseUrl . basename($cleanPath);
        }
        
        // Vérifier si le fichier existe à l'emplacement exact
        $exactPath = $_SERVER['DOCUMENT_ROOT'] . $imageUrl;
        if (file_exists($exactPath)) {
            return BASE_URL . ltrim($imageUrl, '/');
        }
    }
    
    // 2. Vérifier si l'image existe par ID
    $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    foreach ($extensions as $ext) {
        $imagePath = $basePath . $restaurantId . '.' . $ext;
        if (file_exists($imagePath)) {
            return $baseUrl . $restaurantId . '.' . $ext;
        }
    }
    
    // 3. Image par défaut
    $defaultImage = $baseUrl . 'default.jpg';
    $defaultPath = $basePath . 'default.jpg';
    
    if (file_exists($defaultPath)) {
        return $defaultImage;
    }
    
    return 'https://via.placeholder.com/600x400/FF6B6B/ffffff?text=Restaurant+Non+Disponible';
}

/**
 * Fonction pour uploader les images des produits
 */
function handleProductImageUpload($fileInputName, $currentImage = null) {
    return handleImageUpload($fileInputName, 'products', $currentImage);
}

/**
 * Fonction pour uploader les images des restaurants
 */
function handleRestaurantImageUpload($fileInputName, $currentImage = null) {
    return handleImageUpload($fileInputName, 'restaurants', $currentImage);
}

/**
 * Fonction générique pour uploader les images - CORRIGÉE
 */
function handleImageUpload($fileInputName, $type = 'products', $currentImage = null) {
    $basePath = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/' . $type . '/';
    
    // Créer le dossier s'il n'existe pas
    if (!file_exists($basePath)) {
        mkdir($basePath, 0755, true);
    }

    // Supprimer l'ancienne image si elle existe
    if ($currentImage && !empty($currentImage)) {
        // Nettoyer le chemin de l'image
        $imagePath = str_replace(BASE_URL, '', $currentImage);
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $imagePath;
        
        if (file_exists($fullPath) && is_file($fullPath)) {
            unlink($fullPath);
        }
    }
    
    // Vérifier si un fichier a été uploadé
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Vérifier le type de fichier
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = $_FILES[$fileInputName]['type'];
    
    if (!in_array($fileType, $allowedTypes)) {
        return null;
    }
    
    // Générer un nom de fichier unique
    $ext = strtolower(pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION));
    $filename = uniqid() . '.' . $ext;
    $destination = $basePath . $filename;
    
    if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $destination)) {
        return BASE_URL . 'assets/img/' . $type . '/' . $filename;
    }
    
    return null;
}

/**
 * Fonction pour obtenir l'URL d'image d'un menu avec cache-buster - CORRIGÉE
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
