
<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/ChemicalSpillKitRequiredStockModel.php';

// Fetch a Chemical Spill Kit inspection and its material checklist for evaluation.
function getChemicalSpillKitEvaluationDetails($inspectionId, $role, $branch)
{
    global $conn;

    $inspectionId = (int) $inspectionId;
    $role = strtolower(trim((string) $role));
    $branch = trim((string) $branch);
    if ($inspectionId <= 0 || !in_array($role, array('admin', 'inspector'), true)) {
        return false;
    }

    $sql = "SELECT i.inspection_id, i.chemical_spill_kit_id,
                   i.chemical_spill_kit_code, i.location, i.branch,
                   i.inspected_by, i.inspection_date,
                   t.status AS evaluation_status, t.submitted_by,
                   t.reviewed_by, t.rejection_reason, t.reviewed_at,
                   c.material_name, c.current_stocks, c.status AS material_status,
                   c.remarks
            FROM chemical_spill_kit_inspection_tbl AS i
            INNER JOIN transactions_tbl AS t
                ON t.inspection_id = i.inspection_id
               AND t.category = 'Chemical Spill Kit'
               AND t.transaction_type = 'inspection'
            LEFT JOIN chemical_spill_kit_materials_checklist_tbl AS c
                ON c.inspection_id = i.inspection_id
            WHERE i.inspection_id = ?
              AND (? = 'admin' OR LOWER(TRIM(i.branch)) = LOWER(TRIM(?)))
            ORDER BY t.transaction_id DESC, c.checklist_id ASC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log('CSK evaluation detail prepare failed: ' . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'iss', $inspectionId, $role, $branch);
    if (!mysqli_stmt_execute($stmt)) {
        error_log('CSK evaluation detail query failed: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    $result = mysqli_stmt_get_result($stmt);
    $inspection = null;
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if ($inspection === null) {
                $inspection = array(
                    'inspection_id' => (int) $row['inspection_id'],
                    'chemical_spill_kit_id' => (int) $row['chemical_spill_kit_id'],
                    'chemical_spill_kit_code' => $row['chemical_spill_kit_code'],
                    'location' => $row['location'],
                    'branch' => $row['branch'],
                    'inspected_by' => $row['inspected_by'],
                    'inspection_date' => $row['inspection_date'],
                    'evaluation_status' => ucfirst(strtolower((string) $row['evaluation_status'])),
                    'submitted_by' => $row['submitted_by'],
                    'reviewed_by' => $row['reviewed_by'],
                    'rejection_reason' => $row['rejection_reason'],
                    'reviewed_at' => $row['reviewed_at'],
                    'materials' => array()
                );
            }
            if ($row['material_name'] !== null) {
                $materialKey = strtolower(trim((string) $row['material_name']));
                $inspection['materials'][$materialKey] = array(
                    'material_name' => $row['material_name'],
                    'current_stocks' => $row['current_stocks'],
                    'status' => $row['material_status'],
                    'remarks' => $row['remarks']
                );
            }
        }
        mysqli_free_result($result);
    }
    mysqli_stmt_close($stmt);

    if ($inspection === null) {
        return null;
    }

    // Reuse the branch-scoped required-stock model for this inspection's own branch.
    $requiredStockRecords = getRequiredStockModel($inspection['branch']);
    $requiredStockByName = array();
    if (is_array($requiredStockRecords)) {
        foreach ($requiredStockRecords as $materialName => $requiredStock) {
            $requiredStockByName[strtolower(trim((string) $materialName))] = $requiredStock;
        }
    }

    // Keep the evaluation display complete even if an inspection checklist row is missing.
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
    $completeMaterials = array();
    foreach ($materialNames as $materialName) {
        $materialKey = strtolower($materialName);
        $material = $inspection['materials'][$materialKey] ?? array(
            'material_name' => $materialName,
            'current_stocks' => null,
            'status' => null,
            'remarks' => null
        );
        $requiredExists = $requiredStockRecords !== false && array_key_exists($materialKey, $requiredStockByName);
        $requiredStock = $requiredExists ? $requiredStockByName[$materialKey] : null;
        $requiredIsValid = $requiredExists && is_numeric($requiredStock) && is_finite((float) $requiredStock) && (float) $requiredStock >= 0;
        $currentStock = $material['current_stocks'];
        $currentIsValid = $currentStock !== null && trim((string) $currentStock) !== '' && is_numeric($currentStock) && is_finite((float) $currentStock) && (float) $currentStock >= 0;

        if ($requiredStockRecords === false) {
            $material['required_stock_message'] = 'Required stock could not be retrieved.';
        } elseif (!$requiredExists) {
            $material['required_stock_message'] = 'Required stock is not configured for this branch.';
        } elseif (!$requiredIsValid) {
            $material['required_stock_message'] = 'Required stock configuration is invalid.';
        } elseif (!$currentIsValid) {
            $material['required_stock_message'] = 'Current stock is unavailable or invalid.';
        } else {
            $material['required_stock_message'] = '';
        }

        $material['required_stock'] = $requiredIsValid ? (float) $requiredStock : null;
        $material['evaluation_result'] = ($requiredIsValid && $currentIsValid)
            ? ((float) $currentStock >= (float) $requiredStock ? 'Good' : 'No Good')
            : 'Unavailable';
        $completeMaterials[] = $material;
    }
    $inspection['materials'] = $completeMaterials;

    return $inspection;
}

