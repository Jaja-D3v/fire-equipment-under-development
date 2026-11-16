// Shared CSK material cards. Names are data, never generated SQL columns or HTML selectors.
(function () {
  'use strict';
  if (window.cskMaterialForms) return;
  function validQuantity(value) {
    const quantityText = String(value).trim();
    return /^[0-9]+$/.test(quantityText)
      && quantityText.length <= 10
      && Number(value) <= 2147483647;
  }

  function materialKey(name) {
    const normalizedName = name.trim().toLowerCase();
    if (normalizedName === 'boom/pillow') {
      return 'boom pillow';
    }
    return normalizedName;
  }

  function refresh(form, container) {
    const cards = Array.from(container.querySelectorAll('[data-csk-material]'));
    let allQuantitiesValid = form.dataset.cskMaterialsReady === 'true' && cards.length > 0;
    let allMaterialsGood = allQuantitiesValid;
    cards.forEach(card => {
      const stock = card.querySelector('[data-csk-stock]');
      const status = card.querySelector('[data-csk-status]');
      const stockValid = validQuantity(stock.value);
      stock.setCustomValidity(stockValid ? '' : 'Enter a whole number from 0 to 2147483647.');
      allQuantitiesValid = allQuantitiesValid && stockValid;
      if (card.dataset.requiredStock !== '') {
        status.value = stockValid && Number(stock.value) >= Number(card.dataset.requiredStock) ? 'Good' : 'No Good';
      }
      allMaterialsGood = allMaterialsGood && stockValid && status.value === 'Good';
    });
    const overall = form.querySelector('[data-csk-overall-status]');
    if (overall) {
      if (!allQuantitiesValid) {
        overall.textContent = 'Unavailable';
      } else if (allMaterialsGood) {
        overall.textContent = 'Good';
      } else {
        overall.textContent = 'No Good';
      }
      overall.className = 'badge rounded-pill ' + (allMaterialsGood ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger');
    }
    const save = form.querySelector('[data-csk-save]');
    if (save) save.disabled = !allQuantitiesValid || !form.checkValidity();
    form.dispatchEvent(new Event('csk-materials-updated'));
    return allQuantitiesValid;
  }

  function render(form, container, materials, prefix) {
    container.textContent = '';
    materials.forEach((material, index) => {
      const column = document.createElement('div');
      column.className = 'col-12 col-lg-6';
      column.dataset.cskMaterial = '';
      column.dataset.requiredStock = material.required_stock == null ? '' : String(material.required_stock);
      // Only static markup enters innerHTML. Material names and remarks use text/value properties.
      column.innerHTML = '<div class="csk-material-card card h-100 rounded-3"><div class="card-body p-3">' +
        '<div class="csk-material-title fw-semibold mb-2"><span class="csk-material-icon"><i class="bi bi-box-seam"></i></span> <span data-csk-name></span></div>' +
        '<input type="hidden" data-csk-name-input><div class="row g-2">' +
        '<div class="col-5"><label class="form-label">Stock *</label><input type="number" class="form-control actual-stock-input" min="0" max="2147483647" step="1" required data-csk-stock>' +
        '<div class="small text-body-secondary fw-semibold mt-1">Required Stock: <span data-csk-required></span></div></div>' +
        '<div class="col-7"><label class="form-label">Status</label><select class="form-select stock-status" tabindex="-1" data-csk-status>' +
        '<option value="">Not recorded</option><option value="Good">Good</option><option value="No Good">No Good</option></select></div>' +
        '<div class="col-12"><label class="form-label">Remarks <span class="csk-optional">Optional</span></label><textarea class="form-control" rows="2" data-csk-remarks></textarea></div>' +
        '</div></div></div>';
      column.querySelector('[data-csk-name]').textContent = material.material_name;
      column.querySelector('[data-csk-required]').textContent = material.required_stock == null ? 'Not configured' : String(material.required_stock);
      const controls = [
        ['[data-csk-name-input]', 'material_name', material.material_name],
        ['[data-csk-stock]', 'current_stock', material.current_stock == null ? '' : material.current_stock],
        ['[data-csk-status]', 'status', material.status || ''],
        ['[data-csk-remarks]', 'remarks', material.remarks || '']
      ];
      controls.forEach(([selector, field, value]) => {
        const control = column.querySelector(selector);
        control.name = 'materials[' + index + '][' + field + ']';
        control.id = prefix + '-' + index + '-' + field;
        control.value = value;
        const label = control.parentElement.querySelector('label');
        if (label && control.type !== 'hidden') label.htmlFor = control.id;
      });
      column.querySelector('[data-csk-status]').style.pointerEvents = 'none';
      container.appendChild(column);
    });
    form.dataset.cskMaterialsReady = materials.length ? 'true' : 'false';
    if (!materials.length) container.textContent = 'No materials are configured for this branch.';
    refresh(form, container);
  }

  async function load(form, container, branch, prefix, isInspection) {
    const requestVersion = (Number(container.dataset.requestVersion) || 0) + 1;
    container.dataset.requestVersion = String(requestVersion);
    // Preserve add-form savedInputsByMaterial when reopening the same branch, but never across branches/kits.
    const savedInputsByMaterial = new Map();
    if (!isInspection && container.dataset.branch === branch) {
      container.querySelectorAll('[data-csk-material]').forEach(card => {
        savedInputsByMaterial.set(materialKey(card.querySelector('[data-csk-name-input]').value), {
          current_stock: card.querySelector('[data-csk-stock]').value,
          remarks: card.querySelector('[data-csk-remarks]').value
        });
      });
    }
    container.dataset.branch = branch;
    form.dataset.cskMaterialsReady = 'false';
    container.textContent = 'Loading required stock...';
    refresh(form, container);
    try {
      if (!branch || branch.toLowerCase() === 'all') throw new Error('Choose a specific branch.');
      const params = new URLSearchParams({action: 'getRequiredStock', branch});
      const response = await fetch('backend/controller/ChemicalSpillKitRequiredStockController.php?' + params, {
        credentials: 'same-origin', headers: {Accept: 'application/json'}
      });
      const payload = await response.json();
      if (requestVersion !== Number(container.dataset.requestVersion)) return;
      if (!response.ok || !payload.success || String(payload.branch).toLowerCase() !== branch.toLowerCase()
          || !Array.isArray(payload.materials)) throw new Error(payload.message || 'Required stock is unavailable.');
      const names = new Set();
      const materials = payload.materials.map(material => {
        if (typeof material.material_name !== 'string' || !material.material_name.trim()
            || !validQuantity(material.required_stock)) throw new Error('Invalid material configuration.');
        const key = materialKey(material.material_name);
        if (names.has(key)) throw new Error('Duplicate material configuration.');
        names.add(key);
        return Object.assign({}, material, {current_stock: isInspection ? '' : 0}, savedInputsByMaterial.get(key) || {});
      });
      render(form, container, materials, prefix);
    } catch (error) {
      if (requestVersion !== Number(container.dataset.requestVersion)) return;
      container.textContent = error.message || 'Required stock is unavailable.';
      form.dataset.cskMaterialsReady = 'false';
      refresh(form, container);
    }
  }

  function bind(form, container) {
    form.addEventListener('input', () => refresh(form, container));
    form.addEventListener('submit', event => {
      if (!refresh(form, container) || !form.checkValidity()) event.preventDefault();
    });
  }
  window.cskMaterialForms = {render, load, bind, refresh, validQuantity};
})();
