<?php
$requiredStockBranchValues = [];
foreach ($requiredStockBranches as $requiredStockBranchRow) {
    $requiredStockBranchValue = trim((string) ($requiredStockBranchRow['value'] ?? ''));
    if ($requiredStockBranchValue !== '') $requiredStockBranchValues[] = $requiredStockBranchValue;
}
?>

<style>
    #maintenance-category-required_stock .required-stock-card {
        border-radius: 14px;
    }

    #maintenance-category-required_stock .required-stock-toolbar {
        gap: 12px;
        margin-bottom: 20px;
    }

    #maintenance-category-required_stock .required-stock-actions {
        align-items: center;
        gap: 8px;
    }

    #maintenance-category-required_stock .required-stock-add,
    #maintenance-category-required_stock .required-stock-branch,
    #maintenance-category-required_stock .required-stock-search,
    #maintenance-category-required_stock .required-stock-search-icon {
        min-height: 38px;
        height: 38px;
        border-radius: 8px;
        font-size: .875rem;
    }

    #maintenance-category-required_stock .required-stock-add {
        padding: .375rem .75rem;
        font-weight: 600;
        line-height: 1.25;
        white-space: nowrap;
    }

    #maintenance-category-required_stock .required-stock-branch {
        width: 190px;
        padding-top: .375rem;
        padding-bottom: .375rem;
    }

    #maintenance-category-required_stock .required-stock-search-icon {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        padding: 0 .75rem;
    }

    #maintenance-category-required_stock .required-stock-search {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    #maintenance-category-required_stock .required-stock-filter-row {
        margin-bottom: 14px;
    }

    #maintenance-category-required_stock .required-stock-table-wrap {
        max-height: 400px;
        overflow-y: auto;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #maintenance-category-required_stock .required-stock-table {
        min-width: 720px;
        font-size: .875rem;
    }

    #maintenance-category-required_stock .required-stock-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: #f8f9fa;
        box-shadow: inset 0 -1px 0 #dee2e6;
        font-size: .75rem;
        letter-spacing: .04em;
    }

    #maintenance-category-required_stock .required-stock-table tbody td {
        padding-top: .7rem;
        padding-bottom: .7rem;
        vertical-align: middle;
    }

    #required-stock-rows td:last-child {
        white-space: nowrap;
    }

    #required-stock-rows td:last-child .btn {
        min-height: 30px;
        padding: .25rem .55rem;
        font-size: .8rem;
        line-height: 1.25;
    }

    @media (max-width: 991.98px) {
        #maintenance-category-required_stock .required-stock-toolbar {
            align-items: flex-start !important;
        }

        #maintenance-category-required_stock .required-stock-filter-row {
            row-gap: 8px;
        }
    }

    @media (max-width: 575.98px) {
        #maintenance-category-required_stock .required-stock-toolbar {
            margin-bottom: 16px;
        }

        #maintenance-category-required_stock .required-stock-actions {
            width: 100%;
            flex-direction: row;
            justify-content: space-between;
        }

        #maintenance-category-required_stock .required-stock-branch {
            width: min(58vw, 210px);
            flex: 1 1 auto;
        }

        #maintenance-category-required_stock .required-stock-add {
            flex: 0 0 auto;
        }

        #maintenance-category-required_stock .required-stock-filter-row {
            margin-bottom: 12px;
        }

        #maintenance-category-required_stock .required-stock-count {
            text-align: left !important;
        }

        #maintenance-category-required_stock .required-stock-table-wrap {
            max-height: 360px;
        }
    }
</style>
<section class="card border-0 shadow-sm rounded-4 required-stock-card">
    <div class="card-body p-3 p-lg-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center required-stock-toolbar">
            <div>
                <h3 class="h5 fw-semibold mb-1">Required Stock</h3>
                <p class="text-body-secondary mb-0">Manage Chemical Spill Kit minimum quantities by branch.</p>
                <input type="hidden" id="required-stock-equipment" value="chemical_spill_kit">
            </div>
            <div class="d-flex flex-row required-stock-actions">
                <button type="button" class="btn btn-primary required-stock-add" id="required-stock-add">Add Material</button>
                <label class="visually-hidden" for="required-stock-branch">Branch</label>
                <select class="form-select required-stock-branch" id="required-stock-branch" aria-label="Branch" data-default-branch="<?= htmlspecialchars($requiredStockDefaultBranch, ENT_QUOTES, 'UTF-8') ?>">
                    <option value="" disabled>Choose branch</option>
                    <?php foreach ($requiredStockBranchValues as $branchValue): ?>
                        <option value="<?= htmlspecialchars($branchValue, ENT_QUOTES, 'UTF-8') ?>" <?= strcasecmp($branchValue, $requiredStockDefaultBranch) === 0 ? 'selected' : '' ?>><?= htmlspecialchars($branchValue, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row g-3 align-items-center required-stock-filter-row">
            <div class="col-12 col-md-6">
                <label class="visually-hidden" for="required-stock-search">Search materials</label>
                <div class="input-group">
                    <span class="input-group-text bg-body-tertiary border-end-0 required-stock-search-icon"><i class="bi bi-search"></i></span>
                    <input class="form-control border-start-0 required-stock-search" type="search" id="required-stock-search" placeholder="Search materials" autocomplete="off">
                </div>
            </div>
            <div class="col-12 col-md-6 text-md-end small text-body-secondary required-stock-count" id="required-stock-count" aria-live="polite"></div>
        </div>

        <div id="required-stock-feedback" class="alert d-none" role="status" aria-live="polite"></div>
        <div class="table-responsive required-stock-table-wrap">
            <table class="table table-hover align-middle mb-0 required-stock-table">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-3">Material</th>
                        <th scope="col">Required stock</th>
                        <th scope="col">Last updated</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="required-stock-rows">
                    <tr>
                        <td colspan="5" class="text-center text-body-secondary py-5">Choose a branch to view required stock.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="small text-body-secondary mt-3 mb-0">Changes update the configured minimum used by Chemical Spill Kit inspection evaluation.</p>
    </div>
</section>
<div class="modal fade" id="required-stock-material-modal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="required-stock-material-form"><div class="modal-header"><h5 class="modal-title" id="required-stock-material-title">Add Material</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="required-stock-material-action"><input type="hidden" id="required-stock-material-id"><input type="hidden" id="required-stock-material-branch"><div class="mb-3"><label class="form-label">Material name</label><input id="required-stock-material-name" class="form-control" maxlength="100" required></div><div class="mb-3"><label class="form-label">Required stock</label><input id="required-stock-material-quantity" type="number" class="form-control" min="0" max="4294967295" required></div><div id="required-stock-branches-wrap"><label class="form-label">Assign to branches</label><div id="required-stock-branches"></div></div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div></form></div></div></div>
