<?php
require_once __DIR__.'/includes/admin_nav.php';
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';


// Vérifier la connexion à la base de données
if (!$db) {
    die("Erreur de connexion à la base de données");
}

// Actions CRUD
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;
$restaurantId = $_GET['restaurant_id'] ?? 0;

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_menu'])) {
        $id = $_POST['id'] ?? 0;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        $restaurantId = (int)($_POST['restaurant_id'] ?? 0);
        
        // Validation des données
        $errors = [];
        if (empty($name)) $errors[] = "Le nom du menu est requis";
        if (empty($description)) $errors[] = "La description est requise";
        if ($price <= 0) $errors[] = "Le prix doit être supérieur à 0";
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode("<br>", $errors);
            header("Location: menus.php?restaurant_id=$restaurantId");
            exit();
        }

        try {
            // Gestion de l'image
            $currentImage = $_POST['current_image'] ?? '';
            $imageUrl = $currentImage;
            
            if (!empty($_FILES['image']['name'])) {
                $imageUrl = handleProductImageUpload('image', $currentImage);
            }
            
            if ($id > 0) {
                // Mise à jour
                $stmt = $db->prepare("UPDATE menus SET name=?, description=?, price=?, category=?, image_url=? WHERE id=?");
                $stmt->execute([$name, $description, $price, $category, $imageUrl, $id]);
                $_SESSION['message'] = "Menu mis à jour avec succès!";
            } else {
                // Création
                $stmt = $db->prepare("INSERT INTO menus (restaurant_id, name, description, price, category, image_url, is_available) VALUES (?, ?, ?, ?, ?, ?, 1)");
                $stmt->execute([$restaurantId, $name, $description, $price, $category, $imageUrl]);
                $_SESSION['message'] = "Menu ajouté avec succès!";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
        }
        
        header("Location: menus.php?restaurant_id=$restaurantId");
        exit();
    }
}

// Suppression
if ($action === 'delete' && $id > 0) {
    try {
        // Récupérer l'image avant suppression
        $stmt = $db->prepare("SELECT image_url FROM menus WHERE id = ?");
        $stmt->execute([$id]);
        $menu = $stmt->fetch();
        
        // Supprimer l'image si elle existe
        if ($menu && $menu['image_url']) {
            $cleanPath = str_replace(IMG_BASE_URL, IMG_BASE_PATH, $menu['image_url']);
            if (file_exists($cleanPath)) {
                unlink($cleanPath);
            }
        }
        
        // Supprimer le menu
        $db->prepare("DELETE FROM menus WHERE id = ?")->execute([$id]);
        
        $_SESSION['message'] = "Menu supprimé avec succès!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
    
    header("Location: menus.php?restaurant_id=$restaurantId");
    exit();
}

// Récupération des données
$restaurants = $db->query("SELECT id, name FROM restaurants ORDER BY name")->fetchAll();

$selectedRestaurant = null;
$menus = [];

if ($restaurantId > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM restaurants WHERE id = ?");
        $stmt->execute([$restaurantId]);
        $selectedRestaurant = $stmt->fetch();
        
        $stmt = $db->prepare("SELECT * FROM menus WHERE restaurant_id = ? ORDER BY category, name");
        $stmt->execute([$restaurantId]);
        $menus = $stmt->fetchAll();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de lecture des données : " . $e->getMessage();
    }
}

