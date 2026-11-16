<?php
require_once __DIR__ . '/../authentication/SessionChecker.php';
require_once __DIR__ . '/../model/ChemicalSpillKitModel.php';
require_once __DIR__ . '/DropdownController.php';
require_once __DIR__ . '/ActivityLogController.php';

// CSK-specific code endpoints follow the FE getNextCode/checkCode response pattern.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action === 'getNextCode' || $action === 'checkCode') {
        header('Content-Type: application/json; charset=utf-8');
        $branch = trim((string)($_GET['branch'] ?? ''));
        if ($branch === '') {
            echo json_encode(['success' => false, 'exists' => false]);
            exit;
        }

        if ($action === 'getNextCode') {
            $code = getNextChemicalSpillKitCode($branch);
            echo json_encode([
                'success' => is_string($code) && preg_match('/^CSK-\d+$/', $code) === 1,
                'code' => $code
            ]);
            exit;
        }

        $code = trim((string)($_GET['code'] ?? ''));
        $exists = $code === '' ? false : chemicalSpillKitCodeExists($code, $branch);
        echo json_encode([
            'success' => is_bool($exists),
            'exists' => $exists === true
        ]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cskAction = is_string($_POST['csk_action'] ?? 'add') ? trim($_POST['csk_action'] ?? 'add') : '';
    if (!in_array($cskAction, ['add', 'edit', 'archive'], true)) {
        http_response_code(400);
        exit('Invalid inventory action.');
    }
    if ($cskAction === 'edit' || $cskAction === 'archive') {
        $isAdmin = in_array(strtolower(trim($_SESSION['Role'] ?? '')), ['admin', 'super admin'], true);
        $kitId = (int)($_POST['chemical_spill_kit_id'] ?? 0);
        $kitCode = trim($_POST['chemical_spill_kit_code'] ?? '');
        $kitBranch = $isAdmin
            ? trim($_POST['branch'] ?? '')
            : trim($_SESSION['Branch'] ?? '');
        // All-branch QR discovery is read-only; edits and archives need the actual kit branch.
        $isSuperAdmin = strtolower(trim($_SESSION['Role'] ?? '')) === 'super admin';
        $kit = $kitCode !== '' && $kitBranch !== ''
            && !($isSuperAdmin && strcasecmp($kitBranch, 'all') === 0)
            ? getChemicalSpillKitByCode($kitCode, $kitBranch)
            : null;

        if (
            !$kit ||
            (int)($kit['chemical_spill_kit_id'] ?? 0) !== $kitId ||
            (int)($kit['archived'] ?? 1) !== 0
        ) {
            header('Location: chemical-spill-kit-inventory.php?csk_action=error');
            exit;
        }

        if ($cskAction === 'archive') {
            $success = archiveChemicalSpillKit($kitId, $kitBranch);
            header('Location: chemical-spill-kit-inventory.php?csk_action=' . ($success ? 'archived' : 'error'));
            exit;
        }


        $success = editChemicalSpillKit(
            $kitId,
            is_string($_POST['location'] ?? null) ? trim($_POST['location']) : '',
            $_POST['materials'] ?? [],
            $kitBranch
        );

        if ($success) {
            header('Location: ?updated-success=1');
        } else {
            header('Location: ?csk_action=error&csk_error=required_stock');
        }
        exit;
    }

    $code = trim((string)($_POST['chemical_spill_kit_code'] ?? ''));
    $isAdmin = in_array(strtolower(trim((string)($_SESSION['Role'] ?? ''))), ['admin', 'super admin'], true);
    $branch = $isAdmin
        ? trim((string)($_POST['branch'] ?? ''))
        : trim((string)($_SESSION['Branch'] ?? ''));

    if (!preg_match('/^CSK-\d+$/', $code) || $branch === '') {
        http_response_code(400);
        exit('A valid CSK code and branch are required.');
    }

    $codeExists = chemicalSpillKitCodeExists($code, $branch);
    if ($codeExists === null) {
        http_response_code(503);
        exit('Unable to verify the CSK code. No kit was added.');
    }
    if ($codeExists) {
        http_response_code(409);
        exit('This CSK code already exists in the selected branch. No kit was added.');
    }
    $location = is_string($_POST['location'] ?? null) ? trim($_POST['location']) : '';
    $addSuccess = addNewChemicalSpillKitController($code, $branch, $location, $_POST['materials'] ?? []);

    if ($addSuccess) {

        $user_name =
            $_SESSION['EmployeeName']
            ?? 'error while getting employee name';


        createActivityLog(
            $user_name,
            "Add Chemical Spill Kit",
            "Added Chemical Spill Kit $code"
        );


        header(
            "Location: ../../chemical-spill-kit-inventory.php?success-add=1"
        );

        exit;
    } else {
        header('Location: ../../chemical-spill-kit-inventory.php?csk_error=required_stock');
        exit;
    }
}











// Get all Chemical Spill Kit inventory records
function getAllListChemicalSpill($branch, $limit = 10, $offset = 0, $search = '')
{
    return getAllChemicalSpillKit($branch, $limit, $offset, $search);
}

// Admin and Super Admin may select a branch; other roles retain their session scope.
$isChemicalSpillKitAdmin = in_array(strtolower(trim($_SESSION['Role'] ?? '')), ['admin', 'super admin'], true);
if ($isChemicalSpillKitAdmin) {
    $branch = trim($_GET['branch'] ?? (strtolower(trim($_SESSION['Role'] ?? '')) === 'super admin' ? 'all' : ($_SESSION['Branch'] ?? '')));
    if ($branch === '') {
        $branch = strtolower(trim($_SESSION['Role'] ?? '')) === 'super admin' ? 'all' : trim($_SESSION['Branch'] ?? '');
    }
} else {
    $branch = trim($_SESSION['Branch'] ?? '');
}

$search = trim($_GET['search'] ?? '');
$limit = 10;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$totalRecords = getChemicalSpillKitInventoryCount($branch, $search);
$totalPages = max(1, (int)ceil($totalRecords / $limit));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $limit;

$chemicalSpillKits = getAllListChemicalSpill($branch, $limit, $offset, $search);
$branches = $isChemicalSpillKitAdmin ? getAllDropdownBranches() : [];



// Get Chemical Spill Kit by code
function getChemicalSpillKitInventopryByCode($code, $branch)
{
    return getChemicalSpillKitByCode($code, $branch);
}



// Controllers pass validated request shape to the procedural model.
function addNewChemicalSpillKitController($code, $branch, $location, $materials)
{
    return addNewChemicalSpillKit($code, $branch, $location, $materials);
}

function editChemicalSpillKit($id, $location, $materials, $branch = '')
{
    return updateSpillKitMaterials($id, $location, $materials, $branch);
}