// Copy approved inspection values into the corresponding current inventory rows.
// The caller must hold an open transaction and have approved the pending transaction.
function updateChemicalSpillKitInventoryFromInspection($inspectionId, $kitId, $branch, $kitCode)
{
    global $conn;

    $inspectionId = (int) $inspectionId;
    $kitId = (int) $kitId;
    $branch = trim((string) $branch);
    $kitCode = trim((string) $kitCode);
    if ($inspectionId <= 0 || $kitId <= 0 || $branch === '' || $kitCode === '') {
        throw new RuntimeException('Chemical Spill Kit inventory identity is invalid.');
    }

    $materials = array(
        'pads' => array('checklist' => 'Pads', 'inventory' => 'Pads'),
        'boom_pillow' => array('checklist' => 'Boom/Pillow', 'inventory' => 'Boom Pillow'),
        'socks' => array('checklist' => 'Socks', 'inventory' => 'Socks'),
        'disposal_bag' => array('checklist' => 'Disposal Bag', 'inventory' => 'Disposal Bag'),
        'goggles' => array('checklist' => 'Goggles', 'inventory' => 'Goggles'),
        'face_mask' => array('checklist' => 'Face Mask', 'inventory' => 'Face Mask'),
        'rubber_gloves' => array('checklist' => 'Rubber Gloves', 'inventory' => 'Rubber Gloves'),
        'nitrile_gloves' => array('checklist' => 'Nitrile Gloves', 'inventory' => 'Nitrile Gloves')
    );
    $checklistNameToField = array();
    $inventoryNameToField = array();
    foreach ($materials as $field => $material) {
        $checklistNameToField[strtolower($material['checklist'])] = $field;
        $inventoryNameToField[strtolower($material['inventory'])] = $field;
    }
    // Accept the checklist spelling if it already exists in a companion row.
    $inventoryNameToField['boom/pillow'] = 'boom_pillow';

    // Lock and verify the actual kit row and its recorded branch/code relationship.
    $kitStmt = mysqli_prepare(
        $conn,
        'SELECT chemical_spill_kit_id
         FROM chemical_spill_kit_materials_inventory_tbl
         WHERE chemical_spill_kit_id = ? AND branch = ? AND chemical_spill_kit_code = ?
         FOR UPDATE'
    );
    if (!$kitStmt) {
        throw new RuntimeException('Could not prepare the Chemical Spill Kit inventory lookup.');
    }
    mysqli_stmt_bind_param($kitStmt, 'iss', $kitId, $branch, $kitCode);
    if (!mysqli_stmt_execute($kitStmt)) {
        $message = mysqli_stmt_error($kitStmt);
        mysqli_stmt_close($kitStmt);
        throw new RuntimeException('Could not lock the Chemical Spill Kit inventory row: ' . $message);
    }
    $kitResult = mysqli_stmt_get_result($kitStmt);
    $kitRow = $kitResult ? mysqli_fetch_assoc($kitResult) : null;
    if ($kitResult) {
        mysqli_free_result($kitResult);
    }
    mysqli_stmt_close($kitStmt);
    if (!$kitRow) {
        throw new RuntimeException('The Chemical Spill Kit inventory row is missing for this inspection.');
    }

    // Index checklist values by material identity, never by row order.
    $checklistStmt = mysqli_prepare(
        $conn,
        'SELECT material_name, current_stocks, status, remarks
         FROM chemical_spill_kit_materials_checklist_tbl
         WHERE inspection_id = ?
         ORDER BY checklist_id ASC
         FOR UPDATE'
    );
    if (!$checklistStmt) {
        throw new RuntimeException('Could not prepare the Chemical Spill Kit checklist lookup.');
    }
    mysqli_stmt_bind_param($checklistStmt, 'i', $inspectionId);
    if (!mysqli_stmt_execute($checklistStmt)) {
        $message = mysqli_stmt_error($checklistStmt);
        mysqli_stmt_close($checklistStmt);
        throw new RuntimeException('Could not load the approved inspection checklist: ' . $message);
    }
    $checklistResult = mysqli_stmt_get_result($checklistStmt);
    $checklistByField = array();
    if ($checklistResult) {
        while ($row = mysqli_fetch_assoc($checklistResult)) {
            $materialKey = strtolower(trim((string) $row['material_name']));
            if (!isset($checklistNameToField[$materialKey])) {
                throw new RuntimeException('The inspection checklist contains an unknown material name.');
            }
            $field = $checklistNameToField[$materialKey];
            if (isset($checklistByField[$field])) {
                throw new RuntimeException('The inspection checklist contains a duplicate material.');
            }

            $quantity = $row['current_stocks'];
            if (
                $quantity === null ||
                trim((string) $quantity) === '' ||
                strlen(trim((string) $quantity)) > 20 ||
                !is_numeric($quantity) ||
                !is_finite((float) $quantity) ||
                (float) $quantity < 0
            ) {
                throw new RuntimeException('An inspected material quantity is missing or invalid.');
            }
            $status = trim((string) $row['status']);
            if (!in_array($status, array('Good', 'No Good'), true)) {
                throw new RuntimeException('An inspected material status is missing or invalid.');
            }

            $checklistByField[$field] = array(
                'quantity' => trim((string) $quantity),
                'status' => $status,
                'remarks' => $row['remarks']
            );
        }
        mysqli_free_result($checklistResult);
    }
    mysqli_stmt_close($checklistStmt);
    if (count($checklistByField) !== count($materials)) {
        throw new RuntimeException('The inspection checklist does not contain all eight materials.');
    }

    // Lock companion rows for this exact kit and identify each one by its name.
    $inventoryStmt = mysqli_prepare(
        $conn,
        'SELECT m.current_material_id, m.material_name
         FROM chemical_spill_kit_current_inventory_materials_tbl AS m
         INNER JOIN chemical_spill_kit_materials_inventory_tbl AS k
             ON k.chemical_spill_kit_id = m.chemical_spill_kit_id
         WHERE m.chemical_spill_kit_id = ? AND k.branch = ? AND k.chemical_spill_kit_code = ?
         ORDER BY m.current_material_id ASC
         FOR UPDATE'
    );
    if (!$inventoryStmt) {
        throw new RuntimeException('Could not prepare the current material records lookup.');
    }
    mysqli_stmt_bind_param($inventoryStmt, 'iss', $kitId, $branch, $kitCode);
    if (!mysqli_stmt_execute($inventoryStmt)) {
        $message = mysqli_stmt_error($inventoryStmt);
        mysqli_stmt_close($inventoryStmt);
        throw new RuntimeException('Could not lock current material records: ' . $message);
    }
    $inventoryResult = mysqli_stmt_get_result($inventoryStmt);
    if (!$inventoryResult) {
        $message = mysqli_stmt_error($inventoryStmt);
        mysqli_stmt_close($inventoryStmt);
        throw new RuntimeException('Could not read current material records: ' . $message);
    }
    $inventoryRowsByField = array();
    while ($row = mysqli_fetch_assoc($inventoryResult)) {
        $materialKey = strtolower(trim((string) $row['material_name']));
        if (!isset($inventoryNameToField[$materialKey])) {
            continue;
        }
        $field = $inventoryNameToField[$materialKey];
        if (isset($inventoryRowsByField[$field])) {
            mysqli_free_result($inventoryResult);
            mysqli_stmt_close($inventoryStmt);
            throw new RuntimeException('Duplicate current inventory material records were found for this kit.');
        }
        $inventoryRowsByField[$field] = array(
            'id' => (int) $row['current_material_id'],
            'name' => $row['material_name']
        );
    }
    mysqli_free_result($inventoryResult);
    mysqli_stmt_close($inventoryStmt);

    // Replace the kit's current quantity columns with the inspected quantities.
    $quantitySql = 'UPDATE chemical_spill_kit_materials_inventory_tbl
                    SET pads = ?, boom_pillow = ?, socks = ?, disposal_bag = ?,
                        goggles = ?, face_mask = ?, rubber_gloves = ?, nitrile_gloves = ?
                    WHERE chemical_spill_kit_id = ? AND branch = ? AND chemical_spill_kit_code = ?';
    $quantityStmt = mysqli_prepare($conn, $quantitySql);
    if (!$quantityStmt) {
        throw new RuntimeException('Could not prepare the Chemical Spill Kit quantity update.');
    }
    $pads = $checklistByField['pads']['quantity'];
    $boomPillow = $checklistByField['boom_pillow']['quantity'];
    $socks = $checklistByField['socks']['quantity'];
    $disposalBag = $checklistByField['disposal_bag']['quantity'];
    $goggles = $checklistByField['goggles']['quantity'];
    $faceMask = $checklistByField['face_mask']['quantity'];
    $rubberGloves = $checklistByField['rubber_gloves']['quantity'];
    $nitrileGloves = $checklistByField['nitrile_gloves']['quantity'];
    mysqli_stmt_bind_param(
        $quantityStmt,
        'ssssssssiss',
        $pads,
        $boomPillow,
        $socks,
        $disposalBag,
        $goggles,
        $faceMask,
        $rubberGloves,
        $nitrileGloves,
        $kitId,
        $branch,
        $kitCode
    );
    if (!mysqli_stmt_execute($quantityStmt)) {
        $message = mysqli_stmt_error($quantityStmt);
        mysqli_stmt_close($quantityStmt);
        throw new RuntimeException('Could not update Chemical Spill Kit quantities: ' . $message);
    }
    mysqli_stmt_close($quantityStmt);

    $updateMaterialStmt = mysqli_prepare(
        $conn,
        'UPDATE chemical_spill_kit_current_inventory_materials_tbl
         SET status = ?, remarks = ?
         WHERE current_material_id = ? AND chemical_spill_kit_id = ? AND material_name = ?'
    );
    $insertMaterialStmt = mysqli_prepare(
        $conn,
        'INSERT INTO chemical_spill_kit_current_inventory_materials_tbl
            (chemical_spill_kit_id, material_name, status, remarks)
         VALUES (?, ?, ?, ?)'
    );
    if (!$updateMaterialStmt || !$insertMaterialStmt) {
        if ($updateMaterialStmt) {
            mysqli_stmt_close($updateMaterialStmt);
        }
        if ($insertMaterialStmt) {
            mysqli_stmt_close($insertMaterialStmt);
        }
        throw new RuntimeException('Could not prepare current material status/remarks updates.');
    }

    foreach ($materials as $field => $material) {
        $status = $checklistByField[$field]['status'];
        $remarks = $checklistByField[$field]['remarks'];
        if (isset($inventoryRowsByField[$field])) {
            $currentMaterialId = $inventoryRowsByField[$field]['id'];
            $currentMaterialName = $inventoryRowsByField[$field]['name'];
            mysqli_stmt_bind_param(
                $updateMaterialStmt,
                'ssiis',
                $status,
                $remarks,
                $currentMaterialId,
                $kitId,
                $currentMaterialName
            );
            if (!mysqli_stmt_execute($updateMaterialStmt)) {
                $message = mysqli_stmt_error($updateMaterialStmt);
                mysqli_stmt_close($updateMaterialStmt);
                mysqli_stmt_close($insertMaterialStmt);
                throw new RuntimeException('Could not update a current material record: ' . $message);
            }
        } else {
            $currentMaterialName = $material['inventory'];
            mysqli_stmt_bind_param(
                $insertMaterialStmt,
                'isss',
                $kitId,
                $currentMaterialName,
                $status,
                $remarks
            );
            if (!mysqli_stmt_execute($insertMaterialStmt) || mysqli_stmt_affected_rows($insertMaterialStmt) !== 1) {
                $message = mysqli_stmt_error($insertMaterialStmt);
                mysqli_stmt_close($updateMaterialStmt);
                mysqli_stmt_close($insertMaterialStmt);
                throw new RuntimeException('Could not create a missing current material record: ' . $message);
            }
        }
    }
    mysqli_stmt_close($updateMaterialStmt);
    mysqli_stmt_close($insertMaterialStmt);
}

