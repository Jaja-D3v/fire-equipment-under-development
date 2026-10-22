# System Overview

## Purpose and scope
KPFEMS (also described in the repository as the IMS Safety Management System) supports fire extinguisher inventory, QR identification, inspection recording and review, expiry monitoring, activity history, and PDF reports.

## Application structure
- Root PHP pages render the user-facing modules.
- backend/controller contains procedural request handlers and Controller functions.
- backend/model contains database functions using mysqli.
- backend/authentication provides session checking, session-related endpoints, and logout.
- authentication contains login and system selection pages.
- js contains page-specific browser behavior; partials and notification contain shared UI.
- helpers contains PDF and QR printing helpers.
- SQL_FIles contains two EMS SQL dumps and a separate kp201.sql dump.
- Composer declares TCPDF; installed vendor folders are dependencies/assets, not module source.

## Typical request flow
1. A page checks the session and includes a Controller.
2. The page passes filters or identifiers to Controller functions.
3. Controller functions enforce or derive role/branch values and call Model functions.
4. Models query the selected database using mysqli and return rows/counts.
5. The page renders results; JavaScript may update results or call Controller endpoints.
6. Mutating actions update records through existing Controller/Model paths and may write activity logs.

This pattern is procedural, with an existing class-based exception in maintenance.php and its Maintenance Controller/Model.

## Main workflows
Inventory records can be listed, searched, edited, archived, restored, and represented by QR codes. An inspector scans a QR code and submits an inspection checklist. Inspection rows are reviewed as pending, approved, or rejected. Expiry and dashboard pages summarize inventory state. PDF helpers generate inventory or inspection reports.

## Data connections
backend/config/db.php is used for the EMS database; backend/config/db_user.php connects to a separate kp201 database used by authentication-related code. Configuration values are not reproduced in this documentation.

## Limits
This is a source review. It does not certify deployment configuration, live schema, data, current authorization behavior for every endpoint, or runtime correctness. See database-reference.md for SQL dump gaps.
