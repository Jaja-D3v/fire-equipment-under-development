<?php
require_once 'backend/authentication/SessionChecker.php';
require_once 'backend/controller/DashboardSummaryController.php';
// Keep the existing expiration notice's controller functions available.
require_once 'backend/controller/DashboardController.php';
$initialData = null;
$initialError = '';
try {
    $initialData = getDashboardSummaryData($_GET, $_SESSION);
} catch (Throwable $error) {
    $initialError = $error instanceof InvalidArgumentException || $error->getCode() === 403
        ? $error->getMessage() : 'Dashboard data could not be loaded. Please try again.';
    if ($initialError === 'Dashboard data could not be loaded. Please try again.') error_log($error->getMessage());
}
$filters = $initialData['filters'] ?? array(
    'period' => 'this_month',
    'branch' => trim($_SESSION['Branch'] ?? ''),
    'from' => '',
    'to' => '',
    'can_select_branch' => strtolower(trim($_SESSION['Role'] ?? '')) === 'admin'
);
$summary = $initialData['totals'] ?? array('active' => 0, 'inspected' => 0, 'submissions' => 0, 'not_inspected' => 0);
$equipmentCards = $initialData['equipment'] ?? array();
$periods = array('today' => 'Today', 'yesterday' => 'Yesterday', 'last_7' => 'Last 7 Days', 'last_30' => 'Last 30 Days', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'custom' => 'Custom Date Range');
function dashboardEscape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<?php
ob_start();
include 'partials/header.php';
$dashboardHead = ob_get_clean();
echo str_replace('</head>', '<link rel="stylesheet" href="css/dashboard.css?v=' . hash_file('sha256', __DIR__ . '/css/dashboard.css') . '"></head>', $dashboardHead);
?>

