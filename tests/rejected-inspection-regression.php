<?php
// Run: php tests/rejected-inspection-regression.php
// Uses real MariaDB SELECT-only CTE fixtures. No table, record or schema is changed.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../backend/model/RejectedInspectionModel.php';
mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_ONLY);

// Reuse the actual listing/filter builder and sidebar function under test names.
// The only SQL change is a CTE prefix that shadows the source tables for this SELECT.
// This keeps fixtures out of the live tables and avoids maintaining a second query.
$source = file_get_contents(__DIR__ . '/../backend/model/RejectedInspectionModel.php');
$start = strpos($source, 'function getRejectedInspections(');
$end = strpos($source, 'function getRejectedInspectionChemicalKitDetails(');
$function = substr($source, $start, $end - $start);
if (substr_count($function, '$sql = "SELECT') !== 1) {
    throw new RuntimeException('Update the fixture loader for the changed listing function.');
}
$function = str_replace('function getRejectedInspections(', 'function regressionGetRejectedInspections(', $function);
$function = str_replace('$sql = "SELECT', '$sql = $GLOBALS["regressionFixtureSql"] . "SELECT', $function);
eval($function);

$badgeStart = strpos($source, 'function getRejectedInspectionCountForSidebar(');
$badge = substr($source, $badgeStart);
$badge = str_replace('function getRejectedInspectionCountForSidebar(', 'function regressionGetRejectedInspectionCountForSidebar(', $badge);
$badge = str_replace('getRejectedInspections(', 'regressionGetRejectedInspections(', $badge);
eval($badge);

$dateResult = mysqli_query($conn, 'SELECT NOW() AS now_value');
$databaseNow = mysqli_fetch_assoc($dateResult)['now_value'];
mysqli_free_result($dateResult);
$now = strtotime($databaseNow);
$GLOBALS['regressionDate'] = $databaseNow;
$GLOBALS['regressionChecks'] = 0;
$GLOBALS['regressionCases'] = [];

