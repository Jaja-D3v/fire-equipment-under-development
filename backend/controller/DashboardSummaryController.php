<?php
require_once __DIR__ . '/../config/dashboard-equipment.php';
require_once __DIR__ . '/../model/DashboardSummaryModel.php';

// Validate a calendar date before using it in a dashboard range.
function dashboardDateValue($value)
{
    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        throw new InvalidArgumentException('Choose valid start and end dates.');
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('Asia/Manila'));
    if (!$date || $date->format('Y-m-d') !== $value || (int) $date->format('Y') < 1000) {
        throw new InvalidArgumentException('Choose valid start and end dates.');
    }

    return $date;
}

// Resolve the user's branch and inspection dates for both page loads and AJAX.
function resolveDashboardSummaryFilters(array $input, array $session, $now = null)
{
    $role = strtolower(trim($session['Role'] ?? ''));
    $allowedRoles = ['admin', 'inspector', 'super admin'];
    if (!in_array($role, $allowedRoles, true)) {
        throw new RuntimeException('You do not have access to this dashboard.', 403);
    }

    $now = $now ?: new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
    $today = $now->setTime(0, 0);
    $month = $today->modify('first day of this month');

    // Only Admin may select a branch. Other allowed roles use their session branch.
    if ($role === 'admin') {
        $branch = $input['branch'] ?? ($session['Branch'] ?? 'all');
    } else {
        $branch = $session['Branch'] ?? '';
    }

    if (!is_string($branch)) {
        throw new InvalidArgumentException('Choose a valid branch.');
    }

    $branch = trim($branch);
    if ($role === 'admin' && $branch === '') {
        $branch = trim($session['Branch'] ?? '') ?: 'all';
    }
    if ($role !== 'admin' && ($branch === '' || strtolower($branch) === 'all')) {
        throw new RuntimeException('Your account needs an assigned branch.', 403);
    }
    if (strlen($branch) > 400) {
        throw new InvalidArgumentException('Choose a valid branch.');
    }

    $period = $input['period'] ?? 'this_month';
    $allowedPeriods = ['today', 'yesterday', 'last_7', 'last_30', 'this_month', 'last_month', 'custom'];
    if (!is_string($period) || !in_array($period, $allowedPeriods, true)) {
        throw new InvalidArgumentException('Choose a valid date range.');
    }

    // SQL includes the start date and excludes midnight after the last date.
    $end = $today->modify('+1 day');
    switch ($period) {
        case 'today':
            $start = $today;
            break;
        case 'yesterday':
            $start = $today->modify('-1 day');
            $end = $today;
            break;
        case 'last_7':
            $start = $today->modify('-6 days');
            break;
        case 'last_30':
            $start = $today->modify('-29 days');
            break;
        case 'last_month':
            $start = $month->modify('-1 month');
            $end = $month;
            break;
        case 'custom':
            $start = dashboardDateValue($input['from'] ?? '');
            $lastDate = dashboardDateValue($input['to'] ?? '');
            if ($start > $lastDate) {
                throw new InvalidArgumentException('Start date must be on or before end date.');
            }
            if ($lastDate->format('Y') === '9999' && $lastDate->format('m-d') === '12-31') {
                throw new InvalidArgumentException('Choose an end date before December 31, 9999.');
            }
            $end = $lastDate->modify('+1 day');
            break;
        default:
            $start = $month;
            $end = $month->modify('+1 month');
    }

    return [
        'branch' => $branch,
        'period' => $period,
        'from' => $start->format('Y-m-d'),
        'to' => $end->modify('-1 day')->format('Y-m-d'),
        'start' => $start->format('Y-m-d H:i:s'),
        'end' => $end->format('Y-m-d H:i:s'),
        'comparison_start' => $month->modify('-1 month')->format('Y-m-d H:i:s'),
        'comparison_end' => $month->modify('+1 month')->format('Y-m-d H:i:s'),
        'previous_month' => $month->modify('-1 month')->format('Y-m'),
        'current_month' => $month->format('Y-m'),
        'can_select_branch' => $role === 'admin'
    ];
}

// Combine the registered equipment models into the existing dashboard response.
function getDashboardSummaryData(array $input, array $session, $now = null)
{
    $filters = resolveDashboardSummaryFilters($input, $session, $now);
    $totals = ['active' => 0, 'inspected' => 0, 'submissions' => 0, 'not_inspected' => 0, 'history_unique' => 0];
    $equipment = [];
    $comparison = ['current' => 0, 'previous' => 0];
    $months = [];

    // Build a complete month axis, including months without inspections.
    $monthDate = dashboardDateValue($filters['from'])->modify('first day of this month');
    $lastMonth = dashboardDateValue($filters['to'])->modify('first day of this month');
    while ($monthDate <= $lastMonth) {
        $months[] = ['key' => $monthDate->format('Y-m'), 'label' => $monthDate->format('M Y')];
        $monthDate = $monthDate->modify('+1 month');
    }

    foreach (getDashboardEquipmentRegistry() as $definition) {
        $summaryFunction = $definition['summary_function'];
        $equipmentSummary = $summaryFunction($filters);

        foreach ($totals as $key => $value) {
            $totals[$key] += $equipmentSummary[$key];
        }

        // Comparison always uses the current and previous calendar months.
        foreach ($equipmentSummary['comparison'] as $monthlyCount) {
            if ($monthlyCount['month'] === $filters['current_month']) {
                $comparison['current'] += (int) $monthlyCount['inspected'];
            }
            if ($monthlyCount['month'] === $filters['previous_month']) {
                $comparison['previous'] += (int) $monthlyCount['inspected'];
            }
        }

        $countsByMonth = array_column($equipmentSummary['monthly'], 'inspected', 'month');
        $equipmentSummary['monthly'] = [];
        foreach ($months as $month) {
            $equipmentSummary['monthly'][] = (int) ($countsByMonth[$month['key']] ?? 0);
        }

        // Keep internal function names out of the public response.
        unset($equipmentSummary['comparison'], $definition['summary_function']);
        $equipment[] = array_merge($definition, $equipmentSummary);
    }

    $difference = $comparison['current'] - $comparison['previous'];
    if ($difference === 0) {
        $comparison['text'] = 'No change from last month';
    } elseif ($difference > 0) {
        $comparison['text'] = $difference . ' more than last month';
    } else {
        $comparison['text'] = abs($difference) . ' fewer than last month';
    }

    $branches = [];
    if ($filters['can_select_branch']) {
        $branches = getDashboardSummaryBranches();
    }

    return [
        'filters' => $filters,
        'totals' => $totals,
        'equipment' => $equipment,
        'months' => $months,
        'comparison' => $comparison,
        'branches' => $branches,
        'generated_at' => (new DateTimeImmutable('now', new DateTimeZone('Asia/Manila')))->format('M j, Y | g:i A')
    ];
}
