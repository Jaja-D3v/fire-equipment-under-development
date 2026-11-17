
let activeInspectionId = null;
let inspectionRequestGeneration = 0;
let inspectionCanApprove = false;
let activeInspectionPending = false;
let activeInspectionCanCorrect = false;
const inspectionSavingIds = new Set();

function updateInspectionDecisionState() {
    const saving = inspectionSavingIds.has(activeInspectionId);
    const approve = document.querySelector('#approveInspectionForm button[type="submit"]');
    const reject = document.querySelector('#rejectInspectionForm button[type="submit"]');
    if (approve) approve.disabled = saving || !inspectionCanApprove;
    if (reject) reject.disabled = saving || !activeInspectionPending;
    document.querySelectorAll('.so-item-override input').forEach((input) => {
        input.disabled = saving || !activeInspectionCanCorrect;
    });
}

const inspectionModalElement = document.getElementById('viewInspectionModal');
['hide.coreui.modal', 'hide.bs.modal'].forEach((eventName) => {
    inspectionModalElement?.addEventListener(eventName, () => {
        activeInspectionId = null;
        inspectionRequestGeneration++;
        inspectionCanApprove = false;
        activeInspectionPending = false;
        activeInspectionCanCorrect = false;
        updateInspectionDecisionState();
    });
});

