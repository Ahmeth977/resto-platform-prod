<?php
// config.php - Configuration optimisée pour Google App Engine

// 1. Constantes des rôles utilisateurs
define('ROLE_ADMIN', 'admin');
define('ROLE_RESTAURATEUR', 'restaurateur');
define('ROLE_CLIENT', 'client');
require_once __DIR__ . '/image-functions.php';
// 2. Configuration BDD pour Google Cloud SQL
$dbHost = getenv('DB_HOST') ?: '34.52.242.229';
$dbName = getenv('DB_NAME') ?: 'resto_platform';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '781155609';
$dbPort = getenv('DB_PORT') ?: 3306;

// 3. Chemins système - Configuration CORRIGÉE
$isAppEngine = (getenv('GAE_APPLICATION') !== false);
$isLocal = (getenv('GAE_APPLICATION') === false);

if ($isAppEngine) {
    // Environnement Google App Engine en production
    define('BASE_URL', 'https://sencommandes.ew.r.appspot.com/');
} else {
    // Environnement de développement local
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    define('BASE_URL', $protocol . '://' . $host . '/');
}

define('ASSETS_URL', BASE_URL . 'assets/');
define('ROOT_PATH', dirname(__DIR__));

// 4. Configuration des chemins d'images
define('IMG_BASE_URL', BASE_URL . 'assets/img/');

// 5. Fuseau horaire du Sénégal
date_default_timezone_set('Africa/Dakar');

// 6. Mode Développement - détection automatique
define('DEV_MODE', $isLocal);

// 7. Démarrer la session avec configuration sécurisée
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'secure' => !DEV_MODE, // seulement en HTTPS en production
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
        // Connexion TCP/IP
        $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
        
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
 * Fonction pour les produits - SIMPLIFIÉE
 */
function getProductImage($productId, $imageUrl = null) {
    $baseUrl = IMG_BASE_URL . 'products/';
    
    if (!empty($imageUrl)) {
        // Si c'est une URL absolue, la retourner directement
        if (strpos($imageUrl, 'http') === 0 || strpos($imageUrl, 'data:image') === 0) {
            return $imageUrl;
        }
        // Sinon, on suppose que c'est un nom de fichier dans le dossier products
        return $baseUrl . basename($imageUrl);
    }
    
    // En production, on suppose que l'image existe avec l'ID et l'extension .jpg
    return $baseUrl . $productId . '.jpg';
}

/**
 * Fonction pour les restaurants - SIMPLIFIÉE
 */
function getRestaurantImage($restaurantId, $imageUrl = null) {
    $baseUrl = IMG_BASE_URL . 'restaurants/';
    
    if (!empty($imageUrl)) {
        // Si c'est une URL absolue, la retourner directement
        if (strpos($imageUrl, 'http') === 0 || strpos($imageUrl, 'data:image') === 0) {
            return $imageUrl;
        }
        // Sinon, on suppose que c'est un nom de fichier dans le dossier restaurants
        return $baseUrl . basename($imageUrl);
    }
    
    // En production, on suppose que l'image existe avec l'ID et l'extension .jpg
    return $baseUrl . $restaurantId . '.jpg';
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

// Fonction pour obtenir une connexion à la base de données (compatible avec l'ancien code)
function getDBConnection() {
    return connectDB();
}
