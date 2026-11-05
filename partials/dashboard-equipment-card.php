<article class="equipment-summary" data-equipment="<?= htmlspecialchars($equipment['id'], ENT_QUOTES, 'UTF-8') ?>">
    <div class="equipment-summary-head">
        <span class="equipment-symbol" style="--equipment-accent: <?= htmlspecialchars($equipment['color'], ENT_QUOTES, 'UTF-8') ?>"><i class="bi <?= htmlspecialchars($equipment['icon'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i></span>
        <div><h3><?= htmlspecialchars($equipment['label'], ENT_QUOTES, 'UTF-8') ?></h3><p>Current active inventory</p></div>
        <a class="equipment-link" href="<?= htmlspecialchars($equipment['url'], ENT_QUOTES, 'UTF-8') ?>" aria-label="View <?= htmlspecialchars($equipment['label'], ENT_QUOTES, 'UTF-8') ?> inventory"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
    </div>
    <div class="equipment-count"><strong data-module-value="active"><?= number_format($equipment['active']) ?></strong><span>active units</span></div>
    <div class="coverage-track" role="progressbar" aria-label="Inspection coverage" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $equipment['active'] ? round(100 * $equipment['inspected'] / $equipment['active']) : 0 ?>"><span style="width: <?= $equipment['active'] ? 100 * $equipment['inspected'] / $equipment['active'] : 0 ?>%; background: <?= htmlspecialchars($equipment['color'], ENT_QUOTES, 'UTF-8') ?>"></span></div>
    <div class="equipment-summary-foot"><span><b data-module-value="inspected"><?= number_format($equipment['inspected']) ?></b> inspected</span><span><b data-module-value="not_inspected"><?= number_format($equipment['not_inspected']) ?></b> remaining</span></div>
</article>
