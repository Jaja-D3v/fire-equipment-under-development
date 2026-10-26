<?php require_once 'backend/authentication/SessionChecker.php'; ?>

<!DOCTYPE html>
<!--
* CoreUI - Free Bootstrap Admin Template
* @version v5.5.0
* @link https://coreui.io/product/free-bootstrap-admin-template/
* Copyright (c) 2026 creativeLabs Łukasz Holeczek
* Licensed under MIT (https://github.com/coreui/coreui-free-bootstrap-admin-template/blob/main/LICENSE)
-->

<html lang="en">
<?php include 'partials/header.php'; ?>

<body>
  <?php include 'partials/side-nav.php'; ?>
  <div class="wrapper d-flex flex-column min-vh-100">
    <?php include 'partials/header-nav.php'; ?>
    <H6 style="margin-left:50px;">ON GOING DEVELOPMENT</H6>



    // CSK INSPECTION RECORD - DATA FROM CONTROLLER
    <?PHP
    $inspections = $inspections ?? [];
    $totalMonthlyInspections = $totalMonthlyInspections ?? 0;
    $totalRecords = $totalRecords ?? 0;
    $currentPage = max(1, (int)($currentPage ?? ($_GET['page'] ?? 1)));
    $limit = (int)($limit ?? ($_GET['limit'] ?? 10));
    $limit = in_array($limit, [5, 10, 20, 50], true) ? $limit : 10;
    $totalPages = max(1, (int)ceil($totalRecords / $limit));
    $currentPage = min($currentPage, $totalPages);
    $branches = $branches ?? [];
    $search = trim($_GET['search'] ?? '');
    $filterDate = trim($_GET['date'] ?? '');
    $selectedBranch = trim($_GET['branch'] ?? 'all');
    // HELPER: SAFE HTML OUTPUT
    if (!function_exists('cskEscape')) {
      function cskEscape($value)
      {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
      }
    }
    // HELPER: KEEP CURRENT FILTERS WHEN CHANGING PAGE
    function cskPageUrl($page, $limit)
    {
      $params = $_GET;
      $params['page'] = $page;
      $params['limit'] = $limit;
      return '?' . http_build_query($params);
    }
    ?>
    <!-- MAIN CONTENT -->
    <div class="container-fluid px-3 px-lg-4 py-3">
      <!-- PAGE HEADER -->
      <div class="card border-0 rounded-3 shadow-sm mb-3 overflow-hidden">
        <div class="card-body bg-primary text-white py-3 px-3 px-md-4">
          <div class="d-flex align-items-center gap-3">
            <div class="bg-white bg-opacity-25 rounded-3 p-2 d-flex align-items-center justify-content-center">
              <span class="fs-3">▦</span>
            </div>
            <div>
              <h4 class="fs-5 mb-1 fw-semibold">Chemical Spill Kit Inspection Record</h4>
              <p class="mb-0 small text-white-50">Chemical spill kit inspection reports.</p>
            </div>
          </div>
        </div>
      </div>


      <!-- MONTHLY INSPECTION SUMMARY -->
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body px-3 py-3">
          <div class="row align-items-center">
            <div class="col">
              <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px">
                  <i class="bi bi-clipboard-check fs-4"></i>
                </div>
                <div>
                  <div class="text-body-secondary small mb-1">Total Inspections This Month</div>
                  <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-3 fw-bold text-primary lh-1"><?= number_format($totalMonthlyInspections) ?></span>
                    <span class="small text-body-secondary">inspection records</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-auto">
              <div class="border-start ps-3 ms-2">
                <div class="text-body-secondary small mb-1">Reporting Period</div>
                <div class="fw-semibold small"><?= date('F Y') ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- SEARCH AND FILTERS -->
      <div class="card border-0 rounded-3 shadow-sm mb-3">
        <div class="card-body p-3">
          <form method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="page" value="1">
            <input type="hidden" name="limit" value="<?= $limit ?>">
            <div class="col-12 col-lg-5">
              <div class="input-group input-group-sm">
                <input type="search" name="search" class="form-control" placeholder="Search by CSK code or location..." value="<?= cskEscape($search) ?>">
                <button class="btn btn-primary" type="submit">Search</button>
              </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
              <input type="date" name="date" class="form-control form-control-sm" value="<?= cskEscape($filterDate) ?>">
            </div>
            <div class="col-12 col-md-6 col-lg-4">
              <select name="branch" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="all" <?= $selectedBranch === 'all' ? 'selected' : '' ?>>All Branches</option>
                <?php foreach ($branches as $branch): ?>
                  <?php $branchName = is_array($branch) ? ($branch['branch'] ?? '') : $branch; ?>
                  <option value="<?= cskEscape($branchName) ?>" <?= $selectedBranch === (string)$branchName ? 'selected' : '' ?>><?= cskEscape($branchName) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </form>
        </div>
      </div>
      <!-- INSPECTION TABLE -->
      <div class="card border-0 rounded-3 shadow-sm overflow-hidden">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="px-3 py-3 small text-secondary text-uppercase text-nowrap">CSK Code</th>
                <th class="py-3 small text-secondary text-uppercase">Location</th>
                <th class="py-3 small text-secondary text-uppercase text-nowrap">Date Inspected</th>
                <th class="py-3 small text-secondary text-uppercase">Status</th>
                <th class="py-3 small text-secondary text-uppercase">Branch</th>
                <th class="px-3 py-3 small text-secondary text-uppercase text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($inspections)): ?>
                <?php foreach ($inspections as $inspection): ?>
                  <?php
                  // EXPECTED KEYS FROM CONTROLLER/MODEL
                  $inspectionId = $inspection['inspection_id'] ?? 0;
                  $inspectionCode = $inspection['chemical_spill_kit_code'] ?? '';
                  $inspectionLocation = $inspection['location'] ?? '';
                  $inspectionDate = $inspection['inspection_date'] ?? '';
                  $inspectionStatus = $inspection['inspection_status'] ?? 'Pending';
                  $inspectionBranch = $inspection['branch'] ?? '';
                  $statusClass = match (strtolower((string)$inspectionStatus)) {
                    'approved', 'complete', 'completed' => 'bg-success-subtle text-success-emphasis',
                    'rejected' => 'bg-danger-subtle text-danger-emphasis',
                    default => 'bg-warning-subtle text-warning-emphasis'
                  };
                  ?>
                  <tr>
                    <td class="px-3 fw-semibold text-primary small text-nowrap"><?= cskEscape($inspectionCode) ?></td>
                    <td class="small"><?= cskEscape($inspectionLocation) ?></td>
                    <td class="small text-nowrap">
                      <?php if (!empty($inspectionDate) && strtotime($inspectionDate) !== false): ?>
                        <?= date('M d, Y', strtotime($inspectionDate)) ?>
                        <div class="text-body-secondary"><?= date('h:i A', strtotime($inspectionDate)) ?></div>
                      <?php else: ?>
                        <span class="text-body-secondary">—</span>
                      <?php endif; ?>
                    </td>
                    <td><span class="badge rounded-pill <?= $statusClass ?> fw-medium px-3 py-2"><?= cskEscape($inspectionStatus) ?></span></td>
                    <td class="small"><?= cskEscape($inspectionBranch) ?></td>
                    <td class="px-3 text-end">
                      <!-- CHANGE THIS URL IF YOUR VIEW PAGE USES A DIFFERENT FILENAME -->
                      <a href="chemical-spill-kit-inspection-view.php?id=<?= urlencode((string)$inspectionId) ?>" class="btn btn-primary btn-sm">View</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <!-- EMPTY STATE -->
                <tr>
                  <td colspan="6" class="text-center py-5">
                    <div class="text-body-secondary small">No chemical spill kit inspection records found.</div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <!-- RECORD COUNT AND PAGINATION -->
        <div class="card-footer bg-white border-top py-3 px-3">
          <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <span class="small text-body-secondary">
              Showing
              <strong><?= $totalRecords > 0 ? (($currentPage - 1) * $limit + 1) : 0 ?> - <?= min($currentPage * $limit, $totalRecords) ?></strong>
              of <strong><?= number_format($totalRecords) ?></strong> inspections
            </span>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <form method="GET" class="d-flex align-items-center gap-2">
                <?php foreach ($_GET as $key => $value): ?>
                  <?php if (!in_array($key, ['limit', 'page'], true) && is_scalar($value)): ?>
                    <input type="hidden" name="<?= cskEscape($key) ?>" value="<?= cskEscape($value) ?>">
                  <?php endif; ?>
                <?php endforeach; ?>
                <label for="cskLimit" class="small text-body-secondary">Rows</label>
                <select id="cskLimit" name="limit" class="form-select form-select-sm" onchange="this.form.submit()">
                  <?php foreach ([5, 10, 20, 50] as $rowLimit): ?>
                    <option value="<?= $rowLimit ?>" <?= $limit === $rowLimit ? 'selected' : '' ?>><?= $rowLimit ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
              <nav aria-label="Inspection pagination">
                <ul class="pagination pagination-sm mb-0">
                  <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= cskEscape(cskPageUrl(max(1, $currentPage - 1), $limit)) ?>">Previous</a>
                  </li>
                  <li class="page-item active">
                    <span class="page-link"><?= $currentPage ?></span>
                  </li>
                  <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= cskEscape(cskPageUrl(min($totalPages, $currentPage + 1), $limit)) ?>">Next</a>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>






    <!-- CONTENT HERE -->


  </div>
  </div>
  <?php include 'partials/footer.php'; ?>
  </div>
  <!-- CoreUI and necessary plugins-->
  <script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
  <script src="vendors/simplebar/js/simplebar.min.js"></script>
  <script>
    const header = document.querySelector("header.header");

    document.addEventListener("scroll", () => {
      if (header) {
        header.classList.toggle("shadow-sm", document.documentElement.scrollTop > 0);
      }
    });
  </script>
  <?php include_once 'notification/session_timeout.php'; ?>

</body>

</html>