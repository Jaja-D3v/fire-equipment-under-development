<header class="header header-sticky p-0 ims-topbar">
  <div class="container-fluid px-3 px-lg-4">
    <div class="d-flex align-items-center gap-3">
      <button
        class="header-toggler ims-sidebar-toggle"
        type="button"
        aria-label="Toggle navigation"
        onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()">
        <svg class="icon icon-lg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true">
          <path fill="currentColor" d="M80 96h352v32H80zm0 144h352v32H80zm0 144h352v32H80z" />
        </svg>
      </button>

      <!-- <div class="ims-topbar-context">
        <span class="ims-topbar-eyebrow">IMS SAFETY MANAGEMENT</span>
        <span class="ims-topbar-title">Operations workspace</span>
      </div> -->
    </div>

    <div class="d-flex align-items-center gap-2 gap-md-3 ms-auto">
      <span class="ims-role-chip">
        <i class="bi bi-shield-check" aria-hidden="true"></i>
        <?= htmlspecialchars($_SESSION['Role'] ?? 'User') ?>
      </span>

      <?php
      $employeeName = trim($_SESSION['EmployeeName'] ?? 'User');
      $nameParts = preg_split('/\s+/', $employeeName);
      if (count($nameParts) >= 2) {
        $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
      } elseif (!empty($nameParts[0])) {
        $initials = strtoupper(substr($nameParts[0], 0, 2));
      } else {
        $initials = 'U';
      }
      ?>

      <div class="dropdown">
        <a
          class="ims-profile-button"
          data-coreui-toggle="dropdown"
          href="#"
          role="button"
          aria-haspopup="true"
          aria-expanded="false"
          aria-label="User menu for <?= htmlspecialchars($employeeName) ?>">
          <span class="ims-avatar"><?= htmlspecialchars($initials) ?></span>
          <span class="ims-profile-name d-none d-sm-inline"><?= htmlspecialchars($employeeName) ?></span>
          <i class="bi bi-chevron-down ims-profile-chevron" aria-hidden="true"></i>
        </a>

        <div class="dropdown-menu dropdown-menu-end ims-profile-menu">
          <div class="ims-profile-menu-label">Signed in as</div>
          <div class="ims-profile-menu-name"><?= htmlspecialchars($employeeName) ?></div>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="#" id="logoutBtn">
            <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Log out
          </a>
        </div>
      </div>
    </div>
  </div>
</header>

<script>
  document.getElementById('logoutBtn').addEventListener('click', function(e) {
    e.preventDefault();

    Swal.fire({
      title: 'Ready to leave?',
      text: 'Your current session will be securely logged out.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Logout',
      cancelButtonText: 'Stay Logged In',
      reverseButtons: true,
      buttonsStyling: false,
      customClass: {
        popup: 'shadow-lg rounded-4',
        confirmButton: 'btn btn-danger px-4 ms-2',
        cancelButton: 'btn btn-light border px-4'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Logging out...',
          text: 'Please wait a moment.',
          icon: 'info',
          showConfirmButton: false,
          allowOutsideClick: false,
          allowEscapeKey: false,
          didOpen: () => Swal.showLoading()
        });

        setTimeout(() => {
          window.location.href = 'backend/authentication/logout.php';
        }, 1200);
      }
    });
  });
</script>