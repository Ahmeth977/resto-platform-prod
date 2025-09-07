document.addEventListener('DOMContentLoaded', function() {
    // Animation du menu mobile
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    navbarToggler.addEventListener('click', function() {
        const expanded = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', !expanded);
        
        if (!expanded) {
            navbarCollapse.style.height = '100vh';
            navbarCollapse.style.opacity = '1';
        } else {
            navbarCollapse.style.height = '0';
            navbarCollapse.style.opacity = '0';
        }
    });

    // Effet sticky au scroll
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 100) {
            navbar.classList.add('navbar-scrolled');
            navbar.classList.remove('bg-dark');
            navbar.classList.add('bg-dark-transparent');
        } else {
            navbar.classList.remove('navbar-scrolled');
            navbar.classList.add('bg-dark');
            navbar.classList.remove('bg-dark-transparent');
        }
    });

    // Fermer le menu après clic sur mobile
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 992) {
                navbarToggler.click();
            }
        });
    });
});