// Evaluate only the pending Chemical Spill Kit transaction associated with this inspection.
function updateChemicalSpillKitEvaluation($inspectionId, $evaluationStatus, $approverName, $rejectionReason = '')
{
    global $conn;

    $inspectionId = (int) $inspectionId;
    $evaluationStatus = trim((string) $evaluationStatus);
    $approverName = trim((string) $approverName);
    $rejectionReason = trim((string) $rejectionReason);
    $role = strtolower(trim((string) ($_SESSION['Role'] ?? '')));
    $assignedBranch = trim((string) ($_SESSION['Branch'] ?? ''));

    if (
        $inspectionId <= 0 ||
        $approverName === '' ||
        !in_array($evaluationStatus, array('Approved', 'Rejected'), true) ||
        !in_array($role, array('admin', 'inspector'), true) ||
        ($evaluationStatus === 'Rejected' && $rejectionReason === '') ||
        strlen($rejectionReason) > 1000 ||
        ($role === 'inspector' && $assignedBranch === '')
    ) {
        return false;
    }

    $lookupStmt = null;
    $updateStmt = null;
    $transactionStarted = false;
    try {
        if (!mysqli_begin_transaction($conn)) {
            return false;
        }
        $transactionStarted = true;

        $lookupSql = "SELECT t.transaction_id, t.status, t.branch AS transaction_branch,
                             i.branch, i.chemical_spill_kit_id, i.chemical_spill_kit_code
                      FROM transactions_tbl AS t
                      INNER JOIN chemical_spill_kit_inspection_tbl AS i
                          ON i.inspection_id = t.inspection_id
                      WHERE i.inspection_id = ?
                        AND t.category = 'Chemical Spill Kit'
                        AND t.transaction_type = 'inspection'
                        AND (? = 'admin' OR LOWER(TRIM(i.branch)) = LOWER(TRIM(?)))
                      ORDER BY t.transaction_id DESC
                      LIMIT 1
                      FOR UPDATE";
        $lookupStmt = mysqli_prepare($conn, $lookupSql);
        if (!$lookupStmt) {
            throw new RuntimeException('Could not prepare Chemical Spill Kit evaluation lookup.');
        }
        mysqli_stmt_bind_param($lookupStmt, 'iss', $inspectionId, $role, $assignedBranch);
        if (!mysqli_stmt_execute($lookupStmt)) {
            throw new RuntimeException('Could not load Chemical Spill Kit evaluation record.');
        }
        $lookupResult = mysqli_stmt_get_result($lookupStmt);
        $transaction = $lookupResult ? mysqli_fetch_assoc($lookupResult) : null;
        if ($lookupResult) {
            mysqli_free_result($lookupResult);
        }
        mysqli_stmt_close($lookupStmt);
        $lookupStmt = null;

        if (!$transaction || strtolower(trim((string) $transaction['status'])) !== 'pending') {
            throw new RuntimeException('Chemical Spill Kit inspection is not pending or is unavailable to this user.');
        }
        if (
            $evaluationStatus === 'Approved' &&
            strcasecmp(trim((string) $transaction['transaction_branch']), trim((string) $transaction['branch'])) !== 0
        ) {
            throw new RuntimeException('The transaction branch does not match the inspection branch.');
        }

        $transactionId = (int) $transaction['transaction_id'];
        if ($evaluationStatus === 'Approved') {
            $updateSql = "UPDATE transactions_tbl
                          SET status = 'approved', reviewed_by = ?,
                              rejection_reason = NULL, reviewed_at = NOW()
                          WHERE transaction_id = ?
                            AND category = 'Chemical Spill Kit'
                            AND transaction_type = 'inspection'
                            AND status = 'pending'";
            $updateStmt = mysqli_prepare($conn, $updateSql);
            if (!$updateStmt) {
                throw new RuntimeException('Could not prepare Chemical Spill Kit approval update.');
            }
            mysqli_stmt_bind_param($updateStmt, 'si', $approverName, $transactionId);
        } else {
            $updateSql = "UPDATE transactions_tbl
                          SET status = 'rejected', reviewed_by = ?,
                              rejection_reason = ?, reviewed_at = NOW()
                          WHERE transaction_id = ?
                            AND category = 'Chemical Spill Kit'
                            AND transaction_type = 'inspection'
                            AND status = 'pending'";
            $updateStmt = mysqli_prepare($conn, $updateSql);
            if (!$updateStmt) {
                throw new RuntimeException('Could not prepare Chemical Spill Kit rejection update.');
            }
            mysqli_stmt_bind_param($updateStmt, 'ssi', $approverName, $rejectionReason, $transactionId);
        }

        if (!mysqli_stmt_execute($updateStmt) || mysqli_stmt_affected_rows($updateStmt) !== 1) {
            throw new RuntimeException('Could not update the pending Chemical Spill Kit transaction.');
        }
        mysqli_stmt_close($updateStmt);
        $updateStmt = null;

        if ($evaluationStatus === 'Approved') {
            updateChemicalSpillKitInventoryFromInspection(
                $inspectionId,
                $transaction['chemical_spill_kit_id'],
                $transaction['branch'],
                $transaction['chemical_spill_kit_code']
            );
        }

        if (!mysqli_commit($conn)) {
            throw new RuntimeException('Could not commit Chemical Spill Kit evaluation.');
        }
        return true;
    } catch (Throwable $error) {
        if ($transactionStarted) {
            mysqli_rollback($conn);
        }
        error_log('Chemical Spill Kit evaluation failed: ' . $error->getMessage());
        return false;
    } finally {
        if ($lookupStmt instanceof mysqli_stmt) {
            mysqli_stmt_close($lookupStmt);
        }
        if ($updateStmt instanceof mysqli_stmt) {
            mysqli_stmt_close($updateStmt);
        }
    }
}

