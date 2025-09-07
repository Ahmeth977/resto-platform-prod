<?php
// test_db.php - Test de connexion directe
header('Content-Type: text/plain');

$host = '34.52.242.229'; // IP de votre Cloud SQL
$dbname = 'resto_platform';
$user = 'root';
$pass = 'VOTRE_MOT_DE_PASSE'; // Remplacez par le vrai mot de passe
$port = 3306;

echo "Tentative de connexion à: $host:$port, db: $dbname\n";

try {
    // Connexion TCP/IP forcée
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => false // Important: éviter les sockets
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "✅ CONNEXION RÉUSSIE!\n";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM restaurants");
    $result = $stmt->fetch();
    echo "Nombre de restaurants: " . $result['count'] . "\n";
    
} catch (PDOException $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Code d'erreur: " . $e->getCode() . "\n";
}
?>
