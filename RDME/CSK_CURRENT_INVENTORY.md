# CSK current inventory

## Storage and scope

Current quantities are read and written exclusively through
chemical_spill_kit_current_inventory_materials_tbl.current_stock.
The parent chemical_spill_kit_materials_inventory_tbl still stores kit identity,
branch, location and archive metadata. The connected database inspected on 2026-11-09
no longer has the eight legacy parent quantity columns. Revised current-inventory
queries neither select nor write those columns and never fall back to them.
There is no dual write, schema change, migration or orphan cleanup in this revision.
No material_id relationship has been added.

The intended quantity contract is a nullable signed INT range. The current live
column is still VARCHAR(255) NULL; application validation enforces the integer range.
This task does not change that schema. NULL remains unknown; it is never replaced
with a parent quantity or silently converted to zero. Add forms explicitly start
new quantities at zero; inspection and unknown existing quantities require entry.

Read-only regression checks against the connected ems_db on 2026-11-09 found five
active Laguna kits and 40 populated linked companion quantities, all valid whole
numbers. Cebu and Lima currently have no kits. There are also 457 orphan companion
rows with NULL quantities; none were deleted or populated. This supersedes the
earlier four-kit/32-NULL-row snapshot. See REGRESSION_TEST_REPORT.md for current
live application results and the tests that still require isolated acceptance data.

## Dynamic materials and validation

getRequiredStockModel(branch) remains the source of branch-configured material
names and minimum quantities. getChemicalSpillKitConfiguredMaterials() converts
that configuration into name-keyed definitions. Exact duplicate configuration
names and equivalent normalized names are rejected instead of silently merged.

chemicalSpillKitMaterialKey() trims and lowercases names and treats Boom/Pillow
and Boom Pillow as the same material. New current companion records use Boom Pillow;
existing companion spellings are retained. New inspection snapshots retain the
configured checklist spelling. All other materials come from configuration/records,
not a fixed application list. Indexed HTML fields carry material_name as data so
spaces, punctuation and future names do not become column names or CSS selectors.

getChemicalSpillKitCurrentMaterials() retrieves singular current_stock directly.
mergeChemicalSpillKitMaterials() includes newly configured materials without rows
and retains existing materials no longer configured. Existing unconfigured materials
remain editable and keep their stored condition; configured conditions are calculated
server-side against Required Stock. Their remarks remain editable. No records are deleted.

validateChemicalSpillKitMaterials() checks all expected configured/current names,
rejects unknown/duplicate/missing materials, and validates quantities and remarks.
Quantities must be nonempty decimal-digit whole numbers from 0 to 2147483647;
negative, fractional, exponential, nonnumeric and out-of-range input is rejected.
JavaScript also validates, but submitted statuses never override server calculations.
Empty branch configuration blocks new inventory/inspection creation.

## Important functions and write flow

- getAllChemicalSpillKit(): paginated, branch-filtered metadata plus companion materials.
- getChemicalSpillKitByCode(): active kit lookup scoped to code and branch, with companion materials.
- attachChemicalSpillKitMaterials(): merges current records with branch configuration for display.
- addNewChemicalSpillKit(): validates active branch/code and dynamic materials, creates only
  parent metadata, then inserts companions in the same transaction.
- updateSpillKitMaterials(): locks the active parent by kit ID and branch, updates location,
  and saves companion quantities/statuses/remarks in one transaction.
- saveChemicalSpillKitCurrentMaterials(): updates by kit ID and stored material_name or inserts
  a missing named companion. It reuses existing aliases instead of creating duplicates.
- insertChemicalInspection(): saves a dynamic historical checklist and pending transaction;
  submitting an inspection does not change current inventory.
- updateChemicalSpillKitEvaluation(): retains pending-only approval/rejection and role/branch checks.
- updateChemicalSpillKitInventoryFromInspection(): locks/verifies the kit, reads the saved checklist
  and synchronizes companion quantities, statuses and remarks within the approval transaction.
- chemicalSpillKitApprovedMaterials(): rejects empty/duplicate/invalid checklist snapshots before
  synchronizing INT quantities. Existing historical rows and plural current_stocks are never rewritten.

Approval copies exactly the inspected snapshot, including materials removed from current
configuration. Materials introduced after that inspection are left untouched. Historical fractional
or otherwise invalid quantities cannot be copied into INT current_stock; approval fails and rolls
back instead of truncating history. Rejection never synchronizes current quantities.

