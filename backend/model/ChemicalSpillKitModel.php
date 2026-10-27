
<?php
require_once __DIR__ . '/../config/db.php';



//Kunin ang lahat ng Chemical Spill Kit sa isang branch.
function getAllChemicalSpillKit($branch, $limit = 10)
{
    global $conn;
    $sql = "SELECT * FROM chemical_spill_kit_materials_inventory_tbl WHERE branch = ? AND archived = 0 ORDER BY chemical_spill_kit_code ASC LIMIT ? ";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("CSK inventory query error: " . mysqli_error($conn));
        return [];
    }
    mysqli_stmt_bind_param($stmt, "sii", $branch, $limit);
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


// this is used by qr code get by ID and based on branch only
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



// THIS IS FOR UPDATING INVENTORY MATERIALS 
function updateSpillKitMaterials(
    $id,
    $location,

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
    $nitrile_gloves_remarks
) {
    global $conn;

    // Start transaction
    if (!mysqli_begin_transaction($conn)) {
        error_log("CSK inventory transaction error: " . mysqli_error($conn));
        return false;
    }

    // Update main Chemical Spill Kit inventory
    $sql = "UPDATE chemical_spill_kit_materials_inventory_tbl
            SET location = ?,
                pads = ?,
                boom_pillow = ?,
                socks = ?,
                disposal_bag = ?,
                goggles = ?,
                face_mask = ?,
                rubber_gloves = ?,
                nitrile_gloves = ?
            WHERE chemical_spill_kit_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log("CSK inventory prepare error: " . mysqli_error($conn));
        mysqli_rollback($conn);
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sssssssssi",
        $location,
        $pads,
        $boom_pillow,
        $socks,
        $disposal_bag,
        $goggles,
        $face_mask,
        $rubber_gloves,
        $nitrile_gloves,
        $id
    );

    if (!mysqli_stmt_execute($stmt)) {
        error_log("CSK inventory update error: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        mysqli_rollback($conn);
        return false;
    }

    // Verify that the inventory record exists
    if (mysqli_stmt_affected_rows($stmt) === 0) {
        // Zero can also mean values were unchanged, so check existence
        $check = mysqli_prepare(
            $conn,
            "SELECT chemical_spill_kit_id
             FROM chemical_spill_kit_materials_inventory_tbl
             WHERE chemical_spill_kit_id = ?"
        );

        if (!$check) {
            mysqli_stmt_close($stmt);
            mysqli_rollback($conn);
            return false;
        }

        mysqli_stmt_bind_param($check, "i", $id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        $exists = mysqli_stmt_num_rows($check) > 0;

        mysqli_stmt_close($check);

        if (!$exists) {
            mysqli_stmt_close($stmt);
            mysqli_rollback($conn);
            return false;
        }
    }

    mysqli_stmt_close($stmt);

    // Material values to update in the current inventory materials table
    $materials = [
        [
            'name' => 'Pads',
            'stocks' => $pads,
            'status' => $pads_status,
            'remarks' => $pads_remarks
        ],
        [
            'name' => 'Boom Pillow',
            'stocks' => $boom_pillow,
            'status' => $boom_pillow_status,
            'remarks' => $boom_pillow_remarks
        ],
        [
            'name' => 'Socks',
            'stocks' => $socks,
            'status' => $socks_status,
            'remarks' => $socks_remarks
        ],
        [
            'name' => 'Disposal Bag',
            'stocks' => $disposal_bag,
            'status' => $disposal_bag_status,
            'remarks' => $disposal_bag_remarks
        ],
        [
            'name' => 'Goggles',
            'stocks' => $goggles,
            'status' => $goggles_status,
            'remarks' => $goggles_remarks
        ],
        [
            'name' => 'Face Mask',
            'stocks' => $face_mask,
            'status' => $face_mask_status,
            'remarks' => $face_mask_remarks
        ],
        [
            'name' => 'Rubber Gloves',
            'stocks' => $rubber_gloves,
            'status' => $rubber_gloves_status,
            'remarks' => $rubber_gloves_remarks
        ],
        [
            'name' => 'Nitrile Gloves',
            'stocks' => $nitrile_gloves,
            'status' => $nitrile_gloves_status,
            'remarks' => $nitrile_gloves_remarks
        ]
    ];

    // Insert a material if it does not exist; otherwise update its current values
    $sqlMaterial = "INSERT INTO chemical_spill_kit_current_inventory_materials_tbl
                    (chemical_spill_kit_id, material_name, current_stocks, status, remarks)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        current_stocks = VALUES(current_stocks),
                        status = VALUES(status),
                        remarks = VALUES(remarks)";

    $stmtMaterial = mysqli_prepare($conn, $sqlMaterial);

    if (!$stmtMaterial) {
        error_log("CSK current materials prepare error: " . mysqli_error($conn));
        mysqli_rollback($conn);
        return false;
    }

    foreach ($materials as $material) {
        mysqli_stmt_bind_param(
            $stmtMaterial,
            "issss",
            $id,
            $material['name'],
            $material['stocks'],
            $material['status'],
            $material['remarks']
        );

        if (!mysqli_stmt_execute($stmtMaterial)) {
            error_log("CSK current materials update error: " . mysqli_stmt_error($stmtMaterial));
            mysqli_stmt_close($stmtMaterial);
            mysqli_rollback($conn);
            return false;
        }
    }

    mysqli_stmt_close($stmtMaterial);

    // Commit both table updates
    if (!mysqli_commit($conn)) {
        error_log("CSK inventory commit error: " . mysqli_error($conn));
        mysqli_rollback($conn);
        return false;
    }

    return true;
}


