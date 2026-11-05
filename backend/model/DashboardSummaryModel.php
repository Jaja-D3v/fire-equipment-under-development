<?php
require_once __DIR__ . '/../config/db.php';

function dashboardSummarySelect($sql, $types = '', array $params = array())
{
    global $conn;
    $stmt = $conn->prepare($sql);
    try {
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $stmt->close();
    }
}

function getDashboardSummaryBranches()
{
    $rows = dashboardSummarySelect(
        "SELECT value FROM maintenance_dropdown_tbl
         WHERE category = 'branch' AND status = 'active' ORDER BY value"
    );
    return array_column($rows, 'value');
}

function getDashboardFireExtinguisherSummary(array $filters)
{
    return dashboardEquipmentSummary(
        'fire_extinguishers_tbl', 'inspection_checklist_tbl',
        'extinguisher_code', 'extinguisher_code', 'date_inspected', $filters
    );
}

function getDashboardChemicalSpillKitSummary(array $filters)
{
    return dashboardEquipmentSummary(
        'chemical_spill_kit_materials_inventory_tbl', 'chemical_spill_kit_inspection_tbl',
        'chemical_spill_kit_id', 'chemical_spill_kit_id', 'inspection_date', $filters
    );
}

// Identifiers below come only from the explicit module functions above.
function dashboardEquipmentSummary($inventoryTable, $inspectionTable, $inventoryKey, $inspectionKey, $dateColumn, array $filters)
{
    $branch = $filters['branch'];
    $scopeSql = $branch === 'all' ? '' : ' AND LOWER(TRIM(k.branch)) = LOWER(TRIM(?))';
    $params = array($filters['start'], $filters['end']);
    $types = 'ss';
    if ($branch !== 'all') {
        $params[] = $branch;
        $types .= 's';
    }

    // Inventory identities are distinct; repeated inspections only satisfy EXISTS once.
    $active = dashboardSummarySelect(
        "SELECT COUNT(*) AS active,
                COALESCE(SUM(EXISTS (
                    SELECT 1 FROM $inspectionTable i
                    WHERE i.$inspectionKey = k.equipment_key
                      AND LOWER(TRIM(i.branch)) = LOWER(TRIM(k.branch))
                      AND i.$dateColumn >= ? AND i.$dateColumn < ?
                )), 0) AS inspected
         FROM (
             SELECT DISTINCT k.$inventoryKey AS equipment_key, k.branch
             FROM $inventoryTable k WHERE k.archived = 0 $scopeSql
         ) k",
        $types, $params
    )[0];

    $history = dashboardEquipmentHistoryCounts($inspectionTable, $inspectionKey, $dateColumn, $filters);
    $monthly = dashboardEquipmentMonthlyCounts($inspectionTable, $inspectionKey, $dateColumn, $filters);
    $comparisonFilters = $filters;
    $comparisonFilters['start'] = $filters['comparison_start'];
    $comparisonFilters['end'] = $filters['comparison_end'];
    $comparison = dashboardEquipmentMonthlyCounts($inspectionTable, $inspectionKey, $dateColumn, $comparisonFilters);

    return array(
        'active' => (int) $active['active'],
        'inspected' => (int) $active['inspected'],
        'not_inspected' => (int) $active['active'] - (int) $active['inspected'],
        'submissions' => (int) $history['submissions'],
        'history_unique' => (int) $history['unique_equipment'],
        'monthly' => $monthly,
        'comparison' => $comparison
    );
}

function dashboardEquipmentHistoryCounts($table, $key, $dateColumn, array $filters)
{
    $params = array($filters['start'], $filters['end']);
    $scopeSql = '';
    $types = 'ss';
    if ($filters['branch'] !== 'all') {
        $scopeSql = ' AND LOWER(TRIM(i.branch)) = LOWER(TRIM(?))';
        $params[] = $filters['branch'];
        $types .= 's';
    }
    // This counts header records and distinct identities, never CSK material rows.
    return dashboardSummarySelect(
        "SELECT COUNT(*) AS submissions,
                COUNT(DISTINCT i.$key, i.branch) AS unique_equipment
         FROM $table i WHERE i.$dateColumn >= ? AND i.$dateColumn < ? $scopeSql",
        $types, $params
    )[0];
}

function dashboardEquipmentMonthlyCounts($table, $key, $dateColumn, array $filters)
{
    $params = array($filters['start'], $filters['end']);
    $scopeSql = '';
    $types = 'ss';
    if ($filters['branch'] !== 'all') {
        $scopeSql = ' AND LOWER(TRIM(i.branch)) = LOWER(TRIM(?))';
        $params[] = $filters['branch'];
        $types .= 's';
    }
    return dashboardSummarySelect(
        "SELECT DATE_FORMAT(i.$dateColumn, '%Y-%m') AS month,
                COUNT(DISTINCT i.$key, i.branch) AS inspected
         FROM $table i WHERE i.$dateColumn >= ? AND i.$dateColumn < ? $scopeSql
         GROUP BY DATE_FORMAT(i.$dateColumn, '%Y-%m') ORDER BY month",
        $types, $params
    );
}
