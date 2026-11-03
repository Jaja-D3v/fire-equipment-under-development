<?php
require_once __DIR__ . '/../config/db_user.php';
$superAdminIdentityDb = $conn;
require_once __DIR__ . '/../config/db.php';
$superAdminOperationalDb = $conn;

function superAdminRunSelect($connection, $sql, $types = '', $params = array())
{
    $stmt = mysqli_prepare($connection, $sql);
    if (!$stmt) {
        return array();
    }
    if ($types !== '') {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return array();
    }
    $result = mysqli_stmt_get_result($stmt);
    $rows = array();
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function superAdminDateRange($range, $from, $to)
{
    $today = new DateTimeImmutable('today');
    switch ($range) {
        case 'today':
            return array($today->format('Y-m-d'), $today->modify('+1 day')->format('Y-m-d'));
        case '7d':
            return array($today->modify('-6 days')->format('Y-m-d'), $today->modify('+1 day')->format('Y-m-d'));
        case '30d':
            return array($today->modify('-29 days')->format('Y-m-d'), $today->modify('+1 day')->format('Y-m-d'));
        case 'previous_month':
            $first = $today->modify('first day of previous month');
            return array($first->format('Y-m-d'), $first->modify('first day of next month')->format('Y-m-d'));
        case 'custom':
            $fromDate = DateTime::createFromFormat('!Y-m-d', $from);
            $toDate = DateTime::createFromFormat('!Y-m-d', $to);
            if ($fromDate && $toDate && $fromDate->format('Y-m-d') === $from && $toDate->format('Y-m-d') === $to && $from <= $to) {
                return array($from, $toDate->modify('+1 day')->format('Y-m-d'));
            }
            return array($today->modify('-29 days')->format('Y-m-d'), $today->modify('+1 day')->format('Y-m-d'));
        case 'this_month':
        default:
            return array($today->modify('first day of this month')->format('Y-m-d'), $today->modify('+1 day')->format('Y-m-d'));
    }
}

function getSuperAdminOverview($start, $end)
{
    global $superAdminIdentityDb, $superAdminOperationalDb;
    $userRows = superAdminRunSelect(
        $superAdminIdentityDb,
        'SELECT COUNT(*) AS total FROM kane_users_login WHERE what_system = ?',
        's',
        array('KPFEMS')
    );
    $activityRows = superAdminRunSelect(
        $superAdminOperationalDb,
        'SELECT COUNT(DISTINCT user_name) AS total FROM activity_logs_tbl WHERE created_at >= ? AND created_at < ? AND user_name IN (SELECT EmployeeName FROM kp201.kane_users_login WHERE what_system = ?)',
        'sss',
        array($start, $end, 'KPFEMS')
    );
    $inspectionRows = superAdminRunSelect(
        $superAdminOperationalDb,
        'SELECT COUNT(*) AS total FROM inspection_checklist_tbl WHERE date_inspected >= ? AND date_inspected < ?',
        'ss',
        array($start, $end)
    );
    $cskRows = superAdminRunSelect(
        $superAdminOperationalDb,
        'SELECT COUNT(*) AS total FROM chemical_spill_kit_inspection_tbl WHERE inspection_date >= ? AND inspection_date < ?',
        'ss',
        array($start, $end)
    );
    $branchRows = superAdminRunSelect(
        $superAdminOperationalDb,
        'SELECT COUNT(DISTINCT branch) AS total FROM (SELECT branch FROM inspection_checklist_tbl WHERE date_inspected >= ? AND date_inspected < ? UNION SELECT branch FROM chemical_spill_kit_inspection_tbl WHERE inspection_date >= ? AND inspection_date < ?) activity_branches',
        'ssss',
        array($start, $end, $start, $end)
    );
    return array(
        'registered_users' => isset($userRows[0]['total']) ? (int)$userRows[0]['total'] : null,
        'active_users' => isset($activityRows[0]['total']) ? (int)$activityRows[0]['total'] : null,
        'fire_inspections' => isset($inspectionRows[0]['total']) ? (int)$inspectionRows[0]['total'] : null,
        'csk_inspections' => isset($cskRows[0]['total']) ? (int)$cskRows[0]['total'] : null,
        'active_branches' => isset($branchRows[0]['total']) ? (int)$branchRows[0]['total'] : null
    );
}

function getSuperAdminActivity($start, $end, $page, $limit, $search, $user, $action, $module)
{
    global $superAdminOperationalDb;
    $where = 'WHERE created_at >= ? AND created_at < ? AND user_name IN (SELECT EmployeeName FROM kp201.kane_users_login WHERE what_system = ?)';
    $types = 'sss';
    $params = array($start, $end, 'KPFEMS');
    foreach (array('user_name' => $user, 'action' => $action) as $column => $value) {
        if ($value !== '') {
            $where .= " AND $column LIKE ?";
            $types .= 's';
            $params[] = '%' . $value . '%';
        }
    }
    if ($module !== '') {
        $where .= ' AND description LIKE ?';
        $types .= 's';
        $params[] = '%' . $module . '%';
    }
    if ($search !== '') {
        $where .= ' AND (user_name LIKE ? OR action LIKE ? OR description LIKE ?)';
        $types .= 'sss';
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like);
    }
    $offset = ($page - 1) * $limit;
    $count = superAdminRunSelect($superAdminOperationalDb, "SELECT COUNT(*) AS total FROM activity_logs_tbl $where", $types, $params);
    $rows = superAdminRunSelect(
        $superAdminOperationalDb,
        "SELECT user_name, action, description, created_at FROM activity_logs_tbl $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset",
        $types,
        $params
    );
    return array('rows' => $rows, 'total' => isset($count[0]['total']) ? (int)$count[0]['total'] : null);
}

