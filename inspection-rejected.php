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

$date = $_GET['date'] ?? null;
$search = isset($_GET['search']) && is_string($_GET['search'])
    ? trim($_GET['search'])
    : '';

if ($date === '') {
    $date = null;
}

$totalRecords = getRejectedApprovalTotal($date, $search);

$totalPages = (int) ceil($totalRecords / $limit);

if ($totalPages > 0 && $page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $limit;

$result = getRejectedApprovals(
    $limit,
    $offset,
    $date,
    $search
);

$paginationParams = $_GET;
unset($paginationParams['page']);
$paginationQuery = http_build_query($paginationParams);

$inspectionChecklists = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $inspectionChecklists[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php include 'partials/header.php'; ?>


</head>

<body>

    <?php include 'partials/side-nav.php'; ?>

    <div class="wrapper d-flex flex-column min-vh-100">

        <?php include 'partials/header-nav.php'; ?>

        <!-- CONTENT -->
        <div class="container-fluid py-0 inspection-content">

            <div class="inspection-page">

                <!-- Page Header -->
                <div class="card border-0 shadow-sm text-white mb-3 mt-0 overflow-hidden inspection-header" style="background: linear-gradient(135deg, #1e3dc8, #57f9ff);">

                    <div class="card-body p-4">
                        <div class="row align-items-center g-3">

                            <div class="col-auto">

                                <div
                                    class="bg-white bg-opacity-25 rounded-3 p-3 fs-2 inspection-header-icon">

                                    <i class="bi bi-clipboard2-check"></i>

                                </div>

                            </div>

                            <div class="col">

                                <h2 class="fw-bold mb-1">
                                    Rejected Inspection Records
                                </h2>

                                <p class="mb-0 text-white-50">
                                    Inspection reports that have been rejected.
                                </p>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card border-0 shadow-sm mb-3 inspection-filter-card">
                    <div class="card-body">
                        <div class="row g-3 align-items-center">

                            <!-- Search -->
                            <div class="col-12 col-lg-6">

                                <form method="get" action="inspection-rejected.php" class="input-group">
                                    <input type="hidden" name="page" value="1">
                                    <?php if (!empty($date)): ?><input type="hidden" name="date" value="<?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                                    <?php if (isset($_GET['branch'])): ?><input type="hidden" name="branch" value="<?= htmlspecialchars($_GET['branch'], ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                                    <span class="input-group-text bg-white">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="inspectionSearch" name="search" data-server-search="true" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search by FE code or location...">
                                    <button type="submit" class="btn btn-primary" aria-label="Search inspections">Search</button>
                                </form>

                            </div>

                            <!-- Date -->
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-white">

                                        <i class="bi bi-calendar3"></i>

                                    </span>

                                    <input
                                        type="date"
                                        class="form-control"
                                        id="inspectionDateFilter"
                                        value="<?= htmlspecialchars($date ?? '') ?>"
                                        onchange="
                                            const url = new window.URL(window.location.href);
                                            const searchInput = document.getElementById('inspectionSearch');
                                            const search = searchInput ? searchInput.value.trim() : '';
                                            url.searchParams.set('page', '1');
                                            if (this.value) url.searchParams.set('date', this.value);
                                            else url.searchParams.delete('date');
                                            if (search) url.searchParams.set('search', search);
                                            else url.searchParams.delete('search');
                                            window.location.href = url.toString();
                                        ">

                                </div>

                            </div>

                            <!-- Branch Filter - ADMIN ONLY -->
                            <?php if (strtolower(trim($_SESSION['Role'] ?? '')) === 'admin'): ?>

                                <?php
                                // Retrieve the branch assigned to the logged-in administrator.
                                $adminBranch = trim($_SESSION['Branch'] ?? '');

                                // Use the branch specified in the URL; otherwise, use the administrator's assigned branch.
                                $selectedBranch = $_GET['branch'] ?? $adminBranch;

                                // Default to all branches if no branch is specified in the session or URL.
                                if (empty($selectedBranch)) {
                                    $selectedBranch = 'all';
                                }

                                // Retrieve the available branches for the dropdown filter.
                                $branches = getAllDropdownBranches();
                                ?>

                                <div class="col-12 col-md-6 col-lg-3">

                                    <select
                                        class="form-select w-100"
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
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {

                                    const branchFilter =
                                        document.getElementById('branchFilter');

                                    if (!branchFilter) {
                                        return;
                                    }

                                    branchFilter.addEventListener('change', function() {

                                        const selectedBranch = this.value;

                                        const url = new URL(window.location.href);

                                        // Always set the branch parameter.
                                        // This allows "all" to remain selected after reload.
                                        url.searchParams.set('branch', selectedBranch);
                                        url.searchParams.set('page', '1');

                                        // Reload page with selected branch
                                        window.location.href = url.toString();

                                    });

                                });
                            </script>

                        </div>

                    </div>

                </div>

                <!-- Inspection Table -->
                <div class="card border-0 shadow-sm inspection-table-card">

                    <!-- Table -->
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

                                                $timestamp = strtotime(
                                                    $inspection['date_inspected']
                                                );

                                                if ($timestamp !== false) {

                                                    $inspectionDate = date(
                                                        'Y-m-d',
                                                        $timestamp
                                                    );
                                                }
                                            }

                                            ?>

                                            <tr
                                                class="inspection-row"

                                                data-fe-code="<?= htmlspecialchars(
                                                                    strtolower(
                                                                        $inspection['extinguisher_code'] ?? ''
                                                                    )
                                                                ) ?>"

                                                data-location="<?= htmlspecialchars(
                                                                    strtolower(
                                                                        $inspection['location'] ?? ''
                                                                    )
                                                                ) ?>"

                                                data-status="<?= htmlspecialchars(
                                                                    $statusFilterValue
                                                                ) ?>"

                                                data-date="<?= htmlspecialchars(
                                                                $inspectionDate
                                                            ) ?>">

                                                <!-- FE Code -->
                                                <td class="ps-4">

                                                    <span class="text-primary fw-semibold">

                                                        <?= htmlspecialchars(
                                                            $inspection['extinguisher_code'] ?? '—'
                                                        ) ?>

                                                    </span>

                                                </td>

                                                <!-- Location -->
                                                <td>

                                                    <?= htmlspecialchars(
                                                        $inspection['location'] ?? '—'
                                                    ) ?>

                                                </td>

                                                <!-- Date Inspected -->
                                                <td class="text-nowrap">

                                                    <?php if (!empty($inspection['date_inspected'])): ?>

                                                        <?php

                                                        $timestamp = strtotime(
                                                            $inspection['date_inspected']
                                                        );

                                                        ?>

                                                        <?php if ($timestamp !== false): ?>

                                                            <?= date(
                                                                'M d, Y',
                                                                $timestamp
                                                            ) ?>

                                                            <small class="text-body-secondary d-block">

                                                                <?= date(
                                                                    'h:i A',
                                                                    $timestamp
                                                                ) ?>

                                                            </small>

                                                        <?php else: ?>

                                                            —

                                                        <?php endif; ?>

                                                    <?php else: ?>

                                                        —

                                                    <?php endif; ?>

                                                </td>

                                                <!-- Branch -->
                                                <td>

                                                    <?= htmlspecialchars(
                                                        $inspection['branch'] ?? '—'
                                                    ) ?>

                                                </td>

                                                <!-- Status -->
                                                <td>

                                                    <span
                                                        class="badge rounded-pill <?= $statusClass ?> px-3 py-2 status-badge">

                                                        <i
                                                            class="bi <?= $statusIcon ?> me-1">
                                                        </i>

                                                        <?= htmlspecialchars(
                                                            $statusLabel
                                                        ) ?>

                                                    </span>

                                                </td>

                                                <!-- Actions -->
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

                                                            <!-- Approve -->
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-success"
                                                                onclick="approveInspection(<?= (int) $inspection['inspect_id'] ?>)">

                                                                <i class="bi bi-check-lg me-1"></i>

                                                                Approve

                                                            </button>

                                                            <!-- Reject -->
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

                                        <!-- No Search Result -->
                                        <tr
                                            id="noSearchResult"
                                            style="display: none;">

                                            <td
                                                colspan="6"
                                                class="text-center py-5">

                                                <div class="text-body-secondary inspection-empty-state">

                                                    <div>

                                                        <i
                                                            class="bi bi-search fs-1 d-block mb-2">
                                                        </i>

                                                        No inspection records found.

                                                    </div>

                                                </div>

                                            </td>

                                        </tr>

                                    <?php else: ?>

                                        <!-- No Database Records -->

                                        <tr>

                                            <td
                                                colspan="6"
                                                class="text-center py-5">

                                                <div class="text-body-secondary inspection-empty-state">

                                                    <div>

                                                        <i
                                                            class="bi bi-inbox fs-1 d-block mb-2">
                                                        </i>

                                                        No inspection records found.

                                                    </div>

                                                </div>

                                            </td>

                                        </tr>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    <!-- PAGINATION FOOTER -->
                    <div
                        class="bg-body border-top shadow-sm py-2 inspection-pagination">

                        <div class="container-fluid px-3 px-md-4">

                            <div
                                class="inspection-pagination-inner d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">

                                <!-- Showing -->
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

                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>

                                    <nav aria-label="Inspection pagination">

                                        <ul class="pagination pagination-sm mb-0">

                                            <!-- Previous -->
                                            <li
                                                class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">

                                                <a
                                                    class="page-link"
                                                    href="inspection-rejected.php?page=<?= max(1, $page - 1) ?><?= $paginationQuery !== '' ? '&' . htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') : '' ?>"
                                                    aria-label="Previous">

                                                    <i class="bi bi-chevron-left"></i>

                                                    <span class="d-none d-lg-inline ms-1">
                                                        Previous
                                                    </span>

                                                </a>

                                            </li>

                                            <!-- Page Numbers -->
                                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                                                <li
                                                    class="page-item d-none d-md-block <?= ($i == $page) ? 'active' : '' ?>">

                                                    <a
                                                        class="page-link"
                                                        href="inspection-rejected.php?page=<?= $i ?><?= $paginationQuery !== '' ? '&' . htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') : '' ?>">

                                                        <?= $i ?>

                                                    </a>

                                                </li>

                                            <?php endfor; ?>

                                            <!-- Mobile Current Page -->
                                            <li class="page-item d-md-none active">

                                                <span class="page-link">

                                                    <?= $page ?> / <?= $totalPages ?>

                                                </span>

                                            </li>

                                            <!-- Next -->
                                            <li
                                                class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">

                                                <a
                                                    class="page-link"
                                                    href="inspection-rejected.php?page=<?= min($totalPages, $page + 1) ?><?= $paginationQuery !== '' ? '&' . htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') : '' ?>"
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

        </div>

    </div>

    <?php include './partials/view-inspection-modal.php'; ?>

    <!-- CoreUI and necessary plugins -->
    <script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>

    <script src="./js/inspection-approvals.js"></script>

    <script src="vendors/simplebar/js/simplebar.min.js"></script>

    <?php include_once 'notification/session_timeout.php'; ?>

</body>

</html>
