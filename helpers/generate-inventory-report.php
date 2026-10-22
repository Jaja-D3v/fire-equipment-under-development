<?php

// sample link:
// http://localhost/EMS_Home/helpers/generate-inventory-report.php?ids=54,53,58,57,56,1,2,3,4,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,84,85,86,87,88,89,90
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../backend/controller/InspectionController.php';

/*
* * FIRE EXTINGUISHER INVENTORY REPORT*
* * Layout based on the supplied reference:*
* * - Landscape A4*
* * - Company / ERT logos*
* * - Inventory, Schedule and Details title*
* * - Good / No Good legend*
* * - Inventory table*
* * - Summary*
* * - No Good Spare Fire Extinguishers*
* * - Prepared by*
* */

$ids = array();

if (isset($_GET['ids']) && trim($_GET['ids']) !== '') {
    $ids = array_filter(
        array_map('trim', explode(',', $_GET['ids']))

    );
}

if (empty($ids)) {

    die('No inspection records selected.');
}

$inspections = getFireExtinguishersForInventoryReportController($ids);

$branch = 'All Branches';

if (!empty($inspections)) {
    $branches = array();

    foreach ($inspections as $inspection) {
        if (isset($inspection['branch']) && trim($inspection['branch']) !== '') {
            $branches[] = trim($inspection['branch']);
        }
    }

    $branches = array_unique($branches);

    if (count($branches) === 1) {
        $branch = reset($branches);
    } elseif (count($branches) > 1) {
        $branch = 'All Branches';
    }
}

if (empty($inspections)) {

    die('No approved inspection records found.');
}

/* --------------------------------------------------------------------------*
* * Helpers*
* * -------------------------------------------------------------------------- */

function reportValue($data, $keys, $default = '')
{
    foreach ($keys as $key) {

        if (isset($data[$key]) && trim((string) $data[$key]) !== '') {

            return trim((string) $data[$key]);
        }
    }
    return $default;
}

function formatManufacturedDate($value)
{

    if ($value === '') {
        return '';
    }

    $timestamp = strtotime($value);

    if ($timestamp !== false) {
        return date('M. Y', $timestamp);
    }

    return $value;
}

function isGoodCondition($data)
{
    $status = strtolower(
        trim(
            (string) reportValue(
                $data,
                array('status', 'condition_status', 'condition'),
                ''
            )
        )
    );

    if ($status !== '') {
        if (in_array($status, array(
            'good',
            'ok',
            'pass',
            'passed',
            '1',
            'true'
        ), true)) {
            return true;
        }



        if (in_array($status, array(

            'no good',
            'no good',
            'bad',
            'fail',
            'failed',
            '0',
            'false'
        ), true)) {

            return false;
        }
    }

    /*
*     * Fallback for records where status is not populated.*
*     * If at least one inspection checkpoint is explicitly 0, the unit*
*     * is considered No Good.*
*     */

    $checkpoints = array(
        'is_seal_ok',
        'is_pin_ok',
        'is_pressure_ok',
        'is_hose_ok',
        'is_nozzle_ok',
        'is_belt_ok',
        'is_cylinder_body_ok',
        'is_demarcation_line_ok',
        'is_signage_ok',
        'is_cleaning_of_unit_ok'
    );

    $hasCheckpoint = false;
    foreach ($checkpoints as $field) {
        if (isset($data[$field]) && $data[$field] !== '') {
            $hasCheckpoint = true;

            if ((int) $data[$field] === 0) {

                return false;
            }
        }
    }
    return $hasCheckpoint ? true : true;
}



function isSpareUnit($data)
{
    $placement = strtolower(
        trim(
            (string) reportValue(
                $data,
                array(
                    'placement',
                    'placement_type',
                    'position',
                    'unit_placement'
                ),

                ''
            )
        )
    );
    return $placement === 'storage';
}



function drawCell($pdf, $x, $y, $w, $h, $text, $align = 'C', $fontSize = 7)

{

    $pdf->SetFont('helvetica', '', $fontSize);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.20);
    $pdf->SetXY($x, $y);
    $pdf->MultiCell(
        $w,
        $h,
        $text,
        1,
        $align,
        false,
        0,
        $x,
        $y,
        true,
        0,
        false,
        true,
        $h,
        'M',
        true
    );
}

