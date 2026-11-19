<?php

require_once __DIR__ . '/../model/MaintenanceModel.php';

function maintenanceRedirect($query)
{
    $tab = trim((string) ($_POST['active_tab'] ?? $_GET['tab'] ?? 'branch'));
    $allowedTabs = array('branch', 'placement', 'type', 'fire_class', 'capacity', 'required_stock');
    if (!in_array($tab, $allowedTabs, true)) $tab = 'branch';
    header('Location: maintenance.php?' . http_build_query(array_merge(array('tab' => $tab), $query)));
    exit;
}

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
        maintenanceRedirect(array('error' => 'empty'));
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
        maintenanceRedirect(array('error' => 'invalid_category'));
    }

    $result = add( $category, $value );

    if ($result) {

        maintenanceRedirect(array('success' => 'added'));
    }

    maintenanceRedirect(array('error' => 'add_failed'));
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
        maintenanceRedirect(array('error' => 'invalid'));
    }

    $result = update( $id, $value );

    if ($result) {

        maintenanceRedirect(array('success' => 'updated'));
    }

    maintenanceRedirect(array('error' => 'update_failed'));
}


/*  DELETE / DEACTIVATE */
function deleteController()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $id = (int) ($_POST['id'] ?? 0);

    if ($id <= 0) {
        maintenanceRedirect(array('error' => 'invalid'));
    }

    $result = delete($id);

    if ($result) {

        maintenanceRedirect(array('success' => 'deleted'));
    }

    maintenanceRedirect(array('error' => 'delete_failed'));
}
