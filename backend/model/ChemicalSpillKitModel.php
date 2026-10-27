<?php
require_once __DIR__ . '/../config/db.php';

//Kunin ang lahat ng Chemical Spill Kit sa isang branch.
function getAllChemicalSpillKit($branch, $limit = 10, $offset = 0, $search = '')
{
    global $conn;

    $sql = "
        SELECT
            c.*,
            GROUP_CONCAT(
                CONCAT_WS(
                    '|||',
                    m.material_name,
                    COALESCE(m.status, ''),
                    COALESCE(m.remarks, '')
                )
                SEPARATOR '###'
            ) AS material_details
        FROM chemical_spill_kit_materials_inventory_tbl AS c
        LEFT JOIN chemical_spill_kit_current_inventory_materials_tbl AS m
            ON c.chemical_spill_kit_id = m.chemical_spill_kit_id
        WHERE (? = 'all' OR c.branch = ?)
            AND c.archived = 0
            AND (
                ? = ''
                OR c.chemical_spill_kit_code LIKE ?
                OR c.location LIKE ?
                OR c.branch LIKE ?
            )
        GROUP BY c.chemical_spill_kit_id
        ORDER BY c.chemical_spill_kit_code ASC
        LIMIT ? OFFSET ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log("CSK inventory query error: " . mysqli_error($conn));
        return [];
    }

    $searchLike = '%' . $search . '%';

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssii",
        $branch,
        $branch,
        $search,
        $searchLike,
        $searchLike,
        $searchLike,
        $limit,
        $offset
    );

    if (!mysqli_stmt_execute($stmt)) {
        error_log("CSK inventory execute error: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return [];
    }

    $result = mysqli_stmt_get_result($stmt);
    $chemicalSpillKits = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $row['current_materials'] = [];

        if (!empty($row['material_details'])) {
            $materials = explode('###', $row['material_details']);

            foreach ($materials as $material) {
                $details = explode('|||', $material, 3);

                if (count($details) === 3) {
                    $row['current_materials'][$details[0]] = [
                        'material_name' => $details[0],
                        'current_stocks' => null, // No stock/quantity column exists in the live companion table.
                        'status' => $details[1],
                        'remarks' => $details[2]
                    ];
                }
            }
        }

        unset($row['material_details']);
        $chemicalSpillKits[] = $row;
    }

    mysqli_free_result($result);
    mysqli_stmt_close($stmt);

    return $chemicalSpillKits;
}


// For inventory count
function getChemicalSpillKitInventoryCount($branch, $search = '')
{
    global $conn;

    $sql = "SELECT COUNT(*) AS total
            FROM chemical_spill_kit_materials_inventory_tbl
            WHERE (? = 'all' OR branch = ?)
              AND archived = 0
              AND (
                  ? = ''
                  OR chemical_spill_kit_code LIKE ?
                  OR location LIKE ?
                  OR branch LIKE ?
              )";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log("CSK inventory count prepare error: " . mysqli_error($conn));
        return 0;
    }

    $searchLike = '%' . $search . '%';

    mysqli_stmt_bind_param(
        $stmt,
        "ssssss",
        $branch,
        $branch,
        $search,
        $searchLike,
        $searchLike,
        $searchLike
    );

    if (!mysqli_stmt_execute($stmt)) {
        error_log("CSK inventory count execute error: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return 0;
    }

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    mysqli_free_result($result);
    mysqli_stmt_close($stmt);

    return (int)($row['total'] ?? 0);
}


// CSK codes are branch-scoped, matching the FE code workflow.
function chemicalSpillKitCodeExists($code, $branch, $lockForInsert = false)
{
    global $conn;

    $sql = "SELECT chemical_spill_kit_id
            FROM chemical_spill_kit_materials_inventory_tbl
            WHERE chemical_spill_kit_code = ?
              AND branch = ?
            LIMIT 1";
    if ($lockForInsert) {
        $sql .= " FOR UPDATE";
    }

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("CSK code check prepare error: " . mysqli_error($conn));
        return null;
    }

    mysqli_stmt_bind_param($stmt, "ss", $code, $branch);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("CSK code check execute error: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return null;
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        mysqli_stmt_close($stmt);
        return null;
    }

    $exists = mysqli_num_rows($result) > 0;
    mysqli_free_result($result);
    mysqli_stmt_close($stmt);
    return $exists;
}

