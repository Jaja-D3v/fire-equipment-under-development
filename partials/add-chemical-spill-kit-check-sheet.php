<?php
require_once __DIR__ . '/../backend/controller/DropdownController.php';
$cskActiveBranches = getAllBranchDropdown();
$cskSessionBranch = trim((string)($_SESSION['Branch'] ?? ''));
$cskSessionBranchIsAvailable = false;
foreach ($cskActiveBranches as $cskBranchOption) {
  if (trim((string)($cskBranchOption['value'] ?? '')) === $cskSessionBranch && $cskSessionBranch !== '') {
    $cskSessionBranchIsAvailable = true;
    break;
  }
}
?>
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
                <div id="addCskCodeFeedback" class="small mt-1"></div>
              </div>
              <div class="col-12 col-md-4">
                <label for="addCskBranch" class="form-label">Branch <span class="text-danger">*</span></label>
                <select class="form-select" id="addCskBranch" name="branch" required>
                  <option value="" disabled>Select branch</option>
                  <?php foreach ($cskActiveBranches as $cskBranchOption): ?>
                    <?php $cskBranchValue = trim((string)($cskBranchOption['value'] ?? '')); ?>
                    <?php if ($cskBranchValue !== ''): ?>
                      <option value="<?= htmlspecialchars($cskBranchValue, ENT_QUOTES, 'UTF-8') ?>" <?= $cskSessionBranchIsAvailable && $cskSessionBranch === $cskBranchValue ? 'selected' : '' ?>><?= htmlspecialchars($cskBranchValue, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </select>
                <?php if ($cskSessionBranch !== '' && !$cskSessionBranchIsAvailable): ?>
                  <div class="form-text text-warning-emphasis">Your assigned branch (<?= htmlspecialchars($cskSessionBranch, ENT_QUOTES, 'UTF-8') ?>) is not an active branch option. Select an active branch or ask an administrator to update the branch list.</div>
                <?php endif; ?>
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

            <div class="row g-3" id="addCskMaterials">Choose a branch to load materials.</div>
          </section>

        </div>

        <!-- FOOTER -->
        <div class="modal-footer csk-add-footer">
          <button type="button" class="btn csk-btn-cancel" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" id="addChemicalSpillKitSubmit" class="btn csk-btn-save" disabled><i class="bi bi-check-lg me-1"></i> Save Kit</button>
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

  /* CSK Code validation feedback */
  #addCskCodeFeedback {
    font-size: 11px;
    line-height: 1.4;
    margin-top: 4px;
    margin-left: 7px
  }

  #addCskCodeFeedback:empty {
    display: none;
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

