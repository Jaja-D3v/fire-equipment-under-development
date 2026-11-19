<?php
require_once __DIR__ . '/../config/db.php';

// Get configured required stock for one specific Chemical Spill Kit branch.
function getRequiredStockModel($branch)
{
    global $conn;

    $branch = trim((string)$branch);
    if ($branch === '' || strtolower($branch) === 'all') {
        return false;
    }

    $sql = "SELECT material_name, required_stock
            FROM chemical_spill_kit_materials_tbl
            WHERE branch = ? AND is_active = 1
            ORDER BY material_id ASC";

    $stmt = null;
    $result = null;
    $requiredStocks = [];

    try {
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            error_log("Required stock query prepare error: " . mysqli_error($conn));
            return false;
        }

        if (!mysqli_stmt_bind_param($stmt, "s", $branch)) {
            error_log("Required stock bind error: " . mysqli_stmt_error($stmt));
            return false;
        }

        if (!mysqli_stmt_execute($stmt)) {
            error_log("Required stock execute error: " . mysqli_stmt_error($stmt));
            return false;
        }

        $result = mysqli_stmt_get_result($stmt);
        if (!$result) {
            error_log("Required stock result error: " . mysqli_stmt_error($stmt));
            return false;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $name = (string) $row['material_name'];
            if (array_key_exists($name, $requiredStocks)) {
                error_log('Duplicate Chemical Spill Kit required-stock material name.');
                return false;
            }
            $requiredStocks[$name] = $row['required_stock'];
        }

        return $requiredStocks;
    } catch (mysqli_sql_exception $exception) {
        error_log("Required stock database error: " . $exception->getMessage());
        return false;
    } finally {
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        if ($stmt instanceof mysqli_stmt) {
            mysqli_stmt_close($stmt);
        }
    }
}

