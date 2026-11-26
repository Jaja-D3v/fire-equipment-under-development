<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/InspectionModel.php';

function auditReportOptions()
{
    global $conn;
    // Collect branches from shared configuration and current active inventory.
    $branchResult = mysqli_query($conn,
        "SELECT TRIM(value) AS value FROM maintenance_dropdown_tbl
        WHERE category = 'branch' AND status = 'active' AND TRIM(value) <> ''
        UNION SELECT TRIM(branch) AS value FROM fire_extinguishers_tbl
        WHERE archived = 0 AND TRIM(branch) <> ''
        UNION SELECT TRIM(branch) AS value FROM chemical_spill_kit_materials_inventory_tbl
        WHERE archived = 0 AND TRIM(branch) <> '' ORDER BY value");
    if (!$branchResult) {
        throw new RuntimeException('Could not load annual report branches: ' . mysqli_error($conn));
    }
    $branches = mysqli_fetch_all($branchResult, MYSQLI_ASSOC);
    mysqli_free_result($branchResult);
    $rangeResult = mysqli_query($conn,
        "SELECT MIN(report_year) AS first_year FROM (
            SELECT YEAR(date_inspected) AS report_year
            FROM inspection_checklist_tbl
            WHERE date_inspected >= '1900-01-01'
            UNION ALL
            SELECT YEAR(inspection_date) AS report_year
            FROM chemical_spill_kit_inspection_tbl
            WHERE inspection_date >= '1900-01-01'
        ) report_years");
    if (!$rangeResult) {
        throw new RuntimeException('Could not load annual report years: ' . mysqli_error($conn));
    }
    $range = mysqli_fetch_assoc($rangeResult);
    mysqli_free_result($rangeResult);
    $current = (int) date('Y');
    $first = min($current, max(1900, (int) ($range['first_year'] ?: $current)));
    return [
        'equipment' => [
            ['value' => 'fire-extinguisher', 'label' => 'Fire Extinguisher', 'available' => true],
            ['value' => 'chemical-spill-kit', 'label' => 'Chemical Spill Kit', 'available' => true]
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

function auditBuildChemicalSpillKitMonthlyRows($equipment, $inspections)
{
    $completed = [];
    foreach ($inspections as $inspection) {
        $month = (int) date('n', strtotime($inspection['inspection_date']));
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

function auditGetChemicalSpillKitAnnualReport($year, $branch)
{
    global $conn;
    $all = $branch === '__all__' ? 1 : 0;
    $equipmentSql = "SELECT chemical_spill_kit_id AS equipment_id,
            chemical_spill_kit_code AS equipment_code, location, TRIM(branch) AS branch
        FROM chemical_spill_kit_materials_inventory_tbl
        WHERE archived = 0
          AND (? = 1 OR LOWER(TRIM(branch)) = LOWER(TRIM(?)))
        ORDER BY LOWER(TRIM(branch)), chemical_spill_kit_code, chemical_spill_kit_id";
    $stmt = mysqli_prepare($conn, $equipmentSql);
    if (!$stmt) {
        throw new RuntimeException('Could not prepare Chemical Spill Kit inventory lookup.');
    }
    mysqli_stmt_bind_param($stmt, 'is', $all, $branch);
    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        throw new RuntimeException('Could not load Chemical Spill Kit inventory: ' . $error);
    }
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        throw new RuntimeException('Could not read Chemical Spill Kit inventory: ' . $error);
    }
    $equipment = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
    mysqli_stmt_close($stmt);

    $start = sprintf('%04d-01-01 00:00:00', $year);
    $end = sprintf('%04d-01-01 00:00:00', $year + 1);
    $inspectionSql = "SELECT i.chemical_spill_kit_id AS equipment_id, i.inspection_date
        FROM chemical_spill_kit_inspection_tbl i
        INNER JOIN chemical_spill_kit_materials_inventory_tbl k
            ON k.chemical_spill_kit_id = i.chemical_spill_kit_id
            AND LOWER(TRIM(k.branch)) = LOWER(TRIM(i.branch))
        INNER JOIN transactions_tbl t
            ON t.inspection_id = i.inspection_id
            AND t.category = 'Chemical Spill Kit'
            AND t.transaction_type = 'inspection'
            AND LOWER(TRIM(t.branch)) = LOWER(TRIM(i.branch))
        WHERE k.archived = 0
          AND (? = 1 OR LOWER(TRIM(k.branch)) = LOWER(TRIM(?)))
          AND i.inspection_date >= ? AND i.inspection_date < ?
          AND LOWER(TRIM(t.status)) = 'approved'
        ORDER BY i.inspection_date, i.inspection_id";
    $stmt = mysqli_prepare($conn, $inspectionSql);
    if (!$stmt) {
        throw new RuntimeException('Could not prepare Chemical Spill Kit inspection lookup.');
    }
    mysqli_stmt_bind_param($stmt, 'isss', $all, $branch, $start, $end);
    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        throw new RuntimeException('Could not load approved Chemical Spill Kit inspections: ' . $error);
    }
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        throw new RuntimeException('Could not read approved Chemical Spill Kit inspections: ' . $error);
    }
    $inspections = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
    mysqli_stmt_close($stmt);

    return auditBuildChemicalSpillKitMonthlyRows($equipment, $inspections);
}
