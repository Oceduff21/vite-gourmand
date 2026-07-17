<?php
/** @var array $group @var int $min @var bool $hasBoissons @var array|null $menuEnfantInfo @var bool $isMenuEnfant @var array $boissonsByCat @var array $boissonLabels @var array $stepNums @var array $stepOrder */
$labels = ['entree' => 'Entrees', 'plat' => 'Plats', 'dessert' => 'Desserts'];
?>
<section id="step-invites" class="wizard-panel card-custom mb-4 menu-guests-card">
    <div class="wizard-panel-head">
        <h3 class="h5 mb-1"><span class="step-badge"><?= (int)$stepNums['invites'] ?></span> Nombre de personnes</h3>
        <p class="text-muted small mb-0">Indiquez combien de personnes seront presentes. Minimum <strong><?= $min ?></strong> personnes.</p>
    </div>
    <div class="row align-items-center g-3 mt-2">
        <div class="col-sm-4 col-md-3">
            <label class="form-label small fw-bold" for="invites">Total invites</label>
            <input type="number" id="invites" class="form-control form-control-lg wizard-focus-field" value="<?= $min ?>" min="<?= $min ?>" max="500">
        </div>
        <div class="col-sm-8 col-md-9">
            <p class="text-muted mb-2 small">Le prix du menu est recalcule automatiquement selon le nombre de convives.</p>
            <div class="alert alert-light border mb-0 py-2" aria-live="polite">
                <strong>Estimation menu :</strong> <span id="invites-price-preview">0.00</span> EUR
                <span class="text-muted small d-block mt-1">Reduction -10% si <?= $min + 5 ?> personnes ou plus.</span>
            </div>
        </div>
    </div>
    <?php if ($menuEnfantInfo && !$isMenuEnfant): ?>
    <div class="row g-3 mt-3 pt-3 border-top">
        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="has-enfants">
                <label class="form-check-label" for="has-enfants">Des enfants seront presents (menu enfant separe)</label>
            </div>
        </div>
        <div class="col-sm-4 col-md-3" id="enfants-wrap" style="display:none">
            <label class="form-label small fw-bold" for="nb-enfants">Nombre d'enfants</label>
            <input type="number" id="nb-enfants" class="form-control" value="0" min="0" max="500">
        </div>
        <div class="col-sm-8 col-md-9" id="enfants-info" style="display:none">
            <p class="small text-muted mb-0">
                <i class="fa-solid fa-child me-1"></i>
                <span id="enfants-summary">0</span> menu(x) enfant a <?= number_format((float)$menuEnfantInfo['prix'], 2) ?> EUR
            </p>
        </div>
    </div>
    <?php endif; ?>
    <div class="wizard-nav mt-4">
        <button type="button" class="btn btn-outline-secondary btn-wizard-prev" data-prev="step-menu"><i class="fa-solid fa-arrow-up me-1"></i> Precedent</button>
        <button type="button" class="btn btn-primary btn-wizard-next" data-next="step-entree" data-validate="invites">Suite — Entrees <i class="fa-solid fa-arrow-down ms-1"></i></button>
    </div>
</section>

<?php foreach ($group as $type => $items):
    if (empty($items)) continue;
    $idx = array_search($type, $stepOrder, true);
    $prevStep = $idx > 0 ? 'step-' . $stepOrder[$idx - 1] : 'step-invites';
    $nextStep = $idx < count($stepOrder) - 1 ? 'step-' . $stepOrder[$idx + 1] : ($hasBoissons ? 'step-boissons' : 'step-recap');
