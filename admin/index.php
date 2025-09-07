<?php
// Sécurité : Vérification d'authentification en PREMIER
// SÉCURITÉ - Doit être la première ligne

define('ADMIN_ACCESS', true);
require_once __DIR__.'/check_auth.php';

// Inclure les autres fichiers
require_once __DIR__.'/includes/admin_nav.php';
require_once __DIR__.'/../includes/functions.php';

// Vérifier si on accède au dossier directement
if (basename($_SERVER['SCRIPT_FILENAME']) == 'index.php' && 
    rtrim($_SERVER['REQUEST_URI'], '/') == '/resto_plateform/admin') {
    header("Location: /resto_plateform/admin/index.php");
    exit();
}

// Vérifier si on accède au dossier directement (autre méthode)
$request_uri = $_SERVER['REQUEST_URI'];
if (rtrim($request_uri, '/') === '/resto_plateform/admin') {
    header("Location: /resto_plateform/admin/index.php");
    exit();
}

// Vérifier si l'utilisateur tente d'accéder au dossier plutôt qu'au fichier
if (basename($_SERVER['SCRIPT_NAME']) != 'index.php' && empty($_SERVER['QUERY_STRING'])) {
    header("Location: index.php");
    exit();
}

// Vérifier que l'utilisateur existe toujours en base (déjà fait dans check_auth.php)
// Récupération des statistiques RÉELLES
$restaurantsCount = $db->query("SELECT COUNT(*) FROM restaurants")->fetchColumn();
$menusCount = $db->query("SELECT COUNT(*) FROM menus")->fetchColumn();
$usersCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Commandes aujourd'hui
$ordersCount = $db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();

// Revenus du jour
$todayRevenue = $db->query("SELECT COALESCE(SUM(total_price), 0) as revenue FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'")->fetchColumn();

