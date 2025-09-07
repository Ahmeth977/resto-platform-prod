<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/includes/admin_nav.php';



// Récupérer les commandes avec pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Gérer les filtres
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$search_filter = isset($_GET['search']) ? $_GET['search'] : '';

// Construire la requête de base avec jointures pour récupérer TOUTES les commandes
$query = "SELECT o.*, 
                 COALESCE(CONCAT(u.first_name, ' ', u.last_name), o.guest_name, 'Client non inscrit') as client_name,
                 COALESCE(u.phone, o.delivery_phone, 'Non spécifié') as client_phone,
                 r.name as restaurant_name 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          LEFT JOIN restaurants r ON o.restaurant_id = r.id";

$count_query = "SELECT COUNT(*) FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                LEFT JOIN restaurants r ON o.restaurant_id = r.id";

// Ajouter les conditions de filtrage
$conditions = [];
$filter_params = [];

if (!empty($status_filter)) {
    $conditions[] = "o.status = ?";
    $filter_params[] = $status_filter;
}

if (!empty($date_filter)) {
    $conditions[] = "DATE(o.created_at) = ?";
    $filter_params[] = $date_filter;
}

if (!empty($search_filter)) {
    $conditions[] = "(o.id LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR o.guest_name LIKE ? OR r.name LIKE ?)";
    $search_param = "%$search_filter%";
    for ($i = 0; $i < 5; $i++) {
        $filter_params[] = $search_param;
    }
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
    $count_query .= " WHERE " . implode(" AND ", $conditions);
}

// Ajouter le tri et la pagination
$query .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";

// Paramètres pour la requête principale (filtres + pagination)
$main_params = array_merge($filter_params, [$limit, $offset]);

// Exécuter les requêtes
try {
    // Requête principale
    $stmt = $db->prepare($query);
    $stmt->execute($main_params);
    $orders = $stmt->fetchAll();

    // Requête de comptage (seulement avec les filtres)
    $stmt_count = $db->prepare($count_query);
    $stmt_count->execute($filter_params);
    $total_orders = $stmt_count->fetchColumn();
    $total_pages = ceil($total_orders / $limit);
    
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des commandes: " . $e->getMessage());
    $orders = [];
    $total_pages = 1;
    $total_orders = 0;
}

