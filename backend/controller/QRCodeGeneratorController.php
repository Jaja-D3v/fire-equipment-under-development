<?php

require_once __DIR__ . '/../model/QRCodeGeneratorModel.php';
require_once __DIR__ . '/../config/qr-equipment.php';


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

function getQRPageData()
{
    $registry = getQREquipmentRegistry();
    $defaultEquipment = getDefaultQREquipmentId();
    $requestedEquipment = $_GET['equipment'] ?? $defaultEquipment;
    $equipment = is_string($requestedEquipment) ? trim($requestedEquipment) : '';
    if (!isset($registry[$equipment])) $equipment = $defaultEquipment;
    $definition = $registry[$equipment];
    $sessionBranch = trim($_SESSION['Branch'] ?? '');
    $branchFunction = $definition['branches_function'];
    $branchResult = $branchFunction();
    $available = [];
    if ($branchResult) while ($row = $branchResult->fetch_assoc()) $available[] = trim($row['branch']);
    $requested = trim((string)($_GET['branch'] ?? $sessionBranch));
    if ($requested === '') $requested = 'none';
    $valid = $requested === 'all' || in_array($requested, $available, true);
    if (!$valid) {
        $requested = in_array($sessionBranch, $available, true) ? $sessionBranch : 'none';
    }
    $result = false;
    if ($requested !== 'none') {
        $listFunction = $definition['list_function'];
        $queryResult = $listFunction($requested);
        if ($queryResult) {
            $result = [];
            while ($row = $queryResult->fetch_assoc()) {
                $row['code'] = (string)($row['code'] ?? '');
                $row['branch'] = (string)($row['branch'] ?? '');
                $row['location'] = (string)($row['location'] ?? '');
                $row['equipment_type'] = (string)($row['equipment_type'] ?? $definition['label']);
                $result[] = $row;
            }
        }
    }
    return [$equipment, $definition, $requested, $available, $result];
}

function isQRBranchAllowed($equipment, $branch, $pageBranch)
{
    if (strtolower(trim($_SESSION['Role'] ?? '')) !== 'admin') return false;
    $definition = getQREquipmentDefinition($equipment);
    if ($definition === false) return false;
    $branch = trim((string)$branch);
    $pageBranch = trim((string)$pageBranch);
    if ($branch === '' || $pageBranch === 'none') return false;
    if ($pageBranch !== 'all' && strcasecmp($branch, $pageBranch) !== 0) return false;
    $branchFunction = $definition['branches_function'];
    $branches = $branchFunction();
    if (!$branches) return false;
    while ($row = $branches->fetch_assoc()) {
        if (strcasecmp(trim($row['branch']), $branch) === 0) return true;
    }
    return false;
}

function getQRRecordByCodeAndBranch($equipment, $code, $branch)
{
    $definition = getQREquipmentDefinition($equipment);
    if ($definition === false) return false;
    $lookupFunction = $definition['lookup_function'];
    $record = $lookupFunction($code, $branch);
    if (is_array($record) && empty($record['ambiguous'])) {
        $record['equipment_type'] = (string)($record['equipment_type'] ?? $definition['label']);
    }
    return $record;
}
