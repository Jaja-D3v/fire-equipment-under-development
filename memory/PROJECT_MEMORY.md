# EMS Project Memory

> This is a human-maintained project notebook for use with ChatGPT. Keep it accurate and concise. Update completed items only after they are verified.

## Project overview
- Project: IMS Safety Management System / fire equipment management system (EMS).
- Main purpose: manage fire extinguisher inventory, QR codes, inspections, approvals, expiration monitoring, and reports.
- Known stack: procedural PHP, MySQL/MariaDB, mysqli, JavaScript, HTML/CSS, Bootstrap/CoreUI, and TCPDF for PDF reports.
- Environment previously used: Windows with XAMPP. Confirm the current environment before giving environment-specific commands.

## Architecture rules
- Follow the documented target architecture: procedural PHP, with no new classes/OOP unless the project owner explicitly approves.
- The existing codebase is mostly procedural, but `maintenance.php`, `backend/model/MaintenanceModel.php`, and `backend/controller/MaintenanceController.php` are an existing class-based exception. Treat that as an exception, not a direction to expand OOP elsewhere.
- Keep Model and Controller responsibilities separate where the existing flow supports it. Models normally own SQL; Controllers handle requests/authorization/flow and call Models; frontend pages render Controller results.
- Required data flow for new work: Frontend → Controller → Model → Database. Reuse existing naming and routing conventions in the relevant module.
- Follow the actual existing folder structure; inspect files before assuming names.
- Use prepared statements and validate user input.
- Use POST for create/update/delete forms, consistent with existing routes.
- Use Bootstrap and keep pages responsive.

## Verified project structure and implementation
- Application pages are at the repository root: `dashboard.php`, `list-extinguisher.php`, `archived-extinguisher.php`, `expiring-extinguisher.php`, `maintenance.php`, `QR-code.php`, `QR-generator.php`, and inspection approval pages (`inspection-approvals.php`, `inspection-pending.php`, `inspection-approved.php`, `inspection-rejected.php`, `rejected-inspection.php`).
- `backend/model/`: SQL/data functions for extinguishers, inspections, authentication, dashboard data, dropdowns, logs, QR lookup, and report data.
- `backend/controller/`: request handling and Model wrappers, including controllers used as direct JSON/form endpoints.
- `backend/config/`: mysqli connection files. `db.php` selects `ems_db`; `db_user.php` selects the separate `kp201` database. Do not put credential values in this memory.
- `backend/authentication/`: session checking, session extension endpoint, and logout. `authentication/`: login and system selection pages.
- `partials/`: shared header, navigation, footer, and inspection/extinguisher modals. `notification/`: status and timeout UI.
- `js/`: page scripts for login, inventory, QR, approvals, and dashboard. `css/`: app/login styles. `assets/`: logos and other images/icons.
- `helpers/`: TCPDF inspection/inventory report generation and QR label printing. `composer.json` declares TCPDF; `vendor/` and `vendors/` contain dependencies/template assets and are not application source.
- `SQL_FIles/`: EMS SQL files (`db.sql`, `127_0_0_1.sql`) plus a large, separate `kp201.sql` dump. `ChatGPT_Go_VSCode_EMS_Memory_Starter/` is documentation, separate from the root application. `RDME/` contains project notes and dated work items.

