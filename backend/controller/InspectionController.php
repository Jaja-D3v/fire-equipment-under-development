<?php

require_once __DIR__ . '/../model/InspectionModel.php';
require_once __DIR__ . '/../model/FireExtinguisherModel.php';
require_once __DIR__ . '/ActivityLogController.php';
require_once __DIR__ . '/../authentication/SessionChecker.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $action = $_GET['action'] ?? null;

    // Get Inspection Details
    if ($action === 'get') {

        $id = isset($_GET['id'])
            ? (int) $_GET['id']
            : 0;

        header('Content-Type: application/json');

        if ($id <= 0) {

            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid inspection ID.'
            ]);

            exit;
        }

        $inspection = getInspectionCheckListById($id);

        if (!$inspection) {

            echo json_encode([
                'success' => false,
                'message' => 'Inspection record not found.',
                'debug_id' => $id,
                'debug_branch' => getBranch()
            ]);

            exit;
        }

        if ($inspection) {

            echo json_encode([
                'success' => true,
                'inspection' => $inspection
            ]);
        } else {

            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Inspection record not found.'
            ]);
        }
        exit;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? null;

    if ($action == 'inspect') {

        $extinguisher_code = trim($_POST['extinguisher_code'] ?? '');
        $extinguisher_id = (int) ($_POST['extinguisher_id'] ?? 0);
        $location = $_POST['location'] ?? null;
        $capacity = $_POST['capacity'] ?? null;
        $type = $_POST['type'] ?? null;
        $class = $_POST['class'] ?? null;
        $date_inspected = $_POST['date_inspected'] ?? null;
        $inspected_by = $_SESSION['EmployeeName'] ?? 'error while getting employee name';
        $verified_and_approved_by = 'N/A';
        $action_taken = '';
        $target_date_of_implementation = null;
        $remarks = $_POST['remarks'] ?? null;
        $role = strtolower(trim($_SESSION['Role'] ?? ''));
        $branch = $role === 'inspector'
            ? trim($_SESSION['Branch'] ?? '')
            : trim($_POST['branch'] ?? '');

        // Inspection Checklist
        $is_seal_ok = isset($_POST['is_seal_ok']) ? 1 : 0;
        $is_pin_ok = isset($_POST['is_pin_ok']) ? 1 : 0;
        $is_pressure_ok = isset($_POST['is_pressure_ok']) ? 1 : 0;
        $is_hose_ok = isset($_POST['is_hose_ok']) ? 1 : 0;
        $is_nozzle_ok = isset($_POST['is_nozzle_ok']) ? 1 : 0;
        $is_belt_ok = isset($_POST['is_belt_ok']) ? 1 : 0;
        $is_cylinder_body_ok = isset($_POST['is_cylinder_body_ok']) ? 1 : 0;
        $is_demarcation_line_ok = isset($_POST['is_demarcation_line_ok']) ? 1 : 0;
        $is_signage_ok = isset($_POST['is_signage_ok']) ? 1 : 0;
        $is_cleaning_of_unit_ok = isset($_POST['is_cleaning_of_unit_ok']) ? 1 : 0;

        // Determine Overall Status
        if ($is_pressure_ok && $is_hose_ok && $is_nozzle_ok && $is_cylinder_body_ok) {

            $status = 1; // Good

        } else {

            $status = 0; // Not Good
        }

        // Save Inspection Checklist
        $success = addInspectionChecklist(
            $extinguisher_code,
            $location,
            $capacity,
            $type,
            $class,
            $date_inspected,
            $inspected_by,
            $verified_and_approved_by,
            $action_taken,
            $target_date_of_implementation,
            $is_seal_ok,
            $is_pin_ok,
            $is_pressure_ok,
            $is_hose_ok,
            $is_nozzle_ok,
            $is_belt_ok,
            $is_cylinder_body_ok,
            $is_demarcation_line_ok,
            $is_signage_ok,
            $is_cleaning_of_unit_ok,
            $status,
            $branch,
            $remarks,
            $extinguisher_id
        );

        // Redirect After Successful Submission
        if ($success) {
            $user_name = $_SESSION['EmployeeName'] ?? 'error while getting employee name';
            createActivityLog(
                $user_name,
                "Inspect Fire Extinguisher",
                "Inspect fire extinguisher $extinguisher_code"
            );
            header( "Location: ../../scan-QR-Code.php?success-inspect=1" );
            
            exit;
        }

        header("Location: ../../scan-QR-Code.php?error=inspect");
        exit;
    } elseif ($action == 'update_evaluation_status') {

        $inspect_id = (int) ($_POST['inspect_id'] ?? 0);
        $eval_stats = trim($_POST['evaluation_status'] ?? '');
        $action_taken = trim($_POST['action_taken'] ?? '');
        $target_date = trim($_POST['target_date_of_implementation'] ?? '');
        $approver_name = trim($_SESSION['EmployeeName'] ?? '');
        $condition_status = trim($_POST['condition_status'] ?? '');
        $remarks = trim($_POST['remarks'] ?? '');
        $rejectReason = trim($_POST['reject_reason'] ?? '');

        $success = processInspectionEvaluation(
            $inspect_id,
            $eval_stats,
            $approver_name,
            $action_taken,
            $target_date,
            $condition_status,
            $remarks,
            $rejectReason
        );

        if ($success && $eval_stats === 'Approved') {
            header("Location: ../../inspection-pending.php?approved_success=1");
            exit;
        }
        if ($success && $eval_stats === 'Rejected') {
            header("Location: ../../inspection-pending.php?rejected_success=1");
            exit;
        }

        header("Location: ../../inspection-pending.php?error=1");
        exit;
    }
}

