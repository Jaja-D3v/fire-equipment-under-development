<?php

require_once __DIR__ . '/../model/ChemicalSpillKitModel.php';
require_once __DIR__ . '/DropdownController.php';
require_once __DIR__ . '/ActivityLogController.php';

// CSK-specific code endpoints follow the FE getNextCode/checkCode response pattern.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action === 'getNextCode' || $action === 'checkCode') {
        header('Content-Type: application/json; charset=utf-8');
        $branch = trim((string)($_GET['branch'] ?? ''));
        if ($branch === '') {
            echo json_encode(['success' => false, 'exists' => false]);
            exit;
        }

        if ($action === 'getNextCode') {
            $code = getNextChemicalSpillKitCode($branch);
            echo json_encode([
                'success' => is_string($code) && preg_match('/^CSK-\d+$/', $code) === 1,
                'code' => $code
            ]);
            exit;
        }

        $code = trim((string)($_GET['code'] ?? ''));
        $exists = $code === '' ? false : chemicalSpillKitCodeExists($code, $branch);
        echo json_encode([
            'success' => is_bool($exists),
            'exists' => $exists === true
        ]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cskAction = trim($_POST['csk_action'] ?? 'add');
    if ($cskAction === 'edit' || $cskAction === 'archive') {
        $isAdmin = strtolower(trim($_SESSION['Role'] ?? '')) === 'admin';
        $kitId = (int)($_POST['chemical_spill_kit_id'] ?? 0);
        $kitCode = trim($_POST['chemical_spill_kit_code'] ?? '');
        $kitBranch = $isAdmin
            ? trim($_POST['branch'] ?? '')
            : trim($_SESSION['Branch'] ?? '');
        $kit = $kitCode !== '' && $kitBranch !== ''
            ? getChemicalSpillKitByCode($kitCode, $kitBranch)
            : null;

        if (
            !$kit ||
            (int)($kit['chemical_spill_kit_id'] ?? 0) !== $kitId ||
            (int)($kit['archived'] ?? 1) !== 0
        ) {
            header('Location: chemical-spill-kit-inventory.php?csk_action=error');
            exit;
        }

        if ($cskAction === 'archive') {
            $success = archiveChemicalSpillKit($kitId, $kitBranch);
            header('Location: chemical-spill-kit-inventory.php?csk_action=' . ($success ? 'archived' : 'error'));
            exit;
        }


        $success = editChemicalSpillKit(
            $kitId,
            trim($_POST['location'] ?? ''),

            // Pads
            trim($_POST['pads'] ?? ''),
            ($_POST['pads_status'] ?? '') !== '' ? $_POST['pads_status'] : null,
            trim($_POST['pads_remarks'] ?? ''),

            // Boom Pillow
            trim($_POST['boom_pillow'] ?? ''),
            ($_POST['boom_pillow_status'] ?? '') !== '' ? $_POST['boom_pillow_status'] : null,
            trim($_POST['boom_pillow_remarks'] ?? ''),

            // Socks
            trim($_POST['socks'] ?? ''),
            ($_POST['socks_status'] ?? '') !== '' ? $_POST['socks_status'] : null,
            trim($_POST['socks_remarks'] ?? ''),

            // Disposal Bag
            trim($_POST['disposal_bag'] ?? ''),
            ($_POST['disposal_bag_status'] ?? '') !== '' ? $_POST['disposal_bag_status'] : null,
            trim($_POST['disposal_bag_remarks'] ?? ''),

            // Goggles
            trim($_POST['goggles'] ?? ''),
            ($_POST['goggles_status'] ?? '') !== '' ? $_POST['goggles_status'] : null,
            trim($_POST['goggles_remarks'] ?? ''),

            // Face Mask
            trim($_POST['face_mask'] ?? ''),
            ($_POST['face_mask_status'] ?? '') !== '' ? $_POST['face_mask_status'] : null,
            trim($_POST['face_mask_remarks'] ?? ''),

            // Rubber Gloves
            trim($_POST['rubber_gloves'] ?? ''),
            ($_POST['rubber_gloves_status'] ?? '') !== '' ? $_POST['rubber_gloves_status'] : null,
            trim($_POST['rubber_gloves_remarks'] ?? ''),

            // Nitrile Gloves
            trim($_POST['nitrile_gloves'] ?? ''),
            ($_POST['nitrile_gloves_status'] ?? '') !== '' ? $_POST['nitrile_gloves_status'] : null,
            trim($_POST['nitrile_gloves_remarks'] ?? '')
        );

        header('Location: chemical-spill-kit-inventory.php?csk_action=' . ($success ? 'updated' : 'error'));
        exit;
    }

    $code = trim((string)($_POST['chemical_spill_kit_code'] ?? ''));
    $branch = trim((string)($_POST['branch'] ?? ''));

    if (!preg_match('/^CSK-\d+$/', $code) || $branch === '') {
        http_response_code(400);
        exit('A valid CSK code and branch are required.');
    }

    $codeExists = chemicalSpillKitCodeExists($code, $branch);
    if ($codeExists === null) {
        http_response_code(503);
        exit('Unable to verify the CSK code. No kit was added.');
    }
    if ($codeExists) {
        http_response_code(409);
        exit('This CSK code already exists in the selected branch. No kit was added.');
    }
    $location = $_POST['location'] ?? null;

    $pads = $_POST['pads'] ?? null;
    $pads_status = $_POST['pads_status'] ?? null;
    $pads_remarks = $_POST['pads_remarks'] ?? null;

    $boom_pillow = $_POST['boom_pillow'] ?? null;
    $boom_pillow_status = $_POST['boom_pillow_status'] ?? null;
    $boom_pillow_remarks = $_POST['boom_pillow_remarks'] ?? null;

    $socks = $_POST['socks'] ?? null;
    $socks_status = $_POST['socks_status'] ?? null;
    $socks_remarks = $_POST['socks_remarks'] ?? null;

    $disposal_bag = $_POST['disposal_bag'] ?? null;
    $disposal_bag_status = $_POST['disposal_bag_status'] ?? null;
    $disposal_bag_remarks = $_POST['disposal_bag_remarks'] ?? null;

    $goggles = $_POST['goggles'] ?? null;
    $goggles_status = $_POST['goggles_status'] ?? null;
    $goggles_remarks = $_POST['goggles_remarks'] ?? null;

    $face_mask = $_POST['face_mask'] ?? null;
    $face_mask_status = $_POST['face_mask_status'] ?? null;
    $face_mask_remarks = $_POST['face_mask_remarks'] ?? null;

    $rubber_gloves = $_POST['rubber_gloves'] ?? null;
    $rubber_gloves_status = $_POST['rubber_gloves_status'] ?? null;
    $rubber_gloves_remarks = $_POST['rubber_gloves_remarks'] ?? null;

    $nitrile_gloves = $_POST['nitrile_gloves'] ?? null;
    $nitrile_gloves_status = $_POST['nitrile_gloves_status'] ?? null;
    $nitrile_gloves_remarks = $_POST['nitrile_gloves_remarks'] ?? null;



    // add new chemical spill kit 
    function addNewChemicalSpillKitController(
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
    ) {

        return addNewChemicalSpillKit(
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
        );
    }


    $addSuccess =  addNewChemicalSpillKitController(
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
    );

    if ($addSuccess) {

        $user_name =
            $_SESSION['EmployeeName']
            ?? 'error while getting employee name';


        createActivityLog(
            $user_name,
            "Add Chemical Spill Kit",
            "Added Chemical Spill Kit $code"
        );


        header(
            "Location: ../../chemical-spill-kit-inventory.php?success-add=1"
        );

        exit;
    }
}











