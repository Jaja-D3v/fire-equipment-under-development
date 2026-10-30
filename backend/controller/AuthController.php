<?php

require_once __DIR__ . '/../model/AuthModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = loginUser($username, $password);

    if (
        $user &&
        $username === $user['LoginID'] &&
        md5($password) === $user['password'] && $user['Status'] == '1' &&  ($user['what_system'] === 'KPFEMS' || $user['what_system'] === 'KPAMS')
    ) {

        /*
         * Start session
         */
        session_start();

        /*
         * Regenerate session ID after successful login
         */
        session_regenerate_id(true);
        /*
         * Store authenticated user information
         */
        $_SESSION['id'] = $user['id'];
        $_SESSION['LoginID'] = $user['LoginID'];
        $role =  $_SESSION['Role'] = $user['Role'];
        $_SESSION['EmployeeName'] = $user['EmployeeName'];
        $_SESSION['Branch'] = $user['Location'];
        $_SESSION['System'] = $user['what_system'];
        $_SESSION['Status'] = $user['Status'];

        /*
         * Start inactivity timer
         */
        $_SESSION['LAST_ACTIVITY'] = time();

        /*
         * Check if request is AJAX
         */
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($isAjax) {

            header('Content-Type: application/json');


            $documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
            $scriptDir = str_replace('\\', '/', realpath(__DIR__));

            $basePath = str_replace($documentRoot, '', $scriptDir);

            // Alisin ang backend/controller sa project path
            $basePath = preg_replace('#/backend/controller(/.*)?$#', '', $basePath);

            if ($role === 'Inspector') {
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    "last_activity" => $_SESSION["LAST_ACTIVITY"],
                    'redirect' => $basePath . '/dashboard.php'
                ]);

                exit();
            } elseif ($role === 'Admin') {
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    "last_activity" => $_SESSION["LAST_ACTIVITY"],
                    'redirect' => $basePath . '/authentication/system-selector.php'
                ]);

                exit();
            }
        }

        /*
         * Normal login fallback
         */

        if ($role == 'Inspector') {
            header('Location:' . $basePath . '/dashboard.php');
            exit();
        } elseif ($role == 'Admin') {
            header('Location:' . $basePath . '/authentication/system-selector.php');
            exit();
        }
    } else {

        /*
         * Check if request is AJAX
         */
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($isAjax) {

            header('Content-Type: application/json');

            echo json_encode([
                'success' => false,
                'message' => 'Invalid username or password.'
            ]);

            exit();
        }

        /*
         * Normal login fallback
         */
        header(
            'Location: ../../authentication/login.php?error=invalid_credentials'
        );

        exit();
    }
}
