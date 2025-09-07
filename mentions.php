<?php
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/functions.php';

$page_title = "Mentions légales - Sen Commande";
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
        
        .legal-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .legal-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .legal-header h1 {
            color: var(--dark-color);
            font-weight: 700;
        }
        
        .section-title {
            color: var(--primary-color);
            margin: 1.5rem 0 1rem;
            font-weight: 600;
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
    
    <div class="container py-5">
        <div class="legal-container">
            <div class="legal-header">
                <h1>Mentions légales - Sen Commande</h1>
            </div>
            
            <h3 class="section-title">1. Éditeur du site</h3>
            <p><strong>Sen Commande</strong><br>
            [Adresse complète de l'entreprise]<br>
            Téléphone : [Votre numéro de téléphone]<br>
            Email : <a href="mailto:Sencommande23@gmail.com">Sencommande23@gmail.com</a></p>
            
            <h3 class="section-title">2. Directeur de la publication</h3>
            <p>[Nom du responsable de la publication]</p>
            
            <h3 class="section-title">3. Hébergement</h3>
            <p><strong>[Nom de l'hébergeur]</strong><br>
            [Adresse de l'hébergeur]<br>
            Téléphone : [Numéro de téléphone de l'hébergeur]<br>
            Site web : <a href="[Site web de l'hébergeur]" target="_blank">[Site web de l'hébergeur]</a></p>
            
            <h3 class="section-title">4. Propriété intellectuelle</h3>
            <p>L'ensemble de ce site relève de la législation française et internationale sur le droit d'auteur et la propriété intellectuelle. Tous les droits de reproduction sont réservés, y compris pour les documents téléchargeables et les représentations iconographiques et photographiques.</p>
            
            <h3 class="section-title">5. Données personnelles</h3>
            <p>Les informations que vous pouvez donner dans le cadre des formulaires présents sur le site sont nécessaires pour répondre à votre demande et sont destinées à Sen Commande, responsable du traitement. Conformément à la loi "Informatique et Libertés" du 6 janvier 1978 modifiée, vous disposez d'un droit d'accès, de rectification, de modification et de suppression des données qui vous concernent.</p>
            
            <h3 class="section-title">6. Cookies</h3>
            <p>Le site peut utiliser des cookies pour mesurer l'audience et la navigation des visiteurs. Vous pouvez vous opposer à l'enregistrement de cookies en configurant votre navigateur selon les instructions disponibles sur le site de la CNIL.</p>
            
            <h3 class="section-title">7. Limitations de responsabilité</h3>
            <p>Sen Commande ne peut garantir l'exactitude, la complétude ou l'actualité des informations diffusées sur son site. L'utilisateur reconnaît utiliser les informations sous sa responsabilité exclusive.</p>
            
            <div class="contact-info">
                <h4>Coordonnées</h4>
                <p><strong>Email :</strong> <a href="mailto:Sencommande23@gmail.com">Sencommande23@gmail.com</a></p>
                <p><strong>WhatsApp :</strong> [ton numéro WhatsApp officiel]</p>
            </div>
        </div>
    </div>
    
    <?php include __DIR__.'/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>