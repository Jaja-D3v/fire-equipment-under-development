# Database Reference

## Connections
- backend/config/db.php selects the EMS operational database.
- backend/config/db_user.php selects the separate kp201 identity/user database.
Configuration secrets are deliberately omitted.

## EMS tables confirmed in checked-in SQL
The SQL_FIles/127_0_0_1.sql dump defines:
- fire_extinguishers_tbl: extinguisher_id, extinguisher_code, type, capacity, location, branch, manufactured_date, class, placement, condition_status, remarks, expiration_date, created_at, updated_at, archived, refilled_date.
- inspection_checklist_tbl: inspect_id, extinguisher_code, branch, location, capacity, type, class, date_inspected, inspected_by, verified_and_approved_by, evaluation_status, action_taken, target_date_of_implementation, checklist flags, and status.
- activity_logs_tbl: log_id, user_name, action, description, created_at.

The older SQL_FIles/db.sql also defines the three core tables but may have an older or reduced column set. Neither dump establishes the current deployed schema.

## Source-level use
- FireExtinguisherModel.php reads and changes fire_extinguishers_tbl; active listings filter archived = 0, archived listing filters archived = 1.
- InspectionModel.php and MyInspectionsModel.php read/write inspection_checklist_tbl and relate rows to extinguishers by extinguisher_code.
- ActivityLogModel.php reads/writes activity_logs_tbl.
- DashboardModel.php queries extinguisher and inspection data.
- DropdownModel.php queries maintenance_dropdown_tbl; current SQL dumps do not define this table.
- AuthModel.php queries the separate kp201 account/job data through db_user.php.
- Other source functions refer to tables such as branches_tbl and fields absent from the checked-in EMS dump.

## Relationships
The inspected EMS dump does not declare foreign keys between inspection_checklist_tbl and fire_extinguishers_tbl. Application queries associate records using extinguisher_code. Do not infer referential integrity from this logical join.

## Schema caveats
Current Models reference additional fields not present in the checked-in EMS SQL, including some inventory metadata and inspection checklist/rejection/cleaning fields. SQL files may be stale, incomplete, or for a different deployment. The authoritative schema must be inspected on the intended database before migrations or query changes.

## Safety
This document reports schema names only and does not include row data, credentials, password hashes, or personal information. No database query or mutation was performed for this task.
