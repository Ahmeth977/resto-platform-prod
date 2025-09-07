<?php
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/functions.php';

$page_title = "Politique de confidentialité - Sen Commande";
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
        
        .legal-header .update-date {
            color: #666;
            font-style: italic;
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
                <h1>Politique de confidentialité de Sen Commande</h1>
                <p class="update-date">Date de mise à jour : 30 août 2025</p>
            </div>
            
            <p>Chez Sen Commande, nous attachons une grande importance à la protection des données personnelles de nos utilisateurs. Cette politique explique comment nous collectons, utilisons, stockons et protégeons les informations que vous nous confiez lorsque vous utilisez notre site, notre application ou nos canaux de communication comme WhatsApp.</p>
            
            <h3 class="section-title">1. Champ d'application</h3>
            <p>Cette politique concerne toutes les données collectées à travers Sen Commande et nos services associés. Elle ne couvre pas les sites ou services tiers avec lesquels vous pourriez interagir.</p>
            
            <h3 class="section-title">2. Données collectées et utilisation</h3>
            <p>Nous collectons les informations que vous fournissez volontairement comme votre nom, numéro de téléphone, adresse e-mail et adresse de livraison. Elles sont utilisées pour traiter vos commandes, vous identifier et communiquer avec vous. Nous collectons aussi des données techniques comme l'adresse IP, le type d'appareil ou le navigateur utilisé afin d'améliorer la sécurité et l'expérience utilisateur. Nous utilisons également des cookies pour assurer le bon fonctionnement du site, personnaliser votre expérience et analyser le trafic. Vous pouvez désactiver les cookies dans votre navigateur mais cela peut limiter certaines fonctionnalités.</p>
            
            <h3 class="section-title">3. Partage des données</h3>
            <p>Nous ne vendons pas vos informations. Elles peuvent être partagées uniquement avec nos partenaires comme les livreurs ou prestataires techniques pour exécuter les services, avec les autorités si la loi l'exige ou avec votre accord pour certaines opérations de communication.</p>
            
            <h3 class="section-title">4. Conservation des données</h3>
            <p>Vos données sont conservées uniquement le temps nécessaire pour fournir nos services ou selon les obligations légales. Passé ce délai, elles sont supprimées ou rendues anonymes.</p>
            
            <h3 class="section-title">5. Sécurité</h3>
            <p>Nous utilisons différentes mesures pour protéger vos données : connexions sécurisées, hébergement fiable, accès limité aux personnes autorisées et systèmes de sauvegarde et de protection contre les cyberattaques.</p>
            
            <h3 class="section-title">6. Vos droits</h3>
            <p>Vous avez le droit d'accéder à vos données, de demander leur correction, leur suppression, de vous opposer à certains traitements ou de demander la portabilité. Pour cela, vous pouvez nous contacter à l'adresse suivante : <a href="mailto:Sencommande23@gmail.com">Sencommande23@gmail.com</a> ou via WhatsApp au <strong>[ton numéro WhatsApp officiel]</strong>.</p>
            
            <h3 class="section-title">7. Mineurs</h3>
            <p>Nos services sont destinés en priorité aux personnes majeures. Les mineurs doivent obtenir l'autorisation d'un parent ou tuteur avant de fournir des informations personnelles.</p>
            
            <h3 class="section-title">8. Modifications</h3>
            <p>Cette politique peut être modifiée à tout moment. La date de mise à jour sera indiquée en haut. En continuant d'utiliser nos services, vous acceptez la nouvelle version.</p>
            
            <h3 class="section-title">9. Contact</h3>
            <p>Pour toute question liée à vos données personnelles ou à cette politique, vous pouvez nous écrire à : <a href="mailto:Sencommande23@gmail.com">Sencommande23@gmail.com</a> ou via WhatsApp au <strong>[ton numéro WhatsApp officiel]</strong>.</p>
            
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