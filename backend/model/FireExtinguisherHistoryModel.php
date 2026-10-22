
<?php
require_once __DIR__ . '/../config/db.php';

function resolveFireExtinguisherHistoryBranch($branch = null)
{
    $role = strtolower(trim($_SESSION['Role'] ?? ''));

    if ($role === 'inspector') {
        $sessionBranch = isset($_SESSION['Branch']) && is_string($_SESSION['Branch'])
            ? trim($_SESSION['Branch'])
            : '';

        return ($sessionBranch !== '' && strtolower($sessionBranch) !== 'all')
            ? $sessionBranch
            : '';
    }

    if ($role === 'admin') {
        if ($branch === null || $branch === '') {
            $sessionBranch = isset($_SESSION['Branch']) && is_string($_SESSION['Branch'])
                ? trim($_SESSION['Branch'])
                : '';
            return $sessionBranch !== '' ? $sessionBranch : 'all';
        }

        return is_string($branch) ? trim($branch) : '';
    }

    return '';
}


function getFireExtinguisherHistoryDetails($code, $branch = null)
{
    global $conn;

    $branch = resolveFireExtinguisherHistoryBranch($branch);
    if ($branch === '') {
        return null;
    }

    $sql = "
        SELECT *
        FROM fire_extinguishers_tbl
        WHERE extinguisher_code = ?
          AND archived = 0
    ";

    if ($branch !== null && $branch !== 'all') {
        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }
    $sql .= " LIMIT 1 ";

    $stmt = $conn->prepare($sql);
    if ($branch !== null && $branch !== 'all') {
        $stmt->bind_param("ss", $code, $branch);
    } else {
        $stmt->bind_param("s", $code);
    }
    $stmt->execute();

    $result = $stmt->get_result();
    $details = $result->fetch_assoc();


    $stmt->close();

    return $details ?: null;
}



function getFireExtinguisherInspectionHistory(
    $code,
    $dateFrom,
    $dateTo,
    $branch = null
) {
    global $conn;

    $branch = resolveFireExtinguisherHistoryBranch($branch);
    if ($branch === '') {
        return [];
    }

    if ($code !== '') {
        $sql = "
            SELECT *
            FROM inspection_checklist_tbl
            WHERE extinguisher_code = ?
              AND date_inspected >= ?
              AND date_inspected < DATE_ADD(?, INTERVAL 1 DAY)
        ";

        if ($branch !== null && $branch !== 'all') {
            $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
        }
        $sql .= " ORDER BY date_inspected ASC ";

        $stmt = $conn->prepare($sql);
        if ($branch !== null && $branch !== 'all') {
            $stmt->bind_param("ssss", $code, $dateFrom, $dateTo, $branch);
        } else {
            $stmt->bind_param("sss", $code, $dateFrom, $dateTo);
        }
    } else {
        $sql = "
            SELECT *
            FROM inspection_checklist_tbl
            WHERE date_inspected >= ?
              AND date_inspected < DATE_ADD(?, INTERVAL 1 DAY)
        ";

        if ($branch !== null && $branch !== 'all') {
            $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
        }
        $sql .= " ORDER BY date_inspected ASC ";

        $stmt = $conn->prepare($sql);
        if ($branch !== null && $branch !== 'all') {
            $stmt->bind_param("sss", $dateFrom, $dateTo, $branch);
        } else {
            $stmt->bind_param("ss", $dateFrom, $dateTo);
        }
    }

    $stmt->execute();

    $result = $stmt->get_result();
    $records = [];

    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    $stmt->close();


    return $records;
}





function getFireExtinguisherHistoryTimeline($records)
{
    $timeline = [];

    foreach ($records as $record) {
        $evaluationStatus = strtolower(
            trim($record['evaluation_status'] ?? '')
        );

        $inspectionDate = $record['date_inspected'] ?? null;
        $inspector = $record['inspected_by'] ?? 'Unknown';
        $remarks = $record['remarks'] ?? '';

        // Inspection submitted
        $timeline[] = [
            'date' => $inspectionDate,
            'title' => 'Inspection Submitted',
            'description' => 'Inspected by: ' . $inspector,
            'status' => 'completed',
            'reason' => $remarks
        ];

        // Rejected
        if ($evaluationStatus === 'rejected') {
            $timeline[] = [
                'date' => $record['rejection_date'] ?? $inspectionDate,
                'title' => 'Inspection Rejected',
                'description' => 'Inspection was rejected.',
                'status' => 'rejected',
                'reason' => $record['reject_reason'] ?? ''
            ];
        }

        // Pending approval
        if ($evaluationStatus === 'pending') {
            $timeline[] = [
                'date' => $inspectionDate,
                'title' => 'Pending Approval',
                'description' => 'Waiting for approval.',
                'status' => 'pending',
                'reason' => $remarks
            ];
        }

        // Approved
        if ($evaluationStatus === 'approved') {
            $timeline[] = [
                'date' => $inspectionDate,
                'title' => 'Inspection Approved',
                'description' => 'Inspection approved.',
                'status' => 'approved',
                'reason' => $remarks
            ];
        }
    }

    usort($timeline, function ($a, $b) {
        return strtotime($a['date'] ?? '')
            <=> strtotime($b['date'] ?? '');
    });

  

    return $timeline;
}


function getAllFireExtinguisherCodes($branch = null)
{
    global $conn;

    $branch = resolveFireExtinguisherHistoryBranch($branch);
    if ($branch === '') {
        return [];
    }

    $sql = "
        SELECT extinguisher_code
        FROM fire_extinguishers_tbl
        WHERE archived = 0
    ";

    if ($branch !== null && $branch !== 'all') {
        $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?)) ";
    }
    $sql .= " ORDER BY extinguisher_code ASC ";

    if ($branch !== null && $branch !== 'all') {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $branch);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
        $stmt = null;
    }

    $codes = [];

    while ($row = $result->fetch_assoc()) {
        $codes[] = $row['extinguisher_code'];
    }

    if ($stmt !== null) {
        $stmt->close();
    }

    return $codes;
}
