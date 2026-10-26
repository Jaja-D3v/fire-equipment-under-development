
<?php

require_once __DIR__ . '/../model/ChemicalSpillKitInspectionModel.php';
require_once __DIR__ . '/ActivityLogController.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $submitted_by = $_POST['inspected_by'] ?? '';
    $code = $_POST['code'] ?? '';
    $branch = $_POST['branch'] ?? '';
    $location = $_POST['location'] ?? '';

    // name of inspector 
    $inspected_by = $submitted_by;

    // Use current server date and time.
    $date_inspected = date('Y-m-d H:i:s');

    // Pads
    $pads = $_POST['pads'] ?? '';
    $pads_status = $_POST['pads_status'] ?? '';
    $pads_remarks = $_POST['pads_remarks'] ?? '';

    // Boom / Pillow
    $boom_pillow = $_POST['boom_pillow'] ?? '';
    $boom_pillow_status = $_POST['boom_pillow_status'] ?? '';
    $boom_pillow_remarks = $_POST['boom_pillow_remarks'] ?? '';

    // Socks
    $socks = $_POST['socks'] ?? '';
    $socks_status = $_POST['socks_status'] ?? '';
    $socks_remarks = $_POST['socks_remarks'] ?? '';

    // Disposal Bag
    $disposal_bag = $_POST['disposal_bag'] ?? '';
    $disposal_bag_status = $_POST['disposal_bag_status'] ?? '';
    $disposal_bag_remarks = $_POST['disposal_bag_remarks'] ?? '';

    // Goggles
    $goggles = $_POST['goggles'] ?? '';
    $goggles_status = $_POST['goggles_status'] ?? '';
    $goggles_remarks = $_POST['goggles_remarks'] ?? '';

    // Face Mask
    $face_mask = $_POST['face_mask'] ?? '';
    $face_mask_status = $_POST['face_mask_status'] ?? '';
    $face_mask_remarks = $_POST['face_mask_remarks'] ?? '';

    // Rubber Gloves
    $rubber_gloves = $_POST['rubber_gloves'] ?? '';
    $rubber_gloves_status = $_POST['rubber_gloves_status'] ?? '';
    $rubber_gloves_remarks = $_POST['rubber_gloves_remarks'] ?? '';

    // Nitrile Gloves
    $nitrile_gloves = $_POST['nitrile_gloves'] ?? '';
    $nitrile_gloves_status = $_POST['nitrile_gloves_status'] ?? '';
    $nitrile_gloves_remarks = $_POST['nitrile_gloves_remarks'] ?? '';

    // Resolve the kit ID from the database.
    $chemical_spill_kit_id = getChemicalSpillKitIdByCode(
        $code,
        $branch
    );

    if (!$chemical_spill_kit_id) {
        http_response_code(404);
        exit('Chemical Spill Kit not found for the specified branch.');
    }

    $result = insertChemicalInspectionController(
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
    );

    if ($result !== false) {

        $user_name = $_SESSION['EmployeeName'] ?? 'error while getting employee name';
        createActivityLog(
            $user_name,
            "Inspect Chemical Spill Kit",
            "Inspect Chemical Spill Kit $code"
        );
        header("Location: ../../scan-QR-Code.php?success-inspect=1");

        exit;

    } else {
        http_response_code(500);
        echo 'Failed to submit inspection.';
    }

}

/**
 * Controller function that passes inspection data to the model.
 */
function insertChemicalInspectionController(
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
    return insertChemicalInspection(
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
    );
}


// debugger

// echo "<pre style='
//     background:#1e1e1e;
//     color:#d4d4d4;
//     padding:20px;
//     border-radius:10px;
//     font-family:Consolas,monospace;
//     font-size:14px;
//     line-height:1.6;
//     overflow:auto;
// '>";

// echo "========================================\n";
// echo " CHEMICAL SPILL KIT INSPECTION DEBUG\n";
// echo "========================================\n\n";

// echo "----- INSPECTION INFORMATION -----\n";
// var_dump(
//     $code,
//     $branch,
//     $location,
//     $inspected_by,
//     $date_inspected,
//     $chemical_spill_kit_id,
//     $submitted_by
// );

// echo "\n----- PADS -----\n";
// var_dump($pads, $pads_status, $pads_remarks);

// echo "\n----- BOOM PILLOW -----\n";
// var_dump($boom_pillow, $boom_pillow_status, $boom_pillow_remarks);

// echo "\n----- SOCKS -----\n";
// var_dump($socks, $socks_status, $socks_remarks);

// echo "\n----- DISPOSAL BAG -----\n";
// var_dump($disposal_bag, $disposal_bag_status, $disposal_bag_remarks);

// echo "\n----- GOGGLES -----\n";
// var_dump($goggles, $goggles_status, $goggles_remarks);

// echo "\n----- FACE MASK -----\n";
// var_dump($face_mask, $face_mask_status, $face_mask_remarks);

// echo "\n----- RUBBER GLOVES -----\n";
// var_dump($rubber_gloves, $rubber_gloves_status, $rubber_gloves_remarks);

// echo "\n----- NITRILE GLOVES -----\n";
// var_dump($nitrile_gloves, $nitrile_gloves_status, $nitrile_gloves_remarks);

// echo "\n========================================\n";
// echo " END OF DEBUG OUTPUT\n";
// echo "========================================\n";

// echo "</pre>";

// exit;
