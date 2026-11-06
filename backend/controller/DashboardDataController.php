<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Match SessionChecker's cookie settings, but return JSON instead of redirecting.
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 3600,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// An expired session must not return dashboard data.
$sessionExpired = isset($_SESSION['LAST_ACTIVITY'])
    && time() - $_SESSION['LAST_ACTIVITY'] > 310;
if (!isset($_SESSION['id']) || $sessionExpired) {
    session_unset();
    session_destroy();
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Your session has expired. Please sign in again.']);
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time();
$role = strtolower(trim($_SESSION['Role'] ?? ''));
if (!in_array($role, ['admin', 'inspector', 'super admin'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

// Release the session lock before the read-only database queries.
session_write_close();

try {
    require_once __DIR__ . '/DashboardSummaryController.php';
    $data = getDashboardSummaryData($_GET, $_SESSION);
    echo json_encode(['success' => true, 'data' => $data]);
} catch (InvalidArgumentException $error) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $error->getMessage()]);
} catch (Throwable $error) {
    if ($error->getCode() === 403) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => $error->getMessage()]);
    } else {
        // Log database details on the server; keep the client message readable.
        error_log('Dashboard summary: ' . $error->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Dashboard data could not be loaded. Please try again.']);
    }
}
