<?php
// Configuration email
define('EMAIL_FROM', 'no-reply@votre-domaine.com');
define('EMAIL_FROM_NAME', 'FoodManager');
define('EMAIL_REPLY_TO', 'sencommande23@gmail.com');

// Configuration SMTP (recommandé pour une meilleure délivrabilité)
define('SMTP_ENABLED', false);
define('SMTP_HOST', 'smtp.votre-fournisseur.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'votre-email@votre-domaine.com');
define('SMTP_PASSWORD', 'votre-mot-de-passe');
define('SMTP_SECURE', 'tls'); // tls ou ssl

// Fonction améliorée d'envoi d'email
function sendEmail($to, $subject, $message, $headers = '') {
    if (SMTP_ENABLED) {
        // Utiliser PHPMailer ou SwiftMailer pour une solution SMTP
        // Vous devrez installer la bibliothèque via Composer
        return sendEmailSMTP($to, $subject, $message);
    } else {
        // Utiliser la fonction mail() de base
        $default_headers = "MIME-Version: 1.0" . "\r\n";
        $default_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $default_headers .= "From: " . EMAIL_FROM_NAME . " <" . EMAIL_FROM . ">" . "\r\n";
        $default_headers .= "Reply-To: " . EMAIL_REPLY_TO . "\r\n";
        $default_headers .= "X-Mailer: PHP/" . phpversion();
        
        if (!empty($headers)) {
            $default_headers .= $headers;
        }
        
        return mail($to, $subject, $message, $default_headers);
    }
}

// Fonction SMTP (exemple avec PHPMailer - nécessite l'installation via Composer)
function sendEmailSMTP($to, $subject, $message) {
    /*
    // Exemple avec PHPMailer
    require_once '../vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->Port = SMTP_PORT;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    
    $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
    $mail->addAddress($to);
    $mail->addReplyTo(EMAIL_REPLY_TO);
    
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $message;
    
    return $mail->send();
    */
    
    // Fallback à la fonction mail() si SMTP n'est pas configuré
    return sendEmail($to, $subject, $message);
}
?>