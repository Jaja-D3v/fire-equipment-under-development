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

<body>
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

        <div class="container-fluid py-0">

            <!-- ============================= -->
            <!-- PAGE HEADER -->
            <!-- ============================= -->

            <div class="card border-0 shadow-sm text-white mb-1 mt-0 overflow-hidden" style="background: linear-gradient(135deg, #203D70, #6c757d);">
                <div class="card-body p-4">
                    <div class="row align-items-center g-3">

                        <!-- Icon -->
                        <div class="col-auto">
                            <div class="bg-white bg-opacity-10 rounded-3 p-3 fs-3">
                                <i class="bi bi-trash3"></i>
                            </div>

                        </div>

                        <!-- Title & Description -->
                        <div class="col">
                            <h2 class="fw-bold mb-1">
                                Deleted Fire Extinguishers
                            </h2>

                            <p class="mb-0 text-white-50">
                                View deleted fire extinguishers.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="body flex-grow-1">
            <div class="container-fluid py-0">
                <div class="container py-4">

                    <!-- SEARCH -->
                    <div class="d-flex flex-column flex-md-row gap-2 mb-3">
                        <form method="get" action="archived-extinguisher.php" class="input-group flex-grow-1">
                            <input type="hidden" name="page" value="1">
                            <?php if (isset($_GET['branch'])): ?>
                                <input type="hidden" name="branch" value="<?= htmlspecialchars((string) $_GET['branch'], ENT_QUOTES, 'UTF-8') ?>">
                            <?php endif; ?>
                            <span class="input-group-text bg-body border-end-0">
                                <i class="bi bi-search text-primary"></i>
                            </span>
                            <input
                                type="search"
                                id="searchDeletedExtinguisher"
                                name="search"
                                data-server-search="true"
                                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                                class="form-control border-start-0 ps-1"
                                placeholder="Search archived extinguishers...">
                            <button type="submit" class="btn btn-primary">Search</button>
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

                            <div class="col-12 col-md-auto">
                                <select class="form-select" id="branchFilter" style="min-width: 190px;">

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

                            <div
                                class="card border border-secondary-subtle shadow-sm mb-2 extinguisher-card overflow-hidden">

                                <div class="card-body p-2 p-md-3">

                                    <!-- TOP SECTION -->
                                    <div class="d-flex align-items-center gap-3">

                                        <!-- Icon -->
                                        <div class="d-flex align-items-center justify-content-center bg-secondary bg-opacity-10 text-secondary rounded-3 flex-shrink-0" style="width: 50px; height: 50px;">

                                            <i class="bi bi-trash3 fs-4"></i>

                                        </div>

                                        <!-- Title -->
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="mb-1">
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1" style="font-size: 0.65rem;">
                                                    <i class="bi bi-archive me-1"></i>
                                                    Deleted
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

                                            <span class="badge <?= $badgeClass ?> rounded-pill px-3 py-2 text-nowrap">
                                                <i class="bi <?= $statusIcon ?> me-1"></i>
                                                <?= htmlspecialchars($condition) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- DETAILS -->
                                    <div class="row g-2 g-md-3 mt-2 align-items-center">

                                        <!-- FE Code -->
                                        <div class="col-6 col-md-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="d-flex align-items-center justify-content-center bg-secondary bg-opacity-10 text-secondary rounded-3 flex-shrink-0" style="width: 32px; height: 32px;">
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
                                        <div class="col-6 col-md-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3 flex-shrink-0" style="width: 32px; height: 32px;">
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
                                        <div class="col-6 col-md-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-3 flex-shrink-0" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-fire"></i>
                                                </div>

                                                <div class="min-width-0">
                                                    <div class="text-body-secondary small lh-1">
                                                        Type
                                                    </div>
                                                    <div class="fw-semibold small text-truncate" title="<?= htmlspecialchars($data['type']) ?>">
                                                        <?= htmlspecialchars($data['type']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Location -->
                                        <div class="col-6 col-md-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3 flex-shrink-0" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-geo-alt"></i>
                                                </div>

                                                <div class="min-width-0">
                                                    <div class="text-body-secondary small lh-1">
                                                        Location
                                                    </div>

                                                    <div class="fw-semibold small text-truncate extinguisher-location" title="<?= htmlspecialchars($data['location']) ?>">
                                                        <?= htmlspecialchars($data['location']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Branch -->
                                        <div class="col-6 col-md-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-3 flex-shrink-0" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-building"></i>
                                                </div>

                                                <div class="min-width-0">
                                                    <div class="text-body-secondary small lh-1">
                                                        Branch
                                                    </div>

                                                    <div class="fw-semibold small text-truncate" title="<?= htmlspecialchars($data['branch']) ?>">
                                                        <?= htmlspecialchars($data['branch']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <!-- View -->
                                        <div class="col-12 col-md-2">

                                            <button type="button" class="btn btn-sm btn-secondary w-100 px-3 view-extinguisher-btn" data-id="<?= htmlspecialchars($data['extinguisher_id']) ?>" data-bs-toggle="modal" data-bs-target="#viewDeletedFireExtinguisherModal">
                                                <i class="bi bi-eye me-1"></i>
                                                View
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>

                        <!-- NO DATA -->
                        <?php if (empty($info)): ?>
                            <div class="border rounded-3 text-center text-body-secondary py-5 px-3 my-3 shadow-sm">
                                <div class="py-3">
                                    <i class="bi bi-trash3 fs-1 d-block mb-3"></i>
                                    <div class="fw-semibold fs-6">
                                        <?php if ($search !== ''): ?>
                                            No archived extinguishers match “<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>”.
                                        <?php else: ?>
                                            No deleted fire extinguishers.
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-body-secondary">
                                        <?php if ($search !== ''): ?>
                                            Try another extinguisher code or location.
                                        <?php else: ?>
                                            Deleted fire extinguishers will appear here.
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- PAGINATION -->
                    <?php if ($totalRecords >= 0): ?>

                        <div class="card border shadow-sm mt-3 mb-4">
                            <div class="card-body py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">

                                    <!-- Showing Records -->
                                    <div class="text-body-secondary small text-center text-md-start">

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

                                        <ul class="pagination pagination-sm mb-0">

                                            <!-- Previous -->
                                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">

                                                <a
                                                    class="page-link"
                                                    href="archived-extinguisher.php?page=<?= max(1, $page - 1) ?><?= $paginationQuery !== '' ? '&' . htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') : '' ?>"
                                                    aria-label="Previous">

                                                    <span aria-hidden="true">&lsaquo;</span>
                                                    Previous

                                                </a>

                                            </li>

                                            <!-- Page Numbers -->
                                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">

                                                    <a
                                                        class="page-link"
                                                        href="archived-extinguisher.php?page=<?= $i ?><?= $paginationQuery !== '' ? '&' . htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') : '' ?>">

                                                        <?= $i ?>

                                                    </a>

                                                </li>

                                            <?php endfor; ?>

                                            <!-- Next -->
                                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">

                                                <a
                                                    class="page-link"
                                                    href="archived-extinguisher.php?page=<?= min($totalPages, $page + 1) ?><?= $paginationQuery !== '' ? '&' . htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') : '' ?>"
                                                    aria-label="Next">

                                                    Next
                                                    <span aria-hidden="true">&rsaquo;</span>
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
