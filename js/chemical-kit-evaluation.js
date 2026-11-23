function setChemicalKitEvaluationActionsVisible(isPending) {
  const approveForm = document.getElementById("cskApproveInspectionForm");
  const rejectForm = document.getElementById("cskRejectInspectionForm");
  if (approveForm) approveForm.hidden = !isPending;
  if (rejectForm) rejectForm.hidden = !isPending;
  const reasonField = document.getElementById("cskEvaluationRejectionReason");
  if (reasonField) reasonField.disabled = !isPending;
}

function renderChemicalKitMaterials(materials, canEdit) {
  const body = document.getElementById("cskEvalMaterials");
  const overall = document.getElementById("cskEvalOverallStatus");
  const warning = document.getElementById("cskRequiredStockWarning");
  if (!body || !overall) return;

  body.replaceChildren();
  if (!Array.isArray(materials) || materials.length === 0) {
    const row = document.createElement("tr");
    const cell = document.createElement("td");
    cell.colSpan = 5;
    cell.className = "text-body-secondary";
    cell.textContent = "No material checklist records are available.";
    row.appendChild(cell);
    body.appendChild(row);
    overall.textContent = "N/A";
    overall.className =
      "badge rounded-pill bg-secondary-subtle text-secondary-emphasis px-3 py-2";
    if (warning) {
      warning.textContent =
        "Material checklist data is unavailable, so required-stock results cannot be calculated.";
      warning.classList.remove("d-none");
    }
    return;
  }

  const materialStates = [];
  materials.forEach(function (material) {
    const row = document.createElement("tr");
    const requiredValue = material.required_stock === null || material.required_stock === undefined
      ? String(material.required_stock_message || "").indexOf("not configured") !== -1 ? "Not configured" : "Unavailable"
      : String(material.required_stock);
    const canEditMaterial = canEdit && material.status === "No Good";
    const materialIndex = String(materialStates.length);
    const nameCell = document.createElement("td");
    nameCell.textContent = material.material_name || "N/A";
    const stockCell = document.createElement("td");
    const requiredCell = document.createElement("td");
    requiredCell.textContent = requiredValue;
    if (requiredValue !== "Not configured" && requiredValue !== "Unavailable") requiredCell.className = "fw-semibold";
    const resultCell = document.createElement("td");
    const remarksCell = document.createElement("td");
    const materialName = document.createElement("input");
    materialName.type = "hidden";
    materialName.name = "materials[" + materialIndex + "][material_name]";
    materialName.value = String(material.material_name || "");
    materialName.setAttribute("form", "cskApproveInspectionForm");
    const stock = document.createElement("input");
    stock.type = "number";
    stock.className = "form-control form-control-sm";
    stock.name = "materials[" + materialIndex + "][current_stock]";
    stock.value = material.current_stocks === null || material.current_stocks === undefined ? "" : String(material.current_stocks);
    stock.min = "0";
    stock.max = "2147483647";
    stock.step = "1";
    stock.required = true;
    stock.inputMode = "numeric";
    stock.setAttribute("form", "cskApproveInspectionForm");
    stock.setAttribute("aria-label", "Current stock for " + (material.material_name || "material"));
    const remarks = document.createElement("textarea");
    remarks.className = "form-control form-control-sm";
    remarks.name = "materials[" + materialIndex + "][remarks]";
    remarks.value = material.remarks || "";
    remarks.maxLength = 65535;
    remarks.rows = 2;
    remarks.placeholder = "Enter remarks";
    remarks.setAttribute("form", "cskApproveInspectionForm");
    remarks.setAttribute("aria-label", "Remarks for " + (material.material_name || "material"));
    if (canEditMaterial) stockCell.append(materialName, stock);
    else {
      stock.disabled = true;
      stockCell.append(String(material.current_stocks ?? "N/A"));
    }
    row.append(nameCell, stockCell, requiredCell, resultCell, remarksCell);
    body.appendChild(row);
    materialStates.push({ material, stock, remarks, resultCell, remarksCell, canEdit: canEditMaterial });
  });

  function updateResults() {
    const results = [];
    const warnings = [];
    materialStates.forEach(function (state) {
      const stockValue = state.stock.value.trim();
      const required = Number(state.material.required_stock);
      const evaluationResult = /^[0-9]+$/.test(stockValue) && Number(stockValue) <= 2147483647 && Number.isFinite(required) && required >= 0
        ? (Number(stockValue) >= required ? "Good" : "No Good")
        : "Unavailable";
      results.push(evaluationResult);
      state.resultCell.textContent = evaluationResult;
      state.resultCell.className = evaluationResult === "Good" ? "text-success fw-semibold" : evaluationResult === "No Good" ? "text-danger fw-semibold" : "text-warning-emphasis fw-semibold";
      const remarksEligible = state.canEdit;
      state.remarksCell.replaceChildren();
      state.remarks.disabled = !remarksEligible;
      if (remarksEligible) state.remarksCell.append(state.remarks);
      else state.remarksCell.textContent = state.material.remarks ? String(state.material.remarks) : "N/A";
      if (evaluationResult === "Unavailable") warnings.push((state.material.material_name || "A material") + ": " + (state.material.required_stock_message || "Stock comparison is unavailable."));
    });
    const allGood = results.length > 0 && results.every(function (result) { return result === "Good"; });
    const anyNoGood = results.some(function (result) { return result === "No Good"; });
    overall.textContent = allGood ? "Good" : anyNoGood ? "No Good" : "Unavailable";
    overall.className = "badge rounded-pill px-3 py-2 " + (allGood ? "bg-success-subtle text-success-emphasis" : anyNoGood ? "bg-danger-subtle text-danger-emphasis" : "bg-warning-subtle text-warning-emphasis");
    if (warning) {
      warning.textContent = Array.from(new Set(warnings)).join(" ");
      warning.classList.toggle("d-none", warnings.length === 0);
    }
  }
  materialStates.forEach(function (state) { if (state.canEdit) state.stock.addEventListener("input", updateResults); });
  updateResults();
}