Controllers retain session, role, branch and response handling; all SQL remains in Models.
Non-admin inventory writes use the session branch. Inspection submissions verify the session role,
assigned branch and active database kit. Authenticated Required Stock responses retain required_stock
for compatibility and additionally expose the dynamic materials array used by revised forms.

## Screens and reports

The existing add/edit/details/inspection layouts render dynamic cards. Details and edit forms use
companion quantities; new configured rows appear without changing schema. Unknown stock is shown
as unknown, with blank edit fields requiring valid entry. The shared script preserves same-branch
add drafts, ignores stale branch responses and blocks saving while configuration is unavailable.
The scanner's actual-stock-input hook remains available.

The inventory PDF retrieves companion quantities and derives its material columns dynamically.
Groups of up to eight columns repeat on additional pages; eight is a presentation group size,
not a material limit. Existing branch filters and latest-inspection metadata are retained.
CSK history/evaluation displays iterate saved checklist names, including new and retired materials.
Those displays still read historical current_stocks, never current companion stock.

## Files changed

- backend/model/ChemicalSpillKitMaterialModel.php (new shared procedural material functions)
- backend/model/ChemicalSpillKitModel.php
- backend/model/ChemicalSpillKitInspectionModel.php
- backend/model/ChemicalSpillKitRequiredStockModel.php
- backend/model/GenerateChemicalSpillKitInventoryReportModel.php
- backend/model/MyInspectionsModel.php (CSK history display only)
- backend/model/RejectedInspectionModel.php (CSK history display only)
- backend/controller/ChemicalSpillKitController.php
- backend/controller/ChemicalSpillKitInspectionController.php
- backend/controller/ChemicalSpillKitRequiredStockController.php
- chemical-spill-kit-inventory.php
- partials/add-chemical-spill-kit-check-sheet.php
- partials/edit-chemical-spill-kit-check-sheet.php
- partials/chemical-spill-kit-check-sheet.php
- js/csk-materials.js (new shared card rendering/validation)
- helpers/generate-chemical-spill-kit-inventory-report.php
- tests/csk-current-inventory.php (new isolated procedural PHP regression test)
- tests/csk-materials.html (new browser fixture with all requests mocked)
- CSK_CURRENT_INVENTORY.md

## Verification performed

- PHP syntax checks on changed/new PHP files, JavaScript syntax checks, and git diff --check.
- php -n tests/csk-current-inventory.php: 124 isolated assertions passed; tests exercise eight-material creation,
  adding a ninth configured material, editing, name aliases, integer validation, unknown/duplicate
  submissions, retired materials, dynamic inspection creation, pending-only approval, rejection,
  cross-branch rejection and rollback on simulated failures. Every mysqli operation is an array
  stub; the test loads model functions without database/config includes and cannot connect to MySQL.
- Headless Chrome executed tests/csk-materials.html: 39 browser assertions passed using the actual
  shared JavaScript and native DOM. Covered dynamic ninth fields, quantities/statuses, invalid submit
  blocking, escaping names, same-branch drafts, empty branches, blank inspection fields, stale responses,
  duplicate Boom aliases and request errors. All fetch responses were mocked.
- SELECT-only live model checks: Laguna returns four kits/eight configured materials; Cebu and Lima
  return zero kits/zero configured materials. List and code lookup companion results agree;
  legacy quantity fields are absent from returned kit metadata. Report retrieval returns four kits.
- Actual Required Stock controller CLI probes used synthetic in-memory sessions: Admin/Laguna
  returned 200/eight materials, Admin/Cebu and Admin/Lima 200/zero, Inspector cross-branch and Viewer
  requests returned 403. Session files were not written.
- Actual PHP details partial rendered companion quantity 123 and a ninth material from in-memory
  fixtures while ignoring a deliberately conflicting legacy parent quantity.
- Actual TCPDF renderer generated an eight-material one-page PDF and a ninth-material two-page PDF
  with branch changes, both in memory. No PDF files were saved.

No database writes, schema operations or migrations were executed, including during testing.
Live add/edit/submission/approval/rejection persistence tests were not run because they would
modify records. Read-only browser testing now covers temporary Admin/Inspector sessions,
inventory forms, QR lookup, checklist loading, rejected-list filters and live PDF generation;
credential login and write-based acceptance still require an authorized isolated environment. Current-inventory page loads fetch materials per displayed kit;
no unrelated query architecture or caching changes were introduced. Reload an open edit page after
changing branch material configuration; server validation rejects stale/incomplete submissions.
