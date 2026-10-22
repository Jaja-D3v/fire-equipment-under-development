# Inspections

## Overview
Inspection capture is presented in QR-code.php. Inspector history is shown in my-inspections.php. Both require a valid session.

## Inspection submission
The scanner resolves an extinguisher through ScanQRCodeController.php and ScanQRCodeModel.php. The user fills the checklist and submits to backend/controller/InspectionController.php. The Controller validates/processes request data and calls addInspectionChecklist() in InspectionModel.php. The Model inserts an inspection checklist row and updates extinguisher inspection-related data where implemented. Activity logging is also connected through ActivityLogController.php.

Checklist fields and status values are defined by the page and Model. The source includes several individual inspection checks plus overall status/evaluation fields. The SQL dump is incomplete relative to all fields referenced by current Models; see database-reference.md.

## My Inspections
my-inspections.php calls getMyInspectionsPageData() in MyInspectionsController.php. That Controller builds page data from MyInspectionsModel.php functions for filtered/paginated rows, count, and summary. The module supports the filters represented by the page, including search/date/status/branch as implemented. Role and branch restrictions are applied in server-side code.

## Use
1. Scan an extinguisher QR code.
2. Confirm the extinguisher details returned by the lookup.
3. Complete and submit the inspection checklist.
4. Open My Inspections to review records associated with the inspector.
5. Apply available filters and pagination to locate a prior record.

## Limitations
The exact live schema and status workflow must be confirmed against the deployed database. This is source documentation; no inspection was submitted and no live history was queried.
