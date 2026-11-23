<?php
require_once 'backend/authentication/SessionChecker.php';
require_once 'backend/controller/DashboardSummaryController.php';
// Keep the existing expiration notice's controller functions available.
require_once 'backend/controller/DashboardController.php';
// The page and JSON endpoint call the same Controller for identical filter rules.
$initialData = null;
$initialError = '';
try {
    $initialData = getDashboardSummaryData($_GET, $_SESSION);
} catch (Throwable $error) {
    if ($error instanceof InvalidArgumentException || $error->getCode() === 403) {
        $initialError = $error->getMessage();
    } else {
        $initialError = 'Dashboard data could not be loaded. Please try again.';
        error_log($error->getMessage());
    }
}
// Keep the filter controls usable when the initial request fails.
$filters = $initialData['filters'] ?? [
    'period' => 'this_month',
    'branch' => strtolower(trim($_SESSION['Role'] ?? '')) === 'super admin' ? 'all' : trim($_SESSION['Branch'] ?? ''),
    'from' => '',
    'to' => '',
    'can_select_branch' => in_array(strtolower(trim($_SESSION['Role'] ?? '')), ['admin', 'super admin'], true)
];
$summary = $initialData['totals'] ?? [
    'active' => 0,
    'inspected' => 0,
    'submissions' => 0,
    'not_inspected' => 0
];
$equipmentCards = $initialData['equipment'] ?? [];
$periods = [
    'today' => 'Today',
    'yesterday' => 'Yesterday',
    'last_7' => 'Last 7 Days',
    'last_30' => 'Last 30 Days',
    'this_month' => 'This Month',
    'last_month' => 'Last Month',
    'custom' => 'Custom Date Range'
];

// Escape database and request text before displaying it in HTML.
function dashboardEscape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function renderDashboardFireInventoryDetail(array $detail, array $equipment)
{
    $summary = $detail['summary'];
    $active = (int) ($equipment['active'] ?? $summary['active'] ?? 0);
    $inspected = (int) ($equipment['inspected'] ?? 0);
    $remaining = (int) ($equipment['not_inspected'] ?? 0);
    $coverage = $active ? min(100, max(0, 100 * $inspected / $active)) : 0;
    $columns = $detail['capacities'];
    $matrix = $detail['matrix'];
    ?>
    <article class="fe-inventory-card card d-block p-3" data-equipment="fire_extinguisher">
        <div class="d-flex flex-wrap align-items-start gap-2">
            <div class="d-flex align-items-center gap-3"><span class="fe-inventory-icon equipment-symbol" style="--equipment-accent: #305dff"><i class="bi bi-fire" aria-hidden="true"></i></span><div><h3>Fire Extinguisher</h3><p>Current active inventory</p></div></div>
            <button class="fe-details-toggle dashboard-details-toggle equipment-link btn btn-sm ms-auto" type="button" aria-expanded="false" aria-controls="fireInventoryDetails"><span>View Details</span><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
        </div>
        <div class="fe-primary-metric mt-2"><strong data-module-value="active"><?= number_format($active) ?></strong><span>active units</span></div>
        <div class="coverage-track progress mt-2" role="progressbar" aria-label="Fire extinguisher inspection coverage" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= round($coverage) ?>"><span class="progress-bar" style="width: <?= $coverage ?>%; background: #305dff"></span></div>
        <div class="fe-inspection-summary mt-2"><span><b data-module-value="inspected"><?= number_format($inspected) ?></b> Inspected</span><span><b data-module-value="not_inspected"><?= number_format($remaining) ?></b> Not yet inspected</span></div>
        <div class="fe-details dashboard-details" id="fireInventoryDetails" hidden>
            <div class="row g-2 mt-1">
                <?php foreach (['Spare' => ['good' => 'spare_good', 'bad' => 'spare_no_good'], 'Installed' => ['good' => 'installed_good', 'bad' => 'installed_no_good']] as $label => $keys): ?>
                    <div class="col-12 col-lg-6"><section class="fe-status-panel"><h4><?= $label ?> Units</h4><div class="fe-status-values"><span class="fe-status-good"><small>Good</small><b><?= number_format((int) ($summary[$keys['good']] ?? 0)) ?></b></span><span class="fe-status-bad"><small>No Good</small><b><?= number_format((int) ($summary[$keys['bad']] ?? 0)) ?></b></span></div></section></div>
                <?php endforeach; ?>
                <div class="col-12 col-xxl-8"><section class="fe-type-panel"><h4>FE Types by Weight</h4><div class="table-responsive"><table class="table fe-type-table mb-0"><thead><tr><th scope="col">FE Type</th><?php foreach ($columns as $capacity): ?><th scope="col"><?= dashboardEscape($capacity) ?></th><?php endforeach; ?><th scope="col">Total</th></tr></thead><tbody><?php if (!$matrix): ?><tr><td colspan="<?= count($columns) + 2 ?>" class="text-center text-muted">No active type and capacity records.</td></tr><?php endif; ?><?php $columnTotals = array_fill_keys($columns, 0); foreach ($matrix as $type => $capacities): $rowTotal = 0; ?><tr><th scope="row"><?= dashboardEscape($type) ?></th><?php foreach ($columns as $capacity): $count = (int) ($capacities[$capacity] ?? 0); $rowTotal += $count; $columnTotals[$capacity] += $count; ?><td><?= number_format($count) ?></td><?php endforeach; ?><td class="fw-semibold"><?= number_format($rowTotal) ?></td></tr><?php endforeach; ?></tbody><?php if ($matrix): ?><tfoot><tr><th scope="row">Total Units</th><?php foreach ($columns as $capacity): ?><td><?= number_format($columnTotals[$capacity]) ?></td><?php endforeach; ?><td><?= number_format($detail['breakdown_total']) ?></td></tr></tfoot><?php endif; ?></table></div><?php if ($detail['breakdown_total'] !== $active): ?><p class="fe-discrepancy mb-0">Type/weight total excludes <?= number_format($active - $detail['breakdown_total']) ?> active unit(s) with a missing type or capacity.</p><?php endif; ?></section></div>
                <div class="col-12 col-xxl-4"><section class="fe-expiry-panel"><span>Near Expiry Count</span><strong><?= number_format((int) ($summary['near_expiry'] ?? 0)) ?></strong><small>Expires within 2 months</small></section></div>
            </div>
        </div>
    </article>
    <?php
}

