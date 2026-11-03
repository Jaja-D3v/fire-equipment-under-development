<?php
require_once __DIR__ . '/SessionChecker.php';

function requireSuperAdminAccess($jsonResponse = false)
{
    if (($_SESSION['Role'] ?? '') === 'Super Admin') {
        return true;
    }

    http_response_code(403);
    if ($jsonResponse) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array(
            'success' => false,
            'message' => 'Forbidden.'
        ));
        exit;
    }

    $documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    $scriptDir = str_replace('\\', '/', realpath(__DIR__));
    $basePath = str_replace($documentRoot, '', $scriptDir);
    $basePath = preg_replace('#/backend/authentication(/.*)?$#', '', $basePath);
    header('Location: ' . $basePath . '/error-pages/401.html');
    exit;
}
