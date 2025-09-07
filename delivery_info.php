<?php
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/functions.php';
if (!isset($_SESSION['cart']) && isset($_SESSION['pending_cart'])) {
    $_SESSION['cart'] = json_decode($_SESSION['pending_cart'], true);
    $_SESSION['restaurant_id'] = $_SESSION['pending_restaurant_id'];
    unset($_SESSION['pending_cart'], $_SESSION['pending_restaurant_id']);
}
// Vérifier si le panier existe et n'est pas vide
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Récupérer les informations du restaurant
$restaurantId = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : 0;
if ($restaurantId <= 0) {
    header("Location: index.php");
    exit();
}

// Mode invité - pas besoin d'être connecté
$isGuest = !isset($_SESSION['user_id']);

try {
    $db = connectDB();
    
    // Récupérer les infos du restaurant
    $stmt = $db->prepare("SELECT * FROM restaurants WHERE id = ?");
    $stmt->execute([$restaurantId]);
    $restaurant = $stmt->fetch();
    
    if (!$restaurant) {
        header("Location: index.php");
        exit();
    }
    
    // Récupérer les informations utilisateur si connecté
    $user = [];
    if (!$isGuest) {
        $userId = $_SESSION['user_id'];
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
    }
    
    // Récupérer les informations de livraison de la session si elles existent
    $deliveryInfo = [];
    if (isset($_SESSION['delivery_info'])) {
        $deliveryInfo = $_SESSION['delivery_info'];
       
        // Mettre à jour les valeurs de $user avec celles de la session
        if (!$isGuest) {
            $user['first_name'] = $deliveryInfo['first_name'] ?? $user['first_name'] ?? '';
            $user['last_name'] = $deliveryInfo['last_name'] ?? $user['last_name'] ?? '';
            $user['email'] = $deliveryInfo['email'] ?? $user['email'] ?? '';
            $user['phone'] = $deliveryInfo['phone'] ?? $user['phone'] ?? '';
            $user['address'] = $deliveryInfo['address'] ?? $user['address'] ?? '';
            $user['building'] = $deliveryInfo['building'] ?? $user['building'] ?? '';
            $user['apartment'] = $deliveryInfo['apartment'] ?? $user['apartment'] ?? '';
            $user['city'] = $deliveryInfo['city'] ?? $user['city'] ?? '';
            $user['delivery_instructions'] = $deliveryInfo['delivery_instructions'] ?? $user['delivery_instructions'] ?? '';
        }
    }
    
   // Calculer le total du panier
$subtotal = 0;
$deliveryFee = 1000; // Frais de livraison fixes
$cartItems = [];

// Vérifier si le panier existe dans la session
if (isset($_SESSION['cart'])) {
    $cartData = $_SESSION['cart'];
    
    foreach ($cartData as $item) {
        $itemPrice = isset($item['totalPrice']) ? $item['totalPrice'] : 
                    (isset($item['basePrice']) ? $item['basePrice'] : 
                    (isset($item['price']) ? $item['price'] : 0));
        
        // Si totalPrice n'existe pas, calculer en fonction de la quantité
        if (!isset($item['totalPrice'])) {
            $basePrice = isset($item['basePrice']) ? $item['basePrice'] : 
                        (isset($item['price']) ? $item['price'] : 0);
            
            // Ajouter le prix des options
            $optionsPrice = 0;
            if (isset($item['options']) && is_array($item['options'])) {
                foreach ($item['options'] as $option) {
                    $optionsPrice += isset($option['price']) ? $option['price'] : 0;
                }
            }
            
            $itemPrice = ($basePrice + $optionsPrice) * $item['quantity'];
        }
        
        $subtotal += $itemPrice;
        
        $item['displayPrice'] = $itemPrice;
        $cartItems[] = $item;
    }
}

$total = $subtotal + $deliveryFee;
    
} catch (PDOException $e) {
    error_log("Erreur delivery_info: " . $e->getMessage());
    $error = "Une erreur est survenue lors du chargement de la page.";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informations de Livraison | <?= htmlspecialchars($restaurant['name']) ?> | RestoPlatform</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color:rgb(70, 103, 232);
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --dark-color: #292F36;
            --light-color: #F7FFF7;
            --gold-color: #D4AF37;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            padding-top: 80px;
            color: #333;
        }
        
        .delivery-container {
            max-width: 1000px;
            margin: 2rem auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        
        .delivery-header {
            background: var(--primary-color);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .delivery-body {
            padding: 2rem;
        }
        
        .form-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border-radius: 10px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
        }
        
        .section-title i {
            font-size: 1.5rem;
            margin-right: 0.75rem;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(164, 172, 205, 0.25);
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.8rem 2rem;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background:rgb(97, 110, 158);
        }
        
        .order-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            border: 1px solid #e9ecef;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .summary-total {
            font-weight: bold;
            font-size: 1.2rem;
            border-top: 1px solid #dee2e6;
            padding-top: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .required-field::after {
            content: "*";
            color: #dc3545;
            margin-left: 3px;
        }
        
        .progress-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .step-text {
            font-size: 0.9rem;
            text-align: center;
            color: #6c757d;
        }
        
        .progress-step.active .step-number {
            background: var(--primary-color);
            color: white;
        }
        
        .progress-step.active .step-text {
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .progress-step.completed .step-number {
            background: var(--success-color);
            color: white;
        }
        
        .progress-bar::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e9ecef;
            z-index: 1;
        }
        
        /* Styles pour le footer */
        .text-gold {
            color: var(--gold-color) !important;
        }
        
        .btn-gold {
            background-color: var(--gold-color);
            border-color: var(--gold-color);
            color: white;
        }
        
        .btn-gold:hover {
            background-color: #B8860B;
            border-color: #B8860B;
            color: white;
        }
        
        .bg-dark {
            background-color: #1a1a1a !important;
        }
        
        .social-icons a {
            transition: transform 0.3s;
        }
        
        .social-icons a:hover {
            transform: translateY(-3px);
            color: var(--gold-color) !important;
        }
        
        .list-unstyled a {
            transition: color 0.3s;
            text-decoration: none;
        }
        
        .list-unstyled a:hover {
            color: var(--gold-color) !important;
        }
        
        @media (max-width: 768px) {
            .delivery-container {
                margin: 1rem;
                border-radius: 10px;
            }
            
            .delivery-body {
                padding: 1.5rem;
            }
            
            .form-section {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__.'/includes/nav.php'; ?>
    
    <div class="delivery-container">
        <div class="delivery-header">
            <h2 class="mb-0"><i class="fas fa-truck me-2"></i>Informations de Livraison</h2>
        </div>
        
        <div class="delivery-body">
            <!-- Barre de progression -->
            <div class="progress-bar">
                <div class="progress-step completed">
                    <div class="step-number">1</div>
                    <div class="step-text">Panier</div>
                </div>
                <div class="progress-step active">
                    <div class="step-number">2</div>
                    <div class="step-text">Livraison</div>
                </div>
                <div class="progress-step">
                    <div class="step-number">3</div>
                    <div class="step-text">Paiement</div>
                </div>
                <div class="progress-step">
                    <div class="step-number">4</div>
                    <div class="step-text">Confirmation</div>
                </div>
            </div>
            
            <!-- Afficher les messages d'erreur -->
            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-7">
                    <form id="deliveryForm" action="process_delivery.php" method="POST">
                        <input type="hidden" name="restaurant_id" value="<?= $restaurantId ?>">
                        
                        <!-- Section informations de contact -->
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="fas fa-user"></i>
                                Informations de Contact
                            </h4>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="firstName" class="form-label required-field">Prénom</label>
                                    <input type="text" class="form-control" id="firstName" name="first_name" 
                                           value="<?= !empty($user['first_name']) ? htmlspecialchars($user['first_name']) : '' ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="lastName" class="form-label required-field">Nom</label>
                                    <input type="text" class="form-control" id="lastName" name="last_name"
                                           value="<?= !empty($user['last_name']) ? htmlspecialchars($user['last_name']) : '' ?>" required>
                                </div>
                            </div>
<div class="mb-3">
    <label for="email" class="form-label">Email</label> <!-- Retirer required-field -->
    <input type="email" class="form-control" id="email" name="email" 
           value="<?= !empty($user['email']) ? htmlspecialchars($user['email']) : '' ?>"
           placeholder="votre@email.com (optionnel)">
    <div class="form-text">Pour l'envoi du reçu et des notifications (optionnel)</div>
</div>
                            <div class="mb-3">
                                <label for="phone" class="form-label required-field">Téléphone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required 
                                       value="<?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : '' ?>"
                                       placeholder="77 123 45 67">
                                <div class="form-text">Pour que le livreur puisse vous contacter</div>
                            </div>
                        </div>
                        
                        <!-- Section adresse de livraison -->
                        <div class="form-section">
                            <h4 class="section-title">
                                <i class="fas fa-map-marker-alt"></i>
                                Adresse de Livraison
                            </h4>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label required-field">Adresse</label>
                                <input type="text" class="form-control" id="address" name="address" required 
                                       placeholder="Rue, avenue, boulevard" value="<?= !empty($user['address']) ? htmlspecialchars($user['address']) : '' ?>">
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="building" class="form-label">Bâtiment</label>
                                    <input type="text" class="form-control" id="building" name="building" 
                                           placeholder="Numéro, nom" value="<?= !empty($user['building']) ? htmlspecialchars($user['building']) : '' ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="apartment" class="form-label">Appartement</label>
                                    <input type="text" class="form-control" id="apartment" name="apartment" 
                                           placeholder="Numéro, étage" value="<?= !empty($user['apartment']) ? htmlspecialchars($user['apartment']) : '' ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="city" class="form-label required-field">Ville</label>
                                    <input type="text" class="form-control" id="city" name="city" required 
                                           placeholder="Dakar" value="<?= !empty($user['city']) ? htmlspecialchars($user['city']) : 'Dakar' ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="deliveryInstructions" class="form-label">Instructions de livraison</label>
                                <textarea class="form-control" id="deliveryInstructions" name="delivery_instructions" rows="3" 
                                          placeholder="Repères, code d'accès, informations complémentaires..."><?= !empty($user['delivery_instructions']) ? htmlspecialchars($user['delivery_instructions']) : '' ?></textarea>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="saveAddress" name="save_address" checked>
                                <label class="form-check-label" for="saveAddress">
                                    Enregistrer cette adresse pour mes futures commandes
                                </label>
                            </div>
                        </div>
                        // Ajouter cette section après la section "Adresse de Livraison"
<div class="form-section">
    <h4 class="section-title">
        <i class="fas fa-money-bill-wave"></i>
        Méthode de Paiement
    </h4>
    
    <div class="mb-3">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="payment_method" id="payment_cash" value="cash" checked>
            <label class="form-check-label" for="payment_cash">
                Paiement en espèces à la livraison
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="payment_method" id="payment_online" value="online">
            <label class="form-check-label" for="payment_online">
                Paiement en ligne (carte bancaire)
            </label>
        </div>
    </div>
</div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-credit-card me-2"></i>Procéder au Paiement
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="col-md-5">
                    <div class="order-summary">
                        <h4 class="mb-4">Récapitulatif de la commande</h4>
                        
                        <div class="mb-3">
                            <h6>Restaurant: <?= isset($restaurant['name']) ? htmlspecialchars($restaurant['name']) : 'Restaurant' ?></h6>
                            <p class="text-muted mb-0"><?= htmlspecialchars($restaurant['address']) ?></p>
                        </div>
                        
                        <hr>
                        
                        <?php foreach ($cartItems as $item): ?>
<div class="summary-item">
    <div class="d-flex align-items-center">
        <!-- Image du produit -->
        <img src="<?= isset($item['image']) ? $item['image'] : '/assets/img/default-product.png' ?>" 
             alt="<?= isset($item['name']) ? htmlspecialchars($item['name']) : 'Produit' ?>"
             width="40" height="40" style="object-fit: cover; border-radius: 4px; margin-right: 10px;">
        
        <!-- Nom et quantité du produit -->
        <div>
            <div><?= isset($item['name']) ? htmlspecialchars($item['name']) : 'Produit' ?> x<?= $item['quantity'] ?></div>
            <?php if (!empty($item['options'])): ?>
            <small class="text-muted">
                <?= implode(', ', array_map(function($opt) { 
                    return htmlspecialchars($opt['name']); 
                }, $item['options'])) ?>
            </small>
            <?php endif; ?>
        </div>
    </div>
    <span><?= number_format($item['displayPrice'] * $item['quantity'], 2) ?> CFA</span>
</div>
<?php endforeach; ?>
                        
                        <div class="summary-item">
                            <span>Sous-total</span>
                            <span><?= number_format($subtotal, 2) ?> CFA</span>
                        </div>
                        <div class="summary-item">
                            <span>Frais de livraison</span>
                            <span><?= number_format($deliveryFee, 2) ?> CFA</span>
                        </div>
                        
                        <div class="summary-item summary-total">
                            <span>Total</span>
                            <span><?= number_format($total, 2) ?> CFA</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6><i class="fas fa-info-circle me-2"></i>Livraison</h6>
                        <p class="small text-muted mb-0">
                            Temps de livraison estimé: 30-45 min. 
                            Livraison gratuite à partir de 10 000 CFA d'achat.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__.'/includes/footer.php'; ?>
    <!-- Footer -->
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deliveryForm = document.getElementById('deliveryForm');
            
            deliveryForm.addEventListener('submit', function(event) {
                event.preventDefault();
                
                // Validation simple
                let isValid = true;
                const requiredFields = deliveryForm.querySelectorAll('[required]');
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });
                
              
                
                if (isValid) {
                    // Soumettre le formulaire
                    this.submit();
                }
            });
        });
    </script>
</body>
</html>