<?php

require_once __DIR__ . '/../config/db.php';

function getFeInfoByCode($code, $role, $requestedBranch = '')
{
    global $conn;

    $role = strtolower(trim((string) $role));
    $requestedBranch = trim((string) $requestedBranch);

    if (!in_array($role, array('inspector', 'admin'), true)) {
        return false;
    }

    $sql = "SELECT
            extinguisher_id,
            extinguisher_code,
            location,
            type,
            capacity,
            class,
            branch
        FROM fire_extinguishers_tbl
        WHERE extinguisher_code = ?
          AND archived = 0";

    if ($role === 'inspector') {
        $branch = trim((string) ($_SESSION['Branch'] ?? ''));
        if ($branch === '') {
            return false;
        }
        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) LIMIT 1";
    } elseif ($requestedBranch !== '' && strcasecmp($requestedBranch, 'all') !== 0) {
        // Admins may scan across branches, or narrow the lookup to one selected branch.
        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) LIMIT 1";
    } else {
        // A duplicate code is ambiguous for an Admin unless a branch was selected.
        $sql .= " LIMIT 2";
    }

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    if ($role === 'inspector') {
        mysqli_stmt_bind_param($stmt, "ss", $code, $branch);
    } elseif ($requestedBranch !== '' && strcasecmp($requestedBranch, 'all') !== 0) {
        mysqli_stmt_bind_param($stmt, "ss", $code, $requestedBranch);
    } else {
        mysqli_stmt_bind_param($stmt, "s", $code);
    }

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return false;
    }
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        mysqli_stmt_close($stmt);
        return false;
    }

    $row = mysqli_fetch_assoc($result);
    if ($role === 'admin' && ($requestedBranch === '' || strcasecmp($requestedBranch, 'all') === 0)) {
        $secondRow = mysqli_fetch_assoc($result);
        if ($secondRow) {
            mysqli_stmt_close($stmt);
            return array('ambiguous' => true);
        }
    }

    mysqli_stmt_close($stmt);
    return $row;
}
