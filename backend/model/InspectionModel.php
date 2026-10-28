<?php
require_once __DIR__ . '/../config/db.php';

function isValidApprovedInspectionDateRange($fromDate = null, $toDate = null)
{
    foreach ([$fromDate, $toDate] as $date) {
        if ($date === null || $date === '') {
            continue;
        }

        if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $date));
        if (!checkdate($month, $day, $year)) {
            return false;
        }
    }

    return empty($fromDate) || empty($toDate) || $fromDate <= $toDate;
}

// funciton for add inspection list

function addInspectionChecklist(
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
) {
    global $conn;

    $role = strtolower(trim($_SESSION['Role'] ?? ''));
    $branch = trim((string) $branch);
    $extinguisher_code = trim((string) $extinguisher_code);
    $extinguisher_id = (int) $extinguisher_id;

    if ($role === 'inspector') {
        $assignedBranch = trim($_SESSION['Branch'] ?? '');

        if ($assignedBranch === '') {
            return false;
        }

        $branch = $assignedBranch;
    } elseif ($role !== 'admin') {
        return false;
    }

    if (
        $extinguisher_id <= 0 ||
        $extinguisher_code === '' ||
        !$conn->begin_transaction()
    ) {
        return false;
    }

    try {
        // Verify the extinguisher and enforce the Inspector's branch.
        $lookupSql = "SELECT extinguisher_id, extinguisher_code,
                             location, capacity, type, class, branch
                      FROM fire_extinguishers_tbl
                      WHERE extinguisher_id = ?
                        AND extinguisher_code = ?
                        AND archived = 0";

        if ($role === 'inspector') {
            $lookupSql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?))";
        }

        $lookupSql .= " FOR UPDATE";

        $lookupStmt = $conn->prepare($lookupSql);

        if (!$lookupStmt) {
            throw new RuntimeException('Could not prepare extinguisher lookup.');
        }

        if ($role === 'inspector') {
            $lookupStmt->bind_param(
                "iss",
                $extinguisher_id,
                $extinguisher_code,
                $assignedBranch
            );
        } else {
            $lookupStmt->bind_param(
                "is",
                $extinguisher_id,
                $extinguisher_code
            );
        }

        if (!$lookupStmt->execute()) {
            $lookupStmt->close();
            throw new RuntimeException('Could not resolve extinguisher.');
        }

        $lookupResult = $lookupStmt->get_result();
        $extinguisher = $lookupResult
            ? $lookupResult->fetch_assoc()
            : null;

        $lookupStmt->close();

        if (!$extinguisher) {
            throw new RuntimeException(
                'Extinguisher is missing or not authorized.'
            );
        }

        // Use the verified inventory values.
        $extinguisherId = (int) $extinguisher['extinguisher_id'];
        $extinguisher_code = $extinguisher['extinguisher_code'];
        $location = $extinguisher['location'];
        $capacity = $extinguisher['capacity'];
        $type = $extinguisher['type'];
        $class = $extinguisher['class'];
        $branch = $extinguisher['branch'];

        // Insert the inspection checklist first.
        $sql = "INSERT INTO inspection_checklist_tbl (
                    extinguisher_code,
                    location,
                    capacity,
                    type,
                    class,
                    date_inspected,
                    inspected_by,
                    verified_and_approved_by,
                    action_taken,
                    target_date_of_implementation,
                    is_seal_ok,
                    is_pin_ok,
                    is_pressure_ok,
                    is_hose_ok,
                    is_nozzle_ok,
                    is_belt_ok,
                    is_cylinder_body_ok,
                    is_demarcation_line_ok,
                    is_signage_ok,
                    is_cleaning_of_unit_ok,
                    status,
                    branch,
                    remarks
                ) VALUES (
                    ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?
                )";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new RuntimeException('Could not prepare inspection insert.');
        }

        $stmt->bind_param(
            "sssssssssiiiiiiiiiiiss",
            $extinguisher_code,
            $location,
            $capacity,
            $type,
            $class,
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
            $remarks
        );

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Could not save inspection: ' . $error);
        }

        $stmt->close();

        // Get the ID generated by inspection_checklist_tbl.
        $inspectionId = (int) $conn->insert_id;

        if ($inspectionId <= 0) {
            throw new RuntimeException('Could not retrieve inspection ID.');
        }

        // Create the pending transaction linked to this inspection.
        $category = 'Fire Extinguisher';

        $transactionStmt = $conn->prepare(
            "INSERT INTO transactions_tbl (
                category,
                branch,
                inspection_id,
                transaction_type,
                status,
                submitted_by
            ) VALUES (?, ?, ?, 'inspection', 'pending', ?)"
        );

        if (!$transactionStmt) {
            throw new RuntimeException('Could not prepare transaction insert.');
        }

        $transactionStmt->bind_param(
            "ssis",
            $category,
            $branch,
            $inspectionId,
            $inspected_by
        );

        if (!$transactionStmt->execute()) {
            $error = $transactionStmt->error;
            $transactionStmt->close();
            throw new RuntimeException('Could not save transaction: ' . $error);
        }

        $transactionStmt->close();

        // Update the extinguisher's inspection metadata.
        $updateSql = "UPDATE fire_extinguishers_tbl
                      SET last_date_inspected = NOW(),
                          inspected_by = ?
                      WHERE extinguisher_id = ?";

        $updateStmt = $conn->prepare($updateSql);

        if (!$updateStmt) {
            throw new RuntimeException('Could not prepare inventory update.');
        }

        $updateStmt->bind_param(
            "si",
            $inspected_by,
            $extinguisherId
        );

        if (!$updateStmt->execute()) {
            $error = $updateStmt->error;
            $updateStmt->close();
            throw new RuntimeException('Could not update inspection metadata: ' . $error);
        }

        $updateStmt->close();

        // Commit only after every operation succeeds.
        if (!$conn->commit()) {
            throw new RuntimeException('Could not commit inspection.');
        }

        return true;

    } catch (Throwable $error) {
        $conn->rollback();

        // Log the actual error for debugging.
        error_log('addInspectionChecklist error: ' . $error->getMessage());

        return false;
    }
}


