<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';



// Récupérer les statistiques
$stats = [
    'users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'restaurants' => $db->query("SELECT COUNT(*) FROM restaurants")->fetchColumn(),
    'orders' => $db->query("SELECT COUNT(*) FROM orders")->fetchColumn()
];
?>
<!-- Exemple de lien dans la sidebar admin -->
<li class="nav-item">
    <a class="nav-link" href="/resto_plateform/admin/dashboard.php">
        <i class="fas fa-tachometer-alt"></i>
        Tableau de bord
    </a>
</li>
<!-- Interface Admin -->