function drawPageNumber($pdf)
{
    $pageWidth = $pdf->getPageWidth();
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(80, 80, 80);
    $pdf->SetXY($pageWidth - 210, 291);
    $pdf->Cell(32, 4, 'Page ' . $pdf->getAliasNumPage() . ' of ' . $pdf->getAliasNbPages(), 0, 0, 'R');
    $pdf->SetTextColor(0, 0, 0);
}

function drawReportHeader(

    $pdf,
    $companyLogo,
    $ertLogo,
    $asOfDate,
    $branch,
    $marginLeft,
    $marginRight

) {

    $pageWidth = $pdf->getPageWidth();
    drawPageNumber($pdf);



    /* Company logo. */

    if (is_file($companyLogo)) {
        $pdf->Image(
            $companyLogo,
            7,
            5,
            60,
            10,
            '',
            '',
            '',
            false,
            300,
            '',
            false,
            false,
            0,
            false,
            false,
            false

        );
    }




    /* branch */
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor(70, 70, 70);
    $pdf->SetXY($marginLeft, 35);
    $pdf->Cell(
        55,
        5,
        'Branch: ' . $branch,
        0,
        0,
        'L'
    );



    /* ERT logo. */

    if (is_file($ertLogo)) {

        $pdf->Image(

            $ertLogo,
            $pageWidth - $marginRight - 16,
            5,
            14,
            14,
            '',
            '',
            '',
            false,
            300,
            '',
            false,
            false,
            0,
            false,
            false,
            false
        );
    }



    /* Main title. */

    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetXY(65, 21);
    $pdf->Cell(
        $pageWidth - 130,
        6,
        'FIRE EXTINGUISHER INVENTORY, SCHEDULE and DETAILS',
        0,
        1,
        'C'
    );

    /* Date line. */
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetXY(65, 27);
    $pdf->Cell(
        $pageWidth - 130,
        5,
        'As of ' . $asOfDate,
        0,
        1,
        'C'

    );

    /* Legend. */
    $legendY = 34;
    $centerX = $pageWidth / 2;

    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetXY($centerX - 58, $legendY);
    $pdf->Cell(28, 4, 'LEGEND:', 0, 0, 'R');

    $pdf->SetFillColor(102, 255, 102);
    $pdf->Rect($centerX - 29, $legendY - 0.5, 5, 5, 'DF');

    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetXY($centerX - 23, $legendY);
    $pdf->Cell(27, 4, 'GOOD', 0, 0, 'L');

    $pdf->SetFillColor(255, 0, 0);
    $pdf->Rect($centerX + 13, $legendY - 0.5, 5, 5, 'DF');

    $pdf->SetXY($centerX + 19, $legendY);
    $pdf->Cell(32, 4, 'NO GOOD', 0, 0, 'L');

    $pdf->SetFillColor(255, 255, 255);
}



function drawInventoryTableHeader($pdf, $x, $y, $widths)
{
    $headers = array(

        'FE NO.',
        'Location',
        'Capacity',
        'Type',
        'Class',
        'Manufactured',
        'Placement',
        'Condition',
        'Remarks'
    );



    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColor(235, 235, 235);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.20);

    $currentX = $x;

    foreach ($headers as $index => $header) {
        $w = $widths[$index];
        $pdf->SetXY($currentX, $y);
        $pdf->MultiCell(
            $w,
            7,
            $header,
            1,
            'C',
            true,
            0,
            $currentX,
            $y,
            true,
            0,
            false,
            true,
            7,
            'M',
            true
        );

        $currentX += $w;
    }
    $pdf->SetFillColor(235, 235, 235);

    return $y + 7;
}



function drawInventoryRow($pdf, $x, $y, $widths, $data, $number, $rowHeight)

