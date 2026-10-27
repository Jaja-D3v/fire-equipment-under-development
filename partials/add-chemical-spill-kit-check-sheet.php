<!-- ADD CHEMICAL SPILL KIT MODAL -->
<div class="modal fade" id="addChemicalSpillKitModal" tabindex="-1" aria-labelledby="addChemicalSpillKitLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable csk-add-dialog">
    <div class="modal-content csk-add-modal">

      <!-- HEADER -->
      <div class="modal-header csk-add-header">
        <div class="d-flex align-items-center gap-3">
          <div class="csk-add-header-icon"><i class="bi bi-plus-lg"></i></div>
          <div>
            <h5 class="modal-title" id="addChemicalSpillKitLabel">Add Chemical Spill Kit</h5>
            <p class="mb-0">Register a new kit and complete its inventory details.</p>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- FORM -->
      <form action="backend/controller/ChemicalSpillKitController.php" method="POST" id="addChemicalSpillKitForm" class="csk-add-form">
        <div class="modal-body csk-add-body">

          <!-- BASIC INFORMATION -->
          <section class="csk-add-section">
            <div class="csk-section-heading">
              <div class="csk-section-icon"><i class="bi bi-clipboard-check"></i></div>
              <div>
                <h6>Basic Information</h6>
                <p>Enter the identification and location of the kit.</p>
              </div>
            </div>

            <div class="row g-3">
              <div class="col-12 col-md-4">
                <label for="addCskCode" class="form-label">CSK Code <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="addCskCode" name="chemical_spill_kit_code" placeholder="e.g. CSK-003" required>
              </div>
              <div class="col-12 col-md-4">
                <label for="addCskBranch" class="form-label">Branch <span class="text-danger">*</span></label>
                <select class="form-select" id="addCskBranch" name="branch" required>
                  <option value="">Select branch</option>
                  <!-- Replace sample options with existing branch data -->
                  <option value="Laguna">Laguna</option>
                  <option value="Calamba">Calamba</option>
                  <option value="San Pablo">San Pablo</option>
                </select>
              </div>
              <div class="col-12 col-md-4">
                <label for="addCskLocation" class="form-label">Location <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="addCskLocation" name="location" placeholder="Enter kit location" required>
              </div>
            </div>
          </section>

          <!-- MATERIAL STOCKS -->
          <section class="csk-add-section">
            <div class="csk-section-heading">
              <div class="csk-section-icon"><i class="bi bi-box-seam"></i></div>
              <div>
                <h6>Kit Materials</h6>
                <p>Specify the stock, condition, and remarks for each material.</p>
              </div>
            </div>

            <div class="row g-3">

              <!-- PADS -->
              <div class="col-12 col-lg-6">
                <div class="csk-material-card">
                  <div class="csk-material-title"><span class="csk-material-icon"><i class="bi bi-layers"></i></span><span>Pads</span></div>
                  <div class="row g-2">
                    <div class="col-5">
                      <label for="addPads" class="form-label">Stock <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" id="addPads" name="pads" min="0" step="1" value="0" required>
                    </div>
                    <div class="col-7">
                      <label for="addPadsStatus" class="form-label">Status <span class="text-danger">*</span></label>
                      <select class="form-select" id="addPadsStatus" name="pads_status" required>
                        <option value="Good" selected>Good</option>
                        <option value="No Good">No Good</option>
                      </select>
                    </div>
                    <div class="col-12">
                      <label for="addPadsRemarks" class="form-label">Remarks <span class="csk-optional">Optional</span></label>
                      <input type="text" class="form-control" id="addPadsRemarks" name="pads_remarks" placeholder="Remarks for pads...">
                    </div>
                  </div>
                </div>
              </div>

              <!-- BOOM PILLOW -->
              <div class="col-12 col-lg-6">
                <div class="csk-material-card">
                  <div class="csk-material-title"><span class="csk-material-icon"><i class="bi bi-box"></i></span><span>Boom Pillow</span></div>
                  <div class="row g-2">
                    <div class="col-5">
                      <label for="addBoomPillow" class="form-label">Stock <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" id="addBoomPillow" name="boom_pillow" min="0" step="1" value="0" required>
                    </div>
                    <div class="col-7">
                      <label for="addBoomPillowStatus" class="form-label">Status <span class="text-danger">*</span></label>
                      <select class="form-select" id="addBoomPillowStatus" name="boom_pillow_status" required>
                        <option value="Good" selected>Good</option>
                        <option value="No Good">No Good</option>
                      </select>
                    </div>
                    <div class="col-12">
                      <label for="addBoomPillowRemarks" class="form-label">Remarks <span class="csk-optional">Optional</span></label>
                      <input type="text" class="form-control" id="addBoomPillowRemarks" name="boom_pillow_remarks" placeholder="Remarks for boom pillow...">
                    </div>
                  </div>
                </div>
              </div>

              <!-- SOCKS -->
              <div class="col-12 col-lg-6">
                <div class="csk-material-card">
                  <div class="csk-material-title"><span class="csk-material-icon"><i class="bi bi-shield-check"></i></span><span>Socks</span></div>
                  <div class="row g-2">
                    <div class="col-5">
                      <label for="addSocks" class="form-label">Stock <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" id="addSocks" name="socks" min="0" step="1" value="0" required>
                    </div>
                    <div class="col-7">
                      <label for="addSocksStatus" class="form-label">Status <span class="text-danger">*</span></label>
                      <select class="form-select" id="addSocksStatus" name="socks_status" required>
                        <option value="Good" selected>Good</option>
                        <option value="No Good">No Good</option>
                      </select>
                    </div>
                    <div class="col-12">
                      <label for="addSocksRemarks" class="form-label">Remarks <span class="csk-optional">Optional</span></label>
                      <input type="text" class="form-control" id="addSocksRemarks" name="socks_remarks" placeholder="Remarks for socks...">
                    </div>
                  </div>
                </div>
              </div>

              <!-- DISPOSAL BAG -->
              <div class="col-12 col-lg-6">
                <div class="csk-material-card">
                  <div class="csk-material-title"><span class="csk-material-icon"><i class="bi bi-bag"></i></span><span>Disposal Bag</span></div>
                  <div class="row g-2">
                    <div class="col-5">
                      <label for="addDisposalBag" class="form-label">Stock <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" id="addDisposalBag" name="disposal_bag" min="0" step="1" value="0" required>
                    </div>
                    <div class="col-7">
                      <label for="addDisposalBagStatus" class="form-label">Status <span class="text-danger">*</span></label>
                      <select class="form-select" id="addDisposalBagStatus" name="disposal_bag_status" required>
                        <option value="Good" selected>Good</option>
                        <option value="No Good">No Good</option>
                      </select>
                    </div>
                    <div class="col-12">
                      <label for="addDisposalBagRemarks" class="form-label">Remarks <span class="csk-optional">Optional</span></label>
                      <input type="text" class="form-control" id="addDisposalBagRemarks" name="disposal_bag_remarks" placeholder="Remarks for disposal bag...">
                    </div>
                  </div>
                </div>
              </div>

              <!-- GOGGLES -->
              <div class="col-12 col-lg-6">
                <div class="csk-material-card">
                  <div class="csk-material-title"><span class="csk-material-icon"><i class="bi bi-eyeglasses"></i></span><span>Goggles</span></div>
                  <div class="row g-2">
                    <div class="col-5">
                      <label for="addGoggles" class="form-label">Stock <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" id="addGoggles" name="goggles" min="0" step="1" value="0" required>
                    </div>
                    <div class="col-7">
                      <label for="addGogglesStatus" class="form-label">Status <span class="text-danger">*</span></label>
                      <select class="form-select" id="addGogglesStatus" name="goggles_status" required>
                        <option value="Good" selected>Good</option>
                        <option value="No Good">No Good</option>
                      </select>
                    </div>
                    <div class="col-12">
                      <label for="addGogglesRemarks" class="form-label">Remarks <span class="csk-optional">Optional</span></label>
                      <input type="text" class="form-control" id="addGogglesRemarks" name="goggles_remarks" placeholder="Remarks for goggles...">
                    </div>
                  </div>
                </div>
              </div>

              <!-- FACE MASK -->
              <div class="col-12 col-lg-6">
                <div class="csk-material-card">
                  <div class="csk-material-title"><span class="csk-material-icon"><i class="bi bi-shield"></i></span><span>Face Mask</span></div>
                  <div class="row g-2">
                    <div class="col-5">
                      <label for="addFaceMask" class="form-label">Stock <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" id="addFaceMask" name="face_mask" min="0" step="1" value="0" required>
                    </div>
                    <div class="col-7">
                      <label for="addFaceMaskStatus" class="form-label">Status <span class="text-danger">*</span></label>
                      <select class="form-select" id="addFaceMaskStatus" name="face_mask_status" required>
                        <option value="Good" selected>Good</option>
                        <option value="No Good">No Good</option>
                      </select>
                    </div>
                    <div class="col-12">
                      <label for="addFaceMaskRemarks" class="form-label">Remarks <span class="csk-optional">Optional</span></label>
                      <input type="text" class="form-control" id="addFaceMaskRemarks" name="face_mask_remarks" placeholder="Remarks for face mask...">
                    </div>
                  </div>
                </div>
              </div>

              <!-- RUBBER GLOVES -->
              <div class="col-12 col-lg-6">
                <div class="csk-material-card">
                  <div class="csk-material-title"><span class="csk-material-icon"><i class="bi bi-hand-thumbs-up"></i></span><span>Rubber Gloves</span></div>
                  <div class="row g-2">
                    <div class="col-5">
                      <label for="addRubberGloves" class="form-label">Stock <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" id="addRubberGloves" name="rubber_gloves" min="0" step="1" value="0" required>
                    </div>
                    <div class="col-7">
                      <label for="addRubberGlovesStatus" class="form-label">Status <span class="text-danger">*</span></label>
                      <select class="form-select" id="addRubberGlovesStatus" name="rubber_gloves_status" required>
                        <option value="Good" selected>Good</option>
                        <option value="No Good">No Good</option>
                      </select>
                    </div>
                    <div class="col-12">
                      <label for="addRubberGlovesRemarks" class="form-label">Remarks <span class="csk-optional">Optional</span></label>
                      <input type="text" class="form-control" id="addRubberGlovesRemarks" name="rubber_gloves_remarks" placeholder="Remarks for rubber gloves...">
                    </div>
                  </div>
                </div>
              </div>

              <!-- NITRILE GLOVES -->
              <div class="col-12 col-lg-6">
                <div class="csk-material-card">
                  <div class="csk-material-title"><span class="csk-material-icon"><i class="bi bi-hand-thumbs-up-fill"></i></span><span>Nitrile Gloves</span></div>
                  <div class="row g-2">
                    <div class="col-5">
                      <label for="addNitrileGloves" class="form-label">Stock <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" id="addNitrileGloves" name="nitrile_gloves" min="0" step="1" value="0" required>
                    </div>
                    <div class="col-7">
                      <label for="addNitrileGlovesStatus" class="form-label">Status <span class="text-danger">*</span></label>
                      <select class="form-select" id="addNitrileGlovesStatus" name="nitrile_gloves_status" required>
                        <option value="Good" selected>Good</option>
                        <option value="No Good">No Good</option>
                      </select>
                    </div>
                    <div class="col-12">
                      <label for="addNitrileGlovesRemarks" class="form-label">Remarks <span class="csk-optional">Optional</span></label>
                      <input type="text" class="form-control" id="addNitrileGlovesRemarks" name="nitrile_gloves_remarks" placeholder="Remarks for nitrile gloves...">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>

        </div>

        <!-- FOOTER -->
        <div class="modal-footer csk-add-footer">
          <button type="button" class="btn csk-btn-cancel" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn csk-btn-save"><i class="bi bi-check-lg me-1"></i> Save Kit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  /* MODAL DESIGN */
  #addChemicalSpillKitModal {
    --csk-blue: #0d6efd;
    --csk-text: #202938;
    --csk-muted: #778195;
    --csk-border: #e3e8f0;
    --csk-bg: #f5f7fb;
  }

  #addChemicalSpillKitModal .csk-add-dialog {
    width: calc(100% - 32px);
    max-width: 1000px;
    margin: 16px auto;
  }

  #addChemicalSpillKitModal .csk-add-modal {
    display: flex;
    flex-direction: column;
    max-height: calc(100dvh - 32px);
    overflow: hidden;
    color: var(--csk-text);
    background: var(--csk-bg);
    border: 1px solid rgba(255, 255, 255, .8);
    border-radius: 20px;
    box-shadow: 0 24px 70px rgba(24, 39, 75, .18);
  }

  #addChemicalSpillKitModal .csk-add-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid var(--csk-border);
  }

  #addChemicalSpillKitModal .csk-add-header-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 44px;
    height: 44px;
    color: var(--csk-blue);
    background: #eaf2ff;
    border-radius: 13px;
    font-size: 21px;
  }

  #addChemicalSpillKitModal .modal-title {
    font-size: 18px;
    font-weight: 700;
    letter-spacing: -.3px;
  }

  #addChemicalSpillKitModal .csk-add-header p {
    margin-top: 4px;
    color: var(--csk-muted);
    font-size: 12px;
  }

  #addChemicalSpillKitModal .csk-add-form {
    display: flex;
    flex: 1 1 auto;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
  }

  #addChemicalSpillKitModal .csk-add-body {
    flex: 1 1 auto;
    min-height: 0;
    overflow-x: hidden;
    overflow-y: auto;
    padding: 20px 24px;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #c8d0dc transparent;
  }

  #addChemicalSpillKitModal .csk-add-section {
    margin-bottom: 16px;
    padding: 20px;
    background: #fff;
    border: 1px solid var(--csk-border);
    border-radius: 16px;
  }

  #addChemicalSpillKitModal .csk-add-section:last-child {
    margin-bottom: 0;
  }

  #addChemicalSpillKitModal .csk-section-heading {
    display: flex;
    align-items: center;
    gap: 11px;
    margin-bottom: 18px;
  }

  #addChemicalSpillKitModal .csk-section-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    color: var(--csk-blue);
    background: #edf4ff;
    border-radius: 11px;
    font-size: 17px;
  }

  #addChemicalSpillKitModal .csk-section-heading h6 {
    margin: 0 0 4px;
    color: var(--csk-text);
    font-size: 14px;
    font-weight: 700;
  }

  #addChemicalSpillKitModal .csk-section-heading p {
    margin: 0;
    color: var(--csk-muted);
    font-size: 12px;
    line-height: 1.5;
  }

  #addChemicalSpillKitModal .form-label {
    display: block;
    margin-bottom: 6px;
    color: #465166;
    font-size: 11px;
    font-weight: 700;
  }

  #addChemicalSpillKitModal .form-control,
  #addChemicalSpillKitModal .form-select {
    width: 100%;
    min-height: 40px;
    padding: 9px 11px;
    color: var(--csk-text);
    background-color: #fbfcfe;
    border: 1px solid #dce3ed;
    border-radius: 9px;
    font-size: 12px;
    box-shadow: none;
    transition: border-color .18s, background-color .18s, box-shadow .18s;
  }

  #addChemicalSpillKitModal .form-control::placeholder {
    color: #a0a8b6;
  }

  #addChemicalSpillKitModal .form-control:focus,
  #addChemicalSpillKitModal .form-select:focus {
    background-color: #fff;
    border-color: var(--csk-blue);
    box-shadow: 0 0 0 3px rgba(13, 110, 253, .10);
  }

  #addChemicalSpillKitModal textarea.form-control {
    min-height: 82px;
    resize: vertical;
  }

  #addChemicalSpillKitModal .csk-optional {
    color: #929aaa;
    font-weight: 400;
  }

  /* MATERIAL CARDS */
  #addChemicalSpillKitModal .csk-material-card {
    height: 100%;
    padding: 14px;
    background: #fff;
    border: 1px solid #dfe6f0;
    border-radius: 13px;
    transition: border-color .18s, box-shadow .18s;
  }

  #addChemicalSpillKitModal .csk-material-card:focus-within {
    border-color: #b7d1ff;
    box-shadow: 0 3px 12px rgba(13, 110, 253, .06);
  }

  #addChemicalSpillKitModal .csk-material-title {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 13px;
    color: #263449;
    font-size: 13px;
    font-weight: 700;
  }

  #addChemicalSpillKitModal .csk-material-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    color: var(--csk-blue);
    background: #edf4ff;
    border-radius: 9px;
    font-size: 16px;
  }

  /* FOOTER */
  #addChemicalSpillKitModal .csk-add-footer {
    display: flex;
    flex-shrink: 0;
    justify-content: flex-end;
    gap: 9px;
    padding: 14px 24px;
    background: #fff;
    border-top: 1px solid var(--csk-border);
  }

  #addChemicalSpillKitModal .csk-add-footer .btn {
    min-height: 40px;
    padding: 9px 18px;
    border-radius: 9px;
    font-size: 12px;
    font-weight: 700;
    transition: background-color .18s, transform .18s;
  }

  #addChemicalSpillKitModal .csk-btn-cancel {
    color: #4b5565;
    background: #fff;
    border: 1px solid #dce3ed;
  }

  #addChemicalSpillKitModal .csk-btn-cancel:hover {
    background: #f3f6fa;
  }

  #addChemicalSpillKitModal .csk-btn-save {
    color: #fff;
    background: var(--csk-blue);
    border: 1px solid var(--csk-blue);
  }

  #addChemicalSpillKitModal .csk-btn-save:hover {
    color: #fff;
    background: #0b5ed7;
  }

  #addChemicalSpillKitModal .csk-add-footer .btn:active {
    transform: scale(.98);
  }

  /* TABLET */
  @media (max-width: 767.98px) {
    #addChemicalSpillKitModal .csk-add-dialog {
      max-width: 620px;
    }

    #addChemicalSpillKitModal .csk-add-body {
      padding: 16px;
    }

    #addChemicalSpillKitModal .csk-add-section {
      padding: 16px;
    }
  }

  /* MOBILE */
  @media (max-width: 575.98px) {
    #addChemicalSpillKitModal .csk-add-dialog {
      width: calc(100% - 16px);
      margin: 8px auto;
    }

    #addChemicalSpillKitModal .csk-add-modal {
      max-height: calc(100dvh - 16px);
      border-radius: 16px;
    }

    #addChemicalSpillKitModal .csk-add-header {
      padding: 14px;
    }

    #addChemicalSpillKitModal .csk-add-header-icon {
      width: 38px;
      height: 38px;
      font-size: 18px;
    }

    #addChemicalSpillKitModal .modal-title {
      font-size: 15px;
    }

    #addChemicalSpillKitModal .csk-add-header p {
      max-width: 240px;
      font-size: 11px;
    }

    #addChemicalSpillKitModal .csk-add-body {
      padding: 12px;
    }

    #addChemicalSpillKitModal .csk-add-section {
      margin-bottom: 12px;
      padding: 13px;
      border-radius: 13px;
    }

    #addChemicalSpillKitModal .csk-section-heading {
      margin-bottom: 14px;
    }

    #addChemicalSpillKitModal .csk-material-card {
      padding: 12px;
    }

    #addChemicalSpillKitModal .csk-add-footer {
      padding: 11px 12px;
      gap: 8px;
    }

    #addChemicalSpillKitModal .csk-add-footer .btn {
      flex: 1;
      padding: 9px 10px;
    }
  }

  /* SHORT / LANDSCAPE SCREENS */
  @media (max-height: 600px) {
    #addChemicalSpillKitModal .csk-add-dialog {
      margin-top: 8px;
      margin-bottom: 8px;
    }

    #addChemicalSpillKitModal .csk-add-modal {
      max-height: calc(100dvh - 16px);
    }

    #addChemicalSpillKitModal .csk-add-header {
      padding-top: 12px;
      padding-bottom: 12px;
    }

    #addChemicalSpillKitModal .csk-add-body {
      padding-top: 12px;
      padding-bottom: 12px;
    }
  }
</style>