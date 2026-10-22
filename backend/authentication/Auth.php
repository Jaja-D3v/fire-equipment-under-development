<?php
header('Content-Type: application/json');

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

$timeout = 310; // 5 mins + 10s buffer

if (
    isset($_SESSION['LAST_ACTIVITY']) &&
    (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)
) {
    session_unset();
    session_destroy();

    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Session expired."
    ]);
    exit;
}

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized."
    ]);
    exit;
}

// I-refresh ang session activity timestamp sa PHP
$_SESSION['LAST_ACTIVITY'] = time();

echo json_encode([
    "success" => true,
    "message" => "Session extended successfully.",
    "user" => [
        "id" => $_SESSION['id'],
        "LoginID" => $_SESSION['LoginID'] ?? null,
        "Role" => $_SESSION['Role'] ?? null,
        "EmployeeName" => $_SESSION['EmployeeName'] ?? null
    ]
]);