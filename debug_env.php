<?php
// debug_env.php
header('Content-Type: text/plain; charset=utf-8');

echo "=== ENVIRONNEMENT CLOUD RUN ===\n";

// Vérifier le dossier /cloudsql
echo "Dossier /cloudsql existe: " . (is_dir('/cloudsql') ? 'OUI' : 'NON') . "\n";

if (is_dir('/cloudsql')) {
    echo "Contenu de /cloudsql:\n";
    $files = scandir('/cloudsql');
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $fullPath = "/cloudsql/$file";
            echo "- $file (";
            echo "type: " . filetype($fullPath);
            echo ", readable: " . (is_readable($fullPath) ? 'yes' : 'no');
            echo ")\n";
        }
    }
}

// Variables d'environnement
echo "\nVariables d'environnement:\n";
echo "DB_SOCKET: " . (getenv('DB_SOCKET') ?: 'Non définie') . "\n";
echo "DB_NAME: " . (getenv('DB_NAME') ?: 'Non définie') . "\n";

// Test de connexion alternative
echo "\n=== TEST CONNEXION ALTERNATIVE ===\n";
$host = '34.52.242.229';
$dbname = 'resto_platform';
$user = 'root';
$pass = ''; // Remplacez!

try {
    $dsn = "mysql:host=$host;port=3306;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    echo "✅ Connexion TCP/IP réussie!\n";
} catch (PDOException $e) {
    echo "❌ Erreur TCP/IP: " . $e->getMessage() . "\n";
}
?>
