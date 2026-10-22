<?php

require_once __DIR__ . '/../model/FireExtinguisherModel.php';
require_once __DIR__ . '/ActivityLogController.php';
require_once __DIR__ . '/../authentication/SessionChecker.php';


// =====================================================
// Get paginated fire extinguishers
// =====================================================
function getAllFireExtinguishers(
    $limit = 10,
    $offset = 0,
    $placementType = 'all',
    $conditionType = 'all',
    $search = ''
) {
    $role = strtolower(
        trim($_SESSION['Role'] ?? '')
    );

    // ADMIN
    if ($role === 'admin') {

        return getAll(
            $limit,
            $offset,
            $placementType,
            $conditionType,
            $search
        );
    }

    // INSPECTOR
    if ($role === 'inspector') {

        return getAll(
            $limit,
            $offset,
            $placementType,
            $conditionType,
            $search
        );
    }

    return false;
}

// Get fire extinguisher by ID
function getFireExtinguisherById($id)
{
    return getById($id);
}

// Check code / Get next code
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $action = $_GET['action'] ?? '';

    // CHECK FIRE EXTINGUISHER CODE
    if ($action === 'checkCode') {

        header('Content-Type: application/json');

        $code = trim($_GET['code'] ?? '');
        $branch = trim($_GET['branch'] ?? '');

        if ($code === '') {
            echo json_encode([
                'success' => true,
                'exists' => false,
                'deleted' => false
            ]);
            exit;
        }

        $data = getByCode($code, $branch);

        if (!empty($data)) {

            if ((int)$data['archived'] === 1) {

                echo json_encode([
                    'success' => true,
                    'exists' => true,
                    'deleted' => true
                ]);
            } else {

                echo json_encode([
                    'success' => true,
                    'exists' => true,
                    'deleted' => false
                ]);
            }
        } else {

            echo json_encode([
                'success' => true,
                'exists' => false,
                'deleted' => false
            ]);
        }

        exit;
    }

    // GET NEXT FIRE EXTINGUISHER CODE
    if ($action === 'getNextCode') {

        header('Content-Type: application/json');

        $branch = trim($_GET['branch'] ?? '');

        if ($branch === '') {
            echo json_encode([
                'success' => false
            ]);
            exit;
        }

        $code = getNextFireExtinguisherCodeModel($branch);

        echo json_encode([
            'success' => true,
            'code' => $code
        ]);

        exit;
    }

    if ($action === 'getDeleted') {

        header('Content-Type: application/json');

        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {

            echo json_encode([
                'success' => false,
                'message' => 'Invalid fire extinguisher ID.'
            ]);

            exit;
        }

        $data = getDeletedFireExtinguisherById($id);

        if (!$data) {

            echo json_encode([
                'success' => false,
                'message' => 'Deleted fire extinguisher not found.'
            ]);

            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);

        exit;
    }
}


// Controller function for creating/adding
// new fire extinguisher
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ext_code = $_POST['extinguisher_code'] ?? null;
    $ext_type = $_POST['type'] ?? null;
    $ext_capacity = $_POST['capacity'] ?? null;
    $ext_location = $_POST['location'] ?? null;
    $ext_manufactured_date = $_POST['manufactured_date'] ?? null;
    $ext_class = $_POST['class'] ?? null;
    $ext_placement =  $_POST['placement'] ?? null;
    $ext_condition_status = $_POST['condition_status'] ?? null;
    $ext_remarks = $_POST['remarks'] ?? null;
    $ext_expiration_date = $_POST['expiration_date'] ?? null;
    $ext_id = $_POST['extinguisher_id'] ?? null;
    $ext_provider = $_POST['provider'] ?? null;

    /*
    |--------------------------------------------------------------------------
    | FIXED SESSION NAME
    |--------------------------------------------------------------------------
    | Your login uses $_SESSION['Branch']
    | not $_SESSION['branch']
    |--------------------------------------------------------------------------
    */

    $ext_branch =  $_POST['branch'] ?? null;

    $action = $_POST['action'] ?? null;

    $added_by = $_SESSION['EmployeeName'] ?? null;


    // ADD
    if ($action == 'add-new-extinguisher') {

        addNewExtinguisher(
            $ext_code,
            $ext_type,
            $ext_capacity,
            $ext_location,
            $ext_manufactured_date,
            $ext_class,
            $ext_placement,
            $ext_condition_status,
            $ext_remarks,
            $ext_expiration_date,
            $added_by,
            $ext_branch,
            $ext_provider
        );
    }

    // UPDATE
    else if ($action == 'update') {

        updateExtinguisher(
            $ext_id,
            $ext_code,
            $ext_type,
            $ext_capacity,
            $ext_location,
            $ext_manufactured_date,
            $ext_class,
            $ext_placement,
            $ext_condition_status,
            $ext_remarks,
            $ext_expiration_date,
            $ext_provider
        );
    }
}


// UPDATE EXTINGUISHER
function updateExtinguisher(
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

    $success = updateFireExtinguisherModel(
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
    );


    if ($success) {

        $user_name = $_SESSION['EmployeeName'] ?? 'error while getting employee name';

        createActivityLog(
            $user_name,
            "Update Fire Extinguisher",
            "Updated fire extinguisher $code"
        );

        $returnParams = [
            'id' => $id,
            'success-update' => 1
        ];

        foreach (['page', 'search', 'branch', 'condition', 'placement'] as $returnParam) {
            $returnValue = $_POST['return_' . $returnParam] ?? '';
            if ($returnValue !== '') {
                $returnParams[$returnParam] = $returnValue;
            }
        }

        header(
            'Location: ../../list-extinguisher.php?' . http_build_query($returnParams)
        );

        exit;
    }

    return $success;
}


