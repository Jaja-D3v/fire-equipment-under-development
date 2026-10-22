<?php
require_once 'backend/authentication/only-inspector.php';
require_once 'backend/controller/MyInspectionsController.php';


$pageData = getMyInspectionsPageData();

$myInspections = $pageData['myInspections'];
$summary = $pageData['summary'];

$totalInspections = $pageData['totalInspections'];
$totalPending = $pageData['totalPending'];
$totalApproved = $pageData['totalApproved'];

$perPage = $pageData['perPage'];
$currentPage = $pageData['currentPage'];
$totalPages = $pageData['totalPages'];
$offset = $pageData['offset'];

$search = $pageData['search'];
$dateFilter = $pageData['dateFilter'];
$statusFilter = $pageData['statusFilter'];

// Records already filtered and paginated by the backend.
$paginatedInspections = $myInspections;

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

        <!-- MAIN TABLE CARD -->
        <div class="card border-0 shadow-sm">

          <!-- CARD HEADER -->
          <div class="card-header bg-body border-bottom py-3">
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">

              <div>

                <h6 class="fw-semibold mb-1" style="font-size: 13px;">
                  Inspection History
                </h6>

                <small class="text-body-secondary" style="font-size: 11px;">
                  Review your inspection records and
                  approval status.
                </small>

              </div>

              <span class="badge bg-primary-subtle text-primary" style="font-size: 11px;">
                <?= $totalInspections ?> Records
              </span>

            </div>

          </div>

          <!-- SEARCH AND FILTER -->
          <form method="GET" class="card border-0 shadow-sm mb-2">

            <div class="card-body p-3">
              <div class="row g-2 align-items-end">

                <!-- SEARCH -->
                <div class="col-12 col-md-6 col-xl">

                  <label class="form-label text-body-secondary mb-1" style="font-size: 11px;">
                    Search
                  </label>
                  <input type="text" name="search" class="form-control" placeholder="Search code, location, or type..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" style="font-size: 12px;">

                </div>

                <!-- DATE FILTER -->
                <div class="col-12 col-sm-6 col-md-3 col-xl-3">

                  <label class="form-label text-body-secondary mb-1"
                    style="font-size: 11px;">
                    Inspection Date
                  </label>

                  <select name="date"
                    class="form-select"
                    style="font-size: 12px;">

                    <?php foreach (
                      [
                        'all' => 'All Inspection Dates',
                        'today' => 'Today',
                        'yesterday' => 'Yesterday',
                        '7' => 'Last 7 Days',
                        '30' => 'Last 30 Days',
                      ] as $value => $label
                    ): ?>

                      <option
                        value="<?= $value ?>"
                        <?= $dateFilter === $value ? 'selected' : '' ?>>

                        <?= $label ?>

                      </option>

                    <?php endforeach; ?>

                  </select>

                </div>

                <!-- STATUS FILTER -->
                <div class="col-12 col-sm-6 col-md-3 col-xl-2">

                  <label class="form-label text-body-secondary mb-1"
                    style="font-size: 11px;">
                    Status
                  </label>

                  <select name="status"
                    class="form-select"
                    style="font-size: 12px;">

                    <option value="all"
                      <?= $statusFilter === 'all' ? 'selected' : '' ?>>
                      All Statuses
                    </option>

                    <option value="Pending"
                      <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>
                      Pending
                    </option>

                    <option value="Approved"
                      <?= $statusFilter === 'Approved' ? 'selected' : '' ?>>
                      Approved
                    </option>

                  </select>

                </div>

                <!-- ACTIONS -->
                <div class="col-6 col-sm-6 col-md-auto">

                  <button type="submit"
                    class="btn btn-primary w-100"
                    style="font-size: 12px;">
                    Filter
                  </button>

                </div>

                <div class="col-6 col-sm-6 col-md-auto">

                  <a href="<?= htmlspecialchars(
                              basename($_SERVER['PHP_SELF']),
                              ENT_QUOTES,
                              'UTF-8'
                            ) ?>"
                    class="btn btn-light border w-100"
                    style="font-size: 12px;">
                    Clear
                  </a>

                </div>

              </div>

            </div>

          </form>

          <!-- DESKTOP TABLE -->
          <div class="table-responsive d-none d-md-block"
            style="min-height: 390px;">

            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">

                <tr>

                  <th class="px-3" style="font-size: 12px;">
                    Date Inspected
                  </th>

                  <th style="font-size: 12px;">
                    Extinguisher Code
                  </th>

                  <th style="font-size: 12px;">
                    Location
                  </th>

                  <th style="font-size: 12px;">
                    Type
                  </th>

                  <th style="font-size: 12px;">
                    Status
                  </th>

                  <th class="text-end px-3" style="font-size: 12px;">
                    Action
                  </th>

                </tr>

              </thead>

              <tbody>

                <?php if (!empty($paginatedInspections)): ?>

                  <?php foreach (
                    $paginatedInspections as $inspection
                  ): ?>

                    <?php
                    $inspectionDate = strtotime(
                      $inspection['date_inspected']
                    );

                    $isApproved =
                      $inspection['evaluation_status']
                      === 'Approved';
                    ?>

                    <tr>

                      <td class="px-3">

                        <div class="fw-semibold"
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

                      <td class="fw-semibold"
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

                        <div>
                          <?= htmlspecialchars(
                            $inspection['type'],
                            ENT_QUOTES,
                            'UTF-8'
                          ) ?>
                        </div>

                        <small class="text-body-secondary">
                          <?= htmlspecialchars(
                            $inspection['capacity'],
                            ENT_QUOTES,
                            'UTF-8'
                          ) ?>
                        </small>

                      </td>

                      <td>

                        <?php if ($isApproved): ?>

                          <span class="badge bg-success-subtle text-success fw-normal"
                            style="font-size: 11px;">

                            <span class="me-1">✓</span>
                            Approved

                          </span>

                        <?php else: ?>

                          <span class="badge bg-warning-subtle text-warning-emphasis fw-normal"
                            style="font-size: 11px;">

                            <span class="me-1">◷</span>
                            Pending

                          </span>

                        <?php endif; ?>

                      </td>

                      <td class="text-end px-3">

                        <button
                          type="button"
                          class="btn btn-sm btn-outline-primary"
                          style="font-size: 12px;"
                          onclick="document.getElementById('inspectionDetails<?= (int) $inspection['inspect_id'] ?>').showModal();">

                          View Details

                        </button>

                      </td>

                    </tr>

                  <?php endforeach; ?>

                <?php else: ?>

                  <tr>
                    <td colspan="6"
                      class="text-center py-5">

                      <div class="text-body-secondary mb-2"
                        style="font-size: 28px;">
                        ⌕
                      </div>

                      <div class="fw-semibold mb-1"
                        style="font-size: 13px;">
                        No inspections found
                      </div>

                      <div class="text-body-secondary" style="font-size: 12px;">
                        Try changing your search, date, or status filter.
                      </div>

                    </td>
                  </tr>

                <?php endif; ?>

              </tbody>

            </table>

          </div>

          <!-- MOBILE LIST -->
          <div class="d-md-none">

            <?php if (!empty($paginatedInspections)): ?>

              <?php foreach (
                $paginatedInspections as $inspection
              ): ?>

                <?php
                $inspectionDate = strtotime(
                  $inspection['date_inspected']
                );

                $isApproved =
                  $inspection['evaluation_status']
                  === 'Approved';
                ?>

                <div class="border-bottom p-3">

                  <div class="d-flex align-items-start
                                                justify-content-between
                                                gap-2 mb-3">

                    <div>

                      <div class="fw-semibold mb-1"
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

                    <?php if ($isApproved): ?>

                      <span class="badge bg-success-subtle text-success fw-normal">
                        Approved
                      </span>

                    <?php else: ?>

                      <span class="badge bg-warning-subtle text-warning-emphasis fw-normal">
                        Pending
                      </span>

                    <?php endif; ?>

                  </div>

                  <div class="mb-3">

                    <small class="text-body-secondary d-block mb-1">
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

                  <div class="d-flex align-items-center
                                                justify-content-between gap-2">

                    <div>

                      <small class="text-body-secondary d-block mb-1">
                        TYPE / CAPACITY
                      </small>

                      <div style="font-size: 12px;">

                        <?= htmlspecialchars(
                          $inspection['type'],
                          ENT_QUOTES,
                          'UTF-8'
                        ) ?>

                        ·

                        <?= htmlspecialchars(
                          $inspection['capacity'],
                          ENT_QUOTES,
                          'UTF-8'
                        ) ?>

                      </div>

                    </div>

                    <button
                      type="button"
                      class="btn btn-sm btn-outline-primary flex-shrink-0"
                      style="font-size: 12px;"
                      onclick="document.getElementById('inspectionDetails<?= (int) $inspection['inspect_id'] ?>').showModal();">

                      View Details

                    </button>

                  </div>

                </div>

              <?php endforeach; ?>

            <?php else: ?>

              <div class="text-center p-5">

                <div class="text-body-secondary mb-2"
                  style="font-size: 28px;">
                  ⌕
                </div>

                <div class="fw-semibold mb-1"
                  style="font-size: 13px;">
                  No inspections found
                </div>

                <div class="text-body-secondary" style="font-size: 12px;">
                  Try changing your search, date, or status filter.
                </div>

              </div>

            <?php endif; ?>

          </div>

          <!-- PAGINATION -->
          <div class="card-footer bg-body border-top">

            <div class="d-flex flex-column flex-sm-row
                                    align-items-center
                                    justify-content-between gap-3">

              <small class="text-body-secondary"
                style="font-size: 12px;">

                <?php if ($totalInspections > 0): ?>

                  Showing <?= $offset + 1 ?>
                  to
                  <?= min(
                    $offset + $perPage,
                    $totalInspections
                  ) ?>
                  of <?= $totalInspections ?> entries

                <?php else: ?>

                  Showing 0 of 0 entries

                <?php endif; ?>

              </small>

              <?php if ($totalPages > 1): ?>

                <nav aria-label="Inspection pagination">

                  <ul class="pagination pagination-sm mb-0">

                    <?php
                    $pageUrl = basename(
                      $_SERVER['PHP_SELF']
                    );

                    $pageUrl = basename($_SERVER['PHP_SELF']);

                    $queryParams = [
                      'search' => $search,
                      'date' => $dateFilter,
                      'status' => $statusFilter,
                    ];
                    ?>

                    <!-- PREVIOUS -->
                    <li class="page-item <?= $currentPage <= 1
                                            ? 'disabled'
                                            : '' ?>">

                      <?php if ($currentPage > 1): ?>

                        <a class="page-link"
                          href="<?= htmlspecialchars(
                                  $pageUrl . '?' . http_build_query(
                                    array_merge(
                                      $queryParams,
                                      ['page' => $currentPage - 1]
                                    )
                                  ),
                                  ENT_QUOTES,
                                  'UTF-8'
                                ) ?>">
                          Previous
                        </a>

                      <?php else: ?>

                        <span class="page-link">
                          Previous
                        </span>

                      <?php endif; ?>

                    </li>

                    <!-- PAGE NUMBERS -->
                    <?php
                    if ($totalPages <= 5) {
                      $visiblePages = range(1, $totalPages);
                    } elseif ($currentPage <= 3) {
                      $visiblePages = [1, 2, 3, 4, '...', $totalPages];
                    } elseif ($currentPage >= $totalPages - 2) {
                      $visiblePages = [
                        1,
                        '...',
                        $totalPages - 3,
                        $totalPages - 2,
                        $totalPages - 1,
                        $totalPages
                      ];
                    } else {
                      $visiblePages = [
                        1,
                        '...',
                        $currentPage - 1,
                        $currentPage,
                        $currentPage + 1,
                        '...',
                        $totalPages
                      ];
                    }
                    ?>

                    <?php foreach ($visiblePages as $page): ?>

                      <?php if ($page === '...'): ?>

                        <li class="page-item disabled">
                          <span class="page-link">...</span>
                        </li>

                      <?php else: ?>

                        <li class="page-item <?= $page === $currentPage
                                                ? 'active'
                                                : '' ?>">

                          <a class="page-link"
                            href="<?= htmlspecialchars(
                                    $pageUrl . '?' . http_build_query(
                                      array_merge(
                                        $queryParams,
                                        ['page' => $page]
                                      )
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                  ) ?>">
                            <?= $page ?>
                          </a>

                        </li>

                      <?php endif; ?>

                    <?php endforeach; ?>


                    <!-- NEXT -->
                    <li class="page-item <?= $currentPage >= $totalPages
                                            ? 'disabled'
                                            : '' ?>">

                      <?php if ($currentPage < $totalPages): ?>

                        <a class="page-link"
                          href="<?= htmlspecialchars(
                                  $pageUrl . '?' . http_build_query(
                                    array_merge(
                                      $queryParams,
                                      ['page' => $currentPage + 1]
                                    )
                                  ),
                                  ENT_QUOTES,
                                  'UTF-8'
                                ) ?>">
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

    <!-- INSPECTION DETAILS DIALOGS -->
    <?php foreach ($paginatedInspections as $inspection): ?>

      <?php
      $inspectionDate = strtotime(
        $inspection['date_inspected']
      );

      $isApproved =
        $inspection['evaluation_status'] === 'Approved';
      ?>


      <dialog
        id="inspectionDetails<?= (int) $inspection['inspect_id'] ?>"
        style="
        width: min(650px, calc(100% - 24px));
        max-width: 650px;
        max-height: 90vh;
        border: 1px solid #DEE2E6;
        border-radius: 16px;
        padding: 0;
        overflow: hidden;
        box-shadow: 0 20px 55px rgba(0, 0, 0, .20);
        color: #212529;
        background: #FFFFFF;
    ">

        <!-- DIALOG HEADER -->
        <div style="
        background: #203D70;
        color: #FFFFFF;
        border-bottom: 1px solid #183158;
    ">
          <div class="d-flex align-items-center justify-content-between gap-3 px-3 py-2">

            <div class="d-flex align-items-center gap-2">
              <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                style="
                        width: 38px;
                        height: 38px;
                        background: rgba(255,255,255,.12);
                        color: #FFFFFF;
                     ">
                <i class="bi bi-clipboard2-check fs-5"></i>
              </div>

              <div>
                <h5 class="fw-bold mb-1"
                  style="font-size: 15px; color: #FFFFFF;">
                  Inspection Details
                </h5>

                <div style="font-size: 11px; color: #D6E2F5;">
                  Inspection record and evaluation summary
                </div>
              </div>
            </div>

            <button
              type="button"
              class="btn d-flex align-items-center justify-content-center flex-shrink-0"
              style="
                    width: 34px;
                    height: 34px;
                    border: 0;
                    border-radius: 8px;
                    background: #FFFFFF;
                    color: #203D70;
                    font-size: 17px;
                "
              aria-label="Close"
              onclick="this.closest('dialog').close();">
              <i class="bi bi-x-lg"></i>
            </button>

          </div>
        </div>

        <!-- DIALOG BODY -->
        <div class="p-3"
          style="
            max-height: calc(90vh - 110px);
            overflow-y: auto;
            background: #FFFFFF;
            color: #212529;
         ">

          <!-- EVALUATION STATUS -->
          <div class="rounded-3 border p-3 mb-3"
            style="
                <?= $isApproved
                  ? 'background: #E8F5EE; border-color: #B7DFC8 !important;'
                  : 'background: #FFF7E6; border-color: #F5D58A !important;' ?>
             ">

            <div class="d-flex align-items-center gap-2">

              <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                style="
                        width: 38px;
                        height: 38px;
                        <?= $isApproved
                          ? 'background: #D0EBDD; color: #16804A;'
                          : 'background: #FCECC5; color: #946200;' ?>
                     ">
                <i class="bi <?= $isApproved
                                ? 'bi-check-circle-fill'
                                : 'bi-clock-history' ?> fs-5"></i>
              </div>

              <div class="flex-grow-1">

                <div class="mb-1"
                  style="
                            font-size: 10px;
                            font-weight: 700;
                            letter-spacing: .5px;
                            color: <?= $isApproved ? '#246B48' : '#805B10' ?>;
                         ">
                  EVALUATION STATUS
                </div>

                <?php if ($isApproved): ?>

                  <span class="badge rounded-pill px-3 py-1"
                    style="
                                background: #16804A;
                                color: #FFFFFF;
                                font-size: 11px;
                              ">
                    <i class="bi bi-check-lg me-1"></i>
                    Approved
                  </span>

                <?php else: ?>

                  <span class="badge rounded-pill px-3 py-1"
                    style="
                                background: #F5D58A;
                                color: #533A00;
                                font-size: 11px;
                              ">
                    <i class="bi bi-clock me-1"></i>
                    Pending Approval
                  </span>

                <?php endif; ?>

              </div>
            </div>
          </div>

          <!-- SECTION HEADING -->
          <div class="d-flex align-items-center gap-2 mb-2">

            <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
              style="
                    width: 34px;
                    height: 34px;
                    background: #E8EDF5;
                    color: #203D70;
                 ">
              <i class="bi bi-fire fs-5"></i>
            </div>

            <div>
              <h6 class="fw-bold mb-1"
                style="font-size: 13px; color: #212529;">
                Fire Extinguisher Information
              </h6>

              <small style="font-size: 10px; color: #667085;">
                Equipment details and inspection record
              </small>
            </div>

          </div>

          <!-- APPROVER INFORMATION -->
          <div class="rounded-3 border p-2 mb-2"
            style="
                background: #F8FAFC;
                border-color: #DEE2E6 !important;
             ">

            <div class="d-flex align-items-center gap-2">

              <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                style="
                        width: 34px;
                        height: 34px;
                        background: #E8EDF5;
                        color: #203D70;
                     ">
                <i class="bi bi-person-check"></i>
              </div>

              <div class="min-width-0">

                <div class="mb-1"
                  style="
                            font-size: 9px;
                            font-weight: 600;
                            letter-spacing: .4px;
                            color: #667085;
                         ">
                  VERIFIED &amp; APPROVED BY
                </div>

                <div class="fw-semibold text-break"
                  style="font-size: 12px; color: #212529;">
                  <?= htmlspecialchars(
                    (string) ($inspection['verified_and_approved_by'] ?? ''),
                    ENT_QUOTES,
                    'UTF-8'
                  ) ?>
                </div>

              </div>
            </div>
          </div>

          <!-- EXTINGUISHER DETAILS -->
          <div class="row g-2">

            <!-- EXTINGUISHER CODE -->
            <div class="col-12 col-sm-6">
              <div class="border rounded-3 p-2 h-100"
                style="
                        background: #FFFFFF;
                        border-color: #DEE2E6 !important;
                     ">

                <div class="d-flex align-items-center gap-2 mb-1">
                  <i class="bi bi-qr-code"
                    style="color: #0D6EFD; font-size: 13px;"></i>

                  <small style="
                            font-size: 9px;
                            font-weight: 600;
                            letter-spacing: .3px;
                            color: #667085;
                        ">
                    EXTINGUISHER CODE
                  </small>
                </div>

                <div class="fw-bold text-break"
                  style="font-size: 12px; color: #212529;">
                  <?= htmlspecialchars(
                    $inspection['extinguisher_code'],
                    ENT_QUOTES,
                    'UTF-8'
                  ) ?>
                </div>

              </div>
            </div>

            <!-- LOCATION -->
            <div class="col-12 col-sm-6">
              <div class="border rounded-3 p-2 h-100"
                style="
                        background: #FFFFFF;
                        border-color: #DEE2E6 !important;
                     ">

                <div class="d-flex align-items-center gap-2 mb-1">
                  <i class="bi bi-geo-alt"
                    style="color: #0D6EFD; font-size: 13px;"></i>

                  <small style="
                            font-size: 9px;
                            font-weight: 600;
                            letter-spacing: .3px;
                            color: #667085;
                        ">
                    LOCATION
                  </small>
                </div>

                <div class="fw-semibold text-break"
                  style="font-size: 12px; color: #212529;">
                  <?= htmlspecialchars(
                    $inspection['location'],
                    ENT_QUOTES,
                    'UTF-8'
                  ) ?>
                </div>

              </div>
            </div>

            <!-- TYPE -->
            <div class="col-12 col-sm-6">
              <div class="border rounded-3 p-2 h-100"
                style="
                        background: #FFFFFF;
                        border-color: #DEE2E6 !important;
                     ">

                <div class="d-flex align-items-center gap-2 mb-1">
                  <i class="bi bi-fire"
                    style="color: #E5A000; font-size: 13px;"></i>

                  <small style="
                            font-size: 9px;
                            font-weight: 600;
                            letter-spacing: .3px;
                            color: #667085;
                        ">
                    TYPE
                  </small>
                </div>

                <div class="fw-semibold text-break"
                  style="font-size: 12px; color: #212529;">
                  <?= htmlspecialchars(
                    $inspection['type'],
                    ENT_QUOTES,
                    'UTF-8'
                  ) ?>
                </div>

              </div>
            </div>

            <!-- CAPACITY -->
            <div class="col-12 col-sm-6">
              <div class="border rounded-3 p-2 h-100"
                style="
                        background: #FFFFFF;
                        border-color: #DEE2E6 !important;
                     ">

                <div class="d-flex align-items-center gap-2 mb-1">
                  <i class="bi bi-box-seam"
                    style="color: #0D6EFD; font-size: 13px;"></i>

                  <small style="
                            font-size: 9px;
                            font-weight: 600;
                            letter-spacing: .3px;
                            color: #667085;
                        ">
                    CAPACITY
                  </small>
                </div>

                <div class="fw-semibold text-break"
                  style="font-size: 12px; color: #212529;">
                  <?= htmlspecialchars(
                    $inspection['capacity'],
                    ENT_QUOTES,
                    'UTF-8'
                  ) ?>
                </div>

              </div>
            </div>

            <!-- DATE INSPECTED -->
            <div class="col-12">
              <div class="border rounded-3 p-2"
                style="
                        background: #F8FAFC;
                        border-color: #DEE2E6 !important;
                     ">

                <div class="d-flex align-items-center gap-2 mb-1">
                  <i class="bi bi-calendar2-check"
                    style="color: #0D6EFD; font-size: 13px;"></i>

                  <small style="
                            font-size: 9px;
                            font-weight: 600;
                            letter-spacing: .3px;
                            color: #667085;
                        ">
                    DATE INSPECTED
                  </small>
                </div>

                <div class="fw-semibold"
                  style="font-size: 12px; color: #212529;">
                  <?= date('M d, Y h:i A', $inspectionDate) ?>
                </div>

              </div>
            </div>

          </div>
        </div>

        <!-- DIALOG FOOTER -->
        <div class="border-top px-3 py-2 d-flex justify-content-end"
          style="
            background: #F8FAFC;
            border-color: #DEE2E6 !important;
         ">

          <button
            type="button"
            class="btn px-3 py-2"
            style="
                font-size: 11px;
                border-radius: 7px;
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

  <script>
    const header = document.querySelector("header.header");

    document.addEventListener("scroll", () => {
      if (header) {
        header.classList.toggle(
          "shadow-sm",
          document.documentElement.scrollTop > 0
        );
      }
    });

    // Close dialog when clicking outside its content.
    document.querySelectorAll("dialog").forEach((dialog) => {
      dialog.addEventListener("click", (event) => {
        if (event.target === dialog) {
          dialog.close();
        }
      });
    });
  </script>

  <?php include_once 'notification/session_timeout.php'; ?>

</body>

</html>