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
                 <button
                     type="button"
                     class="btn-close"
                     data-bs-dismiss="modal"
                     aria-label="Close">
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
                     data-bs-dismiss="modal">
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