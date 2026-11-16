# User Roles and Permissions

Updated: 2026-11-12. This documents the approved Super Admin permission extension and its verification limits.

## Super Admin

Super Admin retains its actual session role and can use existing Admin and Inspector modules. Its default read scope is all branches, with or without an assigned branch. Existing valid branch filters may narrow records.

Existing functionality includes Dashboard, FE/CSK inventory, Required Stock, inspection review/history/rejected records, QR generation/printing/scanning, PDF/XLSX reports, FE history, activity logs, Maintenance and system selection. Super Admin monitoring remains exclusive to that role.

My Inspections, Rejected Inspection and Activity Log reads do not restrict Super Admin to its own employee name. Their listing/count/summary paths use the same authorized scope.

All-branch read access does not make all a valid equipment/configuration branch. Record actions still need a concrete target branch, actual record identity, existing validation and workflow rules. Ambiguous QR codes still require branch context through the existing ?branch=... parameter.

## Admin

Existing defaults and restrictions remain. Where previously implemented, Admin can select branches or all; omitted filters retain that module's prior session/default behavior.

The Inspector-only pages do not become Admin pages. Super Admin-only monitoring also remains unavailable to Admin.

## Inspector

Existing employee and assigned-branch constraints remain. Request parameters do not widen Inspector scope in the existing protected paths. Existing missing-branch/name validation is retained.

The implementation deliberately preserves legacy behavior, including known baseline limitations described in the regression report; this document does not claim all Inspector paths were already secure.

## Other roles and session controls

Only the explicit Super Admin role was added to relevant existing allowlists. Unsupported roles are not treated as Super Admin. Authenticated session state supplies role and actor identity; no request-supplied role is trusted.

SessionChecker, cookie settings, expiry handling, attribution, approval status validation, CSK Pending-only review, row locks, transaction handling and record/branch association checks remain in place. getCurrentBranch() was not globally changed.

No new functions, permission system, module, controller, model or database object was introduced.

## Verification and limits

The actual local application passed 170/170 browser-suite checks using temporary synthetic Admin/Inspector/Super Admin sessions and blocked mutation requests. Real SQL tests passed 113 assertions/30 SELECT-only cases; the existing isolated CSK suite passed 124 assertions. Mocked-request DOM assertions are separately identified in the report.

Credential login, live persistent actions, physical Honeywell/camera decoding and visual print acceptance were not performed. Existing CSRF/GET-action and legacy scope concerns are documented; they were not silently changed or certified.

See [the detailed implementation report](../RDME/REGRESSION_TEST_REPORT.md) for every changed file, Controller-to-Model trace, test evidence, failed/untested scenarios and remaining concerns.