function updateExpiry($exp_date, $extinguisherCode)
{
    return updateFireExtinguisherExpiryDate($exp_date, $extinguisherCode);
}

function ApprovedBy($name, $inspectid)
{
    Approved_by($name, $inspectid);
}

function RejectedBy($name, $inspectid)
{
    Rejected_by($name, $inspectid);
}

function getAllInspected()
{
    return getAllInspectionCheckList();
}

// Get Pending Approvals
function getPendingApprovals($limit = 10, $offset = 0, $date = null, $search = '')
{
    $role = strtolower(
        trim($_SESSION['Role'] ?? '')
    );

    if ($role === 'admin') {

        $sessionBranch = trim($_SESSION['Branch'] ?? '');
        $branch = trim($_GET['branch'] ?? $sessionBranch);
        if ($branch === '') $branch = 'all';

        return getAllPendingApproval($limit, $offset, $date, $branch, $search);
    }

    if ($role === 'inspector') {
        // Restrict inspectors to their assigned branch
        $branch = trim( $_SESSION['Branch'] ?? '' );

        if ($branch === '') {
            return false;
        }

        return getAllPendingApproval($limit, $offset, $date, $branch, $search);
    }
    return false;
}

// Get Pending Approval Total
function getPendingApprovalTotal($date = null, $search = '')
{
    $role = strtolower(
        trim($_SESSION['Role'] ?? '')
    );


    if ($role === 'admin') {

        $sessionBranch = trim($_SESSION['Branch'] ?? '');
        $branch = trim($_GET['branch'] ?? $sessionBranch);
        if ($branch === '')  $branch = 'all';
        return getPendingApprovalCount($date, $branch, $search);
    }

    if ($role === 'inspector') {

        // Restrict inspectors to their assigned branch
        $branch = trim($_SESSION['Branch'] ?? '');
        if ($branch === '')  return 0;
        return getPendingApprovalCount($date, $branch, $search);
    }

    return 0;
}

