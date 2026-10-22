<?php
require_once __DIR__ . '/../config/db.php';


function getAllBranchDropdown()
{
    global $conn;

    $sql = "SELECT * FROM maintenance_dropdown_tbl 
    WHERE category = 'branch'
    AND status = 'active'
    ORDER BY value ASC

    ";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return [];
    }

    $branches = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $branches[] = $row;
    }

    return $branches;
}


function getAllPlacementDropdown()
{
    global $conn;

    $sql = "
        SELECT value
        FROM maintenance_dropdown_tbl
        WHERE category = 'placement'
        AND status = 'active'
        ORDER BY value ASC
    ";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return [];
    }

    $placements = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $placements[] = $row;
    }

    return $placements;
}

function getAllCapacityDropdown()
{
    global $conn;

    $sql = "
        SELECT value
        FROM maintenance_dropdown_tbl
        WHERE category = 'capacity'
        AND status = 'active'
        ORDER BY value ASC
    ";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return [];
    }

    $capacities = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $capacities[] = $row;
    }

    return $capacities;
}



function getAllTypeDropdown()
{
    global $conn;

    $sql = "
        SELECT value
        FROM maintenance_dropdown_tbl
        WHERE category = 'type'
          AND status = 'active'
        ORDER BY value ASC
    ";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return [];
    }

    $types = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $types[] = $row;
    }

    return $types;
}

function getAllClassDropdown()
{
    global $conn;

    $sql = "
        SELECT value
        FROM maintenance_dropdown_tbl
        WHERE category = 'fire_class'
        AND status = 'active'
        ORDER BY value ASC
    ";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return [];
    }

    $capacities = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $capacities[] = $row;
    }

    return $capacities;
}
