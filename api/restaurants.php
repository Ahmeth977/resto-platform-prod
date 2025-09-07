<?php
header('Content-Type: application/json');
require_once __DIR__.'/../../includes/config.php';
require_once __DIR__.'/../../includes/functions.php';

$search = $_GET['search'] ?? '';
$cuisine = $_GET['cuisine'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$delivery = $_GET['delivery'] ?? '';

try {
    $query = "SELECT 
        r.id, r.name, r.description, r.address, r.phone, 
        r.image_url, r.logo_url, r.lat, r.lng,
        COUNT(m.id) as menu_count,
        (SELECT AVG(rating) FROM reviews WHERE restaurant_id = r.id) as avg_rating
    FROM restaurants r
    LEFT JOIN menus m ON r.id = m.restaurant_id AND m.is_available = 1";
    
    $where = [];
    $params = [];
    
    if (!empty($search)) {
        $where[] = "(r.name LIKE ? OR r.description LIKE ? OR m.name LIKE ? OR m.description LIKE ?)";
        array_push($params, "%$search%", "%$search%", "%$search%", "%$search%");
    }
    
    if (!empty($cuisine)) {
        $where[] = "m.category = ?";
        $params[] = $cuisine;
    }
    
    if (!empty($delivery)) {
        $where[] = "r.has_{$delivery} = 1";
    }
    
    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }
    
    $query .= " GROUP BY r.id";
    
    switch ($sort) {
        case 'rating':
            $query .= " ORDER BY avg_rating DESC";
            break;
        case 'price_asc':
            $query .= " ORDER BY (SELECT MIN(price) FROM menus WHERE restaurant_id = r.id) ASC";
            break;
        case 'price_desc':
            $query .= " ORDER BY (SELECT MAX(price) FROM menus WHERE restaurant_id = r.id) DESC";
            break;
        default:
            $query .= " ORDER BY r.created_at DESC";
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $restaurants = $stmt->fetchAll();
    
    // Générer le HTML
    ob_start();
    foreach ($restaurants as $resto): ?>
        <div class="col-md-6 col-lg-6 col-xl-4">
            <article class="resto-card h-100 shadow-sm" aria-labelledby="resto-<?= $resto['id'] ?>-title">
                <div class="resto-media" aria-hidden="true">
                    <img src="<?= getRestaurantImage($resto['id'], $resto['image_url']) ?>" 
                         class="resto-img" 
                         alt=""
                         loading="lazy">
                    
                    <?php if($resto['avg_rating']): ?>
                        <div class="rating-badge">
                            <i class="fas fa-star text-warning me-1"></i>
                            <?= number_format($resto['avg_rating'], 1) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="resto-body">
                    <h2 id="resto-<?= $resto['id'] ?>-title" class="resto-title">
                        <?= safeOutput($resto['name']) ?>
                    </h2>
                    <p class="resto-description"><?= safeOutput($resto['description']) ?></p>
                    
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <span class="badge bg-secondary rounded-pill">
                            <i class="fas fa-book-open me-1"></i>
                            <?= (int)$resto['menu_count'] ?> menus
                        </span>
                        <a href="restaurant.php?id=<?= $resto['id'] ?>" class="btn btn-primary btn-sm">
                            Voir détails <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </article>
        </div>
    <?php endforeach;
    
    $html = ob_get_clean();
    
    echo json_encode([
        'success' => true,
        'restaurants' => $restaurants,
        'html' => $html
    ]);
    
} catch (PDOException $e) {
    error_log("API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue lors du chargement des restaurants'
    ]);
}