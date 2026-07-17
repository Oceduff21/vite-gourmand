</main> <!-- fermeture main -->

<footer class="footer">

<div class="container">

<div class="row gy-4">

<!-- ABOUT -->
<div class="col-md-3">
<h5 class="footer-title">Vite & Gourmand</h5>
<p>
Entreprise familiale spécialisée dans la restauration événementielle.<br>
Des menus raffinés pour tous vos événements.
</p>
</div>

<!-- LIENS -->
<div class="col-md-3">
<h5 class="footer-title">Liens utiles</h5>
<ul class="list-unstyled footer-links">
<li><a href="menus.php">Nos menus</a></li>
<li><a href="contact.php">Contact</a></li>
<li><a href="faq.php">FAQ</a></li>
<li><a href="politique-confidentialite.php">Politique de confidentialite</a></li>
<li><a href="accessibilite.php">Accessibilite</a></li>
<li><a href="mentions-legales.php">Mentions légales</a></li>
<li><a href="cgv.php">CGV</a></li>
</ul>
</div>

<div class="col-md-3">
<h5 class="footer-title">Horaires</h5>
<?php
require_once __DIR__ . '/site-settings.php';
foreach (getFooterHoraires() as $ligneHoraire):
?>
<p class="mb-1"><?= htmlspecialchars($ligneHoraire) ?></p>
<?php endforeach; ?>
</div>

<!-- CONTACT -->
<div class="col-md-3">
<h5 class="footer-title">Contact</h5>
<p>
<span class="visually-hidden">Adresse : </span>11 Rue Verteuil 33000 Bordeaux, France<br>
<span class="visually-hidden">Telephone : </span><a href="tel:+33412345678" class="text-decoration-none" style="color:inherit">04 12 34 56 78</a><br>
<span class="visually-hidden">Email : </span><a href="mailto:contact@vite-gourmand.fr" class="text-decoration-none" style="color:inherit">contact@vite-gourmand.fr</a>
</p>
</div>

<!-- MAP -->
<div class="col-md-3">
<h5 class="footer-title">Localisation</h5>
<p class="small mb-2">
    <a href="https://www.google.com/maps/search/?api=1&amp;query=11+Rue+Verteuil+33000+Bordeaux" class="text-decoration-none" style="color:inherit">
        Voir l'adresse sur Google Maps
    </a>
</p>
<iframe
    title="Carte interactive — 11 Rue Verteuil, 33000 Bordeaux"
    src="https://www.google.com/maps?q=11+Rue+Verteuil+33000+Bordeaux&amp;output=embed"
    width="100%"
    height="150"
    style="border:0; border-radius:10px;"
    loading="lazy">
</iframe>

</div>

</div>

<hr>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-center">

<p class="mb-0">
© <?= date('Y') ?> Vite & Gourmand
</p>

<p class="mb-0 small footer-credits">
Site realise pour Vite &amp; Gourmand
</p>

</div>

</div>

</footer>

<nav class="scroll-jump-nav" aria-label="Navigation rapide sur la page">
    <button type="button" id="scroll-to-top" class="scroll-jump-btn" hidden aria-label="Remonter en haut de la page">
        <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
    </button>
    <button type="button" id="scroll-to-bottom" class="scroll-jump-btn" hidden aria-label="Aller en bas de la page">
        <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
    </button>
</nav>
<div id="page-end" class="visually-hidden" tabindex="-1">Fin de la page</div>

<!-- COOKIE BANNER -->
<div id="cookie-banner" class="cookie-banner" role="region" aria-label="Information sur les cookies" hidden>
    <div class="cookie-content">
        <p id="cookie-banner-text">Ce site utilise des cookies pour ameliorer votre experience de navigation.</p>
        <div class="cookie-buttons">
            <button type="button" id="accept-cookies" class="btn btn-success btn-sm">Accepter</button>
            <button type="button" id="refuse-cookies" class="btn btn-outline-light btn-sm">Refuser</button>
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>

