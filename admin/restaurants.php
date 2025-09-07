<?php
require_once __DIR__.'/includes/admin_nav.php';
require_once __DIR__.'/../includes/functions.php';


// Activer le rapport d'erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier la connexion à la base de données
if (!$db) {
    die("Erreur de connexion à la base de données");
}

// Initialiser la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Actions CRUD
$action = isset($_GET['action']) ? htmlspecialchars($_GET['action']) : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_restaurant'])) {
        // Récupération et nettoyage des données
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = isset($_POST['name']) ? trim(htmlspecialchars($_POST['name'])) : '';
        $description = isset($_POST['description']) ? trim(htmlspecialchars($_POST['description'])) : '';
        $address = isset($_POST['address']) ? trim(htmlspecialchars($_POST['address'])) : '';
        $phone = isset($_POST['phone']) ? trim(htmlspecialchars($_POST['phone'])) : '';
        
        // Validation des données
        $errors = [];
        if (empty($name)) $errors[] = "Le nom du restaurant est requis";
        if (empty($description)) $errors[] = "La description est requise";
        if (empty($address)) $errors[] = "L'adresse est requise";
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode("<br>", $errors);
            header("Location: restaurants.php");
            exit();
        }

        try {
            // Gestion de l'image
            $currentImage = isset($_POST['current_image']) ? htmlspecialchars($_POST['current_image']) : null;
            $imageUrl = $currentImage;
            
            if (!empty($_FILES['image']['name'])) {
                $imageUrl = handleRestaurantImageUpload('image', $currentImage);
            }
            
            if ($id > 0) {
                // Mise à jour du restaurant existant
                $stmt = $db->prepare("UPDATE restaurants SET name=?, description=?, address=?, phone=?, image_url=? WHERE id=?");
                $stmt->execute([$name, $description, $address, $phone, $imageUrl, $id]);
                $_SESSION['message'] = "Restaurant mis à jour avec succès!";
            } else {
                // Création d'un nouveau restaurant
                $db->beginTransaction();
                
                // Création du compte utilisateur
                $username = strtolower(str_replace(' ', '', $name));
                $email = $username . '@restaurant.com';
                $password = password_hash('temp_password', PASSWORD_DEFAULT);
                
                $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'restaurateur')");
                $stmt->execute([$username, $email, $password]);
                $user_id = $db->lastInsertId();
                
                // Création du restaurant
                $stmt = $db->prepare("INSERT INTO restaurants (user_id, name, description, address, phone, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $name, $description, $address, $phone, $imageUrl]);
                
                $db->commit();
                $_SESSION['message'] = "Restaurant et compte restaurateur créés avec succès!";
            }
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
            error_log("Erreur gestion restaurant: " . $e->getMessage());
        }
        
        header("Location: restaurants.php");
        exit();
    }
}