function getSuperAdminBranches($start, $end)
{
    global $superAdminOperationalDb, $superAdminIdentityDb;
    $identities = superAdminRunSelect(
        $superAdminIdentityDb,
        'SELECT j.Location AS branch, COUNT(DISTINCT u.id) AS users FROM kane_users_login u LEFT JOIN kane_jobinfo j ON u.LoginID = j.IDNumber AND j.ID = (SELECT j2.ID FROM kane_jobinfo j2 WHERE j2.IDNumber = u.LoginID ORDER BY j2.EffectiveDate DESC, j2.ID DESC LIMIT 1) WHERE u.what_system = ? GROUP BY j.Location',
        's',
        array('KPFEMS')
    );
    $inspections = superAdminRunSelect(
        $superAdminOperationalDb,
        'SELECT branch, SUM(activity_count) AS inspections, MAX(last_activity) AS last_activity FROM (SELECT branch, COUNT(*) AS activity_count, MAX(date_inspected) AS last_activity FROM inspection_checklist_tbl WHERE date_inspected >= ? AND date_inspected < ? GROUP BY branch UNION ALL SELECT branch, COUNT(*) AS activity_count, MAX(inspection_date) AS last_activity FROM chemical_spill_kit_inspection_tbl WHERE inspection_date >= ? AND inspection_date < ? GROUP BY branch) i GROUP BY branch',
        'ssss',
        array($start, $end, $start, $end)
    );
    $branchMap = array();
    foreach ($identities as $row) {
        $name = trim((string)$row['branch']);
        if ($name !== '') {
            $branchMap[$name] = array('branch' => $name, 'users' => (int)$row['users'], 'activities' => null, 'inspections' => 0, 'last_activity' => null);
        }
    }
    foreach ($inspections as $row) {
        $name = trim((string)$row['branch']);
        if ($name === '') {
            continue;
        }
        if (!isset($branchMap[$name])) {
            $branchMap[$name] = array('branch' => $name, 'users' => null, 'activities' => null, 'inspections' => 0, 'last_activity' => null);
        }
        $branchMap[$name]['inspections'] = (int)$row['inspections'];
        $branchMap[$name]['last_activity'] = $row['last_activity'];
    }
    // Logs have no branch field. Do not attribute them to branches based on employee names.
    $branches = array_values($branchMap);
    usort($branches, function ($left, $right) {
        return (int)$right['inspections'] - (int)$left['inspections'];
    });
    return $branches;
}

