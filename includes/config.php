<?php
// config.php - Configuration optimisée pour Google App Engine

// 1. Constantes des rôles utilisateurs
define('ROLE_ADMIN', 'admin');
define('ROLE_RESTAURATEUR', 'restaurateur');
define('ROLE_CLIENT', 'client');

// 2. Configuration BDD pour Google Cloud SQL
$dbHost = getenv('DB_HOST') ?: '34.52.242.229';
$dbName = getenv('DB_NAME') ?: 'resto_platform';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '781155609';
$dbPort = getenv('DB_PORT') ?: 3306;

// 3. Chemins système - Configuration CORRIGÉE
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

// Détection automatique de l'environnement
$isAppEngine = (getenv('GAE_APPLICATION') !== false);
$isLocal = ($host === 'localhost' || $host === '127.0.0.1' || strpos($host, '.test') !== false);

// CORRECTION IMPORTANTE: Utilisation du chemin racine correct
if ($isAppEngine) {
    // Environnement Google App Engine en production
    define('BASE_URL', 'https://sencommandes.ew.r.appspot.com/');
} else {
    // Environnement de développement local ou autre
    define('BASE_URL', $protocol . '://' . $host . '/');
}

define('ASSETS_URL', BASE_URL . 'assets/');
define('ROOT_PATH', dirname(__DIR__));

// 4. Configuration des chemins d'images
define('IMG_BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/assets/img/');
define('IMG_BASE_URL', BASE_URL . 'assets/img/');

// 5. Fuseau horaire du Sénégal
date_default_timezone_set('Africa/Dakar');

// 6. Mode Développement - détection automatique
define('DEV_MODE', $isLocal);

// 7. Démarrer la session avec configuration sécurisée
if (session_status() === PHP_SESSION_NONE) {
    $domain = $isAppEngine ? 'sencommandes.ew.r.appspot.com' : $host;
    
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => $domain,
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

/* *************************** */
/* FONCTIONS PRINCIPALES       */
/* *************************** */

/**
 * Connexion sécurisée à la base de données
 */
function connectDB() {
    global $dbHost, $dbName, $dbUser, $dbPass, $dbPort;
    
    try {
        // Détection de l'environnement
        $isAppEngine = (getenv('GAE_APPLICATION') !== false);
        
        if ($isAppEngine) {
            // Connexion via socket Unix (recommandé pour Google App Engine)
            $dsn = "mysql:unix_socket=/cloudsql/sencommandes:europe-west1:resto-platform-db;dbname={$dbName};charset=utf8mb4";
        } else {
            // Connexion TCP/IP pour le développement local
            $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
        }
        
        $conn = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => false
        ]);
        
        return $conn;
        
    } catch(PDOException $exception) {
        error_log("[" . date('Y-m-d H:i:s') . "] MySQL Connection Error: " . $exception->getMessage());
        
        if (DEV_MODE) {
            die("Erreur de connexion à la base de données: " . $exception->getMessage());
        } else {
            die("Désolé, notre service est momentanément indisponible. Veuillez réessayer ultérieurement.");
        }
    }
}

/**
 * Nettoie les données utilisateur
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Redirection sécurisée
 */
function redirect($url) {
    $url = filter_var($url, FILTER_SANITIZE_URL);
    
    if (strpos($url, '/') !== 0) {
        $url = '/' . $url;
    }
    
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
        $allowedDomain = parse_url(BASE_URL, PHP_URL_HOST);
        $urlDomain = parse_url($url, PHP_URL_HOST);
        
        if ($urlDomain !== $allowedDomain) {
            $url = BASE_URL;
        }
    } else {
        $url = BASE_URL . ltrim($url, '/');
    }
    
    header("Location: " . $url);
    exit;
}

/**
 * Génère une URL complète pour une ressource - CORRIGÉE
 */
function asset_url($path) {
    // Retirer le slash initial s'il existe
    $path = ltrim($path, '/');
    return BASE_URL . 'assets/' . $path;
}

/**
 * Génère une URL complète pour une image - CORRIGÉE
 */
function image_url($path) {
    // Retirer le slash initial s'il existe
    $path = ltrim($path, '/');
    return BASE_URL . 'assets/img/' . $path;
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
    
    if ($imageUrl && !empty($imageUrl)) {
        if (strpos($imageUrl, 'http') === 0 || strpos($imageUrl, 'data:image') === 0) {
            return $imageUrl;
        }
        
        $possiblePaths = [
            $_SERVER['DOCUMENT_ROOT'] . $imageUrl,
            IMG_BASE_PATH . basename($imageUrl),
            $imageUrl
        ];
        
        foreach ($possiblePaths as $testPath) {
            if (file_exists($testPath) && is_file($testPath)) {
                if (strpos($testPath, $_SERVER['DOCUMENT_ROOT']) === 0) {
                    return str_replace($_SERVER['DOCUMENT_ROOT'], '', $testPath);
                }
                return $imageUrl;
            }
        }
    }
    
    $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    foreach ($extensions as $ext) {
        $imagePath = $basePath . $productId . '.' . $ext;
        if (file_exists($imagePath)) {
            return $baseUrl . $productId . '.' . $ext;
        }
    }
    
    $defaultImage = $baseUrl . 'default.jpg';
    $defaultPath = $basePath . 'default.jpg';
    
    if (file_exists($defaultPath)) {
        return $defaultImage;
    }
    
    return 'https://via.placeholder.com/400x300/4ECDC4/ffffff?text=Produit+Non+Disponible';
}

/**
 * Fonction pour les restaurants
 */
function getRestaurantImage($restaurantId, $imageUrl = null) {
    $basePath = IMG_BASE_PATH . 'restaurants/';
    $baseUrl = IMG_BASE_URL . 'restaurants/';
    
    if ($imageUrl && !empty($imageUrl)) {
        if (strpos($imageUrl, 'http') === 0 || strpos($imageUrl, 'data:image') === 0) {
            return $imageUrl;
        }
        
        $possiblePaths = [
            $_SERVER['DOCUMENT_ROOT'] . $imageUrl,
            IMG_BASE_PATH . basename($imageUrl),
            $imageUrl
        ];
        
        foreach ($possiblePaths as $testPath) {
            if (file_exists($testPath) && is_file($testPath)) {
                if (strpos($testPath, $_SERVER['DOCUMENT_ROOT']) === 0) {
                    return str_replace($_SERVER['DOCUMENT_ROOT'], '', $testPath);
                }
                return $imageUrl;
            }
        }
    }
    
    $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    foreach ($extensions as $ext) {
        $imagePath = $basePath . $restaurantId . '.' . $ext;
        if (file_exists($imagePath)) {
            return $baseUrl . $restaurantId . '.' . $ext;
        }
    }
    
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
