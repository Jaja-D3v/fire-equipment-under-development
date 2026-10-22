<?php

require_once __DIR__ . '/../config/db.php';

// INSPECTION - LAST 3 MONTHS
function getInspectionLastThreeMonths($branch)
{
    global $conn;

    $sql = "
        SELECT
            DATE_FORMAT(i.date_inspected, '%Y-%m') AS inspection_month,
            COUNT(*) AS total_inspected
        FROM inspection_checklist_tbl i
        WHERE i.date_inspected >= DATE_FORMAT(
            DATE_SUB(CURDATE(), INTERVAL 2 MONTH),
            '%Y-%m-01'
        )
        AND i.date_inspected < DATE_ADD(
            LAST_DAY(CURDATE()),
            INTERVAL 1 DAY
        )
    ";

    if ($branch !== 'all') {
        $sql .= " AND LOWER(TRIM(i.branch)) = LOWER(TRIM(?)) ";
    }

    $sql .= "
        GROUP BY DATE_FORMAT(i.date_inspected, '%Y-%m')
        ORDER BY inspection_month ASC
    ";

    $stmt = $conn->prepare($sql);

    if ($branch !== 'all') {
        $stmt->bind_param("s", $branch);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();

    return $data;
}


// NEARLY EXPIRING FIRE EXTINGUISHERS
function getExpiringFireExtinguishers($branch)
{
    global $conn;

    $sql = "
        SELECT COUNT(*) AS total
        FROM fire_extinguishers_tbl
        WHERE archived = 0
          AND expiration_date >= CURDATE()
          AND expiration_date <= DATE_ADD(
              CURDATE(),
              INTERVAL 2 MONTH
          )
    ";

    /*
    | Only filter branch if NOT admin/all
    */

    if ($branch !== 'all') {
        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return 0;
    }

    if ($branch !== 'all') {
        $stmt->bind_param("s", $branch);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return (int) ($row['total'] ?? 0);
}


// ALL NOT GOOD CONDITION
function getAllNotGoodCondition($branch)
{
    global $conn;

    $sql = "
        SELECT COUNT(*) AS total
        FROM fire_extinguishers_tbl
        WHERE condition_status = 'No Good'
        AND archived = 0
    ";

    if ($branch !== 'all') {
        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }

    $stmt = $conn->prepare($sql);

    if ($branch !== 'all') {
        $stmt->bind_param("s", $branch);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return (int) ($row['total'] ?? 0);
}

// FIRE EXTINGUISHER TYPE COUNTS
function getFireExtinguisherTypeCounts($branch)
{
    global $conn;

    $sql = "
        SELECT
            f.type,
            COUNT(*) AS total
        FROM fire_extinguishers_tbl f
        WHERE f.type IS NOT NULL
          AND TRIM(f.type) != ''
          AND f.archived = 0
    ";

    if ($branch !== 'all') {
        $sql .= " AND LOWER(TRIM(f.branch)) = LOWER(TRIM(?)) ";
    }

    $sql .= "
        GROUP BY f.type
        ORDER BY total DESC
    ";

    $stmt = $conn->prepare($sql);

    if ($branch !== 'all') {
        $stmt->bind_param("s", $branch);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();

    return $data;
}

// ALL GOOD CONDITION
function getAllGoodCondition($branch)
{
    global $conn;

    $sql = "
        SELECT COUNT(*) AS total
        FROM fire_extinguishers_tbl
        WHERE condition_status = 'Good'
         AND archived = 0
    ";

    if ($branch !== 'all') {
        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }

    $stmt = $conn->prepare($sql);

    if ($branch !== 'all') {
        $stmt->bind_param("s", $branch);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return (int) ($row['total'] ?? 0);
}

// INSTALLED - NOT GOOD
function getNotGoodInstalledFireExtinguishersCount($branch)
{
    global $conn;

    $sql = "
        SELECT COUNT(*) AS total
        FROM fire_extinguishers_tbl
        WHERE placement != 'Storage'
          AND condition_status = 'No Good'
          AND archived = 0

    ";

    if ($branch !== 'all') {
        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }

    $stmt = $conn->prepare($sql);

    if ($branch !== 'all') {
        $stmt->bind_param("s", $branch);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return (int) ($row['total'] ?? 0);
}

// INSTALLED - GOOD
function getGoodInstalledFireExtinguishersCount($branch)
{
    global $conn;

    $sql = "
        SELECT COUNT(*) AS total
        FROM fire_extinguishers_tbl
        WHERE placement != 'Storage'
          AND condition_status = 'Good'
          AND archived = 0
    ";

    if ($branch !== 'all') {
        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }

    $stmt = $conn->prepare($sql);

    if ($branch !== 'all') {
        $stmt->bind_param("s", $branch);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return (int) ($row['total'] ?? 0);
}

// ALL INSTALLED FIRE EXTINGUISHERS
function getInstalledFireExtinguishersCount($branch)
{
    global $conn;

    $sql = "
        SELECT COUNT(*) AS total
        FROM fire_extinguishers_tbl
        WHERE placement != 'Storage'
        AND archived = 0
    ";

    if ($branch !== 'all') {
        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }

    $stmt = $conn->prepare($sql);

    if ($branch !== 'all') {
        $stmt->bind_param("s", $branch);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return (int) ($row['total'] ?? 0);
}


// SPARE - NOT GOOD
function getNotGoodSpareFireExtinguishersCount($branch)
{
    global $conn;

    $sql = "
        SELECT COUNT(*) AS total
        FROM fire_extinguishers_tbl
        WHERE placement = 'Storage'
          AND condition_status = 'No Good'
          AND archived = 0
    ";

    if ($branch !== 'all') {
        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }

    $stmt = $conn->prepare($sql);

    if ($branch !== 'all') {
        $stmt->bind_param("s", $branch);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return (int) ($row['total'] ?? 0);
}

// SPARE - GOOD
function getGoodSpareFireExtinguishersCount($branch)
{
    global $conn;

    $sql = "
        SELECT COUNT(*) AS total
        FROM fire_extinguishers_tbl
        WHERE placement = 'Storage'
          AND condition_status = 'Good'
          AND archived = 0
          
    ";

    if ($branch !== 'all') {
        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }

    $stmt = $conn->prepare($sql);

    if ($branch !== 'all') {
        $stmt->bind_param("s", $branch);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return (int) ($row['total'] ?? 0);
}

// ALL SPARE FIRE EXTINGUISHERS
function getSpareFireExtinguishersCount($branch)
{
    global $conn;

    $sql = "
        SELECT COUNT(*) AS total
        FROM fire_extinguishers_tbl
        WHERE placement = 'Storage'
          AND archived = 0
    ";

    if ($branch !== 'all') {
        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }

    $stmt = $conn->prepare($sql);

    if ($branch !== 'all') {
        $stmt->bind_param("s", $branch);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return (int) ($row['total'] ?? 0);
}


// ALL ACTIVE REGISTERED FIRE EXTINGUISHERS
function getAllFireExtinguishersCount($branch)
{
    global $conn;

    $sql = "
        SELECT COUNT(*) AS total
        FROM fire_extinguishers_tbl
        WHERE archived = 0
    ";

    if ($branch !== 'all') {
        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }

    $stmt = $conn->prepare($sql);

    if ($branch !== 'all') {
        $stmt->bind_param("s", $branch);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return (int) ($row['total'] ?? 0);
}
