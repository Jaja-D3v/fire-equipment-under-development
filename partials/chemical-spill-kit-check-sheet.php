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

                <!-- this is for code -->
                <div class="col-12 col-md-4">
                  <label class="form-label small fw-semibold">Kit Code</label>
                  <div class="form-control bg-body-tertiary">
                    <i class="cil-barcode me-2 text-primary"></i>
                    <span id="spillKitCode">—</span>
                  </div>
                </div>

                <!-- this is for branch -->
                <div class="col-12 col-md-4">
                  <label class="form-label small fw-semibold">Branch</label>
                  <div class="form-control bg-body-tertiary">
                    <i class="cil-building me-2 text-primary"></i>
                    <span id="spillKitBranch">—</span>
                  </div>
                </div>

                <!-- this is for lcoation -->
                <div class="col-12 col-md-4">
                  <label class="form-label small fw-semibold">Location</label>
                  <div class="form-control bg-body-tertiary text-truncate">
                    <i class="cil-location-pin me-2 text-primary"></i>
                    <span id="spillKitLocation">—</span>
                  </div>
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
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal"><i class="cil-x me-1"></i> Cancel</button>
          <button type="submit" class="btn btn-primary px-4"><i class="cil-save me-1"></i> Save Checklist</button>
        </div>
      </form>
    </div>
  </div>
</div>