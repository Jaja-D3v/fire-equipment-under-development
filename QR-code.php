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

      <!-- scanner page -->

      <!-- PAGE HERO -->
      <div class="container-fluid py-4 scanner-page">

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
                Point your camera at the QR code on the fire
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
                    <button type="button"
                      class="btn btn-primary w-100 py-3 fw-semibold"
                      disabled>
                      <i class="bi bi-camera-fill me-2"></i>
                      Scan Using Camera
                    </button>
                  </div>

                  <div class="col-12 col-md-6">
                    <a href="scan-QR-Code.php"
                      class="btn btn-light border w-100 py-3 fw-semibold">
                      <i class="bi bi-upc-scan me-2"></i>
                      Scan Using Honeywell Device
                    </a>
                  </div>

                </div>
              </div>

              <!-- CAMERA SCANNER CONTENT -->
              <div class="card-body p-3 p-md-4">

                <!-- CAMERA INTRODUCTION -->
                <div class="rounded-3 bg-body-tertiary p-3 p-md-4 mb-4">
                  <div class="d-flex align-items-center gap-3">

                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 fs-3 flex-shrink-0">
                      <i class="bi bi-camera-video"></i>
                    </div>

                    <div>
                      <h3 class="h5 fw-bold mb-2">
                        Scan Using Camera
                      </h3>
                      <p class="text-body-secondary mb-0">
                        Position the fire extinguisher QR code
                        inside the camera view to scan and
                        open the inspection form.
                      </p>
                    </div>

                  </div>
                </div>

                <!-- ACTUAL CAMERA PREVIEW -->
                <div class="border rounded-3 overflow-hidden">
                  <div id="qr-reader" class="w-100 overflow-hidden"></div>
                </div>

                <!-- EXISTING SCAN RESULT -->
                <div id="scan-result"
                  class="mt-3"
                  aria-live="polite"></div>

                <!-- SCANNING INSTRUCTIONS -->
                <div class="form-text mt-3">
                  <i class="bi bi-info-circle me-1"></i>
                  Allow camera access when prompted. Keep the QR code
                  centered and hold your device steady until it is scanned.
                </div>

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
                <input type="hidden" name="checklist_selection_mode" value="explicit">
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
                          value="<?= htmlspecialchars($serverInspectionDate, ENT_QUOTES, 'UTF-8') ?>"
                          readonly
                          required>

                      </div>

                    </div>


                    <!-- INSPECTED BY -->
                    <div class="info-field">

                      <label
                        for="inspectedBy"
                        class="info-label">
                        Inspected By
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
                        class="condition-badge pending">
                        <i class="bi bi-hourglass-split"></i>
                        Not answered
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
                        Select Good or No Good for every component before submitting.
                      </div>
                    </div>
                  </div>
                  <div id="checklistValidationMessage" class="checklist-validation d-none" role="alert" aria-live="assertive"></div>


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
    const header = document.querySelector("header.header");

    document.addEventListener("scroll", () => {
      if (header) {
        header.classList.toggle("shadow-sm", document.documentElement.scrollTop > 0);
      }
    });

    // to auto filled the date
    const inspectionModalElement = document.getElementById('inspectionChecklistModal');
    const serverInspectionDate = <?= json_encode($serverInspectionDate) ?>;

    inspectionModalElement.addEventListener('show.bs.modal', function() {

      document.getElementById('dateInspected').value = serverInspectionDate;

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
        // Any unchecked critical component is No Good; all four checked is Good.
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

        // Check kung may kahit isang nasagutan
        const hasAnswer = items.some(item => item.checked);

        // Wala pang nasasagutan
        if (!hasAnswer) {

          statusBadge.textContent = 'Status';
          statusBadge.className = 'badge bg-secondary ms-auto';

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
    .checklist-validation { margin: -4px 0 18px; padding: 11px 13px; border: 1px solid #fecaca; border-radius: 10px; background: #fef2f2; color: #b91c1c; font-size: 13px; font-weight: 600; }
    .inspection-header > .d-flex { min-width: 0; }
    .inspection-footer { flex-wrap: wrap; }
    @media (max-width: 991px) { .checklist-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 575px) { .checklist-grid { grid-template-columns: minmax(0, 1fr); } .inspection-header { padding: 14px; } .inspection-body { padding: 10px; } .inspection-section { padding: 14px; } .check-item { grid-template-columns: 39px minmax(0, 1fr); align-items: start; } .check-control { grid-column: 2; } .checklist-choice-group { margin-top: 8px; } .inspection-footer { padding: 12px; } }
    @media (max-width: 359px) { .check-item { padding: 11px; gap: 9px; } .checklist-choice { font-size: 11px; } }
  </style>
  <script>
    // Converts the main checklist's checkboxes into explicit Good and No Good controls while preserving submitted field names.
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('inspectionChecklistForm');
      const statusBadge = document.getElementById('inspectionConditionStatus');
      const message = document.getElementById('checklistValidationMessage');
      if (!form || !statusBadge || !message) return;

      // Updates Overall FE Condition from Pressure, Hose, Nozzle, and Cylinder Body only. Any No Good makes it No Good; Good requires all four, and every other checklist item is excluded.
      function updateInspectionStatus() {
        const values = Array.from(form.querySelectorAll('.checklist-value'));
        const unanswered = values.some(function (input) { return input.value === ''; });
        const criticalValues = ['is_pressure_ok', 'is_hose_ok', 'is_nozzle_ok', 'is_cylinder_body_ok'].map(function (field) {
          return form.querySelector('.checklist-value[name="' + field + '"]');
        });
        const allCriticalGood = criticalValues.every(function (input) { return input && input.value === '1'; });
        statusBadge.className = 'condition-badge ' + (unanswered ? 'pending' : allCriticalGood ? 'good' : 'no-good');
        statusBadge.innerHTML = unanswered ? '<i class="bi bi-hourglass-split"></i> Not answered' : allCriticalGood ? '<i class="bi bi-check-circle-fill"></i> Good' : '<i class="bi bi-x-circle-fill"></i> No Good';
      }

      // Stores one explicit Good or No Good value, updates its buttons, and refreshes the Overall Condition badge.
      function selectChecklistValue(item, source, value) {
        const valueInput = item.querySelector('.checklist-value');
        valueInput.value = value;
        source.checked = value === '1';
        item.classList.remove('is-unanswered');
        item.querySelectorAll('.checklist-choice').forEach(function (button) {
          const selected = button.dataset.value === value;
          button.classList.toggle('is-selected', selected);
          button.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });
        message.classList.add('d-none');
        updateInspectionStatus();
      }

      // Builds Good and No Good buttons for each checklist component without changing its backend field name.
      form.querySelectorAll('.check-control .form-check-input').forEach(function (source) {
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
        [['1', 'Good', 'good-choice'], ['0', 'No Good', 'no-good-choice']].forEach(function (choice) {
          const button = document.createElement('button');
          button.type = 'button';
          button.dataset.value = choice[0];
          button.className = 'checklist-choice ' + choice[2];
          button.textContent = choice[1];
          button.setAttribute('aria-pressed', 'false');
          button.addEventListener('click', function () { selectChecklistValue(item, source, choice[0]); });
          group.appendChild(button);
        });
        source.parentElement.append(valueInput, group);
      });

      // Clears selections and validation feedback whenever a newly scanned extinguisher opens the main checklist.
      document.getElementById('inspectionChecklistModal').addEventListener('show.bs.modal', function () {
        form.querySelectorAll('.checklist-value').forEach(function (input) { input.value = ''; });
        form.querySelectorAll('.checklist-source').forEach(function (input) { input.checked = false; });
        form.querySelectorAll('.checklist-choice').forEach(function (button) { button.classList.remove('is-selected'); button.setAttribute('aria-pressed', 'false'); });
        form.querySelectorAll('.check-item').forEach(function (item) { item.classList.remove('is-unanswered'); });
        message.classList.add('d-none');
        updateInspectionStatus();
      });

      // Blocks incomplete submissions, highlights unanswered components, and focuses the first missing selection.
      form.addEventListener('submit', function (event) {
        const unanswered = Array.from(form.querySelectorAll('.checklist-value')).filter(function (input) { return input.value === ''; });
        if (!unanswered.length) return;
        event.preventDefault();
        unanswered.forEach(function (input) { input.closest('.check-item').classList.add('is-unanswered'); });
        message.textContent = 'Complete all checklist items by selecting Good or No Good before submitting.';
        message.classList.remove('d-none');
        const firstItem = unanswered[0].closest('.check-item');
        firstItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstItem.querySelector('.checklist-choice').focus();
      });

      updateInspectionStatus();
    });
  </script>
  <?php include_once 'notification/session_timeout.php'; ?>

</body>

</html>
