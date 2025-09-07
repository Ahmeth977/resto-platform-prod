<?php
// test_db.php - Version corrigée
header('Content-Type: text/plain');

// CHEMIN EXACT du socket Cloud SQL
$socket = '/cloudsql/sencommandes:europe-west1:resto-platform-db';
$dbname = 'resto_platform';
$user = 'root';
$pass = ''; // Remplacez par le vrai mot de passe

echo "Tentative de connexion via: $socket\n";

try {
    // Connexion via socket Unix
    $dsn = "mysql:unix_socket=$socket;dbname=$dbname;charset=utf8mb4";
    
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => false
    ]);
    
    echo "✅ CONNEXION RÉUSSIE!\n";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM restaurants");
    $result = $stmt->fetch();
    echo "Nombre de restaurants: " . $result['count'] . "\n";
    
} catch (PDOException $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    
    // Debug info
    echo "Socket path: $socket\n";
    echo "DB name: $dbname\n";
}
?>
