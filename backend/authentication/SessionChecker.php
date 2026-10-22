<?php
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Set to 5 minutes + 10 seconds buffer para hindi mauna ang server kaysa sa UI countdown
$timeout = 310;

if (
    !isset($_SESSION['id']) ||
    (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout))
) {
    session_unset();
    session_destroy();

    $projectRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    $currentDir = str_replace('\\', '/', realpath(__DIR__));

    // Kunin ang relative path mula htdocs papunta sa project root
    $basePath = str_replace($projectRoot, '', $currentDir);

    // Alisin ang /backend/authentication sa dulo
    $basePath = preg_replace('#/backend/authentication$#', '', $basePath);

    header('Location: ' . $basePath . '/error-pages/401.html');
    exit;
}

// I-update ang LAST_ACTIVITY sa bawat page refresh/navigation
$_SESSION['LAST_ACTIVITY'] = time();
