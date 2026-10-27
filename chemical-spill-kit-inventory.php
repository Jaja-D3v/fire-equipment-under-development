<?php
require_once 'backend/authentication/SessionChecker.php';
require_once 'backend/controller/DropdownController.php';
?>
<!DOCTYPE html>
<!--
 CoreUI - Free Bootstrap Admin Template
 @version v5.5.0
 @link https://coreui.io/product/free-bootstrap-admin-template/
 Copyright (c) 2026 creativeLabs Łukasz Holeczek
 Licensed under MIT (https://github.com/coreui/coreui-free-bootstrap-admin-template/blob/main/LICENSE)
-->
<html lang="en">
<?php include 'partials/header.php'; ?>
<body>
  <?php include 'partials/side-nav.php'; ?>
  <div class="wrapper d-flex flex-column min-vh-100">
    <?php include 'partials/header-nav.php'; ?>
    <h6 style="margin-left:50px;">ON GOING DEVELOPMENT</h6>
    <?php
    $inspections = $inspections ?? [];
    $totalMonthlyInspections = $totalMonthlyInspections ?? 0;
    $totalRecords = $totalRecords ?? 0;
    $currentPage = max(1, (int)($currentPage ?? ($_GET['page'] ?? 1)));
    $limit = 10;
    $totalPages = max(1, (int)ceil($totalRecords / $limit));
    $currentPage = min($currentPage, $totalPages);
    $branches = $branches ?? [];
    $search = trim($_GET['search'] ?? '');
    $filterDate = trim($_GET['date'] ?? '');
    $selectedBranch = trim($_GET['branch'] ?? 'all');
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
              <h4 class="fs-5 mb-1 fw-semibold">Chemical Spill Kit Inventory Record</h4>
              <p class="mb-0 small text-white-50">Chemical spill inventory</p>
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
                  <div class="text-body-secondary small mb-1">Total Inspected This Month</div>
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
            <div class="col-12 col-lg-5">
              <div class="input-group input-group-sm">
                <input type="search" name="search" id="cskSearch" class="form-control" placeholder="Search by CSK code or location..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                <button class="btn btn-primary" type="submit">Search</button>
              </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
              <input type="date" name="date" class="form-control form-control-sm" value="<?= htmlspecialchars($filterDate, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12 col-md-6 col-lg-4">

              <!-- Branch Filter (Admin Only) -->
              <?php if (strtolower(trim($_SESSION['Role'] ?? '')) === 'admin'): ?>
                <?php
                $adminBranch = trim($_SESSION['Branch'] ?? '');
                $selectedBranch = $_GET['branch'] ?? $adminBranch;
                if (empty($selectedBranch)) {
                  $selectedBranch = 'all';
                }
                $branches = getAllDropdownBranches();
                ?>
                <select class="form-select form-select-sm w-100" id="branchFilter" name="branch">
                  <!-- All Branches -->
                  <option value="all" <?= $selectedBranch === 'all' ? 'selected' : '' ?>>All Branches</option>
                  <?php if (!empty($branches)): ?>
                    <?php foreach ($branches as $branch): ?>
                      <option value="<?= htmlspecialchars($branch['value'], ENT_QUOTES, 'UTF-8') ?>" <?= $selectedBranch === $branch['value'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($branch['value'], ENT_QUOTES, 'UTF-8') ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              <?php endif; ?>
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
                    <td class="px-3 fw-semibold text-primary small text-nowrap"><?= htmlspecialchars((string)$inspectionCode, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="small"><?= htmlspecialchars((string)$inspectionLocation, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="small text-nowrap">
                      <?php if (!empty($inspectionDate) && strtotime($inspectionDate) !== false): ?>
                        <?= date('M d, Y', strtotime($inspectionDate)) ?>
                        <div class="text-body-secondary"><?= date('h:i A', strtotime($inspectionDate)) ?></div>
                      <?php else: ?>
                        <span class="text-body-secondary">—</span>
                      <?php endif; ?>
                    </td>
                    <td><span class="badge rounded-pill <?= $statusClass ?> fw-medium px-3 py-2"><?= htmlspecialchars((string)$inspectionStatus, ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="small"><?= htmlspecialchars((string)$inspectionBranch, ENT_QUOTES, 'UTF-8') ?></td>
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
              <?php
              $previousParams = $_GET;
              $previousParams['page'] = max(1, $currentPage - 1);
              unset($previousParams['limit']);
              if (isset($previousParams['search']) && trim((string)$previousParams['search']) === '') {
                  unset($previousParams['search']);
              }
              $previousUrl = '?' . http_build_query($previousParams);
              $nextParams = $_GET;
              $nextParams['page'] = min($totalPages, $currentPage + 1);
              unset($nextParams['limit']);
              if (isset($nextParams['search']) && trim((string)$nextParams['search']) === '') {
                  unset($nextParams['search']);
              }
              $nextUrl = '?' . http_build_query($nextParams);
              ?>
              <nav aria-label="Inspection pagination">
                <ul class="pagination pagination-sm mb-0">
                  <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= htmlspecialchars($previousUrl, ENT_QUOTES, 'UTF-8') ?>">Previous</a>
                  </li>
                  <li class="page-item active">
                    <span class="page-link"><?= $currentPage ?></span>
                  </li>
                  <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= htmlspecialchars($nextUrl, ENT_QUOTES, 'UTF-8') ?>">Next</a>
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

    // Update the branch filter and reset pagination when the selected branch changes.
    const branchFilter = document.getElementById('branchFilter');
    if (branchFilter) {
      branchFilter.addEventListener('change', function() {
        const form = branchFilter.closest('form');
        if (form) {
          const pageInput = form.querySelector('input[name="page"]');
          if (pageInput) pageInput.value = '1';
          form.submit();
        }
      });
    }
    
    // Remove the search parameter from the URL when the search field is empty.
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('cskSearch');
      if (!searchInput) return;
      searchInput.addEventListener('input', function() {
        const url = new URL(window.location.href);
        const searchValue = this.value.trim();
        if (searchValue === '') {
          url.searchParams.delete('search');
          url.searchParams.delete('page');
        } else {
          url.searchParams.set('search', searchValue);
          url.searchParams.set('page', '1');
        }
        window.history.replaceState({}, '', url.pathname + url.search + url.hash);
      });
    });
  </script>
  <?php include_once 'notification/session_timeout.php'; ?>
</body>
</html>
