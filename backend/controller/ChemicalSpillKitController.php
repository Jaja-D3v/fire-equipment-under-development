<?php

require_once __DIR__ . '/../model/ChemicalSpillKitModel.php';
require_once __DIR__ . '/DropdownController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $code = $_POST['chemical_spill_kit_code'] ?? null;
    $branch = $_POST['branch'] ?? null;
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


    addNewChemicalSpillKitController(
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


































// Get all Chemical Spill Kit inventory records
function getAllListChemicalSpill($branch, $limit = 10, $offset = 0, $search = '')
{
    return getAllChemicalSpillKit($branch, $limit, $offset, $search);
}

// Admins may select a branch; all other roles are restricted to their session branch.
$isChemicalSpillKitAdmin = strtolower(trim($_SESSION['Role'] ?? '')) === 'admin';
if ($isChemicalSpillKitAdmin) {
    $branch = trim($_GET['branch'] ?? 'all');
    if ($branch === '') {
        $branch = 'all';
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
