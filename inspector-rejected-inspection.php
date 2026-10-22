<?php
require_once 'backend/authentication/only-inspector.php';
require_once 'backend/controller/RejectedInspectionController.php';


$perPage = 6;

$currentPage = isset($_GET['page'])
    ? max(1, (int) $_GET['page'])
    : 1;

$totalRejected = count($rejectedInspections);

$totalPages = max(
    1,
    (int) ceil($totalRejected / $perPage)
);

if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $perPage;

$paginatedInspections = array_slice(
    $rejectedInspections,
    $offset,
    $perPage
);

?>

<!DOCTYPE html>
<html lang="en">

<?php include 'partials/header.php'; ?>

<body>

    <?php include 'partials/side-nav.php'; ?>

    <div class="wrapper d-flex flex-column min-vh-100">

        <?php include 'partials/header-nav.php'; ?>

        <main class="body flex-grow-1 px-3 py-4">

            <div class="container-fluid">



                <!-- MAIN CARD -->
                <div class="card border-0 shadow-sm">

                    <!-- HEADER -->
                    <div class="card-header bg-body border-bottom py-3">

                        <div
                            class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">

                            <div>

                                <h6
                                    class="fw-semibold mb-1"
                                    style="font-size: 13px;">
                                    Rejected Inspections
                                </h6>

                                <small
                                    class="text-body-secondary"
                                    style="font-size: 11px;">
                                    Inspections that require correction or re-inspection.
                                </small>

                            </div>

                            <span
                                class="badge bg-danger-subtle text-danger"
                                style="font-size: 11px;">
                                <?= $totalRejected ?> Rejected
                            </span>

                        </div>

                    </div>

                    <!-- FILTER -->
                    <form method="GET" class="card border-0 shadow-sm mb-2">

                        <div class="card-body p-3">

                            <div class="row g-2">

                                <div class="col-12 col-lg">

                                    <input
                                        type="text"
                                        name="search"
                                        class="form-control"
                                        placeholder="Search code, location, or reason..."
                                        value="<?= htmlspecialchars(
                                                    $search,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                        style="font-size: 12px;">

                                </div>

                                <div class="col-12 col-sm-6 col-lg-auto">

                                    <select
                                        name="date"
                                        class="form-select"
                                        style="font-size: 12px;">

                                        <?php foreach (
                                            [
                                                'all' => 'All Inspection Dates',
                                                'today' => 'Today',
                                                'yesterday' => 'Yesterday',
                                                '7' => 'Last 7 Days',
                                                '30' => 'Last 30 Days'
                                            ] as $value => $label
                                        ): ?>

                                            <option
                                                value="<?= $value ?>"
                                                <?= $dateFilter === $value
                                                    ? 'selected'
                                                    : '' ?>>
                                                <?= $label ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                                <div class="col-6 col-sm-auto">

                                    <button
                                        type="submit"
                                        class="btn btn-primary w-100"
                                        style="font-size: 12px;">
                                        Filter
                                    </button>

                                </div>

                                <div class="col-6 col-sm-auto">

                                    <a
                                        href="inspector-rejected-inspection.php"
                                        class="btn btn-light border w-100"
                                        style="font-size: 12px;">
                                        Clear
                                    </a>

                                </div>

                            </div>

                        </div>

                    </form>

                    <!-- DESKTOP TABLE -->
                    <div
                        class="table-responsive d-none d-md-block"
                        style="min-height: 390px;">

                        <table
                            class="table table-hover align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th
                                        class="px-3"
                                        style="font-size: 12px;">
                                        Date Inspected
                                    </th>

                                    <th style="font-size: 12px;">
                                        Extinguisher Code
                                    </th>

                                    <th style="font-size: 12px;">
                                        Location
                                    </th>

                                    <th style="font-size: 12px;">
                                        Rejection Reason
                                    </th>

                                    <th style="font-size: 12px;">
                                        Rejection Date
                                    </th>

                                    <th
                                        class="text-end px-3"
                                        style="font-size: 12px;">
                                        Action
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach (
                                    $paginatedInspections
                                    as $inspection
                                ): ?>

                                    <?php

                                    $inspectionDate = strtotime(
                                        $inspection['date_inspected']
                                    );

                                    $reason = !empty($inspection['reject_reason'])
                                        ? $inspection['reject_reason']
                                        : 'No reason provided.';

                                    $rejectionDate = !empty($inspection['rejection_date'])
                                        ? strtotime(
                                            $inspection['rejection_date']
                                        )
                                        : false;

                                    ?>

                                    <tr>

                                        <td class="px-3">

                                            <div
                                                class="fw-semibold"
                                                style="font-size: 12px;">

                                                <?= date(
                                                    'M d, Y',
                                                    $inspectionDate
                                                ) ?>

                                            </div>

                                            <small class="text-body-secondary">

                                                <?= date(
                                                    'h:i A',
                                                    $inspectionDate
                                                ) ?>

                                            </small>

                                        </td>

                                        <td
                                            class="fw-semibold"
                                            style="font-size: 12px;">

                                            <?= htmlspecialchars(
                                                $inspection['extinguisher_code'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </td>

                                        <td style="font-size: 12px;">

                                            <?= htmlspecialchars(
                                                $inspection['location'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </td>

                                        <td style="font-size: 12px;">

                                            <span
                                                class="badge bg-danger-subtle text-danger fw-normal text-wrap text-start">

                                                <?= htmlspecialchars(
                                                    $reason,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </span>

                                        </td>

                                        <td style="font-size: 12px;">

                                            <?php if ($rejectionDate): ?>

                                                <?= date(
                                                    'M d, Y',
                                                    $rejectionDate
                                                ) ?>

                                            <?php else: ?>

                                                <span
                                                    class="text-body-secondary">
                                                    N/A
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <td class="text-end px-3">

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                style="font-size: 12px;"
                                                onclick="document.getElementById('rejectionDetails<?= (int) $inspection['inspect_id'] ?>').showModal();">

                                                View

                                            </button>



                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                    <!-- MOBILE LIST -->
                    <div
                        class="d-md-none"
                        id="mobileRejectedInspectionList">

                        <?php foreach (
                            $paginatedInspections
                            as $inspection
                        ): ?>

                            <?php

                            $inspectionDate = strtotime(
                                $inspection['date_inspected']
                            );

                            $reason = !empty($inspection['reject_reason'])
                                ? $inspection['reject_reason']
                                : 'No reason provided.';

                            ?>

                            <div class="border-bottom p-3">

                                <div
                                    class="d-flex justify-content-between align-items-start gap-2 mb-3">

                                    <div>

                                        <div
                                            class="fw-semibold"
                                            style="font-size: 13px;">

                                            <?= htmlspecialchars(
                                                $inspection['extinguisher_code'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </div>

                                        <small class="text-body-secondary">

                                            <?= date(
                                                'M d, Y h:i A',
                                                $inspectionDate
                                            ) ?>

                                        </small>

                                    </div>

                                    <span class="badge bg-danger">
                                        Rejected
                                    </span>

                                </div>

                                <div class="mb-3">

                                    <small
                                        class="text-body-secondary d-block mb-1">
                                        LOCATION
                                    </small>

                                    <div style="font-size: 12px;">

                                        <?= htmlspecialchars(
                                            $inspection['location'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </div>

                                </div>

                                <div
                                    class="d-flex align-items-center justify-content-between gap-2">

                                    <span
                                        class="badge bg-danger-subtle text-danger fw-normal text-wrap text-start">

                                        <?= htmlspecialchars(
                                            $reason,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </span>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        style="font-size: 12px;"
                                        onclick="document.getElementById('rejectionDetails<?= (int) $inspection['inspect_id'] ?>').showModal();">

                                        View

                                    </button>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                    <!-- EMPTY STATE -->
                    <?php if (empty($rejectedInspections)): ?>

                        <div class="text-center p-5">

                            <div
                                class="text-body-secondary mb-2"
                                style="font-size: 30px;">
                                ✓
                            </div>

                            <div
                                class="fw-semibold mb-1"
                                style="font-size: 13px;">
                                No rejected inspections found
                            </div>

                            <div
                                class="text-body-secondary"
                                style="font-size: 12px;">
                                Try changing your search or filter.
                            </div>

                        </div>

                    <?php endif; ?>

                    <!-- PAGINATION -->
                    <div class="card-footer bg-body border-top">

                        <div
                            class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3">

                            <small
                                class="text-body-secondary"
                                style="font-size: 12px;">

                                <?php if ($totalRejected > 0): ?>

                                    Showing
                                    <?= $offset + 1 ?>
                                    to
                                    <?= min(
                                        $offset + $perPage,
                                        $totalRejected
                                    ) ?>
                                    of
                                    <?= $totalRejected ?>
                                    entries

                                <?php else: ?>

                                    Showing 0 of 0 entries

                                <?php endif; ?>

                            </small>

                            <?php if ($totalPages > 1): ?>

                                <?php

                                $currentPageUrl = basename(
                                    $_SERVER['PHP_SELF']
                                );

                                ?>

                                <nav
                                    aria-label="Rejected inspection pagination">

                                    <ul class="pagination pagination-sm mb-0">

                                        <!-- PREVIOUS -->
                                        <li
                                            class="page-item <?= $currentPage <= 1
                                                                    ? 'disabled'
                                                                    : '' ?>">

                                            <?php if ($currentPage > 1): ?>

                                                <a
                                                    class="page-link"
                                                    href="<?= htmlspecialchars(
                                                                $currentPageUrl,
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ) ?>?<?= http_build_query([
                                                                        'page' => $currentPage - 1,
                                                                        'search' => $search,
                                                                        'date' => $dateFilter
                                                                    ]) ?>">
                                                    Previous
                                                </a>

                                            <?php else: ?>

                                                <span class="page-link">
                                                    Previous
                                                </span>

                                            <?php endif; ?>

                                        </li>

                                        <!-- PAGE NUMBERS -->
                                        <?php for (
                                            $page = 1;
                                            $page <= $totalPages;
                                            $page++
                                        ): ?>

                                            <li
                                                class="page-item <?= $page === $currentPage
                                                                        ? 'active'
                                                                        : '' ?>">

                                                <a
                                                    class="page-link"
                                                    href="<?= htmlspecialchars(
                                                                $currentPageUrl,
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ) ?>?<?= http_build_query([
                                                                        'page' => $page,
                                                                        'search' => $search,
                                                                        'date' => $dateFilter
                                                                    ]) ?>">

                                                    <?= $page ?>

                                                </a>

                                            </li>

                                        <?php endfor; ?>

                                        <!-- NEXT -->
                                        <li
                                            class="page-item <?= $currentPage >= $totalPages
                                                                    ? 'disabled'
                                                                    : '' ?>">

                                            <?php if ($currentPage < $totalPages): ?>

                                                <a
                                                    class="page-link"
                                                    href="<?= htmlspecialchars(
                                                                $currentPageUrl,
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ) ?>?<?= http_build_query([
                                                                        'page' => $currentPage + 1,
                                                                        'search' => $search,
                                                                        'date' => $dateFilter
                                                                    ]) ?>">
                                                    Next
                                                </a>

                                            <?php else: ?>

                                                <span class="page-link">
                                                    Next
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

        </main>

        <!-- =========================================================
             REJECTION DETAILS
             ========================================================= -->

        <?php foreach (
            $paginatedInspections
            as $inspection
        ): ?>

            <?php

            $inspectionDate = strtotime(
                $inspection['date_inspected']
            );

            $reason = !empty($inspection['reject_reason'])
                ? $inspection['reject_reason']
                : 'No reason provided.';

            $rejectedBy = !empty($inspection['rejected_by'])
                ? $inspection['rejected_by']
                : 'Not recorded';

            $rejectionDate = !empty($inspection['rejection_date'])
                ? strtotime(
                    $inspection['rejection_date']
                )
                : false;

            $targetDate = trim(
                (string) ($inspection['target_date_of_implementation'] ?? '')
            );

            $targetDate = (
                $targetDate !== '' &&
                $targetDate !== '0000-00-00'
            )
                ? strtotime($targetDate)
                : false;



            ?>



            <style>
                .rejection-details-dialog {
                    width: min(900px, calc(100% - 24px));
                    max-width: 900px;
                    max-height: 90vh;
                    margin: auto;
                    padding: 0;
                    border: 1px solid #DEE2E6;
                    border-radius: 16px;
                    overflow: hidden;
                    background: #FFFFFF;
                    color: #212529;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, .22);
                }

                .rejection-details-dialog::backdrop {
                    background: rgba(0, 0, 0, .45);
                }

                .rejection-details-header {
                    background: #203D70;
                    color: #FFFFFF;
                }

                .rejection-details-body {
                    max-height: calc(90vh - 135px);
                    overflow-y: auto;
                    overflow-x: hidden;
                    background: #FFFFFF;
                }

                .rejection-details-dialog .info-card {
                    min-width: 0;
                    overflow-wrap: anywhere;
                    background: #F8FAFC;
                    border: 1px solid #D9E2EF;
                    border-radius: 10px;
                    padding: 12px;
                    height: 100%;
                }

                .rejection-details-dialog .info-label {
                    font-size: 10px;
                    color: #667085;
                    letter-spacing: .3px;
                }

                .rejection-details-dialog .info-value {
                    font-size: 13px;
                    color: #172033;
                    font-weight: 600;
                    overflow-wrap: anywhere;
                }

                @media (max-width: 575.98px) {
                    .rejection-details-dialog {
                        width: calc(100% - 16px);
                        max-width: none;
                        max-height: 94vh;
                        border-radius: 12px;
                    }

                    .rejection-details-body {
                        max-height: calc(94vh - 120px);
                    }

                    .rejection-details-dialog .info-card {
                        padding: 10px;
                    }

                    .rejection-details-dialog .row {
                        --bs-gutter-x: .5rem;
                        --bs-gutter-y: .5rem;
                    }

                    .rejection-details-dialog .section-card {
                        padding: 12px !important;
                    }
                }
            </style>

            <dialog
                id="rejectionDetails<?= (int) $inspection['inspect_id'] ?>"
                class="rejection-details-dialog">

                <!-- HEADER -->
                <div class="rejection-details-header border-bottom">
                    <div class="d-flex align-items-center justify-content-between gap-2 px-3 px-md-4 py-3">

                        <div class="d-flex align-items-center gap-2 gap-md-3 min-width-0">
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                style="
                        width: 44px;
                        height: 44px;
                        background: #DC3545;
                        color: #FFFFFF;
                    ">
                                <i class="bi bi-x-circle-fill fs-4"></i>
                            </div>

                            <div class="min-width-0">
                                <h5 class="fw-bold mb-1"
                                    style="font-size: 17px; color: #FFFFFF;">
                                    Rejection Details
                                </h5>

                                <div style="font-size: 11px; color: #D6E2F5;">
                                    Inspection rejection information and corrective actions.
                                </div>
                            </div>
                        </div>

                        <button type="button"
                            class="btn btn-light d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width: 36px; height: 36px;"
                            aria-label="Close"
                            onclick="this.closest('dialog').close();">
                            <i class="bi bi-x-lg"></i>
                        </button>

                    </div>
                </div>

                <!-- BODY -->
                <div class="rejection-details-body p-2 p-sm-3 p-md-4">

                    <!-- STATUS SUMMARY -->
                    <div class="rounded-3 border p-3 mb-3"
                        style="background: #FFF0F1; border-color: #FFB8BF !important;">

                        <div class="row g-3 align-items-center">

                            <!-- STATUS -->
                            <div class="col-12 col-md-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                        style="width: 42px; height: 42px; background: #DC3545; color: #FFFFFF;">
                                        <i class="bi bi-x-lg fs-5"></i>
                                    </div>

                                    <div>
                                        <div class="info-label">STATUS</div>
                                        <span class="badge rounded-pill mt-1 px-3 py-2"
                                            style="background: #DC3545; color: #FFFFFF;">
                                            Rejected
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- REJECTED BY -->
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                        style="width: 36px; height: 36px; background: #FCE0E3; color: #C82333;">
                                        <i class="bi bi-person-fill fs-5"></i>
                                    </div>

                                    <div class="min-width-0">
                                        <div class="info-label">REJECTED BY</div>
                                        <div class="info-value mt-1">
                                            <?= htmlspecialchars(
                                                (string) $rejectedBy,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- REJECTION DATE -->
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                        style="width: 36px; height: 36px; background: #FCE0E3; color: #C82333;">
                                        <i class="bi bi-calendar-x fs-5"></i>
                                    </div>

                                    <div class="min-width-0">
                                        <div class="info-label">REJECTION DATE</div>
                                        <div class="info-value mt-1">
                                            <?= $rejectionDate
                                                ? date('M d, Y', $rejectionDate)
                                                : 'Not recorded'
                                            ?>
                                        </div>

                                        <?php if ($rejectionDate): ?>
                                            <div class="small text-body-secondary">
                                                <?= date('h:i A', $rejectionDate) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- REJECTION REASON -->
                    <div class="section-card border rounded-3 p-3 mb-3"
                        style="border-color: #DEE2E6 !important;">

                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width: 34px; height: 34px; background: #FCE0E3; color: #C82333;">
                                <i class="bi bi-chat-left-text fs-5"></i>
                            </div>

                            <h6 class="fw-bold mb-0" style="font-size: 14px;">
                                Rejection Reason
                            </h6>
                        </div>

                        <div class="rounded-3 p-3"
                            style="
                    background: #FFF0F1;
                    color: #344054;
                    font-size: 12px;
                    line-height: 1.6;
                    overflow-wrap: anywhere;
                 ">
                            <?= nl2br(
                                htmlspecialchars(
                                    $reason,
                                    ENT_QUOTES,
                                    'UTF-8'
                                )
                            ) ?>
                        </div>
                    </div>

                    <!-- INSPECTION INFORMATION -->
                    <div class="section-card border rounded-3 p-3 mb-3"
                        style="border-color: #DEE2E6 !important;">

                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width: 34px; height: 34px; background: #E0EDFF; color: #0D6EFD;">
                                <i class="bi bi-info-circle-fill fs-5"></i>
                            </div>

                            <h6 class="fw-bold mb-0" style="font-size: 14px;">
                                Inspection Information
                            </h6>
                        </div>

                        <div class="row g-2">

                            <!-- EXTINGUISHER CODE -->
                            <div class="col-12 col-sm-6">
                                <div class="info-card">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 34px; height: 34px; background: #E0EDFF; color: #0D6EFD;">
                                            <i class="bi bi-qr-code fs-5"></i>
                                        </div>

                                        <div class="min-width-0">
                                            <div class="info-label">EXTINGUISHER CODE</div>
                                            <div class="info-value mt-1">
                                                <?= htmlspecialchars(
                                                    $inspection['extinguisher_code'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- LOCATION -->
                            <div class="col-12 col-sm-6">
                                <div class="info-card">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 34px; height: 34px; background: #E0EDFF; color: #0D6EFD;">
                                            <i class="bi bi-geo-alt fs-5"></i>
                                        </div>

                                        <div class="min-width-0">
                                            <div class="info-label">LOCATION</div>
                                            <div class="info-value mt-1">
                                                <?= htmlspecialchars(
                                                    $inspection['location'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TYPE -->
                            <div class="col-12 col-sm-6">
                                <div class="info-card">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 34px; height: 34px; background: #FFF0D6; color: #D99000;">
                                            <i class="bi bi-fire fs-5"></i>
                                        </div>

                                        <div class="min-width-0">
                                            <div class="info-label">TYPE</div>
                                            <div class="info-value mt-1">
                                                <?= htmlspecialchars(
                                                    $inspection['type'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CAPACITY -->
                            <div class="col-12 col-sm-6">
                                <div class="info-card">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 34px; height: 34px; background: #E0EDFF; color: #0D6EFD;">
                                            <i class="bi bi-box-seam fs-5"></i>
                                        </div>

                                        <div class="min-width-0">
                                            <div class="info-label">CAPACITY</div>
                                            <div class="info-value mt-1">
                                                <?= htmlspecialchars(
                                                    $inspection['capacity'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- DATE INSPECTED -->
                            <div class="col-12 col-sm-6">
                                <div class="info-card">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 34px; height: 34px; background: #E0EDFF; color: #0D6EFD;">
                                            <i class="bi bi-calendar3 fs-5"></i>
                                        </div>

                                        <div class="min-width-0">
                                            <div class="info-label">DATE INSPECTED</div>
                                            <div class="info-value mt-1">
                                                <?= date(
                                                    'M d, Y h:i A',
                                                    $inspectionDate
                                                ) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- INSPECTED BY -->
                            <div class="col-12 col-sm-6">
                                <div class="info-card">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 34px; height: 34px; background: #E0EDFF; color: #0D6EFD;">
                                            <i class="bi bi-person-badge fs-5"></i>
                                        </div>

                                        <div class="min-width-0">
                                            <div class="info-label">INSPECTED BY</div>
                                            <div class="info-value mt-1">
                                                <?= !empty($inspection['inspected_by'])
                                                    ? htmlspecialchars(
                                                        $inspection['inspected_by'],
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    )
                                                    : 'Not recorded'
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            

                        </div>
                    </div>

                    <!-- CORRECTIVE ACTION -->
                    <div class="section-card border rounded-3 p-3"
                        style="border-color: #DEE2E6 !important;">

                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width: 34px; height: 34px; background: #F0E1FF; color: #7C3AED;">
                                <i class="bi bi-wrench-adjustable fs-5"></i>
                            </div>

                            <h6 class="fw-bold mb-0" style="font-size: 14px;">
                                Corrective Action
                            </h6>
                        </div>

                        <div class="row g-2">

                            <!-- ACTION TAKEN -->
                            <div class="col-12 col-md-7">
                                <div class="rounded-3 p-3 h-100"
                                    style="background: #E7F1FF;">

                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 32px; height: 32px; background: #D2E5FF; color: #0D6EFD;">
                                            <i class="bi bi-file-earmark-text fs-5"></i>
                                        </div>

                                        <div style="font-size: 10px; font-weight: 600; color: #1557A6;">
                                            ACTION TAKEN
                                        </div>
                                    </div>

                                    <div style="
                            font-size: 12px;
                            line-height: 1.6;
                            color: #172033;
                            overflow-wrap: anywhere;
                        ">
                                        <?= !empty($inspection['action_taken'])
                                            ? nl2br(
                                                htmlspecialchars(
                                                    $inspection['action_taken'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                )
                                            )
                                            : 'N/A'
                                        ?>
                                    </div>

                                </div>
                            </div>

                            <!-- TARGET DATE -->
                            <div class="col-12 col-md-5">
                                <div class="rounded-3 p-3 h-100"
                                    style="background: #E5F4EC;">

                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 32px; height: 32px; background: #D0EBDD; color: #16804A;">
                                            <i class="bi bi-calendar-check fs-5"></i>
                                        </div>

                                        <div style="font-size: 10px; font-weight: 600; color: #246B48;">
                                            TARGET DATE OF IMPLEMENTATION
                                        </div>
                                    </div>

                                    <div class="fw-semibold"
                                        style="font-size: 12px; color: #172033;">
                                        <?= $targetDate !== false
                                            ? date('M d, Y', $targetDate)
                                            : 'N/A'
                                        ?>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="border-top px-3 px-md-4 py-2 d-flex justify-content-end"
                    style="background: #F8FAFC; border-color: #DEE2E6 !important;">

                    <button type="button"
                        class="btn px-3 py-2"
                        style="
                    font-size: 12px;
                    border-radius: 8px;
                    background: #203D70;
                    color: #FFFFFF;
                    border: 1px solid #203D70;
                "
                        onclick="this.closest('dialog').close();">
                        <i class="bi bi-x-circle me-1"></i>
                        Close
                    </button>

                </div>

            </dialog>



        <?php endforeach; ?>

        <?php include 'partials/footer.php'; ?>

    </div>

    <!-- COREUI -->
    <script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
    <script src="vendors/simplebar/js/simplebar.min.js"></script>

    <?php include_once 'notification/session_timeout.php'; ?>

</body>

</html>