async function viewInspection(inspectId, extinguisherCode) {

    const requestedId = Number(inspectId);
    const generation = ++inspectionRequestGeneration;
    activeInspectionId = requestedId;
    inspectionCanApprove = false;
    activeInspectionPending = false;
    activeInspectionCanCorrect = false;
    updateInspectionDecisionState();

    try {
        const response = await fetch(`backend/controller/InspectionController.php?action=get&id=${requestedId}`);

        const data = await response.json();

        if (activeInspectionId !== requestedId || generation !== inspectionRequestGeneration) {
            return;
        }

        if (!data.success || !data.inspection) {

            alert("Unable to load inspection details.");
            return;

        }

        const inspection = data.inspection;

        /*  INSPECTION IDS  */
        const approveInspectId = document.getElementById("approveInspectId");
        const rejectInspectId = document.getElementById("rejectInspectId");


        if (approveInspectId) {

            approveInspectId.value = requestedId;
        }

        if (rejectInspectId) {

            rejectInspectId.value = requestedId;
        }

        const approveExtinguisherCode = document.getElementById("approveExtinguisherCode");
        const rejectExtinguisherCode = document.getElementById("rejectExtinguisherCode");

        if (approveExtinguisherCode) {
            approveExtinguisherCode.value = extinguisherCode || "";
        }

        if (rejectExtinguisherCode) {
            rejectExtinguisherCode.value = extinguisherCode || "";
        }

        /*  BASIC INFORMATION  */
        document.getElementById("viewExtinguisherCode").value = inspection.extinguisher_code || "—";
        document.getElementById("viewInspectionLocation").value = inspection.location || "—";
        document.getElementById("viewInspectionCapacity").value = inspection.capacity || "—";
        document.getElementById("viewInspectionType").value = inspection.type || "—";
        document.getElementById("viewInspectionClass").value = inspection.class || "—";
        document.getElementById("viewInspectedBy").value = inspection.inspected_by || "—";
        document.getElementById("viewVerifiedAndApprovedBy").value = inspection.verified_and_approved_by || "—";
        document.getElementById("viewActionTaken").value = inspection.action_taken || "—";

        /* REMARKS */
        document.getElementById("viewRemarks").value = inspection.remarks || "";

        /* DATE INSPECTED */
        if (inspection.date_inspected) {

            const date = new Date(inspection.date_inspected);

            if (!isNaN(date.getTime())) {

                document.getElementById(
                    "viewDateInspected"
                ).value =
                    date.toLocaleDateString(
                        "en-US",
                        {
                            year: "numeric",
                            month: "long",
                            day: "numeric"
                        }
                    );

            } else {
                document.getElementById("viewDateInspected").value = inspection.date_inspected;
            }

        } else {
            document.getElementById("viewDateInspected").value = "—";
        }

        /*  EVALUATION STATUS  */

        const status = inspection.evaluation_status || "Pending";
        const statusContainer = document.getElementById("viewEvaluationStatus");

        let statusClass = "bg-warning-subtle text-warning-emphasis";
        let statusIcon = "bi-clock";
        let statusLabel = "Pending Approval";

        if (status === "Approved") {

            statusClass = "bg-success-subtle text-success";
            statusIcon = "bi-check-circle-fill";
            statusLabel = "Approved";

        } else if (status === "Rejected") {

            statusClass = "bg-danger-subtle text-danger";
            statusIcon = "bi-x-circle-fill";
            statusLabel = "Rejected";

        }

        statusContainer.innerHTML = `

            <span
                class="badge rounded-pill ${statusClass} px-3 py-2">
                <i
                    class="bi ${statusIcon} me-1">
                </i>
                ${statusLabel}
            </span>
        `;

        /* CORRECTIVE ACTION */
        const actionSelect = document.getElementById("action_taken");
        const otherAction = document.getElementById("other_action");
        const targetDate = document.getElementById("viewTargetDate");

        if (actionSelect) {
            const savedAction = inspection.action_taken || "";

            const standardActions = [
                "Refill",
                "Replacement of Parts",
                "Replacement of Unit"
            ];

            if (standardActions.includes(savedAction)) {
                actionSelect.value = savedAction;

                if (otherAction) {
                    otherAction.value = "";
                    otherAction.classList.add("d-none");
                    otherAction.style.display = "none";
                    otherAction.required = false;
                }
            } else if (savedAction) {
                actionSelect.value = "Others";

                if (otherAction) {
                    otherAction.value = savedAction;
                    otherAction.classList.remove("d-none");
                    otherAction.style.display = "block";
                    otherAction.required = true;
                }
            } else {
                actionSelect.value = "";
            }
        }

        if (targetDate) {
            targetDate.value =
                inspection.target_date_of_implementation || "";
        }

        /*  CHECKLIST STATUS  */
        updateChecklistStatus("Seal", inspection.is_seal_ok);
        updateChecklistStatus("CleaningOfUnit", inspection.is_cleaning_of_unit_ok);
        updateChecklistStatus("Pin", inspection.is_pin_ok);
        updateChecklistStatus("Pressure", inspection.is_pressure_ok);
        updateChecklistStatus("Hose", inspection.is_hose_ok);
        updateChecklistStatus("Nozzle", inspection.is_nozzle_ok);
        updateChecklistStatus("Belt", inspection.is_belt_ok);
        updateChecklistStatus("CylinderBody", inspection.is_cylinder_body_ok);
        updateChecklistStatus("DemarcationLine", inspection.is_demarcation_line_ok);
        updateChecklistStatus("Signage", inspection.is_signage_ok);

        renderInspectionItemOverrides(inspection, data.correction);

        /*  OVERALL CONDITION   */

        const checklistFields = [

            // inspection.is_seal_ok,
            // inspection.is_pin_ok,
            inspection.is_pressure_ok,
            inspection.is_hose_ok,
            inspection.is_nozzle_ok,
            // inspection.is_belt_ok,
            inspection.is_cylinder_body_ok,
            // inspection.is_demarcation_line_ok,
            // inspection.is_signage_ok,
            // inspection.is_cleaning_of_unit_ok

        ];

        const allGood =
            checklistFields.every(
                value =>
                    value == 1 ||
                    value === true ||
                    value === "1"
            );

        const conditionStatus = document.getElementById("viewInspectionConditionStatus");

        if (conditionStatus) {

            if (allGood) {

                conditionStatus.className = "badge bg-success ms-auto";
                conditionStatus.textContent = "Good";

            } else {

                conditionStatus.className = "badge bg-danger ms-auto";
                conditionStatus.textContent = "No Good";

            }

        }

        /* SHOW MODAL */
        const modalElement = document.getElementById("viewInspectionModal");
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        modal.show();

    } catch (error) {

        if (activeInspectionId !== requestedId || generation !== inspectionRequestGeneration) {
            return;
        }

        console.error("Error loading inspection:", error);
        alert("An error occurred while loading the inspection.");

    }

}

