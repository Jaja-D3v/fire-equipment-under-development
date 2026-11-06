<?php
require_once 'backend/authentication/SessionChecker.php';
require_once 'backend/controller/FireExtinguisherController.php';
include './backend/controller/QRCodeGeneratorController.php';
include './backend/controller/DropdownController.php';


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
  <style>
    .fe-inventory-page .fe-ios-empty {
      border: 1px solid #e2e6ec;
      border-radius: 14px;
      background: #fff;
      box-shadow: none;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      color: #253044;
      font-size: 13px;
    }
  </style>

    <?php include 'partials/side-nav.php'; ?>
    <div class="wrapper d-flex flex-column min-vh-100">
        <?php include 'partials/header-nav.php'; ?>

        <!-- CONTENT HERE -->
        <?php

        $limit = 10;

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $search = isset($_GET['search']) && is_string($_GET['search'])
            ? trim($_GET['search'])
            : '';
        $totalRecords = getTotalDeletedFireExtinguishers();

        if ($search !== '') {
            $allArchivedInfo = getAllDeletedFireExtinguishersController(max(1, $totalRecords), 0);
            $allArchivedInfo = is_array($allArchivedInfo) ? $allArchivedInfo : [];

            $info = array_values(array_filter($allArchivedInfo, function ($data) use ($search) {
                $code = (string) ($data['extinguisher_code'] ?? '');
                $location = (string) ($data['location'] ?? '');

                return stripos($code, $search) !== false || stripos($location, $search) !== false;
            }));

            $totalRecords = count($info);
        }

        $totalPages = (int) ceil($totalRecords / $limit);

        if ($totalPages > 0 && $page > $totalPages) {
            $page = $totalPages;
        } elseif ($totalPages === 0) {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;

        if ($search === '') {
            $info = getAllDeletedFireExtinguishersController($limit, $offset);
        } else {
            $info = array_slice($info, $offset, $limit);
        }

        $paginationParams = $_GET;
        unset($paginationParams['page']);
        if ($search === '') {
            unset($paginationParams['search']);
        }
        $paginationQuery = http_build_query($paginationParams);
        ?>

<div class="container-fluid px-3 px-lg-4 pt-3 pt-lg-4">
      <header class="fe-ios-header mb-3">
        <div class="row align-items-center g-3">
          <div class="col-12 col-xl">
            <h1 class="fw-semibold mb-1">Archived Fire Extinguishers</h1>
            <p class="mb-0">View archive fire extinguishers.</p>
          </div>

        </div>
      </header>
    </div>

        <div class="body flex-grow-1">
            <div class="container-fluid px-3 px-lg-4 py-0">
                <div class="container-fluid px-0 pb-4">

                    <!-- SEARCH -->
                    <div class="fe-ios-filters p-3 mb-3">
                        <div class="row g-2 align-items-end">
                        <form method="get" action="archived-extinguisher.php" class="col-12 col-sm-8">
                            <label for="searchDeletedExtinguisher">Search fire extinguisher code</label>
                            <input type="hidden" name="page" value="1">
                            <?php if (isset($_GET['branch'])): ?>
                                <input type="hidden" name="branch" value="<?= htmlspecialchars((string) $_GET['branch'], ENT_QUOTES, 'UTF-8') ?>">
                            <?php endif; ?>
                            <div class="input-group">
                            <input
                                type="search"
                                id="searchDeletedExtinguisher"
                                name="search"
                                data-server-search="true"
                                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                                class="form-control"
                                placeholder="Search archived extinguishers code...">
                            <button type="submit" class="btn btn-primary">Search</button>
                            </div>
                        </form>

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

                            <div class="col-12 col-sm-4">
                                <label for="branchFilter">Branch</label>
                                <select class="form-select" id="branchFilter">

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


                        <script>
                            document.addEventListener('DOMContentLoaded', function() {

                                const branchFilter = document.getElementById('branchFilter');

                                if (!branchFilter) {
                                    return;
                                }

                                branchFilter.addEventListener('change', function() {

                                    const selectedBranch = this.value;
                                    const url = new URL(window.location.href);
                                    url.searchParams.set('branch', selectedBranch);

                                    // Reload page with selected branch
                                    window.location.href = url.toString();

                                });

                            });
                        </script>

                        </div>
                    </div>

                    <!-- ============================= -->
                    <!-- DELETED FIRE EXTINGUISHERS -->
                    <!-- ============================= -->

                    <div id="deletedExtinguisherContainer">

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

                            ?>

                  <div class="card fe-ios-record mb-2 extinguisher-card">
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
                            data-bs-toggle="modal" data-bs-target="#viewDeletedFireExtinguisherModal"
                            aria-label="View details for <?= htmlspecialchars($data['extinguisher_code']) ?>" title="View details">
                            <i class="bi bi-chevron-right" aria-hidden="true"></i>
                          </button>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3 order-2 order-md-1 order-xl-4">
                          <div class="d-flex flex-wrap justify-content-md-end justify-content-xl-start align-items-center gap-1">
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill"><i class="bi bi-archive me-1" aria-hidden="true"></i>Archived</span>
                            <span class="badge <?= $badgeClass ?> rounded-pill">
                              <i class="bi <?= $statusIcon ?> me-1" aria-hidden="true"></i>
                              <?= htmlspecialchars($condition) ?>
                            </span>
                          </div>
                        </div>
                        <div class="col-6 col-xl-3 order-3 order-xl-1">
                          <div class="fe-ios-label">Location</div>
                          <div class="fe-ios-value extinguisher-location"
                            title="<?= htmlspecialchars($data['location']) ?>"><?= htmlspecialchars($data['location']) ?></div>
                        </div>
                        <div class="col-6 col-xl-2 order-4 order-xl-2">
                          <div class="fe-ios-label">Branch</div>
                          <div class="fe-ios-value"
                            title="<?= htmlspecialchars($data['branch']) ?>"><?= htmlspecialchars($data['branch']) ?></div>
                        </div>
                      </div>
                    </div>
                  </div>
                        <?php endforeach; ?>

                        <!-- NO DATA -->
                        <?php if (empty($info)): ?>
                            <div class="fe-ios-empty text-center text-body-secondary py-4 px-3 my-3">
                                <div class="py-3">
                                    <i class="bi bi-trash3 fs-1 d-block mb-3"></i>
                                    <div class="fw-semibold fs-6">
                                        <?php if ($search !== ''): ?>
                                            No archived extinguishers match “<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>”.
                                        <?php else: ?>
                                            No archived fire extinguishers.
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-body-secondary">
                                        <?php if ($search !== ''): ?>
                                            Try another extinguisher code or location.
                                        <?php else: ?>
                                            Archived fire extinguishers will appear here.
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- PAGINATION -->
                    <?php if ($totalRecords >= 0): ?>

                        <div class="card fe-ios-pagination mt-3 mb-4">
                            <div class="card-footer border-0 px-2 px-sm-3 py-3">
                                <div class="d-flex flex-column align-items-center gap-2">

                                    <!-- Showing Records -->
                                    <div class="text-body-secondary text-center" style="font-size: 12px;">

                                        Showing

                                        <strong>
                                            <?= min($offset + 1, $totalRecords) ?>
                                        </strong>

                                        to

                                        <strong>
                                            <?= min($offset + $limit, $totalRecords) ?>
                                        </strong>

                                        of

                                        <strong>
                                            <?= $totalRecords ?>
                                        </strong>

                                        entries

                                    </div>

                                    <!-- Pagination -->
                                    <nav aria-label="Deleted fire extinguisher pagination">

                                        <ul class="pagination pagination-sm flex-nowrap justify-content-center mb-0">

                                            <!-- Previous -->
                                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">

                                                <a
                                                    class="page-link"
                                                    href="archived-extinguisher.php?page=<?= max(1, $page - 1) ?><?= $paginationQuery !== '' ? '&' . htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') : '' ?>"
                                                    aria-label="Previous">

                                                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                                                    <span class="d-none d-md-inline ms-1">Previous</span>

                                                </a>

                                            </li>

                          <!-- Visible page numbers only; record paging and query parameters are unchanged. -->
                          <?php
                          if ($totalPages <= 5) {
                            $visiblePageNumbers = $totalPages > 0 ? range(1, $totalPages) : [];
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

                            <li class="page-item <?= $pageNumber === (int) $page ? 'active' : '' ?>">
                              <a
                                class="page-link"
                                href="archived-extinguisher.php?page=<?= $pageNumber ?><?= $paginationQuery !== '' ? '&' . htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') : '' ?>"
                                <?= $pageNumber === (int) $page ? 'aria-current="page"' : '' ?>>
                                <?= $pageNumber ?>
                              </a>
                            </li>
                            <?php $lastRenderedPage = $pageNumber; ?>
                          <?php endforeach; ?>

                                            <!-- Next -->
                                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">

                                                <a
                                                    class="page-link"
                                                    href="archived-extinguisher.php?page=<?= min($totalPages, $page + 1) ?><?= $paginationQuery !== '' ? '&' . htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') : '' ?>"
                                                    aria-label="Next">

                                                    <span class="d-none d-md-inline me-1">Next</span>
                                                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- modal for view  -->

        <div
            class="modal fade"
            id="viewDeletedFireExtinguisherModal"
            tabindex="-1"
            aria-labelledby="viewDeletedFireExtinguisherModalLabel"
            aria-hidden="true">

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
                                    id="viewDeletedFireExtinguisherModalLabel">

                                    Fire Extinguisher Details

                                </h5>

                                <small class="text-body-secondary">
                                    View complete fire extinguisher information.
                                </small>
                            </div>

                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>

                    </div>


                    <!-- Body -->
                    <div class="modal-body p-3 p-md-4">

                        <!-- Equipment Summary -->
                        <div class="card border-0 bg-body-tertiary rounded-4 mb-4">

                            <div class="card-body p-3 p-md-4">

                                <div class="d-flex align-items-center gap-2 mb-3">

                                    <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-2"
                                        style="width: 34px; height: 34px;">

                                        <i class="bi bi-info-circle"></i>
                                    </div>

                                    <div>
                                        <h6 class="fw-bold mb-0">
                                            Equipment Summary
                                        </h6>

                                        <small class="text-body-secondary">
                                            Basic equipment information
                                        </small>
                                    </div>
                                </div>


                                <div class="row g-3">
                                    <!-- Code -->
                                    <div class="col-md-6">

                                        <label class="form-label small fw-semibold text-body-secondary mb-1">
                                            Fire Extinguisher Code
                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="bi bi-upc-scan text-primary"></i>
                                            </span>

                                            <input
                                                type="text"
                                                id="deletedViewExtinguisherCode"
                                                class="form-control border-start-0 ps-0"
                                                readonly>
                                        </div>
                                    </div>

                                    <!-- Branch -->
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-body-secondary mb-1">
                                            Branch
                                        </label>

                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="bi bi-building text-primary"></i>
                                            </span>

                                            <input
                                                type="text"
                                                id="deletedViewBranch"
                                                class="form-control border-start-0 ps-0"
                                                readonly>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Equipment Details -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div
                                    class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-2"
                                    style="width: 34px; height: 34px;">
                                    <i class="bi bi-fire"></i>
                                </div>

                                <div>
                                    <h6 class="fw-bold mb-0">
                                        Equipment Details
                                    </h6>
                                    <small class="text-body-secondary">
                                        Fire extinguisher specifications
                                    </small>
                                </div>
                            </div>

                            <div class="row g-3">

                                <!-- Type -->
                                <div class="col-md-6">

                                    <label class="form-label small fw-semibold text-body-secondary">
                                        Type
                                    </label>

                                    <input
                                        type="text"
                                        id="deletedViewType"
                                        class="form-control"
                                        readonly>
                                </div>

                                <!-- Capacity -->
                                <div class="col-md-6">

                                    <label class="form-label small fw-semibold text-body-secondary">
                                        Capacity
                                    </label>

                                    <input
                                        type="text"
                                        id="deletedViewCapacity"
                                        class="form-control"
                                        readonly>
                                </div>

                                <!-- Fire Class -->
                                <div class="col-md-6">

                                    <label class="form-label small fw-semibold text-body-secondary">
                                        Fire Class
                                    </label>

                                    <input
                                        type="text"
                                        id="deletedViewClass"
                                        class="form-control"
                                        readonly>
                                </div>

                                <!-- Placement -->
                                <div class="col-md-6">

                                    <label class="form-label small fw-semibold text-body-secondary">
                                        Placement
                                    </label>
                                    <input type="text" id="deletedViewPlacement" class="form-control" readonly>
                                </div>

                                <!-- Location -->
                                <div class="col-md-6">

                                    <label class="form-label small fw-semibold text-body-secondary">
                                        Location
                                    </label>
                                    <input type="text" id="deletedViewLocation" class="form-control" readonly>
                                </div>

                                <!-- Condition -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-body-secondary">
                                        Condition
                                    </label>
                                    <input type="text" id="deletedViewConditionStatus" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Dates & Maintenance -->
                        <div class="border-top pt-4 mt-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-2" style="width: 34px; height: 34px;">

                                    <i class="bi bi-calendar3"></i>

                                </div>

                                <div>
                                    <h6 class="fw-bold mb-0">
                                        Dates & Maintenance
                                    </h6>

                                    <small class="text-body-secondary">
                                        Equipment dates and maintenance information
                                    </small>
                                </div>
                            </div>

                            <div class="row g-3">

                                <!-- Manufactured Date -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-body-secondary">
                                        Manufactured Date
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text bg-body-tertiary border-end-0">
                                            <i class="bi bi-calendar-event"></i>
                                        </span>

                                        <input type="text" id="deletedViewManufacturedDate" class="form-control border-start-0 ps-0" readonly>

                                    </div>
                                </div>

                                <!-- Last Refilled -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-body-secondary">
                                        Last Refilled Date
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text bg-body-tertiary border-end-0">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </span>

                                        <input type="text" id="deletedViewLastRefilledDate" class="form-control border-start-0 ps-0" readonly>

                                    </div>
                                </div>

                                <!-- Expiration -->
                                <div class="col-md-4">

                                    <label class="form-label small fw-semibold text-body-secondary">
                                        Expiration Date
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text bg-body-tertiary border-end-0">
                                            <i class="bi bi-calendar-x"></i>
                                        </span>

                                        <input type="text" id="deletedViewExpirationDate" class="form-control border-start-0 ps-0" readonly>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="border-top pt-4 mt-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-2" style="width: 34px; height: 34px;">
                                    <i class="bi bi-chat-left-text"></i>
                                </div>

                                <div>
                                    <h6 class="fw-bold mb-0">
                                        Remarks
                                    </h6>

                                    <small class="text-body-secondary">
                                        Additional information
                                    </small>
                                </div>

                            </div>

                            <textarea id="deletedViewRemarks" class="form-control" rows="3" readonly></textarea>

                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer border-0 bg-body-tertiary px-3 px-md-4 py-3">

                        <button
                            type="button"
                            class="btn btn-light border px-4"
                            data-bs-dismiss="modal">

                            <i class="bi bi-x-lg me-1"></i>
                            Close
                        </button>

                        <?php if (strtolower(trim($_SESSION['Role'] ?? '')) === 'admin'): ?>

                            <button
                                type="button"
                                id="restoreDeletedFireExtinguisherBtn"
                                class="btn btn-success px-4"
                                data-id="">

                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Restore
                            </button>

                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
        <script>
            document.querySelectorAll('.view-extinguisher-btn').forEach(button => {

                button.addEventListener('click', function() {

                    const id = this.dataset.id;
                    const card = this.closest('.extinguisher-card');
                    const codeElement = card ? card.querySelector('.extinguisher-code') : null;
                    const extinguisherCode = codeElement ? codeElement.textContent.trim() : '';

                    const url =
                        `backend/controller/FireExtinguisherController.php` +
                        `?action=getDeleted` +
                        `&id=${encodeURIComponent(id)}` +
                        `&code=${encodeURIComponent(extinguisherCode)}`;

                    const browserUrl = new URL(window.location.href);
                    browserUrl.searchParams.set('code', extinguisherCode);

                    window.history.replaceState({}, '', browserUrl.toString());

                    fetch(url)

                        .then(response => {

                            if (!response.ok) {

                                throw new Error('Failed to fetch fire extinguisher.');
                            }
                            return response.json();
                        })

                        .then(result => {

                            if (!result.success) {

                                alert(result.message || 'Failed to load fire extinguisher.');
                                return;
                            }

                            const data = result.data;

                            document.getElementById('deletedViewExtinguisherCode').value = data.extinguisher_code ?? '';
                            document.getElementById('deletedViewBranch').value = data.branch ?? '';
                            document.getElementById('deletedViewType').value = data.type ?? '';
                            document.getElementById('deletedViewCapacity').value = data.capacity ?? '';
                            document.getElementById('deletedViewClass').value = data.class ?? '';
                            document.getElementById('deletedViewPlacement').value = data.placement ?? '';
                            document.getElementById('deletedViewLocation').value = data.location ?? '';
                            document.getElementById('deletedViewConditionStatus').value = data.condition_status ?? '';
                            document.getElementById('deletedViewManufacturedDate').value = data.manufactured_date ?? '';
                            document.getElementById('deletedViewLastRefilledDate').value = data.refilled_date ?? '';
                            document.getElementById('deletedViewExpirationDate').value = data.expiration_date ?? '';
                            document.getElementById('deletedViewRemarks').value = data.remarks ?? '';

                            // Set ID for Restore button
                            const restoreButton = document.getElementById('restoreDeletedFireExtinguisherBtn');

                            if (restoreButton) {
                                restoreButton.dataset.id = data.extinguisher_id;
                                restoreButton.dataset.code = data.extinguisher_code;
                            }
                        })

                        .catch(error => {

                            console.error(error);

                        });
                });

            });

            document
                .getElementById('restoreDeletedFireExtinguisherBtn')
                .addEventListener('click', function() {

                    const id = this.dataset.id;

                    if (!id) {
                        alert('Invalid fire extinguisher ID.');
                        return;
                    }

                    const confirmed = confirm(
                        'Are you sure you want to restore this fire extinguisher?\n\n' +
                        'The fire extinguisher will be returned to the active list.'
                    );

                    if (!confirmed) {
                        return;
                    }

                    const code = this.dataset.code || '';

                    window.location.href =
                        `backend/controller/FireExtinguisherController.php` +
                        `?action=restore` +
                        `&id=${encodeURIComponent(id)}` +
                        `&code=${encodeURIComponent(code)}`;

                });
        </script>
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
    <?php include_once 'notification/restored_fe_success.php'; ?>

</body>

</html>
