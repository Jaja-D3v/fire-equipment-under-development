
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

        $lookupSql = "SELECT t.transaction_id, t.status, i.branch
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
