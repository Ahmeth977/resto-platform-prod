<?php
require_once __DIR__.'/../includes/config.php';

try {
    // Test de connexion
    $db->query("SELECT 1");
    
    // Test d'insertion
    $testData = [
        'name' => 'Test Restaurant',
        'address' => '123 Test Street'
    ];
    
    $stmt = $db->prepare("INSERT INTO restaurants (name, address, user_id) VALUES (?, ?, 1)");
    $stmt->execute([$testData['name'], $testData['address']]);
    
    echo "✅ Insertion test réussie. Dernier ID: ".$db->lastInsertId();
    
} catch (PDOException $e) {
    die("❌ Erreur: ".$e->getMessage());
}
?>