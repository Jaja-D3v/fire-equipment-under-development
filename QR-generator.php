<?php
require_once 'backend/authentication/only-admin.php';
include 'backend/controller/QRCodeGeneratorController.php';


[$equipment, $equipmentDefinition, $selectedBranch, $equipmentBranches, $records] = getQRPageData();
$equipmentRegistry = getQREquipmentRegistry();
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'partials/header.php'; ?>

<body>

  <?php include 'partials/side-nav.php'; ?>
  <div class="wrapper d-flex flex-column min-vh-100">
    <?php include 'partials/header-nav.php'; ?>

    <div class="main-content flex-grow-1">
      <main class="container-fluid px-3 px-md-4 py-3 qr-page">

        <!-- PAGE HEADER -->
        <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden qr-header">
          <div class="card-body p-3 p-md-4 bg-light">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="bg-primary-subtle text-primary rounded-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width:56px;height:56px;">
                  <i class="bi bi-qr-code-scan fs-3"></i>
                </div>
                <div>
                  <h4 class="fw-bold mb-1 text-dark">
                    Print QR Codes
                  </h4>
                  <p class="text-body-secondary mb-0 small">
                    Select equipment to print its QR codes.
                  </p>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                  <i class="bi <?= htmlspecialchars($equipmentDefinition['icon']) ?> me-1"></i>
                  <?= htmlspecialchars($equipmentDefinition['label']) ?>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- MAIN CARD -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden qr-main-card">

          <!-- TOOLBAR -->
          <div class="card-body border-bottom bg-white p-3 qr-toolbar">
            <div class="row align-items-center g-2">

              <!-- SEARCH -->
              <div class="col-12 col-md-auto">
                <select class="form-select" id="equipmentFilter" style="min-width:220px;">
                  <?php foreach ($equipmentRegistry as $equipmentId => $definition): ?>
                    <option value="<?= htmlspecialchars($equipmentId) ?>" <?= $equipment === $equipmentId ? 'selected' : '' ?>><?= htmlspecialchars($definition['label']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-12 col-lg">
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-body-secondary"></i>
                  </span>
                  <input type="text" class="form-control border-start-0 ps-0" placeholder="Search code or location..." id="qrSearch">
                </div>
              </div>

              <!-- BRANCH FILTER - ADMIN ONLY -->
              <?php if (strtolower(trim($_SESSION['Role'] ?? '')) === 'admin'): ?>
                <div class="col-12 col-md-auto">
                  <select class="form-select" id="branchFilter" style="min-width:190px;">
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
          </div>

          <!-- TABLE -->
          <div class="table-responsive qr-table-wrapper" style="min-height: 520px;">
            <table class="table table-hover align-middle mb-0 qr-table">
              <thead class="table-light">
                <tr>
                  <th width="55" class="ps-3">
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
                  <th width="50"></th>
                </tr>
              </thead>
              <tbody class="shadow-sm">
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
                          <div class="bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                            <i class="bi <?= htmlspecialchars($equipmentDefinition['icon']) ?>"></i>
                          </div>
                          <span class="fw-semibold">
                            <?= htmlspecialchars($row['code']) ?>
                          </span>
                        </div>
                      </td>

                      <!-- TYPE -->
                      <td>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
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

          <div id="selectionFooter" class="bg-primary-subtle border-top p-3 d-none">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
              <div class="text-primary fw-semibold">
                <i class="bi bi-check-circle-fill me-1"></i>
                <span id="selectedCount">0</span>
                item(s) selected
              </div>
              <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-light border rounded-3 px-3" onclick="clearSelection()">
                  <i class="bi bi-x-lg me-1"></i>
                  Clear
                </button>
                <button type="button" class="btn btn-primary rounded-3 px-3" onclick="printSelected()">
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
    window.qrPrintBranch = <?= json_encode(
                              $selectedBranch
                            ) ?>;
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
