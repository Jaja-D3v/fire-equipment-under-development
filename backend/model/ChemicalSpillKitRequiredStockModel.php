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
            $requiredStocks[(string)$row['material_name']] = (int)$row['required_stock'];
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