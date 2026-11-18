
<?php

// Rendering only: authorization and data selection belong to the controller/model.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Renders the report title, reporting information, branch identification,
 * and equipment table headers on a new landscape PDF page.
 *
 * This function is also used when equipment records continue onto
 * another page to keep the correct branch identification visible.
 */
function auditPdfTableHeader($pdf, $report, $widths, $branch)
{
    $tableWidth = array_sum($widths);
    $leftMargin = 10;

    // Start a new page using the custom 330 mm x 210 mm landscape dimensions.
    $pdf->AddPage('L', [210, 330]);

    // Display the main report title and selected equipment type.
    $pdf->SetFont('dejavusans', 'B', 11);
    $pdf->MultiCell($tableWidth, 6, 'ANNUAL SAFETY EQUIPMENT INSPECTION MONITORING REPORT FOR', 0, 'C');
    $pdf->SetFont('dejavusans', 'BI', 11);
    $pdf->MultiCell($tableWidth, 6, strtoupper($report['equipment']), 0, 'C');
    $pdf->Ln(2);

    // Company logo
    $logoPath = __DIR__ . '/../assets/img/KPPI-LOGO.webp';

    if (file_exists($logoPath)) {


        $pdf->Image(
            $logoPath,
            5,
            3.3,
            56,
            10,
            'webp'
        );
    }

    // ERT logo
    $logoPath = __DIR__ . '/../assets/img/ERT-LOGO.webp';

    if (file_exists($logoPath)) {


        $pdf->Image(
            $logoPath,
            310,
            3.3,
            13,
            13,
            'webp'
        );
    }

    // Display the reporting year, selected scope, and actual branch of the records.
    $pdf->SetFont('dejavusans', '', 8);
    $pdf->MultiCell($tableWidth, 5, 'Reporting Year: ' . $report['year'], 0, 'L');
    $pdf->SetFont('dejavusans', 'B', 8);
    $pdf->MultiCell($tableWidth, 5, 'Branch: ' . $branch, 0, 'L');

    // Explain the meaning of the inspection indicators displayed in the table.
    $pdf->SetFont('dejavusans', '', 6.5);
    $pdf->MultiCell($tableWidth, 4, 'Current active inventory; all 12 months shown. Green check = completed approved inspection; red X = no completed inspection.', 0, 'L');
    $pdf->Ln(2);

    // Define the equipment information columns and the 12 monthly inspection columns.
    $headers = [
        'No.',
        'Equipment Code',
        'Location',
        'Jan.',
        'Feb.',
        'Mar.',
        'Apr.',
        'May',
        'Jun.',
        'Jul.',
        'Aug.',
        'Sep.',
        'Oct.',
        'Nov.',
        'Dec.'
    ];
    $y = $pdf->GetY();
    $x = $leftMargin;
    $pdf->SetFont('dejavusans', 'B', 6.5);

    // Render each column header using its corresponding column width.
    foreach ($headers as $i => $label) {
        $pdf->MultiCell($widths[$i], 8, $label, 1, 'C', false, 0, $x, $y, true, 0, false, true, 8, 'M');
        $x += $widths[$i];
    }

    // Position the cursor below the table headers for the equipment rows.
    $pdf->SetXY($leftMargin, $y + 8);
}

/**
 * Returns the inspection indicator symbol and its corresponding text color.
 *
 * Green check: completed and approved inspection.
 * Red X: no qualifying completed inspection was found.
 */
function auditPdfInspectionIndicator($completed)
{
    if ($completed) {
        return ['✓', [24, 126, 66]];
    }

    // This report records completed, approved inspections only.
    // A red X indicates that no qualifying inspection was found.
    return ['X', [198, 40, 40]];
}

/**
 * Renders the report heading on a separate summary or closing page.
 *
 * Displays the report title, equipment type, reporting year,
 * and selected scope without an equipment table or branch-specific heading.
 */
function auditPdfSummaryPageHeader($pdf, $report, $tableWidth)
{
    // Start a new landscape page for the report summary or closing sections.
    $pdf->AddPage('L', [210, 330]);

    // Display the report title and selected equipment type.
    $pdf->SetFont('dejavusans', 'B', 11);
    $pdf->MultiCell($tableWidth, 6, 'ANNUAL SAFETY EQUIPMENT INSPECTION MONITORING REPORT FOR', 0, 'C');
    $pdf->SetFont('dejavusans', 'BI', 11);
    $pdf->MultiCell($tableWidth, 6, strtoupper($report['equipment']), 0, 'C');
    $pdf->Ln(2);

    // Display the reporting year and selected branch scope.
    $pdf->SetFont('dejavusans', '', 8);
    $pdf->MultiCell($tableWidth, 5, 'Reporting Year: ' . $report['year']
        . '     Selected scope: ' . $report['branch'], 0, 'L');

    // Explain the inspection qualification rules used for the monthly summary.
    $pdf->SetFont('dejavusans', '', 6.5);
    $pdf->MultiCell($tableWidth, 4, 'Monthly counts use the same completed approved inspection rules as the equipment table.', 0, 'L');
    $pdf->Ln(2);
}

