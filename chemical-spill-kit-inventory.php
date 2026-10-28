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
    <?php include 'partials/header-nav.php'; ?>
    <h6 style="margin-left:50px;">ON GOING DEVELOPMENT</h6>
    <?php
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

      <!-- INVENTORY SUMMARY -->
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body px-3 py-3">
          <div class="row align-items-center">
            <div class="col">
              <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px">
                  <i class="bi bi-box-seam fs-4"></i>
                </div>
                <div>
                  <div class="text-body-secondary small mb-1">Inventory Kits</div>
                  <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-3 fw-bold text-primary lh-1"><?= number_format($totalRecords) ?></span>
                    <span class="small text-body-secondary">active kits</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-auto">
              <div class="border-start ps-3 ms-2">
                <div class="text-body-secondary small mb-1">Branch</div>
                <div class="fw-semibold small"><?= htmlspecialchars($selectedBranch === 'all' ? 'All Branches' : $selectedBranch, ENT_QUOTES, 'UTF-8') ?></div>
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

            <!-- Search -->
            <div class="col-12 col-lg-5">
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
              <div class="col-12 col-md-6 col-lg-4">
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
            <div class="col-12 col-md-6 col-lg-3">
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
      </div>


      <!-- INVENTORY TABLE -->
      <div class="card border-0 rounded-3 shadow-sm overflow-hidden">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
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
        <div class="card-footer bg-white border-top py-3 px-3">
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
              if (strtolower(trim($_SESSION['Role'] ?? '')) !== 'admin') {
                unset($previousParams['branch'], $nextParams['branch']);
              }
              if (isset($previousParams['search']) && trim((string)$previousParams['search']) === '') {
                unset($previousParams['search']);
              }
              if (isset($nextParams['search']) && trim((string)$nextParams['search']) === '') {
                unset($nextParams['search']);
              }
              $previousUrl = '?' . http_build_query($previousParams);
              $nextUrl = '?' . http_build_query($nextParams);
              ?>
              <nav aria-label="Inventory pagination">
                <ul class="pagination pagination-sm mb-0">
                  <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= htmlspecialchars($previousUrl, ENT_QUOTES, 'UTF-8') ?>">Previous</a>
                  </li>
                  <li class="page-item active"><span class="page-link"><?= $currentPage ?></span></li>
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

    <?php include 'partials/add-chemical-spill-kit-check-sheet.php'; ?>
    <?php include 'partials/edit-chemical-spill-kit-check-sheet.php'; ?>

    <style>
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
  <?php include_once 'notification/added_csk_success.php'; ?>

</body>

</html>