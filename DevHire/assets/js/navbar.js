/**
 * Navbar Mobile Menu Functionality
 * Premium SaaS Design
 */

document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburger');
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');

    if (!hamburger || !mobileMenuOverlay) {
        return;
    }

    // Toggle mobile menu
    hamburger.addEventListener('click', function() {
        mobileMenuOverlay.classList.toggle('active');
        hamburger.classList.toggle('active');
        document.body.style.overflow = mobileMenuOverlay.classList.contains('active') ? 'hidden' : '';
    });

    // Close menu when clicking outside
    mobileMenuOverlay.addEventListener('click', function(e) {
        if (e.target === mobileMenuOverlay) {
            closeMobileMenu();
        }
    });

    // Close menu when clicking links
    const mobileLinks = mobileMenuOverlay.querySelectorAll('.mobile-menu-link');
    mobileLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            closeMobileMenu();
        });
    });

    // Close menu on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileMenuOverlay.classList.contains('active')) {
            closeMobileMenu();
        }
    });

    function closeMobileMenu() {
        mobileMenuOverlay.classList.remove('active');
        hamburger.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Set active state for current page
    setActiveNavLink();
});

function setActiveNavLink() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(function(link) {
        const linkPath = new URL(link.href).pathname;
        
        // Check if current path matches link path
        if (currentPath === linkPath || 
            (currentPath.includes(linkPath) && linkPath !== '/') ||
            (currentPath === '/' && linkPath.includes('index.php'))) {
            link.classList.add('active');
        }
    });
}
