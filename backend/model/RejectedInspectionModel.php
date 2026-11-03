<?php 
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/ChemicalSpillKitRequiredStockModel.php';

function getRejectedInspections($inspectedBy, $branch, $search = '', $dateFilter = 'all', $equipmentType = 'all')
{
    global $conn;

    $sql = "SELECT * FROM (
        SELECT
            'Fire Extinguisher' AS equipment_type,
            f.inspect_id,
            f.extinguisher_code,
            f.branch,
            f.location,
            f.capacity,
            f.type,
            f.date_inspected,
            f.inspected_by,
            t.status AS evaluation_status,
            f.rejected_by,
            COALESCE(NULLIF(t.rejection_reason, ''), NULLIF(f.reject_reason, '')) AS reject_reason,
            f.action_taken,
            f.target_date_of_implementation,
            COALESCE(f.rejection_date, t.reviewed_at) AS rejection_date
        FROM transactions_tbl AS t
        INNER JOIN inspection_checklist_tbl AS f
            ON t.category = 'Fire Extinguisher'
           AND t.transaction_type = 'inspection'
           AND t.inspection_id = f.inspect_id
           AND t.branch = f.branch
        WHERE t.transaction_type = 'inspection'
          AND t.status = 'Rejected'
          AND t.category = 'Fire Extinguisher'
          AND LOWER(TRIM(f.inspected_by)) = LOWER(TRIM(?))
          AND LOWER(TRIM(f.branch)) = LOWER(TRIM(?))
          AND (? = 'all' OR t.category = ?)
          AND NOT EXISTS (
              SELECT 1
              FROM inspection_checklist_tbl AS newer
              WHERE LOWER(TRIM(newer.extinguisher_code)) = LOWER(TRIM(f.extinguisher_code))
                AND LOWER(TRIM(newer.branch)) = LOWER(TRIM(f.branch))
                AND (newer.date_inspected > f.date_inspected
                     OR (newer.date_inspected = f.date_inspected AND newer.inspect_id > f.inspect_id))
          )

        UNION ALL

        SELECT
            'Chemical Spill Kit' AS equipment_type,
            c.inspection_id AS inspect_id,
            c.chemical_spill_kit_code AS extinguisher_code,
            c.branch,
            c.location,
            NULL AS capacity,
            'Chemical Spill Kit' AS type,
            c.inspection_date AS date_inspected,
            c.inspected_by,
            t.status AS evaluation_status,
            NULL AS rejected_by,
            t.rejection_reason AS reject_reason,
            NULL AS action_taken,
            NULL AS target_date_of_implementation,
            t.reviewed_at AS rejection_date
        FROM transactions_tbl AS t
        INNER JOIN chemical_spill_kit_inspection_tbl AS c
            ON t.category = 'Chemical Spill Kit'
           AND t.transaction_type = 'inspection'
           AND t.inspection_id = c.inspection_id
           AND t.branch = c.branch
        WHERE t.transaction_type = 'inspection'
          AND t.status = 'Rejected'
          AND t.category = 'Chemical Spill Kit'
          AND LOWER(TRIM(c.inspected_by)) = LOWER(TRIM(?))
          AND LOWER(TRIM(c.branch)) = LOWER(TRIM(?))
          AND (? = 'all' OR t.category = ?)
          AND NOT EXISTS (
              SELECT 1
              FROM chemical_spill_kit_inspection_tbl AS newer
              WHERE LOWER(TRIM(newer.chemical_spill_kit_code)) = LOWER(TRIM(c.chemical_spill_kit_code))
                AND LOWER(TRIM(newer.branch)) = LOWER(TRIM(c.branch))
                AND (newer.inspection_date > c.inspection_date
                     OR (newer.inspection_date = c.inspection_date AND newer.inspection_id > c.inspection_id))
          )
    ) AS rejected_inspections
    WHERE 1 = 1";

    $types = 'ssssssss';
    $params = array(
        $inspectedBy,
        $branch,
        $equipmentType,
        $equipmentType,
        $inspectedBy,
        $branch,
        $equipmentType,
        $equipmentType
    );

    if ($search !== '') 
    {
        $sql .= " AND (extinguisher_code LIKE ? OR location LIKE ? OR reject_reason LIKE ?)";

        $keyword = '%' . $search . '%';
        $types .= 'sss';
        array_push( $params, $keyword, $keyword, $keyword );
    }

    switch ($dateFilter) {
        case 'today':
            $sql .= " AND DATE(date_inspected) = CURDATE()";
            break;

        case 'yesterday':
            $sql .= " AND DATE(date_inspected) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            break;

        case '7':
            $sql .= " AND date_inspected >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;

        case '30':
            $sql .= " AND date_inspected >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
    }

    $sql .= " ORDER BY date_inspected DESC, inspect_id DESC, equipment_type ASC";
    $stmt = $conn->prepare($sql);

    if (!$stmt) 
    {
        return false;
    }

    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) 
    {
        $stmt->close();
        return false;
    }

    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return getRejectedInspectionChemicalKitDetails($data);
}

