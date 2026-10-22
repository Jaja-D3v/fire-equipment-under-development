<?php
require_once __DIR__ . '/../config/db.php';

// add activity log
function addActivityLog($user_name, $action, $description)
{
    global $conn;

    $sql = "INSERT INTO activity_logs_tbl
            (
                user_name,
                action,
                description
            )
            VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $user_name,
        $action,
        $description
    );

    return mysqli_stmt_execute($stmt);
}

// get all activity log
function getActivityLogs($limit = 10, $offset = 0, $month = '', $date = '')
{
    global $conn;

    $limit = (int) $limit;
    $offset = (int) $offset;

    $user_name = $_SESSION['EmployeeName'] ?? '';

    $conditions = ["user_name = ?"];
    $params = [$user_name];
    $types = "s";

    // Exact date filter
    if (!empty($date)) {
        $conditions[] = "DATE(created_at) = ?";
        $params[] = $date;
        $types .= "s";
    }

    // Month filter
    elseif ($month !== '') {
        $conditions[] = "MONTH(created_at) = ?";
        $params[] = (int) $month + 1;
        $types .= "i";
    }

    $where = "WHERE " . implode(" AND ", $conditions);

    $sql = "SELECT
                log_id,
                user_name,
                action,
                description,
                created_at
            FROM activity_logs_tbl
            $where
            ORDER BY created_at DESC
            LIMIT $limit OFFSET $offset";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return $result;
}

// activity log count
function getActivityLogsCount($month = '', $date = '')
{
    global $conn;

    $user_name = $_SESSION['EmployeeName'] ?? '';
    $conditions = ["user_name = ?"];
    $params = [$user_name];
    $types = "s";

    // Exact date filter
    if (!empty($date)) {
        $conditions[] = "DATE(created_at) = ?";
        $params[] = $date;
        $types .= "s";
    }

    // Month filter
    elseif ($month !== '') {
        $conditions[] = "MONTH(created_at) = ?";
        $params[] = (int) $month + 1;
        $types .= "i";
    }

    $where = "WHERE " . implode(" AND ", $conditions);

    $sql = "SELECT COUNT(*) AS total
            FROM activity_logs_tbl
            $where";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return 0;
    }

    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return (int) $row['total'];
}