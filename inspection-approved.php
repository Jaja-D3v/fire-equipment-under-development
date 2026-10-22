<?php
require_once 'backend/authentication/only-admin.php';
require_once 'backend/controller/InspectionController.php';
require_once 'backend/controller/DropdownController.php';

$limit = 10;

$page = isset($_GET['page'])
  ? (int) $_GET['page']
  : 1;

if ($page < 1) {
  $page = 1;
}

$fromDate = isset($_GET['from_date']) && is_string($_GET['from_date']) ? trim($_GET['from_date']) : '';
$toDate = isset($_GET['to_date']) && is_string($_GET['to_date']) ? trim($_GET['to_date']) : '';
$search = isset($_GET['search']) && is_string($_GET['search']) ? trim($_GET['search']) : '';
$invalidDateInput = (isset($_GET['from_date']) && !is_string($_GET['from_date'])) ||
  (isset($_GET['to_date']) && !is_string($_GET['to_date']));
$validDateRange = !$invalidDateInput && isValidApprovedInspectionDateRange($fromDate, $toDate);
$invalidDateRange = !$validDateRange;
$fromDate = $fromDate === '' ? null : $fromDate;
$toDate = $toDate === '' ? null : $toDate;

$totalRecords = $validDateRange ? getApprovedApprovalTotal($fromDate, $search, $toDate) : 0;
$totalPages = (int) ceil($totalRecords / $limit);

if ($totalPages > 0 && $page > $totalPages) {
  $page = $totalPages;
}

$offset = ($page - 1) * $limit;
$result = $validDateRange ? getApprovedApprovals($limit, $offset, $fromDate, $search, $toDate) : false;

$paginationParams = $_GET;
unset($paginationParams['page']);
unset($paginationParams['date']);
$paginationQuery = http_build_query($paginationParams);
$inspectionChecklists = [];

if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $inspectionChecklists[] = $row;
  }
}


$branch = $_GET['branch'] ?? ($_SESSION['Branch'] ?? 'all');
$approvedReportIds = $validDateRange ? getAllApprovedApprovalIdsController($fromDate, $branch, $toDate, $search) : [];
$approvedReportIdString = implode(',', $approvedReportIds);
?>

<!DOCTYPE html>

<!-- CoreUI - Free Bootstrap Admin Template
     @version v5.5.0
     @link https://coreui.io/product/free-bootstrap-admin-template/
     Copyright (c) 2026 creativeLabs Łukasz Holeczek
     Licensed under MIT
-->

<html lang="en">

<?php
include 'partials/header.php';
?>

