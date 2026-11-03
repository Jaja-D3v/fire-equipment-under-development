(function () {
  "use strict";

  const panel = document.getElementById("maintenance-category-required_stock");
  if (!panel) return;

  const endpoint = "backend/controller/RequiredStockManagementController.php";
  const equipment = document.getElementById("required-stock-equipment");
  const branch = document.getElementById("required-stock-branch");
  const search = document.getElementById("required-stock-search");
  const rows = document.getElementById("required-stock-rows");
  const feedback = document.getElementById("required-stock-feedback");
  const count = document.getElementById("required-stock-count");
  let materials = [];
  let loading = false;
  let saving = false;

  function showMessage(message, type) {
    feedback.textContent = message;
    feedback.className = "alert alert-" + type;
  }

  function clearMessage() {
    feedback.textContent = "";
    feedback.className = "alert d-none";
  }

  function cell(text, className) {
    const element = document.createElement("td");
    if (className) element.className = className;
    element.textContent = text;
    return element;
  }

  function renderRows() {
    rows.replaceChildren();
    const term = search.value.trim().toLocaleLowerCase();
    const filtered = materials.filter(function (material) {
      return String(material.material_name || "")
        .toLocaleLowerCase()
        .includes(term);
    });
    count.textContent = materials.length
      ? filtered.length + " of " + materials.length + " materials"
      : "";

    if (!filtered.length) {
      const tr = document.createElement("tr");
      const td = cell(
        materials.length
          ? "No materials match your search."
          : "No required-stock records are configured for this branch.",
        "text-center text-body-secondary py-5",
      );
      td.colSpan = 4;
      tr.appendChild(td);
      rows.appendChild(tr);
      return;
    }

    filtered.forEach(function (material) {
      const tr = document.createElement("tr");
      tr.dataset.materialId = String(material.material_id);
      tr.appendChild(
        cell(material.material_name || "Unnamed material", "ps-3 fw-medium"),
      );

      const quantityCell = document.createElement("td");
      quantityCell.className = "required-stock-value";
      quantityCell.textContent = String(material.required_stock);
      tr.appendChild(quantityCell);

      const timestamp = material.updated_at
        ? new Date(String(material.updated_at).replace(" ", "T"))
        : null;
      tr.appendChild(
        cell(
          timestamp && !Number.isNaN(timestamp.getTime())
            ? timestamp.toLocaleString()
            : "—",
          "small text-body-secondary",
        ),
      );

      const actionCell = document.createElement("td");
      actionCell.className = "text-end pe-3";
      const editButton = document.createElement("button");
      editButton.type = "button";
      editButton.className = "btn btn-sm btn-outline-primary rounded-pill px-3";
      editButton.dataset.action = "edit";
      editButton.textContent = "Edit";
      actionCell.appendChild(editButton);
      tr.appendChild(actionCell);
      rows.appendChild(tr);
    });
  }

  async function loadMaterials() {
    const selectedBranch = branch.value;
    materials = [];
    clearMessage();
    if (!selectedBranch) {
      rows.innerHTML =
        '<tr><td colspan="4" class="text-center text-body-secondary py-5">Choose a branch to view required stock.</td></tr>';
      count.textContent = "";
      return;
    }

    loading = true;
    rows.innerHTML =
      '<tr><td colspan="4" class="text-center text-body-secondary py-5"><span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Loading required stock…</td></tr>';
    try {
      const url = new URL(endpoint, window.location.href);
      url.searchParams.set("action", "list");
      url.searchParams.set("equipment", equipment.value);
      url.searchParams.set("branch", selectedBranch);
      const response = await fetch(url.toString(), {
        credentials: "same-origin",
        headers: { Accept: "application/json" },
      });
      const payload = await response.json();
      if (!response.ok || !payload.success)
        throw new Error(payload.message || "Unable to load required stock.");
      materials = Array.isArray(payload.materials) ? payload.materials : [];
      renderRows();
    } catch (error) {
      rows.innerHTML =
        '<tr><td colspan="4" class="text-center text-danger py-5">Required stock could not be loaded.</td></tr>';
      count.textContent = "";
      showMessage(error.message || "Unable to load required stock.", "danger");
    } finally {
      loading = false;
    }
  }

  function enterEdit(row) {
    if (row.dataset.editing === "true") return;
    row.dataset.editing = "true";
    const current = row.querySelector(".required-stock-value");
    const original = current.textContent.trim();
    current.textContent = "";
    const input = document.createElement("input");
    input.type = "number";
    input.min = "0";
    input.max = "4294967295";
    input.step = "1";
    input.inputMode = "numeric";
    input.className = "form-control form-control-sm";
    input.setAttribute("aria-label", "Required stock");
    input.value = original;
    current.appendChild(input);

    const action = row.querySelector("td:last-child");
    action.replaceChildren();
    const save = document.createElement("button");
    save.type = "button";
    save.className = "btn btn-sm btn-primary rounded-pill px-3 me-1";
    save.dataset.action = "save";
    save.textContent = "Save";
    const cancel = document.createElement("button");
    cancel.type = "button";
    cancel.className = "btn btn-sm btn-light rounded-pill px-3";
    cancel.dataset.action = "cancel";
    cancel.textContent = "Cancel";
    action.append(save, cancel);
    input.focus();
  }

  function cancelEdit(row) {
    row.dataset.editing = "false";
    renderRows();
  }

  async function saveRow(row, button) {
    if (saving) return;
    const input = row.querySelector("input");
    const stock = input ? input.value.trim() : "";
    if (!/^(0|[1-9][0-9]*)$/.test(stock) || Number(stock) > 4294967295) {
      showMessage(
        "Required stock must be a non-negative whole number.",
        "warning",
      );
      if (input) input.focus();
      return;
    }
    saving = true;
    button.disabled = true;
    clearMessage();
    const body = new URLSearchParams({
      action: "update",
      equipment: equipment.value,
      branch: branch.value,
      material_id: row.dataset.materialId,
      required_stock: stock,
    });
    try {
      const response = await fetch(endpoint, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
          Accept: "application/json",
        },
        body: body.toString(),
      });
      const payload = await response.json();
      if (!response.ok || !payload.success)
        throw new Error(payload.message || "Unable to save required stock.");
      await loadMaterials();
      showMessage(payload.message || "Required stock saved.", "success");
    } catch (error) {
      button.disabled = false;
      showMessage(error.message || "Unable to save required stock.", "danger");
    } finally {
      saving = false;
    }
  }

  rows.addEventListener("click", function (event) {
    const button = event.target.closest("button[data-action]");
    if (!button) return;
    const row = button.closest("tr");
    if (!row) return;
    if (button.dataset.action === "edit") enterEdit(row);
    if (button.dataset.action === "cancel") cancelEdit(row);
    if (button.dataset.action === "save") saveRow(row, button);
  });

  branch.addEventListener("change", loadMaterials);
  equipment.addEventListener("change", loadMaterials);
  search.addEventListener("input", renderRows);
  document
    .querySelectorAll('[data-category-tab="required_stock"]')
    .forEach(function (tab) {
      tab.addEventListener("click", function () {
        if (!loading) loadMaterials();
      });
    });

  if (!panel.classList.contains("d-none") && branch.value) loadMaterials();
})();
