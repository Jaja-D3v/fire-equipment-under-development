<?php

require_once __DIR__ . '/../config/db_user.php';

function loginUser($loginID)
{
    global $conn;
    $sql = "
        SELECT
            u.id,
            u.LoginID,
            u.password,
            u.Role,
            u.EmployeeName,
            u.what_system,
            u.Status,
            j.Location
        FROM kane_users_login u
        LEFT JOIN kane_jobinfo j
            ON u.LoginID = j.IDNumber
        WHERE u.LoginID = ?
    ";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $loginID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        return false;
    }

    return $user;
}
