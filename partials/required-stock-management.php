<?php
$requiredStockBranchValues = [];
foreach ($requiredStockBranches as $requiredStockBranchRow) {
    $requiredStockBranchValue = trim((string) ($requiredStockBranchRow['value'] ?? ''));
    if ($requiredStockBranchValue !== '') $requiredStockBranchValues[] = $requiredStockBranchValue;
}
?>

<style>
    .inventory-table-scroll {
        max-height: 400px;
        overflow-y: auto;
    }

    .inventory-table-scroll thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: #f8f9fa;
        box-shadow: inset 0 -1px 0 #dee2e6;
    }
</style>
<section class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-3 p-lg-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h3 class="h5 fw-semibold mb-1">Required Stock</h3>
                <p class="text-body-secondary mb-0">Manage Chemical Spill Kit minimum quantities by branch.</p>
                <input type="hidden" id="required-stock-equipment" value="chemical_spill_kit">
            </div>
            <div class="d-flex flex-column flex-sm-row gap-2">
                <label class="visually-hidden" for="required-stock-branch">Branch</label>
                <select class="form-select" id="required-stock-branch" aria-label="Branch" data-default-branch="<?= htmlspecialchars($requiredStockDefaultBranch, ENT_QUOTES, 'UTF-8') ?>">
                    <option value="">Choose branch</option>
                    <?php foreach ($requiredStockBranchValues as $branchValue): ?>
                        <option value="<?= htmlspecialchars($branchValue, ENT_QUOTES, 'UTF-8') ?>" <?= strcasecmp($branchValue, $requiredStockDefaultBranch) === 0 ? 'selected' : '' ?>><?= htmlspecialchars($branchValue, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row g-3 align-items-center mb-3">
            <div class="col-12 col-md-6">
                <label class="visually-hidden" for="required-stock-search">Search materials</label>
                <div class="input-group">
                    <span class="input-group-text bg-body-tertiary border-end-0"><i class="bi bi-search"></i></span>
                    <input class="form-control border-start-0" type="search" id="required-stock-search" placeholder="Search materials" autocomplete="off">
                </div>
            </div>
            <div class="col-12 col-md-6 text-md-end small text-body-secondary" id="required-stock-count" aria-live="polite"></div>
        </div>

        <div id="required-stock-feedback" class="alert d-none" role="status" aria-live="polite"></div>
        <div class="table-responsive inventory-table-scroll">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-3">Material</th>
                        <th scope="col">Required stock</th>
                        <th scope="col">Last updated</th>
                        <th scope="col" class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody id="required-stock-rows">
                    <tr>
                        <td colspan="4" class="text-center text-body-secondary py-5">Choose a branch to view required stock.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="small text-body-secondary mt-3 mb-0">Changes update the configured minimum used by Chemical Spill Kit inspection evaluation.</p>
    </div>
</section>