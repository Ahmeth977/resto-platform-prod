<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Images - FoodManager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --accent-color: #D4AF37;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 500;
        }
        
        .image-preview {
            width: 100%;
            height: 200px;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 15px;
            background-color: #f8f9fa;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .image-preview .placeholder {
            color: #6c757d;
            text-align: center;
            padding: 20px;
        }
        
        .image-preview .placeholder i {
            font-size: 3rem;
            margin-bottom: 10px;
            display: block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
        }
        
        .upload-area {
            border: 2px dashed #6a11cb;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background-color: rgba(106, 17, 203, 0.05);
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .upload-area:hover {
            background-color: rgba(106, 17, 203, 0.1);
        }
        
        .upload-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
        }
        
        .gallery-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        
        .gallery-item-actions {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            padding: 5px;
            display: flex;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .gallery-item:hover .gallery-item-actions {
            opacity: 1;
        }
        
        .gallery-item-actions .btn {
            padding: 0.25rem 0.5rem;
            margin: 0 2px;
        }
        
        .progress {
            height: 8px;
            margin-top: 10px;
        }
        
        .drag-over {
            background-color: rgba(106, 17, 203, 0.2);
            border-color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-utensils me-2"></i>FoodManager
            </a>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    <i class="fas fa-store me-1"></i> Restaurant Le Gourmet
                </span>
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i> Jean Dupont
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Navigation</h5>
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-utensils me-2"></i>Menus
                            </a>
                            <a href="#" class="list-group-item list-group-item-action active">
                                <i class="fas fa-images me-2"></i>Images
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-shopping-cart me-2"></i>Commandes
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-chart-line me-2"></i>Statistiques
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-cog me-2"></i>Paramètres
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-images me-2"></i>Gestion des Images</h2>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary" id="refreshBtn">
                            <i class="fas fa-sync-alt me-1"></i>Actualiser
                        </button>
                    </div>
                </div>
                
                <!-- Logo du restaurant -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-store me-2"></i>Logo du Restaurant</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="image-preview" id="logoPreview">
                                    <div class="placeholder">
                                        <i class="fas fa-camera"></i>
                                        <p>Aperçu du logo</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <form action="upload_restaurant_logo.php" method="post" enctype="multipart/form-data" id="logoForm">
                                    <div class="mb-3">
                                        <label for="logo" class="form-label">Logo du restaurant</label>
                                        <div class="upload-area" id="logoUploadArea">
                                            <div class="upload-icon">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <p>Glissez-déposez votre image ici ou</p>
                                            <input type="file" class="form-control d-none" id="logo" name="logo" accept="image/jpeg,image/png,image/webp">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('logo').click()">
                                                Parcourir les fichiers
                                            </button>
                                            <div class="form-text mt-2">Format carré recommandé (300x300px). Formats acceptés: JPG, PNG, WEBP.</div>
                                        </div>
                                        <div class="progress d-none" id="logoProgress">
                                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <div class="alert alert-danger mt-2 d-none" id="logoError"></div>
                                        <div class="alert alert-success mt-2 d-none" id="logoSuccess"></div>
                                    </div>
                                    <button type="submit" class="btn btn-primary" id="logoSubmitBtn">
                                        <i class="fas fa-upload me-1"></i>Uploader le logo
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Images des produits -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-hamburger me-2"></i>Images des Produits</h5>
                        <div class="form-group">
                            <select class="form-select form-select-sm" id="productSelector">
                                <option value="">Sélectionnez un produit</option>
                                <option value="1">Pizza Margherita</option>
                                <option value="2">Burger Classic</option>
                                <option value="3">Salade César</option>
                                <option value="4">Poulet Braisé</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="noProductSelected" class="text-center py-4">
                            <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Veuillez sélectionner un produit pour gérer ses images</h5>
                        </div>
                        
                        <div id="productImageSection" class="d-none">
                            <div class="row mb-4">
                                <div class="col-md-5">
                                    <div class="image-preview" id="productImagePreview">
                                        <div class="placeholder">
                                            <i class="fas fa-camera"></i>
                                            <p>Aperçu de l'image</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <form action="upload_product_image.php" method="post" enctype="multipart/form-data" id="productImageForm">
                                        <input type="hidden" name="product_id" id="productId" value="">
                                        <div class="mb-3">
                                            <label for="productImage" class="form-label">Image du produit</label>
                                            <div class="upload-area" id="productUploadArea">
                                                <div class="upload-icon">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </div>
                                                <p>Glissez-déposez votre image ici ou</p>
                                                <input type="file" class="form-control d-none" id="productImage" name="image" accept="image/jpeg,image/png">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('productImage').click()">
                                                    Parcourir les fichiers
                                                </button>
                                                <div class="form-text mt-2">Format paysage recommandé. Formats acceptés: JPG, PNG.</div>
                                            </div>
                                            <div class="progress d-none" id="productProgress">
                                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                            </div>
                                            <div class="alert alert-danger mt-2 d-none" id="productError"></div>
                                            <div class="alert alert-success mt-2 d-none" id="productSuccess"></div>
                                        </div>
                                        <button type="submit" class="btn btn-primary" id="productSubmitBtn">
                                            <i class="fas fa-upload me-1"></i>Uploader l'image
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <h5>Galerie d'images</h5>
                            <div class="gallery" id="productGallery">
                                <!-- Les images seront ajoutées dynamiquement ici -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cette image ? Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Supprimer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Références aux éléments DOM
            const logoInput = document.getElementById('logo');
            const logoPreview = document.getElementById('logoPreview');
            const logoForm = document.getElementById('logoForm');
            const logoProgress = document.getElementById('logoProgress');
            const logoError = document.getElementById('logoError');
            const logoSuccess = document.getElementById('logoSuccess');
            const logoSubmitBtn = document.getElementById('logoSubmitBtn');
            const logoUploadArea = document.getElementById('logoUploadArea');
            
            const productSelector = document.getElementById('productSelector');
            const noProductSelected = document.getElementById('noProductSelected');
            const productImageSection = document.getElementById('productImageSection');
            const productImageInput = document.getElementById('productImage');
            const productImagePreview = document.getElementById('productImagePreview');
            const productImageForm = document.getElementById('productImageForm');
            const productIdInput = document.getElementById('productId');
            const productProgress = document.getElementById('productProgress');
            const productError = document.getElementById('productError');
            const productSuccess = document.getElementById('productSuccess');
            const productSubmitBtn = document.getElementById('productSubmitBtn');
            const productUploadArea = document.getElementById('productUploadArea');
            const productGallery = document.getElementById('productGallery');
            const refreshBtn = document.getElementById('refreshBtn');
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            let imageToDelete = null;
            
            // Charger le logo existant
            loadExistingLogo();
            
            // Gestion du logo
            logoInput.addEventListener('change', function(e) {
                handleImagePreview(this, logoPreview);
            });
            
            // Drag and drop pour le logo
            setupDragAndDrop(logoUploadArea, logoInput, logoPreview);
            
            // Soumission du formulaire logo
            logoForm.addEventListener('submit', function(e) {
                e.preventDefault();
                uploadImage(this, logoProgress, logoError, logoSuccess, logoSubmitBtn, function(response) {
                    if (response.success) {
                        loadExistingLogo();
                    }
                });
            });
            
            // Sélection du produit
            productSelector.addEventListener('change', function() {
                const productId = this.value;
                if (productId) {
                    noProductSelected.classList.add('d-none');
                    productImageSection.classList.remove('d-none');
                    productIdInput.value = productId;
                    loadProductImages(productId);
                } else {
                    noProductSelected.classList.remove('d-none');
                    productImageSection.classList.add('d-none');
                }
            });
            
            // Gestion de l'image produit
            productImageInput.addEventListener('change', function(e) {
                handleImagePreview(this, productImagePreview);
            });
            
            // Drag and drop pour l'image produit
            setupDragAndDrop(productUploadArea, productImageInput, productImagePreview);
            
            // Soumission du formulaire image produit
            productImageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                uploadImage(this, productProgress, productError, productSuccess, productSubmitBtn, function(response) {
                    if (response.success) {
                        const productId = productSelector.value;
                        loadProductImages(productId);
                    }
                });
            });
            
            // Bouton actualiser
            refreshBtn.addEventListener('click', function() {
                loadExistingLogo();
                const productId = productSelector.value;
                if (productId) {
                    loadProductImages(productId);
                }
            });
            
            // Confirmation de suppression
            document.getElementById('confirmDelete').addEventListener('click', function() {
                if (imageToDelete) {
                    deleteImage(imageToDelete);
                    deleteModal.hide();
                }
            });
            
            // Fonctions utilitaires
            function loadExistingLogo() {
                // Simuler le chargement d'un logo existant
                // En réalité, vous feriez une requête AJAX pour récupérer le logo
                setTimeout(() => {
                    logoPreview.innerHTML = '<img src="https://via.placeholder.com/300x300/e9ecef/6c757d?text=Logo+Restaurant" alt="Logo du restaurant">';
                }, 500);
            }
            
            function loadProductImages(productId) {
                // Simuler le chargement des images d'un produit
                productGallery.innerHTML = '';
                
                // Images factices pour la démonstration
                const images = [
                    { id: 1, url: 'https://via.placeholder.com/300x200/e9ecef/6c757d?text=Image+1' },
                    { id: 2, url: 'https://via.placeholder.com/300x200/e9ecef/6c757d?text=Image+2' },
                    { id: 3, url: 'https://via.placeholder.com/300x200/e9ecef/6c757d?text=Image+3' }
                ];
                
                images.forEach(image => {
                    const galleryItem = document.createElement('div');
                    galleryItem.className = 'gallery-item';
                    galleryItem.innerHTML = `
                        <img src="${image.url}" alt="Image produit">
                        <div class="gallery-item-actions">
                            <button class="btn btn-sm btn-info" onclick="viewImage('${image.url}')">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="requestDelete(${image.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                    productGallery.appendChild(galleryItem);
                });
            }
            
            function handleImagePreview(input, previewElement) {
                const file = input.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewElement.innerHTML = `<img src="${e.target.result}" alt="Aperçu">`;
                    };
                    reader.readAsDataURL(file);
                }
            }
            
            function setupDragAndDrop(dropArea, inputElement, previewElement) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropArea.addEventListener(eventName, preventDefaults, false);
                });
                
                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropArea.addEventListener(eventName, highlight, false);
                });
                
                ['dragleave', 'drop'].forEach(eventName => {
                    dropArea.addEventListener(eventName, unhighlight, false);
                });
                
                function highlight() {
                    dropArea.classList.add('drag-over');
                }
                
                function unhighlight() {
                    dropArea.classList.remove('drag-over');
                }
                
                dropArea.addEventListener('drop', function(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    inputElement.files = files;
                    handleImagePreview(inputElement, previewElement);
                }, false);
            }
            
            function uploadImage(form, progressElement, errorElement, successElement, submitBtn, callback) {
                const formData = new FormData(form);
                const xhr = new XMLHttpRequest();
                
                // Afficher la barre de progression
                progressElement.classList.remove('d-none');
                errorElement.classList.add('d-none');
                successElement.classList.add('d-none');
                submitBtn.disabled = true;
                
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        progressElement.querySelector('.progress-bar').style.width = percentComplete + '%';
                    }
                });
                
                xhr.addEventListener('load', function() {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            successElement.textContent = response.message;
                            successElement.classList.remove('d-none');
                            form.reset();
                            const previewElement = form.id === 'logoForm' ? logoPreview : productImagePreview;
                            previewElement.innerHTML = `
                                <div class="placeholder">
                                    <i class="fas fa-camera"></i>
                                    <p>Aperçu</p>
                                </div>
                            `;
                        } else {
                            errorElement.textContent = response.message;
                            errorElement.classList.remove('d-none');
                        }
                    } else {
                        errorElement.textContent = 'Une erreur est survenue lors de l\'upload.';
                        errorElement.classList.remove('d-none');
                    }
                    
                    progressElement.classList.add('d-none');
                    submitBtn.disabled = false;
                    
                    if (callback) {
                        callback(JSON.parse(xhr.responseText));
                    }
                });
                
                xhr.addEventListener('error', function() {
                    errorElement.textContent = 'Une erreur est survenue lors de l\'upload.';
                    errorElement.classList.remove('d-none');
                    progressElement.classList.add('d-none');
                    submitBtn.disabled = false;
                });
                
                xhr.open('POST', form.action, true);
                xhr.send(formData);
            }
            
            window.requestDelete = function(imageId) {
                imageToDelete = imageId;
                deleteModal.show();
            };
            
            function deleteImage(imageId) {
                // Simuler la suppression d'une image
                console.log('Suppression de l\'image avec ID:', imageId);
                
                // En réalité, vous feriez une requête AJAX pour supprimer l'image
                const productId = productSelector.value;
                if (productId) {
                    loadProductImages(productId);
                }
                
                // Afficher un message de succès
                productSuccess.textContent = 'Image supprimée avec succès.';
                productSuccess.classList.remove('d-none');
                setTimeout(() => {
                    productSuccess.classList.add('d-none');
                }, 3000);
            }
            
            window.viewImage = function(imageUrl) {
                window.open(imageUrl, '_blank');
            };
        });
    </script>
</body>
</html>