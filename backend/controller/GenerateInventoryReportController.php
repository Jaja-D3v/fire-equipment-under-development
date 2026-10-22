<?php

require_once __DIR__ . '/../model/GenerateInventoryReportModel.php';

session_start();

$role = strtolower(trim($_SESSION['Role'] ?? ''));

if ($role === 'admin') {

    // Admin: gamitin ang branch na pinili sa dropdown
    $branch = trim($_GET['branch'] ?? 'all');

    if ($branch === '') {
        $branch = 'all';
    }

} elseif ($role === 'inspector') {

    // Inspector: gamitin ang assigned branch niya
    $branch = trim($_SESSION['Branch'] ?? '');

    if ($branch === '') {
        die('No branch assigned.');
    }

} else {

    die('Invalid role.');

}

// GET IDS BASED ON SELECTED BRANCH
$ids = getInventoryReportIds($branch);

if (empty($ids)) {
    die('No inventory records found for the selected branch.');
}

$idList = implode(',', $ids);


// OPEN REPORT
header(
    'Location: ../../helpers/generate-inventory-report.php?ids='
    . urlencode($idList)
);

exit;