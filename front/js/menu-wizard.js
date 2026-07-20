/**
 * FRONT — menu-wizard.js
 * Wizard commande (etapes, sliders, validation UI).
 * Config dynamique injectee par menu.php (constantes PHP).
 */
let cart = { invites: MIN_GUESTS, enfants: 0, entree: {}, plat: {}, dessert: {}, boissons: {} };
let currentStepIndex = 0;

function sumBoissons() {
    let total = 0;
    document.querySelectorAll('.boisson-qty').forEach(input => {
        const qty = parseInt(input.value, 10) || 0;
        total += qty * (parseFloat(input.dataset.price) || 0);
    });
    return total;
}

function syncBoissonsCart() {
    cart.boissons = {};
    document.querySelectorAll('.boisson-qty').forEach(input => {
        const qty = parseInt(input.value, 10) || 0;
        if (qty > 0) cart.boissons[input.dataset.id] = qty;
    });
}

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

        const card = document.getElementById('card-' + type + '-' + id);
        if (card) card.classList.toggle('plat-slider-capped', maxAllowed === 0 && val === 0);
    });
}

function updateAllSliderLimits() {
    TYPES.forEach(updateSliderLimits);
}

function syncSlidersMax() {
    document.querySelectorAll('.guest-count-label').forEach(el => { el.textContent = cart.invites; });
    const rg = document.getElementById('recap-guests');
    if (rg) rg.textContent = cart.invites;
    const ra = document.getElementById('recap-adultes');
    if (ra) ra.textContent = Math.max(0, cart.invites - cart.enfants);
    updateAllSliderLimits();
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

function applyDistribution(type, ordered, total) {
    cart[type] = {};
    ordered.forEach(s => { s.value = 0; });
    let i = 0;
    while (total > 0 && ordered.length) {
        const s = ordered[i % ordered.length];
        s.value = (parseInt(s.value, 10) || 0) + 1;
        total--;
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
}

function syncInvitesFromInput() {
    const inp = document.getElementById('invites');
    if (!inp) return;
    cart.invites = Math.max(MIN_GUESTS, parseInt(inp.value, 10) || MIN_GUESTS);
    inp.value = cart.invites;
    if (cart.enfants > cart.invites) {
        cart.enfants = cart.invites;
        const nbE = document.getElementById('nb-enfants');
        if (nbE) nbE.value = cart.enfants;
    }
    syncSlidersMax();
}

function restoreSavedCart(saved) {
    if (!saved || typeof saved !== 'object') return false;

    cart.invites = Math.max(MIN_GUESTS, parseInt(saved.invites, 10) || MIN_GUESTS);
    const invitesInput = document.getElementById('invites');
    if (invitesInput) invitesInput.value = cart.invites;

    cart.enfants = Math.min(cart.invites, Math.max(0, parseInt(saved.enfants, 10) || 0));
    if (HAS_ENFANT_OPTION) {
        const hasEnfants = document.getElementById('has-enfants');
        const nbEnfants = document.getElementById('nb-enfants');
        if (hasEnfants) hasEnfants.checked = cart.enfants > 0;
        if (nbEnfants) nbEnfants.value = cart.enfants;
    }

    cart.entree = {};
    cart.plat = {};
    cart.dessert = {};
    cart.boissons = {};
    document.querySelectorAll('.plat-slider').forEach(s => { s.value = 0; });
    document.querySelectorAll('.boisson-qty').forEach(input => { input.value = 0; });

    TYPES.forEach(type => {
        if (!saved[type] || typeof saved[type] !== 'object') return;
        Object.entries(saved[type]).forEach(([platId, qty]) => {
            qty = parseInt(qty, 10) || 0;
            if (qty <= 0) return;
            const slider = document.getElementById(type + '-' + platId);
            if (!slider) return;
            slider.value = qty;
            cart[type][platId] = qty;
            const lbl = document.getElementById('label-' + type + '-' + platId);
            if (lbl) lbl.textContent = qty;
        });
    });

    if (saved.boissons && typeof saved.boissons === 'object') {
        Object.entries(saved.boissons).forEach(([boissonId, qty]) => {
            qty = parseInt(qty, 10) || 0;
            if (qty <= 0) return;
            const input = document.querySelector('.boisson-qty[data-id="' + boissonId + '"]');
            if (!input) return;
            input.value = qty;
            cart.boissons[boissonId] = qty;
        });
    }

    syncSlidersMax();
    updateUI();
    return true;
}

function autoFillCategory(type) {
    applyDistribution(type, getOrderedSliders(type), cart.invites);
    updateUI();
}

function syncEnfantsUI() {
    if (!HAS_ENFANT_OPTION) return;
    const has = document.getElementById('has-enfants')?.checked;
    const wrap = document.getElementById('enfants-wrap');
    const info = document.getElementById('enfants-info');
    if (wrap) wrap.style.display = has ? '' : 'none';
    if (info) info.style.display = has ? '' : 'none';
    if (!has) {
        cart.enfants = 0;
        const inp = document.getElementById('nb-enfants');
        if (inp) inp.value = 0;
    } else {
        cart.enfants = Math.min(cart.invites, parseInt(document.getElementById('nb-enfants')?.value, 10) || 0);
    }
    const sum = document.getElementById('enfants-summary');
    if (sum) sum.textContent = cart.enfants;
    const line = document.getElementById('recap-enfants-line');
    if (line) line.classList.toggle('d-none', cart.enfants <= 0);
}

function updateChoice(type, id, val) {
    val = parseInt(val, 10) || 0;
    const maxAllowed = Math.max(0, cart.invites - sumTypeExcept(type, id));
    if (val > maxAllowed) val = maxAllowed;

    const slider = document.getElementById(type + '-' + id);
    if (slider) {
        slider.value = val;
        slider.setAttribute('aria-valuenow', String(val));
        slider.setAttribute('aria-valuetext', val + (val > 1 ? ' invites' : ' invite'));
    }

    if (val <= 0) delete cart[type][id];
    else cart[type][id] = val;

    const lbl = document.getElementById('label-' + type + '-' + id);
    if (lbl) lbl.textContent = val;

    updateAllSliderLimits();
    updateUI();
}

function validateStep(stepId) {
    if (stepId === 'invites') {
        return cart.invites >= MIN_GUESTS && cart.enfants >= 0 && cart.enfants <= cart.invites;
    }
    if (TYPES.includes(stepId)) {
        return sumType(stepId) === cart.invites;
    }
    if (stepId === 'boissons') return true;
    if (stepId === 'recap') {
        return TYPES.every(t => sumType(t) === cart.invites);
    }
    return true;
}

function stepMessage(stepId) {
    if (stepId === 'invites') return 'Indiquez au moins ' + MIN_GUESTS + ' invites (enfants inclus, max ' + cart.invites + ').';
    if (TYPES.includes(stepId)) {
        const sum = sumType(stepId);
        return 'Repartissez exactement ' + cart.invites + ' invites pour les ' + (PLAT_TYPE_LABELS[stepId] || stepId).toLowerCase() + ' (' + sum + '/' + cart.invites + ').';
    }
    return 'Completez les etapes precedentes.';
}

function buildDetailedRecap() {
    let html = '';
    TYPES.forEach(type => {
        html += '<div class="recap-block mb-3"><h6 class="fw-bold text-uppercase small text-muted mb-2">' + PLAT_TYPE_LABELS[type] + '</h6><ul class="list-group list-group-flush">';
        let hasItems = false;
        document.querySelectorAll('.plat-slider[data-type="' + type + '"]').forEach(slider => {
            const qty = parseInt(slider.value, 10) || 0;
            if (qty > 0) {
                hasItems = true;
                html += '<li class="list-group-item d-flex justify-content-between px-0"><span>' + (slider.dataset.nom || 'Plat') + '</span><strong>' + qty + ' invite(s)</strong></li>';
            }
        });
        if (!hasItems) html += '<li class="list-group-item px-0 text-muted">Aucune selection</li>';
        html += '</ul></div>';
    });
    const boissonExtra = sumBoissons();
    html += '<div class="recap-block mb-2"><h6 class="fw-bold text-uppercase small text-muted mb-2">Boissons</h6><ul class="list-group list-group-flush">';
    let hasBoisson = false;
    document.querySelectorAll('.boisson-qty').forEach(input => {
        const qty = parseInt(input.value, 10) || 0;
        if (qty > 0) {
            hasBoisson = true;
            const prix = (parseFloat(input.dataset.price) || 0) * qty;
            html += '<li class="list-group-item d-flex justify-content-between px-0"><span>' + (input.dataset.nom || 'Boisson') + '</span><strong>' + qty + ' x ' + prix.toFixed(2) + ' EUR</strong></li>';
        }
    });
    if (!hasBoisson) html += '<li class="list-group-item px-0 text-muted">Aucune (optionnel)</li>';
    html += '</ul></div>';
    if (cart.enfants > 0 && HAS_ENFANT_OPTION) {
        html += '<div class="recap-block mb-2 alert alert-info py-2 small"><i class="fa-solid fa-child me-1"></i> '
            + cart.enfants + ' menu(s) enfant a prevoir (' + (cart.enfants * PRIX_ENFANT).toFixed(2) + ' EUR)</div>';
    }
    return html;
}

function setActionButtonState(btn, ok) {
    if (!btn) return;
    btn.disabled = false;
    btn.setAttribute('aria-disabled', ok ? 'false' : 'true');
    if (btn.id === 'btn-commander') {
        btn.classList.toggle('btn-success', ok);
        btn.classList.toggle('btn-secondary', !ok);
        btn.title = ok ? '' : 'Cliquez pour revenir au champ manquant';
    } else {
        btn.classList.toggle('btn-primary', ok);
        btn.classList.toggle('btn-secondary', !ok);
    }
}

function showValidationMsg(text) {
    const msg = document.getElementById('validation-msg');
    if (!msg) return;
    msg.textContent = text;
    msg.classList.remove('d-none');
}

function hideValidationMsg() {
    document.getElementById('validation-msg')?.classList.add('d-none');
}

function clearFieldErrors() {
    document.querySelectorAll('.wizard-field-error').forEach(el => el.classList.remove('wizard-field-error'));
}

function markFieldError(el) {
    if (!el) return;
    el.classList.add('wizard-field-error');
    el.addEventListener('input', () => el.classList.remove('wizard-field-error'), { once: true });
    el.addEventListener('change', () => el.classList.remove('wizard-field-error'), { once: true });
}

function focusCategoryField(type) {
    const sum = sumType(type);
    const needed = cart.invites - sum;
    let target = null;

    document.querySelectorAll('.plat-slider[data-type="' + type + '"]').forEach(slider => {
        if (target) return;
        const val = parseInt(slider.value, 10) || 0;
        const maxAllowed = parseInt(slider.max, 10) || 0;
        if (needed > 0 && val < maxAllowed) target = slider;
        else if (needed < 0 && val > 0) target = slider;
    });

    if (!target) {
        target = document.querySelector('.plat-slider[data-type="' + type + '"]');
    }

    if (target) {
        markFieldError(target);
        setTimeout(() => {
            target.focus();
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 250);
    }
}

function focusStepField(stepId) {
    if (stepId === 'invites') {
        const inp = document.getElementById('invites');
        markFieldError(inp);
        inp?.focus();
        inp?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    if (TYPES.includes(stepId)) {
        focusCategoryField(stepId);
        return;
    }
    document.getElementById('step-' + stepId)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function shakePanel(stepId) {
    const panel = document.getElementById('step-' + stepId);
    if (!panel) return;
    panel.classList.add('wizard-shake');
    setTimeout(() => panel.classList.remove('wizard-shake'), 600);
}

function focusFirstInvalidField() {
    clearFieldErrors();
    if (!validateStep('invites')) {
        goToStep(WIZARD_STEPS.indexOf('invites'));
        showValidationMsg(stepMessage('invites'));
        shakePanel('invites');
        focusStepField('invites');
        return false;
    }
    for (const type of TYPES) {
        if (sumType(type) !== cart.invites) {
            goToStep(WIZARD_STEPS.indexOf(type));
            showValidationMsg(stepMessage(type));
            shakePanel(type);
            focusStepField(type);
            return false;
        }
    }
    hideValidationMsg();
    return true;
}

function updateWizardNextButtons() {
    document.querySelectorAll('.btn-wizard-next').forEach(btn => {
        const stepId = btn.dataset.validate || btn.closest('.wizard-panel')?.id?.replace('step-', '') || '';
        const ok = stepId ? validateStep(stepId) : false;
        setActionButtonState(btn, ok);
    });
    const sticky = document.getElementById('sticky-next');
    if (sticky) {
        if (currentStepIndex >= WIZARD_STEPS.length - 1) {
            sticky.classList.add('d-none');
        } else {
            sticky.classList.remove('d-none');
            const ok = validateStep(WIZARD_STEPS[currentStepIndex]);
            setActionButtonState(sticky, ok);
        }
    }
}

function updateStepperUI() {
    document.querySelectorAll('.wizard-step-btn').forEach(btn => {
        const idx = parseInt(btn.dataset.step, 10);
        btn.classList.remove('active', 'done', 'locked');
        btn.removeAttribute('aria-current');
        if (idx === currentStepIndex) {
            btn.classList.add('active');
            btn.setAttribute('aria-current', 'step');
        } else if (idx < currentStepIndex || (idx > 0 && WIZARD_STEPS.slice(0, idx).every(s => validateStep(s)))) {
            btn.classList.add('done');
        } else if (idx > currentStepIndex + 1) {
            btn.classList.add('locked');
        }
        const locked = btn.classList.contains('locked');
        btn.setAttribute('aria-disabled', locked ? 'true' : 'false');
        btn.tabIndex = locked ? -1 : 0;
    });
    const stepId = WIZARD_STEPS[currentStepIndex];
    const stickyLabel = document.getElementById('sticky-step-label');
    if (stickyLabel) {
        stickyLabel.textContent = 'Etape ' + (currentStepIndex + 1) + ' — ' + (WIZARD_LABELS[stepId] || stepId);
    }
    document.querySelectorAll('.wizard-panel').forEach(p => {
        const isActive = p.id === 'step-' + stepId;
        p.classList.toggle('wizard-panel-active', isActive);
        p.toggleAttribute('inert', !isActive);
        p.setAttribute('aria-hidden', isActive ? 'false' : 'true');
    });
    updateWizardNextButtons();
}

function goToStep(index, scroll = true) {
    if (index < 0 || index >= WIZARD_STEPS.length) return;
    currentStepIndex = index;
    updateStepperUI();
    const stepId = WIZARD_STEPS[index];
    const el = document.getElementById('step-' + stepId);
    if (scroll && el) {
        setTimeout(() => {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 80);
    }
    if (stepId === 'invites') {
        const inp = document.getElementById('invites');
        if (inp) setTimeout(() => inp.focus(), 400);
    }
}

function tryGoNext(fromBtn) {
    const currentId = WIZARD_STEPS[currentStepIndex];
    if (!validateStep(currentId)) {
        showValidationMsg(stepMessage(currentId));
        shakePanel(currentId);
        focusStepField(currentId);
        return;
    }
    hideValidationMsg();
    let nextIndex = currentStepIndex + 1;
    if (fromBtn && fromBtn.dataset.next) {
        const target = fromBtn.dataset.next.replace('step-', '');
        const idx = WIZARD_STEPS.indexOf(target);
        if (idx >= 0) nextIndex = idx;
    }
    goToStep(nextIndex);
}

function updateUI() {
    syncBoissonsCart();
    syncEnfantsUI();
    const adultes = Math.max(0, cart.invites - cart.enfants);
    const menuAdultes = adultes * BASE_PRICE;
    const menuEnfants = cart.enfants * PRIX_ENFANT;
    const boissonsExtra = sumBoissons();
    const total = menuAdultes + menuEnfants + boissonsExtra;
    let allValid = true;

    TYPES.forEach(type => {
        const sum = sumType(type);
        const pct = cart.invites > 0 ? Math.min(100, (sum / cart.invites) * 100) : 0;
        const ok = sum === cart.invites;
        const over = sum > cart.invites;
        if (!ok) allValid = false;

        const badge = document.getElementById('status-' + type);
        if (badge) {
            if (ok) {
                badge.innerHTML = '<span class="badge bg-success">' + sum + ' / ' + cart.invites + ' OK</span>';
            } else if (over) {
                badge.innerHTML = '<span class="badge bg-danger">' + sum + ' / ' + cart.invites + ' (trop)</span>';
            } else {
                badge.innerHTML = '<span class="badge bg-warning text-dark">' + sum + ' / ' + cart.invites + ' (manque ' + (cart.invites - sum) + ')</span>';
            }
        }
        const bar = document.getElementById('progress-' + type);
        if (bar) {
            bar.style.width = pct + '%';
            bar.className = 'progress-bar ' + (ok ? 'bg-success' : (over ? 'bg-danger' : 'bg-warning'));
        }
        document.querySelectorAll('.plat-slider[data-type="' + type + '"]').forEach(slider => {
            const card = document.getElementById('card-' + type + '-' + slider.dataset.id);
            const v = parseInt(slider.value, 10) || 0;
            if (card) {
                card.classList.toggle('plat-active', v > 0);
            }
        });

        const hint = document.getElementById('hint-' + type);
        if (hint) {
            if (ok) {
                hint.textContent = 'Quota atteint — vous pouvez reajuster entre les plats.';
                hint.className = 'small text-success mb-2';
            } else if (over) {
                hint.textContent = 'Quota depasse — reduisez une selection.';
                hint.className = 'small text-danger mb-2';
            } else {
                hint.textContent = 'Il reste ' + (cart.invites - sum) + ' invite(s) a repartir.';
                hint.className = 'small text-muted mb-2';
            }
        }
    });

    document.getElementById('total').textContent = total.toFixed(2);
    document.getElementById('sticky-total').textContent = total.toFixed(2);
    const elAdultesTotal = document.getElementById('recap-adultes-total');
    if (elAdultesTotal) elAdultesTotal.textContent = menuAdultes.toFixed(2);
    const elEnfantsTotal = document.getElementById('recap-enfants-total');
    const elEnfantsN = document.getElementById('recap-enfants-n');
    if (elEnfantsTotal) elEnfantsTotal.textContent = menuEnfants.toFixed(2);
    if (elEnfantsN) elEnfantsN.textContent = cart.enfants;
    document.getElementById('recap-boissons-total').textContent = boissonsExtra.toFixed(2);
    document.getElementById('recap-detail').innerHTML = buildDetailedRecap();

    const btn = document.getElementById('btn-commander');
    const orderOk = TYPES.every(t => sumType(t) === cart.invites);
    if (btn) {
        setActionButtonState(btn, orderOk);
        if (WIZARD_STEPS[currentStepIndex] === 'recap') {
            if (orderOk) hideValidationMsg();
            else showValidationMsg('Repartissez exactement ' + cart.invites + ' invites pour chaque categorie.');
        }
    }
    updateStepperUI();
}

function autoFill() {
    syncInvitesFromInput();
    TYPES.forEach(type => autoFillCategory(type));
    hideValidationMsg();
    const recapIdx = WIZARD_STEPS.indexOf('recap');
    if (recapIdx >= 0) {
        goToStep(recapIdx);
    }
}

function submitOrder() {
    if (!TYPES.every(t => sumType(t) === cart.invites)) return;
    const f = document.createElement('form');
    f.method = 'POST';
    f.action = 'commande.php';
    const menuInput = document.createElement('input');
    menuInput.type = 'hidden';
    menuInput.name = 'menu_id';
    menuInput.value = MENU_ID;
    const dataInput = document.createElement('input');
    dataInput.type = 'hidden';
    dataInput.name = 'data';
    dataInput.value = JSON.stringify(cart);
    f.appendChild(menuInput);
    f.appendChild(dataInput);
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = CSRF_TOKEN;
    f.appendChild(csrfInput);
    document.body.appendChild(f);
    f.submit();
}

function setInvitesCount(n) {
    const inp = document.getElementById('invites');
    cart.invites = Math.max(MIN_GUESTS, Math.min(500, parseInt(n, 10) || MIN_GUESTS));
    if (inp) inp.value = cart.invites;
    if (cart.enfants > cart.invites) cart.enfants = cart.invites;
    const nbE = document.getElementById('nb-enfants');
    if (nbE) nbE.max = cart.invites;
    cart.entree = {}; cart.plat = {}; cart.dessert = {};
    document.querySelectorAll('.plat-slider').forEach(s => { s.value = 0; });
    syncSlidersMax();
    updateUI();
}

document.querySelector('.btn-guest-dec')?.addEventListener('click', () => {
    setInvitesCount(cart.invites - 1);
});
document.querySelector('.btn-guest-inc')?.addEventListener('click', () => {
    setInvitesCount(cart.invites + 1);
});

document.getElementById('invites').addEventListener('input', e => {
    setInvitesCount(e.target.value);
});
document.getElementById('invites').addEventListener('blur', e => {
    if ((parseInt(e.target.value, 10) || 0) < MIN_GUESTS) {
        setInvitesCount(MIN_GUESTS);
    }
});

if (HAS_ENFANT_OPTION) {
    document.getElementById('has-enfants')?.addEventListener('change', updateUI);
    document.getElementById('nb-enfants')?.addEventListener('input', e => {
        cart.enfants = Math.min(cart.invites, Math.max(0, parseInt(e.target.value, 10) || 0));
        e.target.value = cart.enfants;
        updateUI();
    });
}

document.querySelectorAll('.btn-autofill-cat').forEach(btn => {
    btn.addEventListener('click', () => autoFillCategory(btn.dataset.type));
});

document.querySelectorAll('.plat-slider').forEach(s => {
    s.addEventListener('input', () => updateChoice(s.dataset.type, s.dataset.id, s.value));
});
document.querySelectorAll('.boisson-qty').forEach(input => {
    input.addEventListener('input', updateUI);
});

document.querySelectorAll('.btn-wizard-next').forEach(btn => {
    btn.addEventListener('click', () => {
        if (btn.getAttribute('aria-disabled') === 'true') {
            const stepId = btn.dataset.validate || WIZARD_STEPS[currentStepIndex];
            showValidationMsg(stepMessage(stepId));
            shakePanel(stepId);
            focusStepField(stepId);
            return;
        }
        tryGoNext(btn);
    });
});
document.querySelectorAll('.btn-wizard-prev').forEach(btn => {
    btn.addEventListener('click', () => {
        const prev = btn.dataset.prev?.replace('step-', '');
        const idx = WIZARD_STEPS.indexOf(prev);
        goToStep(idx >= 0 ? idx : Math.max(0, currentStepIndex - 1));
    });
});
document.querySelectorAll('.wizard-step-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        if (btn.classList.contains('locked') || btn.getAttribute('aria-disabled') === 'true') {
            focusFirstInvalidField();
            return;
        }
        const idx = parseInt(btn.dataset.step, 10);
        if (idx <= currentStepIndex) {
            goToStep(idx);
            return;
        }
        for (let i = 0; i < idx; i++) {
            if (!validateStep(WIZARD_STEPS[i])) {
                const msg = document.getElementById('validation-msg');
                if (msg) { msg.textContent = stepMessage(WIZARD_STEPS[i]); msg.classList.remove('d-none'); }
                goToStep(i);
                return;
            }
        }
        goToStep(idx);
    });
});

document.getElementById('sticky-next').addEventListener('click', () => {
    const sticky = document.getElementById('sticky-next');
    if (currentStepIndex >= WIZARD_STEPS.length - 1) return;
    if (sticky?.getAttribute('aria-disabled') === 'true') {
        const stepId = WIZARD_STEPS[currentStepIndex];
        showValidationMsg(stepMessage(stepId));
        shakePanel(stepId);
        focusStepField(stepId);
        return;
    }
    tryGoNext({ dataset: { next: 'step-' + WIZARD_STEPS[currentStepIndex + 1] } });
});

document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    const msg = document.getElementById('validation-msg');
    if (msg && !msg.classList.contains('d-none')) {
        hideValidationMsg();
        clearFieldErrors();
        e.preventDefault();
        return;
    }
    if (currentStepIndex > 0) {
        goToStep(currentStepIndex - 1);
        e.preventDefault();
    }
});

document.querySelectorAll('.btn-autofill-all').forEach(btn => {
    btn.addEventListener('click', autoFill);
});
document.getElementById('btn-commander').addEventListener('click', () => {
    if (!focusFirstInvalidField()) return;
    submitOrder();
});

syncSlidersMax();
updateUI();
if (restoreSavedCart(SAVED_CART)) {
    const recapIdx = WIZARD_STEPS.indexOf('recap');
    if (recapIdx >= 0) {
        goToStep(recapIdx, RESUME_COMMANDE);
    }
} else {
    goToStep(0, false);
}