function getSuperAdminTopUsers($start, $end)
{
    global $superAdminOperationalDb;
    return superAdminRunSelect(
        $superAdminOperationalDb,
        'SELECT user_name, COUNT(*) AS total FROM activity_logs_tbl WHERE created_at >= ? AND created_at < ? AND user_name IN (SELECT EmployeeName FROM kp201.kane_users_login WHERE what_system = ?) GROUP BY user_name ORDER BY total DESC, user_name ASC LIMIT 10',
        'sss',
        array($start, $end, 'KPFEMS')
    );
}

function getSuperAdminPeriodTrend($start, $end, $period)
{
    global $superAdminOperationalDb;
    $format = $period === 'weekly' ? '%x-W%v' : '%Y-%m';
    $logPeriod = "DATE_FORMAT(created_at, '$format')";
    $firePeriod = "DATE_FORMAT(date_inspected, '$format')";
    $spillPeriod = "DATE_FORMAT(inspection_date, '$format')";
    return superAdminRunSelect(
        $superAdminOperationalDb,
        "SELECT period_key, SUM(log_total) AS log_total, SUM(inspection_total) AS inspection_total FROM (SELECT $logPeriod AS period_key, COUNT(*) AS log_total, 0 AS inspection_total FROM activity_logs_tbl WHERE created_at >= ? AND created_at < ? AND user_name IN (SELECT EmployeeName FROM kp201.kane_users_login WHERE what_system = ?) GROUP BY $logPeriod UNION ALL SELECT $firePeriod AS period_key, 0 AS log_total, COUNT(*) AS inspection_total FROM inspection_checklist_tbl WHERE date_inspected >= ? AND date_inspected < ? GROUP BY $firePeriod UNION ALL SELECT $spillPeriod AS period_key, 0 AS log_total, COUNT(*) AS inspection_total FROM chemical_spill_kit_inspection_tbl WHERE inspection_date >= ? AND inspection_date < ? GROUP BY $spillPeriod) periods GROUP BY period_key ORDER BY period_key",
        'sssssss',
        array($start, $end, 'KPFEMS', $start, $end, $start, $end)
    );
}