/**
 * Renders the monthly inspection summary table.
 *
 * Displays the number of inspected and not-inspected equipment records
 * for each month using the summary data supplied by the controller/model.
 * This function handles PDF presentation only and does not calculate the counts.
 */
function auditPdfMonthlySummary($pdf, $monthlySummary, $tableWidth, $leftMargin)
{
    // Display the monthly inspection summary section title.
    $pdf->SetFont('dejavusans', 'B', 9);
    $pdf->MultiCell($tableWidth, 6, 'MONTHLY INSPECTION SUMMARY', 0, 'L');
    $pdf->Ln(1);

    // Define the widths and labels of the three summary columns.
    $widths = [120, 95, 95];
    $headers = ['Month', 'Number of Inspected', 'Number of Not Inspected'];
    $y = $pdf->GetY();
    $x = $leftMargin;
    $pdf->SetFont('dejavusans', 'B', 7);

    // Render the summary table column headers.
    foreach ($headers as $index => $header) {
        $pdf->MultiCell(
            $widths[$index],
            6,
            $header,
            1,
            $index === 0 ? 'L' : 'C',
            false,
            0,
            $x,
            $y,
            true,
            0,
            false,
            true,
            6,
            'M'
        );
        $x += $widths[$index];
    }

    // Move the cursor below the column headers before rendering monthly values.
    $pdf->SetXY($leftMargin, $y + 6);
    $pdf->SetFont('dejavusans', '', 7);

    // Render one row for each month using the supplied inspection counts.
    foreach ($monthlySummary as $row) {
        $y = $pdf->GetY();
        $x = $leftMargin;
        $values = [$row['month'], (string) $row['inspected'], (string) $row['not_inspected']];

        // Display the month and its inspected and not-inspected counts.
        foreach ($values as $index => $value) {
            $pdf->MultiCell(
                $widths[$index],
                6,
                $value,
                1,
                $index === 0 ? 'L' : 'C',
                false,
                0,
                $x,
                $y,
                true,
                0,
                false,
                true,
                6,
                'M'
            );
            $x += $widths[$index];
        }

        // Position the cursor below the current monthly summary row.
        $pdf->SetXY($leftMargin, $y + 6);
    }
}

/**
 * Generates the complete Annual Safety Equipment Inspection Monitoring Report.
 *
 * Handles PDF configuration, equipment records grouped by branch,
 * monthly inspection indicators, monthly summary, remarks, and signatures.
 *
 * All report data and inspection calculations are supplied by the
 * controller/model. This function is responsible for PDF rendering only.
 */
