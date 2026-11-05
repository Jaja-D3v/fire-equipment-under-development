<?php
require_once __DIR__ . '/../authentication/SessionChecker.php';

if (strtolower(trim($_SESSION['Role'] ?? '')) !== 'admin') {
    http_response_code(403);
    exit('Access denied.');
}

function getChemicalSpillKitInventoryReportFilters(array $input, array $session)
{
    $branch = $input['branch'] ?? ($session['Branch'] ?? '');
    $search = $input['search'] ?? '';
    if (!is_string($branch) || !is_string($search)) {
        throw new InvalidArgumentException('Invalid report filters.');
    }
    $branch = trim($branch);
    $search = trim($search);
    if ($branch === '') {
        $branch = trim((string) ($session['Branch'] ?? ''));
    }
    if ($branch === '') {
        $branch = 'all';
    }
    if (strlen($branch) > 400 || strlen($search) > 1000) {
        throw new InvalidArgumentException('Report filters are too long.');
    }
    return array('branch' => $branch, 'search' => $search);
}

try {
    $filters = getChemicalSpillKitInventoryReportFilters($_GET, $_SESSION);
} catch (InvalidArgumentException $error) {
    http_response_code(400);
    exit($error->getMessage());
}

require_once __DIR__ . '/../model/GenerateChemicalSpillKitInventoryReportModel.php';
$records = getChemicalSpillKitInventoryReportRows($filters['branch'], $filters['search']);
if ($records === false) {
    http_response_code(500);
    exit('Unable to generate the inventory report. Please try again.');
}
if (!$records) {
    http_response_code(404);
    exit('No active Chemical Spill Kit inventory records match the selected filters.');
}

// The renderer is only available through this authenticated controller.
define('CSK_INVENTORY_REPORT_AUTHORIZED', true);
require_once __DIR__ . '/../../helpers/generate-chemical-spill-kit-inventory-report.php';

$preparedBy = trim((string) ($_SESSION['EmployeeName'] ?? ''));
$pdf = buildChemicalSpillKitInventoryReport(
    $records,
    $filters['branch'],
    $filters['search'],
    $preparedBy !== '' ? $preparedBy : 'Unknown User'
);
$pdf->Output('chemical-spill-kit-inventory-report.pdf', 'I');
exit;
