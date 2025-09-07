<?php
// order_confirmation.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/functions.php';

// Vérifier si les données de confirmation existent
if (!isset($_SESSION['confirmation_data'])) {
    $_SESSION['error'] = "Aucune donnée de confirmation trouvée.";
    header("Location: index.php");
    exit();
}

$confirmation_data = $_SESSION['confirmation_data'];
$order_id = $confirmation_data['order_id'];
$order = $confirmation_data['order'];
$payment_method = $confirmation_data['payment_method'];

// Nettoyer les données de confirmation après affichage
unset($_SESSION['confirmation_data']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de commande | RestoPlatform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            padding-top: 80px;
        }
        
        .confirmation-container {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            overflow: hidden;
            text-align: center;
        }
        
        .confirmation-header {
            background: #28a745;
            color: white;
            padding: 2rem;
        }
        
        .confirmation-body {
            padding: 2rem;
        }
        
        .confirmation-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
        
        .order-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            text-align: left;
        }
        
        .btn-primary {
            background: #4a6cf7;
            border: none;
            padding: 0.8rem 2rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include __DIR__.'/includes/nav.php'; ?>
    
    <div class="confirmation-container">
        <div class="confirmation-header">
            <h2 class="mb-0"><i class="fas fa-check-circle me-2"></i>Commande Confirmée</h2>
        </div>
        
        <div class="confirmation-body">
            <div class="confirmation-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h3>Merci pour votre commande !</h3>
            <p class="lead">Votre commande #<?= $order_id ?> a été confirmée avec succès.</p>
            
            <div class="order-details">
                <h5>Détails de la commande:</h5>
                <p><strong>Numéro de commande:</strong> #<?= $order_id ?></p>
                <p><strong>Restaurant:</strong> <?= htmlspecialchars($order['restaurant_name']) ?></p>
                <p><strong>Total:</strong> <?= number_format($order['total_price'], 2) ?> CFA</p>
                <p><strong>Méthode de paiement:</strong> 
                    <?php 
                    switch($payment_method) {
                        case 'cash': echo 'Paiement à la livraison'; break;
                        case 'orange_money': echo 'Orange Money'; break;
                        case 'wave': echo 'Wave'; break;
                        default: echo ucfirst($payment_method);
                    }
                    ?>
                </p>
                <p><strong>Statut:</strong> Confirmée</p>
            </div>
            
            <p>Vous recevrez un email de confirmation avec les détails de votre commande.</p>
            <p>Le restaurant prépare déjà votre commande et vous serez livré dans les plus brefs délais.</p>
            
            <div class="mt-4">
                <a href="index.php" class="btn btn-primary me-2">
                    <i class="fas fa-home me-2"></i>Retour à l'accueil
                </a>
                <a href="order_tracking.php?order_id=<?= $order_id ?>" class="btn btn-outline-primary">
                    <i class="fas fa-map-marker-alt me-2"></i>Suivre ma commande
                </a>
            </div>
        </div>
    </div>
    
    <?php include __DIR__.'/includes/footer.php'; ?>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>