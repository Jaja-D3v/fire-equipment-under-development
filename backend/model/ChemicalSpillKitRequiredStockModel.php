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
            WHERE branch = ?
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
            $requiredStocks[(string)$row['material_name']] = $row['required_stock'];
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
function getRequiredStockManagementRowsModel($branch)
{
    global $conn;

    $branch = trim((string) $branch);
    if ($branch === '' || strtolower($branch) === 'all') {
        return false;
    }

    $sql = "SELECT material_id, material_name, required_stock, updated_at
            FROM chemical_spill_kit_materials_tbl
            WHERE branch = ?
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
             WHERE material_id = ? AND branch = ?
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
             WHERE material_id = ? AND branch = ?'
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