<body class="approved-inspection-page">

  <?php include 'partials/side-nav.php'; ?>
  <div class="wrapper d-flex flex-column min-vh-100">
    <?php include 'partials/header-nav.php'; ?>
    <div class="container-fluid py-0 inspection-content">


      <!-- Page Header -->
      <div
        class="card border-0 shadow-sm text-white mb-3 mt-0 overflow-hidden inspection-header"
        style="background: linear-gradient(135deg, #12217e, #003554);">

        <div class="card-body p-3 p-md-4">
          <div class="row align-items-center g-3">

            <!-- Header Icon -->
            <div class="col-auto">
              <div class="bg-white bg-opacity-25 rounded-3 p-3 fs-2 inspection-header-icon">
                <i class="bi bi-clipboard2-check"></i>
              </div>
            </div>

            <!-- Header Title -->
            <div class="col-12 col-sm">
              <h2 class="fw-bold mb-1">
                Approved Inspection Record
              </h2>

              <p class="mb-0 text-white-50">
                Approved inspection reports.
              </p>
            </div>

            <!-- Generate CSV Report -->
            <div class="col-12 col-md-auto">
              <?php if (!empty($approvedReportIds)): ?>

                <a
                  href="helpers/generate-inspection-report-csv.php?ids=<?= urlencode($approvedReportIdString) ?>"
                  class="btn btn-success w-100 px-3 py-2 text-nowrap">
                  <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                  Generate CSV
                </a>

              <?php else: ?>

                <button
                  type="button"
                  class="btn btn-success w-100 px-3 py-2 text-nowrap"
                  disabled>
                  <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                  Generate CSV
                </button>

              <?php endif; ?>
            </div>

          </div>
        </div>

      </div>


      <!-- Filters -->
      <div class="card border-0 shadow-sm mb-3 inspection-filter-card">
        <div class="card-body">
          <?php if ($invalidDateRange): ?>
            <div class="alert alert-danger" role="alert">
              Enter valid dates and make sure From Date is not later than To Date.
            </div>
          <?php endif; ?>
          <div class="row g-3 align-items-end inspection-approved-toolbar">

            <!-- Search -->
            <div class="col-12 col-xl-3">
              <form method="get" action="inspection-approved.php" class="input-group inspection-approved-search">
                <input type="hidden" name="page" value="1">
                <?php if (!empty($fromDate)): ?><input type="hidden" name="from_date" value="<?= htmlspecialchars($fromDate, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                <?php if (!empty($toDate)): ?><input type="hidden" name="to_date" value="<?= htmlspecialchars($toDate, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                <?php if (isset($_GET['branch'])): ?><input type="hidden" name="branch" value="<?= htmlspecialchars($_GET['branch'], ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                <span class="input-group-text bg-white">
                  <i class="bi bi-search"></i>
                </span>
                <input
                  type="text"
                  class="form-control"
                  id="inspectionSearch"
                  name="search"
                  data-server-search="true"
                  value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                  placeholder="Search by FE code or location...">
                <button type="submit" class="btn btn-primary" aria-label="Search inspections">Search</button>
              </form>
            </div>

            <!-- Date Range Filter -->
            <div class="col-12 col-xl-5">
              <form method="get" action="inspection-approved.php" id="inspectionDateRangeForm" class="row g-2 align-items-end inspection-approved-date-form">
                <input type="hidden" name="page" value="1">
                <?php if ($search !== ''): ?><input type="hidden" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                <?php if (isset($_GET['branch'])): ?><input type="hidden" name="branch" value="<?= htmlspecialchars((string) $_GET['branch'], ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                <div class="col-12 col-md-4">
                  <label for="inspectionFromDate" class="form-label mb-1">From Date</label>
                  <input type="date" class="form-control" id="inspectionFromDate" name="from_date" value="<?= htmlspecialchars($fromDate ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-12 col-md-4">
                  <label for="inspectionToDate" class="form-label mb-1">To Date</label>
                  <input type="date" class="form-control" id="inspectionToDate" name="to_date" value="<?= htmlspecialchars($toDate ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-12 col-md-4 d-flex gap-2 inspection-approved-date-actions">
                  <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                  <button type="button" class="btn btn-outline-secondary flex-fill" id="clearInspectionDates">Clear</button>
                </div>
              </form>
            </div>

            <!-- Branch Filter -->
            <?php if (strtolower(trim($_SESSION['Role'] ?? '')) === 'admin'): ?>

              <?php
              $adminBranch = trim($_SESSION['Branch'] ?? '');
              $selectedBranch = $_GET['branch'] ?? $adminBranch;

              if (empty($selectedBranch)) {
                $selectedBranch = 'all';
              }

              $branches = getAllDropdownBranches();
              ?>

              <div class="col-12 col-sm-6 col-xl-2">
                <label for="branchFilter" class="form-label mb-1">Branch</label>
                <select class="form-select w-100" id="branchFilter">

                  <option
                    value="all"
                    <?= $selectedBranch === 'all' ? 'selected' : '' ?>>
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

                <script>
                  document.getElementById('branchFilter')?.addEventListener('change', function() {
                    const branch = this.value;
                    const fromDate = document.getElementById('inspectionFromDate')?.value || '';
                    const toDate = document.getElementById('inspectionToDate')?.value || '';

                    const url = new URL(window.location.href);
                    url.searchParams.set('page', '1');
                    if (fromDate) url.searchParams.set('from_date', fromDate);
                    else url.searchParams.delete('from_date');
                    if (toDate) url.searchParams.set('to_date', toDate);
                    else url.searchParams.delete('to_date');
                    url.searchParams.delete('date');
                    if (branch) url.searchParams.set('branch', branch);
                    window.location.href = url.toString();
                  });
                </script>
              </div>

            <?php endif; ?>

            <script>
              document.getElementById('inspectionDateRangeForm')?.addEventListener('submit', function(event) {
                const fromDate = document.getElementById('inspectionFromDate').value;
                const toDate = document.getElementById('inspectionToDate').value;
                if (fromDate && toDate && fromDate > toDate) {
                  event.preventDefault();
                  document.getElementById('inspectionToDate').setCustomValidity('To Date must be on or after From Date.');
                  document.getElementById('inspectionToDate').reportValidity();
                }
              });
              ['inspectionFromDate', 'inspectionToDate'].forEach(function(id) {
                document.getElementById(id)?.addEventListener('input', function() {
                  document.getElementById('inspectionToDate').setCustomValidity('');
                });
              });
              document.getElementById('clearInspectionDates')?.addEventListener('click', function() {
                const url = new URL(window.location.href);
                url.searchParams.delete('from_date');
                url.searchParams.delete('to_date');
                url.searchParams.delete('date');
                url.searchParams.set('page', '1');
                window.location.href = url.toString();
              });
            </script>

            <!-- Generate Report -->
            <div class="col-12 col-sm-6 col-xl-2">
              <?php if (!empty($approvedReportIds)): ?>

                <a
                  href="helpers/generate-report.php?ids=<?= urlencode($approvedReportIdString) ?>"
                  class="btn btn-primary w-100 inspection-approved-pdf-button"
                  onclick="generateReport(event, this.href)">
                  <i class="bi bi-file-earmark-pdf me-1"></i>
                  Generate PDF Report
                </a>

              <?php else: ?>

                <button
                  type="button"
                  class="btn btn-primary w-100 inspection-approved-pdf-button"
                  disabled>
                  <i class="bi bi-file-earmark-pdf me-1"></i>
                  Generate Report
                </button>

              <?php endif; ?>
            </div>

          </div>
        </div>
      </div>

      <!-- Inspection Table -->
      <div class="card border-0 shadow-sm inspection-table-card mb-5">

        <div class="card-body p-0">

          <div class="table-responsive inspection-table-wrapper">

            <table class="table table-hover align-middle inspection-table">

              <thead class="table-light">
                <tr>
                  <th class="ps-4 py-3 text-nowrap">
                    FE Code
                  </th>

                  <th class="py-3 text-nowrap">
                    Location
                  </th>

                  <th class="py-3 text-nowrap">
                    Date Inspected
                  </th>

                  <th class="py-3 text-nowrap">
                    Branch
                  </th>

                  <th class="py-3 text-nowrap">
                    Status
                  </th>

                  <th class="py-3 text-nowrap text-end pe-4">
                    Actions
                  </th>
                </tr>
              </thead>

              <tbody id="inspectionList">

                <?php if (!empty($inspectionChecklists)): ?>

                  <?php foreach ($inspectionChecklists as $inspection): ?>

                    <?php
                    $status = $inspection['evaluation_status'] ?? 'Pending';

                    switch ($status) {
                      case 'Approved':
                        $statusClass = 'bg-success-subtle text-success';
                        $statusIcon = 'bi-check-circle-fill';
                        $statusFilterValue = 'approved';
                        $statusLabel = 'Approved';
                        break;

                      case 'Rejected':
                        $statusClass = 'bg-danger-subtle text-danger';
                        $statusIcon = 'bi-x-circle-fill';
                        $statusFilterValue = 'rejected';
                        $statusLabel = 'Rejected';
                        break;

                      case 'Pending':
                      default:
                        $statusClass = 'bg-warning-subtle text-warning-emphasis';
                        $statusIcon = 'bi-clock';
                        $statusFilterValue = 'pending';
                        $statusLabel = 'Pending Approval';
                        break;
                    }

                    $inspectionDate = '';

                    if (!empty($inspection['date_inspected'])) {
                      $timestamp = strtotime($inspection['date_inspected']);

                      if ($timestamp !== false) {
                        $inspectionDate = date('Y-m-d', $timestamp);
                      }
                    }
                    ?>

                    <tr
                      class="inspection-row"
                      data-fe-code="<?= htmlspecialchars(
                                      strtolower($inspection['extinguisher_code'] ?? '')
                                    ) ?>"
                      data-location="<?= htmlspecialchars(
                                        strtolower($inspection['location'] ?? '')
                                      ) ?>"
                      data-status="<?= htmlspecialchars($statusFilterValue) ?>"
                      data-date="<?= htmlspecialchars($inspectionDate) ?>">

                      <td class="ps-4">
                        <span class="text-primary fw-semibold">
                          <?= htmlspecialchars(
                            $inspection['extinguisher_code'] ?? '—'
                          ) ?>
                        </span>
                      </td>

                      <td class="inspection-location-cell">
                        <?= htmlspecialchars(
                          $inspection['location'] ?? '—'
                        ) ?>
                      </td>

                      <td class="text-nowrap">
                        <?php if (!empty($inspection['date_inspected'])): ?>

                          <?php
                          $timestamp = strtotime($inspection['date_inspected']);
                          ?>

                          <?php if ($timestamp !== false): ?>

                            <?= date('M d, Y', $timestamp) ?>

                            <small class="text-body-secondary d-block">
                              <?= date('h:i A', $timestamp) ?>
                            </small>

                          <?php else: ?>

                            —

                          <?php endif; ?>

                        <?php else: ?>

                          —

                        <?php endif; ?>
                      </td>

                      <td>
                        <?= htmlspecialchars(
                          $inspection['branch'] ?? '—'
                        ) ?>
                      </td>

                      <td class="text-center">
                        <span
                          class="badge rounded-pill status-badge <?= $statusClass ?> px-3 py-2">

                          <i class="bi <?= $statusIcon ?> me-1"></i>

                          <?= htmlspecialchars($statusLabel) ?>

                        </span>
                      </td>

                      <td class="text-end pe-4">
                        <div class="inspection-actions">

                          <button
                            type="button"
                            class="btn btn-sm btn-primary"
                            onclick="viewInspection(<?= (int) $inspection['inspect_id'] ?>)">

                            <i class="bi bi-eye me-1"></i>
                            View

                          </button>

                          <?php if ($status === 'Pending'): ?>

                            <button
                              type="button"
                              class="btn btn-sm btn-success"
                              onclick="approveInspection(<?= (int) $inspection['inspect_id'] ?>)">

                              <i class="bi bi-check-lg me-1"></i>
                              Approve

                            </button>

                            <button
                              type="button"
                              class="btn btn-sm btn-danger"
                              onclick="rejectInspection(<?= (int) $inspection['inspect_id'] ?>)">

                              <i class="bi bi-x-lg me-1"></i>
                              Reject
                            </button>

                          <?php endif; ?>

                        </div>
                      </td>

                    </tr>

                  <?php endforeach; ?>

                  <!-- Search Result State -->
                  <tr id="noSearchResult" style="display: none;">

                    <td colspan="6" class="text-center py-5">

                      <div class="text-body-secondary">
                        <i class="bi bi-search fs-1 d-block mb-2"></i>
                        No inspection records found.
                      </div>

                    </td>

                  </tr>

                <?php else: ?>

                  <!-- Empty State -->
                  <tr>
                    <td colspan="6" class="text-center py-5">

                      <div class="text-body-secondary inspection-empty-state">
                        <div>
                          <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                          No inspection records found.
                        </div>
                      </div>

                    </td>
                  </tr>

                <?php endif; ?>

              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="bg-body border-top shadow-sm py-2 inspection-pagination">
            <div class="container-fluid px-3 px-md-4">
              <div class="inspection-pagination-inner d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">

                <div class="text-body-secondary small text-center text-md-start">
                  Showing

                  <strong>
                    <?= $totalRecords > 0 ? $offset + 1 : 0 ?>
                  </strong>

                  -

                  <strong>
                    <?= min($offset + $limit, $totalRecords) ?>
                  </strong>

                  of

                  <strong>
                    <?= $totalRecords ?>
                  </strong>

                  inspections
                </div>

                <?php if ($totalPages > 1): ?>

                  <nav aria-label="Inspection pagination">
                    <ul class="pagination pagination-sm mb-0">
                      <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">

                        <a
                          class="page-link"
                          href="inspection-approved.php?page=<?= max(1, $page - 1) ?><?= $paginationQuery !== '' ? '&' . htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') : '' ?>"
                          aria-label="Previous">
                          <i class="bi bi-chevron-left"></i>
                          <span class="d-none d-lg-inline ms-1">
                            Previous
                          </span>

                        </a>

                      </li>

                      <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                        <li class="page-item d-none d-md-block <?= ($i == $page) ? 'active' : '' ?>">

                          <a
                            class="page-link"
                            href="inspection-approved.php?page=<?= $i ?><?= $paginationQuery !== '' ? '&' . htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') : '' ?>">

                            <?= $i ?>
                          </a>

                        </li>

                      <?php endfor; ?>

                      <li class="page-item d-md-none active">

                        <span class="page-link">
                          <?= $page ?> / <?= $totalPages ?>
                        </span>

                      </li>

                      <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">

                        <a
                          class="page-link"
                          href="inspection-approved.php?page=<?= min($totalPages, $page + 1) ?><?= $paginationQuery !== '' ? '&' . htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') : '' ?>"
                          aria-label="Next">

                          <span class="d-none d-lg-inline me-1">
                            Next
                          </span>

                          <i class="bi bi-chevron-right"></i>

                        </a>

                      </li>
                    </ul>
                  </nav>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include './partials/view-inspection-modal.php'; ?>

    <script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
    <script src="./js/inspection-approvals.js"></script>
    <script src="vendors/simplebar/js/simplebar.min.js"></script>

    <?php
    include_once 'notification/session_timeout.php';
    include_once 'notification/loading-for-generating-report.php';
    ?>

</body>

</html>