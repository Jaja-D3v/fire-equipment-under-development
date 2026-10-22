<?php

require_once __DIR__ . '/../config/db.php';

function getCurrentBranch()
{
    return $_SESSION['Branch'] ?? null;
}


// =====================================================
// GET ALL FIRE EXTINGUISHERS
// =====================================================
function getAll(
    $limit,
    $offset,
    $placementType = 'all',
    $conditionType = 'all',
    $search = ''
) {
    global $conn;

    $role = strtolower(
        trim($_SESSION['Role'] ?? '')
    );


    // ========================================================
    // ADMIN
    // ========================================================

    if ($role === 'admin') {

        $branch = trim(
            $_GET['branch'] ?? $_SESSION['Branch']
        );

        $sql = "
            SELECT
                extinguisher_id,
                extinguisher_code,
                type,
                capacity,
                location,
                manufactured_date,
                class,
                placement,
                condition_status,
                remarks,
                expiration_date,
                created_at,
                updated_at,
                refilled_date,
                branch,
                last_date_inspected
            FROM fire_extinguishers_tbl
            WHERE archived = 0
        ";

        $params = [];
        $types = '';


        // ====================================================
        // BRANCH FILTER
        // ====================================================

        if ($branch !== 'all' && $branch !== '') {

            $sql .= "
                AND LOWER(TRIM(branch))
                    = LOWER(TRIM(?))
            ";

            $params[] = $branch;
            $types .= 's';
        }



        // PLACEMENT TYPE FILTER
        //
        // Storage = Spare
        // Anything else = Installed

        if ($placementType === 'spare') {

            $sql .= " AND LOWER(TRIM(placement)) = 'storage' ";
        } elseif ($placementType === 'installed') {

            $sql .= " AND LOWER(TRIM(placement)) <> 'storage' ";
        }


        // CONDITION FILTER

        $conditionValue = strtolower(trim((string) $conditionType));
        if ($conditionValue === 'good') {
            $sql .= " AND LOWER(TRIM(condition_status)) = LOWER(TRIM(?)) ";
            $params[] = 'Good';
            $types .= 's';
        } elseif ($conditionValue === 'no good') {
            $sql .= " AND LOWER(TRIM(condition_status)) IN (LOWER(TRIM(?)), LOWER(TRIM(?))) ";
            $params[] = 'No Good';
            $params[] = 'Not Good';
            $types .= 'ss';
        }


        // SEARCH

        if ($search !== '') {

            $sql .= "
                AND (
                    LOWER(TRIM(extinguisher_code))
                        LIKE LOWER(TRIM(?))
                    OR LOWER(TRIM(location))
                        LIKE LOWER(TRIM(?))
                )
            ";

            $searchValue = '%' . $search . '%';

            $params[] = $searchValue;
            $params[] = $searchValue;

            $types .= 'ss';
        }


        // PAGINATION

        $sql .= "
            ORDER BY extinguisher_id ASC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        $types .= 'ii';


        $stmt = mysqli_prepare(
            $conn,
            $sql
        );

        if (!$stmt) {
            return false;
        }


        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );

        mysqli_stmt_execute($stmt);

        return mysqli_stmt_get_result($stmt);
    }


    // INSPECTOR
    if ($role === 'inspector') {

        $branch = trim(
            $_SESSION['Branch'] ?? ''
        );

        if ($branch === '') {
            return false;
        }


        $sql = "
            SELECT
                extinguisher_id,
                extinguisher_code,
                type,
                capacity,
                location,
                manufactured_date,
                class,
                placement,
                condition_status,
                remarks,
                expiration_date,
                created_at,
                updated_at,
                refilled_date,
                branch
            FROM fire_extinguishers_tbl
            WHERE archived = 0
              AND LOWER(TRIM(branch))
                    = LOWER(TRIM(?))
        ";

        $params = [];
        $types = 's';

        $params[] = $branch;

        $conditionValue = strtolower(trim((string) $conditionType));
        if ($conditionValue === 'good') {
            $sql .= " AND LOWER(TRIM(condition_status)) = LOWER(TRIM(?)) ";
            $params[] = 'Good';
            $types .= 's';
        } elseif ($conditionValue === 'no good') {
            $sql .= " AND LOWER(TRIM(condition_status)) IN (LOWER(TRIM(?)), LOWER(TRIM(?))) ";
            $params[] = 'No Good';
            $params[] = 'Not Good';
            $types .= 'ss';
        }


        // PLACEMENT TYPE FILTER
        if ($placementType === 'spare') {

            $sql .= "
                AND LOWER(TRIM(placement)) = 'storage'
            ";
        } elseif ($placementType === 'installed') {

            $sql .= "
                AND LOWER(TRIM(placement)) <> 'storage'
            ";
        }


        // SEARCH
        if ($search !== '') {

            $sql .= "
                AND (
                    LOWER(TRIM(extinguisher_code))
                        LIKE LOWER(TRIM(?))
                    OR LOWER(TRIM(location))
                        LIKE LOWER(TRIM(?))
                )
            ";

            $searchValue = '%' . $search . '%';

            $params[] = $searchValue;
            $params[] = $searchValue;

            $types .= 'ss';
        }


        $sql .= "
            ORDER BY extinguisher_id DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        $types .= 'ii';


        $stmt = mysqli_prepare(
            $conn,
            $sql
        );

        if (!$stmt) {
            return false;
        }


        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );

        mysqli_stmt_execute($stmt);

        return mysqli_stmt_get_result($stmt);
    }


    return false;
}
// GET FIRE EXTINGUISHER BY ID
function getById($id)
{
    global $conn;

    $role = strtolower(trim($_SESSION['Role'] ?? ''));

    $sql = "SELECT
                extinguisher_id,
                extinguisher_code,
                type,
                capacity,
                location,
                manufactured_date,
                class,
                placement,
                condition_status,
                remarks,
                expiration_date,
                created_at,
                updated_at,
                refilled_date,
                branch,
                provider
            FROM fire_extinguishers_tbl
            WHERE extinguisher_id = ?
              AND archived = 0";


    /*
    | INSPECTOR
    | Inspector can only view own branch.
    */

    if ($role === 'inspector') {

        $branch = getCurrentBranch();

        if (empty($branch)) {
            return false;
        }

        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }


    /*
    | ADMIN
    | Admin can view any branch.
    */ elseif ($role === 'admin') {

        // No branch restriction.
    }

    /*
    | INVALID ROLE
    */ else {

        return false;
    }

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    if ($role === 'inspector') {

        mysqli_stmt_bind_param(
            $stmt,
            "is",
            $id,
            $branch
        );
    } else {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id
        );
    }


    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}