<body>
    <?php include 'partials/side-nav.php'; ?>
    <div class="wrapper d-flex flex-column min-vh-100">
        <?php include 'partials/header-nav.php'; ?>
        <div class="main-content flex-grow-1">
            <?php if ($initialData): include_once 'notification/fe-expiration-notice.php';
            endif; ?>
            <main class="equipment-dashboard container-fluid px-3 px-md-4 px-xl-5 py-3 py-md-4" id="equipmentDashboard" aria-busy="false">
                <header class="dash-heading row align-items-center g-3 mb-4">
                    <div class="col-12 col-xl">
                        <div class="dash-eyebrow">FIRE EQUIPMENT MANAGEMENT SYSTEM</div>
                        <h1>Fire Equipment overview</h1>
                        <p>A clear view of your inventory and inspection progress.</p>
                    </div>
                    <div class="dash-updated col-12 col-xl-auto d-flex align-items-center gap-2"><span class="dash-live-dot" aria-hidden="true"></span><span id="dashboardUpdated"><?= $initialData ? 'Updated ' . dashboardEscape($initialData['generated_at']) : 'Waiting for data' ?></span></div>
                </header>

                <form id="dashboardFilters" class="dash-filters card p-3 mb-4" novalidate>
                    <div class="row g-3 align-items-end">
                    <div class="dash-filter-field col-12 col-sm-6 col-lg-4 col-xxl-3"><label class="form-label" for="dashboardPeriod">Inspection period</label>
                        <select id="dashboardPeriod" name="period" class="form-select"><?php foreach ($periods as $key => $label): ?><option value="<?= dashboardEscape($key) ?>" <?= $filters['period'] === $key ? 'selected' : '' ?>><?= dashboardEscape($label) ?></option><?php endforeach; ?></select>
                    </div>
                    <?php if ($filters['can_select_branch']): ?>
                        <div class="dash-filter-field col-12 col-sm-6 col-lg-4 col-xxl-3"><label class="form-label" for="dashboardBranch">Branch</label>
                            <select id="dashboardBranch" name="branch" class="form-select">
                                <option value="all" <?= $filters['branch'] === 'all' ? 'selected' : '' ?>>All Branches</option>
                                <?php
                                $branchChoices = $initialData['branches'] ?? array();
                                if ($filters['branch'] !== '' && $filters['branch'] !== 'all' && !in_array($filters['branch'], $branchChoices, true)) $branchChoices[] = $filters['branch'];
                                foreach ($branchChoices as $branchChoice): ?>
                                    <option value="<?= dashboardEscape($branchChoice) ?>" <?= $filters['branch'] === $branchChoice ? 'selected' : '' ?>><?= dashboardEscape($branchChoice) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <div class="col-12 col-sm-6 col-lg-4 col-xxl-3"><div class="dash-assigned-branch d-flex align-items-center flex-wrap gap-2"><i class="bi bi-geo-alt" aria-hidden="true"></i><span><?= dashboardEscape($filters['branch']) ?></span><small>Assigned branch</small></div></div>
                    <?php endif; ?>
                    <div class="dash-custom-dates col-12 col-lg-8 col-xxl-4" id="dashboardCustomDates" <?= $filters['period'] !== 'custom' ? 'hidden' : '' ?>>
                        <div class="row g-3">
                        <div class="dash-filter-field col-12 col-sm-6"><label class="form-label" for="dashboardFrom">From</label><input id="dashboardFrom" name="from" type="date" class="form-control" value="<?= dashboardEscape($filters['from']) ?>"></div>
                        <div class="dash-filter-field col-12 col-sm-6"><label class="form-label" for="dashboardTo">To</label><input id="dashboardTo" name="to" type="date" class="form-control" value="<?= dashboardEscape($filters['to']) ?>"></div>
                    </div>
                    </div>
                    <div class="col-12 col-sm-auto ms-sm-auto"><button type="submit" class="dash-refresh btn btn-primary w-100 d-flex justify-content-center align-items-center gap-2 px-3" id="dashboardRefresh"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i><span>Refresh</span></button></div>
                    </div>
                </form>
                <div class="dash-status alert mb-3" id="dashboardStatus" role="status" aria-live="polite" <?= $initialError === '' ? 'hidden' : '' ?>><?= dashboardEscape($initialError) ?></div>
                <a id="dashboardSignIn" href="authentication/login.php" class="dash-sign-in d-inline-block mb-3" hidden>Sign in again</a>

                <section class="dash-section mb-4" aria-labelledby="dashboardSummaryTitle">
                    <div class="dash-section-heading d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h2 id="dashboardSummaryTitle">At a glance</h2>
                            <p id="dashboardRangeLabel">Inspection activity for the selected period</p>
                        </div><span class="dash-scope-label" id="dashboardScopeLabel"><?= dashboardEscape($filters['branch'] === 'all' ? 'All Branches' : $filters['branch']) ?></span>
                    </div>
                    <div class="dash-stat-grid row g-3">
                        <?php
                        $statCards = array(
                            array('active', 'Total Active Equipment', 'bi-box-seam', 'blue', 'Current inventory, excluding archived units.'),
                            array('inspected', 'Unique Equipment Inspected', 'bi-check2-circle', 'green', 'Active units inspected at least once in this period.'),
                            array('submissions', 'Total Inspection Submissions', 'bi-clipboard-check', 'blue', 'Saved inspection records, including re-inspections and archived history.'),
                            array('not_inspected', 'Equipment Not Yet Inspected', 'bi-clock', 'amber', 'Active units with no inspection in this period.')
                        );
                        foreach ($statCards as $stat): ?>
                            <div class="col-12 col-sm-6 col-xl-3"><article class="dash-stat card d-block h-100 p-3 p-lg-4"><span class="dash-stat-icon d-inline-flex align-items-center justify-content-center mb-3 <?= $stat[3] ?>"><i class="bi <?= $stat[2] ?>" aria-hidden="true"></i></span>
                                <h3><?= $stat[1] ?></h3><strong data-summary="<?= $stat[0] ?>"><?= number_format($summary[$stat[0]]) ?></strong>
                                <p><?= $stat[4] ?></p>
                            </article></div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="dash-section mb-4" aria-labelledby="dashboardEquipmentTitle">
                    <div class="dash-section-heading d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h2 id="dashboardEquipmentTitle">Your equipment</h2>
                            <p>Inspection coverage by equipment type</p>
                        </div>
                    </div>
                    <div class="dash-equipment-grid row g-3" id="dashboardEquipmentCards"><?php foreach ($equipmentCards as $equipment): include 'partials/dashboard-equipment-card.php';
                                                                                    endforeach; ?></div>
                </section>

                <section class="dash-chart-grid row g-3 g-lg-4 align-items-start" aria-label="Equipment analytics">
                    <div class="col-12 col-xl-8"><article class="dash-panel dash-activity-panel card d-block p-3 p-lg-4">
                        <div class="dash-panel-heading d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h2>Monthly inspections</h2>
                                <p>Unique equipment per month in the selected period</p>
                            </div>
                            <div class="dash-period-total text-end flex-shrink-0"><span>Period Total</span><strong id="dashboardHistoryTotal"><?= number_format($initialData['totals']['history_unique'] ?? 0) ?></strong></div>
                        </div>
                        <div class="dash-chart-container position-relative w-100"><canvas id="monthlyInspectionChart" role="img" aria-label="Monthly unique equipment inspections"></canvas></div>
                        <div class="dash-chart-legend d-flex justify-content-center flex-wrap gap-3 mt-3" id="dashboardMonthlyLegend"></div>
                        <div class="dash-comparison d-flex flex-column gap-1 mt-3 pt-3"><span id="dashboardComparison"><?= dashboardEscape($initialData['comparison']['text'] ?? '') ?></span><small>Current calendar month compared with last month</small></div>
                        <p class="dash-chart-note mt-3">History includes archived equipment. Period Total counts each unit once across the whole range; monthly values are not added together.</p>
                        <details class="dash-chart-data mt-3">
                            <summary>View monthly counts</summary>
                            <div class="dash-data-table table-responsive" id="dashboardMonthlyTable"></div>
                        </details>
                    </article></div>
                    <div class="col-12 col-xl-4"><div class="dash-side-charts row g-3 g-lg-4">
                        <div class="col-12 col-md-6 col-xl-12"><article class="dash-panel card d-block h-100 p-3 p-lg-4">
                            <div class="dash-panel-heading d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <h2>Inspection coverage</h2>
                                    <p>Active equipment in the selected period</p>
                                </div>
                            </div>
                            <div class="dash-donut-container position-relative mx-auto"><canvas id="dashboardCoverageChart" role="img" aria-label="Inspected and not yet inspected active equipment"></canvas>
                                <div class="dash-donut-value position-absolute top-50 start-50 translate-middle text-center"><strong id="dashboardCoveragePercent">0%</strong><span>inspected</span></div>
                            </div>
                            <div class="dash-chart-legend d-flex justify-content-center flex-wrap gap-3 mt-3" id="dashboardCoverageLegend"></div>
                        </article></div>
                        <div class="col-12 col-md-6 col-xl-12"><article class="dash-panel card d-block h-100 p-3 p-lg-4">
                            <div class="dash-panel-heading d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <h2>Equipment distribution</h2>
                                    <p>Current active inventory</p>
                                </div>
                            </div>
                            <div class="dash-distribution d-flex flex-column gap-3 pt-1" id="dashboardDistribution"></div>
                        </article></div>
                    </div></div>
                </section>
            </main>
        </div>
        <?php include 'partials/footer.php'; ?>
    </div>
    <template id="dashboardEquipmentTemplate"><?php $equipment = array('id' => 'template', 'label' => '', 'icon' => '', 'color' => '#0b5ed7', 'url' => '#', 'active' => 0, 'inspected' => 0, 'not_inspected' => 0);
                                                include 'partials/dashboard-equipment-card.php'; ?></template>
    <script type="application/json" id="dashboardBootstrap">
        <?= json_encode(array('data' => $initialData, 'error' => $initialError), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
    </script>
    <script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
    <script src="vendors/simplebar/js/simplebar.min.js"></script>
    <script src="js/dashboard.js?v=<?= hash_file('sha256', __DIR__ . '/js/dashboard.js') ?>"></script>
    <?php include_once 'notification/session_timeout.php'; ?>
</body>

</html>