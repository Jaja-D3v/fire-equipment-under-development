<?php
require_once 'backend/authentication/SessionChecker.php';
include './backend/controller/DashboardController.php';
include './backend/controller/DropdownController.php';
include './backend/controller/QRCodeGeneratorController.php';

$totalFE = getTotalFireExtinguishersDashboard();
$totalSpareFE = getTotalSpareFireExtinguishers();
$goodSpareFE = getTotalGoodSpareFireExtinguishers();
$notGoodSpareFE = getTotalNotGoodSpareFireExtinguishers();
$totalInstalledFE = getTotalInstalledFireExtinguishers();
$goodInstalledFE = getTotalGoodInstalledFireExtinguishers();
$notGoodInstalledFE = getTotalNotGoodInstalledFireExtinguishers();
$allGoodCondition = getGoodCondition();
$allNotGoodCondition = getNotGoodCondition();
$typeCounts = getFireExtinguisherTypeCountsController();
$monthlyInspectionData = getInspectLastThreeMonths();
$expirySoon = getAllExpiringCount();
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'partials/header.php'; ?>

<body>
  <?php include 'partials/side-nav.php'; ?>

  <div class="wrapper d-flex flex-column min-vh-100">
    <?php include 'partials/header-nav.php'; ?>

    <div class="main-content flex-grow-1">
      <?php include_once 'notification/fe-expiration-notice.php'; ?>

      <main class="kp-dashboard">

        <!-- Dashboard heading  -->
        <div class="kp-dashboard-head">
          <div class="kp-title-wrap">
            <div class="kp-title-icon">
              <i class="fa-solid fa-fire-extinguisher"></i>
            </div>

            <div>
              <h1 class="kp-title">Fire Equipment Dashboard</h1>
              <p class="kp-subtitle">
                Simple overview of fire-safety equipment and inspection status.
              </p>
            </div>
          </div>

          <div class="kp-tools">
            <div class="kp-date">
              <span>As of <strong id="analyticsDate"></strong></span>
            </div>

            <?php if (strtolower(trim($_SESSION['Role'] ?? '')) === 'admin'): ?>
              <?php
              $adminBranch = trim($_SESSION['Branch'] ?? '');
              $selectedBranch = $_GET['branch'] ?? $adminBranch;

              if (empty($selectedBranch)) {
                $selectedBranch = 'all';
              }

              $branches = getAllDropdownBranches();
              ?>

              <select class="form-select kp-branch" id="branchFilter">
                <option value="all" <?= $selectedBranch === 'all' ? 'selected' : '' ?>>
                  All Branches
                </option>

                <?php if (!empty($branches)): ?>
                  <?php foreach ($branches as $branch): ?>
                    <option
                      value="<?= htmlspecialchars($branch['value']) ?>"
                      <?= $selectedBranch === $branch['value'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($branch['value']) ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            <?php endif; ?>
          </div>
        </div>

        <script>
          document.addEventListener('DOMContentLoaded', function() {
            const branchFilter = document.getElementById('branchFilter');

            if (!branchFilter) return;
            branchFilter.addEventListener('change', function() {
              const url = new URL(window.location.href);
              url.searchParams.set('branch', this.value);
              window.location.href = url.toString();
            });
          });
        </script>

        <!-- FIRE EXTINGUISHER METRICS  REAL DATA - NOTHING HARD-CODED HERE -->
        <div class="row g-3 mb-3">

          <!-- Active FE -->
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="kp-card kp-metric">
              <div class="kp-metric-top">
                <div class="kp-metric-icon blue">
                  <i class="fa-solid fa-layer-group"></i>
                </div>
              </div>
              <div class="kp-metric-label">Active Fire Extinguishers</div>
              <div class="kp-metric-number"><?= (int)$totalFE ?></div>
              <div class="kp-metric-caption">Registered in the system</div>
              <div class="kp-status">
                <span class="kp-pill good">
                  Good <?= (int)$allGoodCondition ?>
                </span>
                <span class="kp-pill bad">
                  No Good <?= (int)$allNotGoodCondition ?>
                </span>
              </div>
            </div>
          </div>

          <!-- Spare FE -->
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="kp-card kp-metric">
              <div class="kp-metric-top">
                <div class="kp-metric-icon green">
                  <i class="fa-solid fa-boxes-stacked"></i>
                </div>
              </div>
              <div class="kp-metric-label">Spare Fire Extinguishers</div>
              <div class="kp-metric-number"><?= (int)$totalSpareFE ?></div>
              <div class="kp-metric-caption">Available spare units</div>
              <div class="kp-status">
                <span class="kp-pill good">
                  Good <?= (int)$goodSpareFE ?>
                </span>
                <span class="kp-pill bad">
                  No Good <?= (int)$notGoodSpareFE ?>
                </span>
              </div>
            </div>
          </div>

          <!-- Installed FE -->
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="kp-card kp-metric">
              <div class="kp-metric-top">
                <div class="kp-metric-icon blue">
                  <i class="fa-solid fa-fire-extinguisher"></i>
                </div>
              </div>

              <div class="kp-metric-label">Installed Fire Extinguishers</div>
              <div class="kp-metric-number"><?= (int)$totalInstalledFE ?></div>
              <div class="kp-metric-caption">Currently installed units</div>
              <div class="kp-status">
                <span class="kp-pill good">
                  Good <?= (int)$goodInstalledFE ?>
                </span>
                <span class="kp-pill bad">
                  No Good <?= (int)$notGoodInstalledFE ?>
                </span>
              </div>
            </div>
          </div>

          <!-- Expiring -->
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="kp-card kp-metric">
              <div class="kp-metric-top">
                <div class="kp-metric-icon orange">
                  <i class="bi bi-calendar-x-fill"></i>
                </div>
              </div>
              <div class="kp-metric-label">Expiry </div>
              <div class="kp-metric-number"><?= (int)$expirySoon ?></div>
              <div class="kp-metric-caption">Within the next 2 months</div>
              <div class="kp-status">
                <span class="kp-pill due">
                  Requires attention
                </span>
              </div>
            </div>
          </div>

        </div>

        <?php

        $inspectionData = [];

        for ($i = 2; $i >= 0; $i--) {

          $date = new DateTime();
          $date->modify("-{$i} month");
          $monthKey = $date->format('Y-m');
          $inspectionData[$monthKey] = [
            'label' => $date->format('M'),
            'total' => 0
          ];
        }

        foreach ($monthlyInspectionData as $row) {

          if (isset($inspectionData[$row['inspection_month']])) {

            $inspectionData[$row['inspection_month']]['total'] = (int)$row['total_inspected'];
          }
        }

        $chartLabels = [];
        $chartValues = [];

        foreach ($inspectionData as $data) {

          $chartLabels[] = $data['label'];
          $chartValues[] = $data['total'];
        }

        $totalInspected3Months = array_sum($chartValues);
        $previousMonth = $chartValues[1] ?? 0;
        $currentMonth = $chartValues[2] ?? 0;

        if ($currentMonth > $previousMonth) {

          $trendIcon = 'bi-arrow-up';
          $trendText = 'Up from last month';
          $trendClass = 'text-success';
        } elseif ($currentMonth < $previousMonth) {

          $trendIcon = 'bi-arrow-down';
          $trendText = 'Down from last month';
          $trendClass = 'text-danger';
        } else {

          $trendIcon = 'bi-dash';
          $trendText = 'No change';
          $trendClass = 'text-body-secondary';
        }

        $totalExtinguishers = 0;

        foreach ($typeCounts as $typeData) {

          $totalExtinguishers += (int)$typeData['total'];
        }
        ?>

        <!-- ANALYTICS -->
        <div class="row g-3">

          <!-- Monthly inspections -->
          <div class="col-12 col-xl-6">
            <div class="kp-panel">
              <div class="kp-panel-head">
                <div class="kp-panel-title-wrap">
                  <div class="kp-panel-icon">
                    <i class="bi bi-bar-chart-fill"></i>
                  </div>

                  <div>
                    <h2 class="kp-panel-title">Monthly Inspections</h2>
                    <div class="kp-panel-subtitle">
                      Latest 3 months
                    </div>
                  </div>
                </div>

                <div class="text-end">
                  <div class="small text-body-secondary">Total</div>
                  <div class="fw-bold fs-5"><?= number_format($totalInspected3Months) ?></div>
                  <div class="small <?= $trendClass ?>">
                    <i class="bi <?= $trendIcon ?>"></i>
                    <?= htmlspecialchars($trendText) ?>
                  </div>
                </div>
              </div>

              <div class="kp-panel-body">
                <div class="kp-chart">
                  <canvas id="monthlyInspectionChart"></canvas>
                </div>
              </div>
            </div>
          </div>

          <!-- FE status -->
          <div class="col-12 col-md-6 col-xl-3">
            <div class="kp-panel">
              <div class="kp-panel-head">
                <div class="kp-panel-title-wrap">
                  <div class="kp-panel-icon">
                    <i class="bi bi-pie-chart-fill"></i>
                  </div>

                  <div>
                    <h2 class="kp-panel-title">FE Condition</h2>
                    <div class="kp-panel-subtitle">Current condition</div>
                  </div>
                </div>
              </div>

              <div class="kp-panel-body">
                <div style="height:210px; position:relative;">
                  <canvas id="feStatusChart"></canvas>
                </div>

                <div class="d-flex justify-content-center gap-2 flex-wrap mt-2">
                  <span class="kp-pill good">
                    Good <?= (int)$allGoodCondition ?>
                  </span>

                  <span class="kp-pill due">
                    Due <?= (int)$expirySoon ?>
                  </span>

                  <span class="kp-pill bad">
                    No Good <?= (int)$allNotGoodCondition ?>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick summary -->
          <div class="col-12 col-md-6 col-xl-3">
            <div class="kp-panel">
              <div class="kp-panel-head">
                <div class="kp-panel-title-wrap">
                  <div class="kp-panel-icon">
                    <i class="bi bi-list-check"></i>
                  </div>

                  <div>
                    <h2 class="kp-panel-title">Quick Summary</h2>
                    <div class="kp-panel-subtitle">Fire extinguisher overview</div>
                  </div>
                </div>
              </div>

              <div class="kp-panel-body">
                <ul class="kp-summary">
                  <li>
                    <span class="kp-summary-label">
                      <i class="fa-solid fa-layer-group text-primary"></i>
                      Active Units
                    </span>
                    <span class="kp-summary-value"><?= (int)$totalFE ?></span>
                  </li>

                  <li>
                    <span class="kp-summary-label">
                      <i class="fa-solid fa-boxes-stacked text-success"></i>
                      Spare Units
                    </span>
                    <span class="kp-summary-value"><?= (int)$totalSpareFE ?></span>
                  </li>

                  <li>
                    <span class="kp-summary-label">
                      <i class="fa-solid fa-fire-extinguisher text-primary"></i>
                      Installed
                    </span>
                    <span class="kp-summary-value"><?= (int)$totalInstalledFE ?></span>
                  </li>

                  <li>
                    <span class="kp-summary-label">
                      <i class="fa-solid fa-circle-check text-success"></i>
                      Good
                    </span>
                    <span class="kp-summary-value"><?= (int)$allGoodCondition ?></span>
                  </li>

                  <li>
                    <span class="kp-summary-label">
                      <i class="fa-solid fa-circle-xmark text-danger"></i>
                      No Good
                    </span>
                    <span class="kp-summary-value"><?= (int)$allNotGoodCondition ?></span>
                  </li>

                  <li>
                    <span class="kp-summary-label">
                      <i class="bi bi-calendar-x-fill text-warning"></i>
                      Expiring
                    </span>
                    <span class="kp-summary-value"><?= (int)$expirySoon ?></span>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- FE By Type -->
          <div class="col-12 col-lg-6">
            <div class="kp-panel">
              <div class="kp-panel-head">
                <div class="kp-panel-title-wrap">
                  <div class="kp-panel-icon">
                    <i class="bi bi-grid-fill"></i>
                  </div>

                  <div>
                    <h2 class="kp-panel-title">Fire Extinguishers by Type</h2>
                    <div class="kp-panel-subtitle">
                      Distribution of registered extinguisher types
                    </div>
                  </div>
                </div>
              </div>

              <div class="kp-panel-body">
                <?php if (!empty($typeCounts)): ?>

                  <?php foreach ($typeCounts as $typeData): ?>
                    <?php
                    $type = $typeData['type'];
                    $count = (int)$typeData['total'];

                    $percentage = $totalExtinguishers > 0
                      ? ($count / $totalExtinguishers) * 100
                      : 0;
                    ?>

                    <div class="kp-type-row">
                      <div class="kp-type-label">
                        <span><?= htmlspecialchars($type) ?></span>
                        <span><?= $count ?></span>
                      </div>

                      <div class="kp-progress">
                        <div
                          class="kp-progress-bar"
                          style="width:<?= $percentage ?>%;">
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>

                <?php else: ?>

                  <div class="text-center py-4 text-body-secondary">
                    <i class="bi bi-fire fs-2"></i>
                    <div class="small mt-2">
                      No fire extinguisher type data available.
                    </div>
                  </div>

                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Other 3 categories are intentionally hard-coded for now. -->
          <div class="col-12 col-lg-6">
            <div class="kp-panel">
              <div class="kp-panel-head">
                <div class="kp-panel-title-wrap">
                  <div class="kp-panel-icon">
                    <i class="bi bi-layers-fill"></i>
                  </div>

                  <div>
                    <h2 class="kp-panel-title">Equipment Categories</h2>
                    <div class="kp-panel-subtitle">
                      Fire-safety equipment modules
                    </div>
                  </div>
                </div>
              </div>

              <div class="kp-panel-body">
                <div class="row g-2">

                  <!-- REAL -->
                  <div class="col-6 col-sm-3">
                    <div class="kp-category kp-category-fe">
                      <i class="fa-solid fa-fire-extinguisher"></i>
                      <span class="kp-category-number"><?= (int)$totalFE ?></span>
                      <span class="kp-category-name">Fire Extinguisher</span>
                    </div>
                  </div>

                  <!-- TEMPORARY -->
                  <div class="col-6 col-sm-3">
                    <div class="kp-category kp-category-hose">
                      <i class="fa-solid fa-life-ring"></i>
                      <span class="kp-category-number">0</span>
                      <span class="kp-category-name">Fire Hose</span>
                    </div>
                  </div>

                  <!-- TEMPORARY -->
                  <div class="col-6 col-sm-3">
                    <div class="kp-category kp-category-alarm">
                      <i class="fa-solid fa-bell"></i>
                      <span class="kp-category-number">0</span>
                      <span class="kp-category-name">Pull Alarm</span>
                    </div>
                  </div>

                  <!-- TEMPORARY -->
                  <div class="col-6 col-sm-3">
                    <div class="kp-category kp-category-exit">
                      <i class="fa-solid fa-person-running"></i>
                      <span class="kp-category-number">0</span>
                      <span class="kp-category-name">Exit Light</span>
                    </div>
                  </div>

                </div>

                <div class="kp-soft-note">
                  <i class="bi bi-info-circle me-1"></i>
                  Fire Hose, Pull Alarm, and Exit Light data will be connected
                  when their respective modules are completed.
                </div>
              </div>
            </div>
          </div>

          <!-- Expiring -->
          <div class="col-12">
            <div class="kp-panel">
              <div class="kp-panel-head">
                <div class="kp-panel-title-wrap">
                  <div class="kp-panel-icon"
                    style="background:var(--kp-orange-soft);color:var(--kp-orange);">
                    <i class="bi bi-clock-fill"></i>
                  </div>

                  <div>
                    <h2 class="kp-panel-title">Expiring Soon</h2>
                    <div class="kp-panel-subtitle">
                      Fire extinguishers within 2 months of expiration
                    </div>
                  </div>
                </div>
              </div>

              <div class="kp-panel-body">
                <div class="kp-expiration">
                  <div>
                    <div class="kp-expiration-number">
                      <?= (int)$expirySoon ?>
                    </div>

                    <div class="kp-expiration-title">
                      Fire extinguisher units requiring attention
                    </div>

                    <div class="kp-expiration-text">
                      Check expiration dates and schedule the required action.
                    </div>
                  </div>

                  <div class="kp-expiration-icon">
                    <i class="bi bi-calendar-x-fill"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Existing dashboard.js data -->
        <script>
          const labels = <?= json_encode($chartLabels) ?>;
          const values = <?= json_encode($chartValues) ?>;

          const feGood = <?= (int)$allGoodCondition ?>;
          const feDue = <?= (int)$expirySoon ?>;
          const feNotGood = <?= (int)$allNotGoodCondition ?>;

          document.addEventListener('DOMContentLoaded', function() {
            const dateElement = document.getElementById('analyticsDate');

            if (dateElement) {
              const now = new Date();

              dateElement.textContent = now.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
              });
            }

            if (typeof Chart !== 'undefined') {
              const statusCanvas = document.getElementById('feStatusChart');

              if (statusCanvas) {
                new Chart(statusCanvas, {
                  type: 'doughnut',
                  data: {
                    labels: ['Good', 'Due Soon', 'No Good'],
                    datasets: [{
                      data: [feGood, feDue, feNotGood],
                      backgroundColor: [
                        '#16a66a',
                        '#ef9200',
                        '#ee4d5a'
                      ],
                      borderWidth: 0,
                      hoverOffset: 4
                    }]
                  },
                  options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '74%',
                    plugins: {
                      legend: {
                        display: false
                      }
                    }
                  }
                });
              }
            }
          });
        </script>

        <script src="js/dashboard.js"></script>

      </main>
    </div>

    <?php include 'partials/footer.php'; ?>
  </div>

  <!-- CoreUI -->
  <script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
  <script src="vendors/simplebar/js/simplebar.min.js"></script>

  <script>
    const header = document.querySelector("header.header");

    document.addEventListener("scroll", () => {
      if (header) {
        header.classList.toggle(
          "shadow-sm",
          document.documentElement.scrollTop > 0
        );
      }
    });
  </script>

  <?php include_once 'notification/session_timeout.php'; ?>
</body>
</html>