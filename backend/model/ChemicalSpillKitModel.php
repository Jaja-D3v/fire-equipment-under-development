
<?php
require_once __DIR__ . '/../config/db.php';

/**
 * Kunin ang lahat ng Chemical Spill Kit sa isang branch.
 */
function getAllChemicalSpillKit($branch)
{
    global $conn;

    $sql = "
        SELECT *
        FROM chemical_spill_kit_materials_inventory_tbl
        WHERE branch = ?
          AND archived = 0
        ORDER BY chemical_spill_kit_code ASC
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log("CSK inventory query error: " . mysqli_error($conn));
        return [];
    }

    mysqli_stmt_bind_param($stmt, "s", $branch);

    if (!mysqli_stmt_execute($stmt)) {
        error_log("CSK inventory execute error: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return [];
    }

    $result = mysqli_stmt_get_result($stmt);
    $chemicalSpillKits = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $chemicalSpillKits[] = $row;
    }

    mysqli_free_result($result);
    mysqli_stmt_close($stmt);

    return $chemicalSpillKits;
}

/**
 * Hanapin ang isang Chemical Spill Kit gamit ang QR code at branch.
 */
function getChemicalSpillKitByCode($code, $branch)
{
    global $conn;

    $sql = "
        SELECT *
        FROM chemical_spill_kit_materials_inventory_tbl
        WHERE chemical_spill_kit_code = ?
          AND branch = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log("CSK lookup prepare error: " . mysqli_error($conn));
        return null;
    }

    mysqli_stmt_bind_param($stmt, "ss", $code, $branch);

    if (!mysqli_stmt_execute($stmt)) {
        error_log("CSK lookup execute error: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return null;
    }

    $result = mysqli_stmt_get_result($stmt);
    $chemicalSpillKit = mysqli_fetch_assoc($result);

    mysqli_free_result($result);
    mysqli_stmt_close($stmt);

    return $chemicalSpillKit ?: null;
}