// Récupérer les détails d'une commande spécifique si demandée
$order_details = [];
$order_items = [];
if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])) {
    $order_id = (int)$_GET['id'];
    
    try {
        // Récupérer les informations COMPLÈTES de la commande avec infos de livraison
        $stmt = $db->prepare("
            SELECT o.*, 
                   COALESCE(CONCAT(u.first_name, ' ', u.last_name), o.guest_name, 'Client non inscrit') as client_name,
                   COALESCE(u.email, o.guest_email, 'Non spécifié') as client_email,
                   COALESCE(u.phone, o.delivery_phone, 'Non spécifié') as client_phone,
                   r.name as restaurant_name, r.address as restaurant_address, r.phone as restaurant_phone,
                   p.payment_method, p.status as payment_status, p.transaction_id, p.amount as payment_amount,
                   o.delivery_address, o.delivery_city, o.delivery_building, o.delivery_apartment,
                   o.delivery_instructions, o.delivery_phone, o.client_first_name, o.client_last_name
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            LEFT JOIN restaurants r ON o.restaurant_id = r.id
            LEFT JOIN payments p ON o.id = p.order_id
            WHERE o.id = ?
        ");
        $stmt->execute([$order_id]);
        $order_details = $stmt->fetch();
        
        // Récupérer les articles de la commande
        if ($order_details) {
            $stmt_items = $db->prepare("
                SELECT oi.*, m.name as menu_name, m.image_url as menu_image
                FROM order_items oi 
                LEFT JOIN menus m ON oi.menu_id = m.id 
                WHERE oi.order_id = ?
            ");
            $stmt_items->execute([$order_id]);
            $order_items = $stmt_items->fetchAll();
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des détails de la commande: " . $e->getMessage());
    }
}

// Mettre à jour le statut d'une commande
if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $notify_customer = isset($_POST['notify_customer']) ? true : false;
    
    try {
        $stmt = $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        
        // Notification au client si demandée
        if ($notify_customer) {
            // Récupérer les infos du client
            $stmt = $db->prepare("
                SELECT o.*, 
                       COALESCE(u.email, o.guest_email) as client_email,
                       COALESCE(CONCAT(u.first_name, ' ', u.last_name), o.guest_name) as client_name,
                       u.id as user_id
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?
            ");
            $stmt->execute([$order_id]);
            $order_info = $stmt->fetch();
            
            if ($order_info && !empty($order_info['client_email'])) {
                // Envoyer l'email de notification
                $email_sent = sendOrderNotification($order_id, $status, [
                    'name' => $order_info['client_name'],
                    'email' => $order_info['client_email']
                ]);
                
                if ($email_sent) {
                    $_SESSION['success_message'] = "Statut de la commande mis à jour et client notifié par email.";
                } else {
                    $_SESSION['success_message'] = "Statut de la commande mis à jour, mais l'email de notification n'a pas pu être envoyé.";
                }
            } else {
                $_SESSION['success_message'] = "Statut de la commande mis à jour (notification non envoyée - email manquant).";
            }
        } else {
            $_SESSION['success_message'] = "Statut de la commande mis à jour.";
        }
        
        header("Location: orders.php?action=view&id=" . $order_id);
        exit();
    } catch (PDOException $e) {
        error_log("Erreur lors de la mise à jour du statut: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors de la mise à jour du statut.";
    }
}

// Fonction pour envoyer des notifications par email
function sendOrderNotification($order_id, $status, $customer_info) {
    global $db;
    
    // Récupérer les détails du restaurant
    $stmt = $db->prepare("
        SELECT r.name as restaurant_name, r.email as restaurant_email 
        FROM orders o 
        JOIN restaurants r ON o.restaurant_id = r.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $restaurant = $stmt->fetch();
    
    $status_messages = [
        'pending' => 'est en attente de confirmation',
        'preparing' => 'est en cours de préparation',
        'ready' => 'est prête à être récupérée',
        'delivered' => 'a été livrée',
        'cancelled' => 'a été annulée'
    ];
    
    $status_fr = $status_messages[$status] ?? 'a été mise à jour';
    
    $subject = "FoodManager - Mise à jour de votre commande #" . $order_id;
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 20px; }
            .footer { background: #eee; padding: 15px; text-align: center; border-radius: 0 0 10px 10px; font-size: 12px; }
            .status-update { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .button { display: inline-block; padding: 10px 20px; background: #6a11cb; color: white; text-decoration: none; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>FoodManager</h1>
                <p>Votre service de livraison de repas</p>
            </div>
            <div class='content'>
                <h2>Bonjour " . htmlspecialchars($customer_info['name']) . ",</h2>
                <p>Votre commande <strong>#" . $order_id . "</strong> " . $status_fr . ".</p>
                
                <div class='status-update'>
                    <h3>Statut actuel : " . ucfirst($status) . "</h3>
                    <p>Restaurant : " . htmlspecialchars($restaurant['restaurant_name']) . "</p>
                </div>
                
                <p>Vous pouvez suivre l'état de votre commande en vous connectant à votre compte FoodManager.</p>
                
                <p style='text-align: center;'>
                    <a href='http://votre-site.com/login.php' class='button'>Voir ma commande</a>
                </p>
                
                <p>Si vous avez des questions, n'hésitez pas à répondre à cet email.</p>
            </div>
            <div class='footer'>
                <p>© " . date('Y') . " FoodManager. Tous droits réservés.</p>
                <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre directement.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Headers pour l'email HTML
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: FoodManager <no-reply@foodmanager.com>" . "\r\n";
    $headers .= "Reply-To: " . $restaurant['restaurant_email'] . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Envoyer l'email
    if (!empty($customer_info['email'])) {
        $success = mail($customer_info['email'], $subject, $message, $headers);
        
        // Log pour le débogage
        error_log("Email notification sent to " . $customer_info['email'] . " for order #" . $order_id . ". Status: " . $status . ". Success: " . ($success ? "yes" : "no"));
        
        return $success;
    }
    
    return false;
}

// Fonction pour journaliser les notifications
function logNotification($order_id, $recipient_email, $notification_type, $status, $success, $error_message = null) {
    global $db;
    
    $stmt = $db->prepare("
        INSERT INTO notifications_log 
        (order_id, recipient_email, notification_type, status, success, error_message) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([
        $order_id, 
        $recipient_email, 
        $notification_type, 
        $status, 
        $success ? 1 : 0, 
        $error_message
    ]);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Commandes - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-preparing {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-ready {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-delivered {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .order-details {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .order-items {
            margin-top: 20px;
        }
        
        .order-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        
        .filter-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .table-responsive {
            min-height: 300px;
        }
        
        .summary-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            min-height: 100vh;
            padding: 0;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            padding: 20px;
        }
        
        .header {
            background-color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .pagination .page-link {
            color: var(--primary-color);
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .hover-shadow {
            transition: all 0.3s;
        }
        
        .hover-shadow:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }
        
        .spinner {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <!-- Navigation principale -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include __DIR__.'/includes/admin_sidebar.php'; ?>

            <!-- Contenu principal -->
            <div class="col-lg-10 col-md-9 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-shopping-cart me-2"></i>Gestion des Commandes</h2>
                    <div>
                        <a href="index.php" class="btn btn-outline-primary me-2">
                            <i class="fas fa-arrow-left me-1"></i>Retour au tableau de bord
                        </a>
                        <a href="orders.php?export=csv" class="btn btn-success">
                            <i class="fas fa-download me-1"></i>Exporter
                        </a>
                    </div>
                </div>

                <!-- Messages de notification -->
                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <!-- Filtres -->
                <div class="filter-form">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="action" value="<?= isset($_GET['action']) ? $_GET['action'] : '' ?>">
                        <input type="hidden" name="id" value="<?= isset($_GET['id']) ? $_GET['id'] : '' ?>">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>En attente</option>
                                <option value="preparing" <?= $status_filter == 'preparing' ? 'selected' : '' ?>>En préparation</option>
                                <option value="ready" <?= $status_filter == 'ready' ? 'selected' : '' ?>>Prêt</option>
                                <option value="delivered" <?= $status_filter == 'delivered' ? 'selected' : '' ?>>Livré</option>
                                <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Annulé</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" value="<?= $date_filter ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="search" class="form-label">Recherche</label>
                            <input type="text" class="form-control" id="search" name="search" placeholder="ID, client, restaurant..." value="<?= htmlspecialchars($search_filter) ?>">
                        </div>
                        <div class="col-md-3 align-self-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i>Filtrer
                            </button>
                            <a href="orders.php" class="btn btn-secondary">
                                <i class="fas fa-sync me-1"></i>Réinitialiser
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Résumé des commandes -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="summary-card">
                            <div class="d-flex justify-content-between">
                                <div class="text-center">
                                    <h5 class="mb-0"><?= $total_orders ?></h5>
                                    <small>Total commandes</small>
                                </div>
                                <div class="text-center">
                                    <h5 class="mb-0"><?= $total_pages ?></h5>
                                    <small>Pages totales</small>
                                </div>
                                <div class="text-center">
                                    <h5 class="mb-0"><?= $limit ?></h5>
                                    <small>Commandes par page</small>
                                </div>
                                <div class="text-center">
                                    <h5 class="mb-0"><?= $page ?></h5>
                                    <small>Page actuelle</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Détails d'une commande -->
                <?php if (isset($_GET['action']) && $_GET['action'] == 'view' && !empty($order_details)): ?>
                <div class="order-details">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4>Détails de la commande #<?= $order_details['id'] ?></h4>
                        <a href="orders.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Retour à la liste
                        </a>
                    </div>

                    <div class="row">
                        <!-- Informations client -->
                        <div class="col-md-6">
                            <div class="card mb-4 hover-shadow">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations client</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Nom complet:</strong> <?= htmlspecialchars($order_details['client_name']) ?></p>
                                    <p><strong>Prénom:</strong> <?= htmlspecialchars($order_details['client_first_name'] ?? 'Non spécifié') ?></p>
                                    <p><strong>Nom:</strong> <?= htmlspecialchars($order_details['client_last_name'] ?? 'Non spécifié') ?></p>
                                    <p><strong>Email:</strong> <?= htmlspecialchars($order_details['client_email']) ?></p>
                                    <p><strong>Téléphone:</strong> <?= htmlspecialchars($order_details['delivery_phone'] ?? $order_details['client_phone']) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Informations commande -->
                        <div class="col-md-6">
                            <div class="card mb-4 hover-shadow">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Informations commande</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Restaurant:</strong> <?= htmlspecialchars($order_details['restaurant_name']) ?></p>
                                    <p><strong>Adresse restaurant:</strong> <?= htmlspecialchars($order_details['restaurant_address']) ?></p>
                                    <p><strong>Téléphone restaurant:</strong> <?= htmlspecialchars($order_details['restaurant_phone'] ?? 'Non spécifié') ?></p>
                                    <p><strong>Date:</strong> <?= date('d/m/Y H:i', strtotime($order_details['created_at'])) ?></p>
                                    <p><strong>Statut:</strong> 
                                        <span class="status-badge status-<?= $order_details['status'] ?>">
                                            <?= ucfirst($order_details['status']) ?>
                                        </span>
                                    </p>
                                    <p><strong>Total:</strong> <?= number_format($order_details['total_price'], 2) ?> CFA</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations de livraison -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4 hover-shadow">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-truck me-2"></i>Informations de livraison</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Adresse:</strong> <?= htmlspecialchars($order_details['delivery_address'] ?? 'Non spécifié') ?></p>
                                            <p><strong>Bâtiment:</strong> <?= htmlspecialchars($order_details['delivery_building'] ?? 'Non spécifié') ?></p>
                                            <p><strong>Appartement:</strong> <?= htmlspecialchars($order_details['delivery_apartment'] ?? 'Non spécifié') ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Ville:</strong> <?= htmlspecialchars($order_details['delivery_city'] ?? 'Non spécifié') ?></p>
                                            <p><strong>Téléphone livraison:</strong> <?= htmlspecialchars($order_details['delivery_phone'] ?? 'Non spécifié') ?></p>
                                            <p><strong>Instructions:</strong> <?= htmlspecialchars($order_details['delivery_instructions'] ?? 'Aucune instruction') ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Articles de la commande -->
                    <div class="order-items">
                        <div class="card hover-shadow">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Articles commandés</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Article</th>
                                            <th>Prix unitaire</th>
                                            <th>Quantité</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($item['menu_image'])): ?>
                                                    <img src="<?= htmlspecialchars($item['menu_image']) ?>" 
                                                         alt="<?= htmlspecialchars($item['menu_name']) ?>" 
                                                         width="50" height="50" 
                                                         style="object-fit: cover; border-radius: 5px; margin-right: 10px;">
                                                    <?php endif; ?>
                                                    <div>
                                                        <?= htmlspecialchars($item['menu_name']) ?>
                                                        <?php if (!empty($item['options'])): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($item['options']) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= number_format($item['unit_price'], 2) ?> CFA</td>
                                            <td><?= $item['quantity'] ?></td>
                                            <td><?= number_format($item['unit_price'] * $item['quantity'], 2) ?> CFA</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                            <td><strong><?= number_format($order_details['total_price'], 2) ?> CFA</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Informations de paiement -->
                    <?php if (!empty($order_details['payment_method'])): ?>
                    <div class="payment-info mt-4">
                        <div class="card hover-shadow">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Informations de paiement</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <p><strong>Méthode:</strong> <?= ucfirst($order_details['payment_method']) ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Statut paiement:</strong> 
                                            <span class="badge bg-<?= $order_details['payment_status'] == 'completed' ? 'success' : ($order_details['payment_status'] == 'pending' ? 'warning' : 'danger') ?>">
                                                <?= ucfirst($order_details['payment_status']) ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Montant:</strong> <?= number_format($order_details['payment_amount'], 2) ?> CFA</p>
                                    </div>
                                </div>
                                <?php if (!empty($order_details['transaction_id'])): ?>
                                <p><strong>ID Transaction:</strong> <?= $order_details['transaction_id'] ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Formulaire de mise à jour du statut -->
                    <div class="update-status mt-4">
                        <div class="card hover-shadow">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0"><i class="fas fa-sync me-2"></i>Mettre à jour le statut</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" class="row g-3 align-items-end">
                                    <input type="hidden" name="order_id" value="<?= $order_details['id'] ?>">
                                    <div class="col-md-4">
                                        <label class="form-label">Nouveau statut</label>
                                        <select class="form-select" name="status" required>
                                            <option value="pending" <?= $order_details['status'] == 'pending' ? 'selected' : '' ?>>En attente</option>
                                            <option value="preparing" <?= $order_details['status'] == 'preparing' ? 'selected' : '' ?>>En préparation</option>
                                            <option value="ready" <?= $order_details['status'] == 'ready' ? 'selected' : '' ?>>Prêt</option>
                                            <option value="delivered" <?= $order_details['status'] == 'delivered' ? 'selected' : '' ?>>Livré</option>
                                            <option value="cancelled" <?= $order_details['status'] == 'cancelled' ? 'selected' : '' ?>>Annulé</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="notify_customer" id="notify_customer" checked>
                                            <label class="form-check-label" for="notify_customer">
                                                Notifier le client par email
                                            </label>
                                        </div>
                                        <?php if (!empty($order_details['client_email'])): ?>
                                        <small class="text-muted">Email: <?= htmlspecialchars($order_details['client_email']) ?></small>
                                        <?php else: ?>
                                        <small class="text-danger">Aucune adresse email disponible</small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" name="update_status" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Mettre à jour
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Liste des commandes -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Client</th>
                                        <th>Restaurant</th>
                                        <th>Total</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Aucune commande trouvée</p>
                                            <?php if ($total_orders > 0): ?>
                                            <p class="text-muted small">(Les filtres appliqués ne correspondent à aucune commande)</p>
                                            <?php else: ?>
                                            <p class="text-muted small">Aucune commande n'a été passée pour le moment</p>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><strong>#<?= $order['id'] ?></strong></td>
                                        <td><?= htmlspecialchars($order['client_name']) ?></td>
                                        <td><?= htmlspecialchars($order['restaurant_name']) ?></td>
                                        <td><?= number_format($order['total_price'], 2) ?> CFA</td>
                                        <td>
                                            <span class="status-badge status-<?= $order['status'] ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                        <td>
                                            <a href="orders.php?action=view&id=<?= $order['id'] ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Détails
                                            </a>
                                            <button class="btn btn-sm btn-outline-primary mt-1" onclick="openUpdateModal(<?= $order['id'] ?>, '<?= $order['status'] ?>', '<?= $order['client_phone'] ?>', '<?= htmlspecialchars($order['client_name']) ?>')">
                                                <i class="fas fa-sync-alt"></i> Modifier
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page-1 ?><?= $status_filter ? '&status='.$status_filter : '' ?><?= $date_filter ? '&date='.$date_filter : '' ?><?= $search_filter ? '&search='.urlencode($search_filter) : '' ?>">
                                        <i class="fas fa-chevron-left"></i> Précédent
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= $status_filter ? '&status='.$status_filter : '' ?><?= $date_filter ? '&date='.$date_filter : '' ?><?= $search_filter ? '&search='.urlencode($search_filter) : '' ?>"><?= $i ?></a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page+1 ?><?= $status_filter ? '&status='.$status_filter : '' ?><?= $date_filter ? '&date='.$date_filter : '' ?><?= $search_filter ? '&search='.urlencode($search_filter) : '' ?>">
                                        Suivant <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour mise à jour rapide -->
    <div class="modal fade" id="quickUpdateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mettre à jour le statut</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="quickUpdateForm">
                    <input type="hidden" name="order_id" id="modal_order_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nouveau statut</label>
                            <select class="form-select" name="status" required>
                                <option value="pending">En attente</option>
                                <option value="preparing">En préparation</option>
                                <option value="ready">Prêt</option>
                                <option value="delivered">Livré</option>
                                <option value="cancelled">Annulé</option>
                            </select>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="notify_customer" id="modal_notify" checked>
                            <label class="form-check-label" for="modal_notify">
                                Notifier le client par email
                            </label>
                        </div>
                        <div id="modal_phone_info"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="update_status" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirmation pour la mise à jour du statut
        document.addEventListener('DOMContentLoaded', function() {
            const statusForm = document.querySelector('form[name="update_status"]');
            if (statusForm) {
                statusForm.addEventListener('submit', function(e) {
                    if (!confirm('Êtes-vous sûr de vouloir modifier le statut de cette commande ?')) {
                        e.preventDefault();
                    }
                });
            }
            
            // Auto-submit des filtres date quand on change la date
            const dateFilter = document.getElementById('date');
            if (dateFilter) {
                dateFilter.addEventListener('change', function() {
                    this.form.submit();
                });
            }
        });

        // Gestion du modal de mise à jour rapide
        function openUpdateModal(orderId, currentStatus, clientPhone, clientName) {
            document.getElementById('modal_order_id').value = orderId;
            document.querySelector('#quickUpdateForm select[name="status"]').value = currentStatus;
            
            const phoneInfo = document.getElementById('modal_phone_info');
            if (clientPhone && clientPhone !== 'Non spécifié') {
                phoneInfo.innerHTML = `<small class="text-muted">Notification vers: ${clientPhone} (${clientName})</small>`;
                document.getElementById('modal_notify').disabled = false;
            } else {
                phoneInfo.innerHTML = '<small class="text-danger">Aucun numéro disponible pour notification</small>';
                document.getElementById('modal_notify').disabled = true;
                document.getElementById('modal_notify').checked = false;
            }
            
            const modal = new bootstrap.Modal(document.getElementById('quickUpdateModal'));
            modal.show();
        }
    </script>
</body>
</html>