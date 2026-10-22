# Rejected Inspections

## Overview
inspection-rejected.php displays rejected inspection results in the approval workflow. rejected-inspection.php is a separate inspector-facing rejected-history page. Both are source-confirmed pages and should not be conflated.

## Approval workflow rejected list
inspection-rejected.php calls rejected list/count functions in InspectionController.php and InspectionModel.php. It supports the page's branch, date, search, and pagination filters. The Model applies rejected evaluation status and latest-inspection selection according to its query logic. Admin branch controls are rendered; non-admin access uses server-derived session scope.

## Inspector rejected history
rejected-inspection.php calls RejectedInspectionController.php and RejectedInspectionModel.php. The Model scopes results by inspector identity and session branch and exposes rejected-history retrieval/count behavior for that module.

## Use
1. Open Rejected Inspection from the applicable navigation.
2. Use the page filters to locate rejected records.
3. Open available inspection details and review the recorded rejection information.
4. For inspector history, open the separate rejected-inspection page.

## Related files
inspection-rejected.php; rejected-inspection.php; backend/controller/InspectionController.php; backend/model/InspectionModel.php; backend/controller/RejectedInspectionController.php; backend/model/RejectedInspectionModel.php.

## Limitations
No live rejected record was inspected. A zero-result page does not prove that filtering is incorrect; verify against known records in the target database.
