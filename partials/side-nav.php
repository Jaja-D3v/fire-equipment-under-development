<div class="sidebar sidebar-dark sidebar-fixed border-end" id="sidebar">
  <div class="sidebar-header border-bottom">

    <div class="sidebar-brand d-flex align-items-center justify-content-center gap-2">
      <img src="assets/img/KPFEMS.png"
        alt="Equipment Management System"
        class="img-fluid"
        style="
         max-height: 70px;
         width: auto;
         filter: drop-shadow(0 0 3px rgba(255,255,255,0.9))
                 drop-shadow(0 0 7px rgba(255,255,255,0.5));
          ">
    </div>

    <button class="btn-close d-lg-none" type="button" data-coreui-theme="dark" aria-label="Close" onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()"></button>
  </div>
  <ul class="sidebar-nav" data-coreui="navigation" data-simplebar>
    <li class="nav-title">Navigation</li>
    <li class="nav-item">

      <!-- side-bar navigation -->
      <a class="nav-link" href="dashboard.php">
        <i class="fa-solid fa-gauge"></i>
        Dashboard
      </a>

      <?php if (strtolower(trim($_SESSION['Role'] ?? '')) === 'inspector'): ?>

        <?php
        $rejectedInspectorName = trim($_SESSION['EmployeeName'] ?? '');
        $rejectedInspectorBranch = trim($_SESSION['Branch'] ?? '');
        $rejectedInspectionCount = 0;

        if ($rejectedInspectorName !== '' && $rejectedInspectorBranch !== '') {
          require_once __DIR__ . '/../backend/model/RejectedInspectionModel.php';
          $rejectedInspectionCount = getRejectedInspectionCountForSidebar(
            $rejectedInspectorName,
            $rejectedInspectorBranch
          );
        }
        ?>

    <li class="nav-item">

      <a class="nav-link" href="scan-QR-code.php">
        <i class="fa-solid fa-qrcode"></i>
        Device Scan QR CODE
      </a>

      <a class="nav-link" href="my-inspections.php">
        <i class="fa-solid fa-magnifying-glass-chart"></i>
        Inspection Records
      </a>

      <a class="nav-link" href="inspector-rejected-inspection.php">
        <i class="fa-solid fa-file-circle-xmark"></i>
        <span>Rejected Inspection</span>
        <?php if ($rejectedInspectionCount > 0): ?>
          <span
            class="ms-2 d-inline-flex align-items-center justify-content-center bg-danger text-white fw-semibold"
            style="min-width: 1.25rem; height: 1.25rem; padding: 0 .25rem; border-radius: 999px; font-size: .65rem; line-height: 1;"
            aria-label="<?= $rejectedInspectionCount ?> rejected inspections">
            <?= $rejectedInspectionCount ?>
          </span>
        <?php endif; ?>
      </a>
    </li>
  <?php endif; ?>


  <?php if (strtolower(trim($_SESSION['Role'] ?? '')) === 'admin'): ?>
    <a class="nav-link" href="QR-generator.php">
      <i class="fa-solid fa-qrcode"></i>
      Print QR Code
    </a>
  <?php endif; ?>

  <!-- Fire Extinguisher List -->
  <li class="nav-item">

    <!-- Parent -->
    <a
      class="nav-link d-flex align-items-center"
      data-bs-toggle="collapse"
      href="#fireExtinguisherMenu"
      role="button"
      aria-expanded="false"
      aria-controls="fireExtinguisherMenu">

      <i class="fa-solid fa-fire-extinguisher me-2"></i>

      <span>Fire Extinguisher</span>

      <i class="bi bi-chevron-down ms-auto submenu-chevron"></i>
    </a>


    <!-- Submenu -->
    <div
      class="collapse"
      id="fireExtinguisherMenu">

      <ul class="nav flex-column ms-3">

        <!-- Active Fire Extinguishers -->
        <li class="nav-item">
          <a class="nav-link" href="list-extinguisher.php">
            <i class="bi bi-fire me-2"></i>
            Active Fire Extinguishers
          </a>
        </li>


        <!-- Expiring Soon -->
        <li class="nav-item">
          <a class="nav-link" href="expiring-extinguisher.php">
            <i class="bi bi-hourglass-split me-2"></i>
            Expiry
          </a>
        </li>


        <!-- Archived -->
        <li class="nav-item">

          <a class="nav-link" href="archived-extinguisher.php">
            <i class="bi bi-trash3 me-2"></i>
            Archived
          </a>
        </li>
      </ul>
    </div>
  </li>
  <?php
  $currentStatus = $_GET['status'] ?? '';
  $pendingInspectionCount = 0;
  $sidebarRole = strtolower(trim($_SESSION['Role'] ?? ''));
  $approvalBranch = '';

  if ($sidebarRole === 'admin') {
    $approvalBranch = trim($_GET['branch'] ?? $_SESSION['Branch'] ?? '');
    if ($approvalBranch === '') {
      $approvalBranch = 'all';
    }
  } elseif ($sidebarRole === 'inspector') {
    $approvalBranch = trim($_SESSION['Branch'] ?? '');
  }

  if ($approvalBranch !== '') {
    require_once __DIR__ . '/../backend/model/InspectionModel.php';
    $pendingInspectionCount = (int) getPendingApprovalCount(null, $approvalBranch, '');
  }

  ?>

    <!-- Activity Log -->
  <li class="nav-item">

    <a class="nav-link" href="chemical-spill-kit-inventory.php">
      <i class="fa-solid fa-toolbox"></i>
      Chemical Spill Kit
    </a>
  </li>

  <?php if (strtolower(trim($_SESSION['Role'] ?? '')) === 'admin'): ?>

    <!-- Inspection Approvals -->
    <li class="nav-item">

      <a
        class="nav-link d-flex align-items-center"
        data-bs-toggle="collapse"
        href="#inspectionApprovalMenu"
        role="button"
        aria-expanded="false"
        aria-controls="inspectionApprovalMenu">

        <i class="bi bi-clipboard-check me-2"></i>

        <span>Inspection Approvals</span>

        <?php if ($pendingInspectionCount > 0): ?>
          <span
            class="ms-2 d-inline-block rounded-circle bg-danger"
            style="width: .5rem; height: .5rem;"
            aria-label="Pending inspections require attention"></span>
        <?php endif; ?>

        <i class="bi bi-chevron-down ms-auto submenu-chevron"></i>
      </a>

      <div class="collapse" id="inspectionApprovalMenu">
        <ul class="nav flex-column ms-3">
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center" href="inspection-pending.php">
              <i class="bi bi-hourglass-split me-2"></i>
              Pending
              <?php if ($pendingInspectionCount > 0): ?>
                <span
                  class="ms-auto d-inline-flex align-items-center justify-content-center bg-danger text-white fw-semibold"
                  style="min-width: 1.25rem; height: 1.25rem; padding: 0 .25rem; border-radius: 999px; font-size: .65rem; line-height: 1;"
                  aria-label="<?= $pendingInspectionCount ?> pending inspections">
                  <?= $pendingInspectionCount ?>
                </span>
              <?php endif; ?>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="inspection-approved.php">
              <i class="bi bi-check-circle me-2"></i>
              Approved
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="inspection-rejected.php">
              <i class="bi bi-x-circle me-2"></i>
              Rejected
            </a>
          </li>
        </ul>
      </div>
    </li>
  <?php endif; ?>


  <!-- Activity Log -->
  <li class="nav-item">

    <a class="nav-link" href="activity-log.php">
      <i class="fa-solid fa-user-pen me-2"></i>
      Activity Log
    </a>
  </li>


  <li class="nav-item">
    <a class="nav-link" href="fire-extinguisher-history.php">
      <i class="fa-solid fa-clock-rotate-left"></i>
      Fire Extinguisher History
    </a>
  </li>
  <?php if (strtolower(trim($_SESSION['Role'] ?? '')) === 'admin'): ?>

    <!-- Maintenance -->
    <li class="nav-item">

      <a class="nav-link" href="maintenance.php">
        <i class="bi bi-tools me-2"></i>
        Maintenance

      </a>

    </li>
  <?php endif; ?>

  <!-- end navigation -->
  </li>
  <li class="nav-divider"></li>
  <li class="nav-title">Extras</li>
  </ul>
  <div class="sidebar-footer border-top d-none d-md-flex justify-content-center py-2">
    <small class="text-white">
      IMS · Equipment Management System
    </small>
  </div>
</div>