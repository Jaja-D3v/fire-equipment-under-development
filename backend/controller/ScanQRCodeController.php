<?php

require_once __DIR__ . '/../model/ScanQRCodeModel.php';
require_once __DIR__ . '/../authentication/SessionChecker.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $action = $_GET['action'] ?? null;


    if ($action === 'getFeInfo') {
        header('Content-Type: application/json');

        $code = trim($_GET['code'] ?? '');
        $role = strtolower(trim($_SESSION['Role'] ?? ''));
        $requestedBranch = trim($_GET['branch'] ?? '');
        if ($role === 'admin' && $requestedBranch === '') {
            $requestedBranch = trim($_SESSION['Branch'] ?? '');
        }

        if ($code === '') {
            echo json_encode([
                'success' => false,
                'message' => 'No QR code was provided.'
            ]);
            exit;
        }

        if (!in_array($role, array('inspector', 'admin'), true)) {
            http_response_code(403);
            echo json_encode(array(
                'success' => false,
                'message' => 'You are not allowed to scan fire extinguishers.'
            ));
            exit;
        }

        $data = getFeInfoByCodeController($code, $role, $requestedBranch);

        if (!empty($data['ambiguous'])) {
            echo json_encode(array(
                'success' => false,
                'message' => 'This code exists in more than one branch. Select a branch and scan again.'
            ));
            exit;
        }

        if (!$data) {
            $message = $role === 'inspector'
                ? 'No active fire extinguisher with this code was found in your assigned branch.'
                : 'No active fire extinguisher with this code was found in the selected branch.';
            echo json_encode([
                'success' => false,
                'message' => $message
            ]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);

        exit;
    }
}

// Get fire extinguisher by code
function getFeInfoByCodeController($code, $role, $requestedBranch = '')
{
    return getFeInfoByCode($code, $role, $requestedBranch);
}
