<?php
require_once __DIR__ . '/../config/db.php';

function getExtinguisher($branch = 'all')
{
    global $conn;

    $sql = "
        SELECT
            extinguisher_id,
            extinguisher_code,
            type,
            location,
            branch
        FROM fire_extinguishers_tbl
        WHERE archived = 0
    ";

    if ($branch !== 'all') {

        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }

    $sql .= " ORDER BY extinguisher_code ASC";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    if ($branch !== 'all') {
        $stmt->bind_param("s", $branch);
    }

    $stmt->execute();
    return $stmt->get_result();
}

function getBranches()
{
    global $conn;

    $sql = "
        SELECT DISTINCT branch
        FROM fire_extinguishers_tbl
        WHERE archived = 0
          AND branch IS NOT NULL
          AND TRIM(branch) != ''
        ORDER BY branch ASC
    ";

    return $conn->query($sql);
}

function getFireExtinguisherDetailsByCode($code, $branch = null)
{
    global $conn;

    $sql = "
        SELECT
            extinguisher_code,
            location,
            class,
            type,
            branch
        FROM fire_extinguishers_tbl
        WHERE extinguisher_code = ?
          AND archived = 0
    ";

    if ($branch !== null && $branch !== 'all') {
        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }
    $sql .= " ORDER BY extinguisher_id ASC LIMIT 1";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    if ($branch !== null && $branch !== 'all') {
        $stmt->bind_param("ss", $code, $branch);
    } else {
        $stmt->bind_param("s", $code);
    }
    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $stmt->close();

    return $data ?: false;
}
