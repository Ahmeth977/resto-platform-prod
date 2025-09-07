<?php
// test/test_paths.php
echo "<h1>Test des chemins - Étape 1</h1>";

// 1. Vérification du serveur
echo "<h2>Serveur</h2>";
echo "PHP ".phpversion()." sur ".$_SERVER['SERVER_SOFTWARE'];

// 2. Vérification du dossier racine
echo "<h2>Racine du projet</h2>";
$root = dirname(__DIR__);
echo "Chemat détecté : ".$root;

// 3. Test d'inclusion
echo "<h2>Test d'inclusion</h2>";
if (file_exists($root.'/includes/config.php')) {
    require_once $root.'/includes/config.php';
    echo "<p>✅ Fichier config.php chargé</p>";
    echo "<p>BASE_URL = ".BASE_URL."</p>";
} else {
    die("<p>❌ Fichier config.php introuvable</p>");
}