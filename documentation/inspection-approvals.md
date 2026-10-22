# Inspection Approvals

## Pages and users
inspection-pending.php, inspection-approvals.php, and inspection-approved.php support inspection review. The pages require SessionChecker.php and use InspectionController.php. Admin branch selection is available where rendered; Inspector queries are restricted using session branch logic.

## Pending review
The Pending page retrieves pending inspection rows and supports search, date, branch, and pagination behavior implemented in the page and shared js/inspection-approvals.js. InspectionModel.php selects the applicable/latest inspection per extinguisher code and filters evaluation_status to Pending.

## Approval actions
The review UI exposes the current approval/corrective-action controls. InspectionController.php handles status updates and delegates to InspectionModel.php functions such as updateEvaluationStatus(), updateCorrectiveAction(), Approved_by(), and updateFireExtinguisherExpiryDate(). Refill handling derives an expiration date from the target implementation date in the Controller. The existing business logic should be followed; do not infer unimplemented transitions.

## Approved list
inspection-approved.php queries approved results using date, search, branch, pagination, and corresponding count functions. List/count filters are implemented in InspectionModel.php. The page also provides report-related actions through the existing report Controller/Model path.

## Use
1. Open Pending Inspection.
2. Search, select a date, or choose an authorized branch.
3. Open an inspection's details and review its checklist.
4. Use an available decision/corrective-action control only when authorized.
5. Review resulting records on Approved Inspection.

## Limitations
No approve/reject action was executed during documentation. Latest-inspection semantics and current status values are source-defined; actual data behavior was not tested.
