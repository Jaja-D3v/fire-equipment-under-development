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
            <button type="button" class="btn btn-outline-primary" data-coreui-toggle="modal" data-coreui-target="#spillKitChecklistModal">View Checklist</button>
          </div>
        </div>
      </div>
    </div>
  </div>

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
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>

        <form id="spillKitChecklistForm" style="display: flex; flex: 1 1 auto; flex-direction: column; min-height: 0; overflow: hidden;">
          <div class="modal-body p-3 p-md-4" style="flex: 1 1 auto; min-height: 0; overflow-y: auto;">
            <section class="card border mb-4 rounded-4">
              <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                  <span class="badge bg-primary rounded-3 p-2 fs-6">1</span>
                  <div>
                    <h6 class="fw-bold mb-1">Basic Information</h6>
                    <small class="text-body-secondary">Chemical spill kit details retrieved from the QR code.</small>
                  </div>
                </div>
                <div class="row g-3">
                  <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold">Kit Code</label>
                    <div class="form-control bg-body-tertiary"><i class="cil-barcode me-2 text-primary"></i>CSK-001</div>
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold">Branch</label>
                    <div class="form-control bg-body-tertiary"><i class="cil-building me-2 text-primary"></i>Laguna</div>
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold">Location</label>
                    <div class="form-control bg-body-tertiary text-truncate"><i class="cil-location-pin me-2 text-primary"></i>Hazardous Waste Area</div>
                  </div>
                </div>
              </div>
            </section>

            <section class="card border rounded-4">
              <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-3">
                  <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary rounded-3 p-2 fs-6">2</span>
                    <div>
                      <h6 class="fw-bold mb-1">Stock Checklist</h6>
                      <small class="text-body-secondary">Verify the available stock for each item.</small>
                    </div>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <span class="small text-body-secondary">Overall Status</span>
                    <span id="spillKitStatus" class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-2">Not yet checked</span>
                  </div>
                </div>

                <div class="alert alert-primary bg-primary-subtle border-0 small mb-3">
                  <i class="cil-info me-2"></i>Enter the actual quantity available for each item.
                </div>


                <div class="row g-3" id="spillKitItems">

                  <!-- Pads -->
                  <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 rounded-3">
                      <div class="card-body p-3">
                        <label for="actualStock_pads" class="form-label fw-semibold mb-3">
                          Pads
                        </label>
                        <div class="row g-2 align-items-end">
                          <div class="col-5">
                            <label class="form-label small text-body-secondary">Required Stock</label>
                            <div class="form-control text-center bg-body-tertiary">10</div>
                          </div>
                          <div class="col-7">
                            <label for="actualStock_pads" class="form-label small text-body-secondary">Actual Stock</label>
                            <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_pads" name="actual_stock[pads]" data-required="10" placeholder="Enter quantity" required>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Boom/Pillow -->
                  <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 rounded-3">
                      <div class="card-body p-3">
                        <label for="actualStock_boom" class="form-label fw-semibold mb-3">Boom/Pillow</label>
                        <div class="row g-2 align-items-end">
                          <div class="col-5">
                            <label class="form-label small text-body-secondary">Required Stock</label>
                            <div class="form-control text-center bg-body-tertiary">10</div>
                          </div>
                          <div class="col-7">
                            <label for="actualStock_boom" class="form-label small text-body-secondary">Actual Stock</label>
                            <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_boom" name="actual_stock[boom_pillow]" data-required="10" placeholder="Enter quantity" required>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Socks -->
                  <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 rounded-3">
                      <div class="card-body p-3">
                        <label for="actualStock_socks" class="form-label fw-semibold mb-3">Socks</label>
                        <div class="row g-2 align-items-end">
                          <div class="col-5">
                            <label class="form-label small text-body-secondary">Required Stock</label>
                            <div class="form-control text-center bg-body-tertiary">10</div>
                          </div>
                          <div class="col-7">
                            <label for="actualStock_socks" class="form-label small text-body-secondary">Actual Stock</label>
                            <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_socks" name="actual_stock[socks]" data-required="10" placeholder="Enter quantity" required>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Disposal Bag -->
                  <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 rounded-3">
                      <div class="card-body p-3">
                        <label for="actualStock_disposal_bag" class="form-label fw-semibold mb-3">Disposal Bag</label>
                        <div class="row g-2 align-items-end">
                          <div class="col-5">
                            <label class="form-label small text-body-secondary">Required Stock</label>
                            <div class="form-control text-center bg-body-tertiary">10</div>
                          </div>
                          <div class="col-7">
                            <label for="actualStock_disposal_bag" class="form-label small text-body-secondary">Actual Stock</label>
                            <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_disposal_bag" name="actual_stock[disposal_bag]" data-required="10" placeholder="Enter quantity" required>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Goggles -->
                  <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 rounded-3">
                      <div class="card-body p-3">
                        <label for="actualStock_goggles" class="form-label fw-semibold mb-3">Goggles</label>
                        <div class="row g-2 align-items-end">
                          <div class="col-5">
                            <label class="form-label small text-body-secondary">Required Stock</label>
                            <div class="form-control text-center bg-body-tertiary">10</div>
                          </div>
                          <div class="col-7">
                            <label for="actualStock_goggles" class="form-label small text-body-secondary">Actual Stock</label>
                            <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_goggles" name="actual_stock[goggles]" data-required="10" placeholder="Enter quantity" required>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Face Mask -->
                  <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 rounded-3">
                      <div class="card-body p-3">
                        <label for="actualStock_face_mask" class="form-label fw-semibold mb-3">Face Mask</label>
                        <div class="row g-2 align-items-end">
                          <div class="col-5">
                            <label class="form-label small text-body-secondary">Required Stock</label>
                            <div class="form-control text-center bg-body-tertiary">10</div>
                          </div>
                          <div class="col-7">
                            <label for="actualStock_face_mask" class="form-label small text-body-secondary">Actual Stock</label>
                            <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_face_mask" name="actual_stock[face_mask]" data-required="10" placeholder="Enter quantity" required>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Rubber Gloves -->
                  <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 rounded-3">
                      <div class="card-body p-3">
                        <label for="actualStock_rubber_gloves" class="form-label fw-semibold mb-3">Rubber Gloves</label>
                        <div class="row g-2 align-items-end">
                          <div class="col-5">
                            <label class="form-label small text-body-secondary">Required Stock</label>
                            <div class="form-control text-center bg-body-tertiary">10</div>
                          </div>
                          <div class="col-7">
                            <label for="actualStock_rubber_gloves" class="form-label small text-body-secondary">Actual Stock</label>
                            <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_rubber_gloves" name="actual_stock[rubber_gloves]" data-required="10" placeholder="Enter quantity" required>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Nitrile Gloves -->
                  <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 rounded-3">
                      <div class="card-body p-3">
                        <label for="actualStock_nitrile_gloves" class="form-label fw-semibold mb-3">Nitrile Gloves</label>
                        <div class="row g-2 align-items-end">
                          <div class="col-5">
                            <label class="form-label small text-body-secondary">Required Stock</label>
                            <div class="form-control text-center bg-body-tertiary">10</div>
                          </div>
                          <div class="col-7">
                            <label for="actualStock_nitrile_gloves" class="form-label small text-body-secondary">Actual Stock</label>
                            <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_nitrile_gloves" name="actual_stock[nitrile_gloves]" data-required="10" placeholder="Enter quantity" required>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>

              </div>
            </section>
          </div>

          <div class="modal-footer bg-body px-3 px-md-4 py-3">
            <button type="button" class="btn btn-light border" data-coreui-dismiss="modal"><i class="cil-x me-1"></i> Cancel</button>
            <button type="submit" class="btn btn-primary px-4"><i class="cil-save me-1"></i> Save Checklist</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php include 'partials/footer.php'; ?>
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