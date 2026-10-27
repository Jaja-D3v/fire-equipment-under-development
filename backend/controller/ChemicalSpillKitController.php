<?php

require_once __DIR__ . '/../model/ChemicalSpillKitModel.php';

// Get all Chemical Spill Kit inventory records
function getAllListChemicalSpill($branch, $limit = 10, $offset = 0)
{
    return getAllChemicalSpillKit($branch, $limit, $offset);
}

// Get branch based on the logged-in user's role
if (strtolower(trim($_SESSION['Role'] ?? '')) === 'admin') {
    $branch = trim($_GET['branch'] ?? 'all');
} else {
    $branch = trim($_SESSION['Branch'] ?? '');
}

// Pagination: 10 records per page
$limit = 10;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$offset = ($currentPage - 1) * $limit;

// Get inventory records
$chemicalSpillKits = getAllListChemicalSpill($branch, $limit, $offset);



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
