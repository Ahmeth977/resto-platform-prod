<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Gestionnaire - FoodManager</title>
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
            background: linear-gradient(135deg, var(--light-bg) 0%, #e9ecef 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .registration-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .registration-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .registration-body {
            padding: 2rem;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #dee2e6;
            z-index: 1;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
            transition: all 0.3s;
        }
        
        .step.active .step-number {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }
        
        .step.completed .step-number {
            background-color: var(--accent-color);
            color: white;
        }
        
        .step-label {
            font-size: 0.9rem;
            color: #6c757d;
            text-align: center;
        }
        
        .step.active .step-label {
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .form-section {
            display: none;
        }
        
        .form-section.active {
            display: block;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        
        .form-card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 1.5rem;
            font-weight: 500;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .form-card-body {
            padding: 1.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
        }
        
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 42px;
            color: #6c757d;
        }
        
        .restaurant-logo-preview {
            width: 120px;
            height: 120px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px dashed #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            overflow: hidden;
        }
        
        .restaurant-logo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="registration-header">
            <h2><i class="fas fa-user-plus me-2"></i>Inscription Gestionnaire de Restaurant</h2>
            <p class="mb-0">Créez un nouveau compte gestionnaire pour votre restaurant</p>
        </div>
        
        <div class="registration-body">
            <!-- Indicateur d'étapes -->
            <div class="step-indicator">
                <div class="step active" id="step1">
                    <div class="step-number">1</div>
                    <div class="step-label">Informations<br>personnelles</div>
                </div>
                <div class="step" id="step2">
                    <div class="step-number">2</div>
                    <div class="step-label">Informations<br>du restaurant</div>
                </div>
                <div class="step" id="step3">
                    <div class="step-number">3</div>
                    <div class="step-label">Confirmation</div>
                </div>
            </div>
            
            <!-- Formulaire d'inscription -->
            <form id="registrationForm" method="POST" enctype="multipart/form-data">
                <!-- Étape 1: Informations personnelles -->
                <div class="form-section active" id="section1">
                    <div class="form-card">
                        <div class="form-card-header">
                            <i class="fas fa-user me-2"></i>Informations personnelles
                        </div>
                        <div class="form-card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstName" class="form-label">Prénom</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastName" class="form-label">Nom</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="form-text">Cette adresse servira d'identifiant de connexion</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="position-relative">
                                    <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                    <span class="password-toggle" id="passwordToggle">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                <div class="form-text">Le mot de passe doit contenir au moins 6 caractères</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
                                <div class="position-relative">
                                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                                    <span class="password-toggle" id="confirmPasswordToggle">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                <div class="invalid-feedback" id="passwordError">Les mots de passe ne correspondent pas</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" onclick="nextStep(2)">Suivant <i class="fas fa-arrow-right ms-1"></i></button>
                    </div>
                </div>
                
                <!-- Étape 2: Informations du restaurant -->
                <div class="form-section" id="section2">
                    <div class="form-card">
                        <div class="form-card-header">
                            <i class="fas fa-utensils me-2"></i>Informations du restaurant
                        </div>
                        <div class="form-card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="restaurantName" class="form-label">Nom du restaurant</label>
                                    <input type="text" class="form-control" id="restaurantName" name="restaurantName" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="restaurantPhone" class="form-label">Téléphone du restaurant</label>
                                    <input type="tel" class="form-control" id="restaurantPhone" name="restaurantPhone">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="restaurantDescription" class="form-label">Description du restaurant</label>
                                <textarea class="form-control" id="restaurantDescription" name="restaurantDescription" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="restaurantAddress" class="form-label">Adresse du restaurant</label>
                                <input type="text" class="form-control" id="restaurantAddress" name="restaurantAddress" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="hasDelivery" class="form-label">Service de livraison</label>
                                    <select class="form-select" id="hasDelivery" name="hasDelivery">
                                        <option value="1">Disponible</option>
                                        <option value="0">Non disponible</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="hasPickup" class="form-label">Service à emporter</label>
                                    <select class="form-select" id="hasPickup" name="hasPickup">
                                        <option value="1">Disponible</option>
                                        <option value="0">Non disponible</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="restaurantLogo" class="form-label">Logo du restaurant</label>
                                <input type="file" class="form-control" id="restaurantLogo" name="restaurantLogo" accept="image/*">
                                <div class="form-text">Format recommandé: JPG, PNG ou SVG. Taille max: 2MB</div>
                                
                                <div class="mt-3">
                                    <div class="restaurant-logo-preview" id="logoPreview">
                                        <span id="logoPreviewText"><i class="fas fa-image me-1"></i> Aperçu</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="prevStep(1)"><i class="fas fa-arrow-left me-1"></i> Précédent</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep(3)">Suivant <i class="fas fa-arrow-right ms-1"></i></button>
                    </div>
                </div>
                
                <!-- Étape 3: Confirmation -->
                <div class="form-section" id="section3">
                    <div class="form-card">
                        <div class="form-card-header">
                            <i class="fas fa-check-circle me-2"></i>Confirmation
                        </div>
                        <div class="form-card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>Veuillez vérifier les informations ci-dessous avant de finaliser l'inscription.
                            </div>
                            
                            <h5 class="mb-3">Informations personnelles</h5>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p><strong>Prénom:</strong> <span id="confirmFirstName"></span></p>
                                    <p><strong>Nom:</strong> <span id="confirmLastName"></span></p>
                                    <p><strong>Email:</strong> <span id="confirmEmail"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Téléphone:</strong> <span id="confirmPhone"></span></p>
                                </div>
                            </div>
                            
                            <h5 class="mb-3">Informations du restaurant</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nom du restaurant:</strong> <span id="confirmRestaurantName"></span></p>
                                    <p><strong>Adresse:</strong> <span id="confirmRestaurantAddress"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Téléphone:</strong> <span id="confirmRestaurantPhone"></span></p>
                                    <p><strong>Services:</strong> <span id="confirmServices"></span></p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p><strong>Description:</strong> <span id="confirmRestaurantDescription"></span></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="termsAgreement" required>
                        <label class="form-check-label" for="termsAgreement">
                            J'accepte les <a href="#">conditions d'utilisation</a> et la <a href="#">politique de confidentialité</a>
                        </label>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="prevStep(2)"><i class="fas fa-arrow-left me-1"></i> Précédent</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Confirmer l'inscription</button>
                    </div>
                </div>
            </form>
            
            <!-- Message de succès (caché initialement) -->
            <div class="alert alert-success mt-4" id="successMessage" style="display: none;">
                <h4><i class="fas fa-check-circle me-2"></i>Inscription réussie!</h4>
                <p>Le nouveau gestionnaire a été inscrit avec succès. Un email de confirmation a été envoyé à l'adresse <strong id="successEmail"></strong>.</p>
                <a href="/admin/users.php" class="btn btn-outline-success me-2">Retour à la liste des utilisateurs</a>
                <a href="#" class="btn btn-success" onclick="resetForm()">Créer un autre compte</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables globales
        let currentStep = 1;
        const totalSteps = 3;
        
        // Fonction pour passer à l'étape suivante
        function nextStep(step) {
            // Validation de l'étape actuelle
            if (step === 2 && !validateStep1()) {
                return;
            }
            
            if (step === 3 && !validateStep2()) {
                return;
            }
            
            // Cacher l'étape actuelle
            document.getElementById(`section${currentStep}`).classList.remove('active');
            document.getElementById(`step${currentStep}`).classList.remove('active');
            
            // Afficher la nouvelle étape
            currentStep = step;
            document.getElementById(`section${currentStep}`).classList.add('active');
            document.getElementById(`step${currentStep}`).classList.add('active');
            
            // Si on arrive à l'étape de confirmation, remplir les données
            if (step === 3) {
                populateConfirmation();
            }
        }
        
        // Fonction pour revenir à l'étape précédente
        function prevStep(step) {
            // Cacher l'étape actuelle
            document.getElementById(`section${currentStep}`).classList.remove('active');
            document.getElementById(`step${currentStep}`).classList.remove('active');
            
            // Afficher la nouvelle étape
            currentStep = step;
            document.getElementById(`section${currentStep}`).classList.add('active');
            document.getElementById(`step${currentStep}`).classList.add('active');
        }
        
        // Validation de l'étape 1
        function validateStep1() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const passwordError = document.getElementById('passwordError');
            
            // Vérifier que les mots de passe correspondent
            if (password !== confirmPassword) {
                document.getElementById('confirmPassword').classList.add('is-invalid');
                passwordError.style.display = 'block';
                return false;
            } else {
                document.getElementById('confirmPassword').classList.remove('is-invalid');
                passwordError.style.display = 'none';
                return true;
            }
        }
        
        // Validation de l'étape 2
        function validateStep2() {
            // Ici, vous pouvez ajouter des validations supplémentaires si nécessaire
            return true;
        }
        
        // Remplir la page de confirmation avec les données saisies
        function populateConfirmation() {
            document.getElementById('confirmFirstName').textContent = document.getElementById('firstName').value;
            document.getElementById('confirmLastName').textContent = document.getElementById('lastName').value;
            document.getElementById('confirmEmail').textContent = document.getElementById('email').value;
            document.getElementById('confirmPhone').textContent = document.getElementById('phone').value || 'Non renseigné';
            
            document.getElementById('confirmRestaurantName').textContent = document.getElementById('restaurantName').value;
            document.getElementById('confirmRestaurantAddress').textContent = document.getElementById('restaurantAddress').value;
            document.getElementById('confirmRestaurantPhone').textContent = document.getElementById('restaurantPhone').value || 'Non renseigné';
            document.getElementById('confirmRestaurantDescription').textContent = document.getElementById('restaurantDescription').value || 'Aucune description';
            
            // Services
            const hasDelivery = document.getElementById('hasDelivery').value === '1';
            const hasPickup = document.getElementById('hasPickup').value === '1';
            let services = [];
            
            if (hasDelivery) services.push('Livraison');
            if (hasPickup) services.push('À emporter');
            
            document.getElementById('confirmServices').textContent = services.length > 0 ? services.join(', ') : 'Aucun service';
        }
        
        // Gestion de la soumission du formulaire
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simuler l'envoi des données (dans un cas réel, vous enverriez les données au serveur)
            const email = document.getElementById('email').value;
            document.getElementById('successEmail').textContent = email;
            
            // Cacher le formulaire et afficher le message de succès
            document.getElementById('registrationForm').style.display = 'none';
            document.getElementById('successMessage').style.display = 'block';
        });
        
        // Réinitialiser le formulaire
        function resetForm() {
            document.getElementById('registrationForm').reset();
            document.getElementById('registrationForm').style.display = 'block';
            document.getElementById('successMessage').style.display = 'none';
            
            // Réinitialiser les étapes
            document.querySelectorAll('.form-section').forEach(section => {
                section.classList.remove('active');
            });
            document.querySelectorAll('.step').forEach(step => {
                step.classList.remove('active');
            });
            
            // Retour à la première étape
            currentStep = 1;
            document.getElementById('section1').classList.add('active');
            document.getElementById('step1').classList.add('active');
            
            // Réinitialiser l'aperçu du logo
            document.getElementById('logoPreview').innerHTML = '<span id="logoPreviewText"><i class="fas fa-image me-1"></i> Aperçu</span>';
        }
        
        // Aperçu du logo
        document.getElementById('restaurantLogo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('logoPreview');
                    preview.innerHTML = '';
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Basculer la visibilité du mot de passe
        document.getElementById('passwordToggle').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        document.getElementById('confirmPasswordToggle').addEventListener('click', function() {
            const passwordInput = document.getElementById('confirmPassword');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>