// Derniers restaurants ajoutés
$latestRestaurants = $db->query("SELECT * FROM restaurants ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Commandes récentes
$recentOrders = $db->query("
    SELECT o.*, 
           COALESCE(CONCAT(u.first_name, ' ', u.last_name), o.guest_name, 'Client non inscrit') as client_name,
           COALESCE(u.phone, 'Non spécifié') as client_phone,
           r.name as restaurant_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    JOIN restaurants r ON o.restaurant_id = r.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll();

// Utilisateurs récemment inscrits (restaurateurs seulement)
$recentUsers = $db->query("
    SELECT * FROM users 
    WHERE role = 'restaurateur' 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();

// Calcul de la croissance (pourcentage d'augmentation des commandes par rapport à hier)
$yesterdayOrders = $db->query("
    SELECT COUNT(*) as count 
    FROM orders 
    WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
")->fetchColumn();

$growthPercentage = 0;
if ($yesterdayOrders > 0) {
    $growthPercentage = (($ordersCount - $yesterdayOrders) / $yesterdayOrders) * 100;
}

// Récupération des données pour le graphique des revenus des 7 derniers jours
$weeklyRevenue = $db->query("
    SELECT 
        DATE(created_at) as date,
        COALESCE(SUM(total_price), 0) as revenue
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
        AND status != 'cancelled'
    GROUP BY DATE(created_at)
    ORDER BY date
")->fetchAll();

// Préparer les données pour le graphique
$revenueLabels = [];
$revenueValues = [];

$current_date = new DateTime();
for ($i = 6; $i >= 0; $i--) {
    $date = (new DateTime())->modify("-$i days")->format('Y-m-d');
    $revenueLabels[] = (new DateTime($date))->format('D');
    
    $found = false;
    foreach ($weeklyRevenue as $revenue) {
        if ($revenue['date'] == $date) {
            $revenueValues[] = $revenue['revenue'];
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $revenueValues[] = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin - FoodManager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --accent-color: #D4AF37;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        
        .sidebar {
            background: white;
            min-height: calc(100vh - 73px);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 73px;
        }
        
        .sidebar .nav-link {
            color: #495057;
            border-radius: 5px;
            margin: 5px 0;
            padding: 10px 15px;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }
        
        .card-dashboard {
            transition: transform 0.3s;
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .card-dashboard:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .bg-primary-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }
        
        .bg-success-gradient {
            background: linear-gradient(135deg, var(--success-color) 0%, #3bd671 100%);
            color: white;
        }
        
        .bg-warning-gradient {
            background: linear-gradient(135deg, var(--warning-color) 0%, #ffda6a 100%);
            color: white;
        }
        
        .bg-danger-gradient {
            background: linear-gradient(135deg, var(--danger-color) 0%, #ea868f 100%);
            color: white;
        }
        
        .bg-info-gradient {
            background: linear-gradient(135deg, #17a2b8 0%, #6ad2eb 100%);
            color: white;
        }
        
        .card-icon {
            font-size: 2rem;
            margin-bottom: 15px;
        }
        
        .latest-item {
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s;
        }
        
        .latest-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .quick-action-btn {
            transition: all 0.3s;
            border-radius: 8px;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }
        
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
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
        
        .growth-positive {
            color: var(--success-color);
        }
        
        .growth-negative {
            color: var(--danger-color);
        }
    </style>
</head>
<body>
    <!-- Navigation principale -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-utensils me-2"></i>FoodManager
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($_SESSION['user_name'] ?? 'Administrateur') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Paramètres</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 col-md-3 p-0 sidebar">
                <div class="d-flex flex-column p-3">
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="index.php" class="nav-link active">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Tableau de bord
                            </a>
                        </li>
                        <li>
                            <a href="restaurants.php" class="nav-link">
                                <i class="fas fa-utensils me-2"></i>
                                Restaurants
                            </a>
                        </li>
                        <li>
                            <a href="menus.php" class="nav-link">
                                <i class="fas fa-list me-2"></i>
                                Menus
                            </a>
                        </li>
                        <li>
                            <a href="orders.php" class="nav-link">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Commandes
                            </a>
                        </li>
                        <li>
                            <a href="users.php" class="nav-link">
                                <i class="fas fa-users me-2"></i>
                                Utilisateurs
                            </a>
                        </li>
                        <li>
                            <a href="payments.php" class="nav-link">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Paiements
                            </a>
                        </li>
                        <li>
                            <a href="notifications.php" class="nav-link">
                                <i class="fas fa-bell me-2"></i>
                                Notifications
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Contenu principal -->
            <div class="col-lg-10 col-md-9 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-tachometer-alt me-2"></i>Tableau de bord Administrateur</h2>
                    <button class="btn btn-outline-primary" id="refreshBtn">
                        <i class="fas fa-sync-alt me-1"></i>Actualiser
                    </button>
                </div>

                <!-- Cartes de statistiques -->
                <div class="row mb-4">
                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card card-dashboard bg-primary-gradient text-white h-100">
                            <div class="card-body text-center">
                                <div class="card-icon">
                                    <i class="fas fa-utensils"></i>
                                </div>
                                <h2 class="stat-number"><?= $restaurantsCount ?></h2>
                                <p class="stat-label">Restaurants</p>
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0">
                                <a href="restaurants.php" class="btn btn-sm btn-light w-100">Voir tous</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card card-dashboard bg-success-gradient text-white h-100">
                            <div class="card-body text-center">
                                <div class="card-icon">
                                    <i class="fas fa-list"></i>
                                </div>
                                <h2 class="stat-number"><?= $menusCount ?></h2>
                                <p class="stat-label">Menus</p>
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0">
                                <a href="menus.php" class="btn btn-sm btn-light w-100">Gérer</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card card-dashboard bg-warning-gradient text-white h-100">
                            <div class="card-body text-center">
                                <div class="card-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h2 class="stat-number"><?= $usersCount ?></h2>
                                <p class="stat-label">Utilisateurs</p>
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0">
                                <a href="users.php" class="btn btn-sm btn-light w-100">Gérer</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card card-dashboard bg-info-gradient text-white h-100">
                            <div class="card-body text-center">
                                <div class="card-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <h2 class="stat-number"><?= $ordersCount ?></h2>
                                <p class="stat-label">Commandes aujourd'hui</p>
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0">
                                <a href="orders.php" class="btn btn-sm btn-light w-100">Voir</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card card-dashboard bg-danger-gradient text-white h-100">
                            <div class="card-body text-center">
                                <div class="card-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <h2 class="stat-number"><?= number_format($todayRevenue, 0, ',', ' ') ?> F</h2>
                                <p class="stat-label">Revenus aujourd'hui</p>
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0">
                                <a href="payments.php" class="btn btn-sm btn-light w-100">Détails</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card card-dashboard h-100">
                            <div class="card-body text-center">
                                <div class="card-icon text-primary">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h2 class="stat-number <?= $growthPercentage >= 0 ? 'growth-positive' : 'growth-negative' ?>">
                                    <?= $growthPercentage >= 0 ? '+' : '' ?><?= number_format($growthPercentage, 1) ?>%
                                </h2>
                                <p class="stat-label text-muted">Croissance</p>
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0">
                                <a href="orders.php" class="btn btn-sm btn-outline-primary w-100">Détails</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Graphique des revenus -->
                    <div class="col-lg-8 mb-4">
                        <div class="card card-dashboard h-100">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Revenus des 7 derniers jours</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions rapides -->
                    <div class="col-lg-4 mb-4">
                        <div class="card card-dashboard h-100">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions rapides</h5>
                            </div>
                            <div class="card-body">
                                <a href="restaurants.php?action=add" class="btn btn-primary quick-action-btn w-100 mb-3 text-start">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white rounded-circle p-2 me-3">
                                            <i class="fas fa-plus text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Ajouter un restaurant</h6>
                                            <small>Nouveau restaurant</small>
                                        </div>
                                    </div>
                                </a>
                                
                                <a href="menus.php?action=add" class="btn btn-success quick-action-btn w-100 mb-3 text-start">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white rounded-circle p-2 me-3">
                                            <i class="fas fa-list text-success"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Ajouter un menu</h6>
                                            <small>Nouveau menu</small>
                                        </div>
                                    </div>
                                </a>
                                
                                <a href="users.php?action=add" class="btn btn-warning quick-action-btn w-100 mb-3 text-start">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white rounded-circle p-2 me-3">
                                            <i class="fas fa-user-plus text-warning"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Ajouter un utilisateur</h6>
                                            <small>Nouveau gestionnaire</small>
                                        </div>
                                    </div>
                                </a>
                                
                                <a href="orders.php" class="btn btn-info quick-action-btn w-100 text-start">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white rounded-circle p-2 me-3">
                                            <i class="fas fa-shopping-cart text-info"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Voir les commandes</h6>
                                            <small>Commandes en cours</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Derniers restaurants ajoutés -->
                    <div class="col-lg-6 mb-4">
                        <div class="card card-dashboard h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-utensils me-2"></i>Derniers restaurants</h5>
                                <a href="restaurants.php" class="btn btn-sm btn-outline-primary">Voir tout</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($latestRestaurants as $restaurant): ?>
                                    <a href="restaurants.php?action=edit&id=<?= $restaurant['id'] ?>" 
                                       class="list-group-item list-group-item-action latest-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <?php 
                                            $imageUrl = $restaurant['image_url'] ?? '';
                                            if (!empty($imageUrl)): 
                                            ?>
                                                <img src="<?= htmlspecialchars($imageUrl) ?>" class="rounded me-3" style="width: 40px; height: 40px; object-fit: cover;" alt="<?= htmlspecialchars($restaurant['name']) ?>">
                                            <?php else: ?>
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-utensils text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($restaurant['name']) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars(substr($restaurant['address'], 0, 30)) ?>...</small>
                                            </div>
                                        </div>
                                        <span class="badge bg-light text-dark">
                                            <?= date('d/m/Y', strtotime($restaurant['created_at'])) ?>
                                        </span>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Commandes récentes -->
                    <div class="col-lg-6 mb-4">
                        <div class="card card-dashboard h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Commandes récentes</h5>
                                <a href="orders.php" class="btn btn-sm btn-outline-primary">Voir tout</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recentOrders as $order): ?>
                                    <a href="orders.php?action=view&id=<?= $order['id'] ?>" 
                                       class="list-group-item list-group-item-action latest-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">Commande #<?= $order['id'] ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($order['client_name']) ?></small>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($order['restaurant_name']) ?></small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-light text-dark"><?= number_format($order['total_price'], 0, ',', ' ') ?> F</span>
                                                <div>
                                                    <small class="text-muted"><?= date('H:i', strtotime($order['created_at'])) ?></small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <?php
                                            $statusClass = '';
                                            switch ($order['status']) {
                                                case 'pending': $statusClass = 'status-pending'; break;
                                                case 'preparing': $statusClass = 'status-preparing'; break;
                                                case 'ready': $statusClass = 'status-ready'; break;
                                                case 'delivered': $statusClass = 'status-delivered'; break;
                                                case 'cancelled': $statusClass = 'status-cancelled'; break;
                                                default: $statusClass = 'status-pending';
                                            }
                                            ?>
                                            <span class="status-badge <?= $statusClass ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Graphique des revenus avec les VRAIES données
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($revenueLabels) ?>,
                    datasets: [{
                        label: 'Revenus (FCFA)',
                        data: <?= json_encode($revenueValues) ?>,
                        backgroundColor: 'rgba(106, 17, 203, 0.7)',
                        borderColor: 'rgba(106, 17, 203, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('fr-FR') + ' F';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y.toLocaleString('fr-FR') + ' FCFA';
                                }
                            }
                        }
                    }
                }
            });
            
            // Bouton actualiser
            $('#refreshBtn').click(function() {
                location.reload();
            });
        });
    </script>
</body>
</html>