function generateAuditReportPdf($report)
{
    // Initialize TCPDF using the custom landscape page dimensions.
    $pdf = new TCPDF('L', 'mm', [210, 330], true, 'UTF-8', false);
    $pdf->SetCreator('SMS Audit & Reports');
    $pdf->SetTitle('Annual Safety Equipment Inspection Monitoring Report — ' . $report['equipment']);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(10, 8, 10);
    $pdf->SetAutoPageBreak(false);
    $pdf->SetTextColor(0);
    $pdf->SetDrawColor(0);
    $pdf->SetLineWidth(0.15);

    // Configure the total table width and individual column widths.
    // TCPDF uses millimetres. Convert the requested 2 px increase at 96 DPI.
    $tableWidth = 310;
    $monthWidth = 15 + (2 * 25.4 / 96);

    // The Branch column is removed because branch identification is displayed above the table.
    // The remaining available table width is assigned to the Location column.
    $locationWidth = $tableWidth - 8 - 28 - (12 * $monthWidth);
    $widths = array_merge([8, 28, $locationWidth], array_fill(0, 12, $monthWidth));
    $leftMargin = 10;

    // Group equipment records by branch so each branch can have its own report pages.
    $rowsByBranch = [];
    foreach ($report['rows'] as $item) {
        $branch = trim((string) ($item['branch'] ?? ''));
        $rowsByBranch[$branch !== '' ? $branch : 'Unassigned'][] = $item;
    }

    $rowNumber = 1;

    // Render equipment records one branch at a time.
    foreach ($rowsByBranch as $branch => $branchRows) {

        // Start each branch on its own page and display its branch heading.
        auditPdfTableHeader($pdf, $report, $widths, $branch);

        foreach ($branchRows as $item) {
            $pdf->SetFont('dejavusans', '', 7);

            // Calculate the row height based on the equipment code and Location text.
            // Longer Location values can wrap onto additional lines.
            $height = max(
                6,
                $pdf->getStringHeight($widths[1], $item['equipment_code']),
                $pdf->getStringHeight($widths[2], (string) $item['location'])
            );

            // Start a new page with the same branch heading if the next row
            // would exceed the available equipment table area.
            if ($pdf->GetY() + $height > 194) {
                auditPdfTableHeader($pdf, $report, $widths, $branch);
                $pdf->SetFont('dejavusans', '', 7);
            }

            // Combine the equipment details and its 12 monthly inspection values.
            $cells = array_merge([(string) $rowNumber, $item['equipment_code'], $item['location']], $item['months']);
            $x = $leftMargin;
            $y = $pdf->GetY();

            // Render each equipment cell and apply the appropriate inspection indicator color.
            foreach ($cells as $i => $text) {
                if ($i >= 3) {
                    [$text, $color] = auditPdfInspectionIndicator($text);
                    $pdf->SetTextColor($color[0], $color[1], $color[2]);
                } else {
                    $pdf->SetTextColor(0);
                }

                $pdf->MultiCell(
                    $widths[$i],
                    $height,
                    $text,
                    1,
                    $i === 2 ? 'L' : 'C',
                    false,
                    0,
                    $x,
                    $y,
                    true,
                    1,
                    false,
                    true,
                    $height,
                    'M'
                );
                $x += $widths[$i];
            }

            // Reset the text color and move the cursor below the current equipment row.
            $pdf->SetTextColor(0);
            $pdf->SetXY($leftMargin, $y + $height);
            $rowNumber++;
        }
    }

    // Display a message when no active equipment records match the selected filters.
    if (!$report['rows']) {
        auditPdfTableHeader($pdf, $report, $widths, $report['branch']);
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->MultiCell($tableWidth, 12, 'No active equipment records for the selected branch.', 1, 'C');
    }

    // Keep the consolidated monthly summary after the final equipment table.
    // Move it to a new page if there is insufficient space for the summary and closing sections.
    $summaryHeight = 85;
    if ($pdf->GetY() + $summaryHeight + 57 > 198) {
        auditPdfSummaryPageHeader($pdf, $report, $tableWidth);
    }

    // Render the monthly inspected and not-inspected counts.
    auditPdfMonthlySummary($pdf, $report['monthly_summary'] ?? [], $tableWidth, $leftMargin);

    // Start another page if the remaining space is insufficient for the closing sections.
    if ($pdf->GetY() + 57 > 198) auditPdfSummaryPageHeader($pdf, $report, $tableWidth);

    $pdf->Ln(4);

    // Display the overall equipment and equipment-month inspection statistics.
    $pdf->SetFont('dejavusans', '', 8);
    $pdf->MultiCell($tableWidth, 5, 'Equipment: ' . $report['total_equipment']
        . '     Completed equipment-months: ' . $report['completed']
        . '     Missing equipment-months: ' . $report['missing'], 0, 'L');

    // Render the Remarks / Notes heading and an empty area for written comments.
    // $pdf->MultiCell($tableWidth, 5, 'Remarks / Notes', 0, 'L');
    // $pdf->MultiCell($tableWidth, 14, '', 1, 'L');
    // $pdf->Ln(5);

    // Define the three report approval and acknowledgment signature sections.
    $y = $pdf->GetY();
    // $signatures = [
    //     ['PREPARED BY', 'Safety Officer / Inspector'],
    //     ['CHECKED BY', 'Safety Officer / Supervisor'],
    //     ['APPROVED BY', 'Department Head / Management']
    // ];

    // Render the signature headings, signature lines, and role descriptions.
    // foreach ($signatures as $i => $signature) {
    //     $x = $leftMargin + $i * 105;
    //     $pdf->SetXY($x, $y);
    //     $pdf->SetFont('dejavusans', 'B', 8);
    //     $pdf->MultiCell(100, 5, $signature[0], 0, 'C');
    //     $pdf->Line($x + 8, $y + 12, $x + 92, $y + 12);
    //     $pdf->SetXY($x, $y + 13);
    //     $pdf->SetFont('dejavusans', '', 7);
    //     $pdf->MultiCell(100, 4, "Name & Signature\n" . $signature[1], 0, 'C');
    // }

    // Add page numbering to every generated PDF page.
    $pages = $pdf->getNumPages();
    for ($page = 1; $page <= $pages; $page++) {
        $pdf->setPage($page);
        $pdf->SetXY($leftMargin, 200);
        $pdf->SetFont('dejavusans', '', 7);
        $pdf->Cell($tableWidth, 4, 'Page ' . $page . ' of ' . $pages, 0, 0, 'R');
    }



    // Return the generated PDF as a string instead of directly sending it to the browser.
    return $pdf->Output('', 'S');
}
