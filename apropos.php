<?php
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/functions.php';

$page_title = "À propos - Sen Commande";
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
        
        .about-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('<?= BASE_URL ?>assets/img/about-hero.jpg') no-repeat center center;
            background-size: cover;
            padding: 5rem 0;
            color: white;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .about-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .about-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 2.5rem;
            margin-bottom: 2rem;
        }
        
        .section-title {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-weight: 700;
        }
        
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .value-card {
            text-align: center;
            padding: 1.5rem;
            border-radius: 8px;
            background-color: #f8f9fa;
            transition: transform 0.3s ease;
        }
        
        .value-card:hover {
            transform: translateY(-5px);
        }
        
        .value-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .team-card {
            text-align: center;
        }
        
        .team-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 4px solid var(--primary-color);
        }
        
        .contact-info {
            background-color: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <?php include __DIR__.'/includes/nav.php'; ?>
    
    <section class="about-hero">
        <div class="container">
            <h1 class="display-4 fw-bold">À propos de Sen Commande</h1>
            <p class="lead">Découvrez l'histoire et les valeurs qui animent notre entreprise</p>
        </div>
    </section>
    
    <div class="container py-4">
        <div class="about-container">
            <div class="about-section">
                <h2 class="section-title">Notre Histoire</h2>
                <p>Sen Commande est née d'une passion pour la gastronomie sénégalaise et d'un constat simple : il était souvent difficile de profiter des plats des meilleurs restaurants sans se déplacer.</p>
                <p>Fondée en 2025, notre plateforme connecte les amateurs de bonne cuisine avec les établissements gastronomiques les plus réputés de la région, en leur offrant un service de livraison de qualité professionnelle.</p>
                <p>Aujourd'hui, Sen Commande est fière de collaborer avec les restaurants les plus prestigieux pour vous apporter une expérience culinaire exceptionnelle directement à votre domicile.</p>
            </div>
            
            <div class="about-section">
                <h2 class="section-title">Notre Mission</h2>
                <p>Notre mission est de révolutionner l'expérience de la livraison de repas en mettant l'accent sur :</p>
                
                <div class="values-grid">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h4>Qualité exceptionnelle</h4>
                        <p>Des plats préparés avec soin par les meilleurs chefs</p>
                    </div>
                    
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h4>Livraison rapide</h4>
                        <p>Service express pour préserver la fraîcheur et les saveurs</p>
                    </div>
                    
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4>Satisfaction client</h4>
                        <p>Service client dédié et à votre écoute</p>
                    </div>
                </div>
            </div>
            
            <div class="about-section">
                <h2 class="section-title">Notre Équipe</h2>
                <p>Une équipe passionnée travaille chaque jour pour vous offrir le meilleur service possible :</p>
                
                <div class="team-grid">
                    <div class="team-card">
                        <img src="<?= BASE_URL ?>assets/img/team1.jpg" alt="Membre de l'équipe" class="team-img" onerror="this.src='<?= BASE_URL ?>assets/img/placeholder-team.jpg'">
                        <h4>[Nom du fondateur]</h4>
                        <p>Fondateur & CEO</p>
                    </div>
                    
                    <div class="team-card">
                        <img src="<?= BASE_URL ?>assets/img/team2.jpg" alt="Membre de l'équipe" class="team-img" onerror="this.src='<?= BASE_URL ?>assets/img/placeholder-team.jpg'">
                        <h4>[Nom du responsable]</h4>
                        <p>Responsable Relations Restaurants</p>
                    </div>
                    
                    <div class="team-card">
                        <img src="<?= BASE_URL ?>assets/img/team3.jpg" alt="Membre de l'équipe" class="team-img" onerror="this.src='<?= BASE_URL ?>assets/img/placeholder-team.jpg'">
                        <h4>[Nom du responsable]</h4>
                        <p>Responsable Service Client</p>
                    </div>
                </div>
            </div>
            
            <div class="about-section">
                <h2 class="section-title">Contactez-nous</h2>
                <p>Vous avez des questions, des suggestions ou besoin d'assistance ? Notre équipe est là pour vous aider.</p>
                
                <div class="contact-info">
                    <p><strong>Email :</strong> <a href="mailto:Sencommande23@gmail.com">Sencommande23@gmail.com</a></p>
                    <p><strong>WhatsApp :</strong> [ton numéro WhatsApp officiel]</p>
                    <p><strong>Horaires :</strong> Du lundi au dimanche, de 9h à 23h</p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__.'/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>