<?php

require_once __DIR__ . '/../model/MaintenanceModel.php';

/* GET DATA  */
function indexController()
{
    $categories = [
        'branch',
        'placement',
        'type',
        'fire_class',
        'capacity',
        'location'
    ];

    $data = [];

    foreach ($categories as $category) {

        $page = isset($_GET[$category . '_page']) ? max(1, (int) $_GET[$category . '_page']) : 1;
        $search = isset($_GET[$category . '_search']) ? trim($_GET[$category . '_search']) : '';
        $data[$category] = [
            'records' => getData($category, $page, 5, $search),
            'total' => getTotal($category, $search),
            'page' => $page,
            'total_pages' => ceil(getTotal($category, $search) / 5),
            'search' => $search
        ];
    }

    return $data;
}

/*  ADD  */
function addController()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $category = trim($_POST['category'] ?? '');
    $value = trim($_POST['value'] ?? '');

    if ( empty($category) || empty($value) ) 
    {
        header("Location: maintenance.php?error=empty");
        exit;
    }

    $allowedCategories = [
        'branch',
        'placement',
        'type',
        'fire_class',
        'capacity',
        'location'
    ];

    if (!in_array($category, $allowedCategories)) {
        header("Location: maintenance.php?error=invalid_category");
        exit;
    }

    $result = add( $category, $value );

    if ($result) {

        header("Location: maintenance.php?success=added");
        exit;
    }

    header("Location: maintenance.php?error=add_failed");
    exit;
}


/* UPDATE */
function updateController()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $id = (int) ($_POST['id'] ?? 0);
    $value = trim($_POST['value'] ?? '');

    if (
        $id <= 0 ||
        empty($value)
    ) {
        header("Location: maintenance.php?error=invalid");
        exit;
    }

    $result = update( $id, $value );

    if ($result) {

        header("Location: maintenance.php?success=updated");
        exit;
    }

    header("Location: maintenance.php?error=update_failed");
    exit;
}


/*  DELETE / DEACTIVATE */
function deleteController()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $id = (int) ($_POST['id'] ?? 0);

    if ($id <= 0) {
        header("Location: maintenance.php?error=invalid");
        exit;
    }

    $result = delete($id);

    if ($result) {

        header("Location: maintenance.php?success=deleted");
        exit;
    }

    header("Location: maintenance.php?error=delete_failed");
    exit;
}
