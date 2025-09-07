<?php
// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/functions.php';

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Journaliser pour le débogage
error_log("=== PROCESS_DELIVERY STARTED ===");
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Session cart: " . (isset($_SESSION['cart']) ? print_r($_SESSION['cart'], true) : 'not exists'));

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Méthode non autorisée. Redirection vers index.php");
    $_SESSION['error'] = "Méthode de requête non autorisée.";
    header("Location: index.php");
    exit();
}

// Vérifier si le panier existe
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    error_log("Panier vide. Redirection vers cart.php");
    $_SESSION['error'] = "Votre panier est vide.";
    header("Location: cart.php");
    exit();
}

// Récupérer et valider les données du formulaire
$restaurantId = isset($_POST['restaurant_id']) ? (int)$_POST['restaurant_id'] : 0;
$firstName = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$lastName = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$email = isset($_POST['email']) && !empty($_POST['email']) ? 
         filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL) : null;
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$building = isset($_POST['building']) ? trim($_POST['building']) : '';
$apartment = isset($_POST['apartment']) ? trim($_POST['apartment']) : '';
$city = isset($_POST['city']) ? trim($_POST['city']) : '';
$deliveryInstructions = isset($_POST['delivery_instructions']) ? trim($_POST['delivery_instructions']) : '';
$saveAddress = isset($_POST['save_address']) ? true : false;
$paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'cash';
$isGuest = !isset($_SESSION['user_id']);

// Journaliser les données reçues
error_log("Restaurant ID: " . $restaurantId);
error_log("First Name: " . $firstName);
error_log("Last Name: : " . $lastName);
error_log("Email: " . ($email ? $email : 'none'));
error_log("Phone: " . $phone);
error_log("Address: " . $address);
error_log("City: " . $city);
error_log("Payment Method: " . $paymentMethod);
error_log("Is Guest: " . ($isGuest ? 'yes' : 'no'));

// Validation des données obligatoires
if (empty($firstName) || empty($lastName) || empty($phone) || 
    empty($address) || empty($city) || $restaurantId <= 0) {
    
    $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
    error_log("Champs obligatoires manquants. Redirection vers delivery_info.php");
    header("Location: delivery_info.php?restaurant_id=" . $restaurantId);
    exit();
}

