<?php
if (
    !defined('CSK_INVENTORY_REPORT_AUTHORIZED') ||
    CSK_INVENTORY_REPORT_AUTHORIZED !== true ||
    !isset($_SESSION['id']) ||
    !in_array(strtolower(trim($_SESSION['Role'] ?? '')), ['admin', 'super admin'], true)
) {
    http_response_code(403);
    exit('Access denied.');
}

require_once __DIR__ . '/../vendor/autoload.php';

function cskInventoryReportText($value)
{
    return $value === null || trim((string) $value) === '' ? "\u{2014}" : (string) $value;
}

function cskInventoryReportInspectionDate($value)
{
    if (!$value || strpos((string) $value, '0000-00-00') === 0) {
        return "\u{2014}";
    }
    $timestamp = strtotime($value);
    return $timestamp === false ? "\u{2014}" : date('M d, Y', $timestamp);
}

function cskInventoryReportCells($pdf, array $widths, array $values, $y, $height, $header = false)
{
    $pdf->SetFont('dejavusans', $header ? 'B' : '', $header ? 8 : 8.5);
    $pdf->SetFillColor(235, 235, 235);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.20);
    $x = 8;
    foreach ($values as $index => $value) {
        $align = !$header && $index === 1 ? 'L' : 'C';
        $pdf->MultiCell(
            $widths[$index],
            $height,
            $value,
            1,
            $align,
            $header,
            0,
            $x,
            $y,
            true,
            0,
            false,
            true,
            $height,
            'M',
            true
        );
        $x += $widths[$index];
    }
}

function cskInventoryReportTableHeaderHeight($pdf, array $widths, array $materialColumns)
{
    $headers = array_merge(['Code', 'Location'], array_values($materialColumns), ['Last Date Inspected', 'Last Inspected By']);
    $pdf->SetFont('dejavusans', 'B', 8);
    $height = 15;
    foreach ($headers as $index => $header) {
        $height = max($height, $pdf->getStringHeight($widths[$index], $header));
    }
    return $height;
}

function cskInventoryReportTableHeader($pdf, array $widths, $y, array $materialColumns)
{
    $headers = array_merge(['Code', 'Location'], array_values($materialColumns), ['Last Date Inspected', 'Last Inspected By']);
    $height = cskInventoryReportTableHeaderHeight($pdf, $widths, $materialColumns);
    cskInventoryReportCells($pdf, $widths, $headers, $y, $height, true);
    return $y + $height;
}

// Preserve the active, branch-scoped ordering supplied by the model.  The
// renderer never derives report columns from legacy companion rows.
function cskInventoryReportMaterialColumns(array $records)
{
    $columns = [];
    foreach ($records as $record) {
        foreach (($record['report_materials'] ?? []) as $key => $material) {
            if (!isset($columns[$key])) {
                $columns[$key] = (string) ($material['material_name'] ?? $key);
            }
        }
    }
    return $columns;
}

// A landscape page comfortably supports twelve quantity columns. If there
// are more, distribute columns evenly between sections so a newly added
// material is never rendered alone in an oversized trailing table.
function cskInventoryReportMaterialSections(array $columns, $maximumColumns = 12)
{
    if (!$columns) {
        return [['' => 'Materials']];
    }
    $maximumColumns = max(1, (int) $maximumColumns);
    $total = count($columns);
    $sectionCount = (int) ceil($total / $maximumColumns);
    $baseSize = intdiv($total, $sectionCount);
    $remainder = $total % $sectionCount;
    $sections = [];
    $offset = 0;
    for ($section = 0; $section < $sectionCount; $section++) {
        $size = $baseSize + ($section < $remainder ? 1 : 0);
        $sections[] = array_slice($columns, $offset, $size, true);
        $offset += $size;
    }
    return $sections;
}

function cskInventoryReportRowValues(array $record, array $materialColumns)
{
    $values = [
        cskInventoryReportText($record['chemical_spill_kit_code'] ?? null),
        cskInventoryReportText($record['location'] ?? null)
    ];
    foreach ($materialColumns as $key => $name) {
        $values[] = cskInventoryReportText($record['report_materials'][$key]['current_stock'] ?? null);
    }
    $values[] = cskInventoryReportInspectionDate($record['last_date_inspected'] ?? null);
    $values[] = cskInventoryReportText($record['last_inspected_by'] ?? null);
    return $values;
}

function cskInventoryReportBranchHeader($pdf, $branch, $y)
{
    $pdf->SetFont('dejavusans', 'B', 9);
    $pdf->SetFillColor(220, 220, 220);
    $width = $pdf->getPageWidth() - 16;
    $height = max(7, $pdf->getStringHeight($width, 'Branch: ' . $branch));
    $pdf->MultiCell($width, $height, 'Branch: ' . $branch, 1, 'L', true, 1, 8, $y);
    return $y + $height;
}

