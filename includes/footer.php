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
<li><a href="mentions-legales.php">Mentions légales</a></li>
<li><a href="cgv.php">CGV</a></li>
</ul>
</div>

<div class="col-md-3">
<h5 class="footer-title">Horaires</h5>
<p class="mb-1">Lundi - Vendredi : 9h - 19h</p>
<p class="mb-1">Samedi : 10h - 18h</p>
<p class="mb-1">Dimanche : 10h - 14h</p>
</div>

<!-- CONTACT -->
<div class="col-md-3">
<h5 class="footer-title">Contact</h5>
<p>
📍 11 Rue Verteuil 33000 Bordeaux, France<br>
📞 04 12 34 56 78<br>
📧 contact@vite-gourmand.fr
</p>
</div>

<!-- MAP -->
<div class="col-md-3">
<h5 class="footer-title">Localisation</h5>

<iframe 
    src="https://www.google.com/maps?q=Bordeaux&output=embed"
    width="100%" 
    height="150" 
    style="border:0; border-radius:10px;" 
    allowfullscreen="" 
    loading="lazy">
</iframe>

</div>

</div>

<hr>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-center">

<p class="mb-0">
© <?= date('Y') ?> Vite & Gourmand
</p>

<p class="mb-0 small text-light">
Site réalisé avec ❤️
</p>

</div>

</div>

</footer>

<!-- COOKIE BANNER -->
<div id="cookie-banner" class="cookie-banner">
    <div class="cookie-content">
        <p>Ce site utilise des cookies pour améliorer votre expérience.</p>
        <div class="cookie-buttons">
            <button id="accept-cookies" class="btn btn-success btn-sm">Accepter</button>
            <button id="refuse-cookies" class="btn btn-danger btn-sm">Refuser</button>
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

    document.addEventListener("mousemove", (e) => {

        let x = (e.clientX / window.innerWidth - 0.5) * 20;
        let y = (e.clientY / window.innerHeight - 0.5) * 20;

        slides.forEach(slide => {
            slide.style.transform = `scale(1.1) translate(${x}px, ${y}px)`;
        });

    });

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

});

</body>
</html>
