<?php
require_once __DIR__ . '/../model/SuperAdminMonitoringModel.php';

function getSuperAdminMonitoringData($input)
{
    $range = isset($input['range']) ? (string)$input['range'] : 'this_month';
    $allowedRanges = array('today', '7d', '30d', 'this_month', 'previous_month', 'custom');
    if (!in_array($range, $allowedRanges, true)) {
        $range = 'this_month';
    }
    $bounds = superAdminDateRange($range, isset($input['from']) ? (string)$input['from'] : '', isset($input['to']) ? (string)$input['to'] : '');
    $page = max(1, isset($input['page']) ? (int)$input['page'] : 1);
    $limit = min(50, max(10, isset($input['limit']) ? (int)$input['limit'] : 10));
    $userPage = max(1, isset($input['user_page']) ? (int)$input['user_page'] : 1);
    $overview = getSuperAdminOverview($bounds[0], $bounds[1]);
    return array(
        'range' => array('start' => $bounds[0], 'end_exclusive' => $bounds[1]),
        'overview' => $overview,
        'inspections' => getSuperAdminInspectionStats($bounds[0], $bounds[1]),
        'branches' => getSuperAdminBranches($bounds[0], $bounds[1]),
        'top_users' => getSuperAdminTopUsers($bounds[0], $bounds[1]),
        'users' => getSuperAdminUsers(
            $bounds[0],
            $bounds[1],
            $userPage,
            $limit,
            isset($input['user_search']) ? trim((string)$input['user_search']) : '',
            isset($input['branch']) ? trim((string)$input['branch']) : '',
            isset($input['role']) ? trim((string)$input['role']) : ''
        ),
        'user_filters' => getSuperAdminUserFilters(),
        'trend' => getSuperAdminTrend($bounds[0], $bounds[1]),
        'weekly_trend' => getSuperAdminPeriodTrend($bounds[0], $bounds[1], 'weekly'),
        'monthly_activity_trend' => getSuperAdminPeriodTrend($bounds[0], $bounds[1], 'monthly'),
        'inspection_trend' => getSuperAdminInspectionTrend($bounds[0], $bounds[1]),
        'equipment_types' => getSuperAdminEquipmentTypes($bounds[0], $bounds[1]),
        'logs' => getSuperAdminActivity(
            $bounds[0],
            $bounds[1],
            $page,
            $limit,
            isset($input['search']) ? trim((string)$input['search']) : '',
            isset($input['user']) ? trim((string)$input['user']) : '',
            isset($input['action']) ? trim((string)$input['action']) : '',
            isset($input['module']) ? trim((string)$input['module']) : ''
        )
    );
}