// Get current logged-in branch
function getBranch()
{
    return $_SESSION['Branch'] ?? null;
}




function processInspectionEvaluation(
    $inspectionId,
    $evaluationStatus,
    $approverName,
    $actionTaken,
    $targetDate,
    $conditionStatus,
    $remarks,
    $rejectionReason
) {
    global $conn;

    $inspectionId = (int) $inspectionId;
    $role = strtolower(trim($_SESSION['Role'] ?? ''));
    $assignedBranch = trim($_SESSION['Branch'] ?? '');
    $approverName = trim((string) $approverName);
    $targetDate = trim((string) $targetDate);

    if (
        $inspectionId <= 0 ||
        $approverName === '' ||
        !in_array($evaluationStatus, array('Approved', 'Rejected'), true) ||
        !in_array($role, array('admin', 'inspector'), true) ||
        !$conn->begin_transaction()
    ) {
        return false;
    }

    try {
        $inspectionSql = "SELECT extinguisher_code, branch
                          FROM inspection_checklist_tbl
                          WHERE inspect_id = ?
                          FOR UPDATE";
        $inspectionStmt = mysqli_prepare($conn, $inspectionSql);
        if (!$inspectionStmt) {
            throw new RuntimeException('Could not prepare inspection lookup.');
        }
        mysqli_stmt_bind_param($inspectionStmt, "i", $inspectionId);
        if (!mysqli_stmt_execute($inspectionStmt)) {
            mysqli_stmt_close($inspectionStmt);
            throw new RuntimeException('Could not resolve inspection.');
        }
        $inspectionResult = mysqli_stmt_get_result($inspectionStmt);
        $inspection = $inspectionResult
            ? mysqli_fetch_assoc($inspectionResult)
            : null;
        mysqli_stmt_close($inspectionStmt);

        if (!$inspection) {
            throw new RuntimeException('Inspection does not exist.');
        }

        $inspectionBranch = trim($inspection['branch']);
        if (
            $role === 'inspector' &&
            ($assignedBranch === '' || strcasecmp($assignedBranch, $inspectionBranch) !== 0)
        ) {
            throw new RuntimeException('Inspector cannot update another branch.');
        }

        $shouldUpdateInventory =
            $evaluationStatus === 'Approved' ||
            ($evaluationStatus === 'Rejected' && $actionTaken === 'Refill');

        if ($shouldUpdateInventory) {
            if (
                $evaluationStatus === 'Approved' &&
                !in_array($conditionStatus, array('Good', 'No Good'), true)
            ) {
                throw new RuntimeException('Invalid condition status.');
            }

            $inventorySql = "SELECT extinguisher_id
                             FROM fire_extinguishers_tbl
                             WHERE extinguisher_code = ?
                               AND LOWER(TRIM(branch)) = LOWER(TRIM(?))
                               AND archived = 0
                             FOR UPDATE";
            $inventoryStmt = mysqli_prepare($conn, $inventorySql);
            if (!$inventoryStmt) {
                throw new RuntimeException('Could not prepare inventory lookup.');
            }
            mysqli_stmt_bind_param(
                $inventoryStmt,
                "ss",
                $inspection['extinguisher_code'],
                $inspectionBranch
            );
            if (!mysqli_stmt_execute($inventoryStmt)) {
                mysqli_stmt_close($inventoryStmt);
                throw new RuntimeException('Could not resolve inventory record.');
            }
            $inventoryResult = mysqli_stmt_get_result($inventoryStmt);
            $inventoryRows = $inventoryResult
                ? mysqli_fetch_all($inventoryResult, MYSQLI_ASSOC)
                : array();
            mysqli_stmt_close($inventoryStmt);

            if (count($inventoryRows) !== 1) {
                throw new RuntimeException('Inventory record is missing or ambiguous.');
            }

            $extinguisherId = (int) $inventoryRows[0]['extinguisher_id'];
            if ($actionTaken === 'Refill') {
                $implementationDate = DateTime::createFromFormat('!Y-m-d', $targetDate);
                if (!$implementationDate || $implementationDate->format('Y-m-d') !== $targetDate) {
                    throw new RuntimeException('Invalid refill implementation date.');
                }
                $implementationDate->modify('+3 years');
                $expirationDate = $implementationDate->format('Y-m-d');
            }



            if ($evaluationStatus === 'Approved' && $actionTaken === 'Refill') {
                $inventoryUpdateSql = "UPDATE fire_extinguishers_tbl
                                       SET condition_status = ?, remarks = ?, expiration_date = ?
                                       WHERE extinguisher_id = ?";
                $inventoryUpdateStmt = mysqli_prepare($conn, $inventoryUpdateSql);
                if (!$inventoryUpdateStmt) {
                    throw new RuntimeException('Could not prepare refill update.');
                }
                mysqli_stmt_bind_param(
                    $inventoryUpdateStmt,
                    "sssi",
                    $conditionStatus,
                    $remarks,
                    $expirationDate,
                    $extinguisherId
                );
            } elseif ($evaluationStatus === 'Approved') {
                $inventoryUpdateSql = "UPDATE fire_extinguishers_tbl
                                       SET condition_status = ?, remarks = ?
                                       WHERE extinguisher_id = ?";
                $inventoryUpdateStmt = mysqli_prepare($conn, $inventoryUpdateSql);
                if (!$inventoryUpdateStmt) {
                    throw new RuntimeException('Could not prepare condition update.');
                }
                mysqli_stmt_bind_param(
                    $inventoryUpdateStmt,
                    "ssi",
                    $conditionStatus,
                    $remarks,
                    $extinguisherId
                );
            } else {
                $inventoryUpdateSql = "UPDATE fire_extinguishers_tbl
                                       SET expiration_date = ?
                                       WHERE extinguisher_id = ?";
                $inventoryUpdateStmt = mysqli_prepare($conn, $inventoryUpdateSql);
                if (!$inventoryUpdateStmt) {
                    throw new RuntimeException('Could not prepare refill expiration update.');
                }
                mysqli_stmt_bind_param(
                    $inventoryUpdateStmt,
                    "si",
                    $expirationDate,
                    $extinguisherId
                );
            }

            if (!mysqli_stmt_execute($inventoryUpdateStmt)) {
                mysqli_stmt_close($inventoryUpdateStmt);
                throw new RuntimeException('Could not update inventory record.');
            }
            mysqli_stmt_close($inventoryUpdateStmt);
        }

        $targetDateValue = $targetDate === '' ? null : $targetDate;
        $inspectionUpdateSql = "UPDATE inspection_checklist_tbl
                                SET evaluation_status = ?,
                                    verified_and_approved_by = CASE
                                        WHEN ? = 'Approved' THEN ?
                                        ELSE verified_and_approved_by
                                    END,
                                    rejected_by = CASE
                                        WHEN ? = 'Rejected' THEN ?
                                        ELSE rejected_by
                                    END,
                                    action_taken = ?,
                                    target_date_of_implementation = ?,
                                    reject_reason = ?,
                                    rejection_date = CASE
                                        WHEN ? = 'Rejected' THEN NOW()
                                        ELSE NULL
                                    END
                                WHERE inspect_id = ?
                                  AND LOWER(TRIM(branch)) = LOWER(TRIM(?))";
        $inspectionUpdateStmt = mysqli_prepare($conn, $inspectionUpdateSql);
        if (!$inspectionUpdateStmt) {
            throw new RuntimeException('Could not prepare inspection update.');
        }
        mysqli_stmt_bind_param(
            $inspectionUpdateStmt,
            "sssssssssis",
            $evaluationStatus,
            $evaluationStatus,
            $approverName,
            $evaluationStatus,
            $approverName,
            $actionTaken,
            $targetDateValue,
            $rejectionReason,
            $evaluationStatus,
            $inspectionId,
            $inspectionBranch
        );
        if (!mysqli_stmt_execute($inspectionUpdateStmt)) {
            mysqli_stmt_close($inspectionUpdateStmt);
            throw new RuntimeException('Could not update inspection status.');
        }
        mysqli_stmt_close($inspectionUpdateStmt);

        if (!$conn->commit()) {
            throw new RuntimeException('Could not commit inspection update.');
        }
        return true;
    } catch (Throwable $error) {
        $conn->rollback();
        return false;
    }
}

