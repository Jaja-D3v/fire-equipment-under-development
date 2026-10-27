<!DOCTYPE html>
<html lang="en">
<?php include 'partials/header.php'; ?>

<body>
  <?php include 'partials/side-nav.php'; ?>
  <div class="wrapper d-flex flex-column min-vh-100">
    <?php include 'partials/header-nav.php'; ?>
    <div class="body flex-grow-1 px-3 py-4">
      <div class="container-fluid">
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <div>
              <h4 class="mb-1">Chemical Spill Kit</h4>
              <p class="text-body-secondary mb-0">Manage chemical spill kit stock checklists.</p>
            </div>
            <button type="button" class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#spillKitChecklistModal">
              <i class="cil-clipboard me-1"></i> Open Checklist
            </button>
          </div>
        </div>
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center py-5">
            <div class="fs-1 text-primary mb-2"><i class="cil-qr-code"></i></div>
            <h5>Checklist Preview</h5>
            <p class="text-body-secondary mb-3">Click the button above to open the Chemical Spill Kit checklist.</p>
            <button type="button" class="btn btn-primary px-3"
              data-bs-toggle="modal" data-bs-target="#addChemicalSpillKitModal">
              <i class="cil-plus me-1"></i> Add Spill Kit
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>



  <?php include 'partials/footer.php'; ?>
  <?php include 'partials/add-chemical-spill-kit-check-sheet.php'; ?>

  <script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
  <script src="vendors/simplebar/js/simplebar.min.js"></script>
  <script>
    const header = document.querySelector("header.header");
    document.addEventListener("scroll", () => {
      if (header) header.classList.toggle("shadow-sm", document.documentElement.scrollTop > 0);
    });

    const stockInputs = document.querySelectorAll(".actual-stock-input");
    const statusBadge = document.getElementById("spillKitStatus");

    function updateSpillKitStatus() {
      const values = Array.from(stockInputs);
      const complete = values.every(input => input.value.trim() !== "" && /^\d+$/.test(input.value.trim()));
      if (!complete) {
        statusBadge.className = "badge rounded-pill bg-secondary-subtle text-secondary px-3 py-2";
        statusBadge.textContent = "Not yet checked";
        return;
      }
      const allGood = values.every(input => Number(input.value) >= Number(input.dataset.required));
      statusBadge.className = "badge rounded-pill px-3 py-2 " + (allGood ? "bg-success-subtle text-success" : "bg-danger-subtle text-danger");
      statusBadge.textContent = allGood ? "Good" : "No Good";
    }

    stockInputs.forEach(input => {
      input.addEventListener("input", () => {
        input.value = input.value.replace(/[^0-9]/g, "");
        updateSpillKitStatus();
      });
    });

    document.getElementById("spillKitChecklistForm").addEventListener("submit", event => {
      event.preventDefault();
      updateSpillKitStatus();
      if (!event.currentTarget.checkValidity()) {
        event.currentTarget.reportValidity();
        return;
      }
      alert("UI preview only. Checklist data has not been saved to the database.");
    });

    <?php include_once 'notification/session_timeout.php'; ?>
  </script>
</body>

</html>