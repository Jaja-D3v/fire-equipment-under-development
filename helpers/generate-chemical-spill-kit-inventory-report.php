<?php
if (
    !defined('CSK_INVENTORY_REPORT_AUTHORIZED') ||
    CSK_INVENTORY_REPORT_AUTHORIZED !== true ||
    !isset($_SESSION['id']) ||
    strtolower(trim($_SESSION['Role'] ?? '')) !== 'admin'
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

function cskInventoryReportTableHeader($pdf, array $widths, $y)
{
    $headers = array(
        'Code',
        'Location',
        'Pads',
        'Boom/Pillow',
        'Socks',
        'Disposal Bag',
        'Goggles',
        'Face Mask',
        'Gloves (Rubber)',
        'Gloves (Nitrile)',
        'Last Date Inspected',
        'Last Inspected By'
    );
    cskInventoryReportCells($pdf, $widths, $headers, $y, 15, true);
    return $y + 15;
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

    // 297 mm landscape A4, less two 8 mm margins.
    $widths = array(18, 70, 22, 22, 22, 22, 22, 22, 22, 22, 50, 50);
    $fields = array('pads', 'boom_pillow', 'socks', 'disposal_bag', 'goggles', 'face_mask', 'rubber_gloves', 'nitrile_gloves');
    $asOfDate = date('F j, Y');
    $y = 0;
    $previousBranch = null;
    // Every page reserves signature space as well as the page-number footer.
    $tableBottom = $pdf->getPageHeight() - 8 - 8 - 22;

    foreach ($records as $record) {
        $values = array(cskInventoryReportText($record['chemical_spill_kit_code'] ?? null), cskInventoryReportText($record['location']));
        foreach ($fields as $field) {
            $values[] = cskInventoryReportText($record[$field] ?? null);
        }
        $values[] = cskInventoryReportInspectionDate($record['last_date_inspected'] ?? null);
        $values[] = cskInventoryReportText($record['last_inspected_by'] ?? null);
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
            $groupHeight = max(7, $pdf->getStringHeight($pdf->getPageWidth() - 16, 'Branch: ' . $recordBranch)) + 15;
        }
        if ($pdf->getNumPages() === 0 || $y + $groupHeight + $rowHeight > $tableBottom) {
            $y = cskInventoryReportStartPage($pdf, $branch, $search, $asOfDate);
            $tableBottom = $pdf->getPageHeight() - 8 - 8 - 22;
            if ($branch === 'all') {
                $y = cskInventoryReportBranchHeader($pdf, $recordBranch, $y);
            }
            $y = cskInventoryReportTableHeader($pdf, $widths, $y);
        } elseif ($newGroup) {
            $y = cskInventoryReportBranchHeader($pdf, $recordBranch, $y);
            $y = cskInventoryReportTableHeader($pdf, $widths, $y);
        }

        // Schema-sized names/locations wrap; fit an exceptionally large legacy value.
        $rowHeight = min($rowHeight, $tableBottom - $y);
        cskInventoryReportCells($pdf, $widths, $values, $y, $rowHeight);
        $y += $rowHeight;
        $previousBranch = $recordBranch;
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