// GET ALL LIST
function getAllInspectionCheckList()
{
    global $conn;

    $branch = getBranch();

    if (empty($branch)) {
        return [];
    }

    $sql = "SELECT *
            FROM inspection_checklist_tbl
            WHERE LOWER(TRIM(branch)) = LOWER(TRIM(?))
            ORDER BY inspect_id DESC";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return [];
    }

    mysqli_stmt_bind_param($stmt, "s", $branch);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $data;
}


// GET LIST BY ID
function getInspectionCheckListById($id)
{
    global $conn;

    $sql = "SELECT *
            FROM inspection_checklist_tbl
            WHERE inspect_id = ?
            LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $inspection = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $inspection ?: null;
}

// GET ALL PENDING APPROVAL
function getAllPendingApproval(
    $limit = 10,
    $offset = 0,
    $date = null,
    $branch = 'all',
    $search = ''
) {
    global $conn;

    $sql = "SELECT *
            FROM inspection_checklist_tbl
            WHERE 1 = 1";

    $types = "";
    $params = [];

    // DATE FILTER - AS OF SELECTED DATE
    if (!empty($date)) {

        $sql .= " AND date_inspected < DATE_ADD(?, INTERVAL 1 DAY) ";
        $types .= "s";
        $params[] = $date;
    }

    // BRANCH FILTER

    if ($branch !== 'all' && !empty($branch)) {

        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
        $types .= "s";
        $params[] = $branch;
    }

    if ($search !== '') {
        $sql .= " AND (extinguisher_code LIKE ? OR location LIKE ?) ";
        $types .= "ss";
        $keyword = '%' . $search . '%';
        $params[] = $keyword;
        $params[] = $keyword;
    }

    $sql .= "
        AND NOT EXISTS (
            SELECT 1
            FROM inspection_checklist_tbl AS newer

            WHERE newer.extinguisher_code =
                  inspection_checklist_tbl.extinguisher_code
              AND LOWER(TRIM(newer.branch)) =
                  LOWER(TRIM(inspection_checklist_tbl.branch))

            AND (
                newer.date_inspected >
                inspection_checklist_tbl.date_inspected

                OR (
                    newer.date_inspected =
                    inspection_checklist_tbl.date_inspected

                    AND newer.inspect_id >
                    inspection_checklist_tbl.inspect_id
                )
            )
    ";

    // NEWER RECORD MUST ALSO BE WITHIN SELECTED DATE
    if (!empty($date)) {

        $sql .= " AND newer.date_inspected < DATE_ADD(?, INTERVAL 1 DAY) ";
        $types .= "s";
        $params[] = $date;
    }

    $sql .= " ) ";

    // STATUS
    $sql .= " AND evaluation_status = 'Pending' ";

    // PAGINATION
    $sql .= "
        ORDER BY date_inspected DESC, inspect_id DESC
        LIMIT ? OFFSET ?
    ";

    $types .= "ii";
    $params[] = (int) $limit;
    $params[] = (int) $offset;

    // PREPARE
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    // BIND PARAMETERS
    if (!empty($types)) {

        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );
    }

    // EXECUTE
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return false;
    }

    // RESULT
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}