function getNextChemicalSpillKitCode($branch)
{
    global $conn;

    $branch = trim((string)$branch);
    if ($branch === '') {
        return null;
    }

    $sql = "SELECT chemical_spill_kit_code
            FROM chemical_spill_kit_materials_inventory_tbl
            WHERE chemical_spill_kit_code LIKE 'CSK-%'
              AND LOWER(TRIM(branch)) = LOWER(TRIM(?))
            ORDER BY CAST(SUBSTRING(chemical_spill_kit_code, 5) AS UNSIGNED) DESC
            LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("CSK next-code prepare error: " . mysqli_error($conn));
        return null;
    }

    mysqli_stmt_bind_param($stmt, "s", $branch);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("CSK next-code execute error: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return null;
    }

    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    if ($result) {
        mysqli_free_result($result);
    }
    mysqli_stmt_close($stmt);

    $lastNumber = $row ? (int)substr((string)$row['chemical_spill_kit_code'], 4) : 0;
    return 'CSK-' . str_pad((string)($lastNumber + 1), 3, '0', STR_PAD_LEFT);
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

function archiveChemicalSpillKit($id, $branch)
{
    global $conn;

    $sql = "UPDATE chemical_spill_kit_materials_inventory_tbl
            SET archived = 1
            WHERE chemical_spill_kit_id = ?
              AND archived = 0
              AND (? = 'all' OR branch = ?)";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("CSK archive prepare error: " . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, "iss", $id, $branch, $branch);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("CSK archive execute error: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    $archived = mysqli_stmt_affected_rows($stmt) === 1;
    mysqli_stmt_close($stmt);
    return $archived;
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
    $nitrile_gloves_remarks,
    $kit_remarks = null
) {
    global $conn;

    $stmt = null;
    $check = null;
    $stmtMaterial = null;
    $transactionStarted = false;
    try {
        // Start transaction
        if (!mysqli_begin_transaction($conn)) {
            throw new Exception(mysqli_error($conn));
        }
        $transactionStarted = true;

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
            $check = null;

            if (!$exists) {
                mysqli_stmt_close($stmt);
                mysqli_rollback($conn);
                return false;
            }
        }

        mysqli_stmt_close($stmt);
        $stmt = null;

        // Material values to update in the current inventory materials table
        $materials = [
            [
                'name' => 'Pads',
                'status' => $pads_status,
                'remarks' => $pads_remarks
            ],
            [
                'name' => 'Boom Pillow',
                'status' => $boom_pillow_status,
                'remarks' => $boom_pillow_remarks
            ],
            [
                'name' => 'Socks',
                'status' => $socks_status,
                'remarks' => $socks_remarks
            ],
            [
                'name' => 'Disposal Bag',
                'status' => $disposal_bag_status,
                'remarks' => $disposal_bag_remarks
            ],
            [
                'name' => 'Goggles',
                'status' => $goggles_status,
                'remarks' => $goggles_remarks
            ],
            [
                'name' => 'Face Mask',
                'status' => $face_mask_status,
                'remarks' => $face_mask_remarks
            ],
            [
                'name' => 'Rubber Gloves',
                'status' => $rubber_gloves_status,
                'remarks' => $rubber_gloves_remarks
            ],
            [
                'name' => 'Nitrile Gloves',
                'status' => $nitrile_gloves_status,
                'remarks' => $nitrile_gloves_remarks
            ]
        ];

        // Insert a material if it does not exist; otherwise update its current values
        $sqlMaterial = "INSERT INTO chemical_spill_kit_current_inventory_materials_tbl
                    (chemical_spill_kit_id, material_name, status, remarks)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
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
                "isss",
                $id,
                $material['name'],
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
        $stmtMaterial = null;

        // Commit both table updates
        if (!mysqli_commit($conn)) {
            error_log("CSK inventory commit error: " . mysqli_error($conn));
            mysqli_rollback($conn);
            return false;
        }

        return true;
    } catch (Throwable $e) {
        if ($transactionStarted) {
            mysqli_rollback($conn);
        }
        foreach ([$stmt, $check, $stmtMaterial] as $pendingStatement) {
            if ($pendingStatement instanceof mysqli_stmt) {
                $pendingStatement->close();
            }
        }
        error_log("CSK inventory update failed: " . $e->getMessage());
        return false;
    }
}


