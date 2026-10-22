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



            <script src="js/QR-code.js"></script>

            <!-- inspection checklist modal -->
            <div class="modal fade" id="inspectionChecklistModal" tabindex="-1" aria-labelledby="inspectionChecklistModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content inspection-modal">

                        <!-- HEADER -->
                        <div class="modal-header inspection-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="inspection-header-icon">
                                    <i class="bi bi-clipboard2-check"></i>
                                </div>
                                <div>
                                    <h5
                                        class="modal-title fw-bold mb-1"
                                        id="inspectionChecklistModalLabel">
                                        Inspection Checklist
                                    </h5>
                                    <div class="inspection-subtitle">
                                        Fire extinguisher safety inspection
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="location.reload()">
                            </button>
                        </div>

                        <!-- BODY -->
                        <div class="modal-body inspection-body">
                            <form id="inspectionChecklistForm" action="backend/controller/InspectionController.php" method="post">
                                <input type="hidden" name="action" value="inspect">
                                <input type="hidden" id="inspectionExtinguisherId" name="extinguisher_id" value="">

                                <!-- SECTION 1 — BASIC INFORMATION -->
                                <section class="inspection-section">
                                    <div class="section-heading">
                                        <div class="section-number">
                                            1
                                        </div>

                                        <div>
                                            <h6 class="section-title">
                                                Basic Information
                                            </h6>

                                            <p class="section-description">
                                                Fire extinguisher details retrieved from the QR code.
                                            </p>
                                        </div>
                                    </div>


                                    <div class="information-grid">
                                        <!-- FIRE EXTINGUISHER CODE -->
                                        <div class="info-field">

                                            <label for="extinguisherCode" class="info-label">
                                                Fire Extinguisher Code
                                            </label>

                                            <div class="modern-input">
                                                <div class="input-icon">
                                                    <i class="bi bi-qr-code"></i>
                                                </div>
                                                <input type="text" class="form-control" id="extinguisherCode" name="extinguisher_code" placeholder="FE-0001" readonly required>
                                            </div>
                                        </div>


                                        <!-- LOCATION -->
                                        <div class="info-field">

                                            <label
                                                for="inspectionLocation"
                                                class="info-label">
                                                Location
                                            </label>

                                            <div class="modern-input">
                                                <div class="input-icon">
                                                    <i class="bi bi-geo-alt"></i>
                                                </div>
                                                <input type="text" class="form-control" id="inspectionLocation" name="location" placeholder="Building A - 1st Floor" readonly required>
                                            </div>
                                        </div>


                                        <!-- BRANCH -->
                                        <div class="info-field">

                                            <label
                                                for="inspectionBranch"
                                                class="info-label">
                                                Branch
                                            </label>

                                            <div class="modern-input">

                                                <div class="input-icon">
                                                    <i class="bi bi-building"></i>
                                                </div>

                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="inspectionBranch"
                                                    name="branch"
                                                    placeholder="Branch"
                                                    readonly
                                                    required>

                                            </div>
                                        </div>

                                        <!-- TYPE -->
                                        <div class="info-field">

                                            <label
                                                for="inspectionType"
                                                class="info-label">
                                                Type
                                            </label>

                                            <div class="modern-input">

                                                <div class="input-icon">
                                                    <i class="bi bi-fire"></i>
                                                </div>

                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="inspectionType"
                                                    name="type"
                                                    placeholder="Type"
                                                    readonly
                                                    required>

                                            </div>

                                        </div>


                                        <!-- CAPACITY -->
                                        <div class="info-field">

                                            <label
                                                for="inspectionCapacity"
                                                class="info-label">
                                                Capacity
                                            </label>

                                            <div class="modern-input">

                                                <div class="input-icon">
                                                    <i class="bi bi-speedometer2"></i>
                                                </div>

                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="inspectionCapacity"
                                                    name="capacity"
                                                    placeholder="Capacity"
                                                    readonly
                                                    required>

                                            </div>
                                        </div>

                                        <!-- CLASS -->
                                        <div class="info-field">

                                            <label
                                                for="inspectionClass"
                                                class="info-label">
                                                Class
                                            </label>

                                            <div class="modern-input">

                                                <div class="input-icon">
                                                    <i class="bi bi-shield-check"></i>
                                                </div>

                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="inspectionClass"
                                                    name="class"
                                                    placeholder="Class"
                                                    readonly
                                                    required>

                                            </div>
                                        </div>

                                        <!-- DATE INSPECTED -->
                                        <div class="info-field">

                                            <label
                                                for="dateInspected"
                                                class="info-label">
                                                Date of Inspection
                                            </label>

                                            <div class="modern-input">

                                                <div class="input-icon">
                                                    <i class="bi bi-calendar3"></i>
                                                </div>

                                                <input
                                                    type="date"
                                                    class="form-control"
                                                    id="dateInspected"
                                                    name="date_inspected"
                                                    readonly
                                                    required>

                                            </div>

                                        </div>


                                        <!-- INSPECTED BY -->
                                        <div class="info-field">

                                            <label
                                                for="inspectedBy"
                                                class="info-label">
                                                Inspector
                                            </label>

                                            <div class="modern-input">

                                                <div class="input-icon">
                                                    <i class="bi bi-person"></i>
                                                </div>

                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="inspectedBy"
                                                    name="inspected_by"
                                                    value="<?= htmlspecialchars($_SESSION['EmployeeName']) ?>"
                                                    readonly>

                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <!-- SECTION 2 — INSPECTION CHECKLIST -->
                                <section class="inspection-section">
                                    <div class="section-heading checklist-heading">
                                        <div class="section-number">
                                            2
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="section-title">
                                                Inspection Checklist
                                            </h6>
                                            <p class="section-description">
                                                Verify each component of the fire extinguisher.
                                            </p>
                                        </div>

                                        <!-- STATUS -->
                                        <div class="condition-status">
                                            <span class="condition-label">
                                                Overall Condition
                                            </span>
                                            <span
                                                id="inspectionConditionStatus"
                                                class="condition-badge good">
                                                <i class="bi bi-check-circle-fill"></i>
                                                Good
                                            </span>
                                        </div>
                                    </div>


                                    <!-- INFO NOTICE -->
                                    <div class="inspection-notice">
                                        <div class="notice-icon">
                                            <i class="bi bi-info-circle-fill"></i>
                                        </div>
                                        <div>

                                            <div class="notice-title">
                                                Inspection Guide
                                            </div>

                                            <div class="notice-text">
                                                Check the box when the component is in good condition.
                                            </div>
                                        </div>
                                    </div>


                                    <!-- CHECKLIST -->
                                    <div class="checklist-grid">


                                        <!-- SEAL -->
                                        <div class="check-item">
                                            <div class="check-item-icon">
                                                <i class="bi bi-tag"></i>
                                            </div>
                                            <div class="check-item-content">
                                                <div class="check-item-title">
                                                    Seal
                                                </div>
                                                <div class="check-item-description">
                                                    Safety seal condition
                                                </div>
                                            </div>
                                            <div class="form-check check-control">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="sealCheck"
                                                    name="is_seal_ok"
                                                    value="1">
                                            </div>
                                        </div>

                                        <!-- PIN -->
                                        <div class="check-item">
                                            <div class="check-item-icon">
                                                <i class="bi bi-pin-angle"></i>
                                            </div>
                                            <div class="check-item-content">
                                                <div class="check-item-title">
                                                    Pin
                                                </div>
                                                <div class="check-item-description">
                                                    Safety pin condition
                                                </div>
                                            </div>
                                            <div class="form-check check-control">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="pinCheck"
                                                    name="is_pin_ok"
                                                    value="1">
                                            </div>
                                        </div>

                                        <!-- PRESSURE -->
                                        <div class="check-item">
                                            <div class="check-item-icon">
                                                <i class="bi bi-speedometer2"></i>
                                            </div>
                                            <div class="check-item-content">
                                                <div class="check-item-title">
                                                    Pressure
                                                </div>
                                                <div class="check-item-description">
                                                    Pressure gauge condition
                                                </div>
                                            </div>
                                            <div class="form-check check-control">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="pressureCheck"
                                                    name="is_pressure_ok"
                                                    value="1">
                                            </div>
                                        </div>
                                        <!-- HOSE -->
                                        <div class="check-item">
                                            <div class="check-item-icon">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </div>
                                            <div class="check-item-content">
                                                <div class="check-item-title">
                                                    Hose
                                                </div>
                                                <div class="check-item-description">
                                                    Hose condition
                                                </div>
                                            </div>
                                            <div class="form-check check-control">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="hoseCheck"
                                                    name="is_hose_ok"
                                                    value="1">
                                            </div>
                                        </div>

                                        <!-- NOZZLE -->
                                        <div class="check-item">
                                            <div class="check-item-icon">
                                                <i class="bi bi-send"></i>
                                            </div>
                                            <div class="check-item-content">
                                                <div class="check-item-title">
                                                    Nozzle
                                                </div>
                                                <div class="check-item-description">
                                                    Nozzle condition
                                                </div>
                                            </div>
                                            <div class="form-check check-control">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="nozzleCheck"
                                                    name="is_nozzle_ok"
                                                    value="1">
                                            </div>
                                        </div>

                                        <!-- BELT -->
                                        <div class="check-item">
                                            <div class="check-item-icon">
                                                <i class="bi bi-link-45deg"></i>
                                            </div>
                                            <div class="check-item-content">
                                                <div class="check-item-title">
                                                    Belt
                                                </div>
                                                <div class="check-item-description">
                                                    Belt condition
                                                </div>
                                            </div>
                                            <div class="form-check check-control">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="beltCheck"
                                                    name="is_belt_ok"
                                                    value="1">
                                            </div>
                                        </div>

                                        <!-- CYLINDER BODY -->
                                        <div class="check-item">
                                            <div class="check-item-icon">
                                                <i class="bi bi-fire"></i>
                                            </div>
                                            <div class="check-item-content">
                                                <div class="check-item-title">
                                                    Cylinder Body
                                                </div>
                                                <div class="check-item-description">
                                                    Cylinder body condition
                                                </div>
                                            </div>
                                            <div class="form-check check-control">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="cylinderBodyCheck"
                                                    name="is_cylinder_body_ok"
                                                    value="1">
                                            </div>
                                        </div>

                                        <!-- DEMARCATION LINE -->
                                        <div class="check-item">
                                            <div class="check-item-icon">
                                                <i class="bi bi-sign-turn-slight-right"></i>
                                            </div>
                                            <div class="check-item-content">
                                                <div class="check-item-title">
                                                    Demarcation Line
                                                </div>
                                                <div class="check-item-description">
                                                    Visibility and condition
                                                </div>
                                            </div>
                                            <div class="form-check check-control">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="demarcationLineCheck"
                                                    name="is_demarcation_line_ok"
                                                    value="1">
                                            </div>
                                        </div>

                                        <!-- SIGNAGE -->
                                        <div class="check-item">
                                            <div class="check-item-icon">
                                                <i class="bi bi-signpost-2"></i>
                                            </div>
                                            <div class="check-item-content">
                                                <div class="check-item-title">
                                                    Signage
                                                </div>
                                                <div class="check-item-description">
                                                    Safety signage condition
                                                </div>
                                            </div>

                                            <div class="form-check check-control">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="signageCheck"
                                                    name="is_signage_ok"
                                                    value="1">
                                            </div>
                                        </div>

                                        <!-- CLEANING OF UNIT -->
                                        <div class="check-item">
                                            <div class="check-item-icon">
                                                <i class="bi bi-stars"></i>
                                            </div>
                                            <div class="check-item-content">
                                                <div class="check-item-title">
                                                    Cleaning of Unit
                                                </div>
                                                <div class="check-item-description">
                                                    Unit cleanliness condition
                                                </div>
                                            </div>
                                            <div class="form-check check-control">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="cleaningOfUnitCheck"
                                                    name="is_cleaning_of_unit_ok"
                                                    value="1">
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <!-- SECTION 3 — REMARKS -->
                                <section class="inspection-section remarks-section">
                                    <div class="section-heading">
                                        <div class="section-number">
                                            3
                                        </div>
                                        <div>
                                            <h6 class="section-title">
                                                Remarks / Comments
                                            </h6>
                                            <p class="section-description">
                                                Add additional notes about the inspection.
                                            </p>
                                        </div>
                                    </div>
                                    <textarea
                                        class="form-control remarks-input"
                                        id="remarks"
                                        name="remarks"
                                        rows="4"
                                        placeholder="Enter inspection remarks or type N/A if not applicable"></textarea>
                                </section>
                            </form>
                        </div>

                        <!-- FOOTER -->
                        <div class="modal-footer inspection-footer">
                            <button
                                type="button"
                                class="btn btn-light cancel-btn"
                                data-bs-dismiss="modal"
                                onclick="location.reload()">
                                <i class="bi bi-x-lg me-1"></i>
                                Cancel
                            </button>
                            <button
                                type="submit"
                                form="inspectionChecklistForm"
                                class="btn save-btn">

                                <i class="bi bi-check2-circle me-1"></i>
                                Save Inspection
                            </button>
                        </div>
                    </div>
                </div>
            </div>

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
            async function validateQRCode() {

                const code = qrInput.value.trim();

                if (!code || isValidating) {
                    return;
                }

                isValidating = true;
                qrInput.disabled = true;

                showScanMessage(
                    "info",
                    "Validating QR Code",
                    "Checking fire extinguisher details..."
                );

                try {

                    const response = await fetch(
                        `backend/controller/ScanQRCodeController.php?action=getFeInfo&code=${encodeURIComponent(code)}&branch=${encodeURIComponent(new URLSearchParams(window.location.search).get("branch") || "")}`
                    );

                    if (!response.ok) {
                        throw new Error(`HTTP error: ${response.status}`);
                    }

                    const result = await response.json();

                    // INVALID QR CODE
                    if (!result.success) {
                        showScanMessage(
                            "danger",
                            result.archived ?
                            "Fire Extinguisher Archived" :
                            "QR Code Validation",
                            result.message || "Unable to validate QR code."
                        );

                        return;
                    }

                    // VALID QR CODE
                    const data = result.data;

                    document.getElementById("extinguisherCode").value =
                        data.extinguisher_code ?? "";

                    document.getElementById("inspectionExtinguisherId").value =
                        data.extinguisher_id ?? "";

                    document.getElementById("inspectionLocation").value =
                        data.location ?? "";

                    document.getElementById("inspectionCapacity").value =
                        data.capacity ?? "";

                    document.getElementById("inspectionType").value =
                        data.type ?? "";

                    document.getElementById("inspectionClass").value =
                        data.class ?? "";

                    document.getElementById("inspectionBranch").value =
                        data.branch ?? "";

                    // Reset checklist for the new inspection
                    const form = document.getElementById(
                        "inspectionChecklistForm"
                    );

                    if (form) {

                        form.querySelectorAll(
                            'input[type="checkbox"]'
                        ).forEach(function(checkbox) {
                            checkbox.checked = false;
                        });

                        const remarks = document.getElementById("remarks");

                        if (remarks) {
                            remarks.value = "";
                        }

                        // I-reset din ang condition status kapag bagong inspection.
                        const statusBadge = document.getElementById(
                            "inspectionConditionStatus"
                        );

                        if (statusBadge) {
                            statusBadge.textContent = "Status";
                            statusBadge.className = "badge bg-secondary ms-auto";
                        }
                    }

                    scanResult.innerHTML = "";

                    // Open inspection modal
                    bootstrap.Modal.getOrCreateInstance(
                        inspectionModalElement
                    ).show();

                } catch (error) {
                    console.error("QR validation error:", error);

                    showScanMessage(
                        "danger",
                        "Validation Error",
                        error.message || "Unable to validate QR code."
                    );
                } finally {

                    isValidating = false;
                    qrInput.disabled = false;

                    // Huwag piliting i-focus habang bukas ang inspection modal.
                    if (!inspectionModalElement.classList.contains("show")) {
                        focusQRInput();
                    }
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
