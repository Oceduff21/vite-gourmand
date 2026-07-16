<?php
/** @var array $menu */
/** @var array $entrees */
/** @var array $plats */
/** @var array $desserts */
/** @var array $boissons */
/** @var array $selected */
/** @var array $selectedBoissons */
$selected = $selected ?? ['entree' => [], 'plat' => [], 'dessert' => []];
$selectedBoissons = $selectedBoissons ?? [];
$menu = $menu ?? [];
?>

<div class="alert alert-info">
    <strong>Regles :</strong> selectionnez exactement <strong>3 entrees</strong>, <strong>3 plats</strong> et <strong>3 desserts</strong> par menu.
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label">Titre *</label>
        <input name="titre" class="form-control" value="<?= htmlspecialchars($menu['titre'] ?? '') ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Prix / pers. (EUR) *</label>
        <input name="prix" type="number" step="0.01" min="0" class="form-control" value="<?= htmlspecialchars($menu['prix'] ?? '') ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Min. personnes *</label>
        <input name="min" type="number" min="1" class="form-control" value="<?= htmlspecialchars($menu['min_personnes'] ?? '10') ?>" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Theme</label>
        <input name="theme" class="form-control" value="<?= htmlspecialchars($menu['theme'] ?? '') ?>" placeholder="Noel, Mariage...">
    </div>
    <div class="col-md-4">
        <label class="form-label">Regime</label>
        <select name="regime" class="form-select">
            <?php foreach (['classique', 'vegan', 'vegetarien'] as $r): ?>
            <option value="<?= $r ?>" <?= ($menu['regime'] ?? '') === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Stock</label>
        <input name="stock" type="number" min="0" class="form-control" value="<?= htmlspecialchars($menu['stock'] ?? '10') ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label">Delai reservation (horaires, jours)</label>
        <input name="delai_jours" type="number" min="1" class="form-control" value="<?= htmlspecialchars($menu['delai_jours'] ?? '7') ?>">
        <div class="form-text">Nombre de jours ouvres minimum avant la date de livraison.</div>
    </div>
    <div class="col-md-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($menu['description'] ?? '') ?></textarea>
    </div>
    <div class="col-md-12">
        <label class="form-label">Conditions particulieres</label>
        <textarea name="conditions" class="form-control" rows="2" placeholder="Allergies, materiel requis..."><?= htmlspecialchars($menu['conditions'] ?? '') ?></textarea>
    </div>
</div>

<?php
$sections = [
    'entrees' => ['label' => 'Entrees', 'items' => $entrees, 'key' => 'entree'],
    'plats' => ['label' => 'Plats', 'items' => $plats, 'key' => 'plat'],
    'desserts' => ['label' => 'Desserts', 'items' => $desserts, 'key' => 'dessert'],
];
foreach ($sections as $field => $sec):
?>
<div class="card-custom mb-4 menu-admin-section">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><?= $sec['label'] ?></h5>
        <span class="badge bg-secondary selection-counter" data-target="<?= $field ?>[]">0 / 3</span>
    </div>
    <?php if (empty($sec['items'])): ?>
    <p class="text-muted mb-0">Aucun plat disponible. <a href="admin-plats.php">Ajoutez des plats</a> d'abord.</p>
    <?php else: ?>
    <div class="row g-2">
        <?php foreach ($sec['items'] as $item): ?>
        <div class="col-md-4">
            <label class="plat-check-card d-block">
                <input type="checkbox" name="<?= $field ?>[]" value="<?= (int)$item['id'] ?>"
                    <?= in_array((int)$item['id'], $selected[$sec['key']] ?? [], true) ? 'checked' : '' ?>>
                <span class="plat-check-body">
                    <strong><?= htmlspecialchars($item['nom']) ?></strong>
                    <small class="text-muted d-block"><?= htmlspecialchars($item['regime'] ?? 'classique') ?></small>
                </span>
            </label>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<div class="card-custom mb-4">
    <h5 class="mb-3">Boissons (optionnel)</h5>
    <?php if (empty($boissons)): ?>
    <p class="text-muted mb-0">Aucune boisson en base.</p>
    <?php else: ?>
    <div class="row g-2">
        <?php foreach ($boissons as $b): ?>
        <div class="col-md-4">
            <label class="form-check">
                <input type="checkbox" class="form-check-input" name="boissons[]" value="<?= (int)$b['id'] ?>"
                    <?= in_array((int)$b['id'], $selectedBoissons, true) ? 'checked' : '' ?>>
                <?= htmlspecialchars($b['nom']) ?>
            </label>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.menu-admin-section').forEach(section => {
    const counter = section.querySelector('.selection-counter');
    const name = counter.dataset.target;
    const boxes = section.querySelectorAll('input[name="' + name + '"]');

    function refresh() {
        const n = section.querySelectorAll('input[name="' + name + '"]:checked').length;
        counter.textContent = n + ' / 3';
        counter.className = 'badge selection-counter ' + (n === 3 ? 'bg-success' : (n > 3 ? 'bg-danger' : 'bg-secondary'));
        section.querySelectorAll('.plat-check-card').forEach(card => {
            card.classList.toggle('is-selected', card.querySelector('input').checked);
        });
    }

    boxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const checked = section.querySelectorAll('input[name="' + name + '"]:checked');
            if (checked.length > 3) {
                this.checked = false;
                alert('Maximum 3 choix pour cette categorie.');
            }
            refresh();
        });
    });
    refresh();
});

document.getElementById('menu-admin-form')?.addEventListener('submit', function(e) {
    let ok = true;
    ['entrees[]', 'plats[]', 'desserts[]'].forEach(name => {
        const n = document.querySelectorAll('input[name="' + name + '"]:checked').length;
        if (n !== 3) ok = false;
    });
    if (!ok) {
        e.preventDefault();
        alert('Selectionnez exactement 3 entrees, 3 plats et 3 desserts.');
    }
});
</script>
