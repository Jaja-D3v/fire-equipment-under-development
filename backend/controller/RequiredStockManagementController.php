<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../authentication/SessionChecker.php';
require_once __DIR__ . '/../model/DropdownModel.php';
require_once __DIR__ . '/../model/ChemicalSpillKitRequiredStockModel.php';

function requiredStockManagementJsonResponse($statusCode, array $payload)
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function requiredStockManagementResolveBranch($requestedBranch)
{
    $requestedBranch = trim((string) $requestedBranch);
    if ($requestedBranch === '' || strtolower($requestedBranch) === 'all') {
        return false;
    }

    try {
        foreach (getAllBranchDropdown() as $branchOption) {
            $activeBranch = trim((string) ($branchOption['value'] ?? ''));
            if ($activeBranch !== '' && strcasecmp($activeBranch, $requestedBranch) === 0) {
                return $activeBranch;
            }
        }
    } catch (mysqli_sql_exception $error) {
        error_log('Required stock management branch lookup failed: ' . $error->getMessage());
        return null;
    }

    return false;
}

$role = strtolower(trim((string) ($_SESSION['Role'] ?? '')));
if ($role !== 'admin') {
    requiredStockManagementJsonResponse(403, array(
        'success' => false,
        'message' => 'Only Admin users may manage required stock.'
    ));
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$action = trim((string) ($method === 'POST' ? ($_POST['action'] ?? '') : ($_GET['action'] ?? '')));
$equipment = trim((string) ($method === 'POST' ? ($_POST['equipment'] ?? '') : ($_GET['equipment'] ?? '')));
if ($equipment !== 'chemical_spill_kit') {
    requiredStockManagementJsonResponse(400, array(
        'success' => false,
        'message' => 'Select a supported equipment category.'
    ));
}

if ($method !== 'GET' && $method !== 'POST') {
    header('Allow: GET, POST');
    requiredStockManagementJsonResponse(405, array(
        'success' => false,
        'message' => 'Use GET to list required stock and POST to save a change.'
    ));
}

$branch = requiredStockManagementResolveBranch(
    $method === 'POST' ? ($_POST['branch'] ?? '') : ($_GET['branch'] ?? '')
);
if ($branch === null) {
    requiredStockManagementJsonResponse(500, array(
        'success' => false,
        'message' => 'Unable to validate the selected branch.'
    ));
}
if ($branch === false) {
    requiredStockManagementJsonResponse(400, array(
        'success' => false,
        'message' => 'Choose an active branch.'
    ));
}

if ($method === 'GET' && $action === 'list') {
    try {
        $materials = getRequiredStockManagementRowsModel($branch);
    } catch (Throwable $error) {
        error_log('Required stock management list failed: ' . $error->getMessage());
        $materials = false;
    }
    if ($materials === false) {
        requiredStockManagementJsonResponse(500, array(
            'success' => false,
            'message' => 'Unable to load required stock for this branch.'
        ));
    }
    requiredStockManagementJsonResponse(200, array(
        'success' => true,
        'equipment' => $equipment,
        'branch' => $branch,
        'materials' => $materials
    ));
}

if ($method === 'POST' && $action === 'update') {
    $materialId = filter_var($_POST['material_id'] ?? null, FILTER_VALIDATE_INT);
    $rawStock = trim((string) ($_POST['required_stock'] ?? ''));
    if (
        $materialId === false ||
        $materialId === null ||
        $materialId <= 0 ||
        !preg_match('/^(0|[1-9][0-9]*)$/', $rawStock) ||
        (float) $rawStock > 4294967295
    ) {
        requiredStockManagementJsonResponse(422, array(
            'success' => false,
            'message' => 'Required stock must be a non-negative whole number.'
        ));
    }

    if (!updateRequiredStockManagementRowModel($materialId, $branch, (int) $rawStock)) {
        requiredStockManagementJsonResponse(404, array(
            'success' => false,
            'message' => 'That material was not found in the selected branch, or the update failed.'
        ));
    }

    requiredStockManagementJsonResponse(200, array(
        'success' => true,
        'message' => 'Required stock saved.'
    ));
}

requiredStockManagementJsonResponse(400, array(
    'success' => false,
    'message' => 'Invalid required-stock action.'
));