try {
    $db = connectDB();
    error_log("Connexion à la base de données réussie");
    
    // Récupérer les infos du restaurant
    $stmt = $db->prepare("SELECT * FROM restaurants WHERE id = ?");
    $stmt->execute([$restaurantId]);
    $restaurant = $stmt->fetch();
    
    if (!$restaurant) {
        $_SESSION['error'] = "Restaurant introuvable.";
        error_log("Restaurant introuvable avec ID: " . $restaurantId);
        header("Location: delivery_info.php?restaurant_id=" . $restaurantId);
        exit();
    }
    
    error_log("Restaurant trouvé: " . $restaurant['name']);
    
    // CALCULER LE TOTAL DU PANIER
    $subtotal = 0;
    $deliveryFee = 1000; // Frais de livraison fixes
    
    foreach ($_SESSION['cart'] as $item) {
        $itemPrice = isset($item['basePrice']) ? $item['basePrice'] : (isset($item['price']) ? $item['price'] : 0);
        $itemTotal = $itemPrice * $item['quantity'];
        
        // Ajouter le prix des options
        if (!empty($item['options'])) {
            foreach ($item['options'] as $option) {
                $optionPrice = isset($option['price']) ? $option['price'] : 0;
                $itemTotal += $optionPrice * $item['quantity'];
            }
        }
        
        $subtotal += $itemTotal;
    }
    
    $total = $subtotal + $deliveryFee;
    error_log("Total calculé: " . $total . " CFA");
    
    // Récupérer l'ID utilisateur si connecté, sinon NULL pour les invités
    $userId = $isGuest ? null : $_SESSION['user_id'];
    error_log("User ID: " . ($userId ? $userId : 'null (guest)'));
    
    // Vérifier la structure de la table orders
    $columns = $db->query("DESCRIBE orders")->fetchAll(PDO::FETCH_COLUMN);
    
    // Ajouter le champ payment_method à la requête d'insertion
    if (in_array('guest_phone', $columns) && in_array('payment_method', $columns)) {
        error_log("Colonnes étendues trouvées, utilisation de la requête complète");
        $stmt = $db->prepare("
            INSERT INTO orders (
                user_id, restaurant_id, total_price, status, payment_method,
                guest_name, guest_email, guest_phone,
                delivery_address, delivery_building, delivery_apartment, 
                delivery_city, delivery_instructions
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,                    // 1. user_id (NULL pour les invités)
            $restaurantId,              // 2. restaurant_id
            $total,                     // 3. total_price
            'pending',                  // 4. status
            $paymentMethod,             // 5. payment_method
            $isGuest ? $firstName . ' ' . $lastName : null,  // 6. guest_name (pour les invités)
            $isGuest && $email ? $email : null,    // 7. guest_email (pour les invités)
            $isGuest ? $phone : null,   // 8. guest_phone (pour les invités)
            $address,                   // 9. delivery_address
            $building,                  // 10. delivery_building
            $apartment,                 // 11. delivery_apartment
            $city,                      // 12. delivery_city
            $deliveryInstructions       // 13. delivery_instructions
        ]);
    } else {
        // Gérer le cas où la colonne payment_method n'existe pas
        error_log("Colonne payment_method non trouvée, veuillez mettre à jour la base de données");
        $_SESSION['error'] = "Erreur de configuration. Veuillez contacter l'administrateur.";
        header("Location: delivery_info.php?restaurant_id=" . $restaurantId);
        exit();
    }
   
    $order_id = $db->lastInsertId();
    error_log("Commande créée avec ID: " . $order_id);

    // Enregistrer les articles de la commande
    foreach ($_SESSION['cart'] as $item) {
        $stmt_item = $db->prepare("
            INSERT INTO order_items (order_id, menu_id, quantity, unit_price, options)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $options_json = !empty($item['options']) ? json_encode($item['options']) : null;
        $itemPrice = isset($item['basePrice']) ? $item['basePrice'] : (isset($item['price']) ? $item['price'] : 0);
        
        $stmt_item->execute([
            $order_id,
            $item['productId'],
            $item['quantity'],
            $itemPrice,
            $options_json
        ]);
    }
    
    error_log("Articles de commande enregistrés: " . count($_SESSION['cart']) . " items");

    // ======================================================================
    // ENVOYER L'EMAIL DE NOTIFICATION
   // ======================================================================
// ENVOYER L'EMAIL DE NOTIFICATION
// ======================================================================

// Inclure PHPMailer
require_once __DIR__.'/includes/email_functions.php';

$to = "sencommande23@gmail.com";
$subject = "Nouvelle commande #" . $order_id . " - " . $restaurant['name'];

// Construction du message en HTML
$messageHTML = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Nouvelle commande #" . $order_id . "</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background-color: #4a6cf7; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; color: #4a6cf7; border-bottom: 2px solid #4a6cf7; padding-bottom: 5px; margin-bottom: 10px; }
        .item { margin-bottom: 10px; }
        .total { font-weight: bold; font-size: 1.2em; border-top: 2px solid #ccc; padding-top: 10px; }
        .footer { background-color: #f4f4f4; padding: 10px; text-align: center; font-size: 0.9em; color: #666; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>Nouvelle commande #" . $order_id . "</h1>
        <p>" . $restaurant['name'] . "</p>
    </div>
    
    <div class='content'>
        <div class='section'>
            <div class='section-title'>DÉTAILS DE LA COMMANDE</div>
            <p><strong>Numéro de commande:</strong> #" . $order_id . "</p>
            <p><strong>Restaurant:</strong> " . $restaurant['name'] . "</p>
            <p><strong>Date:</strong> " . date('d/m/Y à H:i') . "</p>
            <p><strong>Type de client:</strong> " . ($isGuest ? "Invité" : "Client enregistré") . "</p>
        </div>
        
        <div class='section'>
            <div class='section-title'>INFORMATIONS DU CLIENT</div>
            <p><strong>Nom:</strong> " . $firstName . " " . $lastName . "</p>
            <p><strong>Email:</strong> " . ($email ? $email : "Non fourni") . "</p>
            <p><strong>Téléphone:</strong> " . $phone . "</p>
        </div>
        
        <div class='section'>
            <div class='section-title'>ADRESSE DE LIVRAISON</div>
            <p><strong>Adresse:</strong> " . $address . "</p>
            " . (!empty($building) ? "<p><strong>Bâtiment:</strong> " . $building . "</p>" : "") . "
            " . (!empty($apartment) ? "<p><strong>Appartement:</strong> " . $apartment . "</p>" : "") . "
            <p><strong>Ville:</strong> " . $city . "</p>
            " . (!empty($deliveryInstructions) ? "<p><strong>Instructions:</strong> " . $deliveryInstructions . "</p>" : "") . "
        </div>
        
        <div class='section'>
            <div class='section-title'>MÉTHODE DE PAIEMENT</div>
            <p><strong>Méthode:</strong> " . ($paymentMethod == 'cash' ? "Paiement en espèces à la livraison" : "Paiement en ligne") . "</p>
        </div>
        
        <div class='section'>
            <div class='section-title'>DÉTAILS DE LA COMMANDE</div>";

foreach ($_SESSION['cart'] as $item) {
    $itemName = isset($item['name']) ? $item['name'] : 'Produit';
    $itemQuantity = $item['quantity'];
    $itemPrice = isset($item['basePrice']) ? $item['basePrice'] : (isset($item['price']) ? $item['price'] : 0);
    $itemTotal = $itemPrice * $itemQuantity;
    
    // Ajouter le prix des options
    $optionsTotal = 0;
    $optionsList = "";
    if (!empty($item['options'])) {
        foreach ($item['options'] as $option) {
            $optionName = isset($option['name']) ? $option['name'] : 'Option';
            $optionPrice = isset($option['price']) ? $option['price'] : 0;
            $optionsTotal += $optionPrice * $itemQuantity;
            $optionsList .= "<li>" . $optionName . " (" . number_format($optionPrice, 2) . " CFA)</li>";
        }
        $itemTotal += $optionsTotal;
    }
    
    $messageHTML .= "
            <div class='item'>
                <p><strong>" . $itemName . " x " . $itemQuantity . " - " . number_format($itemTotal, 2) . " CFA</strong></p>";
    
    if (!empty($optionsList)) {
        $messageHTML .= "<ul>" . $optionsList . "</ul>";
    }
    
    $messageHTML .= "</div>";
}

$messageHTML .= "
            <div class='total'>
                <p>SOUS-TOTAL: " . number_format($subtotal, 2) . " CFA</p>
                <p>FRAIS DE LIVRAISON: " . number_format($deliveryFee, 2) . " CFA</p>
                <p>TOTAL: " . number_format($total, 2) . " CFA</p>
            </div>
        </div>
    </div>
    
    <div class='footer'>
        <p>Cet email a été généré automatiquement. Merci de ne pas y répondre.</p>
    </div>
</body>
</html>";

// Envoyer l'email avec PHPMailer
$mailSent = sendOrderEmail($to, $subject, $messageHTML, $restaurant['name']);

if ($mailSent) {
    error_log("Email envoyé avec succès pour la commande #" . $order_id);
    
    // Envoyer également une copie au client si une adresse email a été fournie
    if ($email) {
        $customerSubject = "Confirmation de votre commande #" . $order_id;
        sendOrderEmail($email, $customerSubject, $messageHTML, $restaurant['name']);
    }
} else {
    error_log("Échec de l'envoi d'email pour la commande #" . $order_id);
}
    // Enregistrer les informations de livraison dans la session pour le paiement
    $_SESSION['delivery_info'] = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'building' => $building,
        'apartment' => $apartment,
        'city' => $city,
        'delivery_instructions' => $deliveryInstructions,
        'restaurant_id' => $restaurantId,
        'is_guest' => $isGuest,
        'order_id' => $order_id,
        'total_price' => $total,
        'payment_method' => $paymentMethod
    ];
    
    // Stocker l'ID de commande pour le processus de paiement
    $_SESSION['order_id'] = $order_id;
    
    // Si l'utilisateur est connecté et veut enregistrer l'adresse
    if (!$isGuest && $saveAddress) {
        error_log("Sauvegarde de l'adresse pour l'utilisateur: " . $userId);
        
        $userId = $_SESSION['user_id'];
        
        // Mettre à jour les informations de base de l'utilisateur
        $updateFields = [];
        $updateValues = [];
        
        $updateFields[] = "first_name = ?";
        $updateValues[] = $firstName;
        
        $updateFields[] = "last_name = ?";
        $updateValues[] = $lastName;
        
        if ($email) {
            $updateFields[] = "email = ?";
            $updateValues[] = $email;
        }
        
        $updateFields[] = "phone = ?";
        $updateValues[] = $phone;
        
        $updateValues[] = $userId;
        
        $stmt = $db->prepare("
            UPDATE users 
            SET " . implode(", ", $updateFields) . "
            WHERE id = ?
        ");
        $stmt->execute($updateValues);
        
        // Vérifier si la table user_addresses existe, sinon la créer
        $tableExists = $db->query("SHOW TABLES LIKE 'user_addresses'")->rowCount() > 0;
        
        if (!$tableExists) {
            // Créer la table si elle n'existe pas
            $db->exec("
                CREATE TABLE user_addresses (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    address VARCHAR(255) NOT NULL,
                    building VARCHAR(100),
                    apartment VARCHAR(100),
                    city VARCHAR(100) NOT NULL,
                    delivery_instructions TEXT,
                    is_primary BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");
        }
        
        // Vérifier si l'utilisateur a déjà une adresse enregistrée
        $stmt = $db->prepare("SELECT id FROM user_addresses WHERE user_id = ? AND is_primary = 1");
        $stmt->execute([$userId]);
        $existingAddress = $stmt->fetch();
        
        if ($existingAddress) {
            // Mettre à jour l'adresse existante
            $stmt = $db->prepare("
                UPDATE user_addresses 
                SET address = ?, building = ?, apartment = ?, city = ?, delivery_instructions = ?
                WHERE id = ?
            ");
            $stmt->execute([$address, $building, $apartment, $city, $deliveryInstructions, $existingAddress['id']]);
        } else {
            // Créer une nouvelle adresse
            $stmt = $db->prepare("
                INSERT INTO user_addresses (user_id, address, building, apartment, city, delivery_instructions, is_primary)
                VALUES (?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([$userId, $address, $building, $apartment, $city, $deliveryInstructions]);
        }
    }
    
    // Vider le panier après commande réussie
    unset($_SESSION['cart']);
    error_log("Panier vidé");
    
    // Rediriger en fonction de la méthode de paiement
    if ($paymentMethod == 'cash') {
        // Pour le paiement à la livraison, rediriger directement vers la confirmation
        error_log("Paiement à la livraison, redirection vers confirmation.php");
        header("Location: confirmation.php?order_id=" . $order_id);
    } else {
        // Pour le paiement en ligne, rediriger vers la page de paiement
        error_log("Paiement en ligne, redirection vers checkout.php");
        header("Location: checkout.php?restaurant_id=" . $restaurantId . "&order_id=" . $order_id);
    }
    exit();
    
} catch (PDOException $e) {
    error_log("Erreur PDO process_delivery: " . $e->getMessage());
    $_SESSION['error'] = "Une erreur de base de données est survenue: " . $e->getMessage();
    header("Location: delivery_info.php?restaurant_id=" . $restaurantId);
    exit();
} catch (Exception $e) {
    error_log("Erreur générale process_delivery: " . $e->getMessage());
    $_SESSION['error'] = "Une erreur inattendue est survenue: " . $e->getMessage();
    header("Location: delivery_info.php?restaurant_id=" . $restaurantId);
    exit();
}