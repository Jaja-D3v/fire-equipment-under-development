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
      td.colSpan = 5;
      tr.appendChild(td);
      rows.appendChild(tr);
      return;
    }

    filtered.forEach(function (material) {
      const tr = document.createElement("tr");
      tr.dataset.materialId = String(material.material_id);
      tr.dataset.materialName = String(material.material_name || '');
      tr.dataset.requiredStock = String(material.required_stock);
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
      tr.appendChild(cell(String(material.is_active) === '1' ? 'Active' : 'Inactive', String(material.is_active) === '1' ? 'text-success small fw-semibold' : 'text-secondary small fw-semibold'));

      const actionCell = document.createElement("td");
      actionCell.className = "text-end pe-3";
      if (String(material.is_active) === '1') {
        [['edit-material', 'Edit'], ['assign-material', 'Assign'], ['delete-material', 'Delete']].forEach(function (item) {
          const button = document.createElement('button'); button.type = 'button'; button.className = 'btn btn-sm ' + (item[0] === 'delete-material' ? 'btn-outline-danger' : 'btn-outline-primary') + ' ms-1'; button.dataset.action = item[0]; button.textContent = item[1]; actionCell.appendChild(button);
        });
      }
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
        '<tr><td colspan="5" class="text-center text-body-secondary py-5">Choose a branch to view required stock.</td></tr>';
      count.textContent = "";
      return;
    }

    loading = true;
    rows.innerHTML =
      '<tr><td colspan="5" class="text-center text-body-secondary py-5"><span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Loading required stock…</td></tr>';
    try {
      const url = new URL(endpoint, window.location.href);
      url.searchParams.set("action", "list");
      url.searchParams.set("equipment", equipment.value);
      url.searchParams.set("branch", selectedBranch);
      url.searchParams.set("include_retired", "1");
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
        '<tr><td colspan="5" class="text-center text-danger py-5">Required stock could not be loaded.</td></tr>';
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

// Additional branch-scoped material lifecycle controls for the Required Stock panel.
(function () {
  const endpoint = 'backend/controller/RequiredStockManagementController.php';
  const branch = document.getElementById('required-stock-branch');
  const equipment = document.getElementById('required-stock-equipment');
  const rows = document.getElementById('required-stock-rows');
  const add = document.getElementById('required-stock-add');
  const form = document.getElementById('required-stock-material-form');
  const modalNode = document.getElementById('required-stock-material-modal');
  if (!branch || !rows || !add || !form || !modalNode) return;
  const modal = new bootstrap.Modal(modalNode);
  const action = document.getElementById('required-stock-material-action');
  const id = document.getElementById('required-stock-material-id');
  const targetBranch = document.getElementById('required-stock-material-branch');
  const name = document.getElementById('required-stock-material-name');
  const quantity = document.getElementById('required-stock-material-quantity');
  const branches = document.getElementById('required-stock-branches');
  const branchWrap = document.getElementById('required-stock-branches-wrap');
  function choices(selected) { branches.replaceChildren(); Array.from(branch.options).filter(o => o.value).forEach(o => { const label = document.createElement('label'); label.className = 'form-check form-check-inline me-2'; const input = document.createElement('input'); input.type = 'checkbox'; input.className = 'form-check-input'; input.value = o.value; input.checked = selected.includes(o.value); label.append(input, document.createTextNode(' ' + o.textContent)); branches.append(label); }); }
  function open(kind, row) { if (!branch.value) return; form.reset(); action.value = kind; id.value = row ? row.dataset.materialId : ''; targetBranch.value = branch.value; name.value = row ? row.dataset.materialName : ''; quantity.value = row ? row.dataset.requiredStock : '0'; branchWrap.classList.toggle('d-none', kind === 'edit'); choices(kind === 'create' ? [branch.value] : []); document.getElementById('required-stock-material-title').textContent = kind === 'assign' ? 'Assign Material to Branches' : kind === 'edit' ? 'Edit Material' : 'Add Material'; modal.show(); }
  async function send(body) { const response = await fetch(endpoint, {method: 'POST', credentials: 'same-origin', headers: {'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8', Accept:'application/json'}, body: new URLSearchParams(body)}); const payload = await response.json(); if (!response.ok || !payload.success) throw new Error(payload.message || 'Request failed.'); return payload; }
  add.addEventListener('click', () => open('create'));
  rows.addEventListener('click', async event => { const button = event.target.closest('button[data-action]'); if (!button) return; const row = button.closest('tr'); const kind = button.dataset.action; if (kind === 'edit-material') open('edit', row); if (kind === 'assign-material') open('assign', row); if (kind === 'delete-material') { if (!confirm('Delete ' + row.dataset.materialName + ' from ' + branch.value + '? Existing kit inventory or pending inspections will keep it as inactive instead of deleting it.')) return; try { const payload = await send({action: 'delete', equipment: equipment.value, branch: branch.value, material_id: row.dataset.materialId}); alert(payload.message); location.reload(); } catch (error) { alert(error.message); } } });
  form.addEventListener('submit', async event => { event.preventDefault(); const selected = Array.from(branches.querySelectorAll('input:checked')).map(input => input.value); if (!name.value.trim() || !/^(0|[1-9][0-9]*)$/.test(quantity.value) || ((action.value === 'create' || action.value === 'assign') && !selected.length)) return; const body = {action: action.value, equipment: equipment.value, branch: targetBranch.value, material_id: id.value, material_name: name.value.trim(), required_stock: quantity.value}; selected.forEach((value, index) => body['branches[' + index + ']'] = value); try { await send(body); location.reload(); } catch (error) { alert(error.message); } });
})();
