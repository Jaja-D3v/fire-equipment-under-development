# QR Code Management

## Overview
QR functionality is split between QR generation/printing and QR scanning for inspection lookup.

## Generate and print
QR-generator.php lists extinguisher codes and branches using QRCodeGeneratorController.php and QRCodeGeneratorModel.php. The page and js/QR-generator.js support selecting/generating labels. helpers/print-qr.php uses installed Composer dependencies to produce printable output. Source behavior should be checked for the selected branch and list scope before bulk printing.

## Scan
QR-code.php displays the scan and inspection workflow. js/QR-code.js reads a scanned extinguisher code and requests backend/controller/ScanQRCodeController.php with the getFeInfo action. The Controller delegates to ScanQRCodeModel.php, which retrieves extinguisher details by code. The inspection form posts to InspectionController.php.

## Use
1. Open QR Generator and choose the available branch/codes.
2. Generate or print the QR labels using the page actions.
3. Open QR Code scanning on a supported camera device.
4. Scan the label and review the returned extinguisher identity/details.
5. Continue to the inspection checklist if the displayed unit is correct.

## Data and limitations
QR payloads identify an extinguisher by its code; they are not described as authorization credentials. Camera permissions, printer output, and device compatibility were not runtime-tested.