function cskInventoryReportStartPage($pdf, $branch, $search, $asOfDate)
{
    $pdf->AddPage('L', array(382, 210));
    $width = $pdf->getPageWidth() - 16;

    // KPPI LOGO
    $logo = __DIR__ . '/../assets/img/KPPI-LOGO.webp';
    if (is_file($logo)) {
        $pdf->Image($logo, 19, 9, 55, 10);
    }

    // ERT LOGO
    $ertLogo = __DIR__ . '/../assets/img/ERT-LOGO.webp';
    if (is_file($ertLogo)) {
        $pdf->Image($ertLogo, 313, 9, 13, 13);
    }


    $pdf->SetFont('dejavusans', 'B', 12);
    $pdf->SetXY(63, 8);
    $pdf->SetFont('dejavusans', 'B', 11);
    $pdf->SetXY(8, 21);
    $pdf->Cell($width, 6, 'CHEMICAL SPILL KIT INVENTORY', 0, 1, 'C');

    $pdf->SetFont('dejavusans', '', 8);
    $pdf->SetXY(8, 28);
    $pdf->MultiCell($width, 5, 'Branch: ' . ($branch === 'all' ? 'All Branches' : $branch) . '    |    Inventory as of ' . $asOfDate, 0, 'L');
    if ($search !== '') {
        $pdf->MultiCell($width, 5, 'Search: ' . $search, 0, 'L');
    }
    return $pdf->GetY() + 3;
}

function buildChemicalSpillKitInventoryReport(array $records, $branch, $search, $preparedBy)
{
    $pdf = new TCPDF('L', 'mm', array(382, 210), true, 'UTF-8', false);
    $pdf->SetCreator('IMS Safety Management System');
    $pdf->SetAuthor('KANEPACKAGE PHILIPPINE INC.');
    $pdf->SetTitle('Chemical Spill Kit Inventory');
    $pdf->SetSubject('Current Chemical Spill Kit inventory with latest inspection information');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(8, 8, 8);
    $pdf->SetAutoPageBreak(false, 8);
    $pdf->setCellPaddings(1, 1, 1, 1);

    $allMaterialColumns = cskInventoryReportMaterialColumns($records);
    $asOfDate = date('F j, Y');
    $y = 0;
    $previousBranch = null;
    // Every page reserves signature space as well as the page-number footer.
    $tableBottom = $pdf->getPageHeight() - 8 - 8 - 22;

    foreach (cskInventoryReportMaterialSections($allMaterialColumns) as $materialColumns) {
        $widths = array_merge([18, 70], array_fill(0, count($materialColumns), 178 / count($materialColumns)), [50, 50]);
        $tableHeaderHeight = cskInventoryReportTableHeaderHeight($pdf, $widths, $materialColumns);
        $y = 0;
        $previousBranch = null;
        $firstSectionRow = true;
        foreach ($records as $record) {
            $values = cskInventoryReportRowValues($record, $materialColumns);
            $pdf->SetFont('dejavusans', '', 8.5);
            $rowHeight = 8;
            foreach ($values as $index => $value) {
                $rowHeight = max($rowHeight, $pdf->getStringHeight($widths[$index], $value));
            }

            $recordBranch = cskInventoryReportText($record['branch']);
            $newGroup = $branch === 'all' && $recordBranch !== $previousBranch;
            $groupHeight = 0;
            if ($newGroup) {
                $pdf->SetFont('dejavusans', 'B', 9);
                $groupHeight = max(7, $pdf->getStringHeight($pdf->getPageWidth() - 16, 'Branch: ' . $recordBranch)) + $tableHeaderHeight;
            }
            if ($firstSectionRow || $y + $groupHeight + $rowHeight > $tableBottom) {
                $y = cskInventoryReportStartPage($pdf, $branch, $search, $asOfDate);
                $tableBottom = $pdf->getPageHeight() - 8 - 8 - 22;
                if ($branch === 'all') {
                    $y = cskInventoryReportBranchHeader($pdf, $recordBranch, $y);
                }
                $y = cskInventoryReportTableHeader($pdf, $widths, $y, $materialColumns);
            } elseif ($newGroup) {
                $y = cskInventoryReportBranchHeader($pdf, $recordBranch, $y);
                $y = cskInventoryReportTableHeader($pdf, $widths, $y, $materialColumns);
            }

            // Schema-sized names/locations wrap; fit an exceptionally large legacy value.
            $rowHeight = min($rowHeight, $tableBottom - $y);
            cskInventoryReportCells($pdf, $widths, $values, $y, $rowHeight);
            $y += $rowHeight;
            $previousBranch = $recordBranch;
            $firstSectionRow = false;
        }
    }

    if ($pdf->getNumPages() === 0) {
        $y = cskInventoryReportStartPage($pdf, $branch, $search, $asOfDate);
    }
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->SetXY(8, $y + 5);
    $pdf->Cell(30, 14, 'Printed By:', 0, 0, 'L');
    $pdf->MultiCell(50, 10, $preparedBy, 'B', 'C', false, 1, 27, $y + 5, true, 0, false, true, 10, 'M', true);

    $pages = $pdf->getNumPages();
    for ($page = 1; $page <= $pages; $page++) {
        $pdf->setPage($page);
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->SetXY(8, $pdf->getPageHeight() - 12);
        $pdf->Cell($pdf->getPageWidth() - 16, 4, 'Page ' . $page . ' of ' . $pages, 0, 0, 'R');
    }
    $pdf->lastPage();
    return $pdf;
}
