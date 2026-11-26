<?php
require_once 'backend/authentication/only-inspector.php';
require_once 'backend/config/db.php';

$inspectionDateResult = $conn->query("SELECT DATE(NOW()) AS inspection_date");
$inspectionDateRow = $inspectionDateResult->fetch_assoc();
$serverInspectionDate = $inspectionDateRow['inspection_date'] ?? '';

include_once 'notification/inspect_fe_success.php';
?>

<!DOCTYPE html>
<!--
* CoreUI - Free Bootstrap Admin Template
* @version v5.5.0
* @link https://coreui.io/product/free-bootstrap-admin-template/
* Copyright (c) 2026 creativeLabs Łukasz Holeczek
* Licensed under MIT (https://github.com/coreui/coreui-free-bootstrap-admin-template/blob/main/LICENSE)
-->

<html lang="en">
<?php include 'partials/header.php'; ?>

<body>
    <?php include 'partials/side-nav.php'; ?>
    <div class="wrapper d-flex flex-column min-vh-100">
        <?php include 'partials/header-nav.php'; ?>

        <div class="body flex-grow-1">

            <div class="container-fluid py-4 scanner-page">

                <!-- PAGE HERO -->
                <div class="rounded-3 p-3 p-md-4 mb-4 bg-primary bg-opacity-10">
                    <div class="row align-items-center g-3">
                        <div class="col-auto">
                            <div class="bg-white rounded-3 p-3 text-primary fs-2 shadow-sm">
                                <i class="bi bi-qr-code-scan"></i>
                            </div>
                        </div>

                        <div class="col">
                            <h2 class="h4 fw-bold text-dark mb-2">
                                Scan Fire Equipment QR Code
                            </h2>
                            <p class="text-body-secondary mb-0">
                                Point your QR scanner at the QR code on the Fire
                                Equipment to view its details and perform an inspection.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- SCANNER CARD -->
                <div class="row justify-content-center">
                    <div class="col-12 col-xl-9 col-xxl-8">

                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                            <!-- MODE BUTTONS -->
                            <div class="card-body p-3 p-md-4 pb-0">
                                <div class="row g-2">

                                    <div class="col-12 col-md-6">
                                        <!-- Ikaw ang maglalagay ng camera module link -->
                                        <a href="QR-code.php"
                                            class="btn btn-light border w-100 py-3 fw-semibold">
                                            <i class="bi bi-camera-fill me-2"></i>
                                            Scan using Camera
                                        </a>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <button type="button"
                                            class="btn btn-primary w-100 py-3 fw-semibold"
                                            disabled>
                                            <i class="bi bi-upc-scan me-2"></i>
                                            Scan Using Honeywell Device
                                        </button>
                                    </div>

                                </div>
                            </div>

                            <!-- MANUAL SCANNER CONTENT -->
                            <div class="card-body p-3 p-md-4">

                                <div class="rounded-3 bg-body-tertiary p-3 p-md-4 mb-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 fs-3 flex-shrink-0">
                                            <i class="bi bi-keyboard"></i>
                                        </div>

                                        <div>
                                            <h3 class="h5 fw-bold mb-2">
                                                Scan Fire Equipment QR Code
                                            </h3>
                                            <p class="text-body-secondary mb-0">
                                                Scan the QR
                                                code and automatically open the inspection form.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <label for="qrCodeInput" class="form-label fw-semibold">
                                    Fire Equipment QR Code
                                </label>

                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white text-primary">
                                        <i class="bi bi-qr-code-scan"></i>
                                    </span>

                                    <!-- Preserve the existing input ID and scanner attributes -->
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="qrCodeInput"
                                        placeholder="Scan QR code..."
                                        autocomplete="off"
                                        autocapitalize="off"
                                        spellcheck="false"
                                        autofocus>
                                </div>

                                <div class="form-text mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Scan the QR code to
                                    view the equipment details and perform an inspection.
                                </div>

                                <div id="scan-result"
                                    class="mt-3"
                                    aria-live="polite"></div>

                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <script src="js/equipment-registry.js"></script>
            <script src="js/QR-code.js?v=<?= hash_file('sha256', __DIR__ . '/js/QR-code.js') ?>"></script>
            <?php include 'partials/fire-extinguisher-check-sheet.php'; ?>
            <?php include 'partials/chemical-spill-kit-check-sheet.php'; ?>

            <style>
                /* FOOTER */

                .inspection-footer {
                    padding: 16px 24px;

                    background: #ffffff;
                    border-top: 1px solid #e8edf3;

                    gap: 10px;
                }

                .cancel-btn {
                    min-height: 42px;

                    border-radius: 10px;

                    padding: 0 18px;

                    color: #475569;
                    font-size: 13px;
                    font-weight: 600;
                }

                .save-btn {
                    min-height: 42px;

                    border: 0;
                    border-radius: 10px;

                    padding: 0 20px;

                    background: #2563eb;
                    color: #ffffff;

                    font-size: 13px;
                    font-weight: 600;

                    box-shadow: 0 5px 12px rgba(37, 99, 235, .18);

                    transition: all .2s ease;
                }
            </style>


        </div>
    </div>
    <?php include 'partials/footer.php'; ?>
    </div>
    <!-- CoreUI and necessary plugins-->
    <script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
    <script src="vendors/simplebar/js/simplebar.min.js"></script>

    <script>
        // to auto filled the date
        const inspectionModalElement = document.getElementById('inspectionChecklistModal');
        const serverInspectionDate = <?= json_encode($serverInspectionDate) ?>;

        inspectionModalElement.addEventListener('show.bs.modal', function() {

            document.getElementById('dateInspected').value = serverInspectionDate;

        });
        document.addEventListener("DOMContentLoaded", function() {

            const qrInput = document.getElementById("qrCodeInput");
            const scanResult = document.getElementById("scan-result");
            const inspectionModalElement = document.getElementById(
                "inspectionChecklistModal"
            );

            if (!qrInput || !scanResult || !inspectionModalElement) {
                return;
            }

            let isValidating = false;
            let qrTypingTimer = null;

            // FOCUS QR INPUT
            function isSideNavVisible() {
                const sidebar = document.querySelector(".sidebar");

                if (!sidebar) {
                    return false;
                }

                // CoreUI sidebar is hidden on mobile when it has "hide" or
                // is not currently visible.
                return !sidebar.classList.contains("hide") &&
                    sidebar.getClientRects().length > 0 &&
                    getComputedStyle(sidebar).visibility !== "hidden";
            }

            function focusQRInput() {
                if (
                    !isValidating &&
                    !inspectionModalElement.classList.contains("show") &&
                    !qrInput.disabled &&
                    !isSideNavVisible()
                ) {
                    qrInput.focus();

                    const cursorPosition = qrInput.value.length;
                    qrInput.setSelectionRange(cursorPosition, cursorPosition);
                }
            }

            function restoreQRFocus() {
                if (
                    !isValidating &&
                    !inspectionModalElement.classList.contains("show") &&
                    !qrInput.disabled &&
                    !isSideNavVisible()
                ) {
                    setTimeout(() => {
                        qrInput.focus();

                        const cursorPosition = qrInput.value.length;
                        qrInput.setSelectionRange(cursorPosition, cursorPosition);
                    }, 150);
                }
            }

            // DISPLAY VALIDATION MESSAGE
            function showScanMessage(type, title, message) {
                scanResult.innerHTML = `
            <div class="alert alert-${type}" role="alert">
                <div class="fw-bold">${title}</div>
                <div class="small">${message}</div>
            </div>
        `;
            }

            // ========================================
            // VALIDATE QR CODE
            // ========================================
            // Badge decoration must never block hardware scanner lookup.
            function updateExpirationBadge(value) {
                try {
                    const badge = document.getElementById('inspectionExpirationBadge');
                    if (badge) {
                        badge.textContent = '';
                        badge.className = 'badge rounded-pill ms-auto align-self-start d-none';
                    }
                    if (typeof setFireExtinguisherExpirationBadge === 'function') {
                        setFireExtinguisherExpirationBadge(value);
                    }
                } catch (error) {
                    console.warn('Expiration badge update failed:', error);
                }
            }

            async function validateQRCode(qrCode = qrInput.value) {
                updateExpirationBadge();
                const code = String(qrCode || '').trim().toUpperCase();

                // Identify equipment type using equipment-registry.js
                let equipmentType = null;

                if (code.startsWith('FE-')) {
                    equipmentType = equipmentRegistry.FE;
                } else if (code.startsWith('CSK-')) {
                    equipmentType = equipmentRegistry.CSK;
                }

                if (!equipmentType) {
                    scanResult.innerHTML = `
                        <div class="alert alert-danger">
                            <strong>Invalid QR Code</strong><br>
                            Unsupported equipment code: ${code}
                        </div>
                    `;
                    return;
                }

                try {
                    const params = new URLSearchParams({
                        action: equipmentType.lookupAction,
                        code: code
                    });

                    // Preserve existing branch selection behavior.
                    const branchSelect = document.getElementById('branch');
                    const selectedBranch =
                        branchSelect?.value ||
                        new URLSearchParams(window.location.search).get('branch') ||
                        '';

                    if (selectedBranch) {
                        params.set('branch', selectedBranch);
                    }

                    const response = await fetch(
                        `backend/controller/ScanQRCodeController.php?${params.toString()}`
                    );

                    const result = await response.json();

                    if (!response.ok || !result.success || !result.data) {
                        scanResult.innerHTML = `
                            <div class="alert alert-warning">
                                ${result.message || 'Equipment not found.'}
                            </div>
                        `;
                        return;
                    }

                    const data = result.data;

                    // if the code starts with FE this will trigger 
                    if (code.startsWith('FE-')) {
                        updateExpirationBadge(data.expiration_badge);
                        document.getElementById('extinguisherCode').value = data.extinguisher_code || code;
                        document.getElementById('inspectionExtinguisherId').value = data.id || data.extinguisher_id || '';
                        document.getElementById('inspectionLocation').value = data.location || '';
                        document.getElementById('inspectionCapacity').value = data.capacity || '';
                        document.getElementById('inspectionType').value = data.type || '';
                        document.getElementById('inspectionClass').value = data.class || '';
                        document.getElementById('inspectionBranch').value = data.branch || '';

                        // Reset the existing FE checklist.
                        document.querySelectorAll('#inspectionChecklistForm input[type="checkbox"]').forEach(checkbox => {
                            checkbox.checked = false;
                        });

                        const remarksField = document.getElementById('remarks');
                        if (remarksField) {
                            remarksField.value = '';
                        }

                        const conditionStatus =
                            document.getElementById('inspectionConditionStatus');

                        if (conditionStatus) {
                            // Trigger the modal's existing four-component condition calculation after reset.
                            const pressureCheck = document.getElementById('pressureCheck');
                            if (pressureCheck) {
                                pressureCheck.dispatchEvent(
                                    new Event('change', { bubbles: true })
                                );
                            }
                        }

                        const modalElement =
                            document.getElementById('inspectionChecklistModal');

                        if (!modalElement) {
                            throw new Error('Fire Extinguisher inspection modal not found.');
                        }

                        bootstrap.Modal.getOrCreateInstance(modalElement).show();
                    }

                    // if the code starts with CSK this will trigger
                    else if (code.startsWith('CSK-')) {
                        const codeField = document.getElementById('spillKitCode');
                        const codeDisplay = document.getElementById('spillKitCodeDisplay');

                        const branchField = document.getElementById('spillKitBranch');
                        const branchDisplay = document.getElementById('spillKitBranchDisplay');
                        const locationField = document.getElementById('spillKitLocation');
                        const locationDisplay = document.getElementById('spillKitLocationDisplay');

                        const modalElement = document.getElementById('spillKitChecklistModal');

                        if (!modalElement) {
                            throw new Error('Chemical Spill Kit inspection modal not found.');
                        }

                        // Kit Code
                        if (codeField) {
                            codeField.value = data.chemical_spill_kit_code || code;
                        }

                        if (codeDisplay) {
                            codeDisplay.textContent = data.chemical_spill_kit_code || code;
                        }

                        // Branch
                        if (branchField) {
                            branchField.value = data.branch || '';
                        }

                        if (branchDisplay) {
                            branchDisplay.textContent = data.branch || '—';
                        }

                        // Location
                        if (locationField) {
                            locationField.value = data.location || '';
                        }

                        if (locationDisplay) {
                            locationDisplay.textContent = data.location || '—';
                        }

                        // Reset stock values and previous inspection status.
                        document.querySelectorAll(
                            '#spillKitChecklistForm .actual-stock-input'
                        ).forEach(input => {
                            input.value = '';
                        });

                        const spillKitStatus = document.getElementById('spillKitStatus');

                        if (spillKitStatus) {
                            spillKitStatus.textContent = 'Not yet checked';
                            spillKitStatus.className =
                                'badge rounded-pill bg-secondary-subtle text-secondary px-3 py-2';
                        }

                        bootstrap.Modal.getOrCreateInstance(modalElement).show();
                    }

                    scanResult.innerHTML = `
                        <div class="alert alert-success">
                            ${code} found successfully.
                        </div>
                    `;

                } catch (error) {
                    console.error('QR validation error:', error);

                    scanResult.innerHTML = `
                        <div class="alert alert-danger">
                            Unable to retrieve equipment information.
                            Please check the console or try again.
                        </div>
                    `;
                }
            }

            // ========================================
            // PHYSICAL QR SCANNER INPUT
            // Auto-validate kapag tumigil ang pag-type
            // ========================================
            qrInput.addEventListener("input", function() {

                clearTimeout(qrTypingTimer);

                if (!qrInput.value.trim()) {
                    return;
                }

                qrTypingTimer = setTimeout(function() {
                    validateQRCode(qrInput.value);
                }, 300);
            });

            // Support scanners that send Enter
            qrInput.addEventListener("keydown", function(event) {

                if (event.key === "Enter") {
                    event.preventDefault();

                    clearTimeout(qrTypingTimer);
                    validateQRCode(qrInput.value);
                }
            });

            // ========================================
            // KEEP INPUT READY
            // ========================================
            qrInput.addEventListener("blur", function() {

                if (!inspectionModalElement.classList.contains("show")) {
                    setTimeout(function() {
                        focusQRInput();
                    }, 100);
                }
            });

            // Initial focus
            focusQRInput();

            // ========================================
            // AFTER INSPECTION MODAL CLOSES
            // ========================================
            inspectionModalElement.addEventListener(
                "hidden.bs.modal",
                function() {

                    qrInput.value = "";
                    scanResult.innerHTML = "";

                    focusQRInput();
                }
            );

        });

        // this is for logic for condition status
        document.addEventListener('DOMContentLoaded', function() {

            const form = document.getElementById('inspectionChecklistForm');
            const statusBadge = document.getElementById('inspectionConditionStatus');

            if (!form || !statusBadge) {
                return;
            }

            function updateInspectionStatus() {

                // Overall FE Condition checks only Pressure, Hose, Nozzle, and Cylinder Body.
                // Any No Good among these four components makes the overall condition No Good.
                // The overall condition is Good only when all four critical components are Good.
                // Seal, Pin, Belt, Demarcation Line, Signage, Cleaning of Unit, and other items do not affect this badge.

                const pressure = document.getElementById('pressureCheck');
                const hose = document.getElementById('hoseCheck');
                const nozzle = document.getElementById('nozzleCheck');
                const cylinderBody = document.getElementById('cylinderBodyCheck');

                const items = [
                    pressure,
                    hose,
                    nozzle,
                    cylinderBody
                ];

                // Safety check
                if (items.some(item => !item)) {
                    return;
                }

                // Overall condition is Good only when all four critical components are Good.
                const allGood = items.every(item => item.checked);

                if (allGood) {

                    statusBadge.textContent = 'Good';
                    statusBadge.className = 'badge bg-success ms-auto';

                } else {

                    statusBadge.textContent = 'No Good';
                    statusBadge.className = 'badge bg-danger ms-auto';

                }
            }

            // Detect checkbox changes kahit nasa loob ng modal
            form.addEventListener('change', function(event) {

                if (
                    event.target.id === 'pressureCheck' ||
                    event.target.id === 'hoseCheck' ||
                    event.target.id === 'nozzleCheck' ||
                    event.target.id === 'cylinderBodyCheck'
                ) {
                    updateInspectionStatus();
                }

            });

            // Initial status
            updateInspectionStatus();

        });
    </script>
    <style>
        .condition-badge.pending { background: #f1f5f9; color: #475569; }
        .condition-badge.no-good { background: #fef2f2; color: #b91c1c; }
        .inspection-modal, .inspection-body, .inspection-section, .checklist-grid, .check-item, .check-item-content { min-width: 0; max-width: 100%; }
        .modal-dialog { width: calc(100% - 1rem); max-width: 1140px; margin: .5rem auto; }
        .checklist-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .check-item { display: grid; grid-template-columns: 39px minmax(0, 1fr) minmax(132px, 168px); align-items: center; box-sizing: border-box; }
        .check-item-description { white-space: normal; overflow: visible; text-overflow: clip; overflow-wrap: anywhere; }
        .check-control { width: 100%; min-width: 0; }
        .checklist-source { display: none; }
        .checklist-choice-group { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; width: 100%; min-width: 0; }
        .checklist-choice { min-width: 0; min-height: 42px; border: 1px solid #cbd5e1; border-radius: 9px; background: #fff; color: #475569; font-size: 12px; font-weight: 700; }
        .checklist-choice.good-choice { color: #15803d; }
        .checklist-choice.no-good-choice { color: #b91c1c; }
        .checklist-choice.good-choice.is-selected { background: #16a34a; border-color: #16a34a; color: #fff; box-shadow: 0 0 0 3px rgba(22, 163, 74, .15); }
        .checklist-choice.no-good-choice.is-selected { background: #dc2626; border-color: #dc2626; color: #fff; box-shadow: 0 0 0 3px rgba(220, 38, 38, .15); }
        .check-item.is-unanswered { border-color: #f59e0b; background: #fffbeb; }
        .checklist-na-control { margin-top: 8px; font-size: 12px; }
        .checklist-validation { margin: -4px 0 18px; padding: 11px 13px; border: 1px solid #fecaca; border-radius: 10px; background: #fef2f2; color: #b91c1c; font-size: 13px; font-weight: 600; }
        .inspection-header > .d-flex { min-width: 0; }
        .inspection-footer { flex-wrap: wrap; }
        @media (max-width: 991px) { .checklist-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 575px) { .checklist-grid { grid-template-columns: minmax(0, 1fr); } .inspection-header { padding: 14px; } .inspection-body { padding: 10px; } .inspection-section { padding: 14px; } .check-item { grid-template-columns: 39px minmax(0, 1fr); align-items: start; } .check-control { grid-column: 2; } .checklist-choice-group { margin-top: 8px; } .inspection-footer { padding: 12px; } }
        @media (max-width: 359px) { .check-item { padding: 11px; gap: 9px; } .checklist-choice { font-size: 11px; } }
    </style>
    <script>
        // Replaces Check Sheet checkboxes with explicit Good and No Good buttons while preserving backend checklist field names.
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('inspectionChecklistForm');
            const statusBadge = document.getElementById('inspectionConditionStatus');
            const message = document.getElementById('checklistValidationMessage');
            if (!form || !statusBadge || !message) return;

            // Updates Overall FE Condition from Pressure, Hose, Nozzle, and Cylinder Body only. Any No Good makes it No Good; Good requires all four, and every other checklist item is excluded.
            function updateCheckSheetStatus() {
                const values = Array.from(form.querySelectorAll('.checklist-value'));
                const unanswered = values.some(function(input) {
                    return input.value === '' && !input.closest('.check-item').querySelector('.checklist-na-input:checked');
                });
                const criticalValues = ['is_pressure_ok', 'is_hose_ok', 'is_nozzle_ok', 'is_cylinder_body_ok'].map(function(field) {
                    return form.querySelector('.checklist-value[name="' + field + '"]');
                });
                const allCriticalGood = criticalValues.every(function(input) { return input && input.value === '1'; });
                statusBadge.className = 'condition-badge ' + (unanswered ? 'pending' : allCriticalGood ? 'good' : 'no-good');
                statusBadge.innerHTML = unanswered ? '<i class="bi bi-hourglass-split"></i> Not answered' : allCriticalGood ? '<i class="bi bi-check-circle-fill"></i> Good' : '<i class="bi bi-x-circle-fill"></i> No Good';
            }

            // Stores one explicit selection for a Check Sheet component and refreshes its buttons, validation, and Overall Condition.
            function selectCheckSheetValue(item, source, value) {
                const valueInput = item.querySelector('.checklist-value');
                const naInput = item.querySelector('.checklist-na-input');
                if (naInput) naInput.checked = false;
                valueInput.value = value;
                source.checked = value === '1';
                item.classList.remove('is-unanswered');
                item.querySelectorAll('.checklist-choice').forEach(function(button) {
                    const selected = button.dataset.value === value;
                    button.classList.toggle('is-selected', selected);
                    button.setAttribute('aria-pressed', selected ? 'true' : 'false');
                });
                message.classList.add('d-none');
                updateCheckSheetStatus();
            }

            // Builds Good and No Good controls for every existing Check Sheet field without changing its submitted name.
            form.querySelectorAll('.check-control .form-check-input').forEach(function(source) {
                const fieldName = source.name;
                const item = source.closest('.check-item');
                if (!fieldName || !item) return;
                source.removeAttribute('name');
                source.classList.add('checklist-source');
                const valueInput = document.createElement('input');
                valueInput.type = 'hidden';
                valueInput.name = fieldName;
                valueInput.value = '';
                valueInput.className = 'checklist-value';
                const group = document.createElement('div');
                group.className = 'checklist-choice-group';
                group.setAttribute('role', 'group');
                group.setAttribute('aria-label', item.querySelector('.check-item-title').textContent.trim() + ' condition');
                [['1', 'Good', 'good-choice'], ['0', 'No Good', 'no-good-choice']].forEach(function(choice) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.dataset.value = choice[0];
                    button.className = 'checklist-choice ' + choice[2];
                    button.textContent = choice[1];
                    button.setAttribute('aria-pressed', 'false');
                    button.addEventListener('click', function() { selectCheckSheetValue(item, source, choice[0]); });
                    group.appendChild(button);
                });
                source.parentElement.append(valueInput, group);
                if (source.dataset.naEligible === 'true') {
                    const naControl = document.createElement('div');
                    naControl.className = 'form-check checklist-na-control';
                    const naInput = document.createElement('input');
                    naInput.type = 'checkbox';
                    naInput.className = 'form-check-input checklist-na-input';
                    naInput.id = fieldName + '-not-applicable';
                    naInput.name = 'not_applicable[' + fieldName + ']';
                    naInput.value = '1';
                    const naLabel = document.createElement('label');
                    naLabel.className = 'form-check-label';
                    naLabel.htmlFor = naInput.id;
                    naLabel.textContent = '- Not Applicable';
                    naInput.addEventListener('change', function() {
                        const notApplicable = naInput.checked;
                        valueInput.value = '';
                        source.checked = false;
                        group.querySelectorAll('.checklist-choice').forEach(function(button) {
                            button.disabled = notApplicable;
                            button.classList.remove('is-selected');
                            button.setAttribute('aria-pressed', 'false');
                        });
                        item.classList.remove('is-unanswered');
                        message.classList.add('d-none');
                        updateCheckSheetStatus();
                    });
                    naControl.append(naInput, naLabel);
                    source.parentElement.appendChild(naControl);
                }
            });

            // Clears selections whenever another extinguisher opens the Check Sheet so no previous answer becomes the default.
            document.getElementById('inspectionChecklistModal').addEventListener('show.bs.modal', function() {
                form.querySelectorAll('.checklist-value').forEach(function(input) { input.value = ''; });
                form.querySelectorAll('.checklist-source').forEach(function(input) { input.checked = false; });
                form.querySelectorAll('.checklist-na-input').forEach(function(input) { input.checked = false; });
                form.querySelectorAll('.checklist-choice').forEach(function(button) { button.disabled = false; });
                form.querySelectorAll('.checklist-choice').forEach(function(button) { button.classList.remove('is-selected'); button.setAttribute('aria-pressed', 'false'); });
                form.querySelectorAll('.check-item').forEach(function(item) { item.classList.remove('is-unanswered'); });
                message.classList.add('d-none');
                updateCheckSheetStatus();
            });

            // Blocks incomplete Check Sheet submissions, highlights missing answers, and asks for confirmation only after every item is answered.
            form.addEventListener('submit', function(event) {
                const unanswered = Array.from(form.querySelectorAll('.checklist-value')).filter(function(input) {
                    return input.value === '' && !input.closest('.check-item').querySelector('.checklist-na-input:checked');
                });
                if (unanswered.length) {
                    event.preventDefault();
                    unanswered.forEach(function(input) { input.closest('.check-item').classList.add('is-unanswered'); });
                    message.textContent = 'Complete all checklist items by selecting Good, No Good, or Not Installed / Not Applicable where available before submitting.';
                    message.classList.remove('d-none');
                    const firstItem = unanswered[0].closest('.check-item');
                    firstItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstItem.querySelector('.checklist-choice').focus();
                    return;
                }
                if (!confirm('Are you sure you want to submit this inspection?')) event.preventDefault();
            });

            updateCheckSheetStatus();
        });
    </script>

    <?php include_once 'notification/session_timeout.php'; ?>

</body>

</html>
