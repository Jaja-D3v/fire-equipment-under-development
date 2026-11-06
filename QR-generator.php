<?php
require_once 'backend/authentication/only-admin.php';
include 'backend/controller/QRCodeGeneratorController.php';


[$equipment, $equipmentDefinition, $selectedBranch, $equipmentBranches, $records] = getQRPageData();
$equipmentRegistry = getQREquipmentRegistry();
?>

<!DOCTYPE html>
<html lang="en">
<?php
// Keep the reference page and shared QR styles untouched; append page-only presentation.
ob_start();
include 'partials/header.php';
$qrPageHead = ob_get_clean();
echo str_replace('</head>', '<link rel="stylesheet" href="css/qr-generator.css?v=' . hash_file('sha256', __DIR__ . '/css/qr-generator.css') . '"></head>', $qrPageHead);
?>

<body>

  <?php include 'partials/side-nav.php'; ?>
  <div class="wrapper d-flex flex-column min-vh-100">
    <?php include 'partials/header-nav.php'; ?>

    <div class="main-content flex-grow-1">
      <main class="container-fluid px-3 px-md-4 py-3 py-lg-4 qr-page qr-generator-page">

        <!-- PAGE HEADER -->
        <div class="card mb-3 overflow-hidden qr-header">
          <div class="card-body p-3 p-md-4 bg-light">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="qr-hero-icon bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0">
                  <i class="bi bi-qr-code-scan" aria-hidden="true"></i>
                </div>
                <div>
                  <h1 class="mb-1 text-dark">
                    Print QR Codes
                  </h1>
                  <p class="text-body-secondary mb-0 small">
                    Select equipment to print its QR codes.
                  </p>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                  <i class="bi <?= htmlspecialchars($equipmentDefinition['icon']) ?> me-1"></i>
                  Fire Equipment
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Separate filter and table panels follow the inspection page structure. -->
        <section class="card bg-white mb-3 qr-filter-panel qr-toolbar" aria-label="QR code filters">
            <div class="row align-items-end g-3">

              <!-- SEARCH -->
              <div class="col-6 col-lg-3">
                <label class="form-label fw-semibold text-body-secondary mb-2" for="equipmentFilter">Equipment</label>
                <select class="form-select" id="equipmentFilter">
                  <?php foreach ($equipmentRegistry as $equipmentId => $definition): ?>
                    <option value="<?= htmlspecialchars($equipmentId) ?>" <?= $equipment === $equipmentId ? 'selected' : '' ?>><?= htmlspecialchars($definition['label']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-12 col-lg-6 order-last order-lg-first">
                <label class="form-label fw-semibold text-body-secondary mb-2" for="qrSearch">Search equipment</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-body-secondary"></i>
                  </span>
                  <input type="text" class="form-control border-start-0 ps-0" placeholder="Search code or location..." id="qrSearch">
                </div>
              </div>

              <!-- BRANCH FILTER - ADMIN ONLY -->
              <?php if (strtolower(trim($_SESSION['Role'] ?? '')) === 'admin'): ?>
                <div class="col-6 col-lg-3">
                  <label class="form-label fw-semibold text-body-secondary mb-2" for="branchFilter">Branch</label>
                  <select class="form-select" id="branchFilter">
                    <option value="all" <?= $selectedBranch === 'all' ? 'selected' : '' ?>>
                      All Branches
                    </option>
                    <?php if ($selectedBranch === 'none'): ?><option value="none" selected>Choose an available branch</option><?php endif; ?>
                    <?php foreach ($equipmentBranches as $branch): ?>
                      <option value="<?= htmlspecialchars($branch) ?>" <?= $selectedBranch === $branch ? 'selected' : '' ?>><?= htmlspecialchars($branch) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endif; ?>

              <script>
                document.addEventListener('DOMContentLoaded', function() {
                  const branchFilter = document.getElementById('branchFilter');
                  const equipmentFilter = document.getElementById('equipmentFilter');

                  function reloadQRPage() {
                    const url = new URL(window.location.href);
                    if (branchFilter) url.searchParams.set('branch', branchFilter.value);
                    if (equipmentFilter) url.searchParams.set('equipment', equipmentFilter.value);
                    window.location.href = url.toString();
                  }
                  if (branchFilter) branchFilter.addEventListener('change', reloadQRPage);
                  if (equipmentFilter) equipmentFilter.addEventListener('change', function() {
                    const url = new URL(window.location.href);
                    url.searchParams.set('equipment', this.value);
                    window.location.href = url.toString();
                  });

                });
              </script>
            </div>
        </section>

        <!-- TABLE -->
        <div class="card overflow-hidden qr-main-card">
          <div class="table-responsive qr-table-wrapper">
            <table class="table table-hover align-middle mb-0 qr-table">
              <thead class="table-light">
                <tr>
                  <th width="44" class="ps-3">
                    <div class="form-check mb-0">
                      <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                    </div>
                  </th>
                  <th class="text-uppercase small text-body-secondary">
                    <?= htmlspecialchars($equipmentDefinition['code_label']) ?>
                  </th>
                  <th class="text-uppercase small text-body-secondary">
                    <?= htmlspecialchars($equipmentDefinition['type_heading']) ?>
                  </th>
                  <th class="text-uppercase small text-body-secondary">
                    Location
                  </th>
                  <th class="text-uppercase small text-body-secondary">
                    Branch
                  </th>
                  <th width="16"></th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($records)): ?>
                  <?php foreach ($records as $row): ?>
                    <tr>

                      <!-- CHECKBOX -->
                      <td class="ps-3">
                        <input class="form-check-input equipment-checkbox" type="checkbox" value="<?= htmlspecialchars($row['code']) ?>" data-branch="<?= htmlspecialchars($row['branch']) ?>" onchange="updateSelection()">
                      </td>

                      <!-- CODE -->
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="qr-record-icon bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="bi <?= htmlspecialchars($equipmentDefinition['icon']) ?>"></i>
                          </div>
                          <span class="fw-semibold">
                            <?= htmlspecialchars($row['code']) ?>
                          </span>
                        </div>
                      </td>

                      <!-- TYPE -->
                      <td>
                        <span class="type-badge badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                          <?= htmlspecialchars($row['equipment_type']) ?>
                        </span>
                      </td>

                      <!-- LOCATION -->
                      <td>
                        <span class="text-body-secondary">
                          <i class="bi bi-geo-alt-fill text-primary me-1"></i>
                          <?= htmlspecialchars($row['location']) ?>
                        </span>
                      </td>

                      <!-- BRANCH -->
                      <td>
                        <span class="text-body-secondary">
                          <i class="bi bi-building text-primary me-1"></i>
                          <?= htmlspecialchars($row['branch']) ?>
                        </span>
                      </td>

                      <!-- ACTION -->
                      <td></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>

                  <tr>
                    <td colspan="6" class="text-center py-5">
                      <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px;height:60px;">
                        <i class="bi <?= htmlspecialchars($equipmentDefinition['icon']) ?> text-body-secondary fs-4"></i>
                      </div>
                      <div class="fw-semibold text-dark">
                        <?= $selectedBranch === 'none' ? 'No available branch selected' : 'No ' . htmlspecialchars($equipmentDefinition['label']) . ' records found' ?>
                      </div>

                      <div class="small text-body-secondary mt-1">
                        <?= $selectedBranch === 'none' ? 'Choose a branch available for this equipment type.' : 'There are no records available for this equipment and branch.' ?>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>

              </tbody>
            </table>
          </div>

          <!-- SELECTION FOOTER -->

          <div id="selectionFooter" class="qr-selection-footer bg-primary-subtle border-top p-3 d-none">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
              <div class="text-primary fw-semibold">
                <i class="bi bi-check-circle-fill me-1"></i>
                <span id="selectedCount">0</span>
                item(s) selected
              </div>
              <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-light border px-3" onclick="clearSelection()">
                  <i class="bi bi-x-lg me-1"></i>
                  Clear
                </button>
                <button type="button" class="btn btn-primary px-3" onclick="printSelected()">
                  <i class="bi bi-printer me-1"></i>
                  Print Selected
                </button>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- SEARCH / SELECTION JS -->
    <?php include 'partials/footer.php'; ?>
  </div>

  <!-- CoreUI -->
  <script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
  <script src="vendors/simplebar/js/simplebar.min.js"></script>
  <script>
    window.qrPrintBranch = <?= json_encode( $selectedBranch ) ?>;
    window.qrEquipment = <?= json_encode($equipment) ?>;
    window.qrEquipmentLabel = <?= json_encode($equipmentDefinition['label']) ?>;
  </script>
  <script src="js/QR-generator.js"></script>
  <script>
    const header =
      document.querySelector(
        "header.header"
      );
    document.addEventListener(
      "scroll",
      () => {
        if (header) {
          header.classList.toggle(
            "shadow-sm",
            document.documentElement.scrollTop > 0

          );
        }
      }
    );
  </script>
  <?php include_once 'notification/session_timeout.php'; ?>
</body>

</html>
