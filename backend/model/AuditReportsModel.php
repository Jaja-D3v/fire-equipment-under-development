<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/InspectionModel.php';

function auditReportOptions()
{
    global $conn;
    // These are actual implemented inventory modules, not extinguisher agent types.
    // There is no equipment-category registry or compatible report-history table.
    $branches = mysqli_fetch_all(mysqli_query($conn,
        "SELECT TRIM(value) AS value FROM maintenance_dropdown_tbl
        WHERE category = 'branch' AND status = 'active' AND TRIM(value) <> ''
        UNION SELECT TRIM(branch) AS value FROM fire_extinguishers_tbl
        WHERE archived = 0 AND TRIM(branch) <> '' ORDER BY value"), MYSQLI_ASSOC);
    $range = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT MIN(YEAR(date_inspected)) AS first_year FROM inspection_checklist_tbl WHERE date_inspected >= '1900-01-01'"));
    $current = (int) date('Y');
    $first = min($current, max(1900, (int) ($range['first_year'] ?: $current)));
    return [
        'equipment' => [
            ['value' => 'fire-extinguisher', 'label' => 'Fire Extinguisher', 'available' => true],
            ['value' => 'chemical-spill-kit', 'label' => 'Chemical Spill Kit — awaiting approval workflow', 'available' => false]
        ],
        'branches' => array_column($branches, 'value'),
        'years' => range($current, $first),
        'current_year' => $current
    ];
}

function auditBuildMonthlyRows($equipment, $inspections)
{
    $completed = [];
    foreach ($inspections as $inspection) {
        if ($inspection['evaluation_status'] !== 'Approved'
            || trim((string) $inspection['verified_and_approved_by']) === ''
            || inspectionApprovalValidationErrors($inspection)) continue;
        $month = (int) date('n', strtotime($inspection['date_inspected']));
        $completed[(int) $inspection['equipment_id']][$month] = true;
    }
    $total = 0;
    $monthlyInspected = array_fill(0, 12, 0);
    foreach ($equipment as &$item) {
        $item['months'] = [];
        for ($month = 1; $month <= 12; $month++) {
            $done = !empty($completed[(int) $item['equipment_id']][$month]);
            $item['months'][] = $done;
            if ($done) {
                $total++;
                $monthlyInspected[$month - 1]++;
            }
        }
    }
    unset($item);
    $monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
    $monthlySummary = [];
    foreach ($monthNames as $index => $monthName) {
        $monthlySummary[] = [
            'month' => $monthName,
            'inspected' => $monthlyInspected[$index],
            'not_inspected' => count($equipment) - $monthlyInspected[$index]
        ];
    }
    return ['rows' => $equipment, 'total_equipment' => count($equipment),
        'completed' => $total, 'missing' => count($equipment) * 12 - $total,
        'monthly_summary' => $monthlySummary];
}

function auditGetAnnualReport($year, $branch)
{
    global $conn;
    // Add another equipment type here: return inventory rows and checklist rows in this same shape.
    // auditBuildMonthlyRows() then supplies the shared monthly indicator data to the PDF helper.
    $all = $branch === '__all__' ? 1 : 0;
    $stmt = mysqli_prepare($conn, "SELECT extinguisher_id AS equipment_id,
        extinguisher_code AS equipment_code, location, TRIM(branch) AS branch
        FROM fire_extinguishers_tbl WHERE archived = 0
        AND (? = 1 OR LOWER(TRIM(branch)) = LOWER(TRIM(?)))
        ORDER BY LOWER(TRIM(branch)), extinguisher_code, extinguisher_id");
    mysqli_stmt_bind_param($stmt, 'is', $all, $branch);
    mysqli_stmt_execute($stmt);
    $equipment = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    // Approval identifies inventory by code + branch. Refuse ambiguous mappings.
    $keys = [];
    foreach ($equipment as $item) {
        $key = mb_strtolower(trim($item['branch']) . '\0' . trim($item['equipment_code']), 'UTF-8');
        if (isset($keys[$key])) throw new RuntimeException('Duplicate equipment code within a branch. Resolve inventory ambiguity before reporting.');
        $keys[$key] = true;
    }
    $start = sprintf('%04d-01-01 00:00:00', $year);
    $end = sprintf('%04d-01-01 00:00:00', $year + 1);
    $stmt = mysqli_prepare($conn, "SELECT e.extinguisher_id AS equipment_id, i.*
        FROM inspection_checklist_tbl i
        INNER JOIN fire_extinguishers_tbl e ON e.extinguisher_code = i.extinguisher_code
            AND LOWER(TRIM(e.branch)) = LOWER(TRIM(i.branch))
        WHERE e.archived = 0 AND (? = 1 OR LOWER(TRIM(e.branch)) = LOWER(TRIM(?)))
            AND i.date_inspected >= ? AND i.date_inspected < ?
            AND i.evaluation_status = 'Approved'");
    mysqli_stmt_bind_param($stmt, 'isss', $all, $branch, $start, $end);
    mysqli_stmt_execute($stmt);
    $inspections = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return auditBuildMonthlyRows($equipment, $inspections);
}
