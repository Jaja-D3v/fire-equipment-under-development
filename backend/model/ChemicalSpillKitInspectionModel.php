
<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/ChemicalSpillKitMaterialModel.php';

// Fetch a Chemical Spill Kit inspection and its material checklist for evaluation.
function getChemicalSpillKitEvaluationDetails($inspectionId, $role, $branch)
{
    global $conn;

    $inspectionId = (int) $inspectionId;
    $role = strtolower(trim((string) $role));
    $branch = trim((string) $branch);
    if ($inspectionId <= 0 || !in_array($role, array('admin', 'inspector', 'super admin'), true)) {
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
              AND (? IN ('admin', 'super admin') OR LOWER(TRIM(i.branch)) = LOWER(TRIM(?)))
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
                $materialKey = chemicalSpillKitMaterialKey($row['material_name']);
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
            $requiredStockByName[chemicalSpillKitMaterialKey($materialName)] = $requiredStock;
        }
    }

    // Display the historical snapshot, including materials no longer configured.
    $materialNames = array_column($inspection['materials'], 'material_name');
    if (!$materialNames && is_array($requiredStockRecords)) $materialNames = array_keys($requiredStockRecords);
    $completeMaterials = array();
    foreach ($materialNames as $materialName) {
        $materialKey = chemicalSpillKitMaterialKey($materialName);
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

// Copy the approved checklist snapshot; never recalculate or rewrite inspection history.
// The approval caller owns the transaction. Newer configured materials are left untouched.
function updateChemicalSpillKitInventoryFromInspection($inspectionId, $kitId, $branch, $kitCode)
{
    global $conn;
    if ($inspectionId <= 0 || $kitId <= 0 || trim($branch) === '' || trim($kitCode) === '') {
        throw new RuntimeException('Chemical Spill Kit inventory identity is invalid.');
    }
    $stmt = mysqli_prepare($conn,
        'SELECT chemical_spill_kit_id FROM chemical_spill_kit_materials_inventory_tbl
         WHERE chemical_spill_kit_id = ? AND branch = ? AND chemical_spill_kit_code = ?
           AND archived = 0 FOR UPDATE');
    try {
        mysqli_stmt_bind_param($stmt, 'iss', $kitId, $branch, $kitCode);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $kit = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
    } finally {
        mysqli_stmt_close($stmt);
    }
    if (!$kit) throw new RuntimeException('The inspection kit is missing or archived.');

    $stmt = mysqli_prepare($conn,
        'SELECT material_name, current_stocks, status, remarks
         FROM chemical_spill_kit_materials_checklist_tbl
         WHERE inspection_id = ? ORDER BY checklist_id ASC FOR UPDATE');
    try {
        mysqli_stmt_bind_param($stmt, 'i', $inspectionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_free_result($result);
    } finally {
        mysqli_stmt_close($stmt);
    }
    $materials = chemicalSpillKitApprovedMaterials($rows);
    if ($materials === false) throw new RuntimeException('The approved checklist has invalid or duplicate material values.');
    $existing = getChemicalSpillKitCurrentMaterials($kitId, true);
    saveChemicalSpillKitCurrentMaterials($kitId, $materials, $existing);
}

// Validate historical snapshot quantities before placing them into current_stock INT.
function chemicalSpillKitApprovedMaterials(array $rows)
{
    if (!$rows) return false;
    $materials = [];
    foreach ($rows as $row) {
        $key = chemicalSpillKitMaterialKey($row['material_name']);
        if ($key === '' || isset($materials[$key])
            || !chemicalSpillKitQuantityIsValid($row['current_stocks'])
            || !in_array($row['status'], ['Good', 'No Good'], true)) return false;
        $materials[$key] = [
            'material_name' => chemicalSpillKitInventoryMaterialName($row['material_name']),
            'current_stock' => (int) trim((string) $row['current_stocks']),
            'status' => $row['status'],
            'remarks' => $row['remarks']
        ];
    }
    return $materials;
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
        !in_array($role, array('admin', 'inspector', 'super admin'), true) ||
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
                        AND (? IN ('admin', 'super admin') OR LOWER(TRIM(i.branch)) = LOWER(TRIM(?)))
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

// New checklist rows stay historical current_stocks; current inventory changes only on approval.
function insertChemicalInspection($code, $branch, $location, $inspected_by, $date_inspected, $submitted, $chemical_spill_kit_id, $submitted_by)
{
    global $conn;
    $stmt = null;
    $transactionStarted = false;
    try {
        mysqli_begin_transaction($conn);
        $transactionStarted = true;
        // Recheck the kit identity inside the transaction before saving the snapshot.
        $stmt = mysqli_prepare($conn,
            'SELECT chemical_spill_kit_id FROM chemical_spill_kit_materials_inventory_tbl
             WHERE chemical_spill_kit_id = ? AND branch = ? AND chemical_spill_kit_code = ?
               AND archived = 0 FOR UPDATE');
        mysqli_stmt_bind_param($stmt, 'iss', $chemical_spill_kit_id, $branch, $code);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $kit = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);
        $stmt = null;
        if (!$kit) throw new RuntimeException('The inspection kit is unavailable.');
        $configured = getChemicalSpillKitConfiguredMaterials($branch);
        if (!$configured) throw new RuntimeException('Required stock configuration is unavailable.');
        $materials = validateChemicalSpillKitMaterials($submitted, $configured);
        if ($materials === false) throw new RuntimeException('Inspection materials or quantities are invalid.');

        $stmt = mysqli_prepare($conn,
            'INSERT INTO chemical_spill_kit_inspection_tbl
             (chemical_spill_kit_id, chemical_spill_kit_code, location, branch, inspected_by, inspection_date)
             VALUES (?, ?, ?, ?, ?, NOW())');
        mysqli_stmt_bind_param($stmt, 'issss', $chemical_spill_kit_id, $code, $location, $branch, $inspected_by);
        mysqli_stmt_execute($stmt);
        $inspectionId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        $stmt = null;

        // Keep the historical column name and store one snapshot row per configured material.
        $stmt = mysqli_prepare($conn,
            'INSERT INTO chemical_spill_kit_materials_checklist_tbl
             (inspection_id, material_name, current_stocks, status, remarks) VALUES (?, ?, ?, ?, ?)');
        foreach ($materials as $key => $material) {
            $name = $configured[$key]['checklist_name'];
            $stock = (string) $material['current_stock'];
            $status = $material['status'];
            $remarks = $material['remarks'];
            mysqli_stmt_bind_param($stmt, 'issss', $inspectionId, $name, $stock, $status, $remarks);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);
        $stmt = null;
        $category = 'Chemical Spill Kit';
        $stmt = mysqli_prepare($conn,
            "INSERT INTO transactions_tbl
             (category, branch, inspection_id, transaction_type, status, submitted_by)
             VALUES (?, ?, ?, 'inspection', 'pending', ?)");
        mysqli_stmt_bind_param($stmt, 'ssis', $category, $branch, $inspectionId, $submitted_by);
        mysqli_stmt_execute($stmt);
        mysqli_commit($conn);
        return $inspectionId;
    } catch (Throwable $error) {
        if ($transactionStarted) mysqli_rollback($conn);
        error_log('Chemical Spill Kit inspection insert failed: ' . $error->getMessage());
        return false;
    } finally {
        if ($stmt) mysqli_stmt_close($stmt);
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