## Verified Frontend → Controller → Model → Database flows
- **Inventory:** `list-extinguisher.php` reads page/filter/search values and calls `getAllFireExtinguishers()` and `getTotalFireExtinguishers()` in `backend/controller/FireExtinguisherController.php`. These delegate to `getAll()` and the total-count Model function in `backend/model/FireExtinguisherModel.php`. `getAll()` queries `fire_extinguishers_tbl`, filters active rows with `archived = 0`, applies branch/placement/condition/search filters, and paginates with `LIMIT`/`OFFSET`.
- **QR scan and inspection submission:** `QR-code.php` displays the scanner/form. `js/QR-code.js` calls `backend/controller/ScanQRCodeController.php?action=getFeInfo`; `getFeInfoByCodeController()` delegates to `getFeInfoByCode()` in `backend/model/ScanQRCodeModel.php`, which looks up the extinguisher using a prepared statement. The form posts to `backend/controller/InspectionController.php`; its `inspect` action builds checklist values and calls `addInspectionChecklist()` in `backend/model/InspectionModel.php`. The Model inserts an inspection row and updates extinguisher inspection metadata.
- **Approvals:** `inspection-pending.php` calls `getPendingApprovalTotal()` and `getPendingApprovals()` in `backend/controller/InspectionController.php`. The Controller selects the branch based on role/session and delegates to `getPendingApprovalCount()` and `getAllPendingApproval()` in `backend/model/InspectionModel.php`. The Model selects the latest inspection per extinguisher code, filters `evaluation_status = 'Pending'`, and applies optional date/branch filters and pagination. The Controller's `update_evaluation_status` POST action calls Model update functions for evaluation and corrective-action fields, and applies refill-related expiration handling.
- **Login:** `authentication/login.php` submits to `backend/controller/AuthController.php` (normal POST or AJAX via `js/login.js`). The Controller calls `loginUser()` in `backend/model/AuthModel.php`; that Model queries `kane_users_login` and joins `kane_jobinfo` to obtain user/job data including location. On accepted login, the Controller initializes the session and routes Inspector/Admin roles. Authentication uses the `kp201` connection, not the EMS `ems_db` connection.
- Other common patterns: page-rendered Controller calls (for example `dashboard.php` to `DashboardController.php`) and JavaScript `fetch()` requests to controller endpoints (for example inventory edit/detail and inspection detail).

## Verified SQL dump facts and schema limits
- `SQL_FIles/127_0_0_1.sql` defines `fire_extinguishers_tbl` with primary key `extinguisher_id`, unique `extinguisher_code`, branch, condition, expiration, archive, and refill fields; `inspection_checklist_tbl` with primary key `inspect_id`, extinguisher code, branch, evaluation status, checklist flags, and action fields; and `activity_logs_tbl` with primary key `log_id`.
- That dump does not declare foreign keys. The application relates inspections to extinguishers using matching `extinguisher_code` in its queries. Multiple inspection rows per code are supported by the dump's primary-key definition; the Model query identifies a latest row for relevant views.
- `SQL_FIles/db.sql` is an older/incomplete schema compared with current Model usage. The inspected EMS dump does not define `maintenance_dropdown_tbl` or `branches_tbl`, though current Models query them.
- Current Model code also references fields not present in `127_0_0_1.sql`, including extinguisher fields such as `added_by`, `last_date_inspected`, and `inspected_by`, and inspection fields such as cleaning status, remarks, rejection information, and rejection date. The source dump therefore does not establish the live schema. Verify the actual database/schema before any SQL change; do not assume a migration or column exists.
- `kp201.sql` is a separate employee/system database dump. Do not copy personal records or authentication data into this memory.
- These are facts about checked-in source and SQL files only; the live database and runtime behavior were not inspected.

## Known modules / features
- Fire extinguisher inventory and active/archive records.
- QR code generation and printing.
- Inspection checklist and inspection history.
- Inspection approval workflow with pending/approved/rejected states.
- Expiration monitoring.
- Branch-based filtering and role-based access.
- Search, date filters, and pagination.
- Inventory and inspection PDF reports.