<script src="js/csk-materials.js"></script>
<script>
  (() => {
    const modal = document.getElementById('addChemicalSpillKitModal');
    const form = document.getElementById('addChemicalSpillKitForm');
    const codeInput = document.getElementById('addCskCode');
    const codeFeedback = document.getElementById('addCskCodeFeedback');
    const branchInput = document.getElementById('addCskBranch');
    const saveButton = document.getElementById('addChemicalSpillKitSubmit');
    if (!modal || !form || !codeInput || !codeFeedback || !branchInput || !saveButton || modal.dataset.cskAddLogicBound === 'true') return;
    modal.dataset.cskAddLogicBound = 'true';

    const materialContainer = document.getElementById('addCskMaterials');
    window.cskMaterialForms.bind(form, materialContainer);
    const loadRequiredStockForBranch = () => window.cskMaterialForms.load(form, materialContainer, branchInput.value.trim(), 'addCsk', false);
    let requestVersion = 0;
    let codeCheckTimeout;
    let codeCheckPending = false;
    let codeIsDuplicate = false;
    let codeIsAvailable = false;

    const refreshSaveButton = () => {
      saveButton.disabled = codeCheckPending || codeIsDuplicate || !codeIsAvailable || form.dataset.cskMaterialsReady !== 'true' || !form.checkValidity();
    };
    const setCodeState = (message, state) => {
      codeFeedback.textContent = message;
      codeFeedback.className = `small mt-1 ${state}`.trim();
      codeInput.classList.toggle('is-valid', state === 'text-success');
      codeInput.classList.toggle('is-invalid', state === 'text-danger');
      refreshSaveButton();
    };

    const requestJson = async (action, branch, code = '') => {
      const params = new URLSearchParams({
        action,
        branch
      });
      if (code !== '') params.set('code', code);
      const response = await fetch(`backend/controller/ChemicalSpillKitController.php?${params.toString()}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      return response.json();
    };
    const setPendingState = () => {
      codeCheckPending = true;
      codeIsDuplicate = false;
      codeIsAvailable = false;
      codeInput.classList.remove('is-valid', 'is-invalid');
      setCodeState('Checking CSK code availability...', 'text-muted');
    };
    const checkCodeAvailability = async (code, branch, version) => {
      try {
        const result = await requestJson('checkCode', branch, code);
        if (version !== requestVersion) return;
        if (result.success !== true || typeof result.exists !== 'boolean') throw new Error('Invalid CSK code-check response.');
        codeCheckPending = false;
        codeIsDuplicate = result.exists;
        codeIsAvailable = !result.exists;
        setCodeState(codeIsDuplicate ? 'This CSK code already exists in the selected branch.' : 'CSK code is available in this branch.', codeIsDuplicate ? 'text-danger' : 'text-success');
      } catch (error) {
        if (version !== requestVersion) return;
        codeCheckPending = false;
        codeIsDuplicate = false;
        codeIsAvailable = false;
        console.error('CSK code check failed:', error);
        setCodeState('Unable to check the CSK code. Saving is disabled.', 'text-warning');
      }
    };
    const startCurrentCodeCheck = (delay = 350) => {
      clearTimeout(codeCheckTimeout);
      const version = ++requestVersion;
      const code = codeInput.value.trim();
      const branch = branchInput.value.trim();
      if (branch === '') {
        codeCheckPending = false;
        codeIsDuplicate = false;
        codeIsAvailable = false;
        codeInput.classList.remove('is-valid', 'is-invalid');
        setCodeState('Select an active branch before checking the CSK code.', 'text-warning');
        return;
      }
      if (code === '') {
        codeCheckPending = false;
        codeIsDuplicate = false;
        codeIsAvailable = false;
        codeInput.classList.remove('is-valid', 'is-invalid');
        setCodeState('Enter a CSK code to check availability.', 'text-warning');
        return;
      }
      if (!/^CSK-\d+$/.test(code)) {
        codeCheckPending = false;
        codeIsDuplicate = false;
        codeIsAvailable = false;
        codeInput.classList.remove('is-valid');
        setCodeState('Use the CSK- prefix followed by a numeric code.', 'text-danger');
        return;
      }
      setPendingState();
      codeCheckTimeout = setTimeout(() => checkCodeAvailability(code, branch, version), delay);
    };
    const generateCodeForBranch = async () => {
      const branch = branchInput.value.trim();
      const version = ++requestVersion;
      clearTimeout(codeCheckTimeout);
      codeIsDuplicate = false;
      codeIsAvailable = false;

      if (branch === '') {
        codeCheckPending = false;
        codeInput.value = '';
        codeInput.classList.remove('is-valid', 'is-invalid');
        setCodeState('Select an active branch to generate a CSK code.', 'text-warning');
        return;
      }

      setPendingState();
      try {
        const result = await requestJson('getNextCode', branch);
        if (version !== requestVersion || branch !== branchInput.value.trim()) return;
        if (result.success !== true || !/^CSK-\d+$/i.test(result.code || '')) {
          throw new Error('The next CSK code could not be generated.');
        }

        codeInput.value = result.code;
        await checkCodeAvailability(result.code, branch, version);
      } catch (error) {
        if (version !== requestVersion || branch !== branchInput.value.trim()) return;
        codeCheckPending = false;
        codeIsDuplicate = false;
        codeIsAvailable = false;
        codeInput.value = '';
        console.error('CSK code generation failed:', error);
        setCodeState('Unable to generate or check a CSK code. Saving is disabled.', 'text-warning');
      }
    };

    form.addEventListener('input', (event) => {
      if (event.target === codeInput) startCurrentCodeCheck();
      else refreshSaveButton();
    });
    form.addEventListener('change', (event) => {
      if (event.target !== branchInput) refreshSaveButton();
    });
    branchInput.addEventListener('change', () => {
      generateCodeForBranch();
      loadRequiredStockForBranch();
    });
    modal.addEventListener('shown.bs.modal', () => {
      if (codeInput.value.trim() === '') generateCodeForBranch();
      else startCurrentCodeCheck(0);
      loadRequiredStockForBranch();
    });
    form.addEventListener('submit', (event) => {
      if (codeCheckPending || codeIsDuplicate || !codeIsAvailable || !form.checkValidity()) {
        event.preventDefault();
        refreshSaveButton();
      }
    });

    form.addEventListener('csk-materials-updated', refreshSaveButton);
    refreshSaveButton();
  })();
</script>