// The Chemical Spill Kit uses the same compact dashboard-card structure while
// retaining its own color and an independent details state.
function renderDashboardChemicalSpillKitCard(array $equipment)
{
    $active = (int) ($equipment['active'] ?? 0);
    $inspected = (int) ($equipment['inspected'] ?? 0);
    $remaining = (int) ($equipment['not_inspected'] ?? 0);
    $coverage = $active ? min(100, max(0, 100 * $inspected / $active)) : 0;
    $color = dashboardEscape($equipment['color'] ?? '#269b8d');
    ?>
    <article class="csk-inventory-card card d-block p-3" data-equipment="chemical_spill_kit">
        <div class="d-flex flex-wrap align-items-start gap-2">
            <div class="d-flex align-items-center gap-3"><span class="csk-inventory-icon equipment-symbol" style="--equipment-accent: <?= $color ?>"><i class="bi <?= dashboardEscape($equipment['icon'] ?? 'bi-shield-exclamation') ?>" aria-hidden="true"></i></span><div><h3><?= dashboardEscape($equipment['label'] ?? 'Chemical Spill Kit') ?></h3><p>Current active inventory</p></div></div>
            <button class="csk-details-toggle dashboard-details-toggle equipment-link btn btn-sm ms-auto" type="button" aria-expanded="false" aria-controls="chemicalSpillKitDetails"><span>View Details</span><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
        </div>
        <div class="fe-primary-metric csk-primary-metric mt-2"><strong data-module-value="active"><?= number_format($active) ?></strong><span>active units</span></div>
        <div class="coverage-track progress mt-2" role="progressbar" aria-label="Chemical Spill Kit inspection coverage" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= round($coverage) ?>"><span class="progress-bar" style="width: <?= $coverage ?>%; background: <?= $color ?>"></span></div>
        <div class="fe-inspection-summary mt-2"><span><b data-module-value="inspected"><?= number_format($inspected) ?></b> Inspected</span><span><b data-module-value="not_inspected"><?= number_format($remaining) ?></b> Remaining</span></div>
        <div class="csk-details dashboard-details" id="chemicalSpillKitDetails" hidden>
            <section class="csk-detail-panel"><h4>Inspection Summary</h4><div class="csk-detail-values"><span><small>Active inventory</small><b data-csk-detail-value="active"><?= number_format($active) ?></b></span><span><small>Inspected</small><b data-csk-detail-value="inspected"><?= number_format($inspected) ?></b></span><span><small>Remaining</small><b data-csk-detail-value="not_inspected"><?= number_format($remaining) ?></b></span></div></section>
        </div>
    </article>
    <?php
}

