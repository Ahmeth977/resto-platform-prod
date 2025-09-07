<?php
// hash_password.php - À exécuter une seule fois
require_once 'includes/config.php';

$email = 'Sambmouhamed593@gmail.com';
$plain_password = 'Mouhamed003@2001';

// Hacher le mot de passe
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

// Afficher le mot de passe hashé pour information
echo "Mot de passe original: " . $plain_password . "<br>";
echo "Mot de passe hashé: " . $hashed_password . "<br>";

// Mettre à jour dans la base de données
try {
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
    $result = $stmt->execute([$hashed_password, $email]);

    if ($result && $stmt->rowCount() > 0) {
        echo "<strong style='color: green;'>Mot de passe mis à jour avec succès!</strong>";
    } else {
        echo "<strong style='color: red;'>Erreur: Aucune ligne mise à jour. Vérifiez l'email.</strong>";
    }
} catch(PDOException $e) {
    echo "<strong style='color: red;'>Erreur SQL: " . $e->getMessage() . "</strong>";
}

// Afficher les informations de l'utilisateur pour vérification
echo "<br><br>Vérification de l'utilisateur:<br>";
$check_stmt = $db->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
$check_stmt->execute([$email]);
$user = $check_stmt->fetch();

if ($user) {
    echo "ID: " . $user['id'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Rôle: " . $user['role'] . "<br>";
    echo "Mot de passe (hashé): " . $user['password'] . "<br>";
    
    // Vérifier si le mot de passe correspond
    if (password_verify($plain_password, $user['password'])) {
        echo "<strong style='color: green;'>✓ Le mot de passe correspond!</strong>";
    } else {
        echo "<strong style='color: red;'>✗ Le mot de passe ne correspond pas!</strong>";
    }
} else {
    echo "<strong style='color: red;'>Utilisateur non trouvé avec l'email: $email</strong>";
}
?>