// Get all Chemical Spill Kit inventory records
function getAllListChemicalSpill($branch, $limit = 10, $offset = 0, $search = '')
{
    return getAllChemicalSpillKit($branch, $limit, $offset, $search);
}

// Admins may select a branch; all other roles are restricted to their session branch.
$isChemicalSpillKitAdmin = strtolower(trim($_SESSION['Role'] ?? '')) === 'admin';
if ($isChemicalSpillKitAdmin) {
    $branch = trim($_GET['branch'] ?? ($_SESSION['Branch'] ?? ''));
    if ($branch === '') {
        $branch = trim($_SESSION['Branch'] ?? '');
    }
} else {
    $branch = trim($_SESSION['Branch'] ?? '');
}

$search = trim($_GET['search'] ?? '');
$limit = 10;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$totalRecords = getChemicalSpillKitInventoryCount($branch, $search);
$totalPages = max(1, (int)ceil($totalRecords / $limit));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $limit;

$chemicalSpillKits = getAllListChemicalSpill($branch, $limit, $offset, $search);
$branches = $isChemicalSpillKitAdmin ? getAllDropdownBranches() : [];



// Get Chemical Spill Kit by code
function getChemicalSpillKitInventopryByCode($code, $branch)
{
    return getChemicalSpillKitByCode($code, $branch);
}



// Edit Chemical Spill Kit inventory
function editChemicalSpillKit(
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
    return updateSpillKitMaterials(
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
    );
}