$fireEquipment = [];
foreach ($equipmentCards as $card) if (($card['id'] ?? '') === 'fire_extinguisher') $fireEquipment = $card;
try {
    $fireInventoryDetail = getDashboardFireInventoryDetail($filters['branch']);
} catch (Throwable $error) {
    $fireInventoryDetail = ['summary' => [], 'matrix' => [], 'capacities' => [], 'breakdown_total' => 0];
    error_log('Fire extinguisher dashboard detail: ' . $error->getMessage());
}
if (isset($_GET['fire_inventory_detail'])) {
    header('Content-Type: application/json; charset=utf-8');
    ob_start(); renderDashboardFireInventoryDetail($fireInventoryDetail, $fireEquipment); $html = ob_get_clean();
    echo json_encode(['success' => true, 'html' => $html]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<?php
// Append dashboard-only CSS after the shared styles without changing the shared header.
ob_start();
include 'partials/header.php';
$dashboardHead = ob_get_clean();
echo str_replace('</head>', '<link rel="stylesheet" href="css/dashboard.css?v=' . hash_file('sha256', __DIR__ . '/css/dashboard.css') . '"><style>
.fe-inventory-card{border:1px solid #dce8fa;border-radius:18px;background:#fff;box-shadow:0 8px 25px rgba(36,74,126,.06);color:#243047}.fe-inventory-card h3{font-size:16px;font-weight:700;margin:0}.fe-inventory-card p{font-size:12px;color:#66738a;margin:3px 0 0}.fe-inventory-icon{width:42px;height:42px;border-radius:13px;background:#edf4ff;color:#305dff;font-size:20px}.fe-details-toggle{border:1px solid #cbdcf7!important;border-radius:9px!important;color:#2258a5!important;background:#f7faff!important;font-size:12px!important;font-weight:600!important;min-height:36px;padding:.4rem .7rem}.fe-details-toggle i{margin-left:.4rem;transition:transform .2s ease}.fe-details-toggle[aria-expanded="true"] i{transform:rotate(180deg)}.fe-primary-metric{display:flex;align-items:baseline;gap:.55rem}.fe-primary-metric strong{font-size:29px;letter-spacing:-1px;line-height:1;color:#183a72;font-variant-numeric:tabular-nums}.fe-primary-metric span,.fe-inspection-summary{font-size:12px;color:#66738a}.fe-inspection-summary{display:flex;justify-content:space-between;gap:1rem}.fe-inspection-summary b{color:#243047}.fe-details{overflow:hidden}.fe-details[hidden]{display:none}.fe-status-panel,.fe-type-panel,.fe-expiry-panel{border:1px solid #e3ebf7;border-radius:13px;background:#fbfdff;padding:10px}.fe-status-panel{height:100%}.fe-type-panel{height:100%}.fe-status-panel h4,.fe-type-panel h4{font-size:13px;font-weight:700;margin:0 0 8px}.fe-status-values{display:grid;grid-template-columns:1fr 1fr;gap:8px}.fe-status-values span{display:flex;align-items:center;justify-content:space-between;padding:7px 8px;border-radius:9px}.fe-status-values small{font-size:11px;font-weight:600}.fe-status-values b{font-size:16px;font-variant-numeric:tabular-nums}.fe-status-good{background:#edf7f1;color:#1d7557}.fe-status-bad{background:#fff1f1;color:#b95050}.fe-type-table{font-size:12px;white-space:nowrap}.fe-type-table th,.fe-type-table td{padding:.4rem .55rem;border-color:#e6edf7;text-align:right;line-height:1.25}.fe-type-table th:first-child{text-align:left}.fe-type-table thead th{background:#f1f6ff;color:#4d6282;font-size:11px}.fe-type-table tfoot{background:#eef5ff;color:#183a72;font-weight:700}.fe-discrepancy{margin-top:7px;color:#7b6741;font-size:11px;line-height:1.35}.fe-expiry-panel{display:flex;flex-direction:column;align-items:flex-start;justify-content:flex-start;height:auto;background:#fff9f0;border-color:#ffe2b7}.fe-expiry-panel span,.fe-expiry-panel small{font-size:11px;color:#9a6826}.fe-expiry-panel strong{font-size:25px;line-height:1.05;color:#c47617;margin:4px 0;font-variant-numeric:tabular-nums}@media(max-width:575.98px){.fe-inventory-card{padding:1rem!important}.fe-details-toggle{width:100%;margin-left:0!important}.fe-inventory-card>div:first-child{width:100%}.fe-type-panel{padding:9px}.fe-type-table th,.fe-type-table td{padding:.45rem}.fe-inspection-summary{font-size:11px}}@media(prefers-reduced-motion:reduce){.fe-details-toggle i{transition:none}}
</style><style>
.dash-equipment-grid{align-items:flex-start}.fe-inventory-card,.csk-inventory-card{min-height:174px}.fe-inventory-icon,.csk-inventory-icon{display:flex;align-items:center;justify-content:center;margin:0;padding:0}.csk-inventory-card{border:1px solid #d9eee8;border-radius:18px;background:#fff;box-shadow:0 8px 25px rgba(36,74,126,.06);color:#243047}.csk-inventory-card h3{font-size:16px;font-weight:700;margin:0}.csk-inventory-card p{font-size:12px;color:#66738a;margin:3px 0 0}.csk-inventory-icon{width:42px;height:42px;border-radius:13px;background:#e9f6f1;color:#269b8d;font-size:20px}.csk-details-toggle{border:1px solid #cce6df!important;border-radius:9px!important;color:#217463!important;background:#f4fbf8!important;font-size:12px!important;font-weight:600!important;min-height:36px;padding:.4rem .7rem}.csk-details-toggle i{margin-left:.4rem;transition:transform .2s ease}.csk-details-toggle[aria-expanded="true"] i{transform:rotate(180deg)}.csk-primary-metric strong{color:#1f7d6b}.csk-details{margin-top:8px}.csk-details[hidden]{display:none}.csk-detail-panel{border:1px solid #dceee8;border-radius:13px;background:#fbfefd;padding:10px}.csk-detail-panel h4{font-size:13px;font-weight:700;margin:0 0 8px}.csk-detail-values{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.csk-detail-values span{display:flex;flex-direction:column;gap:2px;padding:7px 8px;border-radius:9px;background:#edf8f4}.csk-detail-values small{font-size:10px;color:#397a6d}.csk-detail-values b{font-size:16px;color:#1d705f;font-variant-numeric:tabular-nums}.dashboard-details-toggle[aria-expanded="true"] i{transform:rotate(180deg)}@media(max-width:575.98px){.fe-inventory-card,.csk-inventory-card{min-height:0}.csk-detail-values{grid-template-columns:1fr}.csk-inventory-card{padding:1rem!important}}@media(prefers-reduced-motion:reduce){.csk-details-toggle i{transition:none}}
</style></head>', $dashboardHead);
?>

<body>
    <?php include 'partials/side-nav.php'; ?>
    <div class="wrapper d-flex flex-column min-vh-100">
        <?php include 'partials/header-nav.php'; ?>
        <div class="main-content flex-grow-1">
            <?php if ($initialData): ?>
                <?php include_once 'notification/fe-expiration-notice.php'; ?>
            <?php endif; ?>
            <main class="equipment-dashboard container-fluid px-3 px-md-4 px-xl-5 py-3 py-md-4" id="equipmentDashboard" aria-busy="false">
                <header class="dash-heading row align-items-center g-3 mb-4">
                    <div class="col-12 col-xl">
                        <div class="dash-eyebrow">SAFETY EQUIPMENT MANAGEMENT SYSTEM</div>
                        <h1>Safety Equipment overview</h1>
                        <p>A clear view of your inventory and inspection progress.</p>
                    </div>
                    <div class="dash-updated col-12 col-xl-auto d-flex align-items-center gap-2"><span class="dash-live-dot" aria-hidden="true"></span><span id="dashboardUpdated"><?= $initialData ? 'Updated ' . dashboardEscape($initialData['generated_at']) : 'Waiting for data' ?></span></div>
                </header>

                <!-- Bootstrap columns keep filters compact on mobile and aligned on larger screens. -->
                <form id="dashboardFilters" class="dash-filters card p-3 mb-4" novalidate>
                    <div class="row g-3 align-items-end">
                        <div class="dash-filter-field col-12 col-sm-6 col-lg-4 col-xxl-3"><label class="form-label" for="dashboardPeriod">Inspection period</label>
                            <select id="dashboardPeriod" name="period" class="form-select">
                                <?php foreach ($periods as $key => $label): ?>
                                    <option value="<?= dashboardEscape($key) ?>" <?= $filters['period'] === $key ? 'selected' : '' ?>><?= dashboardEscape($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($filters['can_select_branch']): ?>
                            <div class="dash-filter-field col-12 col-sm-6 col-lg-4 col-xxl-3"><label class="form-label" for="dashboardBranch">Branch</label>
                                <select id="dashboardBranch" name="branch" class="form-select">
                                    <option value="all" <?= $filters['branch'] === 'all' ? 'selected' : '' ?>>All Branches</option>
                                    <?php
                                    $branchChoices = $initialData['branches'] ?? array();
                                    // Preserve a selected branch even if it is no longer an active dropdown option.
                                    if ($filters['branch'] !== '' && $filters['branch'] !== 'all' && !in_array($filters['branch'], $branchChoices, true)) {
                                        $branchChoices[] = $filters['branch'];
                                    }
                                    foreach ($branchChoices as $branchChoice): ?>
                                        <option value="<?= dashboardEscape($branchChoice) ?>" <?= $filters['branch'] === $branchChoice ? 'selected' : '' ?>><?= dashboardEscape($branchChoice) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <div class="col-12 col-sm-6 col-lg-4 col-xxl-3">
                                <div class="dash-assigned-branch d-flex align-items-center flex-wrap gap-2"><i class="bi bi-geo-alt" aria-hidden="true"></i><span><?= dashboardEscape($filters['branch']) ?></span><small>Assigned branch</small></div>
                            </div>
                        <?php endif; ?>
                        <div class="dash-custom-dates col-12 col-lg-8 col-xxl-4" id="dashboardCustomDates" <?= $filters['period'] !== 'custom' ? 'hidden' : '' ?>>
                            <div class="row g-3">
                                <div class="dash-filter-field col-12 col-sm-6">
                                    <label class="form-label" for="dashboardFrom">From</label>
                                    <input id="dashboardFrom" name="from" type="date" class="form-control" value="<?= dashboardEscape($filters['from']) ?>">
                                </div>
                                <div class="dash-filter-field col-12 col-sm-6">
                                    <label class="form-label" for="dashboardTo">To</label>
                                    <input id="dashboardTo" name="to" type="date" class="form-control" value="<?= dashboardEscape($filters['to']) ?>">
                                </div>
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
                        $statCards = [
                            [
                                'key' => 'active',
                                'label' => 'Total Active Equipment',
                                'icon' => 'bi-box-seam',
                                'color' => 'blue',
                                'description' => 'Current inventory, excluding archived units.'
                            ],
                            [
                                'key' => 'inspected',
                                'label' => 'Unique Equipment Inspected',
                                'icon' => 'bi-check2-circle',
                                'color' => 'green',
                                'description' => 'Active units inspected at least once in this period.'
                            ],
                            [
                                'key' => 'submissions',
                                'label' => 'Total Inspection Submissions',
                                'icon' => 'bi-clipboard-check',
                                'color' => 'blue',
                                'description' => 'Saved inspection records, including re-inspections and archived history.'
                            ],
                            [
                                'key' => 'not_inspected',
                                'label' => 'Equipment Not Yet Inspected',
                                'icon' => 'bi-clock',
                                'color' => 'amber',
                                'description' => 'Active units with no inspection in this period.'
                            ]
                        ];
                        foreach ($statCards as $stat): ?>
                            <div class="col-12 col-sm-6 col-xl-3">
                                <?php if ($stat['key'] === 'not_inspected'): ?>
                                    <?php include 'partials/dashboard-uninspected-card.php'; ?>
                                <?php elseif ($stat['key'] === 'submissions'): ?>
                                    <?php include 'partials/dashboard-submissions-card.php'; ?>
                                <?php elseif ($stat['key'] === 'inspected'): ?>
                                    <?php include 'partials/dashboard-unique-inspected-card.php'; ?>
                                <?php else: ?>
                                    <article class="dash-stat card d-block h-100 p-3 p-lg-4">
                                        <span class="dash-stat-icon d-inline-flex align-items-center justify-content-center mb-3 <?= $stat['color'] ?>"><i class="bi <?= $stat['icon'] ?>" aria-hidden="true"></i></span>
                                        <h3><?= $stat['label'] ?></h3>
                                        <strong data-summary="<?= $stat['key'] ?>"><?= number_format($summary[$stat['key']]) ?></strong>
                                        <p><?= $stat['description'] ?></p>
                                    </article>
                                <?php endif; ?>
                            </div>
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
                    <!-- Active inventory totals stay independent of the inspection date filter. -->
                    <div class="dash-equipment-grid row g-3 align-items-start" id="dashboardEquipmentCards">
                        <?php foreach ($equipmentCards as $equipment): ?>
                            <?php if (($equipment['id'] ?? '') === 'fire_extinguisher'): ?>
                                <div class="col-12 col-md-6" data-equipment="fire_extinguisher" id="fireInventoryCard" data-dashboard-details-card>
                                    <?php renderDashboardFireInventoryDetail($fireInventoryDetail, $equipment); ?>
                                </div>
                            <?php elseif (($equipment['id'] ?? '') === 'chemical_spill_kit'): ?>
                                <div class="col-12 col-md-6" data-equipment="chemical_spill_kit" id="chemicalSpillKitCard" data-dashboard-details-card>
                                    <?php renderDashboardChemicalSpillKitCard($equipment); ?>
                                </div>
                            <?php else: ?>
                                <?php include 'partials/dashboard-equipment-card.php'; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="dash-chart-grid row g-3 g-lg-4 align-items-start" aria-label="Equipment analytics">
                    <div class="col-12 col-xl-8">
                        <article class="dash-panel dash-activity-panel card d-block p-3 p-lg-4">
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
                        </article>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="dash-side-charts row g-3 g-lg-4">
                            <div class="col-12 col-md-6 col-xl-12">
                                <article class="dash-panel card d-block h-100 p-3 p-lg-4">
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
                                </article>
                            </div>
                            <div class="col-12 col-md-6 col-xl-12">
                                <article class="dash-panel card d-block h-100 p-3 p-lg-4">
                                    <div class="dash-panel-heading d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                                        <div>
                                            <h2>Equipment distribution</h2>
                                            <p>Current active inventory</p>
                                        </div>
                                    </div>
                                    <div class="dash-distribution d-flex flex-column gap-3 pt-1" id="dashboardDistribution"></div>
                                </article>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
        <?php include 'partials/footer.php'; ?>
    </div>
    <!-- AJAX reuses this card markup when the backend returns a new equipment type. -->
    <template id="dashboardEquipmentTemplate">
        <?php
        $equipment = [
            'id' => 'template',
            'label' => '',
            'icon' => '',
            'color' => '#0b5ed7',
            'url' => '#',
            'active' => 0,
            'inspected' => 0,
            'not_inspected' => 0
        ];
        include 'partials/dashboard-equipment-card.php';
        ?>
    </template>
    <script type="application/json" id="dashboardBootstrap">
        <?= json_encode(array('data' => $initialData, 'error' => $initialError), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
    </script>
    <script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
    <script src="vendors/simplebar/js/simplebar.min.js"></script>
    <script src="js/dashboard.js?v=<?= hash_file('sha256', __DIR__ . '/js/dashboard.js') ?>"></script>
    <script>
        // The existing dashboard endpoint remains responsible for summary data. This
        // local fragment request only refreshes the Fire Extinguisher detail panel.
        (function () {
            var form = document.getElementById('dashboardFilters');
            var detailRequest = null;
            function bindDetailToggles() {
                document.querySelectorAll('[data-dashboard-details-card]').forEach(function (card) {
                    if (card.dataset.detailBound) return;
                    card.dataset.detailBound = 'true';
                    card.addEventListener('click', function (event) {
                        var button = event.target.closest('.dashboard-details-toggle');
                        if (!button) return;
                        var details = card.querySelector('.dashboard-details');
                        var expanded = button.getAttribute('aria-expanded') === 'true';
                        details.hidden = expanded;
                        button.setAttribute('aria-expanded', String(!expanded));
                        button.querySelector('span').textContent = expanded ? 'View Details' : 'Hide Details';
                    });
                });
            }
            function syncChemicalSpillKitDetails() {
                var card = document.getElementById('chemicalSpillKitCard');
                if (!card || card.dataset.detailValuesBound) return;
                card.dataset.detailValuesBound = 'true';
                function syncValues() {
                    card.querySelectorAll('[data-csk-detail-value]').forEach(function (detailValue) {
                        var source = card.querySelector('[data-module-value="' + detailValue.dataset.cskDetailValue + '"]');
                        if (source) detailValue.textContent = source.textContent;
                    });
                }
                var observer = new MutationObserver(syncValues);
                card.querySelectorAll('[data-module-value]').forEach(function (source) {
                    observer.observe(source, { childList: true, characterData: true, subtree: true });
                });
                syncValues();
            }
            function refreshFireInventoryDetail() {
                var card = document.getElementById('fireInventoryCard');
                if (!card || !form) return;
                var period = document.getElementById('dashboardPeriod');
                var branch = document.getElementById('dashboardBranch');
                var from = document.getElementById('dashboardFrom');
                var to = document.getElementById('dashboardTo');
                var parameters = new URLSearchParams({ fire_inventory_detail: '1', period: period.value });
                if (branch) parameters.set('branch', branch.value);
                if (period.value === 'custom') { parameters.set('from', from.value); parameters.set('to', to.value); }
                if (detailRequest) detailRequest.abort();
                detailRequest = new AbortController();
                fetch('dashboard.php?' + parameters.toString(), { credentials: 'same-origin', cache: 'no-store', signal: detailRequest.signal })
                    .then(function (response) { return response.ok ? response.json() : Promise.reject(new Error('Detail refresh failed')); })
                    .then(function (result) {
                        if (!result.success || !result.html) return;
                        var wasExpanded = card.querySelector('.fe-details-toggle').getAttribute('aria-expanded') === 'true';
                        card.innerHTML = result.html;
                        card.dataset.detailBound = '';
                        bindDetailToggles();
                        if (wasExpanded) card.querySelector('.fe-details-toggle').click();
                    }).catch(function (error) { if (error.name !== 'AbortError') console.warn('Fire inventory detail refresh failed.', error); });
            }
            bindDetailToggles();
            syncChemicalSpillKitDetails();
            if (form) {
                form.addEventListener('submit', function () { window.setTimeout(refreshFireInventoryDetail, 0); });
                form.addEventListener('change', function () { window.setTimeout(refreshFireInventoryDetail, 0); });
            }
        }());
    </script>
    <?php include_once 'notification/session_timeout.php'; ?>
</body>

</html>
