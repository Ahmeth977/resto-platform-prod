<?php
// Vérifier que l'utilisateur est administrateur
if ($_SESSION['user_role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}
?>

<!-- Sidebar -->
<div class="col-lg-2 col-md-3 p-0 sidebar">
    <div class="d-flex flex-column p-3">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Tableau de bord
                </a>
            </li>
            <li>
                <a href="restaurants.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'restaurants.php' ? 'active' : '' ?>">
                    <i class="fas fa-utensils me-2"></i>
                    Restaurants
                </a>
            </li>
            <li>
                <a href="menus.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'menus.php' ? 'active' : '' ?>">
                    <i class="fas fa-list me-2"></i>
                    Menus
                </a>
            </li>
            <li>
                <a href="orders.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Commandes
                </a>
            </li>
            <li>
                <a href="users.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
                    <i class="fas fa-users me-2"></i>
                    Utilisateurs
                </a>
            </li>
            <li>
                <a href="payments.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : '' ?>">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    Paiements
                </a>
            </li>
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <img src="https://via.placeholder.com/32" alt="Admin" width="32" height="32" class="rounded-circle me-2">
                <strong>Administrateur</strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
            </ul>
        </div>
    </div>
</div>