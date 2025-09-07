<?php
require_once __DIR__ . '/../includes/functions.php';
// config.php
// 1. Constantes des rôles utilisateurs
define('ROLE_ADMIN', 'admin');
define('ROLE_RESTAURATEUR', 'restaurateur');
define('ROLE_CLIENT', 'client');

// 2. Configuration BDD
define('DB_HOST', 'localhost'); 
define('DB_NAME', 'resto_platform');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// 3. Chemins système
define('ROOT_PATH', dirname(__DIR__));
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/resto_plateform/');
define('ASSETS_URL', BASE_URL . 'assets/');

// 4. Configuration des chemins d'images (DÉPLACÉ ICI)
define('IMG_BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/resto_plateform/assets/img/');
define('IMG_BASE_URL', '/resto_plateform/assets/img/'); 
// 5. Fuseau horaire du Sénégal
date_default_timezone_set('Africa/Dakar');

// 6. Mode Développement (true = dev, false = production)
define('DEV_MODE', true);

// 7. Démarrer la session simplement
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* *************************** */
/* FONCTIONS PRINCIPALES       */
/* *************************** */

/**
 * Connexion simple à la base de données
 */
function connectDB() {
    try {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        
        return new PDO($dsn, DB_USER, DB_PASS, $options);
        
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données: " . $e->getMessage());
    }
}

/**
 * Nettoie les données utilisateur (version simplifiée)
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirection simple
 */
function redirect($url) {
    header("Location: " . BASE_URL . ltrim($url, '/'));
    exit;
}

/* *************************** */
/* INITIALISATION DU SYSTÈME   */
/* *************************** */

// Affichage des erreurs en mode dev
if (DEV_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Connexion auto pour le développement
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = ROLE_ADMIN;
}

// 3. Test de connexion DB
try {
    $db = connectDB();
    $db->query("SELECT 1");
} catch (PDOException $e) {
    die("<h1>Configuration requise</h1>
        <div class='alert alert-danger'>
        <p>Impossible de se connecter à la base de données.</p>
        <p><strong>Solution :</strong></p>
        <ol>
            <li>Démarrer le serveur MySQL</li>
            <li>Créer la base '".DB_NAME."'</li>
            <li>Importer le schéma SQL si nécessaire</li>
        </ol>
        <pre>Erreur : {$e->getMessage()}</pre>
        </div>");
}

?>