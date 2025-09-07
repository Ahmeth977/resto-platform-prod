<?php
require_once 'config/database.php';

try {
    // Connexion sans spécifier de base de données
    $temp_conn = new PDO(
        "mysql:host=" . getenv('DB_HOST') ?: 'localhost',
        getenv('DB_USER') ?: 'root',
        getenv('DB_PASS') ?: ''
    );
    
    // Créer la base de données si elle n'existe pas
    $sql = "CREATE DATABASE IF NOT EXISTS resto_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $temp_conn->exec($sql);
    echo "Database 'resto_platform' created or already exists.\n";
    
} catch (PDOException $e) {
    echo "Error creating database: " . $e->getMessage() . "\n";
}
?>