?>
<section id="step-<?= $type ?>" class="wizard-panel menu-category mb-4" data-type="<?= $type ?>">
    <div class="wizard-panel-head d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h3 class="h5 mb-1"><span class="step-badge"><?= (int)$stepNums[$type] ?></span> <?= $labels[$type] ?></h3>
            <p class="text-muted small mb-0">Repartissez <strong class="guest-count-label"><?= $min ?></strong> invites entre les options.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="category-status" id="status-<?= $type ?>"><span class="badge bg-secondary">0 / <?= $min ?></span></div>
            <button type="button" class="btn btn-sm btn-outline-primary btn-autofill-cat" data-type="<?= $type ?>"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> Auto</button>
        </div>
    </div>
    <div class="progress mb-2" style="height:8px"><div class="progress-bar" id="progress-<?= $type ?>" role="progressbar" style="width:0%"></div></div>
    <p class="small text-muted mb-3" id="hint-<?= $type ?>">Repartissez les invites entre les options ci-dessous.</p>
    <div class="row g-3 plat-grid">
        <?php foreach ($items as $item): ?>
        <div class="col-sm-6 col-lg-4">
            <div class="plat-select-card" id="card-<?= $type ?>-<?= (int)$item['id'] ?>">
                <div class="plat-img-wrap">
                    <img src="<?= htmlspecialchars(assetImageUrl('assets/images/' . ($item['image'] ?? 'default.jpg'))) ?>" class="plat-img" alt="<?= htmlspecialchars($item['nom']) ?>" loading="lazy">
                </div>
                <div class="plat-card-body">
                    <div class="plat-card-info">
                        <h5 class="plat-card-title"><?= htmlspecialchars($item['nom']) ?></h5>
                        <div class="plat-card-badges"><?= platBadge($item['regime'] ?? 'classique') ?></div>
                        <div class="plat-allergenes mt-1"><?= renderAllergenesBadges($item['allergenes'] ?? null) ?></div>
                    </div>
                    <div class="plat-card-controls">
                        <label class="form-label small mb-1" for="<?= $type ?>-<?= (int)$item['id'] ?>">Invites</label>
                        <input type="range" class="form-range plat-slider" min="0" max="<?= $min ?>" value="0"
                            id="<?= $type ?>-<?= (int)$item['id'] ?>"
                            data-type="<?= $type ?>" data-id="<?= (int)$item['id'] ?>"
                            data-nom="<?= htmlspecialchars($item['nom'], ENT_QUOTES) ?>"
                            data-regime="<?= htmlspecialchars($item['regime'] ?? 'classique', ENT_QUOTES) ?>">
                        <div class="plat-qty"><span id="label-<?= $type ?>-<?= (int)$item['id'] ?>">0</span> invite(s)</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="wizard-nav mt-4">
        <button type="button" class="btn btn-outline-secondary btn-wizard-prev" data-prev="<?= $prevStep ?>"><i class="fa-solid fa-arrow-up me-1"></i> Precedent</button>
        <button type="button" class="btn btn-primary btn-wizard-next" data-next="<?= $nextStep ?>" data-validate="<?= $type ?>">
            Suite<?= $nextStep === 'step-recap' ? ' — Recapitulatif' : '' ?> <i class="fa-solid fa-arrow-down ms-1"></i>
        </button>
    </div>
</section>
<?php endforeach; ?>

<?php if ($hasBoissons): ?>
<section id="step-boissons" class="wizard-panel menu-category mb-4">
    <div class="wizard-panel-head mb-3">
        <h3 class="h5 mb-1"><span class="step-badge"><?= (int)$stepNums['boissons'] ?></span> Boissons</h3>
        <p class="text-muted small mb-0">Optionnel — selectionnez les quantites souhaitees.</p>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover boisson-list align-middle mb-0">
            <thead class="table-light"><tr><th>Boisson</th><th>Categorie</th><th class="text-end">Prix unit.</th><th style="width:100px">Quantite</th></tr></thead>
            <tbody>
            <?php foreach ($boissonsByCat as $cat => $items): foreach ($items as $b): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($b['nom']) ?></strong></td>
                    <td><span class="badge bg-light text-dark"><?= htmlspecialchars($boissonLabels[$cat] ?? ucfirst($cat)) ?></span></td>
                    <td class="text-end"><?= number_format((float)$b['prix'], 2) ?> EUR</td>
                    <td><input type="number" class="form-control form-control-sm boisson-qty" min="0" max="999" value="0"
                        data-id="<?= (int)$b['id'] ?>" data-nom="<?= htmlspecialchars($b['nom'], ENT_QUOTES) ?>" data-price="<?= (float)$b['prix'] ?>"></td>
                </tr>
            <?php endforeach; endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="wizard-nav mt-3">
        <button type="button" class="btn btn-outline-secondary btn-wizard-prev" data-prev="step-dessert"><i class="fa-solid fa-arrow-up me-1"></i> Precedent</button>
        <button type="button" class="btn btn-primary btn-wizard-next" data-next="step-recap" data-validate="boissons">Suite — Recapitulatif <i class="fa-solid fa-arrow-down ms-1"></i></button>
    </div>
</section>
<?php endif; ?>