document.addEventListener("DOMContentLoaded", function(){

/* ================= HERO ================= */

let slides = document.querySelectorAll(".hero-slide");

if(slides.length > 0){

    let index = 0;
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    const texts = [
        {
            title:"Une cuisine élégante",
            text:"Vite & Gourmand propose des prestations culinaires haut de gamme pour vos événements privés et professionnels."
        },
        {
            title:"Traiteur d’exception",
            text:"Des menus raffinés pour vos événements"
        },
        {
            title:"Mariages & Réceptions",
            text:"Une expérience gastronomique unique"
        },
        {
            title:"Événements sur mesure",
            text:"Cuisine élégante et service premium"
        }
    ];

    const title = document.getElementById("hero-title");
    const text = document.getElementById("hero-text");

    function showSlide(i){

        slides.forEach(s => s.classList.remove("active"));
        slides[i].classList.add("active");

        if(title && text){
            title.style.animation = "none";
            text.style.animation = "none";
            void title.offsetWidth;

            title.innerText = texts[i].title;
            text.innerText = texts[i].text;

            title.style.animation = "fadeUp 1s ease forwards";
            text.style.animation = "fadeUp 1s ease forwards";
        }
    }

    setInterval(() => {
        if (reduceMotion) return;
        index = (index + 1) % slides.length;
        showSlide(index);
    }, 5000);

    const nextBtn = document.querySelector(".next");
    const prevBtn = document.querySelector(".prev");

    if(nextBtn){
        nextBtn.onclick = () => {
            index = (index + 1) % slides.length;
            showSlide(index);
        };
    }

    if(prevBtn){
        prevBtn.onclick = () => {
            index = (index - 1 + slides.length) % slides.length;
            showSlide(index);
        };
    }

    if(!reduceMotion){
        document.addEventListener("mousemove", (e) => {

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

const reveals = document.querySelectorAll(".reveal");

function revealOnScroll(){
    reveals.forEach(el => {
        const windowHeight = window.innerHeight;
        const elementTop = el.getBoundingClientRect().top;

        if(elementTop < windowHeight - 100){
            el.classList.add("active");
        }
    });
}

window.addEventListener("scroll", revealOnScroll);

/* ================= NAVBAR ================= */

window.addEventListener("scroll", function(){
    let navbar = document.querySelector(".custom-navbar");

    if(navbar){
        if(window.scrollY > 50){
            navbar.classList.add("navbar-scrolled");
        } else {
            navbar.classList.remove("navbar-scrolled");
        }
    }
});

/* ================= COOKIES ================= */

const banner = document.getElementById("cookie-banner");

if(banner && !localStorage.getItem("cookieConsent")){
    banner.hidden = false;
    banner.style.display = "block";
}

const acceptBtn = document.getElementById("accept-cookies");
const refuseBtn = document.getElementById("refuse-cookies");

if(acceptBtn){
    acceptBtn.onclick = () => {
        localStorage.setItem("cookieConsent","accepted");
        banner.style.display = "none";
    };
}

if(refuseBtn){
    refuseBtn.onclick = () => {
        localStorage.setItem("cookieConsent","refused");
        banner.style.display = "none";
    };
}

document.querySelectorAll('[data-password-target]').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var input = document.getElementById(cb.dataset.passwordTarget);
        if (!input) return;
        input.type = cb.checked ? 'text' : 'password';
    });
});

});

</script>
<script>
(function(){'use strict';function initScrollJump(){var toTop=document.getElementById('scroll-to-top');var toBottom=document.getElementById('scroll-to-bottom');if(!toTop||!toBottom)return;var edgeThreshold=80,scrollUpThreshold=280,reduceMotion=window.matchMedia('(prefers-reduced-motion: reduce)').matches;function getScrollY(){return window.pageYOffset||document.documentElement.scrollTop||(document.body?document.body.scrollTop:0)||0;}function getScrollMax(){var docHeight=Math.max(document.documentElement.scrollHeight,document.body?document.body.scrollHeight:0);return Math.max(0,docHeight-window.innerHeight);}function isScrollable(){return getScrollMax()>edgeThreshold;}function updateScrollButtons(){if(!isScrollable()){toTop.hidden=true;toBottom.hidden=true;return;}var y=getScrollY(),maxY=getScrollMax(),nearTop=y<=edgeThreshold,nearBottom=y>=maxY-edgeThreshold,scrolledDown=y>scrollUpThreshold;toBottom.hidden=!nearTop;toTop.hidden=!(nearBottom||scrolledDown);}function scrollToPosition(top){window.scrollTo({top:Math.max(0,top),behavior:reduceMotion?'auto':'smooth'});}toTop.addEventListener('click',function(){scrollToPosition(0);});toBottom.addEventListener('click',function(){var pageEnd=document.getElementById('page-end');if(pageEnd){pageEnd.scrollIntoView({behavior:reduceMotion?'auto':'smooth',block:'end'});}else{scrollToPosition(getScrollMax());}});window.addEventListener('scroll',updateScrollButtons,{passive:true});window.addEventListener('resize',updateScrollButtons);window.addEventListener('load',updateScrollButtons);updateScrollButtons();setTimeout(updateScrollButtons,300);}if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',initScrollJump);}else{initScrollJump();}})();
</script>

</body>
</html>
