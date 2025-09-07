<?php
require_once __DIR__.'/includes/auth.php';

// Récupérer les statistiques du restaurant
$restaurant_id = $_SESSION['restaurant_id'];

// Commandes aujourd'hui
$orders_today = $db->prepare("
    SELECT COUNT(*) as count FROM orders 
    WHERE restaurant_id = ? AND DATE(created_at) = CURDATE()
");
$orders_today->execute([$restaurant_id]);
$orders_today_count = $orders_today->fetch()['count'];

// Revenu aujourd'hui
$revenue_today = $db->prepare("
    SELECT COALESCE(SUM(total_price), 0) as total FROM orders 
    WHERE restaurant_id = ? AND DATE(created_at) = CURDATE() AND status != 'cancelled'
");
$revenue_today->execute([$restaurant_id]);
$revenue_today_total = $revenue_today->fetch()['total'];

// Commandes en cours
$orders_processing = $db->prepare("
    SELECT COUNT(*) as count FROM orders 
    WHERE restaurant_id = ? AND status IN ('pending', 'preparing')
");
$orders_processing->execute([$restaurant_id]);
$orders_processing_count = $orders_processing->fetch()['count'];

// Note moyenne
$avg_rating = $db->prepare("
    SELECT COALESCE(AVG(rating), 0) as average FROM reviews 
    WHERE restaurant_id = ?
");
$avg_rating->execute([$restaurant_id]);
$avg_rating_value = round($avg_rating->fetch()['average'], 1);

// Commandes récentes
$recent_orders = $db->prepare("
    SELECT o.*, u.username as customer_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.restaurant_id = ? 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$recent_orders->execute([$restaurant_id]);
$recent_orders_data = $recent_orders->fetchAll();

// Menus populaires
$popular_menus = $db->prepare("
    SELECT m.name, m.price, COUNT(oi.id) as order_count, 
           SUM(oi.quantity * oi.unit_price) as revenue,
           m.is_available
    FROM order_items oi
    JOIN menus m ON oi.menu_id = m.id
    JOIN orders o ON oi.order_id = o.id
    WHERE m.restaurant_id = ? AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY m.id
    ORDER BY order_count DESC
    LIMIT 5
");
$popular_menus->execute([$restaurant_id]);
$popular_menus_data = $popular_menus->fetchAll();

// Revenus des 7 derniers jours
$weekly_revenue = $db->prepare("
    SELECT DATE(created_at) as date, COALESCE(SUM(total_price), 0) as revenue
    FROM orders
    WHERE restaurant_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status != 'cancelled'
    GROUP BY DATE(created_at)
    ORDER BY date
");
$weekly_revenue->execute([$restaurant_id]);
$weekly_revenue_data = $weekly_revenue->fetchAll();

// Préparer les données pour le graphique
$revenue_labels = [];
$revenue_values = [];

$current_date = new DateTime();
for ($i = 6; $i >= 0; $i--) {
    $date = (new DateTime())->modify("-$i days")->format('Y-m-d');
    $revenue_labels[] = (new DateTime($date))->format('D');
    
    $found = false;
    foreach ($weekly_revenue_data as $revenue) {
        if ($revenue['date'] == $date) {
            $revenue_values[] = $revenue['revenue'];
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $revenue_values[] = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - <?= $_SESSION['restaurant_name'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --accent-color: #D4AF37;
            --light-bg: #f8f9fa;
        }
        
        .sidebar {
            background: white;
            min-height: calc(100vh - 73px);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .card-dashboard {
            transition: transform 0.3s;
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card-dashboard:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .bg-primary-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-preparing { background-color: #cce5ff; color: #004085; }
        .status-ready { background-color: #d4edda; color: #155724; }
        .status-delivered { background-color: #d1ecf1; color: #0c5460; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- Navigation principale -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-utensils me-2"></i>FoodManager
            </a>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    <i class="fas fa-store me-1"></i> <?= $_SESSION['restaurant_name'] ?>
                </span>
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i> <?= $_SESSION['username'] ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                    </ul>
                </div>
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
                            <a href="payments.php" class="nav-link">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Paiements
                            </a>
                        </li>
                        <li>
                            <a href="stats.php" class="nav-link">
                                <i class="fas fa-chart-line me-2"></i>
                                Statistiques
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Contenu principal -->
            <div class="col-lg-10 col-md-9 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Tableau de bord du restaurant</h2>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-1"></i>Actualiser
                        </button>
                        <a href="menus.php?action=add" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Nouveau menu
                        </a>
                    </div>
                </div>

                <!-- Cartes de statistiques -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card card-dashboard bg-primary-gradient text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Commandes aujourd'hui</h6>
                                        <h2 class="mb-0"><?= $orders_today_count ?></h2>
                                    </div>
                                    <div>
                                        <i class="fas fa-shopping-cart fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card card-dashboard bg-primary-gradient text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Revenu aujourd'hui</h6>
                                        <h2 class="mb-0"><?= number_format($revenue_today_total, 0, ',', ' ') ?> F</h2>
                                    </div>
                                    <div>
                                        <i class="fas fa-money-bill-wave fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card card-dashboard bg-primary-gradient text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Commandes en cours</h6>
                                        <h2 class="mb-0"><?= $orders_processing_count ?></h2>
                                    </div>
                                    <div>
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card card-dashboard bg-primary-gradient text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Notes moyennes</h6>
                                        <h2 class="mb-0"><?= $avg_rating_value ?>/5</h2>
                                    </div>
                                    <div>
                                        <i class="fas fa-star fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Suite du code (graphiques, commandes récentes, etc.) -->
                <!-- ... (le reste du code HTML de l'interface) ... -->
                
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Graphique des revenus
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($revenue_labels) ?>,
                datasets: [{
                    label: 'Revenus (FCFA)',
                    data: <?= json_encode($revenue_values) ?>,
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
                }
            }
        });
    </script>
</body>
</html>