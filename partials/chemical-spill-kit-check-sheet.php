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
                  <span id="spillKitStatus"
                    class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-2">
                    Not yet checked
                  </span>
                </div>
              </div>

              <div class="alert alert-primary bg-primary-subtle border-0 small mb-3">
                <i class="cil-info me-2"></i>
                Enter the actual quantity available for each item. Status is automatically determined by the required and actual stock.
              </div>

              <div class="row g-3" id="spillKitItems">

                <!-- Pads -->
                <div class="col-12 col-md-6 col-lg-4">
                  <div class="card h-100 rounded-3">
                    <div class="card-body p-3">
                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <label for="actualStock_pads" class="form-label fw-semibold mb-0">Pads</label>
                        <span id="padsStatusBadge" class="badge bg-secondary-subtle text-secondary">Not yet checked</span>
                      </div>
                      <div class="row g-2 mb-3">
                        <div class="col-5">
                          <label class="form-label small text-body-secondary">Required Stock</label>
                          <div class="form-control text-center bg-body-tertiary">10</div>
                        </div>
                        <div class="col-7">
                          <label for="actualStock_pads" class="form-label small text-body-secondary">Actual Stock</label>
                          <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_pads" name="pads" data-required="10" data-item="pads" placeholder="Enter quantity" required>
                        </div>
                      </div>
                      <label for="pads_status" class="form-label small text-body-secondary">Status</label>
                      <select class="form-select mb-3 stock-status" id="pads_status" name="pads_status" data-item="pads" required >
                        <option value="">Not yet checked</option>
                        <option value="Good">Good</option>
                        <option value="No Good">No Good</option>
                      </select>
                      <label for="pads_remarks" class="form-label small text-body-secondary">Remarks</label>
                      <textarea class="form-control" id="pads_remarks" name="pads_remarks" rows="2" placeholder="Enter remarks"></textarea>
                    </div>
                  </div>
                </div>

                <!-- Boom/Pillow -->
                <div class="col-12 col-md-6 col-lg-4">
                  <div class="card h-100 rounded-3">
                    <div class="card-body p-3">
                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <label for="actualStock_boom" class="form-label fw-semibold mb-0">Boom/Pillow</label>
                        <span id="boom_pillowStatusBadge" class="badge bg-secondary-subtle text-secondary">Not yet checked</span>
                      </div>
                      <div class="row g-2 mb-3">
                        <div class="col-5">
                          <label class="form-label small text-body-secondary">Required Stock</label>
                          <div class="form-control text-center bg-body-tertiary">10</div>
                        </div>
                        <div class="col-7">
                          <label for="actualStock_boom" class="form-label small text-body-secondary">Actual Stock</label>
                          <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_boom" name="boom_pillow" data-required="10" data-item="boom_pillow" placeholder="Enter quantity" required>
                        </div>
                      </div>
                      <label for="boom_pillow_status" class="form-label small text-body-secondary">Status</label>
                      <select class="form-select mb-3 stock-status" id="boom_pillow_status" name="boom_pillow_status" data-item="boom_pillow" required >
                        <option value="">Not yet checked</option>
                        <option value="Good">Good</option>
                        <option value="No Good">No Good</option>
                      </select>
                      <label for="boom_pillow_remarks" class="form-label small text-body-secondary">Remarks</label>
                      <textarea class="form-control" id="boom_pillow_remarks" name="boom_pillow_remarks" rows="2" placeholder="Enter remarks"></textarea>
                    </div>
                  </div>
                </div>

                <!-- Socks -->
                <div class="col-12 col-md-6 col-lg-4">
                  <div class="card h-100 rounded-3">
                    <div class="card-body p-3">
                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <label for="actualStock_socks" class="form-label fw-semibold mb-0">Socks</label>
                        <span id="socksStatusBadge" class="badge bg-secondary-subtle text-secondary">Not yet checked</span>
                      </div>
                      <div class="row g-2 mb-3">
                        <div class="col-5">
                          <label class="form-label small text-body-secondary">Required Stock</label>
                          <div class="form-control text-center bg-body-tertiary">10</div>
                        </div>
                        <div class="col-7">
                          <label for="actualStock_socks" class="form-label small text-body-secondary">Actual Stock</label>
                          <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_socks" name="socks" data-required="10" data-item="socks" placeholder="Enter quantity" required>
                        </div>
                      </div>
                      <label for="socks_status" class="form-label small text-body-secondary">Status</label>
                      <select class="form-select mb-3 stock-status" id="socks_status" name="socks_status" data-item="socks" required >
                        <option value="">Not yet checked</option>
                        <option value="Good">Good</option>
                        <option value="No Good">No Good</option>
                      </select>
                      <label for="socks_remarks" class="form-label small text-body-secondary">Remarks</label>
                      <textarea class="form-control" id="socks_remarks" name="socks_remarks" rows="2" placeholder="Enter remarks"></textarea>
                    </div>
                  </div>
                </div>

                <!-- Disposal Bag -->
                <div class="col-12 col-md-6 col-lg-4">
                  <div class="card h-100 rounded-3">
                    <div class="card-body p-3">
                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <label for="actualStock_disposal_bag" class="form-label fw-semibold mb-0">Disposal Bag</label>
                        <span id="disposal_bagStatusBadge" class="badge bg-secondary-subtle text-secondary">Not yet checked</span>
                      </div>
                      <div class="row g-2 mb-3">
                        <div class="col-5">
                          <label class="form-label small text-body-secondary">Required Stock</label>
                          <div class="form-control text-center bg-body-tertiary">10</div>
                        </div>
                        <div class="col-7">
                          <label for="actualStock_disposal_bag" class="form-label small text-body-secondary">Actual Stock</label>
                          <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_disposal_bag" name="disposal_bag" data-required="10" data-item="disposal_bag" placeholder="Enter quantity" required>
                        </div>
                      </div>
                      <label for="disposal_bag_status" class="form-label small text-body-secondary">Status</label>
                      <select class="form-select mb-3 stock-status" id="disposal_bag_status" name="disposal_bag_status" data-item="disposal_bag" required >
                        <option value="">Not yet checked</option>
                        <option value="Good">Good</option>
                        <option value="No Good">No Good</option>
                      </select>
                      <label for="disposal_bag_remarks" class="form-label small text-body-secondary">Remarks</label>
                      <textarea class="form-control" id="disposal_bag_remarks" name="disposal_bag_remarks" rows="2" placeholder="Enter remarks"></textarea>
                    </div>
                  </div>
                </div>

                <!-- Goggles -->
                <div class="col-12 col-md-6 col-lg-4">
                  <div class="card h-100 rounded-3">
                    <div class="card-body p-3">
                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <label for="actualStock_goggles" class="form-label fw-semibold mb-0">Goggles</label>
                        <span id="gogglesStatusBadge" class="badge bg-secondary-subtle text-secondary">Not yet checked</span>
                      </div>
                      <div class="row g-2 mb-3">
                        <div class="col-5">
                          <label class="form-label small text-body-secondary">Required Stock</label>
                          <div class="form-control text-center bg-body-tertiary">10</div>
                        </div>
                        <div class="col-7">
                          <label for="actualStock_goggles" class="form-label small text-body-secondary">Actual Stock</label>
                          <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_goggles" name="goggles" data-required="10" data-item="goggles" placeholder="Enter quantity" required>
                        </div>
                      </div>
                      <label for="goggles_status" class="form-label small text-body-secondary">Status</label>
                      <select class="form-select mb-3 stock-status" id="goggles_status" name="goggles_status" data-item="goggles" required >
                        <option value="">Not yet checked</option>
                        <option value="Good">Good</option>
                        <option value="No Good">No Good</option>
                      </select>
                      <label for="goggles_remarks" class="form-label small text-body-secondary">Remarks</label>
                      <textarea class="form-control" id="goggles_remarks" name="goggles_remarks" rows="2" placeholder="Enter remarks"></textarea>
                    </div>
                  </div>
                </div>

                <!-- Face Mask -->
                <div class="col-12 col-md-6 col-lg-4">
                  <div class="card h-100 rounded-3">
                    <div class="card-body p-3">
                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <label for="actualStock_face_mask" class="form-label fw-semibold mb-0">Face Mask</label>
                        <span id="face_maskStatusBadge" class="badge bg-secondary-subtle text-secondary">Not yet checked</span>
                      </div>
                      <div class="row g-2 mb-3">
                        <div class="col-5">
                          <label class="form-label small text-body-secondary">Required Stock</label>
                          <div class="form-control text-center bg-body-tertiary">10</div>
                        </div>
                        <div class="col-7">
                          <label for="actualStock_face_mask" class="form-label small text-body-secondary">Actual Stock</label>
                          <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_face_mask" name="face_mask" data-required="10" data-item="face_mask" placeholder="Enter quantity" required>
                        </div>
                      </div>
                      <label for="face_mask_status" class="form-label small text-body-secondary">Status</label>
                      <select class="form-select mb-3 stock-status" id="face_mask_status" name="face_mask_status" data-item="face_mask" required>
                        <option value="">Not yet checked</option>
                        <option value="Good">Good</option>
                        <option value="No Good">No Good</option>
                      </select>
                      <label for="face_mask_remarks" class="form-label small text-body-secondary">Remarks</label>
                      <textarea class="form-control" id="face_mask_remarks" name="face_mask_remarks" rows="2" placeholder="Enter remarks"></textarea>
                    </div>
                  </div>
                </div>

                <!-- Rubber Gloves -->
                <div class="col-12 col-md-6 col-lg-4">
                  <div class="card h-100 rounded-3">
                    <div class="card-body p-3">
                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <label for="actualStock_rubber_gloves" class="form-label fw-semibold mb-0">Rubber Gloves</label>
                        <span id="rubber_glovesStatusBadge" class="badge bg-secondary-subtle text-secondary">Not yet checked</span>
                      </div>
                      <div class="row g-2 mb-3">
                        <div class="col-5">
                          <label class="form-label small text-body-secondary">Required Stock</label>
                          <div class="form-control text-center bg-body-tertiary">10</div>
                        </div>
                        <div class="col-7">
                          <label for="actualStock_rubber_gloves" class="form-label small text-body-secondary">Actual Stock</label>
                          <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_rubber_gloves" name="rubber_gloves" data-required="10" data-item="rubber_gloves" placeholder="Enter quantity" required>
                        </div>
                      </div>
                      <label for="rubber_gloves_status" class="form-label small text-body-secondary">Status</label>
                      <select class="form-select mb-3 stock-status" id="rubber_gloves_status" name="rubber_gloves_status" data-item="rubber_gloves" required>
                        <option value="">Not yet checked</option>
                        <option value="Good">Good</option>
                        <option value="No Good">No Good</option>
                      </select>
                      <label for="rubber_gloves_remarks" class="form-label small text-body-secondary">Remarks</label>
                      <textarea class="form-control" id="rubber_gloves_remarks" name="rubber_gloves_remarks" rows="2" placeholder="Enter remarks"></textarea>
                    </div>
                  </div>
                </div>

                <!-- Nitrile Gloves -->
                <div class="col-12 col-md-6 col-lg-4">
                  <div class="card h-100 rounded-3">
                    <div class="card-body p-3">
                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <label for="actualStock_nitrile_gloves" class="form-label fw-semibold mb-0">Nitrile Gloves</label>
                        <span id="nitrile_glovesStatusBadge" class="badge bg-secondary-subtle text-secondary">Not yet checked</span>
                      </div>
                      <div class="row g-2 mb-3">
                        <div class="col-5">
                          <label class="form-label small text-body-secondary">Required Stock</label>
                          <div class="form-control text-center bg-body-tertiary">10</div>
                        </div>
                        <div class="col-7">
                          <label for="actualStock_nitrile_gloves" class="form-label small text-body-secondary">Actual Stock</label>
                          <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_nitrile_gloves" name="nitrile_gloves" data-required="10" data-item="nitrile_gloves" placeholder="Enter quantity" required>
                        </div>
                      </div>
                      <label for="nitrile_gloves_status" class="form-label small text-body-secondary">Status</label>
                      <select class="form-select mb-3 stock-status" id="nitrile_gloves_status" name="nitrile_gloves_status" data-item="nitrile_gloves" required>
                        <option value="">Not yet checked</option>
                        <option value="Good">Good</option>
                        <option value="No Good">No Good</option>
                      </select>
                      <label for="nitrile_gloves_remarks" class="form-label small text-body-secondary">Remarks</label>
                      <textarea class="form-control" id="nitrile_gloves_remarks" name="nitrile_gloves_remarks" rows="2" placeholder="Enter remarks"></textarea>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </section>

        </div>

        <div class="modal-footer bg-body px-3 px-md-4 py-3">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal"><i class="cil-x me-1"></i> Cancel</button>
          <button type="submit" class="btn btn-primary px-4"><i class="cil-save me-1"></i> Save Checklist</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // for chemical checklist
  function updateSpillKitStockStatuses() {
    const stockInputs = document.querySelectorAll(
      "#spillKitChecklistForm .actual-stock-input",
    );

    let allChecked = true;
    let allGood = true;

    stockInputs.forEach((input) => {
      const item = input.dataset.item;
      const required = Number(input.dataset.required);
      const statusSelect = document.querySelector(
        `#spillKitChecklistForm [name="${item}_status"]`,
      );
      const badge = document.getElementById(`${item}StatusBadge`);

      if (!input.value.trim()) {
        allChecked = false;
        allGood = false;

        if (statusSelect) statusSelect.value = "";

        if (badge) {
          badge.textContent = "Not yet checked";
          badge.className = "badge bg-secondary-subtle text-secondary";
        }

        return;
      }

      const actual = Number(input.value);
      const status = actual >= required ? "Good" : "No Good";

      if (statusSelect) statusSelect.value = status;

      if (badge) {
        badge.textContent = status;
        badge.className =
          status === "Good" ?
          "badge bg-success-subtle text-success" :
          "badge bg-danger-subtle text-danger";
      }

      if (status !== "Good") allGood = false;
    });

    const overallStatus = document.getElementById("spillKitStatus");

    if (overallStatus) {
      if (!allChecked) {
        overallStatus.textContent = "Not yet checked";
        overallStatus.className =
          "badge rounded-pill bg-secondary-subtle text-secondary px-3 py-2";
      } else if (allGood) {
        overallStatus.textContent = "Good";
        overallStatus.className =
          "badge rounded-pill bg-success-subtle text-success px-3 py-2";
      } else {
        overallStatus.textContent = "No Good";
        overallStatus.className =
          "badge rounded-pill bg-danger-subtle text-danger px-3 py-2";
      }
    }
  }

  document
    .querySelectorAll("#spillKitChecklistForm .actual-stock-input")
    .forEach((input) => {
      input.addEventListener("input", updateSpillKitStockStatuses);
    });
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