// FOR COUNT ALL PENDING APPROVAL
function getPendingApprovalCount(
    $date = null,
    $branch = 'all',
    $search = ''
) {
    global $conn;

    $sql = "SELECT COUNT(*) AS total
            FROM inspection_checklist_tbl
            WHERE 1 = 1";

    $types = "";
    $params = [];

    // DATE FILTER - AS OF SELECTED DATE
    if (!empty($date)) {

        $sql .= "
            AND date_inspected < DATE_ADD(?, INTERVAL 1 DAY)
        ";

        $types .= "s";
        $params[] = $date;
    }

    // BRANCH FILTER
    if ($branch !== 'all' && !empty($branch)) {

        $sql .= "
            AND LOWER(TRIM(branch)) = LOWER(TRIM(?))
        ";

        $types .= "s";
        $params[] = $branch;
    }

    if ($search !== '') {
        $sql .= " AND (extinguisher_code LIKE ? OR location LIKE ?) ";
        $types .= "ss";
        $keyword = '%' . $search . '%';
        $params[] = $keyword;
        $params[] = $keyword;
    }

    // LATEST RECORD PER EXTINGUISHER CODE
    // AS OF SELECTED DATE
    $sql .= "
        AND NOT EXISTS (
            SELECT 1
            FROM inspection_checklist_tbl AS newer

            WHERE newer.extinguisher_code =
                  inspection_checklist_tbl.extinguisher_code
              AND LOWER(TRIM(newer.branch)) =
                  LOWER(TRIM(inspection_checklist_tbl.branch))

            AND (
                newer.date_inspected >
                inspection_checklist_tbl.date_inspected

                OR (
                    newer.date_inspected =
                    inspection_checklist_tbl.date_inspected

                    AND newer.inspect_id >
                    inspection_checklist_tbl.inspect_id
                )
            )
    ";

    // NEWER RECORD MUST ALSO BE WITHIN SELECTED DATE
    if (!empty($date)) {

        $sql .= "
            AND newer.date_inspected < DATE_ADD(?, INTERVAL 1 DAY)
        ";

        $types .= "s";
        $params[] = $date;
    }

    $sql .= "
        )
    ";

    // STATUS
    $sql .= "
        AND evaluation_status = 'Pending'
    ";

    // PREPARE
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return 0;
    }

    // BIND PARAMETERS
    if (!empty($types)) {

        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );
    }

    // EXECUTE
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return 0;
    }

    // RESULT
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return (int) ($row['total'] ?? 0);
}