// Pour l'édition
$currentMenu = null;
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM menus WHERE id = ?");
        $stmt->execute([$id]);
        $currentMenu = $stmt->fetch();
        
        if ($currentMenu) {
            $restaurantId = $currentMenu['restaurant_id'];
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de lecture du menu : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Menus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .menu-card {
            transition: all 0.3s;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .menu-img {
            height: 120px;
            object-fit: cover;
            width: 100%;
        }
        
        .category-badge {
            font-size: 0.8rem;
            background-color: #e9ecef;
            color: #495057;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 5px;
        }
        
        .price-tag {
            font-weight: bold;
            color: #D4AF37;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Messages de notification -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-list me-2"></i> Gestion des Menus</h2>
            <?php if ($selectedRestaurant): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#menuModal" id="addMenuBtn">
                    <i class="fas fa-plus me-1"></i> Ajouter un menu
                </button>
            <?php endif; ?>
        </div>

        <!-- Sélection du restaurant -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <form method="get">
                    <div class="row align-items-center">
                        <div class="col-md-8 mb-2 mb-md-0">
                            <select name="restaurant_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Sélectionnez un restaurant</option>
                                <?php foreach ($restaurants as $resto): ?>
                                <option value="<?= $resto['id'] ?>" <?= $restaurantId == $resto['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($resto['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($selectedRestaurant): ?>
                        <div class="col-md-4">
                            <a href="restaurants.php?action=edit&id=<?= $selectedRestaurant['id'] ?>" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-edit me-1"></i> Modifier le restaurant
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des menus -->
        <?php if ($selectedRestaurant): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Menus de <?= htmlspecialchars($selectedRestaurant['name']) ?></h4>
                <span class="badge bg-primary"><?= count($menus) ?> menu(s)</span>
            </div>
            
            <?php if (!empty($menus)): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($menus as $menu): ?>
                    <div class="col">
                        <div class="card menu-card h-100">
                            <?php 
                            $imageUrl = $menu['image_url'] ?? '';
                            if (!empty($imageUrl)) {
                                echo '<img src="'.htmlspecialchars($imageUrl).'" 
                                     class="menu-img card-img-top" 
                                     alt="'.htmlspecialchars($menu['name']).'"
                                     onerror="this.onerror=null;this.src=\''.getProductImage(0).'\'">';
                            } else {
                                echo '<div class="menu-img bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-utensils fa-3x text-muted"></i>
                                      </div>';
                            }
                            ?>
                            
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title mb-1"><?= htmlspecialchars($menu['name']) ?></h5>
                                    <span class="price-tag"><?= number_format($menu['price'], 2) ?> CFA</span>
                                </div>
                                
                                <?php if ($menu['category']): ?>
                                    <span class="badge category-badge mb-2"><?= htmlspecialchars($menu['category']) ?></span>
                                <?php endif; ?>
                                
                                <p class="card-text text-muted small"><?= htmlspecialchars(substr($menu['description'], 0, 100)) ?>...</p>
                            </div>
                            
                            <div class="card-footer bg-white border-top-0">
                                <div class="d-flex justify-content-end gap-2">
                                    <button class="btn btn-sm btn-warning edit-menu-btn"
                                            data-id="<?= $menu['id'] ?>"
                                            data-name="<?= htmlspecialchars($menu['name']) ?>"
                                            data-description="<?= htmlspecialchars($menu['description']) ?>"
                                            data-price="<?= $menu['price'] ?>"
                                            data-category="<?= htmlspecialchars($menu['category']) ?>"
                                            data-image="<?= $menu['image_url'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?action=delete&id=<?= $menu['id'] ?>&restaurant_id=<?= $restaurantId ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce menu ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    Aucun menu disponible pour ce restaurant.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-warning">
                Veuillez sélectionner un restaurant pour gérer ses menus.
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal pour ajout/édition de menu -->
    <div class="modal fade" id="menuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="menuId" value="">
                    <input type="hidden" name="current_image" id="currentImage" value="">
                    <input type="hidden" name="restaurant_id" value="<?= $restaurantId ?>">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Ajouter un menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom du menu</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Prix (€)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Catégorie</label>
                                <input type="text" class="form-control" id="category" name="category">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Image du menu</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <img src="" class="preview-image mt-2 d-none" id="imagePreview">
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="save_menu" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Gestion de la modal d'édition
            $('.edit-menu-btn').click(function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const description = $(this).data('description');
                const price = $(this).data('price');
                const category = $(this).data('category');
                const image = $(this).data('image');
                
                $('#modalTitle').text('Modifier le menu');
                $('#menuId').val(id);
                $('#name').val(name);
                $('#description').val(description);
                $('#price').val(price);
                $('#category').val(category);
                
                if (image) {
                    $('#currentImage').val(image);
                    $('#imagePreview').attr('src', image).removeClass('d-none');
                }
                
                const modal = new bootstrap.Modal(document.getElementById('menuModal'));
                modal.show();
            });
            
            // Prévisualisation de l'image
            $('#image').change(function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        $('#imagePreview').attr('src', event.target.result).removeClass('d-none');
                    }
                    reader.readAsDataURL(file);
                }
            });
            
            // Réinitialisation de la modal quand elle se ferme
            $('#menuModal').on('hidden.bs.modal', function () {
                $('#modalTitle').text('Ajouter un menu');
                $('#menuId').val('');
                $('#currentImage').val('');
                $('#imagePreview').attr('src', '').addClass('d-none');
                $(this).find('form')[0].reset();
            });
            
            // Réinitialisation complète quand on clique sur le bouton Ajouter
            $('#addMenuBtn').click(function() {
                $('#menuModal').find('form')[0].reset();
                $('#menuId').val('');
                $('#currentImage').val('');
                $('#imagePreview').attr('src', '').addClass('d-none');
                $('#modalTitle').text('Ajouter un menu');
            });
        });
    </script>
</body>
</html>