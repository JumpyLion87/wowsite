// WoW Header Navigation
document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.getElementById('navToggle');
    const navClose = document.getElementById('navClose');
    const mainNav = document.getElementById('mainNav');
    
    // Toggle mobile navigation
    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function() {
            mainNav.classList.add('nav-open');
            document.body.style.overflow = 'hidden';
        });
    }
    
    // Close mobile navigation
    if (navClose && mainNav) {
        navClose.addEventListener('click', function() {
            mainNav.classList.remove('nav-open');
            document.body.style.overflow = '';
        });
    }
    
    // Close navigation when clicking outside
    document.addEventListener('click', function(event) {
        if (mainNav && mainNav.classList.contains('nav-open') && 
            !mainNav.contains(event.target) && 
            event.target !== navToggle) {
            mainNav.classList.remove('nav-open');
            document.body.style.overflow = '';
        }
    });
    
    // Close navigation on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && mainNav && mainNav.classList.contains('nav-open')) {
            mainNav.classList.remove('nav-open');
            document.body.style.overflow = '';
        }
    });
    
    // Add hover effects for desktop
    const navLinks = document.querySelectorAll('.nav-menu a, .nav-auth a, .logout-btn');
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
    
    // Add active class based on current page
    const currentPath = window.location.pathname;
    const navItems = document.querySelectorAll('.nav-menu a, .nav-auth a');
    
    navItems.forEach(item => {
        const href = item.getAttribute('href');
        if (href && currentPath === href) {
            item.classList.add('active');
        }
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    console.log('WoW Header navigation initialized');
});