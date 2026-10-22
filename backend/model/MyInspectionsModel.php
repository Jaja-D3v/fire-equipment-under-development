<?php

require_once __DIR__ . '/../config/db.php';

$branch = $_SESSION['Branch'];

/**
 * Build shared filters for listing and counting.
 * All data comes from inspection_checklist_tbl only.
 */
function myInspectionsBuildFilters(
    string $inspectorName,
    string $search,
    string $dateFilter,
    string $statusFilter
): array {
    global $branch;

    $where = [
        'i.inspected_by = ?',
        'i.branch = ?'
    ];

    $params = [$inspectorName, $branch];
    $types = 'ss';

    if ($search !== '') {
        $where[] = "(
            i.extinguisher_code LIKE ?
            OR i.evaluation_status LIKE ?
            OR i.inspected_by LIKE ?
        )";

        $searchTerm = '%' . $search . '%';

        for ($x = 0; $x < 3; $x++) {
            $params[] = $searchTerm;
            $types .= 's';
        }
    }

    switch ($dateFilter) {
        case 'today':
            $where[] = 'DATE(i.date_inspected) = CURDATE()';
            break;

        case 'yesterday':
            $where[] = 'DATE(i.date_inspected) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)';
            break;

        case '7':
            $where[] = 'i.date_inspected >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
            $where[] = 'i.date_inspected <= NOW()';
            break;

        case '30':
            $where[] = 'i.date_inspected >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
            $where[] = 'i.date_inspected <= NOW()';
            break;
    }

    if ($statusFilter !== 'all') {
        $where[] = 'i.evaluation_status = ?';
        $params[] = $statusFilter;
        $types .= 's';
    }

    return [
        'sql' => implode(' AND ', $where),
        'types' => $types,
        'params' => $params,
    ];
}

/**
 * Bind parameters dynamically.
 */
function myInspectionsBindParams(
    mysqli_stmt $stmt,
    string $types,
    array &$params
): bool {
    if ($types === '' || empty($params)) {
        return true;
    }

    $bindValues = [$types];

    foreach ($params as &$value) {
        $bindValues[] = &$value;
    }

    unset($value);

    return call_user_func_array(
        [$stmt, 'bind_param'],
        $bindValues
    );
}

/**
 * Get paginated inspection records.
 */

function getMyInspectionsModel(
    string $inspectorName,
    string $search,
    string $dateFilter,
    string $statusFilter,
    int $limit,
    int $offset
): array {
    global $conn, $branch;

    $filters = myInspectionsBuildFilters(
        $inspectorName,
        $search,
        $dateFilter,
        $statusFilter
    );

    $sql = "
        SELECT i.*
        FROM inspection_checklist_tbl AS i
        WHERE i.inspected_by = ?
          AND i.branch = ?

          -- Only the latest inspection per extinguisher is eligible.
          AND NOT EXISTS (
              SELECT 1
              FROM inspection_checklist_tbl AS newer
              WHERE newer.extinguisher_code = i.extinguisher_code
                AND newer.branch = i.branch
                AND (
                    newer.date_inspected > i.date_inspected
                    OR (
                        newer.date_inspected = i.date_inspected
                        AND newer.inspect_id > i.inspect_id
                    )
                )
          )

          -- Exclude the extinguisher if its latest inspection is rejected.
          AND i.evaluation_status IN ('Pending', 'Approved')

          -- Apply search, date, and status filters to the latest record.
          AND {$filters['sql']}

        ORDER BY i.date_inspected DESC, i.inspect_id DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log(
            'My Inspections prepare failed: ' . mysqli_error($conn)
        );
        return [];
    }

    $params = array_merge(
        [$inspectorName, $branch],
        $filters['params'],
        [$limit, $offset]
    );

    $types = 'ss' . $filters['types'] . 'ii';

    if (!myInspectionsBindParams($stmt, $types, $params)) {
        error_log(
            'My Inspections bind failed: ' . mysqli_stmt_error($stmt)
        );
        mysqli_stmt_close($stmt);
        return [];
    }

    if (!mysqli_stmt_execute($stmt)) {
        error_log(
            'My Inspections query failed: ' . mysqli_stmt_error($stmt)
        );
        mysqli_stmt_close($stmt);
        return [];
    }

    $result = mysqli_stmt_get_result($stmt);
    $records = $result
        ? mysqli_fetch_all($result, MYSQLI_ASSOC)
        : [];

    mysqli_stmt_close($stmt);

    return $records;
}

