<?php
session_start();
require_once __DIR__.'/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['cart']) && isset($input['restaurant_id'])) {
        $_SESSION['cart'] = $input['cart'];
        $_SESSION['restaurant_id'] = (int)$input['restaurant_id'];
        
        echo json_encode(['success' => true]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Données invalides']);