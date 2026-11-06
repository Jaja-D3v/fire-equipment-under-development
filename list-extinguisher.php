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

<body class="fe-inventory-page">

  <!-- Scoped inventory presentation; shared navigation and modals keep their styles. -->
  <style>
   
  </style>

  <?php include 'partials/side-nav.php'; ?>
  <div class="wrapper d-flex flex-column min-vh-100">
    <?php
    include 'partials/header-nav.php';
    include_once 'notification/updated_fe_success.php';
    include_once 'notification/delete_fe_success.php';
    include_once 'notification/added_fe_success.php';
    ?>
    <div class="container-fluid px-3 px-lg-4 pt-3 pt-lg-4">
      <header class="fe-ios-header mb-3">
        <div class="row align-items-center g-3">
          <div class="col-12 col-xl">
            <h1 class="fw-semibold mb-1">Active Fire Extinguishers</h1>
            <p class="mb-0">Manage and monitor all active and registered fire extinguishers.</p>
          </div>
          <div class="col-12 col-xl-auto">
            <div class="row g-2">
              <div class="col-12 col-sm-auto">
                <button type="button" class="btn btn-primary px-3 w-100"
                  data-bs-toggle="modal" data-bs-target="#addFireExtinguisherModal">
                  <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>
                  Add Fire Extinguisher
                </button>
              </div>
              <?php if (strtolower(trim($_SESSION['Role'] ?? '')) === 'admin'): ?>
                <div class="col-12 col-sm-auto">
                  <button type="button" class="btn btn-outline-secondary px-3 w-100"
                    id="generateInventoryReportBtn">
                    <i class="bi bi-file-earmark-pdf me-1" aria-hidden="true"></i>
                    Generate Inventory Report
                  </button>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </header>
    </div>

    <div class="body flex-grow-1">
      <div class="container-fluid px-3 px-lg-4 py-0">
        <div class="container-fluid px-0 pb-4">
          <div class="row g-0 justify-content-center">

            <!-- Existing filter IDs and selected values are retained. -->
            <div class="fe-ios-filters p-3 mb-3">
              <div class="row g-2 align-items-end">

              <!-- Condition -->
              <div class="col-12 col-sm-6 col-xl">
                <label for="conditionFilter">Condition</label>
                <div>

                  <select
                    id="conditionFilter"
                    class="form-select"
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

              <!-- Placement -->
              <div class="col-12 col-sm-6 col-xl">
                <label for="placementTypeFilter">Placement</label>
                <div>

                  <select
                    id="placementTypeFilter"
                    class="form-select" aria-label="Filter by placement">

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

                <div class="col-12 col-sm-6 col-xl">
                  <label for="branchFilter">Branch</label>

                  <select
                    class="form-select"
                    id="branchFilter">

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

              <!-- Search -->
              <div class="col-12 col-sm-6 col-xl-4">
                <label for="fireExtinguisherSearchInput">Search equipment</label>
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

                <div class="card fe-ios-record mb-2 extinguisher-card"
                  data-placement="<?= htmlspecialchars($data['placement'] ?? '') ?>">
                  <div class="card-body p-3">
                    <div class="row gx-3 gy-2 align-items-center">
                      <div class="col-9 col-sm-10 col-md-5 col-xl-3 order-0">
                        <div class="d-flex align-items-center">
                          <div class="fe-ios-icon d-flex align-items-center justify-content-center flex-shrink-0 me-2">
                            <i class="fa-solid fa-fire-extinguisher" aria-hidden="true"></i>
                          </div>
                          <div class="fe-ios-identity">
                            <div class="fe-ios-name">Fire Extinguisher</div>
                            <div class="extinguisher-code"><?= htmlspecialchars($data['extinguisher_code']) ?></div>
                          </div>
                        </div>
                      </div>
                      <div class="col-3 col-sm-2 col-md-1 col-xl-1 order-1 order-md-2 order-xl-5 text-end">
                        <button type="button"
                          class="btn btn-outline-primary fe-ios-action view-extinguisher-btn d-inline-flex align-items-center justify-content-center"
                          data-id="<?= htmlspecialchars($data['extinguisher_id']) ?>"
                          data-bs-toggle="modal" data-bs-target="#viewFireExtinguisherModal"
                          aria-label="View details for <?= htmlspecialchars($data['extinguisher_code']) ?>" title="View details">
                          <i class="bi bi-chevron-right" aria-hidden="true"></i>
                        </button>
                      </div>
                      <div class="col-12 col-md-6 col-xl-2 order-2 order-md-1 order-xl-4">
                        <div class="d-flex flex-wrap justify-content-md-end justify-content-xl-start align-items-center gap-1">
                          <span class="badge <?= $badgeClass ?> rounded-pill">
                            <i class="bi <?= $statusIcon ?> me-1" aria-hidden="true"></i>
                            <?= htmlspecialchars($condition) ?>
                          </span>
                          <?php if ($expirationBadge !== null): ?>
                            <span class="badge <?= $expirationBadgeClass ?> rounded-pill">
                              <i class="bi <?= $expirationIcon ?> me-1" aria-hidden="true"></i>
                              <?= htmlspecialchars($expirationBadge) ?>
                            </span>
                          <?php endif; ?>
                        </div>
                      </div>
                      <div class="col-12 col-sm-4 col-xl-2 order-3 order-xl-1">
                        <div class="fe-ios-label">Location</div>
                        <div class="fe-ios-value extinguisher-location"
                          title="<?= htmlspecialchars($data['location']) ?>"><?= htmlspecialchars($data['location']) ?></div>
                      </div>
                      <div class="col-6 col-sm-4 col-xl-2 order-4 order-xl-2">
                        <div class="fe-ios-label">Branch</div>
                        <div class="fe-ios-value"
                          title="<?= htmlspecialchars($data['branch']) ?>"><?= htmlspecialchars($data['branch']) ?></div>
                      </div>
                      <div class="col-6 col-sm-4 col-xl-2 order-5 order-xl-3">
                        <div class="fe-ios-label">Last Inspected</div>
                        <div class="fe-ios-value"
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
                </div>

              <?php endforeach; ?>


              <!-- PAGINATION -->
              <div id="inventoryPagination" class="card fe-ios-pagination mt-3">
                <div class="card-footer bg-body border-0 px-2 px-sm-3 py-3">
                  <div class="d-flex flex-column align-items-center gap-2">
                    <small class="text-body-secondary text-center" style="font-size: 12px;">
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
                        <ul class="pagination pagination-sm flex-nowrap justify-content-center mb-0">

                          <!-- Previous -->
                          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <?php if ($page > 1): ?>

                              <a
                                class="page-link"
                                href="<?= htmlspecialchars($currentPageUrl, ENT_QUOTES, 'UTF-8') ?>?<?= http_build_query($previousParams) ?>"
                                aria-label="Previous">
                                <i class="bi bi-chevron-left" aria-hidden="true"></i>
                                <span class="d-none d-md-inline ms-1">Previous</span>
                              </a>

                            <?php else: ?>

                              <span class="page-link" aria-label="Previous" aria-disabled="true">
                                <i class="bi bi-chevron-left" aria-hidden="true"></i>
                                <span class="d-none d-md-inline ms-1">Previous</span>
                              </span>

                            <?php endif; ?>
                          </li>

                          <!-- Visible page numbers only; record paging and query parameters are unchanged. -->
                          <?php
                          if ($totalPages <= 5) {
                            $visiblePageNumbers = range(1, $totalPages);
                          } elseif ($page <= 3) {
                            $visiblePageNumbers = [1, 2, 3, 4, $totalPages];
                          } elseif ($page >= $totalPages - 2) {
                            $visiblePageNumbers = [1, $totalPages - 3, $totalPages - 2, $totalPages - 1, $totalPages];
                          } else {
                            $visiblePageNumbers = [1, $page - 1, $page, $page + 1, $totalPages];
                          }
                          $lastRenderedPage = 0;
                          ?>

                          <?php foreach ($visiblePageNumbers as $pageNumber): ?>
                            <?php if ($lastRenderedPage > 0 && $pageNumber > $lastRenderedPage + 1): ?>
                              <li class="page-item disabled fe-page-gap" aria-hidden="true">
                                <span class="page-link">&hellip;</span>
                              </li>
                            <?php endif; ?>

                            <?php
                            $pageParams = $paginationParams;
                            $pageParams['page'] = $pageNumber;
                            ?>
                            <li class="page-item <?= $pageNumber === (int) $page ? 'active' : '' ?>">
                              <a
                                class="page-link"
                                href="<?= htmlspecialchars($currentPageUrl, ENT_QUOTES, 'UTF-8') ?>?<?= http_build_query($pageParams) ?>"
                                <?= $pageNumber === (int) $page ? 'aria-current="page"' : '' ?>>
                                <?= $pageNumber ?>
                              </a>
                            </li>
                            <?php $lastRenderedPage = $pageNumber; ?>
                          <?php endforeach; ?>

                          <!-- Next -->
                          <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <?php if ($page < $totalPages): ?>

                              <a
                                class="page-link"
                                href="<?= htmlspecialchars($currentPageUrl, ENT_QUOTES, 'UTF-8') ?>?<?= http_build_query($nextParams) ?>"
                                aria-label="Next">
                                <span class="d-none d-md-inline me-1">Next</span>
                                <i class="bi bi-chevron-right" aria-hidden="true"></i>
                              </a>

                            <?php else: ?>

                              <span class="page-link" aria-label="Next" aria-disabled="true">
                                <span class="d-none d-md-inline me-1">Next</span>
                                <i class="bi bi-chevron-right" aria-hidden="true"></i>
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
                          required>
                      </div>

                      <div class="col-12 text-end">
                        <small class="text-body-secondary">
                          Initially suggested as 3 years after manufacturing; you can edit the expiration date.
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
