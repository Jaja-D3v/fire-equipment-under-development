<div class="modal fade" id="chemicalKitEvaluationModal" tabindex="-1" aria-labelledby="chemicalKitEvaluationTitle" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header px-4 py-3 border-bottom">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-primary-subtle text-primary rounded-3 p-3"><i class="bi bi-box-seam fs-4"></i></div>
          <div>
            <h5 class="modal-title fw-bold mb-1" id="chemicalKitEvaluationTitle">Chemical Spill Kit Evaluation</h5>
            <small class="text-body-secondary">Review the inspection details and material checklist.</small>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-4 py-4">
        <div id="chemicalKitEvaluationMessage" class="alert alert-info" role="status">Loading inspection details...</div>
        <section class="border rounded-4 p-4 mb-4">
          <h6 class="fw-bold mb-3">Inspection Information</h6>
          <div class="row g-3">
            <div class="col-12 col-md-4"><label class="form-label fw-semibold">Kit Code</label><input class="form-control" id="cskEvalCode" readonly></div>
            <div class="col-12 col-md-4"><label class="form-label fw-semibold">Location</label><input class="form-control" id="cskEvalLocation" readonly></div>
            <div class="col-12 col-md-4"><label class="form-label fw-semibold">Branch</label><input class="form-control" id="cskEvalBranch" readonly></div>
            <div class="col-12 col-md-4"><label class="form-label fw-semibold">Inspected By</label><input class="form-control" id="cskEvalInspector" readonly></div>
            <div class="col-12 col-md-4"><label class="form-label fw-semibold">Inspection Date</label><input class="form-control" id="cskEvalDate" readonly></div>
            <div class="col-12 col-md-4"><label class="form-label fw-semibold">Evaluation Status</label><input class="form-control" id="cskEvalStatus" readonly></div>
            <div class="col-12 col-md-6"><label class="form-label fw-semibold">Reviewed By</label><input class="form-control" id="cskEvalReviewer" readonly></div>
            <div class="col-12 col-md-6"><label class="form-label fw-semibold">Reviewed At</label><input class="form-control" id="cskEvalReviewedAt" readonly></div>
          </div>
        </section>
        <section class="border rounded-4 p-4 mb-4">
          <div id="cskRequiredStockWarning" class="alert alert-warning d-none" role="status"></div>
          <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div><h6 class="fw-bold mb-1">Material Checklist</h6><small class="text-body-secondary">Stock, condition, and inspector remarks.</small></div>
            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis px-3 py-2" id="cskEvalOverallStatus">N/A</span>
          </div>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead><tr><th>Material Name</th><th>Current Stock</th><th>Required Stock</th><th>Evaluation Result</th><th>Remarks</th></tr></thead>
              <tbody id="cskEvalMaterials"><tr><td colspan="5" class="text-body-secondary">Loading checklist...</td></tr></tbody>
            </table>
          </div>
        </section>
        <section class="border rounded-4 p-4">
          <label class="form-label fw-semibold" for="cskEvaluationRejectionReason">Rejection Reason</label>
          <textarea class="form-control" id="cskEvaluationRejectionReason" name="rejection_reason" form="cskRejectInspectionForm" rows="3" maxlength="1000" placeholder="Required when rejecting this inspection."></textarea>
        </section>
      </div>
      <div class="modal-footer px-4 py-3 border-top">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <form action="backend/controller/ChemicalSpillKitInspectionController.php" method="POST" id="cskApproveInspectionForm" hidden onsubmit="return confirmChemicalKitEvaluation('Approved');">
          <input type="hidden" name="action" value="evaluate_inspection">
          <input type="hidden" name="evaluation_status" value="Approved">
          <input type="hidden" name="inspection_id" class="csk-evaluation-inspection-id" value="">
          <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Approve</button>
        </form>
        <form action="backend/controller/ChemicalSpillKitInspectionController.php" method="POST" id="cskRejectInspectionForm" hidden onsubmit="return confirmChemicalKitEvaluation('Rejected');">
          <input type="hidden" name="action" value="evaluate_inspection">
          <input type="hidden" name="evaluation_status" value="Rejected">
          <input type="hidden" name="inspection_id" class="csk-evaluation-inspection-id" value="">
          <button type="submit" class="btn btn-danger"><i class="bi bi-x-lg me-1"></i>Reject</button>
        </form>
      </div>
    </div>
  </div>
</div>
