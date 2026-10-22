# User Roles and Permissions

## Roles visible in application logic
The inspected application pages and Controllers principally check session Role values for Admin and Inspector. Other or missing role values may be denied by individual Controller/Model paths. The login integration uses the separate employee/system account source and initializes session data; role provisioning is outside this EMS SQL dump.

## Admin behavior
- Admin-only branch dropdowns appear on inventory, archived, expiration, dashboard, and inspection pages where implemented.
- Admin list Controllers commonly accept a requested branch and default to the session branch.
- Some screens offer an all-branches choice. The exact scope depends on the individual Controller/Model query.
- Maintenance operations are handled by maintenance.php and MaintenanceController; inspect its action checks before assigning operational privileges.

## Inspector behavior
- Inspection and rejected-inspection paths use the session-assigned branch and, in some paths, the session inspector name.
- The rejected-inspection model filters by inspector and branch.
- Listing restrictions are implemented in different layers across modules; do not infer that a UI-hidden branch selector alone enforces authorization.

## Session controls
Root pages commonly require backend/authentication/SessionChecker.php. Shared navigation and session-timeout UI depend on session state. Logout is routed through backend/authentication/Logout.php.

## Security interpretation
This document records observed patterns, not a complete security certification. Each endpoint must be reviewed independently: UI visibility is not authorization, and client-supplied branch parameters must be constrained by server-side role/session logic. Runtime permission tests were not performed.
