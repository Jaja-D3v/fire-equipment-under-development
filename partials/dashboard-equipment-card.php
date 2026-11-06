<?php
// Both server rendering and the AJAX template share this coverage calculation.
$coveragePercent = $equipment['active'] ? 100 * $equipment['inspected'] / $equipment['active'] : 0;
?>
<div class="col-12 col-md-6" data-equipment="<?= htmlspecialchars($equipment['id'], ENT_QUOTES, 'UTF-8') ?>">
    <article class="equipment-summary card d-block h-100 p-3 p-lg-4">
        <div class="equipment-summary-head d-flex align-items-center gap-2 gap-lg-3">
            <span class="equipment-symbol d-inline-flex align-items-center justify-content-center flex-shrink-0" style="--equipment-accent: <?= htmlspecialchars($equipment['color'], ENT_QUOTES, 'UTF-8') ?>"><i class="bi <?= htmlspecialchars($equipment['icon'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i></span>
            <div>
                <h3><?= htmlspecialchars($equipment['label'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p>Current active inventory</p>
            </div>
            <a class="equipment-link d-inline-flex align-items-center justify-content-center flex-shrink-0 ms-auto" href="<?= htmlspecialchars($equipment['url'], ENT_QUOTES, 'UTF-8') ?>" aria-label="View <?= htmlspecialchars($equipment['label'], ENT_QUOTES, 'UTF-8') ?> inventory"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        </div>
        <div class="equipment-count d-flex flex-wrap align-items-baseline gap-2 my-3"><strong data-module-value="active"><?= number_format($equipment['active']) ?></strong><span>active units</span></div>
        <!-- Coverage changes with inspection dates; the active inventory above does not. -->
        <div class="coverage-track progress" role="progressbar" aria-label="Inspection coverage" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= round($coveragePercent) ?>"><span class="progress-bar" style="width: <?= $coveragePercent ?>%; background: <?= htmlspecialchars($equipment['color'], ENT_QUOTES, 'UTF-8') ?>"></span></div>
        <div class="equipment-summary-foot d-flex justify-content-between flex-wrap gap-2 mt-3"><span><b data-module-value="inspected"><?= number_format($equipment['inspected']) ?></b> inspected</span><span><b data-module-value="not_inspected"><?= number_format($equipment['not_inspected']) ?></b> remaining</span></div>
    </article>
</div>