<?php
// Check the login session before loading branch and inspection data.
require_once 'backend/authentication/SessionChecker.php';
require_once 'backend/controller/TransactionsController.php';
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'partials/header.php'; ?>


<body>
    <?php include 'partials/side-nav.php'; ?>
    <div class="wrapper d-flex flex-column min-vh-100">
        <?php include 'partials/header-nav.php'; ?>
        <div class="body flex-grow-1 bg-body-tertiary">
            <main class="container-fluid px-0">
                <div class="inspection-page">
                    <section class="inspection-hero">
                        <div class="inspection-hero-title">
                            <div class="inspection-hero-icon"><i class="bi bi-clipboard-check"></i></div>
                            <div>
                                <h1>Inspection Records</h1>
                                <p>Review and manage inspections across equipment types.</p>
                            </div>
                        </div>
                        <!-- Summary cards show database counts for the branch, not only this page. -->
                        <div class="inspection-stats">
                            <div class="inspection-stat">
                                <div class="inspection-stat-icon pending"><i class="bi bi-hourglass-split"></i></div>
                                <div><strong id="pendingCount"><?= $pendingCount ?></strong><span>Pending</span></div>
                            </div>
                            <div class="inspection-stat">
                                <div class="inspection-stat-icon approved"><i class="bi bi-check-circle-fill"></i></div>
                                <div><strong id="approvedCount"><?= $approvedCount ?></strong><span>Approved</span></div>
                            </div>
                            <div class="inspection-stat">
                                <div class="inspection-stat-icon rejected"><i class="bi bi-x-circle-fill"></i></div>
                                <div><strong id="rejectedCount"><?= $rejectedCount ?></strong><span>Rejected</span></div>
                            </div>
                        </div>
                    </section>
                    <section class="inspection-panel">
                        <!-- GET fields send the selected filters to the controller on submit. -->
                        <form id="inspectionFilterForm" class="inspection-filters" method="get" action="admin-pending-inspections.php">
                            <input type="hidden" name="page" value="1">
                            <!-- Search matches equipment code or location. -->
                            <div class="inspection-field inspection-search-field">
                                <div class="inspection-search-wrap">
                                    <i class="bi bi-search inspection-search-icon"></i>
                                    <input id="inspectionSearch" name="search" class="inspection-control" type="search" placeholder="Search by code or location..." autocomplete="off" value="<?= escapeTransactionsValue($transactionsSearch) ?>">
                                </div>
                            </div>

                            <div class="inspection-field">
                                <select id="equipmentFilter" name="equipment" class="inspection-control">
                                    <option value="">All Equipment</option>
                                    <?php foreach ($transactionsEquipmentOptions as $equipmentOption): ?>
                                        <option value="<?= escapeTransactionsValue($equipmentOption) ?>" <?= $transactionsEquipment === $equipmentOption ? 'selected' : '' ?>>
                                            <?= escapeTransactionsValue($equipmentOption) ?></option><?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Admin can choose a branch; Inspector branch is fixed by the controller. -->
                            <?php if ($transactionsRole === 'admin'): ?>
                                <div class="inspection-field">
                                    <select id="branchFilter" name="branch" class="inspection-control">
                                        <option value="all" <?= $transactionsAllBranches ? 'selected' : '' ?>>All Branches</option>
                                        <?php foreach ($transactionsBranches as $branchOption): ?>
                                            <option value="<?= escapeTransactionsValue($branchOption) ?>" <?= !$transactionsAllBranches && $transactionsSelectedBranch === $branchOption ? 'selected' : '' ?>><?= escapeTransactionsValue($branchOption) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="branch" value="<?= escapeTransactionsValue($transactionsSelectedBranch) ?>">
                                <div class="inspection-field">
                                    <input class="inspection-control" type="text" value="<?= escapeTransactionsValue($transactionsSelectedBranch ?: 'Assigned branch') ?>" aria-label="Assigned branch" readonly>
                                </div>
                            <?php endif; ?>
                            <!-- The two dates make an inclusive inspection-date range. -->
                            <div class="inspection-field inspection-date-field">
                                <div class="inspection-date-range">
                                    <div class="inspection-date-input-wrap">
                                        <input id="dateFrom" name="date_from" class="inspection-control" type="date" value="<?= escapeTransactionsValue($transactionsDateFrom) ?>" aria-label="Inspection date from">
                                        <span class="inspection-date-placeholder" aria-hidden="true">From (dd/mm/yyyy)</span>
                                    </div>
                                    <span class="inspection-date-dash" aria-hidden="true">&ndash;</span>
                                    <div class="inspection-date-input-wrap"><input id="dateTo" name="date_to" class="inspection-control" type="date" value="<?= escapeTransactionsValue($transactionsDateTo) ?>" aria-label="Inspection date to">
                                        <span class="inspection-date-placeholder" aria-hidden="true">To (dd/mm/yyyy)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="inspection-field"><select id="statusFilter" name="status" class="inspection-control">
                                    <option value="">All Status</option>
                                    <option value="pending" <?= $transactionsStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="approved" <?= $transactionsStatus === 'approved' ? 'selected' : '' ?>>Approved</option>
                                    <option value="rejected" <?= $transactionsStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                </select></div>
                            <!-- Filter submits the GET form; Reset opens the page without filter parameters. -->
                            <div class="inspection-filter-actions">
                                <button class="inspection-btn inspection-btn-filter" type="submit">
                                    <i class="bi bi-funnel-fill"></i> Filter
                                </button>
                                <a class="inspection-btn inspection-btn-reset" id="resetInspectionFilters" href="admin-pending-inspections.php">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                                </a>
                            </div>
                        </form>
                        <?php if ($transactionsDateError !== ''): ?>
                            <p class="text-danger small mb-0 mt-2" role="alert"><?= escapeTransactionsValue($transactionsDateError) ?></p>
                        <?php endif; ?>
                    </section>
                    <!-- The controller provides filtered rows and the total used for pagination. -->
                    <section class="inspection-table-card">
                        <div class="inspection-table-scroll table-responsive">
                            <table class="inspection-table">
                                <thead>
                                    <tr>
                                        <th>Equipment Code</th>
                                        <th>Equipment / Location</th>
                                        <th>Date Inspected</th>
                                        <th>Status</th>
                                        <th>Branch</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="inspectionTableBody">
                                    <!-- Display each record returned by the database query as one table row. -->
                                    <?php foreach ($inspectionRecords as $record): ?>
                                        <?php
                                        $recordStatus = ucfirst(strtolower((string) $record['status']));
                                        $recordDate = $record['inspection_date'] ? strtotime($record['inspection_date']) : false;
                                        $recordIcon = $record['equipment'] === 'Chemical Spill Kit' ? 'bi bi-box-seam' : 'bi bi-fire';
                                        ?>
                                        <tr data-code="<?= escapeTransactionsValue($record['code']) ?>" data-equipment="<?= escapeTransactionsValue($record['equipment']) ?>" data-location="<?= escapeTransactionsValue($record['location']) ?>" data-date="<?= $recordDate ? date('Y-m-d', $recordDate) : '' ?>" data-status="<?= escapeTransactionsValue($recordStatus) ?>" data-branch="<?= escapeTransactionsValue($record['branch']) ?>">
                                            <td><span class="inspection-code"><?= escapeTransactionsValue($record['code']) ?></span></td>
                                            <td>
                                                <div class="inspection-equipment">
                                                    <div class="inspection-equipment-icon"><i class="<?= $recordIcon ?>"></i></div>
                                                    <div><strong><?= escapeTransactionsValue($record['equipment']) ?></strong><small><i class="bi bi-geo-alt me-1"></i><?= escapeTransactionsValue($record['location']) ?></small></div>
                                                </div>
                                            </td>
                                            <td><span class="inspection-date-main"><i class="bi bi-calendar3 me-1" aria-hidden="true"></i><?= $recordDate ? date('M d, Y', $recordDate) : '—' ?></span><span class="inspection-date-time"><i class="bi bi-clock me-1" aria-hidden="true"></i><?= $recordDate ? date('h:i A', $recordDate) : '—' ?></span></td>
                                            <td><span class="inspection-status <?= escapeTransactionsValue(strtolower($recordStatus)) ?>"><i class="<?= $recordStatus === 'Pending' ? 'bi bi-hourglass-split' : ($recordStatus === 'Approved' ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill') ?>"></i><?= escapeTransactionsValue($recordStatus) ?></span></td>
                                            <td><span class="inspection-branch"><i class="bi bi-building me-1"></i><?= escapeTransactionsValue($record['branch']) ?></span></td>
                                            <td class="text-center">
                                                <?php if ($record['equipment'] === 'Fire Extinguisher'): ?>
                                                    <button type="button" class="inspection-view" data-inspection-id="<?= (int) $record['inspection_id'] ?>" data-extinguisher-code="<?= escapeTransactionsValue($record['code']) ?>" data-branch="<?= escapeTransactionsValue($record['branch']) ?>" data-status="<?= escapeTransactionsValue($recordStatus) ?>" onclick="setEvaluationActionsVisibility(this.dataset.status); viewInspection(Number(this.dataset.inspectionId), this.dataset.extinguisherCode); document.getElementById('viewInspectionBranch').value = this.dataset.branch"><i class="bi bi-eye"></i> View</button>
                                                <?php else: ?>
                                                    <button type="button" class="inspection-view" data-bs-toggle="modal" data-bs-target="#inspectionRecordModal" data-code="<?= escapeTransactionsValue($record['code']) ?>" data-equipment="<?= escapeTransactionsValue($record['equipment']) ?>" data-location="<?= escapeTransactionsValue($record['location']) ?>" data-date="<?= escapeTransactionsValue($recordDate ? date('M d, Y h:i A', $recordDate) : '') ?>" data-branch="<?= escapeTransactionsValue($record['branch']) ?>" data-status="<?= escapeTransactionsValue($recordStatus) ?>"><i class="bi bi-eye"></i> View</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (!$inspectionRecords): ?><div class="inspection-empty" id="inspectionEmpty" style="display:block">No inspection records match your filters.</div><?php endif; ?>
                        <?php $firstRecord = $transactionsTotalRecords ? $transactionsOffset + 1 : 0;
                        $lastRecord = min($transactionsOffset + count($inspectionRecords), $transactionsTotalRecords); ?>
                        <!-- Page links keep the active filters and request another database page. -->
                        <div class="inspection-table-footer">
                            <div class="inspection-count" id="inspectionCount">Showing <strong><?= $firstRecord ?>&ndash;<?= $lastRecord ?></strong> of <strong><?= $transactionsTotalRecords ?></strong> inspections</div>
                            <div class="inspection-pagination">
                                <a class="inspection-page-btn" href="<?= escapeTransactionsValue('admin-pending-inspections.php?' . $transactionsPaginationPrefix . 'page=1') ?>" aria-label="First page" <?= $transactionsPage <= 1 ? 'aria-disabled="true" tabindex="-1"' : '' ?>><i class="bi bi-chevron-double-left"></i></a>
                                <a class="inspection-page-btn" href="<?= escapeTransactionsValue('admin-pending-inspections.php?' . $transactionsPaginationPrefix . 'page=' . max(1, $transactionsPage - 1)) ?>" aria-label="Previous page" <?= $transactionsPage <= 1 ? 'aria-disabled="true" tabindex="-1"' : '' ?>><i class="bi bi-chevron-left"></i></a>
                                <?php for ($pageNumber = max(1, $transactionsPage - 2); $pageNumber <= min($transactionsTotalPages, $transactionsPage + 2); $pageNumber++): ?><a class="inspection-page-btn <?= $pageNumber === $transactionsPage ? 'active' : '' ?>" href="<?= escapeTransactionsValue('admin-pending-inspections.php?' . $transactionsPaginationPrefix . 'page=' . $pageNumber) ?>" <?= $pageNumber === $transactionsPage ? 'aria-current="page"' : '' ?>><?= $pageNumber ?></a><?php endfor; ?>
                                <a class="inspection-page-btn" href="<?= escapeTransactionsValue('admin-pending-inspections.php?' . $transactionsPaginationPrefix . 'page=' . min($transactionsTotalPages, $transactionsPage + 1)) ?>" aria-label="Next page" <?= $transactionsPage >= $transactionsTotalPages ? 'aria-disabled="true" tabindex="-1"' : '' ?>><i class="bi bi-chevron-right"></i></a>
                                <a class="inspection-page-btn" href="<?= escapeTransactionsValue('admin-pending-inspections.php?' . $transactionsPaginationPrefix . 'page=' . $transactionsTotalPages) ?>" aria-label="Last page" <?= $transactionsPage >= $transactionsTotalPages ? 'aria-disabled="true" tabindex="-1"' : '' ?>><i class="bi bi-chevron-double-right"></i></a>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="modal fade" id="inspectionRecordModal" tabindex="-1" aria-labelledby="inspectionRecordModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="inspectionRecordModalLabel">Inspection Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Code</dt>
                                    <dd class="col-sm-8" id="recordModalCode"></dd>
                                    <dt class="col-sm-4">Equipment</dt>
                                    <dd class="col-sm-8" id="recordModalEquipment"></dd>
                                    <dt class="col-sm-4">Location</dt>
                                    <dd class="col-sm-8" id="recordModalLocation"></dd>
                                    <dt class="col-sm-4">Date inspected</dt>
                                    <dd class="col-sm-8" id="recordModalDate"></dd>
                                    <dt class="col-sm-4">Branch</dt>
                                    <dd class="col-sm-8" id="recordModalBranch"></dd>
                                    <dt class="col-sm-4">Status</dt>
                                    <dd class="col-sm-8" id="recordModalStatus"></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include_once 'partials/evaluate-extinguisher-modal.php'; ?>
                <!-- This script updates date placeholders and fills the selected record details in the modal. -->
                <script>
                    // Show evaluation actions only while the selected inspection is pending.
                    function setEvaluationActionsVisibility(status) {
                        const isPending = String(status || '').trim().toLowerCase() === 'pending';
                        document.getElementById('approveInspectionForm').hidden = !isPending;
                        document.getElementById('rejectInspectionForm').hidden = !isPending;
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        // Keep each placeholder visible only while its date field is empty.
                        document.querySelectorAll('.inspection-date-input-wrap input').forEach(function(input) {
                            const wrapper = input.closest('.inspection-date-input-wrap');
                            const updatePlaceholder = function() {
                                wrapper.classList.toggle('has-value', input.value !== '');
                            };
                            input.addEventListener('input', updatePlaceholder);
                            input.addEventListener('change', updatePlaceholder);
                            input.addEventListener('focus', function() {
                                wrapper.classList.add('is-focused');
                            });
                            input.addEventListener('blur', function() {
                                wrapper.classList.remove('is-focused');
                            });
                            updatePlaceholder();
                        });
                        // Copy the clicked row values into the modal before it opens.
                        const modal = document.getElementById('inspectionRecordModal');
                        modal.addEventListener('show.bs.modal', function(event) {
                            const button = event.relatedTarget;
                            ['Code', 'Equipment', 'Location', 'Date', 'Branch', 'Status'].forEach(function(field) {
                                document.getElementById('recordModal' + field).textContent = button.dataset[field.toLowerCase()] || '—';
                            });
                        });
                    });
                </script>
            </main>
        </div>
        <?php include 'partials/footer.php'; ?>
    </div>
    <script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
    <script src="./js/inspection-approvals.js"></script>
    <script src="vendors/simplebar/js/simplebar.min.js"></script>
    <script>
        const header = document.querySelector("header.header");
        document.addEventListener("scroll", () => {
            if (header) header.classList.toggle("shadow-sm", document.documentElement.scrollTop > 0);
        });
    </script>
    <?php include_once 'notification/session_timeout.php'; ?>
</body>

</html>