{

    $location = reportValue($data, array('location', 'branch', 'area'));
    $capacity = reportValue($data, array('capacity'));
    $type = reportValue($data, array('type', 'extinguisher_type'));
    $class = reportValue($data, array('class', 'fire_class'));
    $manufactured = formatManufacturedDate(

        reportValue(
            $data,
            array(
                'manufactured',
                'manufactured_date',
                'date_manufactured',
                'manufacturing_date'
            )

        )

    );

    $placement = reportValue(
        $data,
        array(
            'placement',
            'placement_type',
            'position',
            'unit_placement'
        )

    );



    $remarks = reportValue(
        $data,
        array(
            'extinguisher_remarks',
            'remarks',
            'remark'
        )
    );

    $good = isGoodCondition($data);

    $extinguisherCode = reportValue(
        $data,
        array('extinguisher_code')
    );



    $values = array(

        $extinguisherCode,
        $location,
        $capacity,
        $type,
        $class,
        $manufactured,
        $placement,
        '',
        $remarks
    );

    $currentX = $x;

    foreach ($values as $index => $value) {
        $w = $widths[$index];

        if ($index === 7) {

            $pdf->SetFillColor(

                $good ? 102 : 255,
                $good ? 255 : 0,
                $good ? 102 : 0

            );



            $pdf->SetDrawColor(0, 0, 0);
            $pdf->Rect($currentX, $y, $w, $rowHeight, 'DF');



            /*

*             * Keep the condition cell clean like the reference.*
*             * The color itself communicates Good / No Good.*

*             */
        } else {

            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont(
                'helvetica',
                '',
                $index === 1 || $index === 8 ? 6.6 : 7

            );



            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetLineWidth(0.20);

            $pdf->SetXY($currentX, $y);
            $pdf->MultiCell(

                $w,
                $rowHeight,
                $value,
                1,
                'C',
                true,
                0,
                $currentX,
                $y,
                true,
                0,
                false,
                true,
                $rowHeight,
                'M',
                true
            );
        }

        $currentX += $w;
    }

    return $good;
}



function drawSummary($pdf, $x, $y, $installedGood, $installedBad, $spareGood, $spareBad)

{

    $summaryWidth = 138;
    $labelWidth = 68;
    $goodWidth = 17;
    $badWidth = 20;
    $totalWidth = 33;
    $rowHeight = 5.5;
    $titleHeight = 6;



    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.20);

    /* Header row. */
    $pdf->SetFillColor(235, 235, 235);
    $pdf->SetXY($x, $y);
    $pdf->MultiCell(

        $labelWidth,
        $titleHeight,
        'Summary as of ' . date('F j, Y'),
        1,
        'C',
        true,
        0,
        $x,
        $y,
        true,
        0,
        false,
        true,
        $titleHeight,
        'M',
        true
    );

    $currentX = $x + $labelWidth;

    foreach (array('Good' => $goodWidth, 'No Good' => $badWidth) as $label => $w) {

        $pdf->SetXY($currentX, $y);
        $pdf->Cell($w, $titleHeight, $label, 1, 0, 'C');
        $currentX += $w;
    }

    $pdf->SetXY($currentX, $y);
    $pdf->Cell($totalWidth, $titleHeight, '', 1, 1, 'C');

    $rows = array(

        array('Installed', $installedGood, $installedBad),
        array('Spare', $spareGood, $spareBad)

    );



    foreach ($rows as $row) {

        $pdf->SetFillColor(235, 235, 235);

        $pdf->SetXY($x, $y + $titleHeight);
        $pdf->Cell($labelWidth, $rowHeight, $row[0], 1, 0, 'C');

        $pdf->Cell($goodWidth, $rowHeight, (string) $row[1], 1, 0, 'C');
        $pdf->Cell($badWidth, $rowHeight, (string) $row[2], 1, 0, 'C');
        $pdf->Cell($totalWidth, $rowHeight, '', 1, 1, 'C');

        $y += $rowHeight;
    }

    $totalGood = $installedGood + $spareGood;
    $totalBad = $installedBad + $spareBad;
    $grandTotal = $totalGood + $totalBad;

    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetXY($x, $y + $titleHeight);
    $pdf->Cell($labelWidth, $rowHeight, 'Total:', 1, 0, 'C');
    $pdf->Cell($goodWidth, $rowHeight, (string) $totalGood, 1, 0, 'C');
    $pdf->Cell($badWidth, $rowHeight, (string) $totalBad, 1, 0, 'C');

    $pdf->SetFillColor(255, 255, 102);
    $pdf->Cell($totalWidth, $rowHeight, (string) $grandTotal, 1, 1, 'C');

    $pdf->SetFillColor(255, 255, 255);

    return $y + $titleHeight + $rowHeight;
}