// For generating report
function getAllApprovedApprovalIds($date = null, $branch = null, $toDate = null, $search = '')
{
    global $conn;

    if (!isValidApprovedInspectionDateRange($date, $toDate)) {
        return array();
    }

    // Default branch kapag walang ipinasa
    if ($branch === null || $branch === '') {
        $branch = isset($_SESSION['Branch'])
            ? $_SESSION['Branch']
            : 'all';
    }

    $sql = "
        SELECT inspection_checklist_tbl.inspect_id
        FROM inspection_checklist_tbl

        WHERE 1 = 1
    ";

    $types = "";
    $params = array();

    if (!empty($date)) {
        $sql .= " AND inspection_checklist_tbl.date_inspected >= ? ";
        $types .= "s";
        $params[] = $date;
    }
    if (!empty($toDate)) {
        $sql .= " AND inspection_checklist_tbl.date_inspected < DATE_ADD(?, INTERVAL 1 DAY) ";
        $types .= "s";
        $params[] = $toDate;
    }

    // BRANCH FILTER
    if ($branch !== 'all' && !empty($branch)) {

        $sql .= "
            AND LOWER(TRIM(inspection_checklist_tbl.branch))
                = LOWER(TRIM(?))
        ";

        $types .= "s";
        $params[] = $branch;
    }

    if ($search !== '') {
        $sql .= " AND (inspection_checklist_tbl.extinguisher_code LIKE ? OR inspection_checklist_tbl.location LIKE ?) ";
        $types .= "ss";
        $keyword = '%' . $search . '%';
        $params[] = $keyword;
        $params[] = $keyword;
    }

    // GET ONLY THE LATEST INSPECTION
    // PER EXTINGUISHER CODE
    $sql .= "
        AND NOT EXISTS (
            SELECT 1
            FROM inspection_checklist_tbl AS newer

            WHERE newer.extinguisher_code =
                  inspection_checklist_tbl.extinguisher_code
              AND LOWER(TRIM(newer.branch)) =
                  LOWER(TRIM(inspection_checklist_tbl.branch))

            AND (
                newer.date_inspected >
                inspection_checklist_tbl.date_inspected

                OR (

                    newer.date_inspected =
                    inspection_checklist_tbl.date_inspected

                    AND newer.inspect_id >
                    inspection_checklist_tbl.inspect_id

                )
            )
    ";

    if (!empty($date)) {
        $sql .= " AND newer.date_inspected >= ? ";
        $types .= "s";
        $params[] = $date;
    }
    if (!empty($toDate)) {
        $sql .= " AND newer.date_inspected < DATE_ADD(?, INTERVAL 1 DAY) ";
        $types .= "s";
        $params[] = $toDate;
    }

    // CLOSE NOT EXISTS
    $sql .= " ) ";

    // APPROVED ONLY
    $sql .= " AND inspection_checklist_tbl.evaluation_status = 'Approved' ";

    // ORDER
    $sql .= "
        ORDER BY
            inspection_checklist_tbl.date_inspected DESC,
            inspection_checklist_tbl.inspect_id DESC
    ";

    // PREPARE
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return array();
    }

    // BIND PARAMETERS
    if (!empty($types)) {

        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );
    }

    // EXECUTE
    if (!mysqli_stmt_execute($stmt)) {

        mysqli_stmt_close($stmt);
        return array();
    }

    // RESULT
    $result = mysqli_stmt_get_result($stmt);
    $ids = array();

    while ($row = mysqli_fetch_assoc($result)) {

        $ids[] = (int) $row['inspect_id'];
    }

    mysqli_stmt_close($stmt);
    return $ids;
}

