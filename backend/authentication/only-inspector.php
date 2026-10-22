<?php
require_once 'SessionChecker.php';

$role = strtolower(trim($_SESSION['Role'] ?? ''));

$documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$scriptDir = str_replace('\\', '/', realpath(__DIR__));

$basePath = str_replace($documentRoot, '', $scriptDir);

// Alisin ang /backend/controller sa project path
$basePath = preg_replace('#/backend/authentication(/.*)?$#', '', $basePath);

if ($role !== 'inspector') {
    header('Location: ' . $basePath . '/error-pages/401.html');
    exit;
}
