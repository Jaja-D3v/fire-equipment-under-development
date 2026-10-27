<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../authentication/SessionChecker.php';
require_once __DIR__ . '/../model/DropdownModel.php';
require_once __DIR__ . '/../model/ChemicalSpillKitRequiredStockModel.php';

function cskRequiredStockJsonResponse($statusCode, array $payload)
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    cskRequiredStockJsonResponse(405, [
        'success' => false,
        'error' => 'method_not_allowed',
        'message' => 'Use GET to retrieve required stock.'
    ]);
}

$role = strtolower(trim((string)($_SESSION['Role'] ?? '')));
if (!in_array($role, ['admin', 'inspector'], true)) {
    cskRequiredStockJsonResponse(403, [
        'success' => false,
        'error' => 'forbidden',
        'message' => 'You are not allowed to retrieve Chemical Spill Kit required stock.'
    ]);
}

$action = trim((string)($_GET['action'] ?? ''));
if ($action !== 'getRequiredStock') {
    cskRequiredStockJsonResponse(400, [
        'success' => false,
        'error' => 'invalid_action',
        'message' => 'Use action=getRequiredStock.'
    ]);
}

$requestedBranch = trim((string)($_GET['branch'] ?? ''));
if ($requestedBranch === '' || strtolower($requestedBranch) === 'all') {
    cskRequiredStockJsonResponse(400, [
        'success' => false,
        'error' => 'invalid_branch',
        'message' => 'A specific branch is required.'
    ]);
}

if ($role !== 'admin') {
    $sessionBranch = trim((string)($_SESSION['Branch'] ?? ''));
    if ($sessionBranch === '' || strcasecmp($requestedBranch, $sessionBranch) !== 0) {
        cskRequiredStockJsonResponse(403, [
            'success' => false,
            'error' => 'branch_forbidden',
            'message' => 'You may only retrieve required stock for your assigned branch.'
        ]);
    }
    $requestedBranch = $sessionBranch;
}

$selectedBranch = null;
try {
    foreach (getAllBranchDropdown() as $branchOption) {
        $activeBranch = trim((string)($branchOption['value'] ?? ''));
        if ($activeBranch !== '' && strcasecmp($activeBranch, $requestedBranch) === 0) {
            $selectedBranch = $activeBranch;
            break;
        }
    }
} catch (mysqli_sql_exception $exception) {
    error_log("Required stock active-branch validation error: " . $exception->getMessage());
    cskRequiredStockJsonResponse(500, [
        'success' => false,
        'error' => 'database_error',
        'message' => 'Unable to validate the requested branch.'
    ]);
}

if ($selectedBranch === null) {
    cskRequiredStockJsonResponse(400, [
        'success' => false,
        'error' => 'invalid_branch',
        'message' => 'The requested branch is not an active branch.'
    ]);
}

$requiredStocks = getRequiredStockModel($selectedBranch);
if ($requiredStocks === false) {
    cskRequiredStockJsonResponse(500, [
        'success' => false,
        'error' => 'database_error',
        'message' => 'Unable to retrieve required stock for this branch.'
    ]);
}

$stockByMaterialName = [];
foreach ($requiredStocks as $materialName => $requiredStock) {
    $stockByMaterialName[strtolower(trim((string)$materialName))] = $requiredStock;
}

$requiredStockByFormKey = [
    'pads' => $stockByMaterialName['pads'] ?? null,
    'boom_pillow' => $stockByMaterialName['boom/pillow'] ?? null,
    'socks' => $stockByMaterialName['socks'] ?? null,
    'disposal_bag' => $stockByMaterialName['disposal bag'] ?? null,
    'goggles' => $stockByMaterialName['goggles'] ?? null,
    'face_mask' => $stockByMaterialName['face mask'] ?? null,
    'rubber_gloves' => $stockByMaterialName['rubber gloves'] ?? null,
    'nitrile_gloves' => $stockByMaterialName['nitrile gloves'] ?? null,
];

cskRequiredStockJsonResponse(200, [
    'success' => true,
    'branch' => $selectedBranch,
    'required_stock' => $requiredStockByFormKey
]);