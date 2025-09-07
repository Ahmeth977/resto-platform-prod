<?php
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/functions.php';

// Traitement du formulaire de contact
$form_message = '';
$form_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation simple
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Le nom est obligatoire";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Une adresse email valide est obligatoire";
    }
    
    if (empty($message)) {
        $errors[] = "Le message ne peut pas être vide";
    }
    
    if (empty($errors)) {
        // Ici vous devriez normalement envoyer un email ou enregistrer en base de données
        // Pour l'instant, on simule juste un envoi réussi
        
        $form_success = true;
        $form_message = "Votre message a été envoyé avec succès! Nous vous répondrons dans les plus brefs délais.";
        
        // Réinitialiser les champs
        $name = $email = $phone = $subject = $message = '';
    } else {
        $form_message = implode("<br>", $errors);
    }
}

$page_title = "Contact - Sen Commande";
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/navbar.css">
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>assets/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>assets/favicon/favicon-16x16.png">
    
    <style>
        :root {
            --primary-color: #ff6b35;
            --secondary-color: #4ECDC4;
            --dark-color: #292F36;
            --light-color: #F7FFF7;
            --accent-color: #EF476F;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            padding-top: 80px;
            color: #333;
            line-height: 1.6;
        }
        
        .contact-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('<?= BASE_URL ?>assets/img/contact-hero.jpg') no-repeat center center;
            background-size: cover;
            padding: 4rem 0;
            color: white;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        
        @media (min-width: 992px) {
            .contact-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        .contact-info {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 2.5rem;
            height: fit-content;
        }
        
        .contact-form {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 2.5rem;
        }
        
        .section-title {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-weight: 700;
        }
        
        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .info-icon {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-right: 1rem;
            min-width: 30px;
        }
        
        .info-content h4 {
            margin-bottom: 0.3rem;
            font-weight: 600;
        }
        
        .info-content p {
            margin-bottom: 0;
            color: #666;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }
        
        .form-control {
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.8rem 2rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #e55a2b;
            border-color: #e55a2b;
            transform: translateY(-2px);
        }
        
        .alert {
            border-radius: 8px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-link:hover {
            background-color: var(--dark-color);
            transform: translateY(-3px);
        }
        
        .map-container {
            margin-top: 3rem;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .map-container iframe {
            width: 100%;
            height: 300px;
            border: none;
        }
        
        .hours-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .hours-list li {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .hours-list li:last-child {
            border-bottom: none;
        }
        
        .address-map {
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }
    </style>
</head>
<body>
    <?php include __DIR__.'/includes/nav.php'; ?>
    
    <section class="contact-hero">
        <div class="container">
            <h1 class="display-4 fw-bold">Contactez-nous</h1>
            <p class="lead">Nous sommes à votre écoute pour toute question ou demande</p>
        </div>
    </section>
    
    <div class="container py-4">
        <div class="contact-container">
            <?php if ($form_message): ?>
                <div class="alert <?= $form_success ? 'alert-success' : 'alert-danger' ?>">
                    <?= $form_message ?>
                </div>
            <?php endif; ?>
            
            <div class="contact-grid">
                <div class="contact-info">
                    <h2 class="section-title">Nos coordonnées</h2>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <h4>Adresse</h4>
                            <p>À côté de Pharmacie Mame Diarra<br>Mbour 1, Thiès<br>Sénégal</p>
                            <div class="address-map">
                                <small><i class="fas fa-info-circle me-1"></i> Facilement accessible depuis le centre-ville de Thiès</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <h4>Téléphone</h4>
                            <p>+221 77 123 45 67</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h4>Email</h4>
                            <p><a href="mailto:Sencommande23@gmail.com">Sencommande23@gmail.com</a></p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <h4>Horaires d'ouverture</h4>
                            <ul class="hours-list">
                                <li><span>Lundi - Vendredi:</span> <span>9h - 23h</span></li>
                                <li><span>Samedi:</span> <span>10h - Minuit</span></li>
                                <li><span>Dimanche:</span> <span>10h - 22h</span></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="social-links">
                        <a href="#" class="social-link">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://wa.me/221771234567" class="social-link">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
                
                <div class="contact-form">
                    <h2 class="section-title">Envoyez-nous un message</h2>
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom complet *</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Sujet</label>
                                    <select class="form-control" id="subject" name="subject">
                                        <option value="">Sélectionnez un sujet</option>
                                        <option value="question" <?= (($subject ?? '') == 'question') ? 'selected' : '' ?>>Question générale</option>
                                        <option value="technical" <?= (($subject ?? '') == 'technical') ? 'selected' : '' ?>>Problème technique</option>
                                        <option value="partnership" <?= (($subject ?? '') == 'partnership') ? 'selected' : '' ?>>Partenariat</option>
                                        <option value="complaint" <?= (($subject ?? '') == 'complaint') ? 'selected' : '' ?>>Réclamation</option>
                                        <option value="other" <?= (($subject ?? '') == 'other') ? 'selected' : '' ?>>Autre</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required><?= htmlspecialchars($message ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i> Envoyer le message
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3859.527374641603!2d-17.44670392501827!3d14.69277578571275!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xec172df5ac2dacd%3A0x5b4c436b4c12cbb0!2sDakar%2C%20Senegal!5e0!3m2!1sen!2sus!4v1693499999999!5m2!1sen!2sus" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </div>
    
    <?php include __DIR__.'/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script pour améliorer l'expérience utilisateur
        document.addEventListener('DOMContentLoaded', function() {
            // Animation des éléments
            const animateOnScroll = () => {
                const elements = document.querySelectorAll('.info-item, .contact-form');
                
                elements.forEach(element => {
                    const position = element.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.3;
                    
                    if (position < screenPosition) {
                        element.style.opacity = 1;
                        element.style.transform = 'translateY(0)';
                    }
                });
            };
            
            // Initialiser les styles pour l'animation
            document.querySelectorAll('.info-item, .contact-form').forEach(element => {
                element.style.opacity = 0;
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            });
            
            // Écouter l'événement de scroll
            window.addEventListener('scroll', animateOnScroll);
            // Déclencher une première fois au chargement
            animateOnScroll();
            
            // Validation du formulaire côté client
            const contactForm = document.querySelector('form');
            if (contactForm) {
                contactForm.addEventListener('submit', function(e) {
                    let valid = true;
                    const requiredFields = this.querySelectorAll('[required]');
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            valid = false;
                            field.classList.add('is-invalid');
                        } else {
                            field.classList.remove('is-invalid');
                        }
                    });
                    
                    if (!valid) {
                        e.preventDefault();
                        // Faire défiler jusqu'au premier champ invalide
                        const firstInvalid = this.querySelector('.is-invalid');
                        if (firstInvalid) {
                            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                });
            }
            
            // Mise à jour de la carte pour Thiès
            const updateMapForThies = () => {
                const iframe = document.querySelector('.map-container iframe');
                if (iframe) {
                    // Coordonnées approximatives de Thiès
                    iframe.src = "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15478.48141916071!2d-16.93598936828665!3d14.792466533772925!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xec172c7ac0e3521%3A0x627d3c1e6c15b84d!2sThi%C3%A8s%2C%20Senegal!5e0!3m2!1sen!2sus!4v1693500000000!5m2!1sen!2sus";
                }
            };
            
            // Mettre à jour la carte pour Thiès
            updateMapForThies();
        });
    </script>
</body>
</html>