function refreshChecklistAfterCorrection(inspection, correction) {
    updateChecklistStatus("Seal", inspection.is_seal_ok);
    updateChecklistStatus("CleaningOfUnit", inspection.is_cleaning_of_unit_ok);
    updateChecklistStatus("Pin", inspection.is_pin_ok);
    updateChecklistStatus("Pressure", inspection.is_pressure_ok);
    updateChecklistStatus("Hose", inspection.is_hose_ok);
    updateChecklistStatus("Nozzle", inspection.is_nozzle_ok);
    updateChecklistStatus("Belt", inspection.is_belt_ok);
    updateChecklistStatus("CylinderBody", inspection.is_cylinder_body_ok);
    updateChecklistStatus("DemarcationLine", inspection.is_demarcation_line_ok);
    updateChecklistStatus("Signage", inspection.is_signage_ok);

    const allGood = [
        inspection.is_pressure_ok,
        inspection.is_hose_ok,
        inspection.is_nozzle_ok,
        inspection.is_cylinder_body_ok
    ].every((value) => value == 1 || value === true || value === "1");
    const conditionStatus = document.getElementById("viewInspectionConditionStatus");
    if (conditionStatus) {
        conditionStatus.className = allGood ? "badge bg-success ms-auto" : "badge bg-danger ms-auto";
        conditionStatus.textContent = allGood ? "Good" : "No Good";
    }
    renderInspectionItemOverrides(inspection, correction);
}

function renderInspectionItemOverrides(inspection, correction) {
    const modal = document.getElementById("viewInspectionModal");
    if (!modal) return;

    modal.querySelectorAll(".so-item-override").forEach((element) => element.remove());
    const pending = inspection.evaluation_status === "Pending";
    activeInspectionPending = pending;
    activeInspectionCanCorrect = pending && !!correction?.can_correct;
    const errors = correction?.validation_errors || [];
    inspectionCanApprove = pending && !!correction?.can_approve && !correction?.error && !errors.length;

    const section = document.getElementById("viewInspectionConditionStatus")?.closest(".border.rounded-4");
    let warning = document.getElementById("inspectionIntegrityWarning");
    if (!warning && section) {
        warning = document.createElement("div");
        warning.id = "inspectionIntegrityWarning";
        warning.setAttribute("role", "alert");
        section.prepend(warning);
    }
    if (warning) {
        const message = errors.length ? errors.join(" ") : correction?.error || "";
        warning.textContent = message ? `Inspection integrity warning: ${message}` : "";
        warning.className = message ? "alert alert-danger" : "d-none";
    }

    ["action_taken", "other_action", "viewTargetDate", "rejectReason"].forEach((id) => {
        const input = document.getElementById(id);
        if (input) input.disabled = !pending;
    });
    if (!correction) {
        updateInspectionDecisionState();
        return;
    }

    Object.entries(correction.fields).forEach(([field, suffix]) => {
        const resultText = document.getElementById(`view${suffix}Text`);
        if (!resultText) return;
        const tracked = correction.corrected.includes(field);
        const corrected = tracked && String(inspection[field]) === "1";
        if (!tracked && String(inspection[field]) === "1") return;

        const wrapper = document.createElement("div");
        wrapper.className = "so-item-override border-top mt-2 pt-2";
        resultText.closest(".h-100")?.append(wrapper);
        const group = document.createElement("div");
        group.className = "form-check";
        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.className = "form-check-input";
        checkbox.id = `so-corrected-${field}`;
        checkbox.checked = corrected;
        const label = document.createElement("label");
        label.htmlFor = checkbox.id;
        label.className = "form-check-label";
        label.textContent = "Corrected by Safety Officer";
        group.append(checkbox, label);
        wrapper.append(group);

        if (corrected) {
            const badge = document.createElement("span");
            badge.className = "badge bg-info-subtle text-info-emphasis mt-2";
            badge.textContent = "SO corrected";
            wrapper.append(badge);
        }

        const feedback = document.createElement("small");
        feedback.className = "d-block mt-1 text-body-secondary";
        feedback.setAttribute("role", "status");
        wrapper.append(feedback);
        checkbox.addEventListener("change", async () => {
            const recordId = Number(inspection.inspect_id);
            if (inspectionSavingIds.has(recordId)) {
                checkbox.checked = corrected;
                return;
            }

            inspectionSavingIds.add(recordId);
            updateInspectionDecisionState();
            feedback.textContent = "Saving...";
            try {
                const body = new URLSearchParams({
                    action: "correct_inspection_item",
                    inspect_id: inspection.inspect_id,
                    checklist_item: field,
                    checked: checkbox.checked ? "1" : "0",
                    csrf_token: correction.csrf_token
                });
                const response = await fetch("backend/controller/InspectionController.php", { method: "POST", body });
                const result = await response.json();
                if (!result.success) throw new Error(result.message);
                if (activeInspectionId !== recordId) return;

                const generation = inspectionRequestGeneration;
                const detailResponse = await fetch(`backend/controller/InspectionController.php?action=get&id=${recordId}`);
                const fresh = await detailResponse.json();
                if (activeInspectionId !== recordId || generation !== inspectionRequestGeneration) return;
                if (!fresh.success || !fresh.inspection) throw new Error("Saved, but unable to reload. Reopen this inspection.");
                refreshChecklistAfterCorrection(fresh.inspection, fresh.correction);
            } catch (error) {
                if (activeInspectionId !== recordId) return;
                checkbox.checked = corrected;
                feedback.textContent = error.message || "Unable to save correction. Reopen this inspection.";
                feedback.className = "d-block mt-1 text-danger";
                inspectionCanApprove = false;
            } finally {
                inspectionSavingIds.delete(recordId);
                updateInspectionDecisionState();
            }
        });
    });
    updateInspectionDecisionState();
}

