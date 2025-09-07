<?php
// admin/includes/admin_nav.php
require_once __DIR__.'/../../includes/config.php';

// Démarrer la session si pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification authentification
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Vérification rôle admin
$stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
?>

<nav class="navbar navbar-expand navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Admin Panel</a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Tableau de bord</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="restaurants.php">Restaurants</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="menus.php">Menus</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="users.php">Utilisateurs</a>
            </li>
            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
        </ul>
    </div>
</nav>