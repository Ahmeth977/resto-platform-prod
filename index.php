<?php
echo "<h1>��� Resto Platform - En construction</h1>";
echo "<p>Application de commande de livraison</p>";

// Test de connexion base de données
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    echo "<p style='color: green;'>✅ Connexion MySQL prête</p>";
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠️ Base de données à configurer</p>";
}
?>