function drawNoGoodDetails($pdf, $x, $y, $records)
{
    $width = 203;
    $locationWidth = 68;
    $feWidth = 17;
    $commentsWidth = 54;
    $remarksWidth = $width - $locationWidth - $feWidth - $commentsWidth;

    $titleHeight = 6;
    $headerHeight = 6;
    $rowHeight = 6;

    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.20);

    $pdf->SetXY($x, $y);
    $pdf->Cell(

        $width,
        $titleHeight,
        'Details of No Good Fire Extinguishers',
        1,
        1,
        'C'
    );

    $headerY = $y + $titleHeight;

    $headers = array(

        array('Location', $locationWidth),
        array('FE No.', $feWidth),
        array('Comments', $commentsWidth),
        array('Remarks', $remarksWidth)

    );

    $currentX = $x;

    foreach ($headers as $header) {

        $pdf->SetXY($currentX, $headerY);
        $pdf->Cell($header[1], $headerHeight, $header[0], 1, 0, 'C');
        $currentX += $header[1];
    }

    $currentY = $headerY + $headerHeight;

    if (empty($records)) {

        $pdf->SetXY($x, $currentY);
        $pdf->Cell(
            $width,
            $rowHeight,
            'No No Good spare fire extinguishers.',
            1,
            1,
            'C'
        );

        return $currentY + $rowHeight;
    }


    $pdf->SetFont('helvetica', '', 7);

    foreach ($records as $record) {

        $location = reportValue(

            $record,
            array('location', 'branch', 'area')

        );

        $feNo = reportValue(

            $record,
            array('extinguisher_code', 'fe_no', 'code')

        );



        // Comments = remarks from fire_extinguishers_tbl*
        $comments = reportValue(

            $record,
            array('remarks')

        );

        // Remarks = condition_status from fire_extinguishers_tbl*

        $remarks = reportValue(

            $record,
            array('condition_status')

        );



        $currentX = $x;



        foreach (

            array(
                array($location, $locationWidth),
                array($feNo, $feWidth),
                array($comments, $commentsWidth),
                array($remarks, $remarksWidth)

            ) as $cell

        ) {

            $pdf->SetXY($currentX, $currentY);

            $pdf->MultiCell(
                $cell[1],
                $rowHeight,
                $cell[0],
                1,
                'C',
                false,
                0,
                $currentX,
                $currentY,
                true,
                0,
                false,
                true,
                $rowHeight,
                'M',
                true
            );

            $currentX += $cell[1];
        }
        $currentY += $rowHeight;
    }
    return $currentY;
}



function drawPreparedBy($pdf, $x, $y, $preparedBy)
{

    $pdf->SetFont('helvetica', '', 7);

    // Prepared by label*
    $pdf->SetXY($x, $y);
    $pdf->Cell(30, 4, 'Printed by:', 0, 0, 'L');

    // Signature line*
    $lineX = $x + 15;
    $lineY = $y + 4;
    $lineWidth = 50;

    $pdf->SetLineWidth(0.20);
    $pdf->Line(

        $lineX,
        $lineY,
        $lineX + $lineWidth,
        $lineY

    );


    // Prepared by name*
    $pdf->SetXY($lineX, $y - 1);
    $pdf->Cell(
        $lineWidth,
        4,
        trim($preparedBy),
        0,
        0,
        'C'

    );

    // Safety Officer*
    $pdf->SetXY($lineX, $y + 4.5);
    $pdf->Cell(
        $lineWidth,
        4,
        'Safety Officer',
        0,
        0,
        'C'
    );
}

/* --------------------------------------------------------------------------*

* * PDF initialization*

* * -------------------------------------------------------------------------- */

$pdf = new TCPDF(

    'P',
    'mm',
    'A4',
    true,
    'UTF-8',
    false

);



$pdf->SetCreator('IMS Safety Management System');
$pdf->SetAuthor('KanePackage Philippine Inc.');
$pdf->SetTitle('Fire Extinguisher Inventory, Schedule and Details');
$pdf->SetSubject('Fire Extinguisher Inventory Report');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$marginLeft = 3;
$marginTop = 8;
$marginRight = 8;
$marginBottom = 8;

$pdf->SetMargins(

    $marginLeft,
    $marginTop,
    $marginRight

);

$pdf->SetAutoPageBreak(false, $marginBottom);

/* Assets. */
$companyLogo = __DIR__ . '/../assets/img/KPPI-LOGO.webp';
$ertLogo = __DIR__ . '/../assets/img/ERT-LOGO.webp';

/* Report date. */
$firstInspection = $inspections[0];

$reportDate = reportValue(
    $firstInspection,
    array(

        'date_inspected',
        'inspection_date',
        'as_of_date'

    )

);

$asOfDate = $reportDate !== ''

    ? date('F j, Y', strtotime($reportDate))
    : date('F j, Y');

/*

* * Table widths sum to the A4 landscape printable width:*

* * 297mm - 8mm left - 8mm right = 281mm.*

* */

$widths = array(

    12, /* FE No. */
    39, /* Location */
    14, /* Capacity */
    19, /* Type */
    15, /* Class */
    26, /* Manufactured */
    26, /* Placement */
    14, /* Condition */
    39  /* Remarks */

);



