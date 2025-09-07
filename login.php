<?php
session_start();
require_once __DIR__.'/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    // Vérification des identifiants
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role IN ('admin', 'restaurateur')");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Connexion réussie
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        
        // Redirection selon le rôle
        if ($user['role'] === 'admin') {
            header("Location: admin/index.php");
        } else {
            header("Location: restaurateur/dashboard.php");
        }
        exit();
    } else {
        // Échec de connexion
        header("Location: acceuil.php?error=invalid_credentials");
        exit();
    }
}
?>