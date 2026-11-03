<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/ChemicalSpillKitRequiredStockModel.php';

$branch = trim((string) ($_SESSION['Branch'] ?? ''));

// Transactions are the listing source; category-qualified joins resolve each
// transaction ID against only its matching inspection table.
function myInspectionsGetTransactionRowsSql(): string
{
    return "(
        SELECT
            t.transaction_id,
            t.inspection_id,
            t.category AS equipment_type,
            t.branch,
            CASE LOWER(TRIM(t.status))
                WHEN 'pending' THEN 'Pending'
                WHEN 'approved' THEN 'Approved'
                WHEN 'rejected' THEN 'Rejected'
                ELSE t.status
            END AS evaluation_status,
            t.submitted_by,
            t.reviewed_by,
            t.rejection_reason,
            t.reviewed_at,
            t.submitted_at,
            f.inspect_id,
            f.extinguisher_code,
            f.location,
            f.type,
            f.capacity,
            f.class,
            f.date_inspected,
            f.inspected_by,
            f.verified_and_approved_by,
            f.action_taken,
            f.remarks,
            f.target_date_of_implementation,
            f.is_seal_ok,
            f.is_pin_ok,
            f.is_pressure_ok,
            f.is_hose_ok,
            f.is_nozzle_ok,
            f.is_belt_ok,
            f.is_cylinder_body_ok,
            f.is_demarcation_line_ok,
            f.is_signage_ok,
            f.status AS condition_status,
            f.rejected_by,
            f.is_cleaning_of_unit_ok,
            f.reject_reason,
            f.rejection_date
        FROM transactions_tbl AS t
        INNER JOIN inspection_checklist_tbl AS f
            ON t.category = 'Fire Extinguisher'
           AND t.transaction_type = 'inspection'
           AND t.inspection_id = f.inspect_id
           AND t.branch = f.branch

        UNION ALL

        SELECT
            t.transaction_id,
            t.inspection_id,
            t.category AS equipment_type,
            t.branch,
            CASE LOWER(TRIM(t.status))
                WHEN 'pending' THEN 'Pending'
                WHEN 'approved' THEN 'Approved'
                WHEN 'rejected' THEN 'Rejected'
                ELSE t.status
            END AS evaluation_status,
            t.submitted_by,
            t.reviewed_by,
            t.rejection_reason,
            t.reviewed_at,
            t.submitted_at,
            c.inspection_id AS inspect_id,
            c.chemical_spill_kit_code AS extinguisher_code,
            c.location,
            'Chemical Spill Kit' AS type,
            NULL AS capacity,
            NULL AS class,
            c.inspection_date AS date_inspected,
            c.inspected_by,
            t.reviewed_by AS verified_and_approved_by,
            NULL AS action_taken,
            NULL AS remarks,
            NULL AS target_date_of_implementation,
            NULL AS is_seal_ok,
            NULL AS is_pin_ok,
            NULL AS is_pressure_ok,
            NULL AS is_hose_ok,
            NULL AS is_nozzle_ok,
            NULL AS is_belt_ok,
            NULL AS is_cylinder_body_ok,
            NULL AS is_demarcation_line_ok,
            NULL AS is_signage_ok,
            NULL AS condition_status,
            NULL AS rejected_by,
            NULL AS is_cleaning_of_unit_ok,
            t.rejection_reason AS reject_reason,
            CASE WHEN LOWER(TRIM(t.status)) = 'rejected' THEN t.reviewed_at ELSE NULL END AS rejection_date
        FROM transactions_tbl AS t
        INNER JOIN chemical_spill_kit_inspection_tbl AS c
            ON t.category = 'Chemical Spill Kit'
           AND t.transaction_type = 'inspection'
           AND t.inspection_id = c.inspection_id
           AND t.branch = c.branch
    )";
}

/**
 * Build shared filters for listing and counting.
 * Apply the page's session-owned inspector and branch restrictions to transactions.
 */
