<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';

// Vérifier que l'utilisateur est admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die('Accès refusé');
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation des données
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $restaurantName = trim($_POST['restaurantName'] ?? '');
    $restaurantDescription = trim($_POST['restaurantDescription'] ?? '');
    $restaurantAddress = trim($_POST['restaurantAddress'] ?? '');
    $restaurantPhone = trim($_POST['restaurantPhone'] ?? '');
    $hasDelivery = ($_POST['hasDelivery'] ?? '0') === '1';
    $hasPickup = ($_POST['hasPickup'] ?? '0') === '1';
    
    // Validation des données
    $errors = [];
    
    if (empty($firstName)) $errors[] = "Le prénom est requis";
    if (empty($lastName)) $errors[] = "Le nom est requis";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'email est invalide";
    if (strlen($password) < 6) $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
    if (empty($restaurantName)) $errors[] = "Le nom du restaurant est requis";
    if (empty($restaurantAddress)) $errors[] = "L'adresse du restaurant est requise";
    
    // Vérifier si l'email existe déjà
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Cet email est déjà utilisé";
    }
    
    // Si des erreurs, les retourner
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit();
    }
    
    try {
        $db->beginTransaction();
        
        // 1. Créer l'utilisateur
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $username = generateUsername($firstName, $lastName);
        
        $stmt = $db->prepare("
            INSERT INTO users (username, first_name, last_name, email, phone, password, role) 
            VALUES (?, ?, ?, ?, ?, ?, 'restaurateur')
        ");
        $stmt->execute([$username, $firstName, $lastName, $email, $phone, $hashedPassword]);
        $userId = $db->lastInsertId();
        
        // 2. Gérer l'upload du logo
        $logoUrl = null;
        if (isset($_FILES['restaurantLogo']) && $_FILES['restaurantLogo']['error'] === UPLOAD_ERR_OK) {
            $logoUrl = handleRestaurantImageUpload('restaurantLogo');
        }
        
        // 3. Créer le restaurant
        $stmt = $db->prepare("
            INSERT INTO restaurants (user_id, name, description, address, phone, image_url, has_delivery, has_pickup) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId, $restaurantName, $restaurantDescription, 
            $restaurantAddress, $restaurantPhone, $logoUrl, 
            $hasDelivery, $hasPickup
        ]);
        $restaurantId = $db->lastInsertId();
        
        // 4. Envoyer un email de bienvenue (simulé)
        sendWelcomeEmail($email, $firstName, $restaurantName, $username, $password);
        
        $db->commit();
        
        // Réponse de succès
        echo json_encode([
            'success' => true, 
            'message' => 'Gestionnaire et restaurant créés avec succès',
            'user_id' => $userId,
            'restaurant_id' => $restaurantId
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Erreur lors de la création: ' . $e->getMessage()]]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'errors' => ['Méthode non autorisée']]);
}

// Fonction pour générer un nom d'utilisateur
function generateUsername($firstName, $lastName) {
    global $db;
    
    $baseUsername = strtolower(substr($firstName, 0, 1) . preg_replace('/\s+/', '', $lastName));
    $username = $baseUsername;
    $counter = 1;
    
    // Vérifier si le nom d'utilisateur existe déjà
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    
    do {
        $stmt->execute([$username]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            $username = $baseUsername . $counter;
            $counter++;
        }
    } while ($exists);
    
    return $username;
}

// Fonction pour envoyer un email de bienvenue (simulée)
function sendWelcomeEmail($email, $firstName, $restaurantName, $username, $password) {
    $subject = "Bienvenue sur FoodManager - Votre compte gestionnaire";
    $message = "
        <h2>Bienvenue sur FoodManager, $firstName!</h2>
        <p>Votre compte gestionnaire a été créé avec succès pour le restaurant <strong>$restaurantName</strong>.</p>
        <p><strong>Identifiants de connexion:</strong></p>
        <ul>
            <li>Email: $email</li>
            <li>Mot de passe: $password</li>
        </ul>
        <p>Nous vous recommandons de changer votre mot de passe après votre première connexion.</p>
        <p>Connectez-vous dès maintenant pour commencer à gérer votre restaurant: <a href=\"https://votredomaine.com/manager\">https://votredomaine.com/manager</a></p>
        <br>
        <p>Cordialement,<br>L'équipe FoodManager</p>
    ";
    
    // Envoyer l'email (cette partie dépendra de votre configuration d'envoi d'emails)
    // mail($email, $subject, $message, "Content-Type: text/html; charset=UTF-8");
    
    // Pour l'instant, on se contente de logger
    error_log("Email de bienvenue envoyé à: $email");
}
?>