function insertChemicalInspection(
    $code,
    $branch,
    $location,
    $inspected_by,
    $date_inspected,
    $pads,
    $pads_status,
    $pads_remarks,
    $boom_pillow,
    $boom_pillow_status,
    $boom_pillow_remarks,
    $socks,
    $socks_status,
    $socks_remarks,
    $disposal_bag,
    $disposal_bag_status,
    $disposal_bag_remarks,
    $goggles,
    $goggles_status,
    $goggles_remarks,
    $face_mask,
    $face_mask_status,
    $face_mask_remarks,
    $rubber_gloves,
    $rubber_gloves_status,
    $rubber_gloves_remarks,
    $nitrile_gloves,
    $nitrile_gloves_status,
    $nitrile_gloves_remarks,
    $chemical_spill_kit_id,
    $submitted_by
) {
    global $conn;

    $materials = [
        [
            'name' => 'Pads',
            'stock' => $pads,
            'status' => $pads_status,
            'remarks' => $pads_remarks
        ],
        [
            'name' => 'Boom/Pillow',
            'stock' => $boom_pillow,
            'status' => $boom_pillow_status,
            'remarks' => $boom_pillow_remarks
        ],
        [
            'name' => 'Socks',
            'stock' => $socks,
            'status' => $socks_status,
            'remarks' => $socks_remarks
        ],
        [
            'name' => 'Disposal Bag',
            'stock' => $disposal_bag,
            'status' => $disposal_bag_status,
            'remarks' => $disposal_bag_remarks
        ],
        [
            'name' => 'Goggles',
            'stock' => $goggles,
            'status' => $goggles_status,
            'remarks' => $goggles_remarks
        ],
        [
            'name' => 'Face Mask',
            'stock' => $face_mask,
            'status' => $face_mask_status,
            'remarks' => $face_mask_remarks
        ],
        [
            'name' => 'Rubber Gloves',
            'stock' => $rubber_gloves,
            'status' => $rubber_gloves_status,
            'remarks' => $rubber_gloves_remarks
        ],
        [
            'name' => 'Nitrile Gloves',
            'stock' => $nitrile_gloves,
            'status' => $nitrile_gloves_status,
            'remarks' => $nitrile_gloves_remarks
        ]
    ];

    $conn->begin_transaction();

    try {
        // Insert main inspection record.
        $stmt = $conn->prepare(
            "INSERT INTO chemical_spill_kit_inspection_tbl
                (chemical_spill_kit_id, chemical_spill_kit_code,
                 location, branch, inspected_by, inspection_date)
             VALUES (?, ?, ?, ?, ?, NOW())"
        );

        $stmt->bind_param(
            "issss",
            $chemical_spill_kit_id,
            $code,
            $location,
            $branch,
            $inspected_by
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $inspectionId = $conn->insert_id;
        $stmt->close();

        // Insert one checklist record per material.
        $stmt = $conn->prepare(
            "INSERT INTO chemical_spill_kit_materials_checklist_tbl
                (inspection_id, material_name, current_stocks, status, remarks)
             VALUES (?, ?, ?, ?, ?)"
        );

        foreach ($materials as $material) {
            $stock = (string) $material['stock'];

            $stmt->bind_param(
                "issss",
                $inspectionId,
                $material['name'],
                $stock,
                $material['status'],
                $material['remarks']
            );

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
        }

        $stmt->close();

        // Create pending transaction record.
        $category = 'Chemical Spill Kit';

        $stmt = $conn->prepare(
            "INSERT INTO transactions_tbl
                (category, branch, inspection_id, transaction_type,
                 status, submitted_by)
             VALUES (?, ?, ?, 'inspection', 'pending', ?)"
        );

        $stmt->bind_param(
            "ssis",
            $category,
            $branch,
            $inspectionId,
            $submitted_by
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $stmt->close();

        $conn->commit();

        return $inspectionId;
    } catch (Throwable $e) {
        $conn->rollback();
        error_log("Chemical Spill Kit inspection insert failed: " . $e->getMessage());
        return false;
    }
}



// for getting by id
function getChemicalSpillKitIdByCode($code, $branch)
{
    global $conn;

    $sql = "
        SELECT chemical_spill_kit_id
        FROM chemical_spill_kit_materials_inventory_tbl
        WHERE chemical_spill_kit_code = ?
          AND branch = ?
          AND archived = 0
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("CSK lookup prepare failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param("ss", $code, $branch);

    if (!$stmt->execute()) {
        error_log("CSK lookup failed: " . $stmt->error);
        $stmt->close();
        return false;
    }

    $stmt->bind_result($id);

    $found = $stmt->fetch();
    $stmt->close();

    return $found ? (int) $id : false;
}