$tableX = $marginLeft;
$tableStartY = 42;
$rowHeight = 8;

// ============================================================
// ROWS PER PAGE SETTINGS
// ============================================================

// setting nunber row per page 
$firstPageRows = 30;
$otherPageRows = 34;

/* --------------------------------------------------------------------------*

* * First pass: calculate summary data.*

* * -------------------------------------------------------------------------- */

$installedGood = 0;
$installedBad = 0;
$spareGood = 0;
$spareBad = 0;

$noGoodRecords = array();

foreach ($inspections as $record) {

    $good = isGoodCondition($record);
    $spare = isSpareUnit($record);

    if ($spare) {
        if ($good) {
            $spareGood++;
        } else {
            $spareBad++;
            $noGoodRecords[] = $record;
        }
    } else {

        if ($good) {

            $installedGood++;
        } else {

            $installedBad++;
            $noGoodRecords[] = $record;
        }
    }
}

/* --------------------------------------------------------------------------*
* * Render report.*
* * -------------------------------------------------------------------------- */

$pdf->AddPage('P', 'A4');

drawReportHeader(

    $pdf,
    $companyLogo,
    $ertLogo,
    $asOfDate,
    $branch,
    $marginLeft,
    $marginRight

);

$dataY = drawInventoryTableHeader(

    $pdf,
    $tableX,
    $tableStartY,
    $widths

);

$rowNumber = 1;
$pageRowCount = 0;

/*
* * We render data based on the available space instead of hard-coding the*
* * number of rows in the actual report. This prevents overlap on different*
* * data sets.*
* */

$totalRecords = count($inspections);

$rowNumber = 1;
$pageRowCount = 0;
$pageNumber = 1;

// ============================================================
// RENDER INVENTORY DATA
// ============================================================

foreach ($inspections as $record) {

    // --------------------------------------------------------
    // Determine the row limit for the current page.
    //
    // Page 1  = $firstPageRows
    // Page 2+ = $otherPageRows
    // --------------------------------------------------------
    $currentPageLimit = ($pageNumber === 1)
        ? $firstPageRows
        : $otherPageRows;

    // --------------------------------------------------------
    // Create a new page when the current page reaches its limit.
    // --------------------------------------------------------
    if ($pageRowCount >= $currentPageLimit) {

        $pdf->AddPage('P', 'A4');
        $pageNumber++;

        // Page number for succeeding pages.
        drawPageNumber($pdf);

        // ----------------------------------------------------
        // IMPORTANT:
        // Page 2 onwards does NOT use drawReportHeader().
        // This removes the large header/logo area.
        // ----------------------------------------------------
        $dataY = drawInventoryTableHeader(
            $pdf,
            $tableX,
            7,
            $widths
        );

        $pageRowCount = 0;
    }

    // --------------------------------------------------------
    // Draw the inventory row.
    // --------------------------------------------------------
    drawInventoryRow(
        $pdf,
        $tableX,
        $dataY,
        $widths,
        $record,
        $rowNumber,
        $rowHeight
    );

    $dataY += $rowHeight;
    $rowNumber++;
    $pageRowCount++;
}

/* --------------------------------------------------------------------------*
* * Summary and closing sections.*
* * -------------------------------------------------------------------------- */

$summaryY = $dataY + 4;

if ($summaryY + 60 > 202) {

    $pdf->AddPage('P', 'A4');
    drawPageNumber($pdf);
    $summaryY = 15;
}



$summaryBottom = drawSummary(

    $pdf,
    3,
    $summaryY,
    $installedGood,
    $installedBad,
    $spareGood,
    $spareBad

);

$detailsY = $summaryBottom + 7;

$detailsBottom = drawNoGoodDetails(

    $pdf,
    3,
    $detailsY,
    $noGoodRecords

);


/*
* * Prepared by must always use the currently logged-in user.*
* * Do not get this value from the inspection records.*
* */

$preparedBy = isset($_SESSION['EmployeeName'])
    ? trim((string) $_SESSION['EmployeeName'])
    : '';

if ($preparedBy === '') {

    $preparedBy = 'Unknown User';
}



drawPreparedBy(

    $pdf,
    $marginLeft,
    $detailsBottom + 7,
    $preparedBy

);

/* --------------------------------------------------------------------------*
* * Output.*
* * -------------------------------------------------------------------------- */

$pdf->Output(

    'fire-extinguisher-inventory-report.pdf',
    'I'

);