// GET FIRE EXTINGUISHER BY CODE
function getByCode($code, $branch)
{
    global $conn;

    $sql = "SELECT
                extinguisher_code,
                location,
                type,
                capacity,
                class,
                branch,
                archived
            FROM fire_extinguishers_tbl
            WHERE extinguisher_code = ?
              AND branch = ?
            LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $code,
        $branch
    );

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// ADD NEW FIRE EXTINGUISHER
function addNewFireExtinguisherModel(
    $code,
    $type,
    $capacity,
    $location,
    $manufactured_date,
    $class,
    $placement,
    $condition_status,
    $remarks,
    $expiration_date,
    $added_by,
    $branch,
    $provider
) {
    global $conn;


    $sql = "INSERT INTO fire_extinguishers_tbl
            (
                extinguisher_code,
                type,
                capacity,
                location,
                manufactured_date,
                class,
                placement,
                condition_status,
                remarks,
                expiration_date,
                branch,
                added_by,
                provider
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sssssssssssss",
        $code,
        $type,
        $capacity,
        $location,
        $manufactured_date,
        $class,
        $placement,
        $condition_status,
        $remarks,
        $expiration_date,
        $branch,
        $added_by,
        $provider
    );

    $result = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $result;
}




// UPDATE FIRE EXTINGUISHER
function updateFireExtinguisherModel(
    $id,
    $code,
    $type,
    $capacity,
    $location,
    $manufactured_date,
    $class,
    $placement,
    $condition_status,
    $remarks,
    $expiration_date,
    $provider
) {
    global $conn;

    $role = strtolower(trim($_SESSION['Role'] ?? ''));
    $branch = getCurrentBranch();

    // Validate inspector branch
    if ($role === 'inspector' && empty($branch)) {
        return false;
    }

    // Allow only admin and inspector
    if ($role !== 'admin' && $role !== 'inspector') {
        return false;
    }

    // Update query
    $sql = "UPDATE fire_extinguishers_tbl
            SET
                extinguisher_code = ?,
                type = ?,
                capacity = ?,
                location = ?,
                manufactured_date = ?,
                class = ?,
                placement = ?,
                condition_status = ?,
                remarks = ?,
                expiration_date = ?,
                provider = ?
            WHERE extinguisher_id = ?
              AND archived = 0";

    // Restrict inspectors to their assigned branch
    if ($role === 'inspector') {
        $sql .= "
            AND LOWER(TRIM(branch)) = LOWER(TRIM(?))
        ";
    }

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($conn));
        return false;
    }

    if ($role === 'inspector') {
        mysqli_stmt_bind_param(
            $stmt,
            "sssssssssss is",
            $code,
            $type,
            $capacity,
            $location,
            $manufactured_date,
            $class,
            $placement,
            $condition_status,
            $remarks,
            $expiration_date,
            $provider,
            $id,
            $branch
        );
    } else {
        mysqli_stmt_bind_param(
            $stmt,
            "sssssssssssi",
            $code,
            $type,
            $capacity,
            $location,
            $manufactured_date,
            $class,
            $placement,
            $condition_status,
            $remarks,
            $expiration_date,
            $provider,
            $id
        );
    }

    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        error_log("Update failed: " . mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);

    return $result;
}



