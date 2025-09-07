<nav class="navbar navbar-expand-lg navbar-light fixed-top" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
    <div class="container">
        <!-- Logo image seulement - AUCUN TEXTE -->
        <a class="navbar-brand" href="/">
    <img src="/assets/img/logo.jpg" alt="À Domicile" height="50" class="d-inline-block align-text-top"
         onerror="this.style.display='none';">
</a>

        <!-- Bouton panier visible sur mobile -->
        <div class="d-lg-none ms-auto me-3">
            <a href="cart.php" class="btn btn-primary btn-sm position-relative">
                <i class="fas fa-shopping-cart"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    0
                </span>
            </a>
        </div>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Barre de recherche intégrée dans la navigation (desktop) -->
            <form class="d-none d-lg-flex me-auto ms-4" style="max-width: 300px;">
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm" placeholder="Rechercher un restaurant..." aria-label="Recherche">
                    <button class="btn btn-primary btn-sm" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php" style="color: #333 !important;">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php" style="color: #333 !important;">Restaurants</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="apropos.php" style="color: #333 !important;">À propos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php" style="color: #333 !important;">Contact</a>
                </li>
                
                <!-- Panier visible sur desktop -->
                <li class="nav-item d-none d-lg-block">
                    <a href="cart.php" class="nav-link position-relative" style="color: #333 !important;">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            0
                        </span>
                    </a>
                </li>
                
                <!-- Compte utilisateur -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #333 !important;">
                        <i class="fas fa-user"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="acceuil.php">Connexion</a></li>
                        <li><a class="dropdown-item" href="register.php">Inscription</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="account.php">Mon compte</a></li>
                        <li><a class="dropdown-item" href="orders.php">Mes commandes</a></li>
                        <li><a class="dropdown-item" href="logout.php">Déconnexion</a></li>
                    </ul>
                </li>
            </ul>
            
            <!-- Barre de recherche pour mobile -->
            <form class="d-lg-none mt-3">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Rechercher un restaurant..." aria-label="Recherche">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</nav>

<style>
    /* Styles pour la navbar améliorée */
    .navbar {
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        padding: 0.5rem 0;
    }
    
    .navbar.scrolled {
        padding: 0.3rem 0;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
        backdrop-filter: blur(10px);
    }
    
    .nav-link {
        position: relative;
        padding: 0.5rem 1rem !important;
        font-weight: 500;
        transition: all 0.3s ease;
        color: #333 !important;
    }
    
    .nav-link:not(.dropdown-toggle):after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 50%;
        background-color: #ff6b35;
        transition: all 0.3s ease;
    }
    
    .nav-link:hover:not(.dropdown-toggle):after,
    .nav-link.active:after {
        width: 70%;
        left: 15%;
    }
    
    .nav-link:hover {
        color: #ff6b35 !important;
    }
    
    .dropdown-menu {
        border: none;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }
    
    .dropdown-item {
        transition: all 0.2s ease;
        color: #333;
    }
    
    .dropdown-item:hover {
        background-color: #f8f9fa;
        padding-left: 1.5rem;
        color: #ff6b35;
    }
    
    /* Page loader */
    .page-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        transition: opacity 0.3s ease;
    }
    
    .page-loader.hidden {
        opacity: 0;
        pointer-events: none;
    }
    
    /* Correction de la couleur du toggler */
    .navbar-toggler {
        border-color: rgba(51, 51, 51, 0.1);
    }
    
    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(51, 51, 51, 0.8)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion du scroll pour la navbar
        const navbar = document.querySelector('.navbar');
        const pageLoader = document.getElementById('pageLoader');
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Masquer le loader après le chargement
        setTimeout(function() {
            if (pageLoader) {
                pageLoader.classList.add('hidden');
                
                // Retirer complètement après l'animation
                setTimeout(function() {
                    pageLoader.remove();
                }, 300);
            }
        }, 1000);
        
        // Gestion des clics sur les liens de la navbar
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                // Afficher le loader pour les pages externes
                if (this.getAttribute('href') && !this.getAttribute('href').startsWith('#')) {
                    if (pageLoader) {
                        pageLoader.classList.remove('hidden');
                    }
                }
            });
        });
        
        // Fermer le menu mobile après un clic
        const navLinks = document.querySelectorAll('.nav-link');
        const menuToggle = document.getElementById('navbarNav');
        const bsCollapse = new bootstrap.Collapse(menuToggle, {toggle: false});
        
        navLinks.forEach((l) => {
            l.addEventListener('click', () => {
                if (menuToggle.classList.contains('show')) {
                    bsCollapse.hide();
                }
            });
        });
    });
</script>