// Return editable Chemical Spill Kit required-stock rows for one branch.
function getRequiredStockManagementRowsModel($branch, $includeRetired = false)
{
    global $conn;

    $branch = trim((string) $branch);
    if ($branch === '' || strtolower($branch) === 'all') {
        return false;
    }

    $sql = "SELECT material_id, material_name, required_stock, is_active, retired_at, updated_at
            FROM chemical_spill_kit_materials_tbl
            WHERE branch = ?" . ($includeRetired ? '' : ' AND is_active = 1') . "
            ORDER BY material_name ASC, material_id ASC";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log('Required stock management prepare failed: ' . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, 's', $branch);
    if (!mysqli_stmt_execute($stmt)) {
        error_log('Required stock management query failed: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        error_log('Required stock management result failed: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
    mysqli_stmt_close($stmt);
    return $rows;
}

function cskRequiredStockMaterialKey($name)
{
    $key = mb_strtolower(trim((string) $name), 'UTF-8');
    return $key === 'boom/pillow' ? 'boom pillow' : $key;
}

function cskRequiredStockValidName($name)
{
    $name = trim((string) $name);
    return $name !== '' && mb_strlen($name, 'UTF-8') <= 100;
}

function cskRequiredStockDuplicateExists($branch, $name, $excludeId = 0)
{
    global $conn;
    $stmt = mysqli_prepare($conn, 'SELECT material_id, material_name FROM chemical_spill_kit_materials_tbl WHERE branch = ? FOR UPDATE');
    if (!$stmt) throw new RuntimeException(mysqli_error($conn));
    try {
        mysqli_stmt_bind_param($stmt, 's', $branch);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $key = cskRequiredStockMaterialKey($name);
        while ($row = mysqli_fetch_assoc($result)) {
            if ((int) $row['material_id'] !== (int) $excludeId && cskRequiredStockMaterialKey($row['material_name']) === $key) {
                mysqli_free_result($result);
                return true;
            }
        }
        mysqli_free_result($result);
        return false;
    } finally { mysqli_stmt_close($stmt); }
}

function createRequiredStockMaterialsModel($name, $quantity, array $branches)
{
    global $conn;
    $name = trim((string) $name); $quantity = (int) $quantity;
    $branches = array_values(array_unique(array_filter(array_map('trim', $branches))));
    if (!cskRequiredStockValidName($name) || $quantity < 0 || !$branches) return false;
    $insert = null; $started = false;
    try {
        if (!mysqli_begin_transaction($conn)) throw new RuntimeException(mysqli_error($conn));
        $started = true;
        $insert = mysqli_prepare($conn, 'INSERT INTO chemical_spill_kit_materials_tbl (material_name, required_stock, branch, is_active, retired_at) VALUES (?, ?, ?, 1, NULL)');
        if (!$insert) throw new RuntimeException(mysqli_error($conn));
        foreach ($branches as $branch) {
            if (strtolower($branch) === 'all' || cskRequiredStockDuplicateExists($branch, $name)) throw new RuntimeException('Duplicate material.');
            mysqli_stmt_bind_param($insert, 'sis', $name, $quantity, $branch);
            if (!mysqli_stmt_execute($insert)) throw new RuntimeException(mysqli_stmt_error($insert));
        }
        if (!mysqli_commit($conn)) throw new RuntimeException(mysqli_error($conn));
        return true;
    } catch (Throwable $error) {
        if ($started) mysqli_rollback($conn);
        error_log('Required stock material create failed: ' . $error->getMessage());
        return false;
    } finally { if ($insert) mysqli_stmt_close($insert); }
}

function updateRequiredStockMaterialModel($id, $branch, $name, $quantity)
{
    global $conn;
    $id = (int) $id; $branch = trim((string) $branch); $name = trim((string) $name); $quantity = (int) $quantity;
    if ($id <= 0 || $branch === '' || strtolower($branch) === 'all' || !cskRequiredStockValidName($name) || $quantity < 0) return false;
    $lock = $update = null; $started = false;
    try {
        if (!mysqli_begin_transaction($conn)) throw new RuntimeException(mysqli_error($conn));
        $started = true;
        $lock = mysqli_prepare($conn, 'SELECT material_name FROM chemical_spill_kit_materials_tbl WHERE material_id = ? AND branch = ? AND is_active = 1 FOR UPDATE');
        mysqli_stmt_bind_param($lock, 'is', $id, $branch); mysqli_stmt_execute($lock);
        $result = mysqli_stmt_get_result($lock); $row = $result ? mysqli_fetch_assoc($result) : null; if ($result) mysqli_free_result($result);
        if (!$row || cskRequiredStockDuplicateExists($branch, $name, $id)) throw new RuntimeException('Missing or duplicate material.');
        if (cskRequiredStockMaterialKey($row['material_name']) !== cskRequiredStockMaterialKey($name)) {
            $dependency = mysqli_prepare($conn,
                'SELECT 1 FROM chemical_spill_kit_current_inventory_materials_tbl cm
                 INNER JOIN chemical_spill_kit_materials_inventory_tbl k ON k.chemical_spill_kit_id = cm.chemical_spill_kit_id
                 WHERE k.branch = ? AND cm.material_name = ? LIMIT 1 FOR UPDATE');
            mysqli_stmt_bind_param($dependency, 'ss', $branch, $row['material_name']);
            mysqli_stmt_execute($dependency);
            $dependencyResult = mysqli_stmt_get_result($dependency);
            $hasInventoryDependency = $dependencyResult && mysqli_fetch_assoc($dependencyResult);
            if ($dependencyResult) mysqli_free_result($dependencyResult);
            mysqli_stmt_close($dependency);
            if ($hasInventoryDependency) throw new RuntimeException('Material name cannot change while branch kit inventory uses it.');
            $pending = mysqli_prepare($conn,
                "SELECT 1 FROM chemical_spill_kit_materials_checklist_tbl c
                 INNER JOIN chemical_spill_kit_inspection_tbl i ON i.inspection_id = c.inspection_id
                 INNER JOIN transactions_tbl t ON t.inspection_id = i.inspection_id
                   AND t.category = 'Chemical Spill Kit' AND t.transaction_type = 'inspection' AND t.status = 'pending'
                 WHERE i.branch = ? AND c.material_name = ? LIMIT 1");
            mysqli_stmt_bind_param($pending, 'ss', $branch, $row['material_name']);
            mysqli_stmt_execute($pending);
            $pendingResult = mysqli_stmt_get_result($pending);
            $hasPendingDependency = $pendingResult && mysqli_fetch_assoc($pendingResult);
            if ($pendingResult) mysqli_free_result($pendingResult);
            mysqli_stmt_close($pending);
            if ($hasPendingDependency) throw new RuntimeException('Material name cannot change while a pending inspection uses it.');
        }
        $update = mysqli_prepare($conn, 'UPDATE chemical_spill_kit_materials_tbl SET material_name = ?, required_stock = ? WHERE material_id = ? AND branch = ? AND is_active = 1');
        mysqli_stmt_bind_param($update, 'siis', $name, $quantity, $id, $branch);
        if (!mysqli_stmt_execute($update)) throw new RuntimeException(mysqli_stmt_error($update));
        if (!mysqli_commit($conn)) throw new RuntimeException(mysqli_error($conn));
        return true;
    } catch (Throwable $error) {
        if ($started) mysqli_rollback($conn);
        error_log('Required stock material update failed: ' . $error->getMessage()); return false;
    } finally { if ($lock) mysqli_stmt_close($lock); if ($update) mysqli_stmt_close($update); }
}

// Delete is physical only when the configuration has no current-kit or pending-inspection dependency.
// Otherwise it is safely hidden from active configuration by setting is_active to 0.
function deleteRequiredStockMaterialModel($id, $branch)
{
    global $conn;
    $id = (int) $id;
    $branch = trim((string) $branch);
    if ($id <= 0 || $branch === '' || strtolower($branch) === 'all') return false;
    $lock = $dependency = $delete = $deactivate = null;
    $started = false;
    try {
        if (!mysqli_begin_transaction($conn)) throw new RuntimeException(mysqli_error($conn));
        $started = true;
        $lock = mysqli_prepare($conn, 'SELECT material_name FROM chemical_spill_kit_materials_tbl WHERE material_id = ? AND branch = ? AND is_active = 1 FOR UPDATE');
        mysqli_stmt_bind_param($lock, 'is', $id, $branch);
        mysqli_stmt_execute($lock);
        $result = mysqli_stmt_get_result($lock);
        $material = $result ? mysqli_fetch_assoc($result) : null;
        if ($result) mysqli_free_result($result);
        if (!$material) throw new RuntimeException('Material was not found in the selected branch.');
        $dependency = mysqli_prepare($conn,
            "SELECT 1 FROM chemical_spill_kit_current_inventory_materials_tbl cm
             INNER JOIN chemical_spill_kit_materials_inventory_tbl k ON k.chemical_spill_kit_id = cm.chemical_spill_kit_id
             WHERE k.branch = ? AND cm.material_name = ?
             UNION ALL
             SELECT 1 FROM chemical_spill_kit_materials_checklist_tbl c
             INNER JOIN chemical_spill_kit_inspection_tbl i ON i.inspection_id = c.inspection_id
             INNER JOIN transactions_tbl t ON t.inspection_id = i.inspection_id
               AND t.category = 'Chemical Spill Kit' AND t.transaction_type = 'inspection' AND t.status = 'pending'
             WHERE i.branch = ? AND c.material_name = ? LIMIT 1");
        mysqli_stmt_bind_param($dependency, 'ssss', $branch, $material['material_name'], $branch, $material['material_name']);
        mysqli_stmt_execute($dependency);
        $dependencyResult = mysqli_stmt_get_result($dependency);
        $hasDependency = $dependencyResult && mysqli_fetch_assoc($dependencyResult);
        if ($dependencyResult) mysqli_free_result($dependencyResult);
        if ($hasDependency) {
            $deactivate = mysqli_prepare($conn, 'UPDATE chemical_spill_kit_materials_tbl SET is_active = 0, retired_at = NOW() WHERE material_id = ? AND branch = ?');
            mysqli_stmt_bind_param($deactivate, 'is', $id, $branch);
            if (!mysqli_stmt_execute($deactivate)) throw new RuntimeException(mysqli_stmt_error($deactivate));
            $outcome = array('deleted' => false, 'message' => 'Material has kit inventory or a pending inspection, so it was made inactive instead of being deleted.');
        } else {
            $delete = mysqli_prepare($conn, 'DELETE FROM chemical_spill_kit_materials_tbl WHERE material_id = ? AND branch = ? AND is_active = 1');
            mysqli_stmt_bind_param($delete, 'is', $id, $branch);
            if (!mysqli_stmt_execute($delete) || mysqli_stmt_affected_rows($delete) !== 1) throw new RuntimeException('Delete failed.');
            $outcome = array('deleted' => true, 'message' => 'Material configuration deleted for the selected branch.');
        }
        if (!mysqli_commit($conn)) throw new RuntimeException(mysqli_error($conn));
        return $outcome;
    } catch (Throwable $error) {
        if ($started) mysqli_rollback($conn);
        error_log('Required stock material delete failed: ' . $error->getMessage());
        return false;
    } finally {
        foreach (array($lock, $dependency, $delete, $deactivate) as $stmt) if ($stmt instanceof mysqli_stmt) mysqli_stmt_close($stmt);
    }
}

function setRequiredStockMaterialActiveModel($id, $branch, $active)
{
    global $conn;
    $id = (int) $id; $branch = trim((string) $branch); $active = $active ? 1 : 0;
    if ($id <= 0 || $branch === '' || strtolower($branch) === 'all') return false;
    $stmt = mysqli_prepare($conn, 'UPDATE chemical_spill_kit_materials_tbl SET is_active = ?, retired_at = CASE WHEN ? = 1 THEN NULL ELSE NOW() END WHERE material_id = ? AND branch = ?');
    if (!$stmt) return false;
    try { mysqli_stmt_bind_param($stmt, 'iiis', $active, $active, $id, $branch); return mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) === 1; }
    finally { mysqli_stmt_close($stmt); }
}

// Update only one required-stock quantity, scoped by both material ID and branch.
function updateRequiredStockManagementRowModel($materialId, $branch, $requiredStock)
{
    global $conn;

    $materialId = (int) $materialId;
    $branch = trim((string) $branch);
    $requiredStock = (int) $requiredStock;
    if ($materialId <= 0 || $branch === '' || strtolower($branch) === 'all' || $requiredStock < 0) {
        return false;
    }

    $lockStmt = null;
    $updateStmt = null;
    $transactionStarted = false;
    try {
        if (!mysqli_begin_transaction($conn)) {
            throw new RuntimeException(mysqli_error($conn));
        }
        $transactionStarted = true;

        $lockStmt = mysqli_prepare(
            $conn,
            'SELECT material_id
             FROM chemical_spill_kit_materials_tbl
             WHERE material_id = ? AND branch = ? AND is_active = 1
             FOR UPDATE'
        );
        if (!$lockStmt) {
            throw new RuntimeException(mysqli_error($conn));
        }
        mysqli_stmt_bind_param($lockStmt, 'is', $materialId, $branch);
        if (!mysqli_stmt_execute($lockStmt)) {
            throw new RuntimeException(mysqli_stmt_error($lockStmt));
        }
        $lockResult = mysqli_stmt_get_result($lockStmt);
        $material = $lockResult ? mysqli_fetch_assoc($lockResult) : null;
        if ($lockResult) {
            mysqli_free_result($lockResult);
        }
        mysqli_stmt_close($lockStmt);
        $lockStmt = null;
        if (!$material) {
            throw new RuntimeException('Required stock material was not found in the selected branch.');
        }

        $updateStmt = mysqli_prepare(
            $conn,
            'UPDATE chemical_spill_kit_materials_tbl
             SET required_stock = ?
             WHERE material_id = ? AND branch = ? AND is_active = 1'
        );
        if (!$updateStmt) {
            throw new RuntimeException(mysqli_error($conn));
        }
        mysqli_stmt_bind_param($updateStmt, 'iis', $requiredStock, $materialId, $branch);
        if (!mysqli_stmt_execute($updateStmt)) {
            throw new RuntimeException(mysqli_stmt_error($updateStmt));
        }

        if (!mysqli_commit($conn)) {
            throw new RuntimeException(mysqli_error($conn));
        }
        return true;
    } catch (Throwable $error) {
        if ($transactionStarted) {
            mysqli_rollback($conn);
        }
        error_log('Required stock management update failed: ' . $error->getMessage());
        return false;
    } finally {
        if ($lockStmt instanceof mysqli_stmt) {
            mysqli_stmt_close($lockStmt);
        }
        if ($updateStmt instanceof mysqli_stmt) {
            mysqli_stmt_close($updateStmt);
        }
    }
}