// GET ALL APPROVED APPROVAL - REVISING LOGIC
function getAllApprovedApproval(
    $limit = 10,
    $offset = 0,
    $date = null,
    $branch = 'all',
    $search = '',
    $toDate = null
) {
    global $conn;

    if (!isValidApprovedInspectionDateRange($date, $toDate)) {
        return false;
    }

    $sql = "SELECT *
            FROM inspection_checklist_tbl
            WHERE 1 = 1";

    $types = "";
    $params = array();

    if (!empty($date)) {
        $sql .= " AND date_inspected >= ? ";
        $types .= "s";
        $params[] = $date;
    }
    if (!empty($toDate)) {
        $sql .= " AND date_inspected < DATE_ADD(?, INTERVAL 1 DAY) ";
        $types .= "s";
        $params[] = $toDate;
    }

    // BRANCH FILTER - USE THE ROLE-SCOPED VALUE PROVIDED BY THE CONTROLLER
    if ($branch !== 'all' && $branch !== '') {

        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";

        $types .= "s";
        $params[] = $branch;
    }

    if ($search !== '') {
        $sql .= " AND (extinguisher_code LIKE ? OR location LIKE ?) ";
        $types .= "ss";
        $keyword = '%' . $search . '%';
        $params[] = $keyword;
        $params[] = $keyword;
    }

    // LATEST RECORD PER EXTINGUISHER CODE
    $sql .= "
        AND NOT EXISTS (
            SELECT 1
            FROM inspection_checklist_tbl AS newer

            WHERE newer.extinguisher_code =
                  inspection_checklist_tbl.extinguisher_code
              AND LOWER(TRIM(newer.branch)) =
                  LOWER(TRIM(inspection_checklist_tbl.branch))

            AND (
                newer.date_inspected >
                inspection_checklist_tbl.date_inspected

                OR (
                    newer.date_inspected =
                    inspection_checklist_tbl.date_inspected

                    AND newer.inspect_id >
                    inspection_checklist_tbl.inspect_id
                )
            )
    ";

    if (!empty($date)) {
        $sql .= " AND newer.date_inspected >= ? ";
        $types .= "s";
        $params[] = $date;
    }
    if (!empty($toDate)) {
        $sql .= " AND newer.date_inspected < DATE_ADD(?, INTERVAL 1 DAY) ";
        $types .= "s";
        $params[] = $toDate;
    }

    // CLOSE NOT EXISTS
    $sql .= " ) ";

    // STATUS
    $sql .= " AND evaluation_status = 'Approved' ";

    // PAGINATION
    $sql .= "
        ORDER BY date_inspected DESC, inspect_id DESC
        LIMIT ? OFFSET ?
    ";

    $types .= "ii";

    $params[] = (int) $limit;
    $params[] = (int) $offset;

    // PREPARE
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    // BIND PARAMETERS
    if (!empty($types)) {

        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );
    }

    // EXECUTE
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return false;
    }

    // RESULT
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}


// FOR COUNT ALL APPROVED APPROVAL
function getApprovedApprovalCount(
    $date = null,
    $branch = 'all',
    $search = '',
    $toDate = null
) {
    global $conn;

    if (!isValidApprovedInspectionDateRange($date, $toDate)) {
        return 0;
    }

    $sql = "SELECT COUNT(*) AS total
            FROM inspection_checklist_tbl
            WHERE 1 = 1";

    $types = "";
    $params = array();

    if (!empty($date)) {
        $sql .= " AND date_inspected >= ? ";
        $types .= "s";
        $params[] = $date;
    }
    if (!empty($toDate)) {
        $sql .= " AND date_inspected < DATE_ADD(?, INTERVAL 1 DAY) ";
        $types .= "s";
        $params[] = $toDate;
    }

    // BRANCH FILTER
    if ($branch !== 'all' && $branch !== '') {

        $sql .= "
            AND LOWER(TRIM(branch)) = LOWER(TRIM(?))
        ";

        $types .= "s";
        $params[] = $branch;
    }

    if ($search !== '') {
        $sql .= " AND (extinguisher_code LIKE ? OR location LIKE ?) ";
        $types .= "ss";
        $keyword = '%' . $search . '%';
        $params[] = $keyword;
        $params[] = $keyword;
    }

    // LATEST RECORD PER EXTINGUISHER CODE
    $sql .= "
        AND NOT EXISTS (
            SELECT 1
            FROM inspection_checklist_tbl AS newer

            WHERE newer.extinguisher_code =
                  inspection_checklist_tbl.extinguisher_code
              AND LOWER(TRIM(newer.branch)) =
                  LOWER(TRIM(inspection_checklist_tbl.branch))

            AND (
                newer.date_inspected >
                inspection_checklist_tbl.date_inspected

                OR (
                    newer.date_inspected =
                    inspection_checklist_tbl.date_inspected

                    AND newer.inspect_id >
                    inspection_checklist_tbl.inspect_id
                )
            )
    ";

    if (!empty($date)) {
        $sql .= " AND newer.date_inspected >= ? ";
        $types .= "s";
        $params[] = $date;
    }
    if (!empty($toDate)) {
        $sql .= " AND newer.date_inspected < DATE_ADD(?, INTERVAL 1 DAY) ";
        $types .= "s";
        $params[] = $toDate;
    }

    // CLOSE NOT EXISTS
    $sql .= " ) ";

    // STATUS
    $sql .= " AND evaluation_status = 'Approved' ";

    // PREPARE
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return 0;
    }

    // BIND PARAMETERS
    if (!empty($types)) {

        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );
    }

    // EXECUTE
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return 0;
    }

    // RESULT
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return (int) ($row['total'] ?? 0);
}

