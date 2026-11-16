
<?php

require_once __DIR__ . '/../model/ChemicalSpillKitInspectionModel.php';
require_once __DIR__ . '/ActivityLogController.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'getEvaluationDetails') {
    require_once __DIR__ . '/../authentication/SessionChecker.php';
    header('Content-Type: application/json; charset=utf-8');

    $role = strtolower(trim((string) ($_SESSION['Role'] ?? '')));
    if (!in_array($role, array('admin', 'inspector', 'super admin'), true)) {
        http_response_code(403);
        echo json_encode(array('success' => false, 'message' => 'Access denied.'));
        exit;
    }

    $inspectionId = (int) ($_GET['id'] ?? 0);
    if ($inspectionId <= 0) {
        http_response_code(400);
        echo json_encode(array('success' => false, 'message' => 'Invalid inspection ID.'));
        exit;
    }

    $inspection = getChemicalSpillKitEvaluationDetails(
        $inspectionId,
        $role,
        trim((string) ($_SESSION['Branch'] ?? ''))
    );
    if (!$inspection) {
        http_response_code(404);
        echo json_encode(array('success' => false, 'message' => 'Inspection not found or unavailable.'));
        exit;
    }

    echo json_encode(array('success' => true, 'inspection' => $inspection));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_once __DIR__ . '/../authentication/SessionChecker.php';
    $postAction = (string) ($_POST['action'] ?? '');

    if ($postAction === 'evaluate_inspection') {
        $role = strtolower(trim((string) ($_SESSION['Role'] ?? '')));
        $evaluationStatus = trim((string) ($_POST['evaluation_status'] ?? ''));
        $inspectionId = (int) ($_POST['inspection_id'] ?? 0);
        $approverName = trim((string) ($_SESSION['EmployeeName'] ?? ''));
        $rejectionReason = trim((string) ($_POST['rejection_reason'] ?? ''));

        if (
            !in_array($role, array('admin', 'inspector', 'super admin'), true) ||
            $inspectionId <= 0 ||
            !in_array($evaluationStatus, array('Approved', 'Rejected'), true) ||
            ($evaluationStatus === 'Rejected' && $rejectionReason === '')
        ) {
            header('Location: ../../admin-pending-inspections.php?error=1');
            exit;
        }

        $success = updateChemicalSpillKitEvaluation(
            $inspectionId,
            $evaluationStatus,
            $approverName,
            $rejectionReason
        );

        if ($success) {
            createActivityLog(
                $approverName,
                $evaluationStatus === 'Approved' ? 'Approve Chemical Spill Kit Inspection' : 'Reject Chemical Spill Kit Inspection',
                $evaluationStatus . ' Chemical Spill Kit inspection ' . $inspectionId
            );
            $notification = $evaluationStatus === 'Approved' ? 'approved_success=1' : 'rejected_success=1';
            header('Location: ../../admin-pending-inspections.php?' . $notification);
            exit;
        }

        header('Location: ../../admin-pending-inspections.php?error=1');
        exit;
    }

    require_once __DIR__ . '/../authentication/SessionChecker.php';
    require_once __DIR__ . '/../model/ChemicalSpillKitModel.php';
    $role = strtolower(trim((string) ($_SESSION['Role'] ?? '')));
    $code = is_string($_POST['code'] ?? null) ? trim($_POST['code']) : '';
    $requestedBranch = is_string($_POST['branch'] ?? null) ? trim($_POST['branch']) : '';
    $assignedBranch = trim((string) ($_SESSION['Branch'] ?? ''));
    if (!in_array($role, ['admin', 'inspector', 'super admin'], true)
        || ($role !== 'admin' && $role !== 'super admin' && ($assignedBranch === '' || strcasecmp($requestedBranch, $assignedBranch) !== 0))) {
        http_response_code(403);
        exit('You may only inspect kits in your assigned branch.');
    }
    // An all-branch read scope cannot identify an inspection's target kit.
    if ($role === 'super admin' && ($requestedBranch === '' || strcasecmp($requestedBranch, 'all') === 0)) {
        http_response_code(400);
        exit('Select a specific kit branch before submitting an inspection.');
    }
    $branch = in_array($role, ['admin', 'super admin'], true) ? $requestedBranch : $assignedBranch;
    $kit = getChemicalSpillKitByCode($code, $branch);
    if (!$kit) {
        http_response_code(404);
        exit('Chemical Spill Kit not found for the specified branch.');
    }
    $inspectedBy = trim((string) ($_SESSION['EmployeeName'] ?? ''));
    $result = insertChemicalInspectionController(
        $code, $branch, $kit['location'], $inspectedBy, date('Y-m-d H:i:s'),
        $_POST['materials'] ?? [], (int) $kit['chemical_spill_kit_id'], $inspectedBy
    );
    if ($result !== false) {
        createActivityLog($inspectedBy, 'Inspect Chemical Spill Kit', 'Inspect Chemical Spill Kit ' . $code);
        header('Location: ../../scan-QR-Code.php?success-inspect=1');
        exit;
    }
    http_response_code(422);
    exit('Failed to submit inspection. Check configured materials and whole-number quantities.');
}

function insertChemicalInspectionController($code, $branch, $location, $inspectedBy, $dateInspected, $materials, $kitId, $submittedBy)
{
    return insertChemicalInspection($code, $branch, $location, $inspectedBy, $dateInspected, $materials, $kitId, $submittedBy);
}
