# Maintenance and Dropdown Management

## Maintenance page
maintenance.php is the maintenance interface and requires a session. It uses the existing class-based MaintenanceController and MaintenanceModel, an exception to the otherwise mostly procedural application. The page handles POST actions and displays maintenance data with pagination. Controller actions include index, add, update, and delete paths.

## Dropdown sources
backend/model/DropdownModel.php supplies branch, placement, capacity, type, and class dropdown data through DropdownController.php. These options are used by inventory, maintenance, QR, and inspection pages. The source also contains differing branch sources in separate code paths; verify the live tables before assuming they are interchangeable.

## Use
1. Open Maintenance from navigation if authorized.
2. Choose the applicable maintenance category/section.
3. Add, edit, or delete a maintenance/dropdown item using the page's form controls.
4. Confirm changes in the rendered list and dependent dropdowns.

## Data and access
Maintenance page/controller code handles POST actions and database calls through its Model. Dropdown data is retrieved from maintenance_dropdown_tbl in DropdownModel.php. Session/role restrictions should be confirmed for each action, not inferred from navigation visibility.

## Limitations
The checked-in EMS dumps do not include all maintenance/dropdown tables used in source. Live schema and permissions were not tested.
