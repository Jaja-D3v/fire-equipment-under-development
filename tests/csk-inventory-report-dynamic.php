<?php
// Isolated renderer test: no database connection or production records are used.
define('CSK_INVENTORY_REPORT_AUTHORIZED', true);
$_SESSION = ['id' => 1, 'Role' => 'admin'];
require_once __DIR__ . '/../helpers/generate-chemical-spill-kit-inventory-report.php';

$checks = 0;
function checkReport($condition, $message)
{
    global $checks;
    $checks++;
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function reportRecord($code, $branch, array $materials, array $legacy = [])
{
    $reportMaterials = [];
    foreach ($materials as $name => $stock) {
        $reportMaterials[strtolower($name)] = ['material_name' => $name, 'current_stock' => $stock];
    }
    return [
        'chemical_spill_kit_code' => $code,
        'location' => 'Chemical Storage',
        'branch' => $branch,
        'last_date_inspected' => '2026-11-19',
        'last_inspected_by' => 'Inspector',
        'report_materials' => $reportMaterials,
        // This represents retired/legacy companion data and must not create a column.
        'current_materials' => $legacy
    ];
}

$existing = ['Pads' => 10, 'Boom Pillow' => 3, 'Socks' => 2, 'Disposal Bag' => 4,
    'Goggles' => 1, 'Face Mask' => 5, 'Rubber Gloves' => 6, 'Nitrile Gloves' => 7];
$newMaterial = $existing + ['super admin add' => 11];
$nineMaterialRecord = reportRecord('CSK-001', 'Laguna', $newMaterial);
$nineColumns = cskInventoryReportMaterialColumns([$nineMaterialRecord]);
checkReport(array_values($nineColumns) === array_keys($newMaterial), 'Active configured materials were not collected in configuration order.');
checkReport(count(cskInventoryReportMaterialSections($nineColumns)) === 1, 'A ninth material must remain in the same landscape table.');
checkReport(cskInventoryReportRowValues($nineMaterialRecord, $nineColumns)[10] === '11', 'New material quantity is not mapped to its own column.');

$manyMaterials = [];
for ($i = 1; $i <= 13; $i++) $manyMaterials['Material ' . $i] = $i;
$manyColumns = cskInventoryReportMaterialColumns([reportRecord('CSK-002', 'Laguna', $manyMaterials)]);
$manySections = cskInventoryReportMaterialSections($manyColumns);
checkReport(array_map('count', $manySections) === [7, 6], 'Wide reports must balance material sections instead of leaving one isolated column.');
checkReport(array_keys(array_merge(...$manySections)) === array_keys($manyColumns), 'Material section ordering changed.');

$laguna = reportRecord('CSK-003', 'Laguna', ['Pads' => 9, 'Laguna Only' => 1, 'No Quantity' => null], ['retired' => ['material_name' => 'Retired Material', 'current_stock' => 99]]);
$cebu = reportRecord('CSK-004', 'Cebu', ['Socks' => 8, 'Cebu Only' => 2]);
$lagunaColumns = cskInventoryReportMaterialColumns([$laguna]);
checkReport(array_values($lagunaColumns) === ['Pads', 'Laguna Only', 'No Quantity'], 'Branch-specific report included another branch or retired material.');
checkReport(cskInventoryReportRowValues($laguna, $lagunaColumns)[4] === '—', 'Missing material quantity must render as an em dash.');
checkReport(array_values(cskInventoryReportMaterialColumns([$laguna, $cebu])) === ['Pads', 'Laguna Only', 'No Quantity', 'Socks', 'Cebu Only'], 'All-branch material ordering is not deterministic.');

// Generate PDFs from isolated fixtures. The long fixture forces a vertical page break.
$outputDirectory = __DIR__;
$ninePdf = $outputDirectory . '/csk-inventory-report-nine-materials.pdf';
$ninePdfObject = buildChemicalSpillKitInventoryReport([$nineMaterialRecord], 'Laguna', '', 'Automated Test');
checkReport($ninePdfObject->getNumPages() === 1, 'Nine configured materials should fit on one landscape report page.');
$ninePdfObject->Output($ninePdf, 'F');
checkReport(is_file($ninePdf) && filesize($ninePdf) > 0, 'Nine-material PDF was not generated.');

$widePdf = $outputDirectory . '/csk-inventory-report-wide-materials.pdf';
$widePdfObject = buildChemicalSpillKitInventoryReport([reportRecord('CSK-002', 'Laguna', $manyMaterials)], 'Laguna', '', 'Automated Test');
checkReport($widePdfObject->getNumPages() === 2, 'Thirteen materials should generate two balanced landscape sections.');
$widePdfObject->Output($widePdf, 'F');
checkReport(is_file($widePdf) && filesize($widePdf) > 0, 'Wide-material PDF was not generated.');

$pagedRecords = [];
for ($i = 1; $i <= 30; $i++) $pagedRecords[] = reportRecord('CSK-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT), 'Laguna', $newMaterial);
$pagedPdf = $outputDirectory . '/csk-inventory-report-page-break.pdf';
$pagedPdfObject = buildChemicalSpillKitInventoryReport($pagedRecords, 'Laguna', '', 'Automated Test');
checkReport($pagedPdfObject->getNumPages() > 1, 'Long reports must continue onto additional pages.');
$pagedPdfObject->Output($pagedPdf, 'F');
checkReport(is_file($pagedPdf) && filesize($pagedPdf) > 0, 'Multi-page PDF was not generated.');

echo "PASS: {$checks} isolated report assertions; PDFs generated without database access.\n";