/* UPDATE CHECKLIST ITEM */
function updateChecklistStatus(
    itemName,
    value
) {

    const textElement = document.getElementById(`view${itemName}Text`);
    const statusElement = document.getElementById(`view${itemName}Status`);


    if (!textElement || !statusElement) {
        return;
    }

    const isGood =
        value == 1 ||
        value === true ||
        value === "1";


    if (isGood) {

        textElement.textContent = "Good";
        textElement.className = "text-success fw-semibold";
        statusElement.className = "fs-4 text-success";
        statusElement.innerHTML = '<i class="bi bi-check-circle-fill"></i>';

    } else {

        textElement.textContent = "No Good";
        textElement.className = "text-danger fw-semibold";
        statusElement.className = "fs-4 text-danger";
        statusElement.innerHTML = '<i class="bi bi-x-circle-fill"></i>';

    }

}

// for others selection 

document.addEventListener("change", function (event) {
    if (event.target.id !== "action_taken") {
        return;
    }

    const actionTaken = event.target;
    const container = actionTaken.closest(".col-12");
    const otherAction = container?.querySelector(
        'input[name="other_action"]'
    );

    if (!otherAction) {
        console.error("Hindi makita ang input[name='other_action'].");
        return;
    }

    if (actionTaken.value === "Others") {
        otherAction.classList.remove("d-none");
        otherAction.style.display = "block";
        otherAction.required = true;
        otherAction.focus();
    } else {
        otherAction.classList.add("d-none");
        otherAction.style.display = "none";
        otherAction.required = false;
        otherAction.value = "";
    }
});