function getRejectedInspectionChemicalKitDetails(array $inspections): array
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
    foreach ($inspections as $inspection) {
        if (($inspection['equipment_type'] ?? '') === 'Chemical Spill Kit') {
            $inspectionIds[] = (int) ($inspection['inspect_id'] ?? 0);
        }
    }
    $inspectionIds = array_values(array_unique(array_filter($inspectionIds)));
    if (!$inspectionIds) {
        return $inspections;
    }

    $idList = implode(',', $inspectionIds);
    $sql = "SELECT i.inspection_id, i.branch AS inspection_branch,
                   c.material_name, c.current_stocks, c.remarks
            FROM chemical_spill_kit_inspection_tbl AS i
            LEFT JOIN chemical_spill_kit_materials_checklist_tbl AS c
                ON c.inspection_id = i.inspection_id
            WHERE i.inspection_id IN ({$idList})
            ORDER BY i.inspection_id ASC, c.checklist_id ASC";
    $result = mysqli_query($conn, $sql);
    $materialsByInspection = array();
    $branchesByInspection = array();
    $requiredStocksByBranch = array();

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $inspectionId = (int) $row['inspection_id'];
            $inspectionBranch = trim((string) $row['inspection_branch']);
            $branchesByInspection[$inspectionId] = $inspectionBranch;
            if (!array_key_exists($inspectionBranch, $requiredStocksByBranch)) {
                $requiredStocksByBranch[$inspectionBranch] = getRequiredStockModel($inspectionBranch);
            }
            if ($row['material_name'] === null) {
                continue;
            }
            $key = strtolower(trim((string) $row['material_name']));
            $materialsByInspection[$inspectionId][$key] = array(
                'material_name' => $row['material_name'],
                'current_stocks' => $row['current_stocks'],
                'remarks' => $row['remarks']
            );
        }
        mysqli_free_result($result);
    } else {
        error_log('Rejected Inspection Chemical Spill Kit checklist query failed: ' . mysqli_error($conn));
    }

    foreach ($inspections as &$inspection) {
        if (($inspection['equipment_type'] ?? '') !== 'Chemical Spill Kit') {
            continue;
        }

        $inspectionId = (int) $inspection['inspect_id'];
        $inspectionBranch = $branchesByInspection[$inspectionId] ?? '';
        $requiredStocks = array_key_exists($inspectionBranch, $requiredStocksByBranch)
            ? $requiredStocksByBranch[$inspectionBranch]
            : false;
        $requiredStockByName = array();
        if (is_array($requiredStocks)) {
            foreach ($requiredStocks as $name => $value) {
                $requiredStockByName[strtolower(trim((string) $name))] = $value;
            }
        }

        $materials = array();
        foreach ($materialNames as $materialName) {
            $key = strtolower(trim($materialName));
            $material = $materialsByInspection[$inspectionId][$key] ?? array(
                'material_name' => $materialName,
                'current_stocks' => null,
                'remarks' => null
            );
            $requiredExists = is_array($requiredStocks) && array_key_exists($key, $requiredStockByName);
            $requiredStock = $requiredExists ? $requiredStockByName[$key] : null;
            $requiredValid = $requiredExists
                && $requiredStock !== null
                && trim((string) $requiredStock) !== ''
                && is_numeric($requiredStock)
                && is_finite((float) $requiredStock)
                && (float) $requiredStock >= 0;
            $currentStock = $material['current_stocks'];
            $currentValid = $currentStock !== null
                && trim((string) $currentStock) !== ''
                && is_numeric($currentStock)
                && is_finite((float) $currentStock)
                && (float) $currentStock >= 0;

            if ($requiredStocks === false) {
                $message = 'Required stock could not be retrieved for the inspection branch.';
            } elseif (!$requiredExists) {
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
        $inspection['materials'] = $materials;
    }
    unset($inspection);

    return $inspections;
}

function getRejectedInspectionCountForSidebar($inspectedBy, $branch)
{
    global $conn;

    $sql = "
        SELECT COUNT(*) AS total
        FROM inspection_checklist_tbl AS i
        WHERE i.evaluation_status = 'Rejected'
          AND LOWER(TRIM(i.inspected_by)) = LOWER(TRIM(?))
          AND LOWER(TRIM(i.branch)) = LOWER(TRIM(?))
          AND i.inspect_id = (
              SELECT i2.inspect_id
              FROM inspection_checklist_tbl AS i2
              WHERE LOWER(TRIM(i2.extinguisher_code)) =
                    LOWER(TRIM(i.extinguisher_code))
                AND LOWER(TRIM(i2.branch)) = LOWER(TRIM(i.branch))
              ORDER BY i2.date_inspected DESC, i2.inspect_id DESC
              LIMIT 1
          )
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return 0;
    }

    mysqli_stmt_bind_param($stmt, 'ss', $inspectedBy, $branch);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return 0;
    }

    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    return (int) ($row['total'] ?? 0);
}
