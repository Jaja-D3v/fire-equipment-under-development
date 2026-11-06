<?php
require_once __DIR__ . '/../config/db.php';

// Run a prepared, read-only query using the project's shared mysqli connection.
function dashboardSummarySelect($sql, $parameterTypes = '', array $parameters = [])
{
    global $conn;
    $statement = $conn->prepare($sql);

    try {
        if ($parameterTypes !== '') {
            $statement->bind_param($parameterTypes, ...$parameters);
        }
        $statement->execute();
        $result = $statement->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $rows;
    } finally {
        $statement->close();
    }
}

// Get active branch options for the Admin filter.
function getDashboardSummaryBranches()
{
    $rows = dashboardSummarySelect(
        "SELECT value FROM maintenance_dropdown_tbl
         WHERE category = 'branch' AND status = 'active' ORDER BY value"
    );
    return array_column($rows, 'value');
}

// Use extinguisher codes to match inventory units with inspection records.
function getDashboardFireExtinguisherSummary(array $filters)
{
    return dashboardEquipmentSummary(
        'fire_extinguishers_tbl', 'inspection_checklist_tbl',
        'extinguisher_code', 'extinguisher_code', 'date_inspected', $filters
    );
}

// Count distinct kits, not individual rows of kit materials.
function getDashboardChemicalSpillKitSummary(array $filters)
{
    return dashboardEquipmentSummary(
        'chemical_spill_kit_materials_inventory_tbl', 'chemical_spill_kit_inspection_tbl',
        'chemical_spill_kit_id', 'chemical_spill_kit_id', 'inspection_date', $filters
    );
}

// Table and column names come only from the two model functions above, never requests.
function dashboardEquipmentSummary($inventoryTable, $inspectionTable, $inventoryKey, $inspectionKey, $dateColumn, array $filters)
{
    $branch = $filters['branch'];
    $branchSql = '';
    if ($branch !== 'all') {
        $branchSql = ' AND LOWER(TRIM(k.branch)) = LOWER(TRIM(?))';
    }
    $parameters = [$filters['start'], $filters['end']];
    $parameterTypes = 'ss';
    if ($branch !== 'all') {
        $parameters[] = $branch;
        $parameterTypes .= 's';
    }

    // The outer inventory query uses only branch and archive filters.
    // Dates apply inside EXISTS, so changing dates never changes the active inventory.
    // Any matching inspection qualifies; repeated records count a unit only once.
    $inventoryCounts = dashboardSummarySelect(
        "SELECT COUNT(*) AS active,
                COALESCE(SUM(EXISTS (
                    SELECT 1 FROM $inspectionTable i
                    WHERE i.$inspectionKey = k.equipment_key
                      AND LOWER(TRIM(i.branch)) = LOWER(TRIM(k.branch))
                      AND i.$dateColumn >= ? AND i.$dateColumn < ?
                )), 0) AS inspected
         FROM (
             SELECT DISTINCT k.$inventoryKey AS equipment_key, k.branch
             FROM $inventoryTable k WHERE k.archived = 0 $branchSql
         ) k",
        $parameterTypes, $parameters
    )[0];

    $history = dashboardEquipmentHistoryCounts($inspectionTable, $inspectionKey, $dateColumn, $filters);
    $monthly = dashboardEquipmentMonthlyCounts($inspectionTable, $inspectionKey, $dateColumn, $filters);
    // Compare whole calendar months even when a different inspection range is selected.
    $comparisonFilters = $filters;
    $comparisonFilters['start'] = $filters['comparison_start'];
    $comparisonFilters['end'] = $filters['comparison_end'];
    $comparison = dashboardEquipmentMonthlyCounts($inspectionTable, $inspectionKey, $dateColumn, $comparisonFilters);

    return array(
        'active' => (int) $inventoryCounts['active'],
        'inspected' => (int) $inventoryCounts['inspected'],
        'not_inspected' => (int) $inventoryCounts['active'] - (int) $inventoryCounts['inspected'],
        'submissions' => (int) $history['submissions'],
        'history_unique' => (int) $history['unique_equipment'],
        'monthly' => $monthly,
        'comparison' => $comparison
    );
}

// History includes archived units and every inspection submission in the range.
function dashboardEquipmentHistoryCounts($table, $key, $dateColumn, array $filters)
{
    $parameters = [$filters['start'], $filters['end']];
    $branchSql = '';
    $parameterTypes = 'ss';
    if ($filters['branch'] !== 'all') {
        $branchSql = ' AND LOWER(TRIM(i.branch)) = LOWER(TRIM(?))';
        $parameters[] = $filters['branch'];
        $parameterTypes .= 's';
    }
    // This counts header records and distinct identities, never CSK material rows.
    return dashboardSummarySelect(
        "SELECT COUNT(*) AS submissions,
                COUNT(DISTINCT i.$key, i.branch) AS unique_equipment
         FROM $table i WHERE i.$dateColumn >= ? AND i.$dateColumn < ? $branchSql",
        $parameterTypes, $parameters
    )[0];
}

// Count a unit once per month. A unit may appear in more than one month.
function dashboardEquipmentMonthlyCounts($table, $key, $dateColumn, array $filters)
{
    $parameters = [$filters['start'], $filters['end']];
    $branchSql = '';
    $parameterTypes = 'ss';
    if ($filters['branch'] !== 'all') {
        $branchSql = ' AND LOWER(TRIM(i.branch)) = LOWER(TRIM(?))';
        $parameters[] = $filters['branch'];
        $parameterTypes .= 's';
    }
    return dashboardSummarySelect(
        "SELECT DATE_FORMAT(i.$dateColumn, '%Y-%m') AS month,
                COUNT(DISTINCT i.$key, i.branch) AS inspected
         FROM $table i WHERE i.$dateColumn >= ? AND i.$dateColumn < ? $branchSql
         GROUP BY DATE_FORMAT(i.$dateColumn, '%Y-%m') ORDER BY month",
        $parameterTypes, $parameters
    );
}