function myInspectionsBuildFilters(
    string $inspectorName,
    string $search,
    string $dateFilter,
    string $statusFilter,
    string $equipmentType = 'all'
): array {
    global $branch;

    $where = array('i.submitted_by = ?', 'i.inspected_by = ?', 'i.branch = ?');

    $params = [$inspectorName, $inspectorName, $branch];
    $types = 'sss';

    if ($search !== '') {
        $where[] = "(
            i.extinguisher_code LIKE ?
            OR i.evaluation_status LIKE ?
            OR i.inspected_by LIKE ?
            OR i.location LIKE ?
            OR i.equipment_type LIKE ?
        )";

        $searchTerm = '%' . $search . '%';

        for ($x = 0; $x < 5; $x++) {
            $params[] = $searchTerm;
            $types .= 's';
        }
    }

    switch ($dateFilter) {
        case 'today':
            $where[] = 'DATE(i.date_inspected) = CURDATE()';
            break;

        case 'yesterday':
            $where[] = 'DATE(i.date_inspected) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)';
            break;

        case '7':
            $where[] = 'i.date_inspected >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
            $where[] = 'i.date_inspected <= NOW()';
            break;

        case '30':
            $where[] = 'i.date_inspected >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
            $where[] = 'i.date_inspected <= NOW()';
            break;
    }

    if ($statusFilter !== 'all') {
        $where[] = 'LOWER(i.evaluation_status) = LOWER(?)';
        $params[] = $statusFilter;
        $types .= 's';
    }

    if ($equipmentType !== 'all' && in_array($equipmentType, array('Fire Extinguisher', 'Chemical Spill Kit'), true)) {
        $where[] = 'i.equipment_type = ?';
        $params[] = $equipmentType;
        $types .= 's';
    }

    return [
        'sql' => implode(' AND ', $where),
        'types' => $types,
        'params' => $params,
    ];
}

/**
 * Bind parameters dynamically.
 */
function myInspectionsBindParams(
    mysqli_stmt $stmt,
    string $types,
    array &$params
): bool {
    if ($types === '' || empty($params)) {
        return true;
    }

    $bindValues = [$types];

    foreach ($params as &$value) {
        $bindValues[] = &$value;
    }

    unset($value);

    return call_user_func_array(
        [$stmt, 'bind_param'],
        $bindValues
    );
}

function myInspectionsLatestRecordCondition(string $rowsSql): string
{
    return "NOT EXISTS (
        SELECT 1
        FROM {$rowsSql} AS newer
        WHERE newer.equipment_type = i.equipment_type
          AND newer.extinguisher_code = i.extinguisher_code
          AND newer.branch = i.branch
          AND (
              newer.date_inspected > i.date_inspected
              OR (
                  newer.date_inspected = i.date_inspected
                  AND newer.inspect_id > i.inspect_id
              )
              OR (
                  newer.date_inspected = i.date_inspected
                  AND newer.inspect_id = i.inspect_id
                  AND newer.transaction_id > i.transaction_id
              )
          )
    )";
}

function myInspectionsAttachChemicalKitChecklists(array $records): array
{
    global $conn;

    $materialNames = array(
        'Pads',
        'Boom/Pillow',
        'Socks',
        'Disposal Bag',
        'Goggles',
        'Face Mask',
        'Rubber Gloves',
        'Nitrile Gloves'
    );

    $inspectionIds = array();
    foreach ($records as $record) {
        if (($record['equipment_type'] ?? '') === 'Chemical Spill Kit') {
            $inspectionIds[] = (int) ($record['inspect_id'] ?? 0);
        }
    }
    $inspectionIds = array_values(array_unique(array_filter($inspectionIds)));
    if (!$inspectionIds) {
        return $records;
    }

    $idList = implode(',', $inspectionIds);
    $sql = "SELECT i.inspection_id, i.branch AS inspection_branch,
                   c.material_name, c.current_stocks, c.status, c.remarks
            FROM chemical_spill_kit_inspection_tbl AS i
            LEFT JOIN chemical_spill_kit_materials_checklist_tbl AS c
                ON c.inspection_id = i.inspection_id
            WHERE i.inspection_id IN ({$idList})
            ORDER BY i.inspection_id ASC, c.checklist_id ASC";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log('My Inspections Chemical Spill Kit checklist query failed: ' . mysqli_error($conn));
        foreach ($records as &$record) {
            if (($record['equipment_type'] ?? '') === 'Chemical Spill Kit') {
                $record['materials'] = myInspectionsBuildChemicalKitMaterials(
                    array(),
                    $materialNames,
                    false,
                    $record['branch'] ?? ''
                );
            }
        }
        unset($record);
        return $records;
    }

    $materialsByInspection = array();
    $branchesByInspection = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $inspectionId = (int) $row['inspection_id'];
        $branchesByInspection[$inspectionId] = $row['inspection_branch'];
        if ($row['material_name'] === null) {
            continue;
        }
        $materialsByInspection[$inspectionId][strtolower(trim((string) $row['material_name']))] = array(
            'material_name' => $row['material_name'],
            'current_stocks' => $row['current_stocks'],
            'status' => $row['status'],
            'remarks' => $row['remarks']
        );
    }
    mysqli_free_result($result);

    foreach ($records as &$record) {
        if (($record['equipment_type'] ?? '') === 'Chemical Spill Kit') {
            $inspectionId = (int) $record['inspect_id'];
            $inspectionBranch = $branchesByInspection[$inspectionId] ?? ($record['branch'] ?? '');
            $requiredStocks = getRequiredStockModel($inspectionBranch);
            $record['materials'] = myInspectionsBuildChemicalKitMaterials(
                $materialsByInspection[$inspectionId] ?? array(),
                $materialNames,
                $requiredStocks,
                $inspectionBranch
            );
        }
    }
    unset($record);

    return $records;
}

