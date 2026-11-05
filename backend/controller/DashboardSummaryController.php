<?php
require_once __DIR__ . '/../config/dashboard-equipment.php';
require_once __DIR__ . '/../model/DashboardSummaryModel.php';

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

function resolveDashboardSummaryFilters(array $input, array $session, $now = null)
{
    $role = strtolower(trim($session['Role'] ?? ''));
    if (!in_array($role, array('admin', 'inspector', 'super admin'), true)) {
        throw new RuntimeException('You do not have access to this dashboard.', 403);
    }
    $now = $now ?: new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
    $today = $now->setTime(0, 0);
    $month = $today->modify('first day of this month');
    $branch = $role === 'admin'
        ? ($input['branch'] ?? ($session['Branch'] ?? 'all'))
        : ($session['Branch'] ?? '');
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
    $allowed = array('today', 'yesterday', 'last_7', 'last_30', 'this_month', 'last_month', 'custom');
    if (!is_string($period) || !in_array($period, $allowed, true)) {
        throw new InvalidArgumentException('Choose a valid date range.');
    }
    $end = $today->modify('+1 day');
    switch ($period) {
        case 'today': $start = $today; break;
        case 'yesterday': $start = $today->modify('-1 day'); $end = $today; break;
        case 'last_7': $start = $today->modify('-6 days'); break;
        case 'last_30': $start = $today->modify('-29 days'); break;
        case 'last_month': $start = $month->modify('-1 month'); $end = $month; break;
        case 'custom':
            $start = dashboardDateValue($input['from'] ?? '');
            $last = dashboardDateValue($input['to'] ?? '');
            if ($start > $last) {
                throw new InvalidArgumentException('Start date must be on or before end date.');
            }
            if ($last->format('Y') === '9999' && $last->format('m-d') === '12-31') {
                throw new InvalidArgumentException('Choose an end date before December 31, 9999.');
            }
            $end = $last->modify('+1 day');
            break;
        default: $start = $month; $end = $month->modify('+1 month');
    }
    return array(
        'branch' => $branch, 'period' => $period,
        'from' => $start->format('Y-m-d'),
        'to' => $end->modify('-1 day')->format('Y-m-d'),
        'start' => $start->format('Y-m-d H:i:s'),
        'end' => $end->format('Y-m-d H:i:s'),
        'comparison_start' => $month->modify('-1 month')->format('Y-m-d H:i:s'),
        'comparison_end' => $month->modify('+1 month')->format('Y-m-d H:i:s'),
        'previous_month' => $month->modify('-1 month')->format('Y-m'),
        'current_month' => $month->format('Y-m'),
        'can_select_branch' => $role === 'admin'
    );
}

function getDashboardSummaryData(array $input, array $session, $now = null)
{
    $filters = resolveDashboardSummaryFilters($input, $session, $now);
    $totals = array('active' => 0, 'inspected' => 0, 'submissions' => 0, 'not_inspected' => 0, 'history_unique' => 0);
    $equipment = array();
    $comparison = array('current' => 0, 'previous' => 0);
    $months = array();
    $cursor = dashboardDateValue($filters['from'])->modify('first day of this month');
    $lastMonth = dashboardDateValue($filters['to'])->modify('first day of this month');
    while ($cursor <= $lastMonth) {
        $months[] = array('key' => $cursor->format('Y-m'), 'label' => $cursor->format('M Y'));
        $cursor = $cursor->modify('+1 month');
    }
    foreach (getDashboardEquipmentRegistry() as $definition) {
        $summary = call_user_func($definition['summary_function'], $filters);
        foreach ($totals as $key => $unused) {
            $totals[$key] += $summary[$key];
        }
        foreach ($summary['comparison'] as $row) {
            if ($row['month'] === $filters['current_month']) $comparison['current'] += (int) $row['inspected'];
            if ($row['month'] === $filters['previous_month']) $comparison['previous'] += (int) $row['inspected'];
        }
        $byMonth = array_column($summary['monthly'], 'inspected', 'month');
        $summary['monthly'] = array_map(function ($month) use ($byMonth) {
            return (int) ($byMonth[$month['key']] ?? 0);
        }, $months);
        unset($summary['comparison'], $definition['summary_function']);
        $equipment[] = array_merge($definition, $summary);
    }
    $difference = $comparison['current'] - $comparison['previous'];
    $comparison['text'] = $difference === 0
        ? 'No change from last month'
        : abs($difference) . ($difference > 0 ? ' more than last month' : ' fewer than last month');

    return array(
        'filters' => $filters,
        'totals' => $totals,
        'equipment' => $equipment,
        'months' => $months,
        'comparison' => $comparison,
        'branches' => $filters['can_select_branch'] ? getDashboardSummaryBranches() : array(),
        'generated_at' => (new DateTimeImmutable('now', new DateTimeZone('Asia/Manila')))->format('M j, Y | g:i A')
    );
}