function regressionAssert($condition, $message)
{
    $GLOBALS['regressionChecks']++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function regressionFe($id = 900001, $code = 'FE-FIXTURE', $branch = 'Laguna', $inspector = 'Fixture Inspector', $date = null)
{
    return [
        'inspect_id' => $id, 'extinguisher_code' => $code, 'branch' => $branch,
        'location' => 'Fixture bay', 'capacity' => '4.5kg', 'type' => 'ABC',
        'date_inspected' => $date ?? $GLOBALS['regressionDate'], 'inspected_by' => $inspector,
        'rejected_by' => 'Fixture Admin', 'reject_reason' => 'Header fallback',
        'action_taken' => null, 'target_date_of_implementation' => null, 'rejection_date' => null
    ];
}

function regressionCsk($id = 900001, $code = 'CSK-FIXTURE', $branch = 'Laguna', $inspector = 'Fixture Inspector', $date = null)
{
    return [
        'inspection_id' => $id, 'chemical_spill_kit_code' => $code, 'branch' => $branch,
        'location' => 'Fixture bay', 'inspection_date' => $date ?? $GLOBALS['regressionDate'],
        'inspected_by' => $inspector
    ];
}

function regressionTransaction($id, $category, $inspectionId = 900001, $status = 'Rejected', $reason = 'Current reason', $branch = 'Laguna', $type = 'inspection', $reviewedAt = null)
{
    return [
        'transaction_id' => $id, 'transaction_type' => $type, 'status' => $status,
        'category' => $category, 'inspection_id' => $inspectionId, 'branch' => $branch,
        'rejection_reason' => $reason, 'reviewed_at' => $reviewedAt ?? $GLOBALS['regressionDate']
    ];
}

function regressionCte($name, $columns, $rows)
{
    global $conn;
    $selects = [];
    if (!$rows) {
        $empty = [];
        foreach ($columns as $column) {
            $empty[] = 'NULL AS ' . $column;
        }
        return $name . ' AS (SELECT ' . implode(',', $empty) . ' WHERE 1=0)';
    }
    foreach ($rows as $rowIndex => $row) {
        $values = [];
        foreach ($columns as $column) {
            $value = $row[$column] ?? null;
            if ($value === null) {
                $literal = 'NULL';
            } elseif (is_int($value)) {
                $literal = (string) $value;
            } else {
                $literal = "'" . mysqli_real_escape_string($conn, (string) $value) . "'";
            }
            $values[] = $literal . ($rowIndex === 0 ? ' AS ' . $column : '');
        }
        $selects[] = 'SELECT ' . implode(',', $values);
    }
    return $name . ' AS (' . implode(' UNION ALL ', $selects) . ')';
}

function regressionCase($label, $fire, $kits, $transactions, $expectedKeys, $options = [], $expectedReasons = [])
{
    $GLOBALS['regressionFixtureSql'] = 'WITH ' . implode(',', [
        regressionCte('transactions_tbl', ['transaction_id', 'transaction_type', 'status', 'category', 'inspection_id', 'branch', 'rejection_reason', 'reviewed_at'], $transactions),
        regressionCte('inspection_checklist_tbl', array_keys(regressionFe()), $fire),
        regressionCte('chemical_spill_kit_inspection_tbl', array_keys(regressionCsk()), $kits)
    ]) . ' ';
    $inspector = $options['inspector'] ?? 'Fixture Inspector';
    $branch = $options['branch'] ?? 'Laguna';
    $records = regressionGetRejectedInspections($inspector, $branch, $options['search'] ?? '', $options['date'] ?? 'all', $options['equipment'] ?? 'all');
    regressionAssert(is_array($records), $label . ': query failed');

    $keys = [];
    foreach ($records as $record) {
        $keys[] = $record['equipment_type'] . ':' . $record['inspect_id'];
    }
    sort($keys);
    sort($expectedKeys);
    regressionAssert($keys === $expectedKeys, $label . ': wrong listing rows ' . json_encode($keys));
    regressionAssert(count($keys) === count(array_unique($keys)), $label . ': duplicate inspection identities');

    // Sidebar is deliberately unfiltered; compare it only with unfiltered listing cases.
    if (!isset($options['search']) && !isset($options['date']) && !isset($options['equipment'])) {
        $count = regressionGetRejectedInspectionCountForSidebar($inspector, $branch);
        regressionAssert($count === count($records), $label . ': sidebar differs from listing');
    }
    foreach ($expectedReasons as $key => $reason) {
        $matching = [];
        foreach ($records as $record) {
            if ($record['equipment_type'] . ':' . $record['inspect_id'] === $key) {
                $matching[] = $record;
            }
        }
        regressionAssert(count($matching) === 1 && $matching[0]['reject_reason'] === $reason, $label . ': incorrect rejection reason');
    }
    $GLOBALS['regressionCases'][] = $label;
}

$fe = [regressionFe()];
$csk = [regressionCsk()];
$both = ['Chemical Spill Kit:900001', 'Fire Extinguisher:900001'];
$rejected = [
    regressionTransaction(1, 'Fire Extinguisher'), regressionTransaction(2, 'Fire Extinguisher'),
    regressionTransaction(3, 'Chemical Spill Kit'), regressionTransaction(4, 'Chemical Spill Kit')
];
regressionCase('Identical duplicates in each category', $fe, $csk, $rejected, $both);

foreach (['Approved', 'Pending'] as $status) {
    $conflict = [
        regressionTransaction(1, 'Fire Extinguisher'), regressionTransaction(2, 'Fire Extinguisher', 900001, $status),
        regressionTransaction(3, 'Chemical Spill Kit'), regressionTransaction(4, 'Chemical Spill Kit', 900001, $status)
    ];
    regressionCase('Newer ' . $status . ' suppresses older Rejected', $fe, $csk, $conflict, []);
}
$latestRejected = [
    regressionTransaction(1, 'Fire Extinguisher', 900001, 'Approved', 'Old FE'),
    regressionTransaction(2, 'Fire Extinguisher', 900001, 'Rejected', 'Latest FE reason'),
    regressionTransaction(3, 'Chemical Spill Kit', 900001, 'Pending', 'Old CSK'),
    regressionTransaction(4, 'Chemical Spill Kit', 900001, 'Rejected', 'Latest CSK reason')
];
$latestReasons = ['Fire Extinguisher:900001' => 'Latest FE reason', 'Chemical Spill Kit:900001' => 'Latest CSK reason'];
regressionCase('Newer Rejected and authoritative reasons', $fe, $csk, $latestRejected, $both, [], $latestReasons);

$reasonConflicts = [
    regressionTransaction(1, 'Fire Extinguisher', 900001, 'Rejected', 'Old reason'),
    regressionTransaction(2, 'Fire Extinguisher', 900001, 'Rejected', 'Latest FE reason'),
    regressionTransaction(3, 'Chemical Spill Kit', 900001, 'Rejected', 'Old reason'),
    regressionTransaction(4, 'Chemical Spill Kit', 900001, 'Rejected', 'Latest CSK reason')
];
regressionCase('Conflicting reasons use highest transaction ID', $fe, $csk, $reasonConflicts, $both, [], $latestReasons);
$blankReasons = [
    regressionTransaction(1, 'Fire Extinguisher', 900001, 'Rejected', 'Old transaction reason'),
    regressionTransaction(2, 'Fire Extinguisher', 900001, 'Rejected', ''),
    regressionTransaction(3, 'Chemical Spill Kit', 900001, 'Rejected', 'Old transaction reason'),
    regressionTransaction(4, 'Chemical Spill Kit', 900001, 'Rejected', null)
];
regressionCase('Blank latest reasons preserve FE header fallback and CSK NULL', $fe, $csk, $blankReasons, $both, [],
    ['Fire Extinguisher:900001' => 'Header fallback', 'Chemical Spill Kit:900001' => null]);
regressionCase('Search authoritative reason', $fe, $csk, $reasonConflicts, ['Fire Extinguisher:900001'], ['search' => 'Latest FE']);
regressionCase('Search cannot revive stale reason', $fe, $csk, $reasonConflicts, [], ['search' => 'Old reason']);
regressionCase('FE equipment filter', $fe, $csk, $rejected, ['Fire Extinguisher:900001'], ['equipment' => 'Fire Extinguisher']);
regressionCase('CSK equipment filter', $fe, $csk, $rejected, ['Chemical Spill Kit:900001'], ['equipment' => 'Chemical Spill Kit']);
regressionCase('Other inspector excluded', $fe, $csk, $rejected, [], ['inspector' => 'Other Inspector']);
regressionCase('Other branch excluded', $fe, $csk, $rejected, [], ['branch' => 'Cebu']);

$isolation = [
    regressionTransaction(1, 'Fire Extinguisher'),
    regressionTransaction(2, 'Fire Extinguisher', 900001, 'Approved', 'Other branch', 'Cebu'),
    regressionTransaction(3, 'Fire Extinguisher', 900001, 'Approved', 'Other type', 'Laguna', 'registration'),
    regressionTransaction(4, 'Chemical Spill Kit', 900001, 'Approved', 'Other category')
];
regressionCase('Other branch/type/category cannot suppress FE', $fe, [], $isolation, ['Fire Extinguisher:900001']);

$branchFire = [regressionFe(), regressionFe(900002, 'FE-FIXTURE', 'Cebu')];
$branchKits = [regressionCsk(), regressionCsk(900002, 'CSK-FIXTURE', 'Cebu')];
$branchTransactions = [
    regressionTransaction(1, 'Fire Extinguisher'), regressionTransaction(2, 'Fire Extinguisher', 900002, 'Rejected', 'Cebu FE', 'Cebu'),
    regressionTransaction(3, 'Chemical Spill Kit'), regressionTransaction(4, 'Chemical Spill Kit', 900002, 'Rejected', 'Cebu CSK', 'Cebu')
];
regressionCase('Same codes in another branch independent', $branchFire, $branchKits, $branchTransactions, $both);
regressionCase('Cebu sees its own same-code records', $branchFire, $branchKits, $branchTransactions, ['Fire Extinguisher:900002', 'Chemical Spill Kit:900002'], ['branch' => 'Cebu']);

$older = date('Y-m-d H:i:s', strtotime('-1 day', $now));
$newerFire = [regressionFe(900001, 'FE-FIXTURE', 'Laguna', 'Fixture Inspector', $older), regressionFe(900002)];
$newerKits = [regressionCsk(900001, 'CSK-FIXTURE', 'Laguna', 'Fixture Inspector', $older), regressionCsk(900002)];
regressionCase('Newer headers suppress old rejection without transactions', $newerFire, $newerKits, $rejected, []);
$sameDateFire = [regressionFe(), regressionFe(900002)];
$sameDateKits = [regressionCsk(), regressionCsk(900002)];
$newHeaderTransactions = array_merge($rejected, [regressionTransaction(5, 'Fire Extinguisher', 900002), regressionTransaction(6, 'Chemical Spill Kit', 900002)]);
regressionCase('Same date higher inspection ID is latest', $sameDateFire, $sameDateKits, $newHeaderTransactions, ['Fire Extinguisher:900002', 'Chemical Spill Kit:900002']);

regressionCase('Today date filter', $fe, $csk, $rejected, $both, ['date' => 'today']);
regressionCase('Yesterday excludes today', $fe, $csk, $rejected, [], ['date' => 'yesterday']);
regressionCase('Yesterday includes yesterday headers',
    [regressionFe(900001, 'FE-FIXTURE', 'Laguna', 'Fixture Inspector', $older)],
    [regressionCsk(900001, 'CSK-FIXTURE', 'Laguna', 'Fixture Inspector', $older)],
    $rejected, $both, ['date' => 'yesterday']);

foreach ([['7', '-6 days', true], ['7', '-8 days', false], ['30', '-29 days', true], ['30', '-31 days', false]] as $dateCase) {
    $filter = $dateCase[0];
    $offset = $dateCase[1];
    $date = date('Y-m-d H:i:s', strtotime($offset, $now));
    regressionCase('Date ' . $filter . ' offset ' . $offset,
        [regressionFe(900001, 'FE-FIXTURE', 'Laguna', 'Fixture Inspector', $date)],
        [regressionCsk(900001, 'CSK-FIXTURE', 'Laguna', 'Fixture Inspector', $date)],
        $rejected, $dateCase[2] ? $both : [], ['date' => $filter]);
}
$reviewTimeConflict = [
    regressionTransaction(1, 'Fire Extinguisher', 900001, 'Rejected', 'Older row reviewed later', 'Laguna', 'inspection', date('Y-m-d H:i:s', strtotime('+1 day', $now))),
    regressionTransaction(2, 'Fire Extinguisher', 900001, 'Pending', 'Newer transaction')
];
regressionCase('Transaction ID takes precedence over reviewed timestamp', $fe, [], $reviewTimeConflict, []);
regressionCase('Empty result', [], [], [], []);

// Super Admin must not inherit inspector ownership or assigned-branch restrictions.
$originalSession = $_SESSION ?? [];
foreach (['Laguna', ''] as $assignedBranch) {
    $_SESSION = ['Role' => 'Super Admin', 'Branch' => $assignedBranch, 'EmployeeName' => ''];
    regressionCase('Super Admin all owners/branches: ' . ($assignedBranch ?: 'unassigned'),
        $branchFire, $branchKits, $branchTransactions,
        ['Fire Extinguisher:900001', 'Fire Extinguisher:900002', 'Chemical Spill Kit:900001', 'Chemical Spill Kit:900002'],
        ['inspector' => '', 'branch' => $assignedBranch]);
}
regressionCase('Super Admin global equipment filter', $branchFire, $branchKits, $branchTransactions,
    ['Fire Extinguisher:900001', 'Fire Extinguisher:900002'], ['inspector' => '', 'branch' => '', 'equipment' => 'Fire Extinguisher']);
regressionCase('Super Admin search reaches another branch', $branchFire, $branchKits, $branchTransactions,
    ['Fire Extinguisher:900002'], ['inspector' => '', 'branch' => 'Laguna', 'search' => 'Cebu FE']);
$_SESSION = $originalSession;

mysqli_rollback($conn);
echo 'PASS: ' . $GLOBALS['regressionChecks'] . ' assertions across ' . count($GLOBALS['regressionCases']) . " real MariaDB SELECT-only fixture cases.\n";
foreach ($GLOBALS['regressionCases'] as $label) {
    echo '  PASSED: ' . $label . "\n";
}
