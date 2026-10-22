# Fire Extinguisher Inventory

## Overview and access
list-extinguisher.php is the active inventory page. It requires a session and calls FireExtinguisherController.php. Admins receive a branch selector; Model/Controller logic scopes records by role and branch. Active list/count Model queries include archived = 0.

## Features
- Search and pagination for active units.
- Placement and condition filters.
- Admin branch selection.
- Add and edit forms, detail view, and record actions.
- Extinguisher code generation and inventory report entry points.
- Dynamic results may use js/list-extinguisher.js.

Search fields and SQL predicates are defined in FireExtinguisherModel.php. Current list and count functions accept placement, condition, and search values; pagination is applied to the row query and the count query supplies the total.

## Use
1. Open Active Fire Extinguishers.
2. Select branch, placement, and condition filters as available.
3. Enter a keyword and use Search or Enter.
4. Navigate result pages; current pagination links retain query parameters.
5. Use the available add, view, edit, archive, or report actions according to your role.

## Processing and data
The Controller delegates list/count retrieval to FireExtinguisherModel.php. The Model reads fire_extinguishers_tbl and uses prepared statements for dynamic query values in the relevant data functions. Add/edit/archive behavior is implemented by Controller/Model functions; archive is represented by an archived flag rather than physical deletion in the ordinary archive path.

Important functions include getAllFireExtinguishers(), getTotalFireExtinguishers(), getAll(), getTotalFireExtinguishersModel(), addNewExtinguisher(), updateExtinguisher(), and delete/restore functions.

## Related files
list-extinguisher.php; backend/controller/FireExtinguisherController.php; backend/model/FireExtinguisherModel.php; js/list-extinguisher.js; helpers/generate-inventory-report.php.

## Limitations
The source SQL dumps do not fully match all current Model columns. Confirm the deployed schema before database changes. Runtime tests were not performed.
