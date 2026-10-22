<?php

require_once __DIR__ . '/../model/RejectedInspectionModel.php';

$role = strtolower(trim($_SESSION['Role'] ?? ''));
$employeeName = trim($_SESSION['EmployeeName'] ?? '');
$branch = trim($_SESSION['Branch'] ?? '');


if ($role !== 'inspector' || $employeeName === '' || $branch === '') {
    http_response_code(403);
    exit('Unauthorized access.');
}

$search = trim($_GET['search'] ?? '');
$dateFilter = $_GET['date'] ?? 'all';

$allowedFilters = ['all', 'today', 'yesterday', '7', '30'];

if (!in_array($dateFilter, $allowedFilters, true)) {
    $dateFilter = 'all';
}

$rejectedInspections = getRejectedInspections(
    $employeeName,
    $branch,
    $search,
    $dateFilter
);

if ($rejectedInspections === false) {
    http_response_code(500);
    exit('Unable to retrieve rejected inspections.');
}

$totalRejected = count($rejectedInspections);