// SOFT DELETE / ARCHIVE
function deleteFireExtinguisherById($id)
{
    global $conn;

    $role = strtolower(trim($_SESSION['Role'] ?? ''));
    $branch = getCurrentBranch();

    if ($role === 'inspector' && empty($branch)) {
        return false;
    }

    if ($role !== 'admin' && $role !== 'inspector') {
        return false;
    }

    $sql = "UPDATE fire_extinguishers_tbl
            SET archived = 1
            WHERE extinguisher_id = ?";

    if ($role === 'inspector') {

        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    if ($role === 'inspector') {

        mysqli_stmt_bind_param($stmt, "is", $id, $branch);
    } else {

        mysqli_stmt_bind_param($stmt, "i", $id);
    }

    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}


// GET NEXT FIRE EXTINGUISHER CODE
function getNextFireExtinguisherCodeModel($branch)
{
    global $conn;

    if (empty($branch)) {
        return 'FE-001';
    }

    $sql = "SELECT extinguisher_code
            FROM fire_extinguishers_tbl
            WHERE extinguisher_code LIKE 'FE-%'
              AND LOWER(TRIM(branch)) = LOWER(TRIM(?))
            ORDER BY CAST(
                SUBSTRING(extinguisher_code, 4)
                AS UNSIGNED
            ) DESC
            LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return 'FE-001';
    }

    mysqli_stmt_bind_param($stmt, "s", $branch);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (!$row) {
        return 'FE-001';
    }

    $lastNumber = (int) substr(
        $row['extinguisher_code'],
        3
    );

    return 'FE-' . str_pad(
        $lastNumber + 1,
        3,
        '0',
        STR_PAD_LEFT
    );
}

// UPDATE REFILLED DATE
function update_refilled($ext_code)
{
    global $conn;

    $branch = getCurrentBranch();

    if (empty($branch)) {
        return false;
    }

    $sql = "UPDATE fire_extinguishers_tbl
            SET
                refilled_date = CURDATE(),
                expiration_date =
                    DATE_ADD(
                        CURDATE(),
                        INTERVAL 3 YEAR
                    )
            WHERE extinguisher_code = ?
              AND LOWER(TRIM(branch)) =
                  LOWER(TRIM(?))";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $ext_code,
        $branch
    );

    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}


