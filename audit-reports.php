<?php require_once __DIR__ . '/backend/authentication/only-admin.php'; ?>
<!DOCTYPE html>
<html lang="en">
<?php include __DIR__ . '/partials/header.php'; ?>
<body>
<link rel="stylesheet" href="css/audit-reports.css">
<?php include __DIR__ . '/partials/side-nav.php'; ?>
<div class="wrapper d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/partials/header-nav.php'; ?>
    <main class="body flex-grow-1 px-3 py-4 audit-reports">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-3 mb-4">
                <span class="audit-heading-icon rounded-3"><i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i></span>
                <div><h1 class="h3 fw-bold mb-1">Audit &amp; Reports</h1>
                    <p class="text-body-secondary mb-0">Generate annual safety equipment inspection monitoring reports.</p></div>
            </div>
            <div id="audit-message" class="alert d-none" role="status" aria-live="polite"></div>
            <div class="row g-3 mb-3">
                <section class="col-12 col-xl-6" aria-labelledby="configuration-heading">
                    <div class="card h-100">
                        <div class="card-header"><h2 id="configuration-heading" class="h6 mb-0"><i class="bi bi-gear me-2" aria-hidden="true"></i>Report Configuration</h2></div>
                        <div class="card-body">
                            <form id="audit-form">
                                <fieldset id="audit-fields" disabled>
                                    <div class="row g-3">
                                        <div class="col-12 col-sm-5"><label class="form-label" for="audit-equipment">Equipment Type</label><select id="audit-equipment" name="equipment" class="form-select" required></select></div>
                                        <div class="col-12 col-sm-3"><label class="form-label" for="audit-year">Reporting Year</label><select id="audit-year" name="year" class="form-select" required></select></div>
                                        <div class="col-12 col-sm-4"><label class="form-label" for="audit-branch">Branch / Location</label><select id="audit-branch" name="branch" class="form-select" required></select></div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-bar-chart-fill me-2" aria-hidden="true"></i>Generate Report</button>
                                        <button type="reset" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise me-2" aria-hidden="true"></i>Reset Filters</button>
                                    </div>
                                </fieldset>
                            </form>
                            <p class="small text-body-secondary mt-3 mb-0">Includes current active inventory. Past inventory locations and archived equipment are not reconstructed. All 12 months are included, including future months.</p>
                        </div>
                    </div>
                </section>
                <section class="col-12 col-xl-6" aria-labelledby="summary-heading">
                    <div class="card h-100">
                        <div class="card-header"><h2 id="summary-heading" class="h6 mb-0"><i class="bi bi-info-circle me-2" aria-hidden="true"></i>Report Preview Information</h2></div>
                        <div class="card-body">
                            <p id="audit-summary-empty" class="text-body-secondary mb-0">Generate a report to view its inspection totals.</p>
                            <div id="audit-summary" class="d-none" aria-live="polite">
                                <dl class="row g-3 mb-0">
                                    <div class="col-12 col-sm-5"><dt>Selected Equipment Type</dt><dd id="summary-equipment"></dd></div>
                                    <div class="col-5 col-sm-3"><dt>Reporting Year</dt><dd id="summary-year"></dd></div>
                                    <div class="col-7 col-sm-4"><dt>Branch / Location</dt><dd id="summary-branch"></dd></div>
                                    <div class="col-4 border-top pt-3"><dt>Total Equipment Records</dt><dd id="summary-total" class="fs-4"></dd></div>
                                    <div class="col-4 border-top pt-3"><dt>Completed Monthly Inspections</dt><dd id="summary-completed" class="fs-4"></dd></div>
                                    <div class="col-4 border-top pt-3"><dt>Missing Equipment-Month Inspections</dt><dd id="summary-missing" class="fs-4"></dd></div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <!-- <section class="card mb-3" aria-labelledby="generated-heading">
                <div class="card-header"><h2 class="h6 mb-0" id="generated-heading"><i class="bi bi-files me-2" aria-hidden="true"></i>Generated Reports</h2></div>
                <div class="card-body"><p class="small text-body-secondary mb-0">Saved report history is not available. The current preview can be downloaded; it is cleared when you leave or reset this page.</p></div>
            </section> -->
            <section class="card" aria-labelledby="preview-heading">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h2 id="preview-heading" class="h6 mb-0"><i class="bi bi-file-earmark-pdf me-2" aria-hidden="true"></i>Annual Inspection Report Preview</h2>
                    <div id="audit-actions" class="d-none d-flex flex-wrap gap-2">
                        <a id="audit-open" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">Open PDF / Print</a>
                        <a id="audit-download" class="btn btn-sm btn-primary">Download PDF</a>
                    </div>
                </div>
                <div class="card-body p-2">
                    <div id="audit-preview-empty" class="text-center text-body-secondary py-5"><i class="bi bi-file-earmark-pdf fs-1" aria-hidden="true"></i><p class="mt-2 mb-0">Your annual inspection report will appear here.</p></div>
                    <iframe id="audit-preview" title="Annual safety equipment inspection PDF" class="d-none w-100 border-0"></iframe>
                </div>
            </section>
        </div>
    </main>
    <?php include __DIR__ . '/partials/footer.php'; ?>
</div>
<script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
<script src="vendors/simplebar/js/simplebar.min.js"></script>
<script src="js/audit-reports.js"></script>
<?php include_once __DIR__ . '/notification/session_timeout.php'; ?>
</body>
</html>
