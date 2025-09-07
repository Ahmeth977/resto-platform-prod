<?php
function sendOrderEmail($to, $subject, $messageHTML, $fromName = 'RestoPlatform') {
    // Headers pour email HTML
    $headers = "From: $fromName <no-reply@restoplatform.com>\r\n";
    $headers .= "Reply-To: no-reply@restoplatform.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    $headers .= "X-Priority: 1 (Highest)\r\n";
    
    // Envoyer l'email
    return mail($to, $subject, $messageHTML, $headers);
}