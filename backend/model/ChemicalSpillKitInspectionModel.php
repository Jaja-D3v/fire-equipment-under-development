
<?php

require_once __DIR__ . '/../config/db.php';

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
