<?php
// Use the same model summaries as the headline total; never add invented categories.
$uninspectedBreakdownSum = array_sum(array_column($equipmentCards, 'not_inspected'));
$uninspectedMismatch = $initialData && $uninspectedBreakdownSum !== $summary['not_inspected'];
?>
<article class="dash-stat dash-uninspected-flip card d-block h-100 p-3 p-lg-4" id="dashboardUninspectedCard">
    <div class="dash-uninspected-front" id="dashboardUninspectedFront" role="button" tabindex="0" aria-expanded="false" aria-controls="dashboardUninspectedBack" aria-label="Equipment Not Yet Inspected: show equipment breakdown">
        <span class="dash-stat-icon d-inline-flex align-items-center justify-content-center mb-3 <?= $stat['color'] ?>"><i class="bi <?= $stat['icon'] ?>" aria-hidden="true"></i></span>
        <span class="dash-uninspected-hint text-secondary" aria-hidden="true" title="Show equipment breakdown"><i class="bi bi-arrow-repeat"></i></span>
        <h3><?= $stat['label'] ?></h3>
        <strong data-summary="<?= $stat['key'] ?>"><?= number_format($summary[$stat['key']]) ?></strong>
        <p><?= $stat['description'] ?></p>
    </div>
    <div class="dash-uninspected-back d-flex flex-column gap-1 p-2 p-sm-3" id="dashboardUninspectedBack" aria-hidden="true" hidden inert>
        <div class="dash-uninspected-back-heading d-flex align-items-start gap-2 flex-shrink-0">
            <i class="bi bi-clock flex-shrink-0" aria-hidden="true"></i>
            <div><h3>Equipment Not Yet Inspected</h3><p>Breakdown by equipment type</p></div>
        </div>
        <p class="dash-uninspected-total d-flex justify-content-between align-items-center gap-2 px-2 py-1 flex-shrink-0"><span>Total Uninspected</span><b data-summary="not_inspected"><?= number_format($summary['not_inspected']) ?></b></p>
        <!-- Only this flexible list scrolls; the header, total, and Back button stay visible. -->
        <div class="dash-uninspected-list flex-grow-1 overflow-auto" tabindex="0" role="region" aria-label="Uninspected equipment breakdown">
            <p id="dashboardUninspectedMismatch" class="dash-uninspected-message text-secondary" role="status" <?= !$uninspectedMismatch ? 'hidden' : '' ?>><?= $uninspectedMismatch ? 'Breakdown totals ' . number_format($uninspectedBreakdownSum) . '; dashboard total is ' . number_format($summary['not_inspected']) . '.' : '' ?></p>
            <ul class="list-unstyled d-flex flex-column gap-1 mb-0" id="dashboardUninspectedList">
                <?php foreach ($equipmentCards as $uninspectedEquipment): ?>
                    <?php if ((int) $uninspectedEquipment['not_inspected'] <= 0) continue; ?>
                    <li class="dash-uninspected-row d-flex justify-content-between align-items-center gap-2 px-2 py-1"><span><?= dashboardEscape($uninspectedEquipment['label']) ?></span><b class="dash-uninspected-badge badge rounded-pill flex-shrink-0"><?= number_format($uninspectedEquipment['not_inspected']) ?></b></li>
                <?php endforeach; ?>
            </ul>
            <p id="dashboardUninspectedEmpty" class="text-secondary" <?= $initialData && $uninspectedBreakdownSum > 0 ? 'hidden' : '' ?>><?= $initialData ? 'No uninspected equipment in this period.' : 'Breakdown unavailable until dashboard data loads.' ?></p>
        </div>
        <div class="d-flex justify-content-end flex-shrink-0">
            <button type="button" class="dash-uninspected-back-button btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" id="dashboardUninspectedReturn"><i class="bi bi-arrow-left" aria-hidden="true"></i>Back</button>
        </div>
    </div>
</article>
