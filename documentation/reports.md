# Reports

## Inventory report
The active inventory page exposes report actions. backend/controller/GenerateInventoryReportController.php and backend/model/GenerateInventoryReportModel.php provide report data selection. helpers/generate-inventory-report.php uses TCPDF to render the inventory PDF. The report Model selects authorized inventory identifiers by branch; helper code formats and lays out records and summaries.

## Inspection report
Approved inspection UI provides report actions through functions in InspectionController.php and InspectionModel.php. helpers/generate-report.php loads TCPDF and InspectionController.php, prepares report data, and draws report headers, tables, rows, summaries, and prepared-by information.

## QR print output
helpers/print-qr.php uses the installed Composer autoloader for QR label printing.

## Use
1. Open the relevant inventory or inspection page.
2. Apply the filters supported by that page.
3. Select the report action and follow its loading/output flow.
4. Review the generated PDF before distribution.

## Limitations
PDF rendering, page breaks, fonts, installed dependency versions, and report data were not runtime-tested. Do not infer that a report includes all pages or filters beyond its implemented data-selection functions.
