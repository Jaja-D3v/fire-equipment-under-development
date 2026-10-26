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
                                Scan Fire Extinguisher QR Code
                            </h2>
                            <p class="text-body-secondary mb-0">
                                Point your QR scanner at the QR code on the fire
                                extinguisher to view its details and perform an inspection.
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
                                                Scan Fire Extinguisher QR Code
                                            </h3>
                                            <p class="text-body-secondary mb-0">
                                                Scan the QR
                                                code and automatically open the inspection form.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <label for="qrCodeInput" class="form-label fw-semibold">
                                    Fire Extinguisher QR Code
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
                                    view the extinguisher details and perform an inspection.
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
                /* =========================================================
                                MODAL
                        ========================================================= */

                .inspection-modal {
                    border: 0;
                    border-radius: 22px;
                    overflow: hidden;
                    background: #f8fafc;
                    box-shadow: 0 25px 70px rgba(15, 23, 42, 0.18);
                }


                /* =========================================================
                                HEADER
                        ========================================================= */

                .inspection-header {
                    background: #ffffff;
                    border-bottom: 1px solid #e8edf3;
                    padding: 20px 26px;
                }

                .inspection-header-icon {
                    width: 48px;
                    height: 48px;
                    border-radius: 14px;

                    display: flex;
                    align-items: center;
                    justify-content: center;

                    background: #eef5ff;
                    color: #2563eb;

                    font-size: 22px;
                }

                .inspection-subtitle {
                    color: #64748b;
                    font-size: 13px;
                }


                /* =========================================================
                                        BODY
                                                    ========================================================= */

                .inspection-body {
                    padding: 24px;
                    background: #f8fafc;
                }


                /* =========================================================
                            SECTION
                                ========================================================= */

                .inspection-section {
                    background: #ffffff;
                    border: 1px solid #e7edf4;
                    border-radius: 18px;
                    padding: 22px;
                    margin-bottom: 18px;
                }

                .inspection-section:last-child {
                    margin-bottom: 0;
                }


                /* =========================================================
                                SECTION HEADING
                                    ========================================================= */

                .section-heading {
                    display: flex;
                    align-items: center;
                    gap: 13px;
                    margin-bottom: 22px;
                }

                .section-number {
                    width: 34px;
                    height: 34px;
                    flex-shrink: 0;

                    display: flex;
                    align-items: center;
                    justify-content: center;

                    border-radius: 10px;

                    background: #2563eb;
                    color: #ffffff;

                    font-size: 14px;
                    font-weight: 700;
                }

                .section-title {
                    margin: 0;
                    color: #172033;
                    font-size: 15px;
                    font-weight: 700;
                }

                .section-description {
                    margin: 3px 0 0;
                    color: #7b8798;
                    font-size: 12px;
                }


                /* =========================================================
                                        INFORMATION GRID
                                            ========================================================= */

                .information-grid {
                    display: grid;
                    grid-template-columns: repeat(3, 1fr);
                    gap: 18px;
                }

                .info-field {
                    min-width: 0;
                }

                .info-label {
                    display: block;
                    margin-bottom: 7px;

                    color: #475569;
                    font-size: 12px;
                    font-weight: 600;
                }


                /* =========================================================
                                        MODERN INPUT
                                            ========================================================= */

                .modern-input {
                    display: flex;
                    align-items: center;

                    min-height: 45px;

                    border: 1px solid #dfe6ee;
                    border-radius: 11px;

                    background: #f8fafc;

                    overflow: hidden;

                    transition: all .2s ease;
                }

                .modern-input:focus-within {
                    border-color: #93b4ef;
                    box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
                }

                .input-icon {
                    width: 43px;
                    height: 45px;

                    display: flex;
                    align-items: center;
                    justify-content: center;

                    color: #64748b;
                    font-size: 16px;

                    flex-shrink: 0;
                }

                .modern-input .form-control {
                    height: 45px;

                    border: 0 !important;
                    outline: 0 !important;
                    box-shadow: none !important;

                    background: transparent;

                    color: #263449;
                    font-size: 13px;
                    font-weight: 500;

                    padding-left: 0;
                }

                .modern-input .form-control::placeholder {
                    color: #a0aabb;
                }


                /* =========================================================
                                        CHECKLIST HEADER
                                            ========================================================= */

                .checklist-heading {
                    margin-bottom: 18px;
                }

                .condition-status {
                    margin-left: auto;

                    display: flex;
                    align-items: center;
                    gap: 9px;
                }

                .condition-label {
                    color: #7b8798;
                    font-size: 12px;
                    font-weight: 600;
                }

                .condition-badge {
                    display: inline-flex;
                    align-items: center;
                    gap: 5px;

                    padding: 7px 11px;

                    border-radius: 999px;

                    font-size: 12px;
                    font-weight: 700;
                }

                .condition-badge.good {
                    background: #ecfdf3;
                    color: #15803d;
                }


                /* =========================================================
                                NOTICE
                                    ========================================================= */

                .inspection-notice {
                    display: flex;
                    align-items: flex-start;
                    gap: 11px;

                    padding: 13px 15px;

                    margin-bottom: 18px;

                    border: 1px solid #dbeafe;
                    border-radius: 12px;

                    background: #f8fbff;
                }

                .notice-icon {
                    color: #2563eb;
                    font-size: 17px;
                    line-height: 1.2;
                }

                .notice-title {
                    color: #334155;
                    font-size: 12px;
                    font-weight: 700;
                    margin-bottom: 2px;
                }

                .notice-text {
                    color: #64748b;
                    font-size: 12px;
                }


                /* =========================================================
                                            CHECKLIST GRID
                                                ========================================================= */

                .checklist-grid {
                    display: grid;
                    grid-template-columns: repeat(3, 1fr);
                    gap: 12px;
                }

                .check-item {
                    min-height: 76px;

                    display: flex;
                    align-items: center;
                    gap: 12px;

                    padding: 13px 14px;

                    border: 1px solid #e5eaf0;
                    border-radius: 14px;

                    background: #ffffff;

                    transition: all .2s ease;
                }

                .check-item:hover {
                    border-color: #cbd8ea;
                    background: #fbfdff;
                    transform: translateY(-1px);
                }

                .check-item-icon {
                    width: 39px;
                    height: 39px;

                    display: flex;
                    align-items: center;
                    justify-content: center;

                    flex-shrink: 0;

                    border-radius: 11px;

                    background: #eff6ff;
                    color: #2563eb;

                    font-size: 17px;
                }

                .check-item-content {
                    min-width: 0;
                    flex: 1;
                }

                .check-item-title {
                    color: #263449;
                    font-size: 13px;
                    font-weight: 700;
                    margin-bottom: 2px;
                }

                .check-item-description {
                    color: #94a0b2;
                    font-size: 11px;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }


                /* =========================================================
                                    CHECKBOX
                                            ========================================================= */

                .check-control {
                    margin: 0;
                    padding: 0;

                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .check-control .form-check-input {
                    width: 21px;
                    height: 21px;

                    margin: 0;

                    border: 2px solid #cbd5e1;

                    cursor: pointer;

                    box-shadow: none;
                }

                .check-control .form-check-input:focus {
                    box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
                }

                .check-control .form-check-input:checked {
                    background-color: #2563eb;
                    border-color: #2563eb;
                }


                /* =========================================================
                                    REMARKS
                                        ========================================================= */

                .remarks-section {
                    margin-bottom: 0;
                }

                .remarks-input {
                    resize: vertical;

                    border: 1px solid #dfe6ee;
                    border-radius: 12px;

                    background: #f8fafc;

                    padding: 13px 15px;

                    font-size: 13px;

                    box-shadow: none !important;
                }

                .remarks-input:focus {
                    border-color: #93b4ef;
                    background: #ffffff;

                    box-shadow: 0 0 0 3px rgba(37, 99, 235, .08) !important;
                }

                .remarks-input::placeholder {
                    color: #a0aabb;
                }


                /* =========================================================
                        FOOTER
                                ========================================================= */

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

                .save-btn:hover {
                    background: #1d4ed8;
                    color: #ffffff;
                    transform: translateY(-1px);
                }


                /* =========================================================
                        RESPONSIVE
                                    ========================================================= */

                @media (max-width: 991px) {

                    .information-grid {
                        grid-template-columns: repeat(2, 1fr);
                    }

                    .checklist-grid {
                        grid-template-columns: repeat(2, 1fr);
                    }

                }


                @media (max-width: 767px) {

                    .inspection-header {
                        padding: 16px 18px;
                    }

                    .inspection-body {
                        padding: 14px;
                    }

                    .inspection-section {
                        padding: 17px;
                        border-radius: 15px;
                    }

                    .information-grid {
                        grid-template-columns: 1fr;
                        gap: 14px;
                    }

                    .checklist-grid {
                        grid-template-columns: 1fr;
                    }

                    .checklist-heading {
                        align-items: flex-start;
                    }

                    .condition-status {
                        margin-left: 0;
                        margin-top: 4px;
                    }

                    .inspection-footer {
                        padding: 13px 14px;
                    }

                    .cancel-btn,
                    .save-btn {
                        flex: 1;
                    }

                }


                @media (max-width: 480px) {

                    .inspection-header-icon {
                        width: 42px;
                        height: 42px;
                        font-size: 19px;
                    }

                    .inspection-header .modal-title {
                        font-size: 15px;
                    }

                    .inspection-subtitle {
                        font-size: 11px;
                    }

                    .section-heading {
                        align-items: flex-start;
                    }

                    .condition-status {
                        flex-direction: column;
                        align-items: flex-start;
                        gap: 5px;
                    }

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
            async function validateQRCode(qrCode) {
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
                        const branchField = document.getElementById('spillKitBranch');
                        const locationField = document.getElementById('spillKitLocation');

                        if (!document.getElementById('spillKitChecklistModal')) {
                            throw new Error('Chemical Spill Kit inspection modal not found.');
                        }

                        if (codeField) {
                            codeField.value = data.chemical_spill_kit_code || code;
                        }

                        if (branchField) {
                            branchField.value = data.branch || '';
                        }

                        if (locationField) {
                            locationField.value = data.location || '';
                        }

                        // Reset stock values and previous inspection status.
                        document.querySelectorAll(
                            '#spillKitChecklistForm .actual-stock-input'
                        ).forEach(input => {
                            input.value = '';
                        });

                        const spillKitStatus =
                            document.getElementById('spillKitStatus');

                        if (spillKitStatus) {
                            spillKitStatus.value = '';
                        }

                        const modalElement =
                            document.getElementById('spillKitChecklistModal');

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
                    validateQRCode();
                }, 300);
            });

            // Support scanners that send Enter
            qrInput.addEventListener("keydown", function(event) {

                if (event.key === "Enter") {
                    event.preventDefault();

                    clearTimeout(qrTypingTimer);
                    validateQRCode();
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