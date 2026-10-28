<?php
require_once __DIR__ . '/../model/TransactionsModel.php';
require_once __DIR__ . '/DropdownController.php';

// Allow only the roles that are allowed to review inspection records.
$transactionsRole = strtolower(trim($_SESSION['Role'] ?? ''));
if ($transactionsRole !== 'admin' && $transactionsRole !== 'inspector') {
    http_response_code(403);
    exit('Access denied.');
}

// Read branch choices from the same dropdown table used by the other modules.
$transactionsBranches = [];
$branchRows = getAllDropdownBranches();
foreach ($branchRows as $branchRow) {
    if (!empty($branchRow['value'])) {
        $transactionsBranches[] = $branchRow['value'];
    }
}

$transactionsSessionBranch = trim($_SESSION['Branch'] ?? '');
$transactionsBranchParam = $_GET['branch'] ?? '';
if (!is_string($transactionsBranchParam)) {
    $transactionsBranchParam = '';
}
$transactionsAllBranches = false;

// Admin can select a valid branch or All Branches. Inspector always uses the session branch.
if ($transactionsRole === 'admin') {
    if ($transactionsBranchParam === 'all') {
        $transactionsAllBranches = true;
        $transactionsSelectedBranch = '';
    } elseif (in_array($transactionsBranchParam, $transactionsBranches, true)) {
        $transactionsSelectedBranch = $transactionsBranchParam;
    } else {
        $transactionsSelectedBranch = $transactionsSessionBranch;
    }
} else {
    $transactionsSelectedBranch = $transactionsSessionBranch;
}

// Read filters from the URL and keep only supported equipment and status values.
$transactionsEquipmentOptions = ['Fire Extinguisher', 'Chemical Spill Kit'];
$transactionsSearch = $_GET['search'] ?? '';
if (!is_string($transactionsSearch)) {
    $transactionsSearch = '';
}
$transactionsSearch = trim($transactionsSearch);

$transactionsEquipment = $_GET['equipment'] ?? '';
if (!is_string($transactionsEquipment) || !in_array($transactionsEquipment, $transactionsEquipmentOptions, true)) {
    $transactionsEquipment = '';
}

$transactionsStatus = $_GET['status'] ?? '';
if (!is_string($transactionsStatus)) {
    $transactionsStatus = '';
}
$transactionsStatus = strtolower(trim($transactionsStatus));
if (!in_array($transactionsStatus, ['pending', 'approved', 'rejected'], true)) {
    $transactionsStatus = '';
}

// Check that a date is a real calendar date in the YYYY-MM-DD format.
function isValidTransactionsDate($date)
{
    if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    $dateParts = explode('-', $date);
    return checkdate((int) $dateParts[1], (int) $dateParts[2], (int) $dateParts[0]);
}

$transactionsDateFrom = $_GET['date_from'] ?? '';
if (!is_string($transactionsDateFrom) || ($transactionsDateFrom !== '' && !isValidTransactionsDate($transactionsDateFrom))) {
    $transactionsDateFrom = '';
}

$transactionsDateTo = $_GET['date_to'] ?? '';
if (!is_string($transactionsDateTo) || ($transactionsDateTo !== '' && !isValidTransactionsDate($transactionsDateTo))) {
    $transactionsDateTo = '';
}

$transactionsDateError = '';
if ($transactionsDateFrom !== '' && $transactionsDateTo !== '' && $transactionsDateFrom > $transactionsDateTo) {
    $transactionsDateError = 'The From date must be earlier than or equal to the To date.';
    $transactionsDateFrom = '';
    $transactionsDateTo = '';
}

// Count matching rows first so the page links and result range use the full filtered total.
$transactionsLimit = 10;
$transactionsTotalRecords = getTransactionsInspectionTotal(
    $transactionsSelectedBranch,
    $transactionsAllBranches,
    $transactionsSearch,
    $transactionsEquipment,
    $transactionsDateFrom,
    $transactionsDateTo,
    $transactionsStatus
);
$transactionsTotalPages = (int) ceil($transactionsTotalRecords / $transactionsLimit);
if ($transactionsTotalPages < 1) {
    $transactionsTotalPages = 1;
}

$transactionsPage = $_GET['page'] ?? 1;
$transactionsPage = is_scalar($transactionsPage) ? max(1, (int) $transactionsPage) : 1;
if ($transactionsPage > $transactionsTotalPages) {
    $transactionsPage = $transactionsTotalPages;
}
$transactionsOffset = ($transactionsPage - 1) * $transactionsLimit;

// Ask the model for only the current page of matching inspection records.
$inspectionRecords = getTransactionsInspectionPage(
    $transactionsSelectedBranch,
    $transactionsAllBranches,
    $transactionsSearch,
    $transactionsEquipment,
    $transactionsDateFrom,
    $transactionsDateTo,
    $transactionsStatus,
    $transactionsLimit,
    $transactionsOffset
);

// Get branch-wide status totals for the summary cards.
$transactionsStatusCounts = getTransactionsInspectionStatusCounts(
    $transactionsSelectedBranch,
    $transactionsAllBranches
);
$pendingCount = $transactionsStatusCounts['pending'];
$approvedCount = $transactionsStatusCounts['approved'];
$rejectedCount = $transactionsStatusCounts['rejected'];

// Keep the selected filters in pagination links, but remove the old page number.
$transactionsPaginationParams = $_GET;
unset($transactionsPaginationParams['page']);
$transactionsPaginationQuery = http_build_query($transactionsPaginationParams);
$transactionsPaginationPrefix = $transactionsPaginationQuery === '' ? '' : $transactionsPaginationQuery . '&';

// Escape values before they are printed in the HTML page.
function escapeTransactionsValue($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}