// GET ALL REJECTED APPROVAL
function getAllRejectedApproval(
    $limit = 10,
    $offset = 0,
    $date = null,
    $branch = 'all',
    $search = ''
) {
    global $conn;

    $sql = "SELECT *
            FROM inspection_checklist_tbl
            WHERE 1 = 1";

    $types = "";
    $params = [];

    // DATE FILTER - SELECTED DATE ONLY

    if (!empty($date)) {

        $sql .= " AND date_inspected >= ? AND date_inspected < DATE_ADD(?, INTERVAL 1 DAY) ";
        $types .= "ss";
        $params[] = $date;
        $params[] = $date;
    }

    // ========================================================
    // BRANCH FILTER
    // ========================================================

    if ($branch !== 'all' && !empty($branch)) {

        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
        $types .= "s";
        $params[] = $branch;
    }

    if ($search !== '') {
        $sql .= " AND (extinguisher_code LIKE ? OR location LIKE ?) ";
        $types .= "ss";
        $keyword = '%' . $search . '%';
        $params[] = $keyword;
        $params[] = $keyword;
    }

    // LATEST RECORD PER EXTINGUISHER CODE
    // WITHIN THE SELECTED DATE

    $sql .= "
        AND NOT EXISTS (
            SELECT 1
            FROM inspection_checklist_tbl AS newer

            WHERE newer.extinguisher_code =
                  inspection_checklist_tbl.extinguisher_code
              AND LOWER(TRIM(newer.branch)) =
                  LOWER(TRIM(inspection_checklist_tbl.branch))

            AND (
                newer.date_inspected >
                inspection_checklist_tbl.date_inspected

                OR (
                    newer.date_inspected =
                    inspection_checklist_tbl.date_inspected

                    AND newer.inspect_id >
                    inspection_checklist_tbl.inspect_id
                )
            )
    ";

    // NEWER RECORD MUST ALSO BE WITHIN SELECTED DATE
    if (!empty($date)) {

        $sql .= " AND newer.date_inspected >= ? AND newer.date_inspected < DATE_ADD(?, INTERVAL 1 DAY) ";

        $types .= "ss";
        $params[] = $date;
        $params[] = $date;
    }

    $sql .= " ) ";

    // STATUS
    $sql .= " AND evaluation_status = 'Rejected' ";

    // PAGINATION
    $sql .= "
        ORDER BY date_inspected DESC, inspect_id DESC
        LIMIT ? OFFSET ?
    ";

    $types .= "ii";
    $params[] = (int) $limit;
    $params[] = (int) $offset;

    // PREPARE
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    // BIND PARAMETERS
    if (!empty($types)) {

        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );
    }

    // EXECUTE
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return false;
    }

    // RESULT
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}


// GET REJECTED APPROVAL COUNT
function getRejectedApprovalCount(
    $date = null,
    $branch = 'all',
    $search = ''
) {
    global $conn;

    $sql = "SELECT COUNT(*) AS total
            FROM inspection_checklist_tbl
            WHERE 1 = 1";

    $types = "";
    $params = [];

    // DATE FILTER - SELECTED DATE ONLY
    if (!empty($date)) {

        $sql .= " AND date_inspected >= ? AND date_inspected < DATE_ADD(?, INTERVAL 1 DAY) ";
        $types .= "ss";
        $params[] = $date;
        $params[] = $date;
    }

    // BRANCH FILTER
    if ($branch !== 'all' && !empty($branch)) {

        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
        $types .= "s";
        $params[] = $branch;
    }

    if ($search !== '') {
        $sql .= " AND (extinguisher_code LIKE ? OR location LIKE ?) ";
        $types .= "ss";
        $keyword = '%' . $search . '%';
        $params[] = $keyword;
        $params[] = $keyword;
    }

    // LATEST RECORD PER EXTINGUISHER CODE
    // WITHIN THE SELECTED DATE

    $sql .= "
        AND NOT EXISTS (
            SELECT 1
            FROM inspection_checklist_tbl AS newer

            WHERE newer.extinguisher_code =
                  inspection_checklist_tbl.extinguisher_code
              AND LOWER(TRIM(newer.branch)) =
                  LOWER(TRIM(inspection_checklist_tbl.branch))

            AND (
                newer.date_inspected >
                inspection_checklist_tbl.date_inspected

                OR (
                    newer.date_inspected =
                    inspection_checklist_tbl.date_inspected

                    AND newer.inspect_id >
                    inspection_checklist_tbl.inspect_id
                )
            )
    ";

    // NEWER RECORD MUST ALSO BE WITHIN SELECTED DATE
    if (!empty($date)) {

        $sql .= " AND newer.date_inspected >= ? AND newer.date_inspected < DATE_ADD(?, INTERVAL 1 DAY) ";
        $types .= "ss";
        $params[] = $date;
        $params[] = $date;
    }

    $sql .= " ) ";

    // STATUS
    $sql .= " AND evaluation_status = 'Rejected' ";

    // PREPARE
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return 0;
    }

    // BIND PARAMETERS
    if (!empty($types)) {

        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );
    }

    // EXECUTE
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return 0;
    }

    // RESULT
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return (int) ($row['total'] ?? 0);
}

// FOR UPDATE EVALUATION STATUS
function updateEvaluationStatus($id, $evaluation_status)
{
    global $conn;

    $branch = getBranch();

    if (empty($branch)) {
        return false;
    }

    $sql = "UPDATE inspection_checklist_tbl
            SET evaluation_status = ?,
                rejection_date = CASE
                    WHEN LOWER(TRIM(?)) = 'rejected'
                    THEN NOW()
                    ELSE NULL
                END
            WHERE inspect_id = ?
              AND LOWER(TRIM(branch)) = LOWER(TRIM(?))";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ssis",
        $evaluation_status,
        $evaluation_status,
        $id,
        $branch
    );

    $result = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $result;
}

