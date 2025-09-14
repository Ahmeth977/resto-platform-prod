<?php
// functions.php - Version corrigée pour le déploiement

// Ne plus redéfinir les constantes qui sont déjà dans config.php
// Utiliser directement les constantes définies dans config.php

/**
 * Fonction pour vérifier les autorisations - CORRIGÉE
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
 * Fonction pour les produits - OPTIMISÉE
 */

// functions.php - Version corrigée

/**
 * Fonction pour les produits - CORRIGÉE pour App Engine
/**
 * Fonction pour les produits - CORRIGÉE
 */
function getProductImage($productId, $imageUrl = null) {
    $basePath = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/products/';
    $baseUrl = BASE_URL . 'assets/img/products/';
    
    // 1. Vérifier l'image personnalisée d'abord
    if ($imageUrl && !empty($imageUrl)) {
        // Si c'est un chemin relatif, le convertir en chemin absolu
        if (strpos($imageUrl, 'http') !== 0 && strpos($imageUrl, 'data:image') !== 0) {
            // Nettoyer le chemin
            $cleanPath = ltrim($imageUrl, '/');
            $testPath = $basePath . basename($cleanPath);
            
            if (file_exists($testPath)) {
                return $baseUrl . basename($cleanPath);
            }
        } else {
            return $imageUrl; // URL absolue
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
    return $baseUrl . 'default.jpg';
}

/**
 * Fonction pour les restaurants - CORRIGÉE
 */
function getRestaurantImage($restaurantId, $imageUrl = null) {
    $basePath = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/restaurants/';
    $baseUrl = BASE_URL . 'assets/img/restaurants/';
    
    // 1. Vérifier l'image personnalisée d'abord
    if ($imageUrl && !empty($imageUrl)) {
        // Si c'est un chemin relatif, le convertir en chemin absolu
        if (strpos($imageUrl, 'http') !== 0 && strpos($imageUrl, 'data:image') !== 0) {
            // Nettoyer le chemin
            $cleanPath = ltrim($imageUrl, '/');
            $testPath = $basePath . basename($cleanPath);
            
            if (file_exists($testPath)) {
                return $baseUrl . basename($cleanPath);
            }
        } else {
            return $imageUrl; // URL absolue
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
    return $baseUrl . 'default.jpg';
}
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
    $basePath = IMG_BASE_PATH . $type . '/';
    
    // Créer le dossier s'il n'existe pas
    if (!file_exists($basePath)) {
        mkdir($basePath, 0755, true);
    }

    // Supprimer l'ancienne image si elle existe
    if ($currentImage && !empty($currentImage)) {
        // Nettoyer le chemin de l'image
        $imagePath = str_replace(IMG_BASE_URL, '', $currentImage);
        $fullPath = IMG_BASE_PATH . $imagePath;
        
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
        return IMG_BASE_URL . $type . '/' . $filename;
    }
    
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
    echo "IMG_BASE_PATH: " . IMG_BASE_PATH . "<br>";
    echo "IMG_BASE_URL: " . IMG_BASE_URL . "<br>";
    echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
    
    // Vérifier l'existence des dossiers
    $types = ['products', 'restaurants'];
    foreach ($types as $type) {
        $path = IMG_BASE_PATH . $type . '/';
        echo "Dossier $type: " . (is_dir($path) ? "EXISTE" : "MANQUANT") . " ($path)<br>";
        
        // Lister les fichiers dans le dossier
        if (is_dir($path)) {
            $files = scandir($path);
            echo "Fichiers dans $type: " . implode(', ', array_filter($files, function($file) {
                return $file !== '.' && $file !== '..';
            })) . "<br>";
        }
    }
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

// Note: La fonction pour afficher les articles du panier n'est pas définie dans le code fourni
// Vous devrez l'implémenter séparément
?>