function myInspectionsBuildChemicalKitMaterials(array $checklist, array $materialNames, $requiredStocks, string $branch): array
{
    $requiredStockByName = array();
    if (is_array($requiredStocks)) {
        foreach ($requiredStocks as $name => $value) {
            $requiredStockByName[strtolower(trim((string) $name))] = $value;
        }
    }

    $materials = array();
    foreach ($materialNames as $materialName) {
        $key = strtolower(trim($materialName));
        $material = $checklist[$key] ?? array(
            'material_name' => $materialName,
            'current_stocks' => null,
            'status' => null,
            'remarks' => null
        );
        $hasRequiredStock = is_array($requiredStocks) && array_key_exists($key, $requiredStockByName);
        $requiredStock = $hasRequiredStock ? $requiredStockByName[$key] : null;
        $requiredValid = $hasRequiredStock && $requiredStock !== null && trim((string) $requiredStock) !== '' && is_numeric($requiredStock) && is_finite((float) $requiredStock) && (float) $requiredStock >= 0;
        $currentStock = $material['current_stocks'] ?? null;
        $currentValid = $currentStock !== null && trim((string) $currentStock) !== '' && is_numeric($currentStock) && is_finite((float) $currentStock) && (float) $currentStock >= 0;

        if ($requiredStocks === false) {
            $message = 'Required stock could not be retrieved for ' . $branch . '.';
        } elseif (!$hasRequiredStock) {
            $message = 'Required stock is not configured for this branch.';
        } elseif (!$requiredValid) {
            $message = 'Required stock configuration is invalid.';
        } elseif (!$currentValid) {
            $message = 'Current stock is unavailable or invalid.';
        } else {
            $message = '';
        }

        $material['required_stock'] = $requiredValid ? (float) $requiredStock : null;
        $material['evaluation_result'] = ($requiredValid && $currentValid)
            ? ((float) $currentStock >= (float) $requiredStock ? 'Good' : 'No Good')
            : 'Unavailable';
        $material['evaluation_message'] = $message;
        $materials[] = $material;
    }

    return $materials;
}

/**
 * Get paginated inspection records.
 */

