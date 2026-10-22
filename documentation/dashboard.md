# Dashboard

## Overview
dashboard.php presents an overview of extinguisher inventory and inspection/expiry indicators. It is intended for signed-in EMS users, with branch scope selected according to role.

## Data flow
The page requires SessionChecker.php and calls functions in DashboardController.php. That Controller derives the dashboard branch and calls DashboardModel.php functions for inspection trend, expiry count, condition totals, type counts, installed totals, and spare totals. js/dashboard.js supports dashboard presentation.

## Displayed information
Source functions cover:
- Inspection activity over recent months.
- Expiring units.
- Good and not-good condition totals.
- Extinguisher type counts.
- Installed and spare inventory totals, split by condition.

The page includes an expiration notice and shared navigation. Exact chart labels and displayed cards are defined in dashboard.php and js/dashboard.js.

## Branch scope
Admin users are shown a branch selector. The Controller's getDashboardBranch() selects the effective branch from session/request values according to role. Inspect the current Controller before relying on an all-branch result.

## Use
1. Sign in and open Dashboard from navigation.
2. If an admin selector is available, choose the desired branch.
3. Review the summary cards and charts.
4. Use the expiration notice/list path to inspect units requiring attention.

## Data and limitations
DashboardModel.php queries fire_extinguishers_tbl and inspection_checklist_tbl. Counts reflect the Model predicates and not necessarily all operational statuses. Live results and chart rendering were not tested.