// UPDATE REMARKS
function update_remarks($remarks, $ext_code)
{
    global $conn;

    $branch = getCurrentBranch();

    if (empty($branch)) {
        return false;
    }

    $sql = "UPDATE fire_extinguishers_tbl
            SET remarks = ?
            WHERE extinguisher_code = ?
              AND LOWER(TRIM(branch)) =
                  LOWER(TRIM(?))";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $remarks,
        $ext_code,
        $branch
    );

    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}


// UPDATE FIRE EXTINGUISHER STATUS
function update_status($status, $ext_code)
{
    global $conn;

    $branch = getCurrentBranch();

    if (empty($branch)) {
        return false;
    }

    $sql = "UPDATE fire_extinguishers_tbl
            SET condition_status = ?
            WHERE extinguisher_code = ?
              AND LOWER(TRIM(branch)) =
                  LOWER(TRIM(?))";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $status,
        $ext_code,
        $branch
    );

    $result = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $result;
}

// GET TOTAL FIRE EXTINGUISHERS
function getTotalFireExtinguishersModel(
    $placementType = 'all',
    $conditionType = 'all',
    $search = ''
) {
    global $conn;

    $role = strtolower(trim($_SESSION['Role'] ?? ''));

    // DETERMINE BRANCH BASED ON ROLE
    if ($role === 'admin') {

        $branch = trim(
            $_GET['branch'] ?? $_SESSION['Branch']
        );
    } elseif ($role === 'inspector') {

        $branch = trim(
            $_SESSION['Branch'] ?? ''
        );
    } else {

        return 0;
    }

    // BASE QUERY
    $sql = "
        SELECT COUNT(*) AS total
        FROM fire_extinguishers_tbl
        WHERE archived = 0
    ";

    $params = [];
    $types = '';

    // BRANCH FILTER
    if ($branch !== 'all' && $branch !== '') {

        $sql .= "
            AND LOWER(TRIM(branch))
                = LOWER(TRIM(?))
        ";

        $params[] = $branch;
        $types .= 's';
    }


    // PLACEMENT TYPE FILTER
    //
    // Storage = Spare
    // Anything else = Installed

    if ($placementType === 'spare') {

        $sql .= " AND LOWER(TRIM(placement)) = 'storage' ";
    } elseif ($placementType === 'installed') {

        $sql .= " AND LOWER(TRIM(placement)) <> 'storage' ";
    }


    // CONDITION FILTER
    $conditionValue = strtolower(trim((string) $conditionType));
    if ($conditionValue === 'good') {
        $sql .= " AND LOWER(TRIM(condition_status)) = LOWER(TRIM(?)) ";
        $params[] = 'Good';
        $types .= 's';
    } elseif ($conditionValue === 'no good') {
        $sql .= " AND LOWER(TRIM(condition_status)) IN (LOWER(TRIM(?)), LOWER(TRIM(?))) ";
        $params[] = 'No Good';
        $params[] = 'Not Good';
        $types .= 'ss';
    }

    // SEARCH FILTER FOR TOTAL COUNT
    $search = trim($search);

    if ($search !== '') {

        $sql .= "
            AND (
                LOWER(TRIM(extinguisher_code))
                    LIKE LOWER(TRIM(?))
                OR LOWER(TRIM(location))
                    LIKE LOWER(TRIM(?))
            )
        ";

        $searchValue = '%' . $search . '%';

        $params[] = $searchValue;
        $params[] = $searchValue;

        $types .= 'ss';
    }

    // PREPARE
    $stmt = mysqli_prepare(
        $conn,
        $sql
    );

    if (!$stmt) {
        return 0;
    }

    // BIND PARAMETERS
    if (!empty($params)) {

        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );
    }

    // EXECUTE
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return (int) (
        $row['total'] ?? 0
    );
}

