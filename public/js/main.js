// =============================================
//  DAMIAN CAFE - Main JavaScript
// =============================================

document.addEventListener('DOMContentLoaded', function () {

  // --- Navbar scroll effect ---
  const navbar = document.getElementById('mainNavbar');
  if (navbar) {
    window.addEventListener('scroll', function () {
      navbar.classList.toggle('scrolled', window.scrollY > 50);
    });
  }

  // --- Sync carousel dots ---
  const testimonialCarousel = document.getElementById('testimoniCarousel');
  if (testimonialCarousel) {
    testimonialCarousel.addEventListener('slid.bs.carousel', function (e) {
      document.querySelectorAll('.dc-dot').forEach((dot, i) => {
        dot.classList.toggle('active', i === e.to);
      });
    });
  }

  // --- Fade-in on scroll ---
  const fadeEls = document.querySelectorAll('.dc-layanan-card, .dc-menu-card, .dc-stat');
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    fadeEls.forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      observer.observe(el);
    });
  }

});