function Approved_by($name, $id)
{
    global $conn;

    $sql = "
        UPDATE inspection_checklist_tbl
        SET verified_and_approved_by = ?
        WHERE inspect_id = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("si", $name, $id);
    return $stmt->execute();
}


function Rejected_by($name, $id)
{
    global $conn;

    $sql = "
        UPDATE inspection_checklist_tbl
        SET rejected_by = ?
        WHERE inspect_id = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("si", $name, $id);

    return $stmt->execute();
}





function updateCorrectiveAction(
    $inspect_id,
    $action_taken,
    $target_date,
    $rejection_reason
) {
    global $conn;

    $branch = getCurrentBranch();

    if (empty($branch)) {
        return false;
    }

    $sql = "UPDATE inspection_checklist_tbl
            SET
                action_taken = ?,
                target_date_of_implementation = ?,
                reject_reason = ?
            WHERE inspect_id = ?
              AND LOWER(TRIM(branch)) = LOWER(TRIM(?))";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sssis",
        $action_taken,
        $target_date,
        $rejection_reason,
        $inspect_id,
        $branch
    );

    $success = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $success;
}

function updateFireExtinguisherExpiryDate($exp_date, $code)
{
    global $conn;



    $sql = "UPDATE fire_extinguishers_tbl
            SET expiration_date = ?
            WHERE extinguisher_code = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $exp_date,
        $code
    );

    $success = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $success;
}

// GET APPROVED INSPECTION REPORTS
// EXACT RECORDS FROM APPROVED LIST
function getApprovedInspectionReports($ids)
{
    global $conn;

    if (empty($ids)) {
        return [];
    }

    // Make sure IDs are integers
    $ids = array_map('intval', $ids);

    // Remove duplicates
    $ids = array_values(array_unique($ids));

    if (empty($ids)) {
        return [];
    }

    // CREATE PLACEHOLDERS
    $placeholders = implode(
        ',',
        array_fill(0, count($ids), '?')
    );

    // QUERY
    $sql = "
        SELECT
            i.inspect_id,
            i.extinguisher_code,
            i.location,
            i.capacity,
            i.type,
            i.class,

            i.date_inspected,
            i.inspected_by,
            i.verified_and_approved_by,

            i.action_taken,
            i.target_date_of_implementation,

            f.remarks AS extinguisher_remarks,

            i.is_seal_ok,
            i.is_pin_ok,
            i.is_pressure_ok,
            i.is_hose_ok,
            i.is_nozzle_ok,
            i.is_belt_ok,
            i.is_cylinder_body_ok,
            i.is_demarcation_line_ok,
            i.is_signage_ok,
            i.is_cleaning_of_unit_ok,

            i.status,
            i.evaluation_status,
            i.branch

        FROM inspection_checklist_tbl AS i

        LEFT JOIN fire_extinguishers_tbl AS f
            ON TRIM(f.extinguisher_code) =
            TRIM(i.extinguisher_code)
        AND TRIM(f.branch) = TRIM(i.branch)
        AND f.archived = 0

        WHERE i.inspect_id IN ($placeholders)

        AND i.evaluation_status = 'Approved'

        ORDER BY
            i.date_inspected ASC,
            i.inspect_id ASC
    ";

    // PREPARE
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return [];
    }

    // BIND IDS
    $types = str_repeat('i', count($ids));

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$ids
    );

    // EXECUTE
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return [];
    }

    // GET RESULT
    $result = mysqli_stmt_get_result($stmt);
    $inspections = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $inspections[] = $row;
    }

    mysqli_stmt_close($stmt);
    return $inspections;
}


function updateFireExtinguisherStatus($extinguisher_code, $status, $remarks)
{
    global $conn;

    $sql = "UPDATE fire_extinguishers_tbl
            SET condition_status = ?,
                remarks = ?
            WHERE extinguisher_code = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $status,
        $remarks,
        $extinguisher_code
    );

    $success = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $success;
}

// get fire extinguisher for inventory report
function getFireExtinguishersForInventoryReport($ids)
{
    global $conn;

    if (empty($ids)) {
        return [];
    }

    $placeholders = implode(
        ',',
        array_fill(0, count($ids), '?')
    );

    $sql = "
        SELECT
            extinguisher_code,
            location,
            capacity,
            type,
            class,
            manufactured_date,
            placement,
            condition_status,
            remarks,
            branch

        FROM fire_extinguishers_tbl

        WHERE extinguisher_id IN ($placeholders)
          AND archived = 0

        ORDER BY extinguisher_id ASC
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return [];
    }

    $types = str_repeat('i', count($ids));

    $ids = array_map('intval', $ids);

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$ids
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $extinguishers = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $extinguishers[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $extinguishers;
}
