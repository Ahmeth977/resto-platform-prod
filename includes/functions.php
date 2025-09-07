<?php
require_once __DIR__ . '/config.php';
// Fonction pour vérifier les autorisations
function checkAdminAccess() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== ROLE_ADMIN) {
        header("Location: /login.php?error=access_denied");
        exit();
    }
}

// Fonction pour vérifier si l'utilisateur est admin
function isAdmin() {
    return isset($_SESSION['user_id']) && $_SESSION['user_role'] === ROLE_ADMIN;
}
// Fonction pour les produits
function getProductImage($productId, $imageUrl = null) {
    $basePath = IMG_BASE_PATH . 'products/';
    $baseUrl = IMG_BASE_URL . 'products/';
    
    // Debug: Afficher les chemins pour vérification
    error_log("DEBUG PRODUCT: basePath = $basePath");
    error_log("DEBUG PRODUCT: baseUrl = $baseUrl");
    error_log("DEBUG PRODUCT: imageUrl = " . ($imageUrl ?: 'null'));
    error_log("DEBUG PRODUCT: document_root = " . $_SERVER['DOCUMENT_ROOT']);
    
    // 1. Vérifier l'image personnalisée d'abord
    if ($imageUrl && !empty($imageUrl)) {
        // Si c'est déjà une URL complète
        if (strpos($imageUrl, 'http') === 0 || strpos($imageUrl, 'data:image') === 0) {
            return $imageUrl;
        }
        
        // Vérifier si le fichier existe (plusieurs chemins possibles)
        $possiblePaths = [
            $_SERVER['DOCUMENT_ROOT'] . $imageUrl,
            IMG_BASE_PATH . basename($imageUrl),
            $imageUrl // Chemin absolu déjà
        ];
        
        foreach ($possiblePaths as $testPath) {
            error_log("DEBUG PRODUCT: Testing path: $testPath");
            if (file_exists($testPath) && is_file($testPath)) {
                // Retourner le chemin relatif pour le web
                if (strpos($testPath, $_SERVER['DOCUMENT_ROOT']) === 0) {
                    return str_replace($_SERVER['DOCUMENT_ROOT'], '', $testPath);
                }
                return $imageUrl;
            }
        }
    }
    
    // 2. Vérifier si l'image existe par ID
    $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    foreach ($extensions as $ext) {
        $imagePath = $basePath . $productId . '.' . $ext;
        error_log("DEBUG PRODUCT: Testing ID path: $imagePath");
        if (file_exists($imagePath)) {
            return $baseUrl . $productId . '.' . $ext;
        }
    }
    
    // 3. Image par défaut du dossier
    $defaultImage = $baseUrl . 'default.jpg';
    $defaultPath = $basePath . 'default.jpg';
    error_log("DEBUG PRODUCT: Testing default: $defaultPath");
    
    if (file_exists($defaultPath)) {
        return $defaultImage;
    }
    
    // 4. Fallback vers placeholder
    return 'https://via.placeholder.com/400x300/4ECDC4/ffffff?text=Produit+Non+Disponible';
}

// Fonction pour les restaurants
function getRestaurantImage($restaurantId, $imageUrl = null) {
    $basePath = IMG_BASE_PATH . 'restaurants/';
    $baseUrl = IMG_BASE_URL . 'restaurants/';
    
    // Debug: Afficher les chemins pour vérification
    error_log("DEBUG RESTAURANT: basePath = $basePath");
    error_log("DEBUG RESTAURANT: baseUrl = $baseUrl");
    error_log("DEBUG RESTAURANT: imageUrl = " . ($imageUrl ?: 'null'));
    
    // 1. Vérifier l'image personnalisée d'abord
    if ($imageUrl && !empty($imageUrl)) {
        // Si c'est déjà une URL complète
        if (strpos($imageUrl, 'http') === 0 || strpos($imageUrl, 'data:image') === 0) {
            return $imageUrl;
        }
        
        // Vérifier si le fichier existe (plusieurs chemins possibles)
        $possiblePaths = [
            $_SERVER['DOCUMENT_ROOT'] . $imageUrl,
            IMG_BASE_PATH . basename($imageUrl),
            $imageUrl // Chemin absolu déjà
        ];
        
        foreach ($possiblePaths as $testPath) {
            error_log("DEBUG RESTAURANT: Testing path: $testPath");
            if (file_exists($testPath) && is_file($testPath)) {
                // Retourner le chemin relatif pour le web
                if (strpos($testPath, $_SERVER['DOCUMENT_ROOT']) === 0) {
                    return str_replace($_SERVER['DOCUMENT_ROOT'], '', $testPath);
                }
                return $imageUrl;
            }
        }
    }
    
    // 2. Vérifier si l'image existe par ID
    $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    foreach ($extensions as $ext) {
        $imagePath = $basePath . $restaurantId . '.' . $ext;
        error_log("DEBUG RESTAURANT: Testing ID path: $imagePath");
        if (file_exists($imagePath)) {
            return $baseUrl . $restaurantId . '.' . $ext;
        }
    }
    
    // 3. Image par défaut du dossier
    $defaultImage = $baseUrl . 'default.jpg';
    $defaultPath = $basePath . 'default.jpg';
    error_log("DEBUG RESTAURANT: Testing default: $defaultPath");
    
    if (file_exists($defaultPath)) {
        return $defaultImage;
    }
    
    // 4. Fallback vers placeholder
    return 'https://via.placeholder.com/600x400/FF6B6B/ffffff?text=Restaurant+Non+Disponible';
}

// Fonction pour uploader les images des produits
function handleProductImageUpload($fileInputName, $currentImage = null) {
    return handleImageUpload($fileInputName, 'products', $currentImage);
}

// Fonction pour uploader les images des restaurants
function handleRestaurantImageUpload($fileInputName, $currentImage = null) {
    return handleImageUpload($fileInputName, 'restaurants', $currentImage);
}

// Fonction générique pour uploader les images
function handleImageUpload($fileInputName, $type = 'products', $currentImage = null) {
    $basePath = IMG_BASE_PATH . $type . '/';
    
    // Créer le dossier s'il n'existe pas
    if (!file_exists($basePath)) {
        mkdir($basePath, 0755, true);
    }

    // Supprimer l'ancienne image si elle existe
    if ($currentImage && !empty($currentImage)) {
        $pathsToCheck = [
            $_SERVER['DOCUMENT_ROOT'] . $currentImage,
            IMG_BASE_PATH . basename($currentImage),
            $currentImage
        ];
        
        foreach ($pathsToCheck as $pathToDelete) {
            if (file_exists($pathToDelete) && is_file($pathToDelete)) {
                unlink($pathToDelete);
                break;
            }
        }
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

// Fonction de debug pour vérifier les chemins
function debugImagePaths() {
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
// Fonction pour afficher les articles du panier
  

?>