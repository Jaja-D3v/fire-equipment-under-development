<?php
require_once __DIR__ . '/../authentication/SuperAdminGuard.php';
requireSuperAdminAccess(true);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
require_once __DIR__ . '/SuperAdminMonitoringController.php';

try {
    echo json_encode(array('success' => true, 'data' => getSuperAdminMonitoringData($_GET)));
} catch (Throwable $e) {
    error_log('Super Admin monitoring request failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Monitoring data is temporarily unavailable.'));
}
