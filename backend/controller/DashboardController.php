<?php

require_once __DIR__ . '/../model/DashboardModel.php';

/*
|--------------------------------------------------------------------------
| DASHBOARD CONTROLLER
|--------------------------------------------------------------------------
| Admin:
|   - Default = ALL branches
|   - Can filter by selected branch
|
| Non-Admin:
|   - Always uses $_SESSION['Branch']
|   - Ignores any branch filter from request
|--------------------------------------------------------------------------
*/

/**
 * Get selected branch based on user role.
 *
 * Admin:
 *     ?branch=Calamba  -> Calamba
 *     ?branch=all      -> All
 *     no branch        -> All
 *
 * Non-admin:
 *     Always -> $_SESSION['Branch']
 */
function getDashboardBranch()
{
    if (strtolower(trim($_SESSION['Role'] ?? '')) === 'super admin') {
        return trim($_GET['branch'] ?? '') ?: 'all';
    }

    if (($_SESSION['Role'] ?? '') === 'Admin') {

        // If branch is selected from dropdown, use it
        if (isset($_GET['branch']) && trim($_GET['branch']) !== '') {
            return trim($_GET['branch']);
        }

        // Default branch = branch of logged-in Admin
        return trim($_SESSION['Branch'] ?? 'all');
    }

    // Non-admin can ONLY access their own branch
    return $_SESSION['Branch'] ?? null;
}

/**
 * INSPECTION - LAST 3 MONTHS
 */
function getInspectLastThreeMonths()
{
    $branch = getDashboardBranch();

    return getInspectionLastThreeMonths($branch);
}

function getTotalInspectedLastThreeMonths()
{
    return getInspectionLastThreeMonthsTotal(getDashboardBranch());
}

/**
 * FIRE EXTINGUISHER TYPE COUNTS
 */
function getFireExtinguisherTypeCountsController()
{
    $branch = getDashboardBranch();

    return getFireExtinguisherTypeCounts($branch);
}

/**
 * EXPIRING FIRE EXTINGUISHERS
 */
function getAllExpiringCount()
{
    $branch = getDashboardBranch();

    return getExpiringFireExtinguishers($branch);
}

/**
 * NOT GOOD CONDITION
 */
function getNotGoodCondition()
{
    $branch = getDashboardBranch();

    return getAllNotGoodCondition($branch);
}

/**
 * GOOD CONDITION
 */
function getGoodCondition()
{
    $branch = getDashboardBranch();

    return getAllGoodCondition($branch);
}

/**
 * INSTALLED - NOT GOOD
 */
function getTotalNotGoodInstalledFireExtinguishers()
{
    $branch = getDashboardBranch();

    return getNotGoodInstalledFireExtinguishersCount($branch);
}

/**
 * INSTALLED - GOOD
 */
function getTotalGoodInstalledFireExtinguishers()
{
    $branch = getDashboardBranch();

    return getGoodInstalledFireExtinguishersCount($branch);
}

/**
 * TOTAL INSTALLED
 */
function getTotalInstalledFireExtinguishers()
{
    $branch = getDashboardBranch();

    return getInstalledFireExtinguishersCount($branch);
}

/**
 * SPARE - NOT GOOD
 */
function getTotalNotGoodSpareFireExtinguishers()
{
    $branch = getDashboardBranch();

    return getNotGoodSpareFireExtinguishersCount($branch);
}

/**
 * SPARE - GOOD
 */
function getTotalGoodSpareFireExtinguishers()
{
    $branch = getDashboardBranch();

    return getGoodSpareFireExtinguishersCount($branch);
}

/**
 * TOTAL SPARE
 */
function getTotalSpareFireExtinguishers()
{
    $branch = getDashboardBranch();

    return getSpareFireExtinguishersCount($branch);
}

/**
 * TOTAL FIRE EXTINGUISHERS
 */
function getTotalFireExtinguishersDashboard()
{
    $branch = getDashboardBranch();

    return getAllFireExtinguishersCount($branch);
}

// Prepare the Fire Extinguisher detail card data for the dashboard view.
function getDashboardFireInventoryDetail($branch)
{
    $summary = getDashboardFireInventorySummary($branch);
    $rows = getDashboardFireInventoryTypeCapacityRows($branch);
    $matrix = [];
    $capacities = [];
    $breakdownTotal = 0;

    foreach ($rows as $row) {
        $type = trim((string) ($row['fe_type'] ?? ''));
        $capacity = trim((string) ($row['capacity'] ?? ''));
        if ($type === '' || $capacity === '') {
            continue;
        }

        $count = (int) ($row['total'] ?? 0);
        $matrix[$type][$capacity] = $count;
        $capacities[$capacity] = true;
        $breakdownTotal += $count;
    }

    uksort($matrix, 'strnatcasecmp');
    $capacities = array_keys($capacities);
    usort($capacities, function ($left, $right) {
        $leftNumber = (float) preg_replace('/[^0-9.]/', '', $left);
        $rightNumber = (float) preg_replace('/[^0-9.]/', '', $right);
        return $leftNumber === $rightNumber ? strnatcasecmp($left, $right) : $leftNumber <=> $rightNumber;
    });

    return [
        'summary' => $summary,
        'matrix' => $matrix,
        'capacities' => $capacities,
        'breakdown_total' => $breakdownTotal
    ];
}