function getSuperAdminUsers($start, $end, $page, $limit, $search, $branch, $role)
{
    global $superAdminIdentityDb, $superAdminOperationalDb;
    $where = 'WHERE u.what_system = ?';
    $types = 's';
    $params = array('KPFEMS');
    if ($search !== '') {
        $where .= ' AND (u.EmployeeName LIKE ? OR u.LoginID LIKE ?)';
        $types .= 'ss';
        $like = '%' . $search . '%';
        array_push($params, $like, $like);
    }
    if ($branch !== '') {
        $where .= ' AND j.Location = ?';
        $types .= 's';
        $params[] = $branch;
    }
    if ($role !== '') {
        $where .= ' AND u.Role = ?';
        $types .= 's';
        $params[] = $role;
    }
    $countRows = superAdminRunSelect(
        $superAdminIdentityDb,
        "SELECT COUNT(DISTINCT u.id) AS total FROM kane_users_login u LEFT JOIN kane_jobinfo j ON u.LoginID = j.IDNumber AND j.ID = (SELECT j2.ID FROM kane_jobinfo j2 WHERE j2.IDNumber = u.LoginID ORDER BY j2.EffectiveDate DESC, j2.ID DESC LIMIT 1) $where",
        $types,
        $params
    );
    $offset = ($page - 1) * $limit;
    $users = superAdminRunSelect(
        $superAdminIdentityDb,
        "SELECT u.EmployeeName AS employee_name, u.LoginID AS login_id, u.Role AS role, j.Location AS branch, u.last_login FROM kane_users_login u LEFT JOIN kane_jobinfo j ON u.LoginID = j.IDNumber AND j.ID = (SELECT j2.ID FROM kane_jobinfo j2 WHERE j2.IDNumber = u.LoginID ORDER BY j2.EffectiveDate DESC, j2.ID DESC LIMIT 1) $where ORDER BY u.EmployeeName ASC LIMIT $limit OFFSET $offset",
        $types,
        $params
    );
    if (!$users) {
        return array('rows' => array(), 'total' => isset($countRows[0]['total']) ? (int)$countRows[0]['total'] : null);
    }
    $duplicateNames = superAdminRunSelect(
        $superAdminIdentityDb,
        'SELECT EmployeeName AS employee_name FROM kane_users_login WHERE what_system = ? GROUP BY EmployeeName HAVING COUNT(DISTINCT id) > 1',
        's',
        array('KPFEMS')
    );
    $ambiguousNames = array();
    foreach ($duplicateNames as $duplicate) {
        $ambiguousNames[(string)$duplicate['employee_name']] = true;
    }
    $names = array();
    foreach ($users as $user) {
        $names[(string)$user['employee_name']] = true;
    }
    $names = array_keys($names);
    $logMap = array();
    if ($names) {
        $placeholders = implode(',', array_fill(0, count($names), '?'));
        $logRows = superAdminRunSelect(
            $superAdminOperationalDb,
            "SELECT user_name, COUNT(*) AS total, MAX(created_at) AS last_activity FROM activity_logs_tbl WHERE created_at >= ? AND created_at < ? AND user_name IN ($placeholders) AND user_name IN (SELECT EmployeeName FROM kp201.kane_users_login WHERE what_system = ?) GROUP BY user_name",
            'ss' . str_repeat('s', count($names)) . 's',
            array_merge(array($start, $end), $names, array('KPFEMS'))
        );
        foreach ($logRows as $row) {
            $logMap[(string)$row['user_name']] = $row;
        }
    }
    foreach ($users as &$user) {
        if (isset($ambiguousNames[(string)$user['employee_name']])) {
            $user['last_activity'] = null;
            $user['activity_count'] = null;
            continue;
        }
        $log = isset($logMap[(string)$user['employee_name']]) ? $logMap[(string)$user['employee_name']] : null;
        $user['last_activity'] = $log ? $log['last_activity'] : null;
        $user['activity_count'] = $log ? (int)$log['total'] : 0;
    }
    unset($user);
    return array('rows' => $users, 'total' => isset($countRows[0]['total']) ? (int)$countRows[0]['total'] : null);
}

function getSuperAdminUserFilters()
{
    global $superAdminIdentityDb;
    $branches = superAdminRunSelect(
        $superAdminIdentityDb,
        "SELECT DISTINCT j.Location AS value FROM kane_users_login u INNER JOIN kane_jobinfo j ON u.LoginID = j.IDNumber AND j.ID = (SELECT j2.ID FROM kane_jobinfo j2 WHERE j2.IDNumber = u.LoginID ORDER BY j2.EffectiveDate DESC, j2.ID DESC LIMIT 1) WHERE u.what_system = ? AND j.Location <> '' ORDER BY j.Location",
        's',
        array('KPFEMS')
    );
    $roles = superAdminRunSelect(
        $superAdminIdentityDb,
        "SELECT DISTINCT Role AS value FROM kane_users_login WHERE what_system = ? AND Role <> '' ORDER BY Role",
        's',
        array('KPFEMS')
    );
    return array('branches' => $branches, 'roles' => $roles);
}

