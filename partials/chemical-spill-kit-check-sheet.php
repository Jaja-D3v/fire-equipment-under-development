<?php
$inspectorName = $_SESSION['EmployeeName'] ?? '';
$inspectionDate = date('Y-m-d');
?>

<div class="modal fade" id="spillKitChecklistModal" tabindex="-1" aria-labelledby="spillKitChecklistLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header px-4 py-3">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-3 bg-primary-subtle text-primary p-3">
            <i class="cil-clipboard fs-3"></i>
          </div>
          <div>
            <h5 class="modal-title fw-bold" id="spillKitChecklistLabel">Chemical Spill Kit Checklist</h5>
            <p class="text-body-secondary small mb-0">Record actual stocks for each kit item.</p>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="backend/controller/ChemicalSpillKitInspectionController.php" method="POST" id="spillKitChecklistForm" style="display: flex; flex: 1 1 auto; flex-direction: column; min-height: 0; overflow: hidden;">
        <div class="modal-body p-3 p-md-4" style="flex: 1 1 auto; min-height: 0; overflow-y: auto;">


          <!-- Basic Information -->
          <section class="card border mb-4 rounded-4">
            <div class="card-body p-3 p-md-4">
              <div class="d-flex align-items-center gap-3 mb-4">
                <span class="badge bg-primary rounded-3 p-2 fs-6">1</span>
                <div>
                  <h6 class="fw-bold mb-1">Basic Information</h6>
                  <small class="text-body-secondary">
                    Chemical spill kit details retrieved from the QR code.
                  </small>
                </div>
              </div>

              <div class="row g-3">

                <!-- Kit Code -->
                <div class="col-12 col-md-4">
                  <label class="form-label small fw-semibold">Kit Code</label>
                  <div class="form-control bg-body-tertiary">
                    <i class="cil-barcode me-2 text-primary"></i>
                    <span id="spillKitCodeDisplay">—</span>
                    <input type="hidden" id="spillKitCode" name="code">
                  </div>
                </div>

                <!-- Branch -->
                <div class="col-12 col-md-4">
                  <label class="form-label small fw-semibold">Branch</label>
                  <div class="form-control bg-body-tertiary">
                    <i class="cil-building me-2 text-primary"></i>
                    <span id="spillKitBranchDisplay">—</span>
                    <input type="hidden" id="spillKitBranch" name="branch">
                  </div>
                </div>

                <!-- Location -->
                <div class="col-12 col-md-4">
                  <label class="form-label small fw-semibold">Location</label>
                  <div class="form-control bg-body-tertiary text-truncate">
                    <i class="cil-location-pin me-2 text-primary"></i>
                    <span id="spillKitLocationDisplay">—</span>
                    <input type="hidden" id="spillKitLocation" name="location">
                  </div>
                </div>


                <!-- Inspection By -->
                <div class="col-12 col-md-6">
                  <label class="form-label small fw-semibold">Inspection By</label>
                  <div class="form-control bg-body-tertiary">
                    <i class="cil-user me-2 text-primary"></i>

                    <span id="spillKitInspectorDisplay"><?= htmlspecialchars($inspectorName, ENT_QUOTES, 'UTF-8') ?: '—' ?></span>

                    <input
                      type="hidden"
                      id="spillKitInspector"
                      name="inspected_by"
                      value="<?= htmlspecialchars($inspectorName, ENT_QUOTES, 'UTF-8') ?>">
                  </div>
                </div>

                <!-- Inspection Date -->
                <div class="col-12 col-md-6">
                  <label class="form-label small fw-semibold">Inspection Date</label>
                  <div class="form-control bg-body-tertiary">
                    <i class="cil-calendar me-2 text-primary"></i>

                    <span id="spillKitInspectionDateDisplay"><?= htmlspecialchars($inspectionDate, ENT_QUOTES, 'UTF-8') ?></span>

                    <input
                      type="hidden"
                      id="spillKitInspectionDate"
                      name="inspection_date"
                      value="<?= htmlspecialchars($inspectionDate, ENT_QUOTES, 'UTF-8') ?>">
                  </div>
                </div>


              </div>
            </div>
          </section>


          <!-- Stock Checklist -->
          <section class="card border rounded-4">
            <div class="card-body p-3 p-md-4">
              <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-3">
                <div class="d-flex align-items-center gap-3">
                  <span class="badge bg-primary rounded-3 p-2 fs-6">2</span>
                  <div>
                    <h6 class="fw-bold mb-1">Stock Checklist</h6>
                    <small class="text-body-secondary">
                      Verify the available stock for each item.
                    </small>
                  </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                  <span class="small text-body-secondary">Overall Status</span>
                  <span id="spillKitStatus" data-csk-overall-status
                    class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-2">
                    Not yet checked
                  </span>
                </div>
              </div>

              <div class="alert alert-primary bg-primary-subtle border-0 small mb-3">
                <i class="cil-info me-2"></i>
                Enter the actual quantity available for each item. Status is automatically determined by the required and actual stock.
              </div>
              <div id="spillKitRequiredStockWarning" class="alert alert-warning d-none small mb-3" role="alert"></div>

              <div class="row g-3" id="spillKitItems">Loading materials...</div>
            </div>
          </section>

        </div>

        <div class="modal-footer bg-body px-3 px-md-4 py-3">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal"><i class="cil-x me-1"></i> Cancel</button>
          <button type="submit" class="btn btn-primary px-4" id="spillKitChecklistSaveButton" data-csk-save disabled><i class="cil-save me-1"></i> Save Checklist</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="js/csk-materials.js"></script>
<script>
  (() => {
    const form = document.getElementById('spillKitChecklistForm');
    const modal = document.getElementById('spillKitChecklistModal');
    const container = document.getElementById('spillKitItems');
    window.cskMaterialForms.bind(form, container);
    modal.addEventListener('shown.bs.modal', () => {
      const branch = form.elements.namedItem('branch').value.trim();
      window.cskMaterialForms.load(form, container, branch, 'inspectCsk', true);
    });
  })();
</script>

<style>
  /* Remove dropdown arrows from all status fields */
  .stock-status {
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    background-image: none !important;
    cursor: default;
  }
</style>
