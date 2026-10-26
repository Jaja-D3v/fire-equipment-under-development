<?php
require_once 'backend/authentication/only-inspector.php';
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
            <script src="js/QR-code.js"></script>
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

        inspectionModalElement.addEventListener('show.bs.modal', function() {

            const today = new Date().toISOString().split('T')[0];
            document.getElementById('dateInspected').value = today;

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
            async function validateQRCode(qrCode = qrInput.value) {
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
                            conditionStatus.value = '';
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

                // Check kung may kahit isang nasagutan
                const hasAnswer = items.some(item => item.checked);

                // Wala pang nasasagutan
                if (!hasAnswer) {

                    statusBadge.textContent = 'Status';
                    statusBadge.className = 'badge bg-secondary ms-auto';

                    return;
                }

                // Lahat checked = Good
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

    <?php include_once 'notification/session_timeout.php'; ?>

</body>

</html>