function getSuperAdminInspectionStats($start, $end)
{
    global $superAdminOperationalDb;
    $rows = superAdminRunSelect(
        $superAdminOperationalDb,
        "SELECT evaluation_status, COUNT(*) AS total FROM inspection_checklist_tbl WHERE date_inspected >= ? AND date_inspected < ? GROUP BY evaluation_status",
        'ss',
        array($start, $end)
    );
    $stats = array('submitted' => 0, 'approved' => 0, 'rejected' => 0, 'pending' => 0);
    foreach ($rows as $row) {
        $key = strtolower((string)$row['evaluation_status']);
        if (isset($stats[$key])) {
            $stats[$key] = (int)$row['total'];
            $stats['submitted'] += (int)$row['total'];
        }
    }
    $csk = superAdminRunSelect(
        $superAdminOperationalDb,
        'SELECT COUNT(*) AS total FROM chemical_spill_kit_inspection_tbl WHERE inspection_date >= ? AND inspection_date < ?',
        'ss',
        array($start, $end)
    );
    if (isset($csk[0]['total'])) {
        $stats['submitted'] += (int)$csk[0]['total'];
    }
    $stats['approval_status_note'] = 'Approved, rejected, and pending counts cover fire extinguisher inspections; spill kit approval status is unavailable.';
    return $stats;
}

function getSuperAdminTrend($start, $end)
{
    global $superAdminOperationalDb;
    return superAdminRunSelect(
        $superAdminOperationalDb,
        'SELECT activity_date, SUM(log_total) AS log_total, SUM(inspection_total) AS inspection_total FROM (SELECT DATE(created_at) AS activity_date, COUNT(*) AS log_total, 0 AS inspection_total FROM activity_logs_tbl WHERE created_at >= ? AND created_at < ? AND user_name IN (SELECT EmployeeName FROM kp201.kane_users_login WHERE what_system = ?) GROUP BY DATE(created_at) UNION ALL SELECT DATE(date_inspected) AS activity_date, 0 AS log_total, COUNT(*) AS inspection_total FROM inspection_checklist_tbl WHERE date_inspected >= ? AND date_inspected < ? GROUP BY DATE(date_inspected) UNION ALL SELECT DATE(inspection_date) AS activity_date, 0 AS log_total, COUNT(*) AS inspection_total FROM chemical_spill_kit_inspection_tbl WHERE inspection_date >= ? AND inspection_date < ? GROUP BY DATE(inspection_date)) daily GROUP BY activity_date ORDER BY activity_date',
        'sssssss',
        array($start, $end, 'KPFEMS', $start, $end, $start, $end)
    );
}

function getSuperAdminInspectionTrend($start, $end)
{
    global $superAdminOperationalDb;
    return superAdminRunSelect(
        $superAdminOperationalDb,
        'SELECT month_key, SUM(total) AS total FROM (SELECT DATE_FORMAT(date_inspected, "%Y-%m") AS month_key, COUNT(*) AS total FROM inspection_checklist_tbl WHERE date_inspected >= ? AND date_inspected < ? GROUP BY DATE_FORMAT(date_inspected, "%Y-%m") UNION ALL SELECT DATE_FORMAT(inspection_date, "%Y-%m") AS month_key, COUNT(*) AS total FROM chemical_spill_kit_inspection_tbl WHERE inspection_date >= ? AND inspection_date < ? GROUP BY DATE_FORMAT(inspection_date, "%Y-%m")) monthly GROUP BY month_key ORDER BY month_key',
        'ssss',
        array($start, $end, $start, $end)
    );
}

function getSuperAdminEquipmentTypes($start, $end)
{
    global $superAdminOperationalDb;
    return superAdminRunSelect(
        $superAdminOperationalDb,
        'SELECT equipment_type, SUM(total) AS total FROM (SELECT type AS equipment_type, COUNT(*) AS total FROM inspection_checklist_tbl WHERE date_inspected >= ? AND date_inspected < ? GROUP BY type UNION ALL SELECT "Chemical Spill Kit" AS equipment_type, COUNT(*) AS total FROM chemical_spill_kit_inspection_tbl WHERE inspection_date >= ? AND inspection_date < ?) equipment GROUP BY equipment_type ORDER BY total DESC',
        'ssss',
        array($start, $end, $start, $end)
    );
}
