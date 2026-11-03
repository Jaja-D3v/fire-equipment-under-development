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
              <div id="spillKitRequiredStockWarning" class="alert alert-warning d-none small mb-3" role="alert"></div>

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
                          <div class="form-control text-center bg-body-tertiary" data-required-stock-for="pads">Loading...</div>
                        </div>
                        <div class="col-7">
                          <label for="actualStock_pads" class="form-label small text-body-secondary">Actual Stock</label>
                          <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_pads" name="pads" data-item="pads" placeholder="Enter quantity" required>
                        </div>
                      </div>
                      <label for="pads_status" class="form-label small text-body-secondary">Status</label>
                      <select class="form-select mb-3 stock-status" id="pads_status" name="pads_status" data-item="pads" required >
                        <option value="">Not yet checked</option>
                        <option value="Good">Good</option>
                        <option value="No Good">No Good</option>
                        <option value="Unavailable">Unavailable</option>
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
                          <div class="form-control text-center bg-body-tertiary" data-required-stock-for="boom_pillow">Loading...</div>
                        </div>
                        <div class="col-7">
                          <label for="actualStock_boom" class="form-label small text-body-secondary">Actual Stock</label>
                          <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_boom" name="boom_pillow" data-item="boom_pillow" placeholder="Enter quantity" required>
                        </div>
                      </div>
                      <label for="boom_pillow_status" class="form-label small text-body-secondary">Status</label>
                      <select class="form-select mb-3 stock-status" id="boom_pillow_status" name="boom_pillow_status" data-item="boom_pillow" required >
                        <option value="">Not yet checked</option>
                        <option value="Good">Good</option>
                        <option value="No Good">No Good</option>
                        <option value="Unavailable">Unavailable</option>
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
                          <div class="form-control text-center bg-body-tertiary" data-required-stock-for="socks">Loading...</div>
                        </div>
                        <div class="col-7">
                          <label for="actualStock_socks" class="form-label small text-body-secondary">Actual Stock</label>
                          <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_socks" name="socks" data-item="socks" placeholder="Enter quantity" required>
                        </div>
                      </div>
                      <label for="socks_status" class="form-label small text-body-secondary">Status</label>
                      <select class="form-select mb-3 stock-status" id="socks_status" name="socks_status" data-item="socks" required >
                        <option value="">Not yet checked</option>
                        <option value="Good">Good</option>
                        <option value="No Good">No Good</option>
                        <option value="Unavailable">Unavailable</option>
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
                          <div class="form-control text-center bg-body-tertiary" data-required-stock-for="disposal_bag">Loading...</div>
                        </div>
                        <div class="col-7">
                          <label for="actualStock_disposal_bag" class="form-label small text-body-secondary">Actual Stock</label>
                          <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_disposal_bag" name="disposal_bag" data-item="disposal_bag" placeholder="Enter quantity" required>
                        </div>
                      </div>
                      <label for="disposal_bag_status" class="form-label small text-body-secondary">Status</label>
                      <select class="form-select mb-3 stock-status" id="disposal_bag_status" name="disposal_bag_status" data-item="disposal_bag" required >
                        <option value="">Not yet checked</option>
                        <option value="Good">Good</option>
                        <option value="No Good">No Good</option>
                        <option value="Unavailable">Unavailable</option>
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
                          <div class="form-control text-center bg-body-tertiary" data-required-stock-for="goggles">Loading...</div>
                        </div>
                        <div class="col-7">
                          <label for="actualStock_goggles" class="form-label small text-body-secondary">Actual Stock</label>
                          <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_goggles" name="goggles" data-item="goggles" placeholder="Enter quantity" required>
                        </div>
                      </div>
                      <label for="goggles_status" class="form-label small text-body-secondary">Status</label>
                      <select class="form-select mb-3 stock-status" id="goggles_status" name="goggles_status" data-item="goggles" required >
                        <option value="">Not yet checked</option>
                        <option value="Good">Good</option>
                        <option value="No Good">No Good</option>
                        <option value="Unavailable">Unavailable</option>
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
                          <div class="form-control text-center bg-body-tertiary" data-required-stock-for="face_mask">Loading...</div>
                        </div>
                        <div class="col-7">
                          <label for="actualStock_face_mask" class="form-label small text-body-secondary">Actual Stock</label>
                          <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_face_mask" name="face_mask" data-item="face_mask" placeholder="Enter quantity" required>
                        </div>
                      </div>
                      <label for="face_mask_status" class="form-label small text-body-secondary">Status</label>
                      <select class="form-select mb-3 stock-status" id="face_mask_status" name="face_mask_status" data-item="face_mask" required>
                        <option value="">Not yet checked</option>
                        <option value="Good">Good</option>
                        <option value="No Good">No Good</option>
                        <option value="Unavailable">Unavailable</option>
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
                          <div class="form-control text-center bg-body-tertiary" data-required-stock-for="rubber_gloves">Loading...</div>
                        </div>
                        <div class="col-7">
                          <label for="actualStock_rubber_gloves" class="form-label small text-body-secondary">Actual Stock</label>
                          <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_rubber_gloves" name="rubber_gloves" data-item="rubber_gloves" placeholder="Enter quantity" required>
                        </div>
                      </div>
                      <label for="rubber_gloves_status" class="form-label small text-body-secondary">Status</label>
                      <select class="form-select mb-3 stock-status" id="rubber_gloves_status" name="rubber_gloves_status" data-item="rubber_gloves" required>
                        <option value="">Not yet checked</option>
                        <option value="Good">Good</option>
                        <option value="No Good">No Good</option>
                        <option value="Unavailable">Unavailable</option>
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
                          <div class="form-control text-center bg-body-tertiary" data-required-stock-for="nitrile_gloves">Loading...</div>
                        </div>
                        <div class="col-7">
                          <label for="actualStock_nitrile_gloves" class="form-label small text-body-secondary">Actual Stock</label>
                          <input type="number" min="0" step="1" class="form-control actual-stock-input" id="actualStock_nitrile_gloves" name="nitrile_gloves" data-item="nitrile_gloves" placeholder="Enter quantity" required>
                        </div>
                      </div>
                      <label for="nitrile_gloves_status" class="form-label small text-body-secondary">Status</label>
                      <select class="form-select mb-3 stock-status" id="nitrile_gloves_status" name="nitrile_gloves_status" data-item="nitrile_gloves" required>
                        <option value="">Not yet checked</option>
                        <option value="Good">Good</option>
                        <option value="No Good">No Good</option>
                        <option value="Unavailable">Unavailable</option>
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
          <button type="submit" class="btn btn-primary px-4" id="spillKitChecklistSaveButton" disabled><i class="cil-save me-1"></i> Save Checklist</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  (() => {
    const form = document.getElementById('spillKitChecklistForm');
    const modal = document.getElementById('spillKitChecklistModal');
    if (!form || !modal || form.dataset.requiredStockBound === 'true') return;
    form.dataset.requiredStockBound = 'true';

    const fields = ['pads', 'boom_pillow', 'socks', 'disposal_bag', 'goggles', 'face_mask', 'rubber_gloves', 'nitrile_gloves'];
    const labels = {
      pads: 'Pads',
      boom_pillow: 'Boom/Pillow',
      socks: 'Socks',
      disposal_bag: 'Disposal Bag',
      goggles: 'Goggles',
      face_mask: 'Face Mask',
      rubber_gloves: 'Rubber Gloves',
      nitrile_gloves: 'Nitrile Gloves'
    };
    let requiredStock = Object.fromEntries(fields.map((field) => [field, null]));
    let requiredStockState = 'idle';
    let requestVersion = 0;

    const renderStatuses = () => {
      const statuses = [];
      const missingRequirements = fields.filter((field) => !Number.isFinite(requiredStock[field]) || requiredStock[field] < 0);
      const requirementsReady = requiredStockState === 'ready' && missingRequirements.length === 0;

      fields.forEach((field) => {
        const input = form.elements.namedItem(field);
        const statusSelect = form.elements.namedItem(`${field}_status`);
        const requiredDisplay = form.querySelector(`[data-required-stock-for="${field}"]`);
        const badge = document.getElementById(`${field}StatusBadge`);
        const requiredAmount = requiredStock[field];

        if (requiredDisplay) {
          if (requiredStockState === 'loading' || requiredStockState === 'idle') requiredDisplay.textContent = 'Loading...';
          else if (requiredStockState === 'error') requiredDisplay.textContent = 'Unavailable';
          else if (requiredAmount === null || requiredAmount === undefined) requiredDisplay.textContent = 'Not configured';
          else requiredDisplay.textContent = String(requiredAmount);
        }

        const raw = input.value.trim();
        const quantity = raw === '' ? Number.NaN : Number(raw);
        const currentIsValid = raw !== '' && input.validity.valid && Number.isFinite(quantity) && quantity >= 0;
        const status = requirementsReady && currentIsValid
          ? (quantity >= requiredAmount ? 'Good' : 'No Good')
          : 'Unavailable';
        statusSelect.value = status;
        statuses.push(status);

        if (badge) {
          badge.textContent = status;
          badge.className = status === 'Good'
            ? 'badge bg-success-subtle text-success'
            : (status === 'No Good' ? 'badge bg-danger-subtle text-danger' : 'badge bg-warning-subtle text-warning-emphasis');
        }
      });

      const warning = document.getElementById('spillKitRequiredStockWarning');
      let warningMessage = '';
      if (requiredStockState === 'loading' || requiredStockState === 'idle') {
        warningMessage = 'Loading required stock for the scanned kit branch.';
      } else if (requiredStockState === 'error') {
        warningMessage = 'Required stock could not be retrieved for this branch. Saving is disabled.';
      } else if (missingRequirements.length > 0) {
        warningMessage = `Required stock is missing or invalid for: ${missingRequirements.map((field) => labels[field]).join(', ')}. Saving is disabled.`;
      } else if (statuses.includes('Unavailable')) {
        warningMessage = 'Enter a valid current stock quantity for every material.';
      }
      if (warning) {
        warning.textContent = warningMessage;
        warning.classList.toggle('d-none', warningMessage === '');
      }

      const overallStatus = document.getElementById('spillKitStatus');
      const allGood = requirementsReady && statuses.length === fields.length && statuses.every((status) => status === 'Good');
      const hasUnavailable = !requirementsReady || statuses.includes('Unavailable');
      if (overallStatus) {
        overallStatus.textContent = requiredStockState === 'idle' || requiredStockState === 'loading'
          ? 'Checking...'
          : (allGood ? 'Good' : (hasUnavailable ? 'Unavailable' : 'No Good'));
        overallStatus.className = `badge rounded-pill px-3 py-2 ${allGood
          ? 'bg-success-subtle text-success'
          : (hasUnavailable ? 'bg-warning-subtle text-warning-emphasis' : 'bg-danger-subtle text-danger')}`;
      }

      const saveButton = document.getElementById('spillKitChecklistSaveButton');
      if (saveButton) saveButton.disabled = !requirementsReady || !form.checkValidity();
    };

    const loadRequiredStock = async () => {
      const branch = (form.elements.namedItem('branch')?.value || '').trim();
      const currentRequest = ++requestVersion;
      requiredStock = Object.fromEntries(fields.map((field) => [field, null]));
      requiredStockState = 'loading';
      renderStatuses();

      try {
        if (!branch || branch.toLowerCase() === 'all') throw new Error('The scanned kit branch is unavailable.');
        const params = new URLSearchParams({ action: 'getRequiredStock', branch });
        const response = await fetch(`backend/controller/ChemicalSpillKitRequiredStockController.php?${params.toString()}`, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const result = await response.json();
        if (currentRequest !== requestVersion) return;
        if (
          result.success !== true ||
          !result.required_stock ||
          typeof result.required_stock !== 'object' ||
          String(result.branch || '').toLowerCase() !== branch.toLowerCase()
        ) throw new Error(result.message || 'Invalid required-stock response.');

        const nextRequiredStock = {};
        fields.forEach((field) => {
          const value = result.required_stock[field];
          if (value === null || value === undefined || String(value).trim() === '') {
            nextRequiredStock[field] = null;
            return;
          }
          const amount = Number(value);
          if (!Number.isFinite(amount) || amount < 0) throw new Error(`Invalid required stock for ${field}.`);
          nextRequiredStock[field] = amount;
        });
        requiredStock = nextRequiredStock;
        requiredStockState = 'ready';
      } catch (error) {
        if (currentRequest !== requestVersion) return;
        requiredStockState = 'error';
        console.error('Chemical Spill Kit required-stock request failed:', error);
      }
      renderStatuses();
    };

    form.addEventListener('input', (event) => {
      if (fields.includes(event.target.name)) renderStatuses();
    });
    form.addEventListener('submit', (event) => {
      const requirementsReady = requiredStockState === 'ready' && fields.every((field) => Number.isFinite(requiredStock[field]) && requiredStock[field] >= 0);
      if (!requirementsReady) {
        event.preventDefault();
        const warning = document.getElementById('spillKitRequiredStockWarning');
        if (warning) {
          warning.textContent = 'Required stock is unavailable or incomplete for the scanned kit branch. No changes were submitted.';
          warning.classList.remove('d-none');
        }
      }
    });
    modal.addEventListener('shown.bs.modal', loadRequiredStock);
    renderStatuses();
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
