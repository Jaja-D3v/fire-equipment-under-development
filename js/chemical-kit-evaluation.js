function setChemicalKitEvaluationActionsVisible(isPending) {
  const approveForm = document.getElementById("cskApproveInspectionForm");
  const rejectForm = document.getElementById("cskRejectInspectionForm");
  if (approveForm) approveForm.hidden = !isPending;
  if (rejectForm) rejectForm.hidden = !isPending;
  const reasonField = document.getElementById("cskEvaluationRejectionReason");
  if (reasonField) reasonField.disabled = !isPending;
}

function renderChemicalKitMaterials(materials) {
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

  const results = [];
  const warnings = [];
  materials.forEach(function (material) {
    const row = document.createElement("tr");
    const requiredValue =
      material.required_stock === null || material.required_stock === undefined
        ? String(material.required_stock_message || "").indexOf(
            "not configured",
          ) !== -1
          ? "Not configured"
          : "Unavailable"
        : String(material.required_stock);
    const evaluationResult = String(
      material.evaluation_result || "Unavailable",
    );
    if (evaluationResult === "Unavailable") {
      warnings.push(
        (material.material_name || "A material") +
          ": " +
          (material.required_stock_message ||
            "Stock comparison is unavailable."),
      );
    }
    results.push(evaluationResult);

    [
      material.material_name,
      material.current_stocks,
      requiredValue,
      evaluationResult,
      material.remarks,
    ].forEach(function (value, index) {
      const cell = document.createElement("td");
      cell.textContent =
        value === null || value === undefined || String(value).trim() === ""
          ? "N/A"
          : String(value);
      if (index === 3) {
        cell.className =
          evaluationResult === "Good"
            ? "text-success fw-semibold"
            : evaluationResult === "No Good"
              ? "text-danger fw-semibold"
              : "text-warning-emphasis fw-semibold";
      } else if (
        index === 2 &&
        requiredValue !== "Not configured" &&
        requiredValue !== "Unavailable"
      ) {
        cell.className = "fw-semibold";
      }
      row.appendChild(cell);
    });
    body.appendChild(row);
  });

  const allGood =
    results.length === 8 &&
    results.every(function (result) {
      return result === "Good";
    });
  const anyNoGood = results.some(function (result) {
    return result === "No Good";
  });
  const unavailable = results.some(function (result) {
    return result === "Unavailable";
  });
  overall.textContent = allGood
    ? "Good"
    : anyNoGood
      ? "No Good"
      : unavailable
        ? "Unavailable"
        : "N/A";
  overall.className =
    "badge rounded-pill px-3 py-2 " +
    (allGood
      ? "bg-success-subtle text-success-emphasis"
      : anyNoGood
        ? "bg-danger-subtle text-danger-emphasis"
        : "bg-warning-subtle text-warning-emphasis");
  if (warning) {
    warning.textContent = Array.from(new Set(warnings)).join(" ");
    warning.classList.toggle("d-none", warnings.length === 0);
  }
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
    renderChemicalKitMaterials(inspection.materials);

    const isPending =
      String(inspection.evaluation_status || "").toLowerCase() === "pending";
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