// Suppression d'un restaurant
if ($action === 'delete' && $id > 0) {
    try {
        $db->beginTransaction();
        
        // 1. Récupérer les infos du restaurant
        $stmt = $db->prepare("SELECT user_id, image_url FROM restaurants WHERE id = ?");
        $stmt->execute([$id]);
        $restaurant = $stmt->fetch();
        
        if ($restaurant) {
            // 2. Supprimer l'image si elle existe
            if ($restaurant['image_url']) {
                $imagePath = $_SERVER['DOCUMENT_ROOT'] . $restaurant['image_url'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // 3. Supprimer le restaurant
            $db->prepare("DELETE FROM restaurants WHERE id = ?")->execute([$id]);
            
            // 4. Supprimer l'utilisateur associé
            $db->prepare("DELETE FROM users WHERE id = ?")->execute([$restaurant['user_id']]);
            
            $db->commit();
            $_SESSION['message'] = "Restaurant supprimé avec succès!";
        }
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
    
    header("Location: restaurants.php");
    exit();
}

// Récupération des données
$restaurants = $db->query("SELECT * FROM restaurants ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Restaurants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .resto-card {
            transition: all 0.3s;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .resto-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .resto-img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        
        .action-btns .btn {
            width: 40px;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Messages de notification -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-utensils me-2"></i> Gestion des Restaurants</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#restaurantModal" id="addRestaurantBtn">
                <i class="fas fa-plus me-1"></i> Ajouter
            </button>
        </div>

        <!-- Liste des restaurants avec DataTables -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="restaurantsTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Adresse</th>
                                <th>Téléphone</th>
                                <th>Créé le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
<?php foreach ($restaurants as $resto): ?>
<tr>
    <td>
        <?php 
        $imageUrl = $resto['image_url'] ?? '';
        if (!empty($imageUrl)) {
            // Vérifier si l'image existe physiquement
            $cleanPath = str_replace(IMG_BASE_URL, IMG_BASE_PATH, $imageUrl);
            if (file_exists($cleanPath)) {
        ?>
            <img src="<?= htmlspecialchars($imageUrl) ?>" 
                 class="rounded" 
                 style="width:50px; height:50px; object-fit:cover"
                 alt="<?= htmlspecialchars($resto['name']) ?>">
        <?php } else { ?>
            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                 style="width:50px; height:50px;">
                <i class="fas fa-utensils text-muted"></i>
            </div>
        <?php } 
        } else { ?>
            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                 style="width:50px; height:50px;">
                <i class="fas fa-utensils text-muted"></i>
            </div>
        <?php } ?>
    </td>
    <td><?= htmlspecialchars($resto['name']) ?></td>
    <td><?= htmlspecialchars(substr($resto['description'], 0, 50)) ?>...</td>
    <td><?= htmlspecialchars($resto['address']) ?></td>
    <td><?= htmlspecialchars($resto['phone']) ?></td>
    <td><?= date('d/m/Y', strtotime($resto['created_at'])) ?></td>
    <td class="action-btns">
        <a href="menus.php?restaurant_id=<?= $resto['id'] ?>" class="btn btn-sm btn-info" title="Voir les menus">
            <i class="fas fa-list"></i>
        </a>
        <button class="btn btn-sm btn-warning edit-btn" 
                data-id="<?= $resto['id'] ?>" 
                data-name="<?= htmlspecialchars($resto['name']) ?>"
                data-description="<?= htmlspecialchars($resto['description']) ?>"
                data-address="<?= htmlspecialchars($resto['address']) ?>"
                data-phone="<?= htmlspecialchars($resto['phone']) ?>"
                data-image="<?= $resto['image_url'] ?>"
                title="Modifier">
            <i class="fas fa-edit"></i>
        </button>
        <a href="?action=delete&id=<?= $resto['id'] ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr ? Cette action supprimera aussi tous les menus associés.')">
            <i class="fas fa-trash"></i>
        </a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour ajout/édition -->
    <div class="modal fade" id="restaurantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="restaurantId" value="">
                    <input type="hidden" name="current_image" id="currentImage" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Ajouter un restaurant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom du restaurant</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Adresse</label>
                                <input type="text" class="form-control" id="address" name="address" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Image du restaurant</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <img src="" class="preview-image mt-2 d-none" id="imagePreview">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="save_restaurant" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Initialisation de DataTable
        $(document).ready(function() {
            $('#restaurantsTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
                },
                responsive: true
            });
            
            // Gestion de la modal d'édition
            $('.edit-btn').click(function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const description = $(this).data('description');
                const address = $(this).data('address');
                const phone = $(this).data('phone');
                const image = $(this).data('image');
                
                $('#modalTitle').text('Modifier le restaurant');
                $('#restaurantId').val(id);
                $('#name').val(name);
                $('#description').val(description);
                $('#address').val(address);
                $('#phone').val(phone);
                
                if (image) {
                    $('#currentImage').val(image);
                    $('#imagePreview').attr('src', image).removeClass('d-none');
                }
                
                const modal = new bootstrap.Modal(document.getElementById('restaurantModal'));
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
            $('#restaurantModal').on('hidden.bs.modal', function () {
                $('#modalTitle').text('Ajouter un restaurant');
                $('#restaurantId').val('');
                $('#currentImage').val('');
                $('#imagePreview').attr('src', '').addClass('d-none');
                $(this).find('form')[0].reset();
            });
            
            // Réinitialisation complète quand on clique sur le bouton Ajouter
            $('#addRestaurantBtn').click(function() {
                $('#restaurantModal').find('form')[0].reset();
                $('#restaurantId').val('');
                $('#currentImage').val('');
                $('#imagePreview').attr('src', '').addClass('d-none');
                $('#modalTitle').text('Ajouter un restaurant');
            });
        });
    </script>
</body>
</html>