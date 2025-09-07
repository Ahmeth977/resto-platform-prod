<?php
// process_payment.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/functions.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode de requête non autorisée.";
    header("Location: cart.php");
    exit();
}

// Vérifier si les informations de commande existent
if (!isset($_SESSION['order_id']) || !isset($_SESSION['delivery_info'])) {
    $_SESSION['error'] = "Aucune commande trouvée. Veuillez compléter vos informations de livraison.";
    header("Location: cart.php");
    exit();
}

$order_id = $_POST['order_id'];
$payment_method = $_POST['payment_method'] ?? 'cash';

// Valider les données
if (empty($order_id)) {
    $_SESSION['error'] = "Identifiant de commande manquant.";
    header("Location: checkout.php");
    exit();
}

// Mettre à jour le statut de la commande dans la base de données
try {
    $db = connectDB();
    
    // Mettre à jour la commande
    $stmt = $db->prepare("UPDATE orders SET status = 'confirmed', payment_method = ? WHERE id = ?");
    $stmt->execute([$payment_method, $order_id]);
    
    // Récupérer les détails de la commande
    $stmt = $db->prepare("SELECT o.*, r.name as restaurant_name FROM orders o 
                         JOIN restaurants r ON o.restaurant_id = r.id 
                         WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        $_SESSION['error'] = "Commande introuvable.";
        header("Location: cart.php");
        exit();
    }
    
    // Préparer les données pour la page de confirmation
    $_SESSION['confirmation_data'] = [
        'order_id' => $order_id,
        'order' => $order,
        'payment_method' => $payment_method
    ];
    
    // Rediriger vers la page de confirmation
    header("Location: order_confirmation.php");
    exit();
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors du traitement du paiement: " . $e->getMessage();
    header("Location: checkout.php");
    exit();
}