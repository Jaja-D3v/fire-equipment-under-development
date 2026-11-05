<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Same cookie settings and 310-second inactivity policy as SessionChecker.php,
// with JSON status codes instead of an HTML redirect for fetch requests.
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params(array(
        'lifetime' => 3600, 'path' => '/', 'secure' => $isHttps,
        'httponly' => true, 'samesite' => 'Lax'
    ));
    session_start();
}
if (
    !isset($_SESSION['id']) ||
    (isset($_SESSION['LAST_ACTIVITY']) && time() - $_SESSION['LAST_ACTIVITY'] > 310)
) {
    session_unset();
    session_destroy();
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'Your session has expired. Please sign in again.'));
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();
if (!in_array(strtolower(trim($_SESSION['Role'] ?? '')), array('admin', 'inspector', 'super admin'), true)) {
    http_response_code(403);
    echo json_encode(array('success' => false, 'message' => 'Access denied.'));
    exit;
}
session_write_close();

try {
    require_once __DIR__ . '/DashboardSummaryController.php';
    $data = getDashboardSummaryData($_GET, $_SESSION);
    echo json_encode(array('success' => true, 'data' => $data));
} catch (InvalidArgumentException $error) {
    http_response_code(400);
    echo json_encode(array('success' => false, 'message' => $error->getMessage()));
} catch (Throwable $error) {
    $forbidden = $error->getCode() === 403;
    http_response_code($forbidden ? 403 : 500);
    if (!$forbidden) error_log('Dashboard summary: ' . $error->getMessage());
    echo json_encode(array('success' => false, 'message' => $forbidden ? $error->getMessage() : 'Dashboard data could not be loaded. Please try again.'));
}
