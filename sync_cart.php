<?php
session_start();
require_once __DIR__.'/includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['cart']) && isset($input['restaurant_id'])) {
        // Formater et valider les données du panier
        $formattedCart = [];
        
        foreach ($input['cart'] as $item) {
            $formattedItem = [
                'id' => $item['id'],
                'productId' => $item['productId'],
                'name' => $item['name'],
                'basePrice' => floatval($item['basePrice']),
                'quantity' => intval($item['quantity']),
                'image' => $item['image'],
                'options' => []
            ];
            
            // Traiter les options
            if (isset($item['options']) && is_array($item['options'])) {
                foreach ($item['options'] as $option) {
                    $formattedItem['options'][] = [
                        'id' => $option['id'],
                        'name' => $option['name'],
                        'price' => floatval($option['price'])
                    ];
                }
            }
            
            // Calculer le prix total
            $optionsTotal = 0;
            foreach ($formattedItem['options'] as $option) {
                $optionsTotal += $option['price'] * $formattedItem['quantity'];
            }
            
            $formattedItem['totalPrice'] = ($formattedItem['basePrice'] * $formattedItem['quantity']) + $optionsTotal;
            
            $formattedCart[] = $formattedItem;
        }
        
        // Sauvegarder dans la session
        $_SESSION['cart'] = $formattedCart;
        $_SESSION['restaurant_id'] = intval($input['restaurant_id']);
        
        echo json_encode(['success' => true, 'message' => 'Panier synchronisé']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Données manquantes']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
}
?>