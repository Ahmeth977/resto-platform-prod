<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';

// Vérifier si l'utilisateur est déjà connecté
session_start();
if (isset($_SESSION['user_id'])) {
    redirectBasedOnRole();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validation basique
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        // Vérifier les identifiants dans la base de données (uniquement pour restaurateurs)
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = 'restaurateur'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            
            // Vérifier si l'utilisateur a un restaurant associé
            $stmt = $db->prepare("SELECT * FROM restaurants WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $restaurant = $stmt->fetch();
            
            if ($restaurant) {
                $_SESSION['restaurant_id'] = $restaurant['id'];
                $_SESSION['restaurant_name'] = $restaurant['name'];
                
                // Redirection vers le tableau de bord manager
                header("Location: index.php");
                exit();
            } else {
                $error = "Aucun restaurant associé à ce compte";
            }
        } else {
            $error = "Email ou mot de passe incorrect ou compte non autorisé";
        }
    }
}

// Fonction de redirection basée sur le rôle
function redirectBasedOnRole() {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: ../admin/index.php");
        exit();
    } else if ($_SESSION['user_role'] === 'restaurateur') {
        header("Location: index.php");
        exit();
    } else {
        header("Location: ../manager/index.php");
        exit();
    }
}

// Si on arrive ici, c'est qu'il y a une erreur
$_SESSION['login_error'] = $error;
header("Location: ../index.php");
exit();
?>