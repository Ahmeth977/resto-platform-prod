<?php
// test_routing.php - Script de diagnostic
echo "<h1>Test de Routage</h1>";
echo "<p>URL demandée: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Script exécuté: " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p>Variables SERVER:</p>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>";

// Test des redirections
echo "<h2>Test des liens:</h2>";
echo '<a href="test_routing.php?param=1">Lien 1</a><br>';
echo '<a href="restaurant.php?id=1">Restaurant</a><br>';
echo '<a href="assets/css/style.css">Fichier CSS</a><br>';
?>