/**
 * Count records using the same filters as the listing.
 */
function getMyInspectionsCountModel(
    string $inspectorName,
    string $search,
    string $dateFilter,
    string $statusFilter
): int {
    global $conn, $branch;

    $filters = myInspectionsBuildFilters(
        $inspectorName,
        $search,
        $dateFilter,
        $statusFilter
    );

    $sql = "
        SELECT COUNT(*) AS total
        FROM inspection_checklist_tbl AS i
        WHERE i.inspected_by = ?
          AND i.branch = ?

          -- Use the same latest-inspection rule as the listing query.
          AND NOT EXISTS (
              SELECT 1
              FROM inspection_checklist_tbl AS newer
              WHERE newer.extinguisher_code = i.extinguisher_code
                AND newer.branch = i.branch
                AND (
                    newer.date_inspected > i.date_inspected
                    OR (
                        newer.date_inspected = i.date_inspected
                        AND newer.inspect_id > i.inspect_id
                    )
                )
          )

          -- Exclude latest rejected inspections.
          AND i.evaluation_status IN ('Pending', 'Approved')

          -- Same filters as the listing query.
          AND {$filters['sql']}
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log(
            'My Inspections count prepare failed: ' . mysqli_error($conn)
        );
        return 0;
    }

    $params = array_merge(
        [$inspectorName, $branch],
        $filters['params']
    );

    $types = 'ss' . $filters['types'];

    if (!myInspectionsBindParams($stmt, $types, $params)) {
        error_log(
            'My Inspections count bind failed: ' . mysqli_stmt_error($stmt)
        );
        mysqli_stmt_close($stmt);
        return 0;
    }

    if (!mysqli_stmt_execute($stmt)) {
        error_log(
            'My Inspections count query failed: ' . mysqli_stmt_error($stmt)
        );
        mysqli_stmt_close($stmt);
        return 0;
    }

    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;

    mysqli_stmt_close($stmt);

    return (int) ($row['total'] ?? 0);
}




/**
 * Summary counts for the logged-in inspector.
 */
function getMyInspectionsSummaryModel(string $inspectorName): array
{
    global $conn;

    $branch = $_SESSION['Branch'] ?? '';

    $summary = [
        'total' => 0,
        'pending' => 0,
        'approved' => 0,
    ];

    $sql = "
        SELECT
            COUNT(*) AS total,
            COALESCE(
                SUM(evaluation_status = 'Pending'), 0
            ) AS pending,
            COALESCE(
                SUM(evaluation_status = 'Approved'), 0
            ) AS approved
        FROM inspection_checklist_tbl
        WHERE inspected_by = ?
          AND branch = ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log(
            'My Inspections summary prepare failed: ' . mysqli_error($conn)
        );
        return $summary;
    }

    mysqli_stmt_bind_param($stmt, 'ss', $inspectorName, $branch);

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $row = $result ? mysqli_fetch_assoc($result) : null;

        if ($row) {
            $summary = [
                'total' => (int) $row['total'],
                'pending' => (int) $row['pending'],
                'approved' => (int) $row['approved'],
            ];
        }
    } else {
        error_log(
            'My Inspections summary query failed: ' . mysqli_stmt_error($stmt)
        );
    }

    mysqli_stmt_close($stmt);

    return $summary;
}
