<?php
require_once 'backend/authentication/SessionChecker.php';
include './backend/controller/FireExtinguisherController.php';
include './backend/controller/DropdownController.php';
include 'backend/controller/QRCodeGeneratorController.php';
include_once 'notification/loading-for-generating-report.php';

$branchDropdown = getAllDropdownBranches();
?>

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
    <?php
    include 'partials/header-nav.php';
    include_once 'notification/updated_fe_success.php';
    include_once 'notification/delete_fe_success.php';
    include_once 'notification/added_fe_success.php';
    ?>
    <div class="container-fluid py-0">

      <div class="card border-0 shadow-sm text-white mb-1 mt-0 overflow-hidden" style="background: linear-gradient(135deg, #203D70, #55A9E8);">

        <div class="card-body p-4">
          <div class="row align-items-center g-3">

            <!-- Icon -->
            <div class="col-auto">
              <div class="bg-white bg-opacity-10 rounded-3 p-3 fs-3">
                <i class="bi bi-fire"></i>
              </div>
            </div>

            <!-- Title & Description -->
            <div class="col">
              <h2 class="fw-bold mb-1">
                Active Fire Extinguishers
              </h2>

              <p class="mb-0 text-white-50">
                Manage and monitor all active and registered fire extinguishers.
              </p>
            </div>

            <!-- Generate Report -->
            <div class="col-12 col-md-auto">
              <?php if (strtolower(trim($_SESSION['Role'] ?? '')) === 'admin'): ?>
                <button
                  type="button"
                  class="btn btn-light px-3 py-2 w-100"
                  id="generateInventoryReportBtn">
                  <i class="bi bi-file-earmark-pdf me-1"></i>
                  Generate Inventory Report
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="body flex-grow-1">
      <div class="container-fluid py-0">
        <div class="container py-4">
          <div class="row justify-content-center">

            <!-- Content -->
            <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-3 mb-3">

              <!-- Sort - Left -->
              <div>
                <div class="input-group">
                  <span class="input-group-text bg-body border-end-0">
                    <i class="bi bi-funnel text-primary"></i>
                  </span>

                  <select
                    id="conditionFilter"
                    class="form-select border-start-0 ps-1"
                    aria-label="Filter by condition">

                    <option value="all"
                      <?= ($_GET['condition'] ?? 'all') === 'all'
                        ? 'selected'
                        : '' ?>>
                      All Conditions
                    </option>

                    <option value="Good"
                      <?= ($_GET['condition'] ?? '') === 'Good'
                        ? 'selected'
                        : '' ?>>
                      Good Condition
                    </option>

                    <option value="No Good"
                      <?= ($_GET['condition'] ?? '') === 'No Good'
                        ? 'selected'
                        : '' ?>>
                      No Good
                    </option>

                  </select>
                </div>
              </div>

              <!-- Placement Type Filter -->
              <div>
                <div class="input-group">
                  <span class="input-group-text bg-body border-end-0">
                    <i class="bi bi-pin-map text-primary"></i>
                  </span>

                  <select
                    id="placementTypeFilter"
                    class="form-select border-start-0 ps-1">

                    <option value="all"
                      <?= ($_GET['placement'] ?? 'all') === 'all'
                        ? 'selected'
                        : '' ?>>
                      All Placement Types
                    </option>

                    <option value="installed"
                      <?= ($_GET['placement'] ?? '') === 'installed'
                        ? 'selected'
                        : '' ?>>
                      Installed
                    </option>

                    <option value="spare"
                      <?= ($_GET['placement'] ?? '') === 'spare'
                        ? 'selected'
                        : '' ?>>
                      Spare
                    </option>

                  </select>
                </div>
              </div>


              <!-- Branch Filter - ADMIN ONLY -->
              <?php if (strtolower(trim($_SESSION['Role'] ?? '')) === 'admin'): ?>

                <?php
                // Branch ng naka-login na Admin
                $adminBranch = trim($_SESSION['Branch'] ?? '');
                $selectedBranch = $_GET['branch'] ?? $adminBranch;
                if (empty($selectedBranch)) {
                  $selectedBranch = 'all';
                }
                // Get all branches
                $branches = getAllDropdownBranches();
                ?>

                <div class="col-12 col-md-auto">

                  <select
                    class="form-select"
                    id="branchFilter"
                    style="min-width: 190px;">

                    <!-- All Branches -->
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
                </div>
              <?php endif; ?>

              <!-- Add + Search - Right -->
              <div class="d-flex flex-column flex-md-row gap-2 ms-lg-auto">

                <button
                  type="button"
                  class="btn btn-primary text-nowrap"
                  data-bs-toggle="modal"
                  data-bs-target="#addFireExtinguisherModal">
                  <i class="bi bi-plus-lg me-1"></i>
                  Add Fire Extinguisher
                </button>

                <div class="input-group">
                  <input
                    type="text"
                    id="fireExtinguisherSearchInput"
                    class="form-control"
                    placeholder="Search..."
                    value="<?= htmlspecialchars(
                              $_GET['search'] ?? '',
                              ENT_QUOTES,
                              'UTF-8'
                            ) ?>">

                  <button id="searchFireExtinguisherBtn" class="btn btn-primary" type="button">
                    Search
                  </button>
                </div>

              </div>
            </div>
            <?php

            $limit = 10;
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

            if ($page < 1) {
              $page = 1;
            }

            $offset = ($page - 1) * $limit;

            // PLACEMENT TYPE FILTER
            $placementType = strtolower(trim($_GET['placement'] ?? 'all'));
            $conditionType = trim($_GET['condition'] ?? 'all');
            $search = trim($_GET['search'] ?? '');

            // GET FIRE EXTINGUISHERS
            $info = getAllFireExtinguishers(
              $limit,
              $offset,
              $placementType,
              $conditionType,
              $search
            );

            // GET FILTERED TOTAL
            $totalRecords = getTotalFireExtinguishers(
              $placementType,
              $conditionType,
              $search
            );

            $totalPages = (int) ceil(
              $totalRecords / $limit
            );

            // PREVENT INVALID PAGE
            if ($totalPages > 0 && $page > $totalPages) {

              $page = $totalPages;
              $offset = ($page - 1) * $limit;
              $info = getAllFireExtinguishers(
                $limit,
                $offset,
                $placementType,
                $conditionType,
                $search
              );
            }
            ?>

            <div id="inventoryResults">
              <!-- Content here -->
              <?php foreach ($info as $data): ?>

                <?php
                $condition = $data['condition_status'];

                if ($condition === 'Good') {
                  $badgeClass = 'bg-success-subtle text-success';
                  $statusIcon = 'bi-check-circle-fill';
                } else {
                  $badgeClass = 'bg-danger-subtle text-danger';
                  $statusIcon = 'bi-exclamation-circle-fill';
                }

                // ___expiration and running days
                $expirationBadge = null;
                $expirationBadgeClass = '';
                $expirationIcon = '';

                if (!empty($data['expiration_date'])) {

                  $today = new DateTime('today');
                  $expirationDate = new DateTime($data['expiration_date']);

                  // Two months from today
                  $twoMonthsFromNow = (clone $today)->modify('+2 months');

                  if ($expirationDate < $today) {

                    // Already expired
                    $expiredDays = $today->diff($expirationDate)->days;

                    $expirationBadge = "Expired {$expiredDays} day(s) ago";
                    $expirationBadgeClass = 'bg-danger-subtle text-danger';
                    $expirationIcon = 'bi-exclamation-triangle-fill';
                  } elseif ($expirationDate <= $twoMonthsFromNow) {

                    // Within 2 months before expiration
                    $remainingDays = $today->diff($expirationDate)->days;

                    $expirationBadge = "Expires in {$remainingDays} days";
                    $expirationBadgeClass = 'bg-warning-subtle text-warning-emphasis';
                    $expirationIcon = 'bi-hourglass-split';
                  }
                }
                ?>

                <div class="card border border-primary-subtle shadow-sm mb-2 extinguisher-card overflow-hidden"
                  data-placement="<?= htmlspecialchars($data['placement'] ?? '') ?>">

                  <div class="card-body p-2 p-md-3">

                    <!-- TOP SECTION -->
                    <div class="d-flex align-items-center gap-3">

                      <!-- Icon -->
                      <div
                        class="d-flex align-items-center justify-content-center
                           bg-primary bg-opacity-10 text-primary
                           rounded-3 flex-shrink-0"
                        style="width: 50px; height: 50px;">

                        <i class="bi bi-fire fs-4"></i>

                      </div>

                      <!-- Title -->
                      <div class="flex-grow-1 min-width-0">

                        <div class="mb-1">
                          <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1"
                            style="font-size: 0.65rem;">

                            <i class="bi bi-shield-fill me-1"></i>
                            Fire Safety Equipment
                          </span>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap">

                          <div class="fw-bold fs-5 lh-sm">
                            Fire Extinguisher
                          </div>

                          <span class="text-body-secondary small extinguisher-code">
                            <?= htmlspecialchars($data['extinguisher_code']) ?>
                          </span>

                        </div>
                      </div>

                      <!-- Status -->
                      <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-sm-end gap-2">

                        <!-- Condition -->
                        <span class="badge <?= $badgeClass ?> rounded-pill px-3 py-2 text-nowrap">
                          <i class="bi <?= $statusIcon ?> me-1"></i>
                          <?= htmlspecialchars($condition) ?>
                        </span>

                        <!-- Expiration -->
                        <?php if ($expirationBadge !== null): ?>

                          <span
                            class="badge <?= $expirationBadgeClass ?> rounded-pill px-3 py-2 text-nowrap">
                            <i class="bi <?= $expirationIcon ?> me-1"></i>
                            <?= htmlspecialchars($expirationBadge) ?>
                          </span>
                        <?php endif; ?>
                      </div>
                    </div>

                    <!-- ============================= -->
                    <!-- DETAILS + ACTION -->
                    <!-- ============================= -->
                    <div class="row g-2 g-md-3 mt-2 align-items-center">

                      <!-- FE Code -->
                      <div class="col-6 col-md">
                        <div class="d-flex align-items-center gap-2">
                          <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3 flex-shrink-0"
                            style="width: 32px; height: 32px;">
                            <i class="bi bi-qr-code"></i>
                          </div>

                          <div class="min-width-0">
                            <div class="text-body-secondary small lh-1">
                              FE Code
                            </div>
                            <div class="fw-semibold small text-truncate">
                              <?= htmlspecialchars($data['extinguisher_code']) ?>
                            </div>
                          </div>
                        </div>
                      </div>


                      <!-- Capacity -->
                      <div class="col-6 col-md">
                        <div class="d-flex align-items-center gap-2">

                          <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3 flex-shrink-0"
                            style="width: 32px; height: 32px;">
                            <i class="bi bi-box-seam"></i>
                          </div>

                          <div class="min-width-0">
                            <div class="text-body-secondary small lh-1">
                              Capacity
                            </div>

                            <div class="fw-semibold small text-truncate">
                              <?= htmlspecialchars($data['capacity']) ?>
                            </div>
                          </div>
                        </div>
                      </div>


                      <!-- Type -->
                      <div class="col-6 col-md">
                        <div class="d-flex align-items-center gap-2">

                          <div class="d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-3 flex-shrink-0"
                            style="width: 32px; height: 32px;">
                            <i class="bi bi-fire"></i>
                          </div>

                          <div class="min-width-0">
                            <div class="text-body-secondary small lh-1">
                              Type
                            </div>

                            <div
                              class="fw-semibold small text-truncate"
                              title="<?= htmlspecialchars($data['type']) ?>">

                              <?= htmlspecialchars($data['type']) ?>

                            </div>
                          </div>
                        </div>
                      </div>


                      <!-- Location -->
                      <div class="col-6 col-md">

                        <div class="d-flex align-items-center gap-2">

                          <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3 flex-shrink-0" style="width: 32px; height: 32px;">
                            <i class="bi bi-geo-alt"></i>
                          </div>

                          <div class="min-width-0">
                            <div class="text-body-secondary small lh-1">
                              Location
                            </div>
                            <div
                              class="fw-semibold small text-truncate extinguisher-location"
                              title="<?= htmlspecialchars($data['location']) ?>">
                              <?= htmlspecialchars($data['location']) ?>
                            </div>
                          </div>

                        </div>
                      </div>

                      <!-- Branch -->
                      <div class="col-6 col-md">
                        <div class="d-flex align-items-center gap-2">
                          <div class="d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-3 flex-shrink-0" style="width: 32px; height: 32px;">
                            <i class="bi bi-building"></i>
                          </div>

                          <div class="min-width-0">
                            <div class="text-body-secondary small lh-1">
                              Branch
                            </div>

                            <div
                              class="fw-semibold small text-truncate"
                              title="<?= htmlspecialchars($data['branch']) ?>">
                              <?= htmlspecialchars($data['branch']) ?>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Last Inspected -->
                      <div class="col-6 col-md">
                        <div class="d-flex align-items-center gap-2">
                          <div class="d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info rounded-3 flex-shrink-0" style="width: 32px; height: 32px;">
                            <i class="bi bi-calendar-check"></i>
                          </div>

                          <div class="min-width-0">
                            <div class="text-body-secondary small lh-1">
                              Last Inspected
                            </div>

                            <div
                              class="fw-semibold small text-truncate"
                              title="<?= !empty($data['last_date_inspected'])
                                        ? htmlspecialchars($data['last_date_inspected'])
                                        : 'Not inspected' ?>">

                              <?php if (!empty($data['last_date_inspected'])): ?>
                                <?= htmlspecialchars(
                                  date(
                                    'M d, Y',
                                    strtotime($data['last_date_inspected'])
                                  )
                                ) ?>

                              <?php else: ?>
                                N/A
                              <?php endif; ?>

                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- View -->
                      <div class="col-12 col-md-auto">
                        <button
                          type="button"
                          class="btn btn-sm btn-primary w-100 px-4 view-extinguisher-btn"
                          data-id="<?= htmlspecialchars($data['extinguisher_id']) ?>"
                          data-bs-toggle="modal"
                          data-bs-target="#viewFireExtinguisherModal">
                          <i class="bi bi-eye me-1"></i>
                          View
                        </button>
                      </div>

                    </div>
                  </div>
                </div>

              <?php endforeach; ?>


              <!-- PAGINATION -->
              <div id="inventoryPagination" class="card border border-primary-subtle shadow-sm rounded-3 mt-3">
                <div class="card-footer bg-body border-0 px-3 py-3">
                  <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3">
                    <small class="text-body-secondary" style="font-size: 12px;">
                      <?php if ($totalRecords > 0): ?>
                        Showing
                        <strong class="text-body"><?= $offset + 1 ?></strong>
                        to
                        <strong class="text-body">
                          <?= min($offset + $limit, $totalRecords) ?>
                        </strong>
                        of
                        <strong class="text-body"><?= $totalRecords ?></strong>
                        entries
                      <?php else: ?>
                        Showing 0 of 0 entries
                      <?php endif; ?>
                    </small>

                    <?php if ($totalPages > 1): ?>

                      <?php
                      $currentPageUrl = basename($_SERVER['PHP_SELF']);

                      $paginationParams = $_GET;
                      unset($paginationParams['page']);
                      unset($paginationParams['success-add']);
                      unset($paginationParams['success-update']);

                      $previousParams = $paginationParams;
                      $previousParams['page'] = max(1, $page - 1);

                      $nextParams = $paginationParams;
                      $nextParams['page'] = min($totalPages, $page + 1);
                      ?>

                      <nav aria-label="Fire extinguisher pagination">
                        <ul class="pagination pagination-sm mb-0">

                          <!-- Previous -->
                          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <?php if ($page > 1): ?>

                              <a
                                class="page-link"
                                href="<?= htmlspecialchars($currentPageUrl, ENT_QUOTES, 'UTF-8') ?>?<?= http_build_query($previousParams) ?>"
                                aria-label="Previous">
                                <i class="bi bi-chevron-left"></i>
                                <span class="d-none d-sm-inline ms-1">Previous</span>
                              </a>

                            <?php else: ?>

                              <span class="page-link">
                                <i class="bi bi-chevron-left"></i>
                                <span class="d-none d-sm-inline ms-1">Previous</span>
                              </span>

                            <?php endif; ?>
                          </li>

                          <!-- Page Numbers -->
                          <?php
                          // Show only 5 consecutive page numbers
                          $visiblePages = 5;

                          $startPage = max(1, $page - 2);
                          $endPage = min($totalPages, $startPage + $visiblePages - 1);

                          // Adjust the starting page when near the end
                          $startPage = max(1, $endPage - $visiblePages + 1);

                          // First page and leading ellipsis
                          if ($startPage > 1):
                            $pageParams = $paginationParams;
                            $pageParams['page'] = 1;
                          ?>
                            <li class="page-item">
                              <a
                                class="page-link"
                                href="<?= htmlspecialchars($currentPageUrl, ENT_QUOTES, 'UTF-8') ?>?<?= http_build_query($pageParams) ?>">
                                1
                              </a>
                            </li>

                            <?php if ($startPage > 2): ?>
                              <li class="page-item disabled">
                                <span class="page-link">&hellip;</span>
                              </li>
                            <?php endif; ?>
                          <?php endif; ?>

                          <!-- Five visible page numbers -->
                          <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <?php
                            $pageParams = $paginationParams;
                            $pageParams['page'] = $i;
                            ?>

                            <li class="page-item <?= $i === (int) $page ? 'active' : '' ?>">
                              <a
                                class="page-link"
                                href="<?= htmlspecialchars($currentPageUrl, ENT_QUOTES, 'UTF-8') ?>?<?= http_build_query($pageParams) ?>"
                                <?= $i === (int) $page ? 'aria-current="page"' : '' ?>>
                                <?= $i ?>
                              </a>
                            </li>
                          <?php endfor; ?>

                          <!-- Trailing ellipsis and last page -->
                          <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                              <li class="page-item disabled">
                                <span class="page-link">&hellip;</span>
                              </li>
                            <?php endif; ?>

                            <?php
                            $pageParams = $paginationParams;
                            $pageParams['page'] = $totalPages;
                            ?>

                            <li class="page-item <?= (int) $page === (int) $totalPages ? 'active' : '' ?>">
                              <a
                                class="page-link"
                                href="<?= htmlspecialchars($currentPageUrl, ENT_QUOTES, 'UTF-8') ?>?<?= http_build_query($pageParams) ?>"
                                <?= (int) $page === (int) $totalPages ? 'aria-current="page"' : '' ?>>
                                <?= $totalPages ?>
                              </a>
                            </li>
                          <?php endif; ?>


                          <!-- Next -->
                          <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <?php if ($page < $totalPages): ?>

                              <a
                                class="page-link"
                                href="<?= htmlspecialchars($currentPageUrl, ENT_QUOTES, 'UTF-8') ?>?<?= http_build_query($nextParams) ?>"
                                aria-label="Next">
                                <span class="d-none d-sm-inline me-1">Next</span>
                                <i class="bi bi-chevron-right"></i>
                              </a>

                            <?php else: ?>

                              <span class="page-link">
                                <span class="d-none d-sm-inline me-1">Next</span>
                                <i class="bi bi-chevron-right"></i>
                              </span>

                            <?php endif; ?>
                          </li>

                        </ul>
                      </nav>

                    <?php endif; ?>

                  </div>
                </div>
              </div>
            </div>



            <?php include 'partials/edit-fire-extinguisher-modal.php' ?>
            <!-- this form is for add new extinguisher -->
            <div
              class="modal fade"
              id="addFireExtinguisherModal"
              tabindex="-1"
              aria-labelledby="addFireExtinguisherModalLabel"
              aria-hidden="true">
              <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content rounded-4 border-0 shadow">

                  <!-- Header -->
                  <div class="modal-header px-4 py-3">
                    <div>
                      <h5 class="modal-title fw-semibold mb-1" id="addFireExtinguisherModalLabel">
                        Add Fire Extinguisher
                      </h5>

                      <small class="text-body-secondary">
                        Register a new fire extinguisher
                      </small>
                    </div>

                    <button
                      type="button"
                      class="btn-close"
                      data-bs-dismiss="modal"
                      aria-label="Close"></button>
                  </div>

                  <!-- Body -->
                  <div class="modal-body px-4 py-4">

                    <form
                      class="row g-3"
                      action="backend/controller/FireExtinguisherController.php"
                      method="post">

                      <input
                        type="hidden"
                        name="action"
                        value="add-new-extinguisher">

                      <!-- Fire Extinguisher Code -->
                      <div class="col-md-6">
                        <label for="extinguisherCode" class="form-label">
                          Fire Extinguisher Code
                        </label>

                        <input
                          type="text"
                          class="form-control"
                          id="extinguisherCode"
                          name="extinguisher_code"
                          placeholder="e.g. FE-001"
                          required>

                        <div id="extinguisherCodeFeedback" class="small mt-1"></div>
                      </div>

                      <!-- Type -->
                      <div class="col-md-6">
                        <label for="type" class="form-label">
                          Type
                        </label>

                        <select
                          id="type"
                          name="type"
                          class="form-select"
                          required>
                          <option value="" selected disabled>
                            Select type
                          </option>

                          <?php
                          $types = getAllTypeDropdownController();

                          if (!empty($types)):
                            foreach ($types as $type):
                          ?>
                              <option value="<?= htmlspecialchars($type['value']) ?>">
                                <?= htmlspecialchars($type['value']) ?>
                              </option>
                          <?php
                            endforeach;
                          endif;
                          ?>
                        </select>
                      </div>

                      <!-- Capacity -->
                      <div class="col-md-4">
                        <label for="capacity" class="form-label">
                          Capacity
                        </label>

                        <select
                          class="form-select"
                          id="capacity"
                          name="capacity"
                          required>
                          <option value="" selected disabled>
                            Select capacity
                          </option>

                          <?php
                          $capacities = getAllCapacityDropdownController();

                          if (!empty($capacities)):
                            foreach ($capacities as $capacity):
                          ?>
                              <option value="<?= htmlspecialchars($capacity['value']) ?>">
                                <?= htmlspecialchars($capacity['value']) ?>
                              </option>
                          <?php
                            endforeach;
                          endif;
                          ?>
                        </select>
                      </div>

                      <!-- Class -->
                      <div class="col-md-4">
                        <label for="fireClass" class="form-label">
                          Fire Class
                        </label>

                        <select id="fireClass" name="class" class="form-select" required>
                          <option value="" selected disabled>
                            Select class
                          </option>

                          <?php
                          $classes = getAllClassDropdownController();

                          if (!empty($classes)):
                            foreach ($classes as $class):
                          ?>
                              <option value="<?= htmlspecialchars($class['value']) ?>">
                                <?= htmlspecialchars($class['value']) ?>
                              </option>
                          <?php
                            endforeach;
                          endif;
                          ?>

                        </select>
                      </div>

                      <!-- Placement -->
                      <div class="col-md-4">
                        <label for="placement" class="form-label">
                          Placement
                        </label>

                        <select id="placement" name="placement" class="form-select" required>
                          <option value="" selected disabled>
                            Select placement
                          </option>

                          <?php
                          $placements = getAllPlacementDropdownController();

                          if (!empty($placements)):
                            foreach ($placements as $placement):
                          ?>
                              <option value="<?= htmlspecialchars($placement['value']) ?>">
                                <?= htmlspecialchars($placement['value']) ?>
                              </option>
                          <?php
                            endforeach;
                          endif;
                          ?>
                        </select>
                      </div>

                      <!-- Location -->
                      <div class="col-12 col-md-8">
                        <label for="location" class="form-label">
                          Location
                        </label>

                        <input
                          type="text"
                          class="form-control"
                          id="location"
                          name="location"
                          placeholder="e.g. Building 1 - 2nd Floor"
                          required>
                      </div>

                      <!-- Condition -->
                      <div class="col-12 col-md-4">
                        <label for="conditionStatus" class="form-label">
                          Condition
                        </label>

                        <select id="conditionStatus" name="condition_status" class="form-select" required>

                          <option value="" selected disabled>
                            Select condition
                          </option>
                          <option value="Good">Good</option>
                          <option value="No Good">No Good</option>

                        </select>
                      </div>

                      <!-- Manufactured Date -->
                      <div class="col-md-6">
                        <label for="manufacturedDate" class="form-label">
                          Manufactured Date
                        </label>

                        <input
                          type="date"
                          class="form-control"
                          id="manufacturedDate"
                          name="manufactured_date">
                      </div>

                      <!-- Expiration Date -->
                      <div class="col-md-6">
                        <label for="expirationDate" class="form-label">
                          Expiration Date
                        </label>

                        <input
                          type="date"
                          class="form-control"
                          id="expirationDate"
                          name="expiration_date"
                          required
                          readonly>
                      </div>

                      <div class="col-12 text-end">
                        <small class="text-body-secondary">
                          Automatically computed as 3 years from the manufactured date.
                        </small>
                      </div>

                      <!-- Branch -->
                      <div class="col-12 col-md-4">
                        <label for="branch" class="form-label">
                          Branch
                        </label>
                        <select id="branch" name="branch" class="form-select" required onchange="generateFireExtinguisherCode()">
                          <option value="" selected disabled>
                            Select branch
                          </option>

                          <?php foreach ($branchDropdown as $branch): ?>
                            <option value="<?= htmlspecialchars($branch['value']) ?>">
                              <?= htmlspecialchars($branch['value']) ?>
                            </option>
                          <?php endforeach; ?>

                        </select>
                      </div>

                      <!-- Provider -->
                      <div class="col-12 col-md-8">
                        <label for="provider" class="form-label">
                          Provider
                        </label>

                        <input
                          type="text"
                          class="form-control"
                          id="provider"
                          name="provider"
                          placeholder="Enter provider name"
                          required>
                      </div>

                      <!-- Remarks -->
                      <div class="col-12">
                        <label for="remarks" class="form-label">
                          Remarks
                        </label>

                        <textarea
                          class="form-control"
                          id="remarks"
                          name="remarks"
                          rows="2"
                          placeholder="Additional remarks (optional)"></textarea>
                      </div>

                      <!-- Footer -->
                      <div class="modal-footer px-4 py-3">

                        <button
                          type="button"
                          class="btn btn-light border"
                          data-bs-dismiss="modal">
                          Cancel
                        </button>

                        <button
                          type="submit"
                          id="addFireExtinguisherBtn"
                          class="btn btn-primary px-4">
                          <i class="bi bi-plus-lg me-1"></i>
                          Add Fire Extinguisher
                        </button>
                      </div>
                    </form>

                  </div>
                </div>
              </div>
            </div>

            <!-- this is for view modal -->

            <!-- View Fire Extinguisher Modal -->
            <div class="modal fade" id="viewFireExtinguisherModal" tabindex="-1" aria-labelledby="viewFireExtinguisherModalLabel" aria-hidden="true">

              <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">

                  <!-- Header -->
                  <div class="modal-header border-0 bg-primary-subtle px-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                      <div class="d-flex align-items-center justify-content-center bg-primary text-white rounded-3 flex-shrink-0"
                        style="width: 46px; height: 46px;">
                        <i class="bi bi-fire fs-4"></i>
                      </div>

                      <div>
                        <h5
                          class="modal-title fw-bold mb-1"
                          id="viewFireExtinguisherModalLabel">
                          Fire Extinguisher Details
                        </h5>

                        <small class="text-body-secondary">
                          View complete fire extinguisher information.
                        </small>
                      </div>
                    </div>

                    <button
                      type="button"
                      class="btn-close"
                      data-bs-dismiss="modal"
                      aria-label="Close">
                    </button>
                  </div>


                  <!-- Body -->
                  <div class="modal-body p-3 p-md-4">

                    <!-- Equipment Summary -->
                    <div class="card border-0 bg-light-subtle rounded-4 mb-3">
                      <div class="card-body p-3">
                        <div class="row align-items-center g-3">

                          <!-- Code -->
                          <div class="col-12 col-md-7">
                            <div class="text-body-secondary small mb-1">
                              Fire Extinguisher Code
                            </div>

                            <div class="d-flex align-items-center gap-2">
                              <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3"
                                style="width: 36px; height: 36px;">
                                <i class="bi bi-qr-code"></i>
                              </div>

                              <input
                                type="text"
                                class="form-control-plaintext fw-bold fs-4 p-0"
                                id="viewExtinguisherCode"
                                readonly>
                            </div>
                          </div>


                          <!-- Condition -->
                          <div class="col-12 col-md-5">
                            <div class="text-body-secondary small mb-1">
                              Current Condition
                            </div>
                            <div class="d-flex align-items-center">
                              <input
                                type="text"
                                class="form-control-plaintext fw-bold p-0"
                                id="viewConditionStatus"
                                readonly>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>


                    <!-- Basic Information -->
                    <div class="mb-3">
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3"
                          style="width: 32px; height: 32px;">
                          <i class="bi bi-info-circle"></i>
                        </div>

                        <div>
                          <div class="fw-bold">
                            Basic Information
                          </div>

                          <small class="text-body-secondary">
                            Equipment specifications and placement
                          </small>
                        </div>

                      </div>


                      <div class="row g-2">

                        <!-- Type -->
                        <div class="col-12 col-md-6">
                          <div class="border rounded-3 p-3 h-100">
                            <label class="form-label small text-body-secondary mb-1">
                              <i class="bi bi-fire me-1 text-primary"></i>
                              Type
                            </label>

                            <input
                              type="text"
                              class="form-control-plaintext fw-semibold p-0"
                              id="viewType"
                              readonly>
                          </div>
                        </div>


                        <!-- Capacity -->
                        <div class="col-6 col-md-3">
                          <div class="border rounded-3 p-3 h-100">
                            <label class="form-label small text-body-secondary mb-1">
                              <i class="bi bi-box-seam me-1 text-primary"></i>
                              Capacity
                            </label>

                            <input
                              type="text"
                              class="form-control-plaintext fw-semibold p-0"
                              id="viewCapacity"
                              readonly>

                          </div>
                        </div>


                        <!-- Fire Class -->
                        <div class="col-6 col-md-3">
                          <div class="border rounded-3 p-3 h-100">
                            <label class="form-label small text-body-secondary mb-1">
                              <i class="bi bi-shield-check me-1 text-primary"></i>
                              Fire Class
                            </label>

                            <input
                              type="text"
                              class="form-control-plaintext fw-semibold p-0"
                              id="viewClass"
                              readonly>

                          </div>
                        </div>

                        <!-- Placement -->
                        <div class="col-12 col-md-6">
                          <div class="border rounded-3 p-3 h-100">
                            <label class="form-label small text-body-secondary mb-1">
                              <i class="bi bi-pin-map me-1 text-primary"></i>
                              Placement
                            </label>

                            <input
                              type="text"
                              class="form-control-plaintext fw-semibold p-0"
                              id="viewPlacement"
                              readonly>

                          </div>
                        </div>


                        <!-- Location -->
                        <div class="col-12 col-md-6">
                          <div class="border rounded-3 p-3 h-100">
                            <label class="form-label small text-body-secondary mb-1">
                              <i class="bi bi-geo-alt me-1 text-primary"></i>
                              Location
                            </label>

                            <input
                              type="text"
                              class="form-control-plaintext fw-semibold p-0"
                              id="viewLocation"
                              readonly>

                          </div>
                        </div>
                      </div>
                    </div>


                    <!-- Important Dates -->
                    <div class="mb-3">
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3"
                          style="width: 32px; height: 32px;">
                          <i class="bi bi-info-circle"></i>
                        </div>

                        <div>
                          <div class="fw-bold">
                            Product & Provider Details
                          </div>

                          <small class="text-body-secondary">
                            Supplier and manufacturing and expiration details
                          </small>
                        </div>
                      </div>

                      <div class="row g-2">

                        <!-- Manufactured Date -->
                        <div class="col-12 col-md-6">
                          <div class="border rounded-3 p-3">
                            <label class="form-label small text-body-secondary mb-1">
                              <i class="bi bi-calendar-event me-1 text-primary"></i>
                              Manufactured Date
                            </label>
                            <input
                              type="date"
                              class="form-control-plaintext fw-semibold p-0"
                              id="viewManufacturedDate"
                              readonly>
                          </div>
                        </div>

                        <!-- Refilled Date -->
                        <div class="col-12 col-md-6">
                          <div class="border rounded-3 p-3">
                            <label class="form-label small text-body-secondary mb-1">
                              <i class="bi bi-arrow-repeat me-1 text-primary"></i>
                              Last Refilled Date
                            </label>
                            <input
                              type="date"
                              class="form-control-plaintext fw-semibold p-0"
                              id="viewLastRefilledDate"
                              readonly>
                          </div>
                        </div>

                        <!-- Expiration Date -->
                        <div class="col-12 col-md-6">
                          <div class="border rounded-3 p-3">

                            <label class="form-label small text-body-secondary mb-1">
                              <i class="bi bi-calendar-x me-1 text-primary"></i>
                              Expiration Date
                            </label>

                            <input
                              type="date"
                              class="form-control-plaintext fw-semibold p-0"
                              id="viewExpirationDate"
                              readonly>

                          </div>
                          <small class="text-body-secondary d-block mt-1">
                            Automatically calculated as 3 years from the manufacturing or refill date.
                          </small>
                        </div>

                        <!-- Provider -->
                        <div class="col-12 col-md-6">
                          <div class="border rounded-3 p-3">

                            <label class="form-label small text-body-secondary mb-1">
                              <i class="bi bi-building me-1 text-primary"></i>
                              Provider
                            </label>

                            <input
                              type="text"
                              class="form-control-plaintext fw-semibold p-0"
                              id="viewProvider"
                              readonly>

                          </div>
                        </div>



                      </div>
                    </div>

                    <!-- Remarks -->
                    <div>

                      <div class="d-flex align-items-center gap-2 mb-2">

                        <div
                          class="d-flex align-items-center justify-content-center bg-secondary bg-opacity-10 text-secondary rounded-3"
                          style="width: 32px; height: 32px;">
                          <i class="bi bi-chat-left-text text-primary"></i>
                        </div>

                        <div>
                          <div class="fw-bold">
                            Remarks
                          </div>

                          <small class="text-body-secondary">
                            Additional information
                          </small>
                        </div>
                      </div>

                      <div class="border rounded-3 p-3 bg-light-subtle">

                        <textarea
                          class="form-control-plaintext text-body-secondary p-0"
                          id="viewRemarks"
                          rows="2"
                          readonly></textarea>
                      </div>
                    </div>
                  </div>

                  <!-- Footer -->
                  <div class="modal-footer border-0 bg-light-subtle px-3 px-md-4 py-3">

                    <button
                      type="button"
                      class="btn btn-light border px-3"
                      data-bs-dismiss="modal">
                      Close
                    </button>

                    <?php if (strtolower(trim($_SESSION['Role'] ?? '')) === 'admin'): ?>


                      <button
                        type="button"
                        id="viewEditFireExtinguisherBtn"
                        class="btn btn-warning px-4 edit-extinguisher-btn"
                        data-id=""
                        data-bs-toggle="modal"
                        data-bs-target="#editFireExtinguisherModal">

                        <i class="bi bi-pencil me-1"></i>
                        Edit

                      </button>

                      <a
                        href="#"
                        id="viewDeleteFireExtinguisherBtn"
                        class="btn btn-secondary px-3">

                        <i class="bi bi-trash me-1"></i>
                        Archive
                      </a>
                    <?php endif; ?>

                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include 'partials/footer.php'; ?>
  </div>
  <!-- CoreUI and necessary plugins-->
  <script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
  <script src="vendors/simplebar/js/simplebar.min.js"></script>
  <script src="js/list-extinguisher.js"></script>
  <?php include_once 'notification/session_timeout.php'; ?>

</body>

</html>
