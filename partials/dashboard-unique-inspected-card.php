<article class="dash-stat dash-uninspected-flip dash-detail-flip card d-block p-3 p-lg-4" id="dashboardUniqueInspectedCard">
    <div class="dash-uninspected-front dash-detail-front" id="dashboardUniqueInspectedFront" role="button" tabindex="0" aria-expanded="false" aria-controls="dashboardUniqueInspectedBack" aria-label="Unique Equipment Inspected: show category summary">
        <span class="dash-stat-icon d-inline-flex align-items-center justify-content-center mb-3 <?= $stat['color'] ?>"><i class="bi <?= $stat['icon'] ?>" aria-hidden="true"></i></span>
        <span class="dash-uninspected-hint text-secondary" aria-hidden="true" title="Show category summary"><i class="bi bi-arrow-repeat"></i></span>
        <h3><?= $stat['label'] ?></h3><strong data-summary="inspected"><?= number_format($summary['inspected']) ?></strong>
        <p><?= $stat['description'] ?></p>
    </div>
    <div class="dash-uninspected-back dash-detail-back d-flex flex-column gap-1 p-2 p-sm-3" id="dashboardUniqueInspectedBack" aria-hidden="true" hidden inert>
        <div class="dash-uninspected-back-heading d-flex align-items-start gap-2 flex-shrink-0"><i class="bi bi-check2-circle flex-shrink-0" aria-hidden="true"></i>
            <div>
                <h3>Unique Equipment Inspected</h3>
                <p>Active equipment inspected in this period</p>
            </div>
        </div>
        <p class="dash-uninspected-total d-flex justify-content-between align-items-center gap-2 px-2 py-1 flex-shrink-0">
            <span>Unique equipment</span>
            <b data-summary="inspected"><?= number_format($summary['inspected']) ?></b>
        </p>
        <div class="dash-uninspected-list dash-detail-list flex-grow-1 overflow-auto" tabindex="0" role="region" aria-label="Unique equipment inspected category summary">
            <div id="dashboardUniqueInspectedList"></div>
            <p id="dashboardUniqueInspectedEmpty" class="text-secondary" hidden>No active equipment was inspected in this period.</p>
        </div>
        <div class="d-flex justify-content-end flex-shrink-0">
            <button type="button" class="dash-uninspected-back-button btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" id="dashboardUniqueInspectedReturn">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>Back
            </button>
        </div>
    </div>
</article>