function getMyInspectionsModel(
    string $inspectorName,
    string $search,
    string $dateFilter,
    string $statusFilter,
    int $limit,
    int $offset,
    string $equipmentType = 'all'
): array {
    global $conn;

    $filters = myInspectionsBuildFilters(
        $inspectorName,
        $search,
        $dateFilter,
        $statusFilter,
        $equipmentType
    );

    $rowsSql = myInspectionsGetTransactionRowsSql();
    $latestCondition = myInspectionsLatestRecordCondition($rowsSql);
    $sql = "SELECT i.*
            FROM {$rowsSql} AS i
            WHERE {$filters['sql']}
              AND {$latestCondition}
            ORDER BY i.date_inspected DESC, i.transaction_id DESC
            LIMIT ? OFFSET ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log(
            'My Inspections prepare failed: ' . mysqli_error($conn)
        );
        return [];
    }

    $params = array_merge(
        $filters['params'],
        [$limit, $offset]
    );

    $types = $filters['types'] . 'ii';

    if (!myInspectionsBindParams($stmt, $types, $params)) {
        error_log(
            'My Inspections bind failed: ' . mysqli_stmt_error($stmt)
        );
        mysqli_stmt_close($stmt);
        return [];
    }

    if (!mysqli_stmt_execute($stmt)) {
        error_log(
            'My Inspections query failed: ' . mysqli_stmt_error($stmt)
        );
        mysqli_stmt_close($stmt);
        return [];
    }

    $result = mysqli_stmt_get_result($stmt);
    $records = $result
        ? mysqli_fetch_all($result, MYSQLI_ASSOC)
        : [];

    mysqli_stmt_close($stmt);

    return myInspectionsAttachChemicalKitChecklists($records);
}

/**
 * Count records using the same filters as the listing.
 */
function getMyInspectionsCountModel(
    string $inspectorName,
    string $search,
    string $dateFilter,
    string $statusFilter,
    string $equipmentType = 'all'
): int {
    global $conn;

    $filters = myInspectionsBuildFilters(
        $inspectorName,
        $search,
        $dateFilter,
        $statusFilter,
        $equipmentType
    );

    $rowsSql = myInspectionsGetTransactionRowsSql();
    $latestCondition = myInspectionsLatestRecordCondition($rowsSql);
    $sql = "SELECT COUNT(*) AS total
            FROM {$rowsSql} AS i
            WHERE {$filters['sql']}
              AND {$latestCondition}";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log(
            'My Inspections count prepare failed: ' . mysqli_error($conn)
        );
        return 0;
    }

    $params = array_merge(
        $filters['params']
    );

    $types = $filters['types'];

    if (!myInspectionsBindParams($stmt, $types, $params)) {
        error_log(
            'My Inspections count bind failed: ' . mysqli_stmt_error($stmt)
        );
        mysqli_stmt_close($stmt);
        return 0;
    }

    if (!mysqli_stmt_execute($stmt)) {
        error_log(
            'My Inspections count query failed: ' . mysqli_stmt_error($stmt)
        );
        mysqli_stmt_close($stmt);
        return 0;
    }

    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;

    mysqli_stmt_close($stmt);

    return (int) ($row['total'] ?? 0);
}




/**
 * Summary counts for the logged-in inspector.
 */
function getMyInspectionsSummaryModel(string $inspectorName, string $equipmentType = 'all'): array
{
    global $conn;

    $branch = $_SESSION['Branch'] ?? '';

    $summary = [
        'total' => 0,
        'pending' => 0,
        'approved' => 0,
        'rejected' => 0,
    ];

    $rowsSql = myInspectionsGetTransactionRowsSql();
    $sql = "SELECT COUNT(*) AS total,
                   COALESCE(SUM(LOWER(evaluation_status) = 'pending'), 0) AS pending,
                   COALESCE(SUM(LOWER(evaluation_status) = 'approved'), 0) AS approved,
                   COALESCE(SUM(LOWER(evaluation_status) = 'rejected'), 0) AS rejected
            FROM {$rowsSql} AS i
            WHERE i.submitted_by = ?
              AND i.inspected_by = ?
              AND i.branch = ?
              AND (? = 'all' OR i.equipment_type = ?)";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log(
            'My Inspections summary prepare failed: ' . mysqli_error($conn)
        );
        return $summary;
    }

    mysqli_stmt_bind_param($stmt, 'sssss', $inspectorName, $inspectorName, $branch, $equipmentType, $equipmentType);

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $row = $result ? mysqli_fetch_assoc($result) : null;

        if ($row) {
            $summary = [
                'total' => (int) $row['total'],
                'pending' => (int) $row['pending'],
                'approved' => (int) $row['approved'],
                'rejected' => (int) $row['rejected'],
            ];
        }
    } else {
        error_log(
            'My Inspections summary query failed: ' . mysqli_stmt_error($stmt)
        );
    }

    mysqli_stmt_close($stmt);

    return $summary;
}
