<?php
require_once 'backend/authentication/SuperAdminGuard.php';
requireSuperAdminAccess(false);
?>
<!DOCTYPE html>
<html lang="en">
<?php
ob_start();
include 'partials/header.php';
$superAdminHeaderMarkup = ob_get_clean();
echo str_replace(
  '</head>',
  '  <link rel="stylesheet" href="css/super-admin-monitoring.css">' . PHP_EOL . '</head>',
  $superAdminHeaderMarkup
);
?>

<body>
  <?php include 'partials/side-nav.php'; ?>
  <div class="wrapper d-flex flex-column min-vh-100">
    <?php include 'partials/header-nav.php'; ?>
    <main class="main-content flex-grow-1 sa-monitoring sa-dashboard container-fluid px-3 px-lg-4 py-4">
      <header class="sa-page-head">
        <div class="sa-head-copy">
          <div class="sa-eyebrow"><span class="sa-eyebrow-mark"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></span> SYSTEM MONITORING</div>
          <h1>Usage overview</h1>
          <p>Understand activity, inspections, and branch engagement across KPFEMS.</p>
        </div>
        <div class="sa-date-toolbar" aria-label="Dashboard date range">
          <label class="sa-toolbar-label" for="saRange">Reporting period</label>
          <div class="sa-date-controls">
            <select id="saRange" class="form-select" aria-label="Reporting period">
              <option value="today">Today</option>
              <option value="7d">Last 7 days</option>
              <option value="30d">Last 30 days</option>
              <option value="this_month" selected>This month</option>
              <option value="previous_month">Previous month</option>
              <option value="custom">Custom range</option>
            </select>
            <input type="date" id="saFrom" class="form-control d-none" aria-label="Start date">
            <input type="date" id="saTo" class="form-control d-none" aria-label="End date">
          </div>
        </div>
      </header>
      <div class="sa-section-heading">
        <div><span class="sa-section-kicker">AT A GLANCE</span><h2>System overview</h2></div>
        <p>Key counts for the selected reporting period</p>
      </div>
      <div id="saNotice" class="alert alert-light border" role="status">Loading monitoring data…</div>
      <section class="row g-3 mb-4" aria-label="System overview">
        <div class="col-12 col-sm-6 col-xl-3">
          <article class="sa-card sa-stat"><span class="sa-icon"><i class="fa-solid fa-users"></i></span>
            <div class="sa-label">Registered users</div>
            <div class="sa-value" id="saUsers">N/A</div>
            <div class="sa-caption">KPFEMS accounts only</div>
          </article>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <article class="sa-card sa-stat"><span class="sa-icon"><i class="fa-solid fa-user-clock"></i></span>
            <div class="sa-label">Users active in range</div>
            <div class="sa-value" id="saActive">N/A</div>
            <div class="sa-caption">Users with a recorded activity log in the selected range</div>
          </article>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <article class="sa-card sa-stat"><span class="sa-icon"><i class="fa-solid fa-clipboard-check"></i></span>
            <div class="sa-label">Inspections in range</div>
            <div class="sa-value" id="saInspections">N/A</div>
            <div class="sa-caption">Fire extinguisher and spill kit submissions</div>
          </article>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <article class="sa-card sa-stat"><span class="sa-icon"><i class="fa-solid fa-building"></i></span>
            <div class="sa-label">Branches with inspections</div>
            <div class="sa-value" id="saBranches">N/A</div>
            <div class="sa-caption">Branches present in inspection records</div>
          </article>
        </div>
      </section>
      <div class="sa-section-heading">
        <div><span class="sa-section-kicker">SYSTEM ACTIVITY</span><h2>Engagement &amp; inspection status</h2></div>
        <p>Recorded events and review outcomes</p>
      </div>
      <section class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
          <article class="sa-card h-100">
            <div class="sa-card-head">
              <div>
                <h2>Recorded usage trend</h2>
                <p>Activity logs and inspection submissions shown separately per day</p>
              </div>
            </div>
            <div class="sa-chart-wrap"><canvas id="saTrendChart" aria-label="Recorded usage trend chart"></canvas><div class="sa-chart-empty" aria-live="polite">No recorded activity for this period.</div></div>
          </article>
        </div>
        <div class="col-12 col-xl-5">
          <article class="sa-card h-100">
            <div class="sa-card-head">
              <div>
                <h2>Inspection status</h2>
                <p id="saStatusNote">Status breakdown from available inspection records</p>
              </div>
            </div>
            <div class="sa-status-grid">
              <div class="sa-status-item status-submitted"><span>Submitted</span><strong id="saSubmitted">N/A</strong></div>
              <div class="sa-status-item status-approved"><span>Approved</span><strong id="saApproved">N/A</strong></div>
              <div class="sa-status-item status-pending"><span>Pending</span><strong id="saPending">N/A</strong></div>
              <div class="sa-status-item status-rejected"><span>Rejected</span><strong id="saRejected">N/A</strong></div>
            </div>
            <div class="sa-chart-wrap sa-doughnut"><canvas id="saStatusChart" aria-label="Inspection status chart"></canvas><div class="sa-chart-empty" aria-live="polite">No inspection status data in this period.</div></div>
          </article>
        </div>
      </section>
      <div class="sa-section-heading">
        <div><span class="sa-section-kicker">USAGE TRENDS</span><h2>Activity over time</h2></div>
        <p>Compare activity log entries with inspection submissions</p>
      </div>
      <section class="row g-3 mb-4">
        <div class="col-12 col-xl-6">
          <article class="sa-card h-100">
            <div class="sa-card-head">
              <div>
                <h2>Weekly activity</h2>
                <p>Activity logs and inspections shown separately</p>
              </div>
            </div>
            <div class="sa-chart-wrap"><canvas id="saWeeklyChart" aria-label="Weekly activity trend"></canvas><div class="sa-chart-empty" aria-live="polite">No weekly activity for this period.</div></div>
          </article>
        </div>
        <div class="col-12 col-xl-6">
          <article class="sa-card h-100">
            <div class="sa-card-head">
              <div>
                <h2>Monthly activity</h2>
                <p>Activity logs and inspections shown separately</p>
              </div>
            </div>
            <div class="sa-chart-wrap"><canvas id="saMonthlyChart" aria-label="Monthly activity trend"></canvas><div class="sa-chart-empty" aria-live="polite">No monthly activity for this period.</div></div>
          </article>
        </div>
      </section>
      <div class="sa-section-heading">
        <div><span class="sa-section-kicker">INSPECTION ANALYTICS</span><h2>Inspection activity</h2></div>
        <p>Submission trends and equipment mix</p>
      </div>
      <section class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
          <article class="sa-card h-100">
            <div class="sa-card-head">
              <div>
                <h2>Inspection submissions by month</h2>
                <p>Fire extinguisher and spill kit records</p>
              </div>
            </div>
            <div class="sa-chart-wrap"><canvas id="saInspectionTrend" aria-label="Monthly inspection submission trend"></canvas><div class="sa-chart-empty" aria-live="polite">No inspection submissions in this period.</div></div>
          </article>
        </div>
        <div class="col-12 col-xl-5">
          <article class="sa-card h-100">
            <div class="sa-card-head">
              <div>
                <h2>Inspection by equipment type</h2>
                <p>Recorded inspections in the selected range</p>
              </div>
            </div>
            <div class="sa-chart-wrap"><canvas id="saEquipmentChart" aria-label="Inspection activity by equipment type"></canvas><div class="sa-chart-empty" aria-live="polite">No equipment type records in this period.</div></div>
          </article>
        </div>
      </section>
      <div class="sa-section-heading">
        <div><span class="sa-section-kicker">BRANCHES &amp; USERS</span><h2>Engagement by branch and user</h2></div>
        <p>Available activity records grouped by source</p>
      </div>
      <section class="row g-3 mb-4">
        <div class="col-12 col-xl-6">
          <article class="sa-card h-100">
            <div class="sa-card-head">
              <div>
                <h2>Most active users</h2>
                <p>Ranked by available activity log entries</p>
              </div>
            </div>
            <div class="sa-chart-wrap"><canvas id="saTopUsersChart" aria-label="Most active users chart"></canvas><div class="sa-chart-empty" aria-live="polite">No activity log entries in this period.</div></div>
          </article>
        </div>
      </section>
      <section class="row g-3 mb-4">
        <div class="col-12">
          <article class="sa-card">
            <div class="sa-card-head">
              <div>
                <h2>Branch usage</h2>
                <p>Inspection submissions by branch. Activity logs have no branch field, so no inspection record does not confirm inactivity.</p>
              </div>
            </div>
            <div class="sa-chart-wrap sa-branch-chart"><canvas id="saBranchChart" aria-label="Inspection comparison by branch"></canvas><div class="sa-chart-empty" aria-live="polite">No branch inspection submissions in this period.</div></div>
            <div class="table-responsive">
              <table class="table sa-table">
                <thead>
                  <tr>
                    <th>Branch</th>
                    <th>Registered users</th>
                    <th>Recorded activities</th>
                    <th>Inspection submissions</th>
                    <th>Last inspection</th>
                    <th>Usage status</th>
                  </tr>
                </thead>
                <tbody id="saBranchRows">
                  <tr>
                    <td colspan="6" class="text-center text-body-secondary py-4">Loading…</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </article>
        </div>
      </section>
      <div class="sa-section-heading">
        <div><span class="sa-section-kicker">PEOPLE</span><h2>User monitoring</h2></div>
        <p>Account details and recorded engagement</p>
      </div>
      <section class="row g-3 mb-4">
        <div class="col-12">
          <article class="sa-card">
            <div class="sa-card-head">
              <div>
                <h3>User activity</h3>
                <p>KPFEMS accounts only. Login timestamps use the identity field when populated; activity comes from existing logs.</p>
              </div>
              <div class="sa-filter-toolbar"><label class="sa-search-field"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><input id="saUserSearch" class="form-control" type="search" placeholder="Search name or login ID" aria-label="Search users"></label><select id="saBranchFilter" class="form-select" aria-label="Filter users by branch">
                  <option value="">All branches</option>
                </select><select id="saRoleFilter" class="form-select" aria-label="Filter users by role">
                  <option value="">All roles</option>
                </select><button id="saClearUserFilters" class="sa-clear-filters" type="button">Clear</button></div>
            </div>
            <div class="table-responsive">
              <table class="table sa-table">
                <thead>
                  <tr>
                    <th>Employee</th>
                    <th>Login ID</th>
                    <th>Role</th>
                    <th>Branch</th>
                    <th>Last login</th>
                    <th>Last activity in range</th>
                    <th>Activities in range</th>
                  </tr>
                </thead>
                <tbody id="saUserRows">
                  <tr>
                    <td colspan="7" class="text-center text-body-secondary py-4">Loading…</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="sa-pagination">
              <span id="saUserCount" class="sa-pagination-count"></span>
              <nav class="sa-pager" aria-label="User table pages">
                <button class="sa-page-control" id="saUserPrev" type="button" aria-label="Previous page"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                <div class="sa-page-list" id="saUserPages"></div>
                <button class="sa-page-control" id="saUserNext" type="button" aria-label="Next page"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                <span class="sa-page-info" id="saUserPageInfo" aria-live="polite"></span>
              </nav>
            </div>
          </article>
        </div>
      </section>
      <div class="sa-section-heading">
        <div><span class="sa-section-kicker">AUDIT TRAIL</span><h2>Activity log</h2></div>
        <p>Search and review recorded system events</p>
      </div>
      <section class="row g-3">
        <div class="col-12">
          <article class="sa-card">
            <div class="sa-card-head">
              <div>
                <h3>Recorded events</h3>
                <p>Module text searches descriptions; branch attribution is unavailable.</p>
              </div>
              <div class="sa-filter-toolbar sa-log-filters"><label class="sa-search-field"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><input id="saSearch" class="form-control" type="search" placeholder="Search activity" aria-label="Search activity logs"></label><input id="saUserFilter" class="form-control" type="search" placeholder="Filter user" aria-label="Filter by user"><input id="saActionFilter" class="form-control" type="search" placeholder="Action type" aria-label="Filter by action type"><input id="saModuleFilter" class="form-control" type="search" placeholder="Module text" aria-label="Filter module text in descriptions"><button id="saClearLogFilters" class="sa-clear-filters" type="button">Clear</button></div>
            </div>
            <div class="table-responsive">
              <table class="table sa-table">
                <thead>
                  <tr>
                    <th>Employee / User</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>Date and time</th>
                  </tr>
                </thead>
                <tbody id="saLogRows">
                  <tr>
                    <td colspan="4" class="text-center text-body-secondary py-4">Loading…</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="sa-pagination">
              <span id="saLogCount" class="sa-pagination-count"></span>
              <nav class="sa-pager" aria-label="Activity log pages">
                <button class="sa-page-control" id="saPrev" type="button" aria-label="Previous page"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                <div class="sa-page-list" id="saLogPages"></div>
                <button class="sa-page-control" id="saNext" type="button" aria-label="Next page"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                <span class="sa-page-info" id="saLogPageInfo" aria-live="polite"></span>
              </nav>
            </div>
          </article>
        </div>
      </section>
      <p class="sa-footnote mt-3">Metrics reflect available recorded data. No recorded activity does not confirm that a branch or user is inactive.</p>
    </main>
  </div>
  <script src="js/super-admin-monitoring.js"></script>
</body>

</html>