// GET - fetch fire extinguisher for edit modal
// & get by code for fire extinguisher
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $action = $_GET['action'] ?? null;


    // GET BY ID
    if ($action === 'get') {

        $ext_id = $_GET['id'] ?? null;

        header('Content-Type: application/json');

        if (!$ext_id) {

            echo json_encode([
                'success' => false,
                'message' =>
                'Invalid fire extinguisher ID.'
            ]);

            exit;
        }


        $data = getById($ext_id);


        if (!$data) {

            echo json_encode([
                'success' => false,
                'message' =>
                'Fire extinguisher not found.'
            ]);

            exit;
        }


        echo json_encode([
            'success' => true,
            'data' => $data
        ]);

        exit;
    }

    // GET BRANCHES
    else if ($action === 'getBranches') {

        header('Content-Type: application/json');

        $role =
            strtolower(trim($_SESSION['Role'] ?? ''));


        if ($role !== 'admin') {

            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized.'
            ]);

            exit;
        }


        global $conn;


        $sql = "
            SELECT DISTINCT branch
            FROM fire_extinguishers_tbl
            WHERE archived = 0
              AND branch IS NOT NULL
              AND TRIM(branch) != ''
            ORDER BY branch ASC
        ";


        $result = mysqli_query($conn, $sql);
        $branches = [];
        if ($result) {

            while ($row = mysqli_fetch_assoc($result)) {

                $branches[] = $row['branch'];
            }
        }
        echo json_encode([
            'success' => true,
            'data' => $branches
        ]);

        exit;
    }
}

// ADD NEW EXTINGUISHER
function addNewExtinguisher(
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

    $success = addNewFireExtinguisherModel(
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
    );


    if ($success) {

        $user_name =
            $_SESSION['EmployeeName']
            ?? 'error while getting employee name';


        createActivityLog(
            $user_name,
            "Add Fire Extinguisher",
            "Added fire extinguisher $code"
        );


        header(
            "Location: ../../list-extinguisher.php?success-add=1"
        );

        exit;
    }


    return $success;
}

// DELETE FIRE EXTINGUISHER
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $action = $_GET['action'] ?? '';

    if ($action === 'delete') {

        $ext_id = $_GET['id'] ?? null;


        if (!$ext_id) {

            header(
                "Location: ../../list-extinguisher.php?error=invalid_id"
            );

            exit;
        }

        $data = getFireExtinguisherById($ext_id);

        if (!$data) {

            header(
                "Location: ../../list-extinguisher.php?error=not_found"
            );

            exit;
        }

        $code =  $data['extinguisher_code'];
        $success =  deleteFireExtinguisherById($ext_id);


        if ($success) {

            $user_name = $_SESSION['EmployeeName'] ?? 'error while getting employee name';
            createActivityLog( $user_name, "Delete Fire Extinguisher", "Deleted fire extinguisher $code" );
            header( "Location: ../../list-extinguisher.php?success-delete=1" );
            exit;
        }
        header( "Location: ../../list-extinguisher.php?error=delete_failed" );
        exit;
    }


    if ($action === 'getExpiry') {

        header('Content-Type: application/json');

        $limit = isset($_GET['limit'])
            ? (int) $_GET['limit']
            : 10;

        $offset = isset($_GET['offset'])
            ? (int) $_GET['offset']
            : 0;

        if ($limit < 1) {
            $limit = 10;
        }

        if ($offset < 0) {
            $offset = 0;
        }

        $result = getExpiry($limit, $offset);

        echo json_encode([
            'success' => true,
            'data' => $result['data'],
            'total' => $result['total']
        ]);

        exit;
    }
}

// GET NEXT CODE
function getNextFireExtinguisherCode($branch)
{
    return getNextFireExtinguisherCodeModel($branch);
}


// GET TOTAL FIRE EXTINGUISHERS
function getTotalFireExtinguishers(
    $placementType = 'all',
    $conditionType = 'all',
    $search = ''
) {
    return getTotalFireExtinguishersModel(
        $placementType,
        $conditionType,
        $search
    );
}

function getAllDeletedFireExtinguishersController($limit, $offset){
    return getAllDeletedFireExtinguishers($limit, $offset);
}


// function getAllArchiveFireExtinguishers(
//     $limit = 10,
//     $offset = 0
// ) {
//     $role = strtolower(trim($_SESSION['Role'] ?? ''));

//     if ($role === 'admin') {
//         $branch = trim($_GET['branch'] ?? 'all');
//     } else {
//         $branch = trim($_SESSION['Branch'] ?? '');
//     }

//     return getAllDeletedFireExtinguishers(
//         $limit,
//         $offset,
//         $branch
//     );
// }


// GET TOTAL DELETED FIRE EXTINGUISHERS
function getTotalDeletedFireExtinguishers()
{
    return getAllDeletedFireExtinguishersModel();
}

if ($action === 'restore') {

    $id = (int)($_GET['id'] ?? 0);
    $code = ($_GET['code'] ?? 'unable to get code');


    if ($id <= 0) {
        header("Location: ../../archived-extinguisher.php?error=invalid_id");
        exit;
    }

    $success = restoreFireExtinguisher($id);

    if ($success) {

        $user_name = $_SESSION['EmployeeName'] ?? 'error while getting employee name';

        createActivityLog(
            $user_name,
            "Restored Fire Extinguisher",
            "Restored fire extinguisher $code"
        );
        header("Location: ../../archived-extinguisher.php?success-restored=1");
        exit;
    }

    header("Location: ../../archived-extinguisher.php?error-restore=0");
    exit;
}
