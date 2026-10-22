
async function viewInspection(inspectId, extinguisherCode) {

    try {
        const response = await fetch(`backend/controller/InspectionController.php?action=get&id=${inspectId}`);

        const data = await response.json();

        if (!data.success || !data.inspection) {

            alert("Unable to load inspection details.");
            return;

        }

        const inspection = data.inspection;

        /*  INSPECTION IDS  */
        const approveInspectId = document.getElementById("approveInspectId");
        const rejectInspectId = document.getElementById("rejectInspectId");


        if (approveInspectId) {

            approveInspectId.value = inspectId;
        }

        if (rejectInspectId) {

            rejectInspectId.value = inspectId;
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

        console.error("Error loading inspection:", error);
        alert("An error occurred while loading the inspection.");

    }

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

    if (!setCorrectiveActionValues("approve")) {
        return false;
    }

    return confirm("Are you sure you want to approve this inspection?\n\n" +
        "This action will change the inspection status to Approved.");
}

/* REJECT CONFIRMATION */

function confirmReject() {

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
