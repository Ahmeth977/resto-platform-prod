<!-- Footer -->
<footer class="bg-dark text-white py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <h5 class="text-gold mb-4">SenCommanDe</h5>
                <p>L'excellence culinaire livrée chez vous. Découvrez les meilleurs restaurants de votre ville.</p>
                <div class="social-icons mt-4">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                <h5 class="text-gold mb-4">Navigation</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?= BASE_URL ?>" class="text-white">Accueil</a></li>
                    <li class="mb-2"><a href="#restaurants" class="text-white">Restaurants</a></li>
                    <li class="mb-2"><a href="apropos.php" class="text-white">À propos</a></li>
                    <li class="mb-2"><a href="contact.php" class="text-white">Contact</a></li>
                </ul>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                <h5 class="text-gold mb-4">Contact</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-map-marker-alt text-gold me-2"></i> Thiès-Mbour1</li>
                    <li class="mb-2"><i class="fas fa-phone text-gold me-2"></i> +221 78 115 56 09</li>
                    <li class="mb-2"><i class="fas fa-envelope text-gold me-2"></i> sencommande23@gmail.com</li>
                </ul>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <h5 class="text-gold mb-4">Newsletter</h5>
                <p>Abonnez-vous pour recevoir nos offres exclusives.</p>
                <form class="mt-3">
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Votre email" aria-label="Votre email">
                        <button class="btn btn-gold" type="submit">OK</button>
                    </div>
                </form>
            </div>
        </div>
        
        <hr class="my-4 bg-secondary">
        
        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0">&copy; <?= date('Y') ?> RestoPremium. Tous droits réservés.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="mentions.php" class="text-white me-3">Mentions légales</a>
                <a href="politique.php" class="text-white">Politique de confidentialité</a>
            </div>
        </div>
    </div>
</footer>