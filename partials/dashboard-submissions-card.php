<?php $submissionBreakdown = $initialData['submission_breakdown'] ?? []; ?>
<article class="dash-stat dash-uninspected-flip dash-detail-flip card d-block p-3 p-lg-4" id="dashboardSubmissionsCard">
    <div class="dash-uninspected-front dash-detail-front" id="dashboardSubmissionsFront" role="button" tabindex="0" aria-expanded="false" aria-controls="dashboardSubmissionsBack" aria-label="Total Inspection Submissions: show status breakdown">
        <span class="dash-stat-icon d-inline-flex align-items-center justify-content-center mb-3 <?= $stat['color'] ?>"><i class="bi <?= $stat['icon'] ?>" aria-hidden="true"></i></span>
        <span class="dash-uninspected-hint text-secondary" aria-hidden="true" title="Show submission breakdown"><i class="bi bi-arrow-repeat"></i></span>
        <h3><?= $stat['label'] ?></h3><strong data-summary="submissions"><?= number_format($summary['submissions']) ?></strong><p><?= $stat['description'] ?></p>
    </div>
    <div class="dash-uninspected-back dash-detail-back d-flex flex-column gap-1 p-2 p-sm-3" id="dashboardSubmissionsBack" aria-hidden="true" hidden inert>
        <div class="dash-uninspected-back-heading d-flex align-items-start gap-2 flex-shrink-0"><i class="bi bi-clipboard-check flex-shrink-0" aria-hidden="true"></i><div><h3>Total Inspection Submissions</h3><p>Submitted inspection headers by category and status</p></div></div>
        <p class="dash-uninspected-total d-flex justify-content-between align-items-center gap-2 px-2 py-1 flex-shrink-0"><span>Total submissions</span><b data-summary="submissions"><?= number_format($summary['submissions']) ?></b></p>
        <div class="dash-uninspected-list dash-detail-list flex-grow-1 overflow-auto" tabindex="0" role="region" aria-label="Inspection submission status breakdown"><div id="dashboardSubmissionsList"></div><p id="dashboardSubmissionsEmpty" class="text-secondary" hidden>No inspection submissions in this period.</p></div>
        <div class="d-flex justify-content-end flex-shrink-0"><button type="button" class="dash-uninspected-back-button btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" id="dashboardSubmissionsReturn"><i class="bi bi-arrow-left" aria-hidden="true"></i>Back</button></div>
    </div>
</article>