async function viewChemicalSpillKitEvaluation(inspectionId, status) {
  const modalElement = document.getElementById("chemicalKitEvaluationModal");
  const message = document.getElementById("chemicalKitEvaluationMessage");
  if (!modalElement || !message) return;

  const id = Number(inspectionId);
  document
    .querySelectorAll(".csk-evaluation-inspection-id")
    .forEach(function (input) {
      input.value = String(id);
    });
  setChemicalKitEvaluationActionsVisible(
    String(status || "").toLowerCase() === "pending",
  );
  message.className = "alert alert-info";
  message.textContent = "Loading inspection details...";
  bootstrap.Modal.getOrCreateInstance(modalElement).show();

  try {
    const params = new URLSearchParams({
      action: "getEvaluationDetails",
      id: String(id),
    });
    const response = await fetch(
      "backend/controller/ChemicalSpillKitInspectionController.php?" +
        params.toString(),
      {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      },
    );
    const result = await response.json();
    if (!response.ok || result.success !== true || !result.inspection) {
      throw new Error(
        result.message ||
          "Unable to load Chemical Spill Kit inspection details.",
      );
    }

    const inspection = result.inspection;
    document.getElementById("cskEvalCode").value =
      inspection.chemical_spill_kit_code || "N/A";
    document.getElementById("cskEvalLocation").value =
      inspection.location || "N/A";
    document.getElementById("cskEvalBranch").value = inspection.branch || "N/A";
    document.getElementById("cskEvalInspector").value =
      inspection.inspected_by || "N/A";
    document.getElementById("cskEvalDate").value =
      inspection.inspection_date || "N/A";
    document.getElementById("cskEvalStatus").value =
      inspection.evaluation_status || "Pending";
    document.getElementById("cskEvalReviewer").value =
      inspection.reviewed_by || "N/A";
    document.getElementById("cskEvalReviewedAt").value =
      inspection.reviewed_at || "N/A";
    const isPending =
      String(inspection.evaluation_status || "").toLowerCase() === "pending";
    renderChemicalKitMaterials(inspection.materials, isPending);
    setChemicalKitEvaluationActionsVisible(isPending);
    document.getElementById("cskEvaluationRejectionReason").value =
      inspection.rejection_reason || "";
    message.className = isPending ? "alert alert-warning" : "alert alert-light";
    message.textContent = isPending
      ? "This inspection is waiting for evaluation."
      : "This inspection has already been evaluated.";
  } catch (error) {
    message.className = "alert alert-danger";
    message.textContent =
      error.message || "Unable to load Chemical Spill Kit inspection details.";
    setChemicalKitEvaluationActionsVisible(false);
    console.error(
      "Chemical Spill Kit evaluation detail request failed:",
      error,
    );
  }
}

function confirmChemicalKitEvaluation(status) {
  if (status === "Rejected") {
    const reason = document.getElementById("cskEvaluationRejectionReason");
    if (!reason || !reason.value.trim()) {
      alert("Please provide a reason for rejecting this inspection.");
      if (reason) reason.focus();
      return false;
    }
  }
  return confirm(
    "Are you sure you want to " +
      status.toLowerCase() +
      " this Chemical Spill Kit inspection?",
  );
}