## Important business rules to verify in code
- **Verified from source:** Admin listing Controllers generally accept a branch from the URL and default to the session branch; some fall back to `all` when no branch is assigned. Inspector inventory/approval listing queries generally use the session branch. Enforcement is split across Controllers and Models and is not uniform across every endpoint.
- **Verified from source:** active extinguisher listing/report queries commonly filter `archived = 0`; archive is implemented as a soft update to `archived = 1`, with restore functions in `FireExtinguisherModel.php`.
- **Verified from source:** pending/approved/rejected are `evaluation_status` values; pending and rejected queries include latest-inspection logic. `RejectedInspectionModel.php` restricts its result to the current inspector name and session branch and to the latest inspection per code.
- **Verified from source:** `getExpiry()` selects non-archived records expiring from today through two months ahead and supports search, condition, branch, and pagination/count logic.
- **Verified from source:** inspection submission's overall `status` is computed using pressure, hose, nozzle, and cylinder-body checks. The approval-details JavaScript's displayed all-good state checks all listed checklist fields; the rules differ and need investigation before changing either.
- **Verified from source:** when the approval action is `Refill`, the Controller derives a new expiration date as three years after `target_date_of_implementation` and calls the expiration update Model function.
- **Verified from source:** QR labels encode the extinguisher code; scanner lookup uses that code to fetch extinguisher details.
- Check current code and actual schema before relying on these rules for new behavior. Do not treat source-only findings as proof of live data/schema behavior.

## Existing architecture exceptions
- `maintenance.php` uses the class-based `MaintenanceController` and `MaintenanceModel`.
- `backend/controller/FireExtinguisherController.php` has a `getBranches` request action containing a direct SQL query.
- Several Controller files also contain request endpoint logic (GET/POST action routing and JSON responses), rather than being only callable function libraries.
- Fire extinguisher archive/delete is currently triggered by a GET `action=delete` endpoint. This is an observed legacy exception to the preferred POST mutation convention; do not broaden it without a deliberate task.

## Previously encountered technical concerns
These are historical notes, not proof that an issue still exists:
- mysqli prepared statement parameter count/type mismatches.
- Branch, date, status, and search filters becoming inconsistent.
- Pagination not resetting after filter changes.
- Expiring-extinguisher query parameter mismatches.
- PHP GD extension and TCPDF PNG alpha-channel handling.
- PHP version compatibility differences.
- Database migration issues and MariaDB/InnoDB errors.

## Current task
- Goal: set up a ChatGPT Go + VS Code workflow with persistent project rules and manually maintained project memory.
- No direct automatic connection between the ChatGPT website and the local VS Code folder is assumed.

## Current known issues
- Add issue, reproduction steps, relevant file(s), and current status here.
- Do not mark an issue fixed until the user confirms the test result.

### INVESTIGATION NEEDED (source review; not confirmed runtime bugs)
- `AuthController.php` calls `loginUser($username, $password)`, while `AuthModel.php` declares `loginUser($loginID)` and selects the password field for Controller-side comparison. The Controller compares an MD5 value. Verify intended function contract and actual login behavior before making a change.
- `InspectionController.php` receives `$date_inspected`, but `addInspectionChecklist()` in `InspectionModel.php` inserts `NOW()` for `date_inspected`. Confirm intended date behavior and current schema.
- Inspection submission's `status` calculation checks four checklist fields, while `js/inspection-approvals.js` derives displayed all-good state from all checklist fields. Confirm the intended business rule.
- Current Models reference columns/tables absent from the checked-in EMS schema dump (see “Verified SQL dump facts and schema limits”). Identify the authoritative schema before query changes.
- `BranchModel.php` queries `branches_tbl`, while `DropdownModel.php` uses `maintenance_dropdown_tbl` for branch options. Confirm which source is intended and whether both tables exist in the live database.
- Some approval Model updates use branch predicates, while approver/rejector or extinguisher-expiration updates shown in source do not consistently include one. Review authorization requirements and endpoint behavior before changing these functions.

## Verified decisions
- Add dated decisions here, including the reason and affected modules.

## Recent verified changes
- Date: 2026-10-11
- Change: Added source-verified architecture, folder map, representative call flows, SQL dump limits, existing exceptions, and investigation items from a read-only codebase review.
- Files: `ChatGPT_Go_VSCode_EMS_Memory_Starter/PROJECT_MEMORY.md`
- Test performed: Read-only source and SQL dump inspection; no runtime tests or database access.
- Result: Memory updated with verified source findings and clearly labeled unresolved questions; application files were not changed.
