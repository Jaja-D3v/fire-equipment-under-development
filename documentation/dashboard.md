# Dashboard

## Request flow

The dashboard follows the project's procedural PHP and vanilla JavaScript approach.

1. `dashboard.php` checks the session and calls `getDashboardSummaryData()` for the initial page.
2. `backend/controller/DashboardSummaryController.php` validates role, branch, and dates, calls the equipment Models, and assembles the response.
3. `backend/model/DashboardSummaryModel.php` runs prepared queries through the shared connection in `backend/config/db.php`.
4. `js/dashboard.js` updates the cards, charts, and table. Filter changes use `fetch()` to `backend/controller/DashboardDataController.php`, which calls the same summary Controller.

`backend/config/dashboard-equipment.php` maps supported equipment modules to labels and Model functions. New modules must be registered on the backend; the frontend loops over the returned equipment array.

The older `DashboardController.php` and `DashboardModel.php` remain available for the existing expiration notice and legacy callers. The summary dashboard does not use their inventory calculations.

## Counting rules

- Active inventory counts distinct equipment identity and branch with `archived = 0`. Inspection dates never restrict inventory.
- Inspected counts active units with at least one matching inspection in the selected period. Repeat inspections do not count a unit twice.
- Not inspected is active minus inspected.
- Submissions counts matching inspection headers, including re-inspections and archived equipment history.
- Period Total counts distinct equipment identities across the entire selected range. It is not the sum of the monthly bars.
- Monthly bars count distinct equipment identities within each month, including archived equipment history.
- Coverage compares inspected and remaining active units for the selected period.
- Distribution uses current active inventory by equipment type, independent of inspection dates.

Chemical spill kits are counted by distinct kit ID and branch, not by their material rows. The existing queries do not add an approval-status filter.

## Filters and access

Only Admin can select a branch, including `all`. Inspector and Super Admin use their assigned session branch and cannot override it through the URL. Missing assignments for those roles return an access error.

Dates use Asia/Manila time. SQL includes the start and excludes midnight after the last date. This Month covers the full calendar month. Today, Yesterday, Last 7 Days, Last 30 Days, Last Month, and Custom Date Range remain available.

The comparison always uses the current and previous calendar months, regardless of the selected inspection period.

## JSON and JavaScript

The endpoint returns `success: true` with `data`, or `success: false` with a readable `message`.

- 200: dashboard data.
- 400: invalid branch or dates.
- 401: missing or expired session.
- 403: unsupported role or missing required branch assignment.
- 500: unexpected server/database failure; details are logged on the server.

`refreshDashboard()` sends the selected filters, cancels older requests, and prevents stale responses from overwriting newer results. It preserves previous results after a failed request. An expired session displays a sign-in link.

`renderDashboard()` updates statistics, equipment cards, the uninspected breakdown, and charts. Separate chart functions reuse the existing Chart.js instances. The monthly table remains available if Chart.js cannot load.

## Bootstrap and partials

The shared header provides Bootstrap 5.3.3. Dashboard layout uses its container, rows, responsive columns, gutters, spacing, flex utilities, cards, controls, buttons, progress bars, and responsive table wrapper.

`css/dashboard.css` adds colors, typography, borders, chart parent dimensions, and the scoped 180ms flip effect. It does not replace the responsive grid.

`partials/dashboard-equipment-card.php` serves both the initial equipment list and AJAX template. `partials/dashboard-uninspected-card.php` renders the flip card. Only its list scrolls; the heading, total, and Back button remain fixed. Both faces keep the front card's dimensions and support keyboard access and reduced motion. Any total mismatch is reported without inventing equipment categories.

## Refactor verification

The November 6, 2026 refactor was compared with saved source and live database results:

- 32 data responses matched the original, excluding the generated timestamp.
- 10 validation failures retained the same exception type, code, and message.
- 13 endpoint cases retained their response bodies and status codes, including a simulated database failure.
- Inventory totals stayed constant across date changes for each tested branch.
- Assigned roles ignored requested branch overrides.
- Browser chart values and labels matched database-backed responses.
- 36 browser layout cases from 320px to 1920px had no horizontal overflow.
- Card dimensions, a 42-row test list, keyboard access, reduced motion, empty states, and mismatch reporting passed.
- PHP syntax, JavaScript syntax, and Git whitespace checks passed.

Verification scripts, isolated sessions, screenshots, and comparison data were created in the system temp folder. No database records or schema were changed.