// GET ALL DELETED FIRE EXTINGUISHERS
function getAllDeletedFireExtinguishers($limit, $offset)
{
    global $conn;

    $role = strtolower(trim($_SESSION['Role'] ?? ''));

    // ADMIN = use selected branch from URL
    // INSPECTOR = use assigned branch from session
    if ($role === 'admin') {

        // Selected branch from dropdown
        if (
            isset($_GET['branch']) &&
            trim($_GET['branch']) !== ''
        ) {
            $branch = trim($_GET['branch']);
        } else {
            // Default = Admin's assigned branch
            $branch = trim($_SESSION['Branch'] ?? '');
        }

        // If no branch is assigned, show all
        if ($branch === '') {
            $branch = 'all';
        }
    } else {

        // Inspector = assigned branch only
        $branch = trim($_SESSION['Branch'] ?? '');
    }

    $sql = "SELECT *
            FROM fire_extinguishers_tbl
            WHERE archived = 1";

    $params = [];
    $types = '';

    // Apply branch filter
    if ($branch !== 'all' && !empty($branch)) {

        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";

        $params[] = $branch;
        $types .= 's';
    }

    $sql .= "
        ORDER BY extinguisher_id DESC
        LIMIT ? OFFSET ?
    ";

    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return [];
    }

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $data = mysqli_fetch_all(
        $result,
        MYSQLI_ASSOC
    );

    mysqli_stmt_close($stmt);

    return $data;
}

// GET TOTAL DELETED FIRE EXTINGUISHERS
function getAllDeletedFireExtinguishersModel()
{
    global $conn;

    $role = strtolower(trim($_SESSION['Role'] ?? ''));

    if ($role === 'admin') {
        $branch = trim($_GET['branch'] ?? $_SESSION['Branch'] ?? '');

        if ($branch === '') {
            $branch = 'all';
        }
    } else {
        $branch = trim($_SESSION['Branch'] ?? '');
    }

    $sql = "SELECT COUNT(*) AS total
            FROM fire_extinguishers_tbl
            WHERE archived = 1";

    $params = [];
    $types = '';

    if ($branch !== 'all' && $branch !== '') {
        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?))";
        $params[] = $branch;
        $types .= 's';
    }

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return 0;
    }

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    return (int) ($row['total'] ?? 0);
}


// fet all archived fire extinguisher
function getDeletedFireExtinguisherById($id)
{
    global $conn;

    $sql = "
        SELECT *
        FROM fire_extinguishers_tbl
        WHERE extinguisher_id = ?
          AND archived = 1
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $data;
}

//  for restoration of fire extinguisher
function restoreFireExtinguisher($id)
{
    global $conn;

    $sql = "
        UPDATE fire_extinguishers_tbl
        SET archived = 0
        WHERE extinguisher_id = ?
          AND archived = 1
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $success;
}


