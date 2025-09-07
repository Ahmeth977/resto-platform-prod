<?php
require_once __DIR__.'/includes/config.php';

// Vérifier si l'ID du restaurant est présent et valide
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$restaurantId = (int)$_GET['id'];

try {
    // Récupérer les informations du restaurant
    $stmt = $db->prepare("SELECT * FROM restaurants WHERE id = ?");
    $stmt->execute([$restaurantId]);
    $restaurant = $stmt->fetch();

    // Si le restaurant n'existe pas, rediriger vers l'accueil
    if (!$restaurant) {
        header("Location: index.php");
        exit();
    }

    // Récupérer les menus du restaurant
    $stmt = $db->prepare("SELECT * FROM menus WHERE restaurant_id = ?");
    $stmt->execute([$restaurantId]);
    $menus = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($restaurant['name']) ?> | RestoPlatform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/nav.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero-section {
            height: 300px;
            background-size: cover;
            background-position: center;
            position: relative;
            margin-bottom: 2rem;
        }
        .hero-overlay {
            background: rgba(0,0,0,0.5);
            height: 100%;
            display: flex;
            align-items: center;
        }
        .menu-item {
            border-left: 3px solid #0d6efd;
        }
    </style>
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <div class="hero-section" style="background-image: url('<?= !empty($restaurant['image_url']) ? htmlspecialchars($restaurant['image_url']) : 'assets/img/placeholders/placeholder'.($restaurant['id']%3+1).'.jpg' ?>');">
        <div class="hero-overlay">
            <div class="container text-white">
                <h1 class="display-4"><?= htmlspecialchars($restaurant['name']) ?></h1>
                <p class="lead"><?= htmlspecialchars($restaurant['short_description'] ?? '') ?></p>
            </div>
        </div>
    </div>

    <main class="container mb-5">
        <div class="row">
            <div class="col-md-8">
                <h2>À propos</h2>
                <p><?= nl2br(htmlspecialchars($restaurant['description'])) ?></p>
                
                <h2 class="mt-4">Menus</h2>
                
                <?php if (empty($menus)): ?>
                    <div class="alert alert-info">
                        Ce restaurant n'a pas encore de menus disponibles.
                    </div>
                <?php else: ?>
                    <?php
                    // Grouper les menus par catégorie
                    $categories = [];
                    foreach ($menus as $menu) {
                        $category = $menu['category'] ?? 'Autres';
                        if (!isset($categories[$category])) {
                            $categories[$category] = [];
                        }
                        $categories[$category][] = $menu;
                    }
                    ?>
                    
                    <?php foreach ($categories as $category => $items): ?>
                        <h3 class="text-primary mt-4"><?= htmlspecialchars($category) ?></h3>
                        <div class="list-group mb-3">
                            <?php foreach ($items as $menu): ?>
                                <div class="list-group-item menu-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?= htmlspecialchars($menu['name']) ?></h5>
                                        <strong><?= number_format($menu['price'], 2) ?> €</strong>
                                    </div>
                                    <p class="mb-1"><?= htmlspecialchars($menu['description']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Informations</h5>
                        
                        <p>
                            <i class="fas fa-map-marker-alt text-primary"></i> 
                            <strong>Adresse :</strong><br>
                            <?= nl2br(htmlspecialchars($restaurant['address'])) ?>
                        </p>
                        
                        <?php if (!empty($restaurant['phone'])): ?>
                            <p>
                                <i class="fas fa-phone text-primary"></i> 
                                <strong>Téléphone :</strong><br>
                                <?= htmlspecialchars($restaurant['phone']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2 mt-3">
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left"></i> Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>