// this is for adding new chemical spill kit
// Add new Chemical Spill Kit
function addNewChemicalSpillKit(
    $code,
    $branch,
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
    $nitrile_gloves_remarks,
    $kit_remarks = null,
) {
    global $conn;

    $stmt = null;
    $materialStmt = null;
    $transactionStarted = false;
    try {
        // Start transaction so the kit and its material details are saved together.
        if (!$conn->begin_transaction()) {
            throw new Exception($conn->error);
        }
        $transactionStarted = true;

        if (!preg_match('/^CSK-\d+$/', (string)$code) || trim((string)$branch) === '') {
            throw new Exception("Invalid CSK code or branch.");
        }

        // Lock/check the code + branch pair before writing. The current table has a matching non-unique index.
        if (chemicalSpillKitCodeExists($code, $branch, true) !== false) {
            throw new Exception("CSK code already exists in this branch or could not be verified.");
        }

        // Insert the main Chemical Spill Kit inventory record.
        $sql = "INSERT INTO chemical_spill_kit_materials_inventory_tbl
                (chemical_spill_kit_code, branch, location, pads, boom_pillow,
                 socks, disposal_bag, goggles, face_mask, rubber_gloves,
                 nitrile_gloves, archived, is_inspected_this_month)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception($conn->error);
        }

        $stmt->bind_param(
            "sssssssssss",
            $code,
            $branch,
            $location,
            $pads,
            $boom_pillow,
            $socks,
            $disposal_bag,
            $goggles,
            $face_mask,
            $rubber_gloves,
            $nitrile_gloves
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        // Get the new kit ID for its material status and remarks records.
        $chemical_spill_kit_id = $conn->insert_id;
        $stmt->close();
        $stmt = null;

        // Insert each material's status and remarks.
        $materials = [
            [
                'name' => 'Pads',
                'status' => $pads_status,
                'remarks' => $pads_remarks
            ],
            [
                'name' => 'Boom Pillow',
                'status' => $boom_pillow_status,
                'remarks' => $boom_pillow_remarks
            ],
            [
                'name' => 'Socks',
                'status' => $socks_status,
                'remarks' => $socks_remarks
            ],
            [
                'name' => 'Disposal Bag',
                'status' => $disposal_bag_status,
                'remarks' => $disposal_bag_remarks
            ],
            [
                'name' => 'Goggles',
                'status' => $goggles_status,
                'remarks' => $goggles_remarks
            ],
            [
                'name' => 'Face Mask',
                'status' => $face_mask_status,
                'remarks' => $face_mask_remarks
            ],
            [
                'name' => 'Rubber Gloves',
                'status' => $rubber_gloves_status,
                'remarks' => $rubber_gloves_remarks
            ],
            [
                'name' => 'Nitrile Gloves',
                'status' => $nitrile_gloves_status,
                'remarks' => $nitrile_gloves_remarks
            ]
        ];

        $materialSql = "INSERT INTO chemical_spill_kit_current_inventory_materials_tbl
                        (chemical_spill_kit_id, material_name, status, remarks)
                        VALUES (?, ?, ?, ?)";

        $materialStmt = $conn->prepare($materialSql);

        if (!$materialStmt) {
            throw new Exception($conn->error);
        }

        foreach ($materials as $material) {
            $materialName = $material['name'];
            $materialStatus = $material['status'];
            $materialRemarks = $material['remarks'];

            $materialStmt->bind_param(
                "isss",
                $chemical_spill_kit_id,
                $materialName,
                $materialStatus,
                $materialRemarks
            );

            if (!$materialStmt->execute()) {
                throw new Exception($materialStmt->error);
            }
        }

        $materialStmt->close();
        $materialStmt = null;

        // Commit only if the kit and all materials were inserted successfully.
        if (!$conn->commit()) {
            throw new Exception($conn->error);
        }

        return true;
    } catch (Throwable $e) {
        if ($transactionStarted) {
            $conn->rollback();
        }
        if ($stmt instanceof mysqli_stmt) {
            $stmt->close();
        }
        if ($materialStmt instanceof mysqli_stmt) {
            $materialStmt->close();
        }
        error_log("Failed to add Chemical Spill Kit: " . $e->getMessage());

        return false;
    }
}
