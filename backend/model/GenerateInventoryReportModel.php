<?php

require_once __DIR__ . '/../config/db.php';

function getInventoryReportIds($branch = 'all')
{
    global $conn;

    $sql = "SELECT extinguisher_id
            FROM fire_extinguishers_tbl
            WHERE archived = 0";

    if ($branch !== 'all') {
        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?))";
    }

    $sql .= " ORDER BY extinguisher_id ASC";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return [];
    }

    if ($branch !== 'all') {
        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $branch
        );
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $ids = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $ids[] = (int) $row['extinguisher_id'];
    }

    mysqli_stmt_close($stmt);

    return $ids;
}
