<?php
// test_routing.php - Script de diagnostic
echo "<h1>Test de Routage Google App Engine</h1>";
echo "<p>Script actuel: " . $_SERVER['PHP_SELF'] . "</p>";
echo "<p>URL demandée: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Fichier actuel: " . __FILE__ . "</p>";

// Testons l'accès à différents fichiers
$files = ['index.php', 'restaurant.php', 'assets/css/style.css'];
foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "<p style='color:green'>✓ $file existe</p>";
    } else {
        echo "<p style='color:red'>✗ $file n'existe pas</p>";
    }
}
?>
