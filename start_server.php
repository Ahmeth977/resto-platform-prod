<?php
// start_server.php
$host = 'localhost';
$port = 8000;
$router = 'router.php';

echo "==========================================\n";
echo "Démarrage du serveur de développement PHP\n";
echo "==========================================\n";
echo "URL: http://{$host}:{$port}\n";
echo "Répertoire: " . __DIR__ . "\n";
echo "Routeur: {$router}\n";
echo "Appuyez sur Ctrl+C pour arrêter le serveur\n";
echo "==========================================\n\n";

// Démarrer le serveur
passthru("php -S {$host}:{$port} {$router}");
?>