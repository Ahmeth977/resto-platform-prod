<?php
// config.php

// 1. Constantes des rôles utilisateurs
define('ROLE_ADMIN', 'admin');
define('ROLE_RESTAURATEUR', 'restaurateur');
define('ROLE_CLIENT', 'client');

// 2. Configuration BDD pour Google Cloud SQL - Même configuration que database.php
$dbHost = getenv('DB_HOST') ?: '34.52.242.229';
$dbName = getenv('DB_NAME') ?: 'resto_platform';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '781155609';
$dbPort = 3306; // Port MySQL standard

// 3. Chemins système - Détection automatique pour différents environnements
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);

// Déterminer BASE_URL automatiquement
if ($host === 'localhost' || $host === '127.0.0.1') {
    // Environnement de développement local
    define('BASE_URL', $protocol . '://' . $host . $scriptPath . '/');
} else {
    // Environnement de production (Google Cloud)
    // Supprime le sous-dossier "resto_plateform" pour la production
    $basePath = str_replace('/resto_plateform', '', $scriptPath);
    define('BASE_URL', $protocol . '://' . $host . $basePath . '/');
}

define('ASSETS_URL', BASE_URL . 'assets/');
define('ROOT_PATH', dirname(__DIR__));

// 4. Configuration des chemins d'images
define('IMG_BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/assets/img/');
define('IMG_BASE_URL', BASE_URL . 'assets/img/');

// 5. Fuseau horaire du Sénégal
date_default_timezone_set('Africa/Dakar');

// 6. Mode Développement - détection automatique
define('DEV_MODE', ($host === 'localhost' || $host === '127.0.0.1' || strpos($host, '.test') !== false));

// 7. Démarrer la session avec configuration sécurisée
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400, // 24 heures
        'path' => '/',
        'domain' => $host,
        'secure' => ($protocol === 'https'),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

/* *************************** */
/* FONCTIONS PRINCIPALES       */
/* *************************** */

/**
 * Connexion sécurisée à la base de données avec la même logique que Database.php
 */
function connectDB() {
    global $dbHost, $dbName, $dbUser, $dbPass, $dbPort;
    
    try {
        // Connexion TCP/IP forcée - IMPORTANT: pas de socket Unix!
        $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8";
        
        $conn = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => false
        ]);
        
        return $conn;
        
    } catch(PDOException $exception) {
        // Journalisation de l'erreur
        error_log("[" . date('Y-m-d H:i:s') . "] MySQL Connection Error: " . $exception->getMessage());
        error_log("Trying to connect to: {$dbHost}:{$dbPort}, db: {$dbName}");
        
        // Message adapté selon l'environnement
        if (DEV_MODE) {
            die("Erreur de connexion à la base de données: " . $exception->getMessage());
        } else {
            die("Désolé, notre service est momentanément indisponible. Veuillez réessayer ultérieurement.");
        }
    }
}

/**
 * Nettoie les données utilisateur (version sécurisée)
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    
    // Nettoyage de base
    $data = trim($data);
    $data = stripslashes($data);
    
    // Protection contre XSS
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Redirection sécurisée avec validation de l'URL
 */
function redirect($url) {
    // Nettoyer et valider l'URL
    $url = filter_var($url, FILTER_SANITIZE_URL);
    
    // S'assurer que l'URL commence par un slash
    if (strpos($url, '/') !== 0) {
        $url = '/' . $url;
    }
    
    // Éviter les redirections ouvertes
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
        // Autoriser seulement les URLs de notre domaine
        $allowedDomain = parse_url(BASE_URL, PHP_URL_HOST);
        $urlDomain = parse_url($url, PHP_URL_HOST);
        
        if ($urlDomain !== $allowedDomain) {
            $url = '/'; // Rediriger vers la page d'accueil en cas de domaine non autorisé
        }
    } else {
        // URL relative - construire l'URL complète
        $url = BASE_URL . ltrim($url, '/');
    }
    
    header("Location: " . $url);
    exit;
}

/**
 * Génère une URL complète pour une ressource
 */
function asset_url($path) {
    return BASE_URL . 'assets/' . ltrim($path, '/');
}

/**
 * Génère une URL complète pour une image
 */
function image_url($path) {
    return BASE_URL . 'assets/img/' . ltrim($path, '/');
}

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
 * Fonction pour les produits
 */
