<?php
// generate_placeholder.php

// 1. Configuration de base
header('Content-Type: image/jpeg');
require_once __DIR__.'/includes/config.php';

// 2. Récupération des paramètres
$restoId = (int)$_GET['id'];
$pattern = isset($_GET['pattern']) ? $_GET['pattern'] : 'default';

// 3. Chemins des fichiers
$patternsDir = __DIR__.'/assets/img/placeholders/';
$outputDir = __DIR__.'/assets/img/generated/';

// Créer le dossier "generated" si inexistant
if (!file_exists($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// 4. Vérification du pattern
$availablePatterns = ['french', 'italian', 'asian', 'burger', 'default'];
$pattern = in_array($pattern, $availablePatterns) ? $pattern : 'default';
$patternFile = $patternsDir.$pattern.'.jpg';

// 5. Génération de l'image
$outputFile = $outputDir.'resto_'.$restoId.'.jpg';

if (!file_exists($outputFile) || filemtime($outputFile) < time() - 86400) {
    // Charger l'image de base
    $image = imagecreatefromjpeg($patternFile);
    
    // Couleur du texte (blanc avec ombre)
    $white = imagecolorallocate($image, 255, 255, 255);
    $shadow = imagecolorallocate($image, 0, 0, 0);
    
    // Police (à télécharger dans assets/fonts/)
    $font = __DIR__.'/assets/fonts/Montserrat-Bold.ttf';
    
    // Texte à ajouter
    $text = "Resto #".$restoId;
    
    // Positionnement avec ombre
    $fontSize = 24;
    $x = 30;
    $y = 50;
    
    // Ajout de l'ombre
    imagettftext($image, $fontSize, 0, $x+2, $y+2, $shadow, $font, $text);
    // Ajout du texte principal
    imagettftext($image, $fontSize, 0, $x, $y, $white, $font, $text);
    
    // Sauvegarde
    imagejpeg($image, $outputFile, 90);
    imagedestroy($image);
}

// 6. Affichage
readfile($outputFile);