// Get Approved Approvals
function getApprovedApprovals(
    $limit = 10,
    $offset = 0,
    $date = null,
    $search = '',
    $toDate = null
) {

    if (!isValidApprovedInspectionDateRange($date, $toDate)) {
        return false;
    }

    $role = strtolower( trim($_SESSION['Role'] ?? '') );

    if ($role === 'admin') {

        $sessionBranch = trim($_SESSION['Branch'] ?? '');
        $branch = trim($_GET['branch'] ?? $sessionBranch);
        if ($branch === '')  $branch = 'all';
        return getAllApprovedApproval($limit, $offset, $date, $branch, $search, $toDate);
    }

    if ($role === 'inspector') {

        // Restrict inspectors to their assigned branch
        $branch = trim($_SESSION['Branch'] ?? '');
        if ($branch === '')  return false;
        return getAllApprovedApproval($limit, $offset, $date, $branch, $search, $toDate);
    }
    return false;
}

// Get Approved Approval Total
function getApprovedApprovalTotal($date = null, $search = '', $toDate = null)
{
    if (!isValidApprovedInspectionDateRange($date, $toDate)) {
        return 0;
    }
    $role = strtolower(
        trim($_SESSION['Role'] ?? '')
    );

    if ($role === 'admin') {

        $sessionBranch = trim($_SESSION['Branch'] ?? '');
        $branch = trim($_GET['branch'] ?? $sessionBranch);
        if ($branch === '')  $branch = 'all';

        return getApprovedApprovalCount($date, $branch, $search, $toDate);
    }

    if ($role === 'inspector') {

        // Restrict inspectors to their assigned branch
        $branch = trim($_SESSION['Branch'] ?? '');
        if ($branch === '')  return 0;

        return getApprovedApprovalCount($date, $branch, $search, $toDate);
    }
    return 0;
}

// Get Rejected Approvals
function getRejectedApprovals($limit = 10, $offset = 0, $date = null, $search = '')
{
    $role = strtolower(trim($_SESSION['Role'] ?? ''));

    if ($role === 'admin') {

        $sessionBranch = trim($_SESSION['Branch'] ?? '');
        $branch = trim($_GET['branch'] ?? $sessionBranch);
        if ($branch === '')  $branch = 'all';

        return getAllRejectedApproval($limit, $offset, $date, $branch, $search);
    }

    if ($role === 'inspector') {

        // Restrict inspectors to their assigned branch
        $branch = trim($_SESSION['Branch'] ?? '');
        if ($branch === '')  return false;
        return getAllRejectedApproval($limit, $offset, $date, $branch, $search);
    }
    return false;
}

// Get Rejected Approval Total
function getRejectedApprovalTotal($date = null, $search = '')
{
    $role = strtolower(
        trim($_SESSION['Role'] ?? '')
    );

    if ($role === 'admin') {

        $sessionBranch = trim($_SESSION['Branch'] ?? '');
        $branch = trim($_GET['branch'] ?? $sessionBranch);
        if ($branch === '')  $branch = 'all';
        return getRejectedApprovalCount($date, $branch, $search);
    }

    if ($role === 'inspector') {

        $branch = trim($_SESSION['Branch'] ?? '');
        if ($branch === '')  return 0;
        return getRejectedApprovalCount($date, $branch, $search);
    }
    return 0;
}


function updateEvalStats($eval_id, $evalStatus)
{
    return updateEvaluationStatus($eval_id, $evalStatus);
}


// Retrieve Approved Inspection for generate report 
function getApprovedInspectionReportsController($ids)
{
    if (empty($ids))  return [];

    return getApprovedInspectionReports($ids);
}

// getAllApprovedApprovalIds

function getAllApprovedApprovalIdsController($date = null, $branch = 'all', $toDate = null, $search = '')
{
    $role = strtolower(trim($_SESSION['Role'] ?? ''));

    if ($role === 'admin') {
        $sessionBranch = trim($_SESSION['Branch'] ?? '');
        $branch = trim($_GET['branch'] ?? $sessionBranch);
        if ($branch === '') $branch = 'all';
    } elseif ($role === 'inspector') {
        $branch = trim($_SESSION['Branch'] ?? '');
        if ($branch === '') return [];
    } else {
        return [];
    }

    return getAllApprovedApprovalIds($date, $branch, $toDate, $search);
}

// for inventory report
function getFireExtinguishersForInventoryReportController($ids)
{
    if (empty($ids)) return [];

    return getFireExtinguishersForInventoryReport($ids);
}
