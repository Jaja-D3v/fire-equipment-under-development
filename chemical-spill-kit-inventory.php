<?php
require_once 'backend/authentication/SessionChecker.php';
require_once 'backend/controller/DropdownController.php';
require_once 'backend/controller/ChemicalSpillKitController.php';


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
    <?php include 'partials/header-nav.php';
    $chemicalSpillKits = $chemicalSpillKits ?? [];
    $totalRecords = $totalRecords ?? 0;
    $currentPage = max(1, (int)($currentPage ?? ($_GET['page'] ?? 1)));
    $limit = 10;
    $totalPages = max(1, (int)ceil($totalRecords / $limit));
    $currentPage = min($currentPage, $totalPages);
    $branches = $branches ?? [];
    $search = trim($_GET['search'] ?? '');
    $selectedBranch = $branch ?? 'all';
    $materialDefinitions = [
      ['name' => 'Pads', 'field' => 'pads', 'icon' => 'bi-layers'],
      ['name' => 'Boom Pillow', 'field' => 'boom_pillow', 'icon' => 'bi-box'],
      ['name' => 'Socks', 'field' => 'socks', 'icon' => 'bi-shield-check'],
      ['name' => 'Disposal Bag', 'field' => 'disposal_bag', 'icon' => 'bi-bag'],
      ['name' => 'Goggles', 'field' => 'goggles', 'icon' => 'bi-eyeglasses'],
      ['name' => 'Face Mask', 'field' => 'face_mask', 'icon' => 'bi-shield'],
      ['name' => 'Rubber Gloves', 'field' => 'rubber_gloves', 'icon' => 'bi-hand-thumbs-up'],
      ['name' => 'Nitrile Gloves', 'field' => 'nitrile_gloves', 'icon' => 'bi-hand-thumbs-up-fill']
    ];
    ?>
    <!-- MAIN CONTENT -->
    <main class="inspection-page csk-inventory-page">
      <!-- PAGE HEADER -->
      <section class="inspection-hero csk-inventory-hero">
        <div class="inspection-hero-title">
          <div class="inspection-hero-icon"><i class="bi bi-shield-exclamation"></i></div>
          <div>
            <h1>Chemical Spill Kit Inventory</h1>
            <p>Track active kits and monthly inspection status by branch.</p>
          </div>
        </div>
      </section>

      <!-- INVENTORY SUMMARY -->
      <section class="inspection-panel csk-summary-panel">
        <div class="csk-summary-content">
          <div class="row align-items-center">
            <div class="col">
              <div class="d-flex align-items-center gap-3">
                <div class="csk-summary-icon">
                  <i class="bi bi-box-seam fs-4"></i>
                </div>
                <div>
                  <div class="text-body-secondary small mb-1">Inventory Kits</div>
                  <div class="d-flex align-items-baseline gap-2">
                    <span class="csk-summary-count"><?= number_format($totalRecords) ?></span>
                    <span class="small text-body-secondary">active kits</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-auto">
              <div class="csk-summary-branch">
                <div class="text-body-secondary small mb-1">Branch</div>
                <div class="fw-semibold small"><?= htmlspecialchars($selectedBranch === 'all' ? 'All Branches' : $selectedBranch, ENT_QUOTES, 'UTF-8') ?></div>
              </div>
            </div>
          </div>
        </div>
      </section>


      <!-- SEARCH AND FILTERS -->
      <section class="inspection-panel csk-filter-panel">
        <div class="csk-filter-body">
          <form method="GET" class="row g-2 align-items-center csk-filter-form">
            <input type="hidden" name="page" value="1">

            <!-- Search -->
            <div class="col-12 col-lg-5 csk-search-group">
              <div class="input-group input-group-sm">
                <input
                  type="search"
                  name="search"
                  id="cskSearch"
                  class="form-control"
                  placeholder="Search by CSK code, location, or branch..."
                  value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                <button class="btn btn-primary px-3" type="submit">
                  <i class="cil-search me-1"></i>
                  Search
                </button>
              </div>
            </div>

            <!-- Branch Filter (Admin Only) -->
            <?php if (strtolower(trim($_SESSION['Role'] ?? '')) === 'admin'): ?>
              <div class="csk-branch-field">
                <select
                  class="form-select form-select-sm w-100"
                  id="branchFilter"
                  name="branch">
                  <option value="all" <?= $selectedBranch === 'all' ? 'selected' : '' ?>>
                    All Branches
                  </option>

                  <?php if (!empty($branches)): ?>
                    <?php foreach ($branches as $branch): ?>
                      <option
                        value="<?= htmlspecialchars($branch['value'], ENT_QUOTES, 'UTF-8') ?>"
                        <?= $selectedBranch === $branch['value'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($branch['value'], ENT_QUOTES, 'UTF-8') ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
            <?php endif; ?>

            <!-- Add Chemical Spill Kit -->
            <div class="csk-add-field">
              <button
                type="button"
                class="btn btn-primary btn-sm w-100 text-nowrap"
                data-bs-toggle="modal"
                data-bs-target="#addChemicalSpillKitModal">
                <i class="cil-plus me-1"></i>
                Add Spill Kit
              </button>
            </div>
          </form>
        </div>
      </section>


      <!-- INVENTORY TABLE -->
      <div class="inspection-table-card csk-inventory-table-card">
        <div class="table-responsive">
          <table class="inspection-table">
            <thead>
              <tr>
                <th class="px-3 py-3 small text-secondary text-uppercase text-nowrap">CSK Code</th>
                <th class="py-3 small text-secondary text-uppercase">Location</th>
                <th class="py-3 small text-secondary text-uppercase">Branch</th>
                <th class="py-3 small text-secondary text-uppercase text-nowrap">Inspected This Month</th>
                <th class="px-3 py-3 small text-secondary text-uppercase text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($chemicalSpillKits)): ?>
                <?php foreach ($chemicalSpillKits as $kit): ?>
                  <tr>
                    <td class="px-3 fw-semibold text-primary small text-nowrap"><?= htmlspecialchars((string)$kit['chemical_spill_kit_code'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="small"><?= htmlspecialchars((string)$kit['location'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="small"><?= htmlspecialchars((string)$kit['branch'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="small">
                      <?php if (!empty($kit['is_inspected_this_month'])): ?>
                        <span class="badge rounded-pill bg-success-subtle text-success-emphasis px-3 py-2">Yes</span>
                      <?php else: ?>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis px-3 py-2">No</span>
                      <?php endif; ?>
                    </td>
                    <td class="px-3 text-end">
                      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewCskModal-<?= (int)$kit['chemical_spill_kit_id'] ?>">
                        <i class="bi bi-eye me-1"></i>View Details
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center py-5">
                    <div class="text-body-secondary small">No chemical spill kit inventory records found.</div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>



        <!-- RECORD COUNT AND PAGINATION -->
        <div class="inspection-table-footer csk-inventory-footer">
          <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <span class="small text-body-secondary">
              Showing
              <strong><?= $totalRecords > 0 ? (($currentPage - 1) * $limit + 1) : 0 ?> - <?= min($currentPage * $limit, $totalRecords) ?></strong>
              of <strong><?= number_format($totalRecords) ?></strong> inventory kits
            </span>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <?php
              $previousParams = $_GET;
              $previousParams['page'] = max(1, $currentPage - 1);
              unset($previousParams['limit']);
              $nextParams = $_GET;
              $nextParams['page'] = min($totalPages, $currentPage + 1);
              unset($nextParams['limit']);
              unset($previousParams['success-add'], $nextParams['success-add']);
              if (strtolower(trim($_SESSION['Role'] ?? '')) !== 'admin') {
                unset($previousParams['branch'], $nextParams['branch']);
              }
              if (isset($previousParams['search']) && trim((string)$previousParams['search']) === '') {
                unset($previousParams['search']);
              }
              if (isset($nextParams['search']) && trim((string)$nextParams['search']) === '') {
                unset($nextParams['search']);
              }
              $previousUrl = 'chemical-spill-kit-inventory.php?' . http_build_query($previousParams);
              $nextUrl = 'chemical-spill-kit-inventory.php?' . http_build_query($nextParams);
              $pageParams = $previousParams;
              unset($pageParams['page']);
              $firstParams = $pageParams;
              $firstParams['page'] = 1;
              $firstUrl = 'chemical-spill-kit-inventory.php?' . http_build_query($firstParams);
              $lastParams = $pageParams;
              $lastParams['page'] = $totalPages;
              $lastUrl = 'chemical-spill-kit-inventory.php?' . http_build_query($lastParams);
              if ($totalPages <= 4) {
                $paginationPageNumbers = range(1, $totalPages);
              } elseif ($currentPage <= 3) {
                $paginationPageNumbers = [1, 2, 3, $totalPages];
              } elseif ($currentPage >= $totalPages - 2) {
                $paginationPageNumbers = [1, $totalPages - 2, $totalPages - 1, $totalPages];
              } else {
                $paginationPageNumbers = [1, $currentPage - 1, $currentPage, $totalPages];
              }
              $paginationPageNumbers = array_values(array_unique($paginationPageNumbers));
              sort($paginationPageNumbers);
              $previousPageNumber = 0;
              ?>
                            <nav class="inspection-pagination csk-inventory-pagination" aria-label="Inventory pagination">
                <a class="inspection-page-btn" href="<?= htmlspecialchars($firstUrl, ENT_QUOTES, 'UTF-8') ?>" aria-label="First page" <?= $currentPage <= 1 ? 'aria-disabled="true" tabindex="-1"' : '' ?>><i class="bi bi-chevron-double-left"></i></a>
                <a class="inspection-page-btn" href="<?= htmlspecialchars($previousUrl, ENT_QUOTES, 'UTF-8') ?>" aria-label="Previous page" <?= $currentPage <= 1 ? 'aria-disabled="true" tabindex="-1"' : '' ?>><i class="bi bi-chevron-left"></i></a>
                <?php foreach ($paginationPageNumbers as $pageNumber): ?>
                  <?php if ($previousPageNumber > 0 && $pageNumber > $previousPageNumber + 1): ?><span class="csk-inventory-page-ellipsis" aria-hidden="true">&hellip;</span><?php endif; ?>
                  <?php $pageParams['page'] = $pageNumber; $pageUrl = 'chemical-spill-kit-inventory.php?' . http_build_query($pageParams); ?>
                  <a class="inspection-page-btn <?= $pageNumber === $currentPage ? 'active' : '' ?>" href="<?= htmlspecialchars($pageUrl, ENT_QUOTES, 'UTF-8') ?>" <?= $pageNumber === $currentPage ? 'aria-current="page"' : '' ?>><?= $pageNumber ?></a>
                  <?php $previousPageNumber = $pageNumber; ?>
                <?php endforeach; ?>
                <a class="inspection-page-btn" href="<?= htmlspecialchars($nextUrl, ENT_QUOTES, 'UTF-8') ?>" aria-label="Next page" <?= $currentPage >= $totalPages ? 'aria-disabled="true" tabindex="-1"' : '' ?>><i class="bi bi-chevron-right"></i></a>
                <a class="inspection-page-btn" href="<?= htmlspecialchars($lastUrl, ENT_QUOTES, 'UTF-8') ?>" aria-label="Last page" <?= $currentPage >= $totalPages ? 'aria-disabled="true" tabindex="-1"' : '' ?>><i class="bi bi-chevron-double-right"></i></a>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include 'partials/add-chemical-spill-kit-check-sheet.php'; ?>
    <?php include 'partials/edit-chemical-spill-kit-check-sheet.php'; ?>

    <style>
      .csk-inventory-page { min-height: 100%; }
      .csk-inventory-hero { margin-bottom: 16px; padding: 18px 22px; border-radius: 16px; }
      .csk-inventory-hero .inspection-hero-icon { width: 50px; height: 50px; border-radius: 14px; font-size: 22px; }
      .csk-inventory-hero h1 { margin-bottom: 3px; font-size: 22px; }
      .csk-inventory-hero p { font-size: 12px; }
      .csk-summary-panel, .csk-filter-panel { margin: 0 0 16px; padding: 14px 18px; }
      .csk-summary-content { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
      .csk-summary-icon { display: grid; width: 42px; height: 42px; place-items: center; color: #0866f5; background: #eaf2ff; border-radius: 12px; font-size: 20px; }
      .csk-summary-count { color: #0866f5; font-size: 22px; font-weight: 750; line-height: 1; }
      .csk-summary-branch { padding-left: 18px; border-left: 1px solid #e7edf7; }
      .csk-filter-form { display: grid; grid-template-columns: minmax(240px, 1fr) minmax(170px, .65fr) auto; align-items: center; gap: 10px; width: 100%; margin: 0; }
      .csk-filter-form > div { width: auto; max-width: none; margin: 0; }
      .csk-filter-form > .csk-search-group { grid-column: auto; }
      .csk-filter-form > .csk-add-field { min-width: 145px; }
      .csk-filter-form .form-control, .csk-filter-form .form-select { min-height: 38px; border-color: #dce5f4; border-radius: 9px; font-size: 13px; }
      .csk-filter-form .btn { min-height: 38px; border-radius: 9px; font-size: 12px; font-weight: 650; }
      .csk-inventory-table-card { overflow: hidden; }
      .csk-inventory-table-card .inspection-table th, .csk-inventory-table-card .inspection-table td { padding: 11px 14px; }
      .csk-inventory-table-card .inspection-table th { white-space: nowrap; }
      .csk-inventory-table-card .inspection-table .btn { border-radius: 8px; font-size: 12px; font-weight: 650; }
      .csk-inventory-footer { flex-wrap: wrap; }
      .csk-inventory-pagination { justify-content: flex-end; gap: 5px; }
      .csk-inventory-page-ellipsis { display: inline-grid; min-width: 22px; height: 38px; place-items: center; color: #637797; font-size: 13px; }
      .csk-inventory-pagination .inspection-page-btn[aria-disabled="true"] { opacity: .45; }
      @media (max-width: 560px) { .csk-inventory-pagination { width: 100%; justify-content: flex-end; } }
      @media (max-width: 767.98px) { .csk-filter-form { grid-template-columns: minmax(0, 1fr) minmax(140px, .8fr); } .csk-filter-form > .csk-search-group { grid-column: 1 / -1; } .csk-filter-form > .csk-add-field { min-width: 0; } }
      @media (max-width: 480px) { .csk-filter-form { grid-template-columns: 1fr; } .csk-filter-form > .csk-search-group { grid-column: auto; } }
      @media (max-width: 480px) { .csk-summary-content { align-items: flex-start; flex-direction: column; } .csk-summary-branch { padding-left: 0; border-left: 0; } }
      .csk-inventory-dialog {
        width: calc(100% - 32px);
        max-width: 900px;
        margin: 16px auto;
      }

      .csk-edit-dialog {
        max-width: 1000px;
      }

      /* Keep the edit form inside the modal's height constraint so its body can scroll. */
      .csk-edit-form {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        min-height: 0;
        overflow: hidden;
      }

      .csk-edit-form .csk-inventory-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
      }

      .csk-edit-form .csk-inventory-footer {
        flex: 0 0 auto;
      }

      .csk-inventory-modal {
        --csk-blue: #0d6efd;
        --csk-text: #202938;
        --csk-muted: #778195;
        --csk-border: #e3e8f0;
        --csk-bg: #f5f7fb;
        display: flex;
        flex-direction: column;
        max-height: calc(100dvh - 32px);
        overflow: hidden;
        color: var(--csk-text);
        background: var(--csk-bg);
        border: 1px solid rgba(255, 255, 255, .8);
        border-radius: 20px;
        box-shadow: 0 24px 70px rgba(24, 39, 75, .18);
      }

      .csk-inventory-header,
      .csk-inventory-footer {
        display: flex;
        flex-shrink: 0;
        gap: 12px;
        padding: 16px 24px;
        background: #fff;
        border-color: var(--csk-border);
      }

      .csk-inventory-header-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        color: var(--csk-blue);
        background: #eaf2ff;
        border-radius: 13px;
        font-size: 20px;
      }

      .csk-inventory-modal .modal-title {
        color: var(--csk-text);
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -.3px;
      }

      .csk-inventory-header p {
        margin-top: 4px;
        color: var(--csk-muted);
        font-size: 12px;
      }

      .csk-inventory-body {
        min-height: 0;
        overflow-y: auto;
        padding: 20px 24px;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #c8d0dc transparent;
      }

      .csk-inventory-modal .csk-add-section {
        margin-bottom: 16px;
        padding: 20px;
        background: #fff;
        border: 1px solid var(--csk-border);
        border-radius: 16px;
      }

      .csk-inventory-modal .csk-add-section:last-child {
        margin-bottom: 0;
      }

      .csk-inventory-modal .csk-section-heading {
        display: flex;
        align-items: center;
        gap: 11px;
        margin-bottom: 18px;
      }

      .csk-inventory-modal .csk-section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        color: var(--csk-blue);
        background: #edf4ff;
        border-radius: 11px;
        font-size: 17px;
      }

      .csk-inventory-modal .csk-section-heading h6 {
        margin: 0 0 4px;
        color: var(--csk-text);
        font-size: 14px;
        font-weight: 700;
      }

      .csk-inventory-modal .csk-section-heading p {
        margin: 0;
        color: var(--csk-muted);
        font-size: 12px;
        line-height: 1.5;
      }

      .csk-inventory-modal .csk-material-card {
        padding: 14px;
        background: #fff;
        border: 1px solid #dfe6f0;
        border-radius: 13px;
      }

      .csk-inventory-modal .csk-material-title {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 13px;
        color: #263449;
        font-size: 13px;
        font-weight: 700;
      }

      .csk-inventory-modal .csk-material-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        color: var(--csk-blue);
        background: #edf4ff;
        border-radius: 9px;
        font-size: 16px;
      }

      .csk-inventory-modal .form-label,
      .csk-detail-label {
        display: block;
        margin-bottom: 6px;
        color: #465166;
        font-size: 11px;
        font-weight: 700;
      }

      .csk-inventory-modal .form-control,
      .csk-inventory-modal .form-select {
        width: 100%;
        min-height: 40px;
        padding: 9px 11px;
        color: var(--csk-text);
        background-color: #fbfcfe;
        border: 1px solid #dce3ed;
        border-radius: 9px;
        font-size: 12px;
        box-shadow: none;
        transition: border-color .18s, background-color .18s, box-shadow .18s;
      }

      .csk-inventory-modal .form-control:focus,
      .csk-inventory-modal .form-select:focus {
        background-color: #fff;
        border-color: var(--csk-blue);
        box-shadow: 0 0 0 3px rgba(13, 110, 253, .10);
      }

      .csk-inventory-modal textarea.form-control {
        min-height: 70px;
        resize: vertical;
      }

      .csk-detail-value {
        color: #263449;
        font-size: 13px;
        font-weight: 600;
        overflow-wrap: anywhere;
      }

      .csk-detail-remarks {
        color: var(--csk-muted);
        font-size: 12px;
        line-height: 1.5;
        overflow-wrap: anywhere;
      }

      .csk-inventory-footer {
        justify-content: flex-end;
        padding: 14px 24px;
        border-top: 1px solid var(--csk-border);
      }

      .csk-inventory-footer .btn {
        min-height: 40px;
        padding: 9px 18px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 700;
        transition: background-color .18s, transform .18s;
      }

      .csk-inventory-footer .btn:active {
        transform: scale(.98);
      }

      .csk-inventory-modal .csk-btn-cancel {
        color: #4b5565;
        background: #fff;
        border: 1px solid #dce3ed;
      }

      .csk-inventory-modal .csk-btn-cancel:hover {
        background: #f3f6fa;
      }

      .csk-inventory-modal .csk-btn-save {
        color: #fff;
        background: var(--csk-blue);
        border: 1px solid var(--csk-blue);
      }

      .csk-inventory-modal .csk-btn-save:hover {
        color: #fff;
        background: #0b5ed7;
      }

      .csk-inventory-modal .csk-btn-archive {
        color: #b42318;
        background: #fff;
        border: 1px solid #f0b7b2;
      }

      .csk-inventory-modal .csk-btn-archive:hover {
        color: #912018;
        background: #fff4f2;
      }

      @media (max-width: 767.98px) {
        .csk-inventory-dialog {
          max-width: 620px;
        }

        .csk-inventory-header,
        .csk-inventory-footer {
          padding-right: 16px;
          padding-left: 16px;
        }

        .csk-inventory-body {
          padding: 16px;
        }

        .csk-inventory-modal .csk-add-section {
          padding: 16px;
        }

        .csk-inventory-footer .btn {
          flex: 1;
          padding-right: 10px;
          padding-left: 10px;
        }
      }

      @media (max-width: 420px) {
        .csk-inventory-dialog {
          width: calc(100% - 16px);
          margin: 8px auto;
        }

        .csk-inventory-modal {
          max-height: calc(100dvh - 16px);
        }
      }
    </style>
    <!-- CONTENT HERE -->
  </main>
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
          window.history.replaceState({}, '', url.pathname + url.search + url.hash);
          window.location.reload();
          return;
        } else {
          url.searchParams.set('search', searchValue);
          url.searchParams.set('page', '1');
        }
        window.history.replaceState({}, '', url.pathname + url.search + url.hash);
      });
    });
  </script>
  <?php include_once 'notification/session_timeout.php'; ?>
  <?php include_once 'notification/added_csk_success.php'; ?>

</body>

</html>