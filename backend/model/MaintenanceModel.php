<?php
require_once __DIR__ . '/../config/db.php';

/*
    |--------------------------------------------------------------------------
    | GET PAGINATED DATA
    |--------------------------------------------------------------------------
    */

function getData($category, $page = 1, $limit = 5, $search = '')
{
    global $conn;
    $offset = ($page - 1) * $limit;

    $sql = "
            SELECT id, category, value, status
            FROM maintenance_dropdown_tbl
            WHERE category = ?
            AND status = 'active'
        ";

    $params = [$category];
    $types = "s";

    if (!empty($search)) {

        $sql .= " AND value LIKE ?";

        $params[] = "%" . $search . "%";
        $types .= "s";
    }

    $sql .= "
            ORDER BY value ASC
            LIMIT ? OFFSET ?
        ";

    $params[] = $limit;
    $params[] = $offset;

    $types .= "ii";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        $types,
        ...$params
    );

    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}


/*
    |--------------------------------------------------------------------------
    | GET TOTAL
    |--------------------------------------------------------------------------
    */

function getTotal($category, $search = '')
{
    global $conn;
    $sql = " SELECT COUNT(*) AS total
            FROM maintenance_dropdown_tbl
            WHERE category = ?
            AND status = 'active'
        ";

    $params = [$category];
    $types = "s";

    if (!empty($search)) {

        $sql .= " AND value LIKE ?";

        $params[] = "%" . $search . "%";
        $types .= "s";
    }

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        $types,
        ...$params
    );

    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_assoc()['total'];
}


/*
    |--------------------------------------------------------------------------
    | GET SINGLE RECORD
    |--------------------------------------------------------------------------
    */

function getById($id)
{
    global $conn;
    $sql = "
            SELECT *
            FROM maintenance_dropdown_tbl
            WHERE id = ?
            LIMIT 1
        ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id);

    $stmt->execute();

    return $stmt
        ->get_result()
        ->fetch_assoc();
}


/*
    |--------------------------------------------------------------------------
    | ADD
    |--------------------------------------------------------------------------
    */

function add($category, $value)
{
    global $conn;

    $value = trim($value);

    $sql = "
        INSERT INTO maintenance_dropdown_tbl
        (category, value)
        VALUES (?, ?)
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ss",
        $category,
        $value
    );

    return $stmt->execute();
}

function update($id, $value)
{
    global $conn;

    $value = trim($value);

    $sql = "
        UPDATE maintenance_dropdown_tbl
        SET value = ?
        WHERE id = ?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "si",
        $value,
        $id
    );

    return $stmt->execute();
}





/*
    |--------------------------------------------------------------------------
    | DEACTIVATE
    |--------------------------------------------------------------------------
    */


function delete($id)
{
    global $conn;

    $sql = "
        DELETE FROM maintenance_dropdown_tbl
        WHERE id = ?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id);

    return $stmt->execute();
}
