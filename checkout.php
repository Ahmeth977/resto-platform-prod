<?php
// checkout.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/functions.php';
// Vérifier si on vient de process_delivery
if (isset($_SESSION['checkout_ready']) && $_SESSION['checkout_ready']) {
    $restaurantId = $_SESSION['checkout_restaurant_id'];
    $order_id = $_SESSION['checkout_order_id'];
    
    // Nettoyer la session
    unset($_SESSION['checkout_ready'], $_SESSION['checkout_restaurant_id'], $_SESSION['checkout_order_id']);
}
// Vérifier si les informations de commande existent
if (!isset($_SESSION['order_id']) || !isset($_SESSION['delivery_info'])) {
    $_SESSION['error'] = "Aucune commande trouvée. Veuillez compléter vos informations de livraison.";
    header("Location: cart.php");
    exit();
}

$order_id = $_SESSION['order_id'];
$delivery_info = $_SESSION['delivery_info'];

// Récupérer les détails de la commande depuis la base de données
try {
    $db = connectDB();
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
    
    // Récupérer les articles de la commande
    $stmt_items = $db->prepare("SELECT oi.*, mi.name as item_name, mi.image as item_image 
                               FROM order_items oi 
                               LEFT JOIN menu_items mi ON oi.menu_id = mi.id 
                               WHERE oi.order_id = ?");
    $stmt_items->execute([$order_id]);
    $order_items = $stmt_items->fetchAll();
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données: " . $e->getMessage();
    header("Location: cart.php");
    exit();
}

// Page HTML de checkout
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement | RestoPlatform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            padding-top: 80px;
        }
        
        .checkout-container {
            max-width: 1200px;
            margin: 2rem auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        
        .checkout-header {
            background: #4a6cf7;
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .checkout-body {
            padding: 2rem;
        }
        
        .order-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            border: 1px solid #e9ecef;
        }
        
        .payment-methods {
            margin-top: 2rem;
        }
        
        .payment-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-card:hover, .payment-card.selected {
            border-color: #4a6cf7;
            background-color: rgba(74, 108, 247, 0.05);
        }
        
        .payment-card.selected {
            border-width: 2px;
        }
        
        .btn-primary {
            background: #4a6cf7;
            border: none;
            padding: 0.8rem 2rem;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: #3a5cd8;
        }
        
        .item-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include __DIR__.'/includes/nav.php'; ?>
    
    <div class="checkout-container">
        <div class="checkout-header">
            <h2 class="mb-0"><i class="fas fa-credit-card me-2"></i>Paiement de la commande</h2>
        </div>
        
        <div class="checkout-body">
            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-7">
                    <h3 class="mb-4">Méthode de paiement</h3>
                    
                    <div class="payment-methods">
                        <div class="payment-card" data-method="cash">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="cashPayment" value="cash" checked>
                                <label class="form-check-label fw-bold" for="cashPayment">
                                    Paiement à la livraison
                                </label>
                            </div>
                            <p class="text-muted mt-2 mb-0">Payez en espèces lorsque votre commande vous est livrée.</p>
                        </div>
                        
                        <div class="payment-card" data-method="orange_money">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="omPayment" value="orange_money">
                                <label class="form-check-label fw-bold" for="omPayment">
                                    Orange Money
                                </label>
                            </div>
                            <p class="text-muted mt-2 mb-0">Paiement sécurisé via Orange Money.</p>
                        </div>
                        
                        <div class="payment-card" data-method="wave">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="wavePayment" value="wave">
                                <label class="form-check-label fw-bold" for="wavePayment">
                                    Wave
                                </label>
                            </div>
                            <p class="text-muted mt-2 mb-0">Paiement sécurisé via Wave.</p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <form id="paymentForm" action="process_payment.php" method="POST">
                            <input type="hidden" name="order_id" value="<?= $order_id ?>">
                            <input type="hidden" name="payment_method" value="cash">
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-check-circle me-2"></i>Confirmer le paiement
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="col-md-5">
                    <div class="order-summary">
                        <h4 class="mb-4">Récapitulatif de la commande #<?= $order_id ?></h4>
                        
                        <div class="mb-3">
                            <h6>Restaurant: <?= htmlspecialchars($order['restaurant_name']) ?></h6>
                        </div>
                        
                        <hr>
                        
                        <h6>Articles commandés:</h6>
                        <?php foreach ($order_items as $item): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <?php if (!empty($item['item_image'])): ?>
                                <img src="<?= htmlspecialchars($item['item_image']) ?>" alt="<?= htmlspecialchars($item['item_name']) ?>" class="item-image me-2">
                                <?php else: ?>
                                <div class="item-image bg-light d-flex align-items-center justify-content-center me-2">
                                    <i class="fas fa-utensils text-muted"></i>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-medium"><?= htmlspecialchars($item['item_name'] ?? 'Produit #' . $item['menu_id']) ?></div>
                                    <small class="text-muted">x<?= $item['quantity'] ?></small>
                                </div>
                            </div>
                            <span><?= number_format($item['unit_price'] * $item['quantity'], 2) ?> CFA</span>
                        </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-1">
                            <span>Sous-total</span>
                            <span><?= number_format($order['total_price'] - 1000, 2) ?> CFA</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Frais de livraison</span>
                            <span>1 000 CFA</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold fs-5 mt-3 pt-2 border-top">
                            <span>Total</span>
                            <span><?= number_format($order['total_price'], 2) ?> CFA</span>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5>Informations de livraison</h5>
                        <div class="card">
                            <div class="card-body">
                                <p class="mb-1"><strong><?= htmlspecialchars($delivery_info['first_name'] . ' ' . $delivery_info['last_name']) ?></strong></p>
                                <p class="mb-1"><?= htmlspecialchars($delivery_info['address']) ?></p>
                                <?php if (!empty($delivery_info['building'])): ?>
                                <p class="mb-1">Bâtiment: <?= htmlspecialchars($delivery_info['building']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($delivery_info['apartment'])): ?>
                                <p class="mb-1">Appartement: <?= htmlspecialchars($delivery_info['apartment']) ?></p>
                                <?php endif; ?>
                                <p class="mb-1"><?= htmlspecialchars($delivery_info['city']) ?></p>
                                <p class="mb-0">Téléphone: <?= htmlspecialchars($delivery_info['phone']) ?></p>
                                <?php if (!empty($delivery_info['delivery_instructions'])): ?>
                                <p class="mt-2 mb-0"><small>Instructions: <?= htmlspecialchars($delivery_info['delivery_instructions']) ?></small></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__.'/includes/footer.php'; ?>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion de la sélection des méthodes de paiement
            const paymentCards = document.querySelectorAll('.payment-card');
            const paymentMethodInput = document.querySelector('input[name="payment_method"]');
            
            paymentCards.forEach(card => {
                card.addEventListener('click', function() {
                    const method = this.getAttribute('data-method');
                    const radioInput = this.querySelector('input[type="radio"]');
                    
                    // Désélectionner toutes les cartes
                    paymentCards.forEach(c => c.classList.remove('selected'));
                    
                    // Sélectionner la carte cliquée
                    this.classList.add('selected');
                    radioInput.checked = true;
                    paymentMethodInput.value = method;
                });
            });
            
            // Sélectionner la première méthode par défaut
            document.querySelector('.payment-card').classList.add('selected');
            
            // Gestion de la soumission du formulaire
            const paymentForm = document.getElementById('paymentForm');
            
            paymentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Afficher un indicateur de chargement
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Traitement...';
                submitBtn.disabled = true;
                
                // Soumettre le formulaire
                this.submit();
            });
        });
    </script>
</body>
</html>