function getProductImage($productId, $imageUrl = null) {
    $basePath = IMG_BASE_PATH . 'products/';
    $baseUrl = IMG_BASE_URL . 'products/';
    
    // Debug: Afficher les chemins pour vérification
    if (DEV_MODE) {
        error_log("DEBUG PRODUCT: basePath = $basePath");
        error_log("DEBUG PRODUCT: baseUrl = $baseUrl");
        error_log("DEBUG PRODUCT: imageUrl = " . ($imageUrl ?: 'null'));
        error_log("DEBUG PRODUCT: document_root = " . $_SERVER['DOCUMENT_ROOT']);
    }
    
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
            if (DEV_MODE) {
                error_log("DEBUG PRODUCT: Testing path: $testPath");
            }
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
        if (DEV_MODE) {
            error_log("DEBUG PRODUCT: Testing ID path: $imagePath");
        }
        if (file_exists($imagePath)) {
            return $baseUrl . $productId . '.' . $ext;
        }
    }
    
    // 3. Image par défaut du dossier
    $defaultImage = $baseUrl . 'default.jpg';
    $defaultPath = $basePath . 'default.jpg';
    if (DEV_MODE) {
        error_log("DEBUG PRODUCT: Testing default: $defaultPath");
    }
    
    if (file_exists($defaultPath)) {
        return $defaultImage;
    }
    
    // 4. Fallback vers placeholder
    return 'https://via.placeholder.com/400x300/4ECDC4/ffffff?text=Produit+Non+Disponible';
}

/**
 * Fonction pour les restaurants
 */
function getRestaurantImage($restaurantId, $imageUrl = null) {
    $basePath = IMG_BASE_PATH . 'restaurants/';
    $baseUrl = IMG_BASE_URL . 'restaurants/';
    
    // Debug: Afficher les chemins pour vérification
    if (DEV_MODE) {
        error_log("DEBUG RESTAURANT: basePath = $basePath");
        error_log("DEBUG RESTAURANT: baseUrl = $baseUrl");
        error_log("DEBUG RESTAURANT: imageUrl = " . ($imageUrl ?: 'null'));
    }
    
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
            if (DEV_MODE) {
                error_log("DEBUG RESTAURANT: Testing path: $testPath");
            }
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
        if (DEV_MODE) {
            error_log("DEBUG RESTAURANT: Testing ID path: $imagePath");
        }
        if (file_exists($imagePath)) {
            return $baseUrl . $restaurantId . '.' . $ext;
        }
    }
    
    // 3. Image par défaut du dossier
    $defaultImage = $baseUrl . 'default.jpg';
    $defaultPath = $basePath . 'default.jpg';
    if (DEV_MODE) {
        error_log("DEBUG RESTAURANT: Testing default: $defaultPath");
    }
    
    if (file_exists($defaultPath)) {
        return $defaultImage;
    }
    
    // 4. Fallback vers placeholder
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
 * Fonction générique pour uploader les images
 */
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
    
    // Vérifier si un fichier a été uploadé
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
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
 * Fonction de debug pour vérifier les chemins
 */
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

/* *************************** */
/* INITIALISATION DU SYSTÈME   */
/* *************************** */

// Affichage des erreurs en mode dev
if (DEV_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/logs/php_errors.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/logs/php_errors.log');
}

// Créer le répertoire de logs si nécessaire
if (!is_dir(ROOT_PATH . '/logs')) {
    mkdir(ROOT_PATH . '/logs', 0755, true);
}

// Test de connexion DB (seulement en mode développement)
if (DEV_MODE) {
    try {
        $db = connectDB();
        $db->query("SELECT 1");
        
        // Connexion auto pour le développement
        $_SESSION['user_id'] = 1;
        $_SESSION['user_role'] = ROLE_ADMIN;
        
    } catch (PDOException $e) {
        die("<h1>Configuration requise</h1>
            <div class='alert alert-danger'>
            <p>Impossible de se connecter à la base de données.</p>
            <p><strong>Solution :</strong></p>
            <ol>
                <li>Vérifier que l'instance Cloud SQL est en cours d'exécution</li>
                <li>Vérifier les paramètres de connexion (hôte, utilisateur, mot de passe)</li>
                <li>Vérifier que la base de données existe</li>
            </ol>
            <pre>Erreur : {$e->getMessage()}</pre>
            </div>");
    }
}

// Fonction pour obtenir une connexion à la base de données (compatible avec l'ancien code)
function getDBConnection() {
    return connectDB();
}
?>