function getExpiry(
    $limit = 10,
    $offset = 0,
    $search = '',
    $condition = 'all'
) {
    global $conn;

    $limit = (int) $limit;
    $offset = (int) $offset;

    if ($limit < 1) {
        $limit = 10;
    }

    if ($offset < 0) {
        $offset = 0;
    }

    $search = trim($search);
    $condition = strtolower(trim($condition));

    $role = strtolower(trim($_SESSION['Role'] ?? ''));
    $sessionBranch = trim($_SESSION['Branch'] ?? '');

    /**
     * |--------------------------------------------------------------------------
     * | BRANCH FILTER
     * |--------------------------------------------------------------------------
     * | Admin:
     * |   - Uses ?branch=...
     * |   - If no branch is selected, defaults to session branch
     * |
     * | Inspector:
     * |   - Always use session branch
     * |--------------------------------------------------------------------------
     */

    if ($role === 'admin') {

        $selectedBranch = trim(
            $_GET['branch'] ?? $sessionBranch
        );
    } else {

        $selectedBranch = $sessionBranch;
    }

    if ($selectedBranch === '') {
        $selectedBranch = 'all';
    }

    /**
     * GET DATA
     */

    $sql = "
        SELECT *
        FROM fire_extinguishers_tbl
        WHERE archived = 0
          AND expiration_date >= CURDATE()
          AND expiration_date <= DATE_ADD(CURDATE(), INTERVAL 2 MONTH)
    ";

    /*
     * SEARCH FILTER
     */
    if ($search !== '') {

        $sql .= "
            AND (
                extinguisher_code LIKE ?
                OR type LIKE ?
                OR capacity LIKE ?
                OR class LIKE ?
                OR location LIKE ?
                OR branch LIKE ?
            )
        ";
    }

    /*
     * CONDITION FILTER
     */
    if ($condition !== 'all') {

        if ($condition === 'good') {

            $sql .= " AND LOWER(TRIM(condition_status)) = 'good' ";
        } elseif ($condition === 'not-good') {

            $sql .= " AND LOWER(TRIM(condition_status)) <> 'good' ";
        }
    }

    /*
     * BRANCH FILTER
     */
    if (strtolower($selectedBranch) !== 'all') {

        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }

    $sql .= "
        ORDER BY expiration_date ASC
        LIMIT ? OFFSET ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return [
            'data' => [],
            'total' => 0
        ];
    }

    /*
     * BIND PARAMETERS
     */
    if ($search !== '') {

        $searchValue = '%' . $search . '%';

        if (strtolower($selectedBranch) !== 'all') {

            mysqli_stmt_bind_param(
                $stmt,
                "sssssssii",
                $searchValue,
                $searchValue,
                $searchValue,
                $searchValue,
                $searchValue,
                $searchValue,
                $selectedBranch,
                $limit,
                $offset
            );
        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "ssssssii",
                $searchValue,
                $searchValue,
                $searchValue,
                $searchValue,
                $searchValue,
                $searchValue,
                $limit,
                $offset
            );
        }
    } else {

        if (strtolower($selectedBranch) !== 'all') {

            mysqli_stmt_bind_param(
                $stmt,
                "sii",
                $selectedBranch,
                $limit,
                $offset
            );
        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $limit,
                $offset
            );
        }
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $data = [];

    if ($result) {

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }

    mysqli_stmt_close($stmt);

    /**
     * | GET TOTAL RECORDS
     */

    $totalSql = "
        SELECT COUNT(*) AS total
        FROM fire_extinguishers_tbl
        WHERE archived = 0
          AND expiration_date >= CURDATE()
          AND expiration_date <= DATE_ADD(CURDATE(), INTERVAL 2 MONTH)
    ";

    /*
     * SEARCH FILTER
     */
    if ($search !== '') {

        $totalSql .= "
            AND (
                extinguisher_code LIKE ?
                OR type LIKE ?
                OR capacity LIKE ?
                OR class LIKE ?
                OR location LIKE ?
                OR branch LIKE ?
            )
        ";
    }

    /*
     * CONDITION FILTER
     */
    if ($condition !== 'all') {

        if ($condition === 'good') {

            $totalSql .= " AND LOWER(TRIM(condition_status)) = 'good' ";
        } elseif ($condition === 'not-good') {

            $totalSql .= " AND LOWER(TRIM(condition_status)) <> 'good' ";
        }
    }

    /*
     * BRANCH FILTER
     */
    if (strtolower($selectedBranch) !== 'all') {

        $totalSql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }

    $totalStmt = mysqli_prepare($conn, $totalSql);
    $total = 0;

    if ($totalStmt) {

        /*
         * BIND TOTAL PARAMETERS
         */
        if ($search !== '') {

            $searchValue = '%' . $search . '%';

            if (strtolower($selectedBranch) !== 'all') {

                mysqli_stmt_bind_param(
                    $totalStmt,
                    "sssssss",
                    $searchValue,
                    $searchValue,
                    $searchValue,
                    $searchValue,
                    $searchValue,
                    $searchValue,
                    $selectedBranch
                );
            } else {

                mysqli_stmt_bind_param(
                    $totalStmt,
                    "ssssss",
                    $searchValue,
                    $searchValue,
                    $searchValue,
                    $searchValue,
                    $searchValue,
                    $searchValue
                );
            }
        } else {

            if (strtolower($selectedBranch) !== 'all') {

                mysqli_stmt_bind_param(
                    $totalStmt,
                    "s",
                    $selectedBranch
                );
            }
        }

        mysqli_stmt_execute($totalStmt);

        $totalResult = mysqli_stmt_get_result($totalStmt);

        if ($totalResult) {

            $totalRow = mysqli_fetch_assoc($totalResult);
            $total = (int) ($totalRow['total'] ?? 0);
        }

        mysqli_stmt_close($totalStmt);
    }

    return [
        'data' => $data,
        'total' => $total
    ];
}
