(function () {
    const cfg = window.MODIFIER_PLATS_CONFIG;
    if (!cfg) return;

    const TYPES = cfg.types || [];
    const MIN_GUESTS = cfg.minGuests || 1;
    const PLAT_LABELS = { entree: 'Entrees', plat: 'Plats', dessert: 'Desserts' };

    let cart = {
        invites: cfg.invites || MIN_GUESTS,
        entree: { ...(cfg.savedCart?.entree || {}) },
        plat: { ...(cfg.savedCart?.plat || {}) },
        dessert: { ...(cfg.savedCart?.dessert || {}) },
    };

    const form = document.getElementById('modifier-commande-form');
    const hiddenCart = document.getElementById('plat_cart');
    const nbInput = document.querySelector('input[name="nb_personnes"]');
    const validationMsg = document.getElementById('plat-validation-msg');

    function sumType(type) {
        return Object.values(cart[type] || {}).reduce((a, b) => a + b, 0);
    }

    function sumTypeExcept(type, excludeId) {
        let sum = 0;
        document.querySelectorAll('.plat-slider[data-type="' + type + '"]').forEach(s => {
            if (String(s.dataset.id) !== String(excludeId)) {
                sum += parseInt(s.value, 10) || 0;
            }
        });
        return sum;
    }

    function updateSliderLimits(type) {
        document.querySelectorAll('.plat-slider[data-type="' + type + '"]').forEach(s => {
            const id = s.dataset.id;
            const others = sumTypeExcept(type, id);
            const maxAllowed = Math.max(0, cart.invites - others);
            s.max = maxAllowed;
            let val = parseInt(s.value, 10) || 0;
            if (val > maxAllowed) {
                val = maxAllowed;
                s.value = val;
                if (val <= 0) delete cart[type][id];
                else cart[type][id] = val;
                const lbl = document.getElementById('label-' + type + '-' + id);
                if (lbl) lbl.textContent = val;
            }
        });
    }

    function updateAllSliderLimits() {
        TYPES.forEach(updateSliderLimits);
    }

    function syncGuestLabels() {
        document.querySelectorAll('.guest-count-label').forEach(el => {
            el.textContent = cart.invites;
        });
    }

    function updateChoice(type, id, val) {
        val = parseInt(val, 10) || 0;
        const maxAllowed = Math.max(0, cart.invites - sumTypeExcept(type, id));
        if (val > maxAllowed) val = maxAllowed;

        const slider = document.getElementById(type + '-' + id);
        if (slider) slider.value = val;

        if (val <= 0) delete cart[type][id];
        else cart[type][id] = val;

        const lbl = document.getElementById('label-' + type + '-' + id);
        if (lbl) lbl.textContent = val;

        updateAllSliderLimits();
        updateUI();
    }

    function getRegimeBucket(regime) {
        const r = (regime || 'classique').toLowerCase();
        if (['vegan', 'vegetarien'].includes(r)) return 'veg';
        if (['sans gluten', 'sans lactose', 'halal'].includes(r)) return 'diet';
        return 'protein';
    }

    function getOrderedSliders(type) {
        const sliders = Array.from(document.querySelectorAll('.plat-slider[data-type="' + type + '"]'));
        const buckets = { veg: [], diet: [], protein: [] };
        sliders.forEach(s => buckets[getRegimeBucket(s.dataset.regime)].push(s));
        const ordered = [];
        const maxB = Math.max(buckets.veg.length, buckets.diet.length, buckets.protein.length, 1);
        for (let i = 0; i < maxB; i++) {
            if (buckets.veg[i]) ordered.push(buckets.veg[i]);
            if (buckets.diet[i]) ordered.push(buckets.diet[i]);
            if (buckets.protein[i]) ordered.push(buckets.protein[i]);
        }
        return ordered.length ? ordered : sliders;
    }

    function autoFillCategory(type) {
        cart[type] = {};
        const ordered = getOrderedSliders(type);
        ordered.forEach(s => { s.value = 0; });
        let remaining = cart.invites;
        let i = 0;
        while (remaining > 0 && ordered.length) {
            const s = ordered[i % ordered.length];
            s.value = (parseInt(s.value, 10) || 0) + 1;
            remaining--;
            i++;
        }
        ordered.forEach(s => {
            const val = parseInt(s.value, 10) || 0;
            const id = s.dataset.id;
            if (val <= 0) delete cart[type][id];
            else cart[type][id] = val;
            const lbl = document.getElementById('label-' + type + '-' + id);
            if (lbl) lbl.textContent = val;
        });
        updateAllSliderLimits();
        updateUI();
    }

    function validatePlats() {
        for (const type of TYPES) {
            if (sumType(type) !== cart.invites) {
                return 'Repartissez exactement ' + cart.invites + ' invites pour les '
                    + (PLAT_LABELS[type] || type).toLowerCase()
                    + ' (' + sumType(type) + '/' + cart.invites + ').';
            }
        }
        return '';
    }

    function showValidation(msg) {
        if (!validationMsg) return;
        validationMsg.textContent = msg;
        validationMsg.classList.toggle('d-none', !msg);
    }

    function updateUI() {
        TYPES.forEach(type => {
            const sum = sumType(type);
            const ok = sum === cart.invites;
            const badge = document.getElementById('status-' + type);
            if (badge) {
                if (ok) {
                    badge.innerHTML = '<span class="badge bg-success">' + sum + ' / ' + cart.invites + ' OK</span>';
                } else if (sum > cart.invites) {
                    badge.innerHTML = '<span class="badge bg-danger">' + sum + ' / ' + cart.invites + ' (trop)</span>';
                } else {
                    badge.innerHTML = '<span class="badge bg-warning text-dark">' + sum + ' / ' + cart.invites + '</span>';
                }
            }
            const bar = document.getElementById('progress-' + type);
            if (bar) {
                const pct = cart.invites > 0 ? Math.min(100, (sum / cart.invites) * 100) : 0;
                bar.style.width = pct + '%';
                bar.className = 'progress-bar ' + (ok ? 'bg-success' : (sum > cart.invites ? 'bg-danger' : 'bg-warning'));
            }
        });
        showValidation('');
    }

    function restoreSlidersFromCart() {
        document.querySelectorAll('.plat-slider').forEach(s => {
            const type = s.dataset.type;
            const id = s.dataset.id;
            const val = (cart[type] && cart[type][id]) ? cart[type][id] : 0;
            s.value = val;
            const lbl = document.getElementById('label-' + type + '-' + id);
            if (lbl) lbl.textContent = val;
        });
    }

    if (nbInput) {
        nbInput.addEventListener('input', () => {
            cart.invites = Math.max(MIN_GUESTS, parseInt(nbInput.value, 10) || MIN_GUESTS);
            nbInput.value = cart.invites;
            syncGuestLabels();
            updateAllSliderLimits();
            updateUI();
        });
    }

    document.querySelectorAll('.plat-slider').forEach(s => {
        s.addEventListener('input', () => updateChoice(s.dataset.type, s.dataset.id, s.value));
    });

    document.querySelectorAll('.btn-autofill-cat').forEach(btn => {
        btn.addEventListener('click', () => autoFillCategory(btn.dataset.type));
    });

    if (form) {
        form.addEventListener('submit', (e) => {
            cart.invites = Math.max(MIN_GUESTS, parseInt(nbInput?.value, 10) || MIN_GUESTS);
            const err = validatePlats();
            if (err) {
                e.preventDefault();
                showValidation(err);
                const firstBad = TYPES.find(t => sumType(t) !== cart.invites);
                if (firstBad) {
                    document.getElementById('section-' + firstBad)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                return;
            }
            if (hiddenCart) {
                hiddenCart.value = JSON.stringify({
                    invites: cart.invites,
                    entree: cart.entree,
                    plat: cart.plat,
                    dessert: cart.dessert,
                });
            }
        });
    }

    restoreSlidersFromCart();
    syncGuestLabels();
    updateAllSliderLimits();
    updateUI();
})();
