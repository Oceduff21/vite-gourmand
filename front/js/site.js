/**
 * FRONT — site.js
 * UI globale : hero carousel, reveal scroll, navbar, cookies, password toggle.
 * Couche presentation uniquement (pas de logique metier serveur).
 */
document.addEventListener('DOMContentLoaded', function () {

    /* ================= HERO ================= */

    let slides = document.querySelectorAll('.hero-slide');

    if (slides.length > 0) {

        let index = 0;
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const texts = [
            {
                title: 'Une cuisine élégante',
                text: 'Vite & Gourmand propose des prestations culinaires haut de gamme pour vos événements privés et professionnels.'
            },
            {
                title: 'Traiteur d’exception',
                text: 'Des menus raffinés pour vos événements'
            },
            {
                title: 'Mariages & Réceptions',
                text: 'Une expérience gastronomique unique'
            },
            {
                title: 'Événements sur mesure',
                text: 'Cuisine élégante et service premium'
            }
        ];

        const title = document.getElementById('hero-title');
        const text = document.getElementById('hero-text');

        function showSlide(i) {
            slides.forEach(s => s.classList.remove('active'));
            slides[i].classList.add('active');

            if (title && text) {
                title.style.animation = 'none';
                text.style.animation = 'none';
                void title.offsetWidth;

                title.innerText = texts[i].title;
                text.innerText = texts[i].text;

                title.style.animation = 'fadeUp 1s ease forwards';
                text.style.animation = 'fadeUp 1s ease forwards';
            }
        }

        setInterval(() => {
            if (reduceMotion) return;
            index = (index + 1) % slides.length;
            showSlide(index);
        }, 5000);

        const nextBtn = document.querySelector('.next');
        const prevBtn = document.querySelector('.prev');

        if (nextBtn) {
            nextBtn.onclick = () => {
                index = (index + 1) % slides.length;
                showSlide(index);
            };
        }

        if (prevBtn) {
            prevBtn.onclick = () => {
                index = (index - 1 + slides.length) % slides.length;
                showSlide(index);
            };
        }

        if (!reduceMotion) {
            document.addEventListener('mousemove', (e) => {
                let x = (e.clientX / window.innerWidth - 0.5) * 20;
                let y = (e.clientY / window.innerHeight - 0.5) * 20;
                slides.forEach(slide => {
                    slide.style.transform = `scale(1.1) translate(${x}px, ${y}px)`;
                });
            });
        }

        showSlide(index);
    }

    /* ================= REVEAL ================= */

    const reveals = document.querySelectorAll('.reveal');

    function revealOnScroll() {
        reveals.forEach(el => {
            const windowHeight = window.innerHeight;
            const elementTop = el.getBoundingClientRect().top;
            if (elementTop < windowHeight - 100) {
                el.classList.add('active');
            }
        });
    }

    window.addEventListener('scroll', revealOnScroll);

    /* ================= NAVBAR ================= */

    window.addEventListener('scroll', function () {
        let navbar = document.querySelector('.custom-navbar');
        if (navbar) {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        }
    });

    /* ================= COOKIES ================= */

    const banner = document.getElementById('cookie-banner');

    if (banner && !localStorage.getItem('cookieConsent')) {
        banner.hidden = false;
        banner.style.display = 'block';
    }

    const acceptBtn = document.getElementById('accept-cookies');
    const refuseBtn = document.getElementById('refuse-cookies');

    if (acceptBtn) {
        acceptBtn.onclick = () => {
            localStorage.setItem('cookieConsent', 'accepted');
            banner.style.display = 'none';
        };
    }

    if (refuseBtn) {
        refuseBtn.onclick = () => {
            localStorage.setItem('cookieConsent', 'refused');
            banner.style.display = 'none';
        };
    }

    document.querySelectorAll('[data-password-target]').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var input = document.getElementById(cb.dataset.passwordTarget);
            if (!input) return;
            input.type = cb.checked ? 'text' : 'password';
        });
    });
});
