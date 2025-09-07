<?php
// test_db.php - Version corrigée
header('Content-Type: text/plain');

// UTILISEZ LE SOCKET CLOUD SQL au lieu de l'IP
$socket = '/cloudsql/sencommandes:europe-west1:resto-platform-db';
$dbname = 'resto_platform';
$user = 'root';
$pass = 'VOTRE_MOT_DE_PASSE';

echo "Tentative de connexion via socket Cloud SQL\n";

try {
    // Connexion via socket Unix (méthode Cloud SQL)
    $dsn = "mysql:unix_socket=$socket;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "✅ CONNEXION RÉUSSIE via socket Cloud SQL!\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM restaurants");
    $result = $stmt->fetch();
    echo "Nombre de restaurants: " . $result['count'] . "\n";
    
} catch (PDOException $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}
?>
