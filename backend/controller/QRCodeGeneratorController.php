<?php

require_once __DIR__ . '/../model/QRCodeGeneratorModel.php';


function getAllFireExtinguishersCode()
{
    $role = strtolower(trim($_SESSION['Role'] ?? ''));
    $sessionBranch = trim($_SESSION['Branch'] ?? '');

    // ADMIN
    if ($role === 'admin') {

        // If branch is selected from dropdown, use it
        if (
            isset($_GET['branch']) &&
            trim($_GET['branch']) !== ''
        ) {
            $selectedBranch = trim($_GET['branch']);
        } else {
            // Default = Admin's assigned branch
            $selectedBranch = $sessionBranch;
        }

        // If Admin has no assigned branch
        if ($selectedBranch === '') {
            $selectedBranch = 'all';
        }

        return getExtinguisher($selectedBranch);
    }

    // INSPECTOR
    if ($role === 'inspector') {

        if (empty($sessionBranch)) {
            return false;
        }
        return getExtinguisher($sessionBranch);
    }


    return false;
}


function getFireExtinguisherQRDetails($code, $branch = null)
{
    return getFireExtinguisherDetailsByCode($code, $branch);
}

function getAuthorizedQRPrintBranch($requestedBranch)
{
    $role = strtolower(trim($_SESSION['Role'] ?? ''));
    $sessionBranch = trim($_SESSION['Branch'] ?? '');

    if ($role === 'inspector') {
        return $sessionBranch !== '' ? $sessionBranch : false;
    }
    if ($role !== 'admin') {
        return false;
    }

    $branch = trim((string) $requestedBranch);
    if ($branch === '') {
        $branch = $sessionBranch !== '' ? $sessionBranch : 'all';
    }
    if (strcasecmp($branch, 'all') === 0) {
        return 'all';
    }

    $branches = getBranches();
    if (!$branches) {
        return false;
    }
    while ($row = $branches->fetch_assoc()) {
        if (strcasecmp(trim($row['branch']), $branch) === 0) {
            return trim($row['branch']);
        }
    }
    return false;
}

function getAllBranches()
{
    return getBranches();
}
