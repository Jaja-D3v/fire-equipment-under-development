<?php

session_start();

/**
 * --------------------------------------------------------------------------
 * Destroy PHP Session
 * --------------------------------------------------------------------------
 */

// Unset all session variables
$_SESSION = [];

// Delete PHP session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy the session
session_destroy();

// Redirect to login form
header('Location: ../../authentication/login.php');
exit;