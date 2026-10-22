
<?php

require_once __DIR__ . '/../../backend/config/db.php';

function getApprovedInspectionReport($ids)
{
    global $conn;

    if (empty($ids)) {
        return array();
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sql = "
        SELECT
            inspect_id,
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
            remarks,
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
            evaluation_status,
            branch
        FROM inspection_checklist_tbl
        WHERE inspect_id IN ($placeholders)
          AND evaluation_status = ?
        ORDER BY date_inspected DESC
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log('Report prepare error: ' . mysqli_error($conn));
        return false;
    }

    $types = str_repeat('i', count($ids)) . 's';
    $params = array_merge($ids, array('Approved'));
    $bindParams = array($types);

    foreach ($params as $key => $value) {
        $bindParams[] = &$params[$key];
    }

    if (!call_user_func_array(
        'mysqli_stmt_bind_param',
        array_merge(array($stmt), $bindParams)
    )) {
        error_log('Report bind error: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    if (!mysqli_stmt_execute($stmt)) {
        error_log('Report execute error: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    // Requires mysqlnd on the server.
    $result = mysqli_stmt_get_result($stmt);

    if ($result === false) {
        error_log('Report result error: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    $rows = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_free_result($result);
    mysqli_stmt_close($stmt);

    return $rows;
}