/* SET CORRECTIVE ACTION VALUES */
function setCorrectiveActionValues(type) {
    const conditionStatus = document.getElementById(
        "viewInspectionConditionStatus"
    );

    const conditionValue = conditionStatus
        ? conditionStatus.textContent.trim()
        : "";

    // Correct IDs from Section 3
    const actionSelect = document.getElementById("action_taken");
    const otherAction = document.getElementById("other_action");
    const targetDate = document.getElementById("viewTargetDate");

    if (!actionSelect || !targetDate) {
        alert("Corrective Action fields are missing.");
        return false;
    }

    let actionValue = actionSelect.value;
    let customAction = "";

 

    // Handle Others
    if (actionValue === "Others") {
        customAction = otherAction ? otherAction.value.trim() : "";

        if (!customAction) {
            alert("Please specify the other action.");

            if (otherAction) {
                otherAction.focus();
            }

            return false;
        }

        // Save the custom text as action_taken
        actionValue = customAction;
    }

    // APPROVE
    if (type === "approve") {
        const approveAction = document.getElementById("approveActionTaken");
        const approveOther = document.getElementById("approveOtherAction");
        const approveDate = document.getElementById("approveTargetDate");
        const approveCondition = document.getElementById("approveConditionStatus");
        const approveRemarks = document.getElementById("approveRemarks");

        if (approveAction) approveAction.value = actionValue;
        if (approveOther) approveOther.value = customAction;
        if (approveDate) approveDate.value = targetDate.value;
        if (approveCondition) approveCondition.value = conditionValue;

        if (approveRemarks) {
            const remarks = document.getElementById("viewRemarks");
            approveRemarks.value = remarks ? remarks.value.trim() : "";
        }
    }

    // REJECT
    if (type === "reject") {
        const rejectAction = document.getElementById("rejectActionTaken");
        const rejectOther = document.getElementById("rejectOtherAction");
        const rejectDate = document.getElementById("rejectTargetDate");
        const rejectCondition = document.getElementById("rejectConditionStatus");

        if (rejectAction) rejectAction.value = actionValue;
        if (rejectOther) rejectOther.value = customAction;
        if (rejectDate) rejectDate.value = targetDate.value;
        if (rejectCondition) rejectCondition.value = conditionValue;
    }

    return true;
}

/*  APPROVE CONFIRMATION */
function confirmApprove() {

    if (inspectionSavingIds.has(activeInspectionId)) {
        alert("Wait for the correction to finish saving.");
        return false;
    }
    if (!inspectionCanApprove) {
        alert("This inspection has integrity errors and cannot be approved.");
        return false;
    }

    if (!setCorrectiveActionValues("approve")) {
        return false;
    }

    return confirm("Are you sure you want to approve this inspection?\n\n" +
        "This action will change the inspection status to Approved.");
}

/* REJECT CONFIRMATION */

function confirmReject() {

    if (inspectionSavingIds.has(activeInspectionId)) {
        alert("Wait for the correction to finish saving.");
        return false;
    }

    const rejectReason = document.getElementById("rejectReason");
    const rejectReasonInput = document.getElementById("rejectReasonInput");

    if (!rejectReason || !rejectReason.value.trim()) {
        alert("Please provide a reason for rejecting this inspection.");

        if (rejectReason) {
            rejectReason.focus();
        }

        return false;
    }

    if (rejectReasonInput) {
        rejectReasonInput.value = rejectReason.value.trim();
    }

    if (!setCorrectiveActionValues("reject")) {
        return false;
    }

    return confirm(
        "Are you sure you want to reject this inspection?\n\n" +
        "This action will change the inspection status to Rejected."
    );
}

// for search ng approve pending na module 
const inspectionSearch = document.getElementById("inspectionSearch");

if (inspectionSearch && inspectionSearch.dataset.serverSearch === "true") {
    inspectionSearch.addEventListener("input", function () {
        if (this.value.trim() !== "") {
            return;
        }

        const url = new URL(window.location.href);
        url.searchParams.delete("search");
        url.searchParams.set("page", "1");

        if (url.href !== window.location.href) {
            window.location.href = url.toString();
        }
    });
} else if (inspectionSearch) {
    inspectionSearch.addEventListener("input", function () {
        const searchValue = this.value.trim().toLowerCase();
        const rows = document.querySelectorAll("#inspectionList .inspection-row");
        const noSearchResult = document.getElementById("noSearchResult");

        let visibleRows = 0;

        rows.forEach(function (row) {
            const feCode = row.dataset.feCode || "";
            const location = row.dataset.location || "";
            const matches = feCode.includes(searchValue) || location.includes(searchValue);

            row.style.display = matches ? "" : "none";

            if (matches) {
                visibleRows++;
            }
        });

        if (noSearchResult) {
            noSearchResult.style.display =
                visibleRows === 0 ? "" : "none";
        }
    });
}
