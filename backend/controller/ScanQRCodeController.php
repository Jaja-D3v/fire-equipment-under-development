<?php

require_once __DIR__ . '/../model/ScanQRCodeModel.php';
require_once __DIR__ . '/../model/ChemicalSpillKitModel.php';
require_once __DIR__ . '/../authentication/SessionChecker.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
    exit;
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$code = trim($_GET['code'] ?? '');
$role = strtolower(trim($_SESSION['Role'] ?? ''));

if (!in_array($role, ['inspector', 'admin'], true)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'You are not allowed to scan equipment.'
    ]);
    exit;
}

if ($code === '') {
    echo json_encode([
        'success' => false,
        'message' => 'No QR code was provided.'
    ]);
    exit;
}

/**
 * Resolve branch securely.
 * Inspectors always use their assigned session branch.
 * Admins use their selected branch, falling back to their assigned branch.
 */
function resolveScanBranch($role)
{
    if ($role === 'inspector') {
        return trim($_SESSION['Branch'] ?? '');
    }

    $branch = trim($_GET['branch'] ?? '');

    if ($branch === '') {
        $branch = trim($_SESSION['Branch'] ?? '');
    }

    return $branch;
}

/**
 * Existing Fire Extinguisher lookup.
 * Keep the existing model and lookup behavior.
 */
if ($action === 'getFeInfo') {
    $requestedBranch = resolveScanBranch($role);

    $data = getFeInfoByCodeController(
        $code,
        $role,
        $requestedBranch
    );

    if (!empty($data['ambiguous'])) {
        echo json_encode([
            'success' => false,
            'message' => 'This code exists in more than one branch. Select a branch and scan again.'
        ]);
        exit;
    }

    if (!$data) {
        echo json_encode([
            'success' => false,
            'message' => $role === 'inspector'
                ? 'No active fire extinguisher with this code was found in your assigned branch.'
                : 'No active fire extinguisher with this code was found in the selected branch.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'equipmentType' => 'FE',
        'data' => $data
    ]);
    exit;
}

/**
 * Chemical Spill Kit lookup.
 */
if ($action === 'getChemicalSpillKitInfo') {
    if (!preg_match('/^CSK-/i', $code)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid Chemical Spill Kit QR code.'
        ]);
        exit;
    }

    $branch = resolveScanBranch($role);

    if ($branch === '' || strcasecmp($branch, 'all') === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Select a specific branch before scanning this kit.'
        ]);
        exit;
    }

    $results = getChemicalSpillKitByCode($code, $branch);

    // The existing CSK model returns an array of rows.
    $data = (
        is_array($results) &&
        isset($results[0]) &&
        is_array($results[0])
    ) ? $results[0] : null;

    if (!$data) {
        echo json_encode([
            'success' => false,
            'message' => 'No active Chemical Spill Kit was found in your authorized branch.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'equipmentType' => 'CSK',
        'data' => $data
    ]);
    exit;
}

http_response_code(400);
echo json_encode([
    'success' => false,
    'message' => 'Unsupported equipment scanning action.'
]);
exit;


/**
 * Existing Fire Extinguisher controller helper.
 */
function getFeInfoByCodeController($code, $role, $requestedBranch = '')
{
    return getFeInfoByCode($code, $role, $requestedBranch);
}
