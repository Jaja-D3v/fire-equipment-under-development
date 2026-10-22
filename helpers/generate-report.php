<?php

// Load required libraries and application dependencies.
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../backend/controller/InspectionController.php';

// Parse the inspection IDs supplied through the request.
$ids = [];

if (isset($_GET['ids']) && !empty($_GET['ids'])) {

    $ids = array_filter(
        array_map(
            'trim',
            explode(',', $_GET['ids'])
        )
    );
}

if (empty($ids)) {
    die('No inspection records selected.');
}

// Retrieve approved inspection records through the controller layer.
$inspections = getApprovedInspectionReportsController($ids);

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

// Initialize the inspection report PDF.
$pdf = new TCPDF(

    'L',
    'mm',
    'A4',
    true,
    'UTF-8',
    false

);

$pdf->SetCreator( 'IMS Safety Management System' );
$pdf->SetAuthor( 'KanePackage Philippine Inc.' );
$pdf->SetTitle( 'Fire Extinguisher Inspection Checksheet' );
$pdf->SetSubject( 'Approved Fire Extinguisher Inspection Report' );
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(
    8,
    8,
    8
);

$pdf->SetAutoPageBreak(
    true,
    8
);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$marginLeft   = 8;
$marginTop    = 8;
$marginRight  = 8;
$marginBottom = 8;

$pdf->SetMargins(
    $marginLeft,
    $marginTop,
    $marginRight
);

$pdf->SetAutoPageBreak(
    false
);

$pdf->AddPage('L', 'A4');

// Define report assets.
$companyLogo = __DIR__ . '/../assets/img/KPPI-LOGO.webp';
$ertLogo     = __DIR__ . '/../assets/img/ERT-LOGO.webp';

// Prepare values displayed in the report header.
// ========================================================
// FORMAT DATE INSPECTED
// ========================================================

$inspectionDates = array();

foreach ($inspections as $inspection) {

    if (
        isset($inspection['date_inspected']) &&
        trim($inspection['date_inspected']) !== ''
    ) {
        $timestamp = strtotime($inspection['date_inspected']);

        if ($timestamp !== false) {
            $inspectionDates[] = $timestamp;
        }
    }
}

$dateInspected = '';

$branches = array();

foreach ($inspections as $inspection) {

    if (
        isset($inspection['branch']) &&
        trim($inspection['branch']) !== ''
    ) {
        $branches[] = trim($inspection['branch']);
    }
}

$branches = array_unique($branches);

$branch = !empty($branches)
    ? implode(', ', $branches)
    : 'All Branches';

if (!empty($inspectionDates)) {

    // Remove duplicate dates
    $inspectionDates = array_unique($inspectionDates);

    // Get unique date + month + year combinations
    $dateGroups = array();

    foreach ($inspectionDates as $timestamp) {

        $year  = date('Y', $timestamp);
        $month = date('n', $timestamp);
        $day   = date('j', $timestamp);

        $key = $year . '-' . $month;

        if (!isset($dateGroups[$key])) {
            $dateGroups[$key] = array(
                'year'  => $year,
                'month' => $month,
                'days'  => array()
            );
        }

        $dateGroups[$key]['days'][] = $day;
    }

    // Sort by year/month
    ksort($dateGroups);

    $formattedDates = array();

    foreach ($dateGroups as $group) {

        $days = array_unique($group['days']);
        sort($days);

        // Same exact day
        if (count($days) === 1) {

            $formattedDates[] =
                date(
                    'M. j, Y',
                    mktime(
                        0,
                        0,
                        0,
                        (int) $group['month'],
                        (int) $days[0],
                        (int) $group['year']
                    )
                );
        } else {

            // Same month/year but different days
            $formattedDates[] =
                date(
                    'M. Y',
                    mktime(
                        0,
                        0,
                        0,
                        (int) $group['month'],
                        1,
                        (int) $group['year']
                    )
                );
        }
    }

    $dateInspected = implode(', ', $formattedDates);
}

$inspectedNames = array();

foreach ($inspections as $inspection) {
    $name = trim($inspection['inspected_by'] ?? '');

    if (
        $name !== '' &&
        !in_array($name, $inspectedNames, true)
    ) {
        $inspectedNames[] = $name;
    }
}

$inspectedBy = implode("\n", $inspectedNames);



$approvedNames = [];

foreach ($inspections as $inspection) {

    $name = trim(
        $inspection['verified_and_approved_by'] ?? ''
    );

    if (
        $name !== '' &&
        !in_array($name, $approvedNames, true)
    ) {
        $approvedNames[] = $name;
    }
}

$approvedBy = implode("
", $approvedNames);

// Define the report table column widths.
$width = [
    'no'          => 15,
    'location'    => 25,

    'capacity'    => 13,
    'type'        => 13,
    'class'       => 13,

    'seal'        => 12,
    'pin'         => 12,
    'pressure'    => 12,
    'hose'        => 12,
    'nozzle'      => 12,
    'belt'        => 12,
    'cylinder'    => 12,
    'demarcation' => 12,
    'signage'     => 12,
    'cleaning'    => 12,

    'comments'    => 30,
    'status'      => 12,
    'action'      => 19,
    'target'      => 20
];

// Render the report title, logos, and inspection information.
function drawReportHeader(
    $pdf,
    $companyLogo,
    $ertLogo,
    $dateInspected,
    $inspectedBy,
    $approvedBy,
    $branch,
    $marginLeft,
    $marginTop
) {

    $font = 'helvetica';

    $headerX = 8;
    $headerY = 8;

    $logoX = 10;
    $logoY = 12;
    $logoW = 55;
    $logoH = 9;

    $titleX = 64;
    $titleY = 10;
    $titleW = 78;

    $ertX = 140;
    $ertY = 11;
    $ertW = 13;
    $ertH = 13;

    $infoX = 158;
    $infoY = 9;
    $infoW = 130;

    if (is_file($companyLogo)) {

        $pdf->Image(
            $companyLogo,
            $logoX,
            $logoY,
            $logoW,
            $logoH,
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
    } else {

        $pdf->SetDrawColor(
            150,
            150,
            150
        );

        $pdf->SetLineWidth(
            0.25
        );

        $pdf->Rect(
            $logoX,
            $logoY,
            $logoW,
            $logoH
        );

        $pdf->Line(
            $logoX,
            $logoY,
            $logoX + $logoW,
            $logoY + $logoH
        );

        $pdf->Line(
            $logoX + $logoW,
            $logoY,
            $logoX,
            $logoY + $logoH
        );

        $pdf->SetTextColor(
            100,
            100,
            100
        );

        $pdf->SetFont(
            $font,
            'B',
            7
        );

        $pdf->SetXY(
            $logoX,
            $logoY + 6
        );

        $pdf->Cell(
            $logoW,
            4,
            'COMPANY LOGO',
            0,
            1,
            'C'
        );

        $pdf->SetFont(
            $font,
            '',
            7
        );

        $pdf->SetXY(
            $logoX,
            $logoY + 11
        );

        $pdf->Cell(
            $logoW,
            3,
            '(REPLACE IMAGE)',
            0,
            0,
            'C'
        );
    }

    $pdf->SetTextColor(
        0,
        0,
        0
    );

    $pdf->SetFont(
        $font,
        'B',
        14
    );

    $pdf->SetXY(
        $titleX,
        $titleY
    );

    $pdf->Cell(
        $titleW,
        8,
        'FIRE EXTINGUISHER',
        0,
        1,
        'C'
    );

    $pdf->SetFont(
        $font,
        'B',
        14
    );

    $pdf->SetXY(
        $titleX,
        $titleY + 7
    );

    $pdf->Cell(
        $titleW,
        8,
        'INSPECTION CHECKSHEET',
        0,
        0,
        'C'
    );

    if (is_file($ertLogo)) {

        $pdf->Image(
            $ertLogo,
            $ertX,
            $ertY,
            $ertW,
            $ertH,
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
    } else {

        $pdf->SetDrawColor(
            150,
            150,
            150
        );

        $pdf->SetLineWidth(
            0.25
        );

        $pdf->Rect(
            $ertX,
            $ertY,
            $ertW,
            $ertH
        );

        $pdf->Line(
            $ertX,
            $ertY,
            $ertX + $ertW,
            $ertY + $ertH
        );

        $pdf->Line(
            $ertX + $ertW,
            $ertY,
            $ertX,
            $ertY + $ertH
        );

        $pdf->SetTextColor(
            100,
            100,
            100
        );

        $pdf->SetFont(
            $font,
            'B',
            8
        );

        $pdf->SetXY(
            $ertX,
            $ertY + 5
        );

        $pdf->Cell(
            $ertW,
            3,
            'ERT LOGO',
            0,
            1,
            'C'
        );

        $pdf->SetFont(
            $font,
            '',
            8
        );

        $pdf->SetXY(
            $ertX,
            $ertY + 9
        );

        $pdf->Cell(
            $ertW,
            3,
            '(REPLACE IMAGE)',
            0,
            0,
            'C'
        );
    }

    $infoColW = $infoW / 3;

    $labelH = 7;
    $valueH = 14;

    $infoItems = [

        [
            'Date Inspected',
            $dateInspected,
            ''
        ],

        [
            'Inspected by',
            $inspectedBy,
            '(ERT)'
        ],

        [
            'Verified & Approved by',
            $approvedBy,
            '(SO)'
        ]

    ];

    foreach ($infoItems as $i => $item) {

        $x = $infoX + (
            $i * $infoColW
        );

        $pdf->SetFillColor(
            215,
            215,
            215
        );

        $pdf->SetDrawColor(
            80,
            80,
            80
        );

        $pdf->SetLineWidth(
            0.20
        );

        $pdf->SetTextColor(
            0,
            0,
            0
        );

        $pdf->SetFont(
            $font,
            '',
            7
        );

        $pdf->SetXY(
            $x,
            $infoY
        );

        $pdf->Cell(
            $infoColW,
            $labelH,
            $item[0],
            1,
            0,
            'C',
            true
        );

        $pdf->SetFillColor(
            255,
            255,
            255
        );

        $pdf->SetXY(
            $x,
            $infoY + $labelH
        );

        $pdf->Cell(
            $infoColW,
            $valueH,
            '',
            1,
            0,
            'C',
            true
        );

        if (
            trim($item[1]) !== ''
        ) {

            $pdf->SetTextColor(
                0,
                0,
                0
            );

            $pdf->SetFont(
                $font,
                '',
                9
            );

            if ($i === 1 || $i === 2) {

                $nameLines = preg_split(
                    '/\R+/',
                    trim($item[1])
                );

                $lineH = 3.5;

                $currentY = $infoY + $labelH + 1.5;

                foreach ($nameLines as $nameLine) {

                    $nameLine = trim($nameLine);

                    if ($nameLine === '') {
                        continue;
                    }

                    $pdf->SetXY(
                        $x + 1,
                        $currentY
                    );

                    $pdf->Cell(
                        $infoColW - 2,
                        $lineH,
                        $nameLine,
                        0,
                        0,
                        'C'
                    );

                    $currentY += $lineH;
                }
            } else {

                $pdf->SetXY(
                    $x,
                    $infoY + $labelH + 3
                );

                $pdf->Cell(
                    $infoColW,
                    4,
                    $item[1],
                    0,
                    0,
                    'C'
                );
            }
        }

        if (
            trim($item[2]) !== ''
        ) {

            $pdf->SetFont(
                $font,
                '',
                7
            );

            $pdf->SetXY(
                $x,
                $infoY + $labelH + 10
            );

            $pdf->Cell(
                $infoColW,
                3,
                $item[2],
                0,
                0,
                'C'
            );
        }
    }

    $pdf->SetTextColor(
        0,
        0,
        0
    );

    $pdf->SetDrawColor(
        0,
        0,
        0
    );

    $pdf->SetLineWidth(
        0.20
    );

    $pdf->SetFont(
        $font,
        '',
        7
    );

    // ========================================================
    // BRANCH
    // ========================================================

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor(70, 70, 70);

    $pdf->SetXY($marginLeft, 25);

    $pdf->Cell(
        0,
        5,
        'Branch: ' . $branch,
        0,
        1,
        'L'
    );
}

// Render the table headers and column groups.
function drawTableHeader(
    $pdf,
    $width,
    $tableX,
    $tableY
) {

    $font = 'dejavusans';

    $groupH  = 7;
    $headerH = 11;

    $pdf->SetFont($font, '', 6.7);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColor(215, 215, 215);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.20);

    $x = $tableX;

    foreach (
        [
            'No.'      => 'no',
            'Location' => 'location'
        ] as $label => $key
    ) {

        $pdf->MultiCell(
            $width[$key],
            $groupH + $headerH,
            $label,
            1,
            'C',
            true,
            0,
            $x,
            $tableY,
            true,
            0,
            false,
            true,
            $groupH + $headerH,
            'M',
            true
        );

        $x += $width[$key];
    }

    $unitWidth =
        $width['capacity'] +
        $width['type'] +
        $width['class'];

    $pdf->SetXY($x, $tableY);

    $pdf->Cell(
        $unitWidth,
        $groupH,
        'Unit Description',
        1,
        0,
        'C',
        true
    );

    $unitY = $tableY + $groupH;

    $ux = $x;

    foreach (
        [
            'Capacity' => 'capacity',
            'Type'     => 'type',
            'Class'    => 'class'
        ] as $label => $key
    ) {

        $pdf->MultiCell(
            $width[$key],
            $headerH,
            $label,
            1,
            'C',
            true,
            0,
            $ux,
            $unitY,
            true,
            0,
            false,
            true,
            $headerH,
            'M',
            true
        );

        $ux += $width[$key];
    }

    $checkpointWidth =
        $width['seal'] +
        $width['pin'] +
        $width['pressure'] +
        $width['hose'] +
        $width['nozzle'] +
        $width['belt'] +
        $width['cylinder'] +
        $width['demarcation'] +
        $width['signage'] +
        $width['cleaning'];

    $pdf->SetXY($ux, $tableY);

    $pdf->Cell(
        $checkpointWidth,
        $groupH,
        'Checkpoints  (NOTE: Put "√" if GOOD; "X" if NO GOOD)',
        1,
        0,
        'C',
        true
    );

    $checkpointHeaders = [
        'Seal' => 'seal',
        'Pin' => 'pin',
        "Pressure
(195
psi)" => 'pressure',
        'Hose' => 'hose',
        'Nozzle' => 'nozzle',
        'Belt' => 'belt',
        "Cylinder
(Body)" => 'cylinder',
        "Demar-
cation
line" => 'demarcation',
        'Signage' => 'signage',
        "Cleaning
of unit" => 'cleaning'
    ];

    $cx = $ux;

    foreach ($checkpointHeaders as $label => $key) {

        $pdf->MultiCell(
            $width[$key],
            $headerH,
            $label,
            1,
            'C',
            true,
            0,
            $cx,
            $unitY,
            true,
            0,
            false,
            true,
            $headerH,
            'M',
            true
        );

        $cx += $width[$key];
    }

    $rightHeaders = [
        'Comments' => 'comments',
        "Status
(\"√\" or
\"X\")" => 'status',
        'Action Taken' => 'action',
        "Target Date of
Implementation" => 'target'
    ];

    $rx = $cx;

    foreach ($rightHeaders as $label => $key) {

        $pdf->MultiCell(
            $width[$key],
            $groupH + $headerH,
            $label,
            1,
            'C',
            true,
            0,
            $rx,
            $tableY,
            true,
            0,
            false,
            true,
            $groupH + $headerH,
            'M',
            true
        );

        $rx += $width[$key];
    }

    return $tableY + $groupH + $headerH;
}

// Render one inspection record in the report table.
function drawInspectionRow(
    $pdf,
    $width,
    $data,
    $no,
    $x,
    $y,
    $rowHeight
) {

    $font = 'helvetica';

    $pdf->SetFont($font, '', 7);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.20);

    $pdf->SetFont('helvetica', '', 9);

    $pdf->SetXY($x, $y);

    $pdf->Cell(
        $width['no'],
        $rowHeight,
        $data['extinguisher_code'] ?? '',
        1,
        0,
        'C'
    );

    $x += $width['no'];

    $location = $data['location'] ?? '';

    $pdf->SetFont('helvetica', '', 9);

    $pdf->Rect(
        $x,
        $y,
        $width['location'],
        $rowHeight
    );

    $textWidth = $width['location'] - 2;

    if ($pdf->GetStringWidth($location) <= $textWidth) {

        $pdf->SetXY(
            $x + 1,
            $y
        );

        $pdf->Cell(
            $textWidth,
            $rowHeight,
            $location,
            0,
            0,
            'C'
        );
    } else {

        $pdf->SetXY(
            $x + 1,
            $y
        );

        $pdf->MultiCell(
            $textWidth,
            4,
            $location,
            0,
            'L',
            false,
            0,
            $x + 1,
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

    $x += $width['location'];

    $pdf->SetFont('helvetica', '', 9);

    foreach (
        [
            'capacity' => $data['capacity'] ?? '',
            'type'     => $data['type'] ?? '',
            'class'    => $data['class'] ?? ''
        ] as $key => $value
    ) {

        $pdf->Rect(
            $x,
            $y,
            $width[$key],
            $rowHeight
        );

        $textWidth = $width[$key] - 2;

        if ($pdf->GetStringWidth($value) <= $textWidth) {

            $pdf->SetXY(
                $x + 1,
                $y
            );

            $pdf->Cell(
                $textWidth,
                $rowHeight,
                $value,
                0,
                0,
                'C'
            );
        } else {

            $pdf->SetXY(
                $x + 1,
                $y
            );

            $pdf->MultiCell(
                $textWidth,
                4,
                $value,
                0,
                'L',
                false,
                0,
                $x + 1,
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

        $x += $width[$key];
    }

    $checkpoints = [
        'seal'        => 'is_seal_ok',
        'pin'         => 'is_pin_ok',
        'pressure'    => 'is_pressure_ok',
        'hose'        => 'is_hose_ok',
        'nozzle'      => 'is_nozzle_ok',
        'belt'        => 'is_belt_ok',
        'cylinder'    => 'is_cylinder_body_ok',
        'demarcation' => 'is_demarcation_line_ok',
        'signage'     => 'is_signage_ok',
        'cleaning'    => 'is_cleaning_of_unit_ok'
    ];

    foreach ($checkpoints as $column => $field) {

        $value = $data[$field] ?? null;

        if ($value === null || $value === '') {

            $symbol = '';
        } else {

            $symbol = ((int) $value === 1)
                ? '√'
                : 'X';
        }

        $pdf->SetFont(
            'dejavusans',
            '',
            9
        );

        $pdf->SetXY(
            $x,
            $y
        );

        $pdf->Cell(
            $width[$column],
            $rowHeight,
            $symbol,
            1,
            0,
            'C'
        );

        $x += $width[$column];
    }

    $comments = $data['extinguisher_remarks'] ?? '';

    $pdf->SetFont('helvetica', '', 9);

    $pdf->Rect(
        $x,
        $y,
        $width['comments'],
        $rowHeight
    );

    $textWidth = $width['comments'] - 2;

    if ($pdf->GetStringWidth($comments) <= $textWidth) {

        $pdf->SetXY(
            $x + 1,
            $y
        );

        $pdf->Cell(
            $textWidth,
            $rowHeight,
            $comments,
            0,
            0,
            'C'
        );
    } else {

        $pdf->SetXY(
            $x + 1,
            $y
        );

        $pdf->MultiCell(
            $textWidth,
            4,
            $comments,
            0,
            'L',
            false,
            0,
            $x + 1,
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

    $x += $width['comments'];

    $pdf->SetFont(
        'dejavusans',
        '',
        9
    );

    $statusRaw = trim(
        (string) ($data['status'] ?? '')
    );

    $status = strtolower($statusRaw);

    if (
        in_array(
            $status,
            ['good', 'ok', 'pass', 'passed', '1'],
            true
        )
    ) {

        $statusSymbol = '√';
    } elseif (
        in_array(
            $status,
            ['no good', 'bad', 'fail', 'failed', '0'],
            true
        )
    ) {

        $statusSymbol = 'X';
    } else {

        $statusSymbol = $statusRaw;
    }

    $pdf->SetXY(
        $x,
        $y
    );

    $pdf->Cell(
        $width['status'],
        $rowHeight,
        $statusSymbol,
        1,
        0,
        'C'
    );

    $x += $width['status'];

    $actionTaken = $data['action_taken'] ?? '';

    $pdf->SetFont('helvetica', '', 9);

    $pdf->Rect(
        $x,
        $y,
        $width['action'],
        $rowHeight
    );

    $textWidth = $width['action'] - 2;

    if ($pdf->GetStringWidth($actionTaken) <= $textWidth) {

        $pdf->SetXY(
            $x + 1,
            $y
        );

        $pdf->Cell(
            $textWidth,
            $rowHeight,
            $actionTaken,
            0,
            0,
            'C'
        );
    } else {

        $pdf->SetXY(
            $x + 1,
            $y
        );

        $pdf->MultiCell(
            $textWidth,
            4,
            $actionTaken,
            0,
            'L',
            false,
            0,
            $x + 1,
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

    $x += $width['action'];

    $pdf->SetFont(
        'helvetica',
        '',
        9
    );

    $pdf->SetXY(
        $x,
        $y
    );

    $pdf->Cell(
        $width['target'],
        $rowHeight,
        $data['target_date_of_implementation'] ?? '',
        1,
        0,
        'C'
    );
}

drawReportHeader(
    $pdf,
    $companyLogo,
    $ertLogo,
    $dateInspected,
    $inspectedBy,
    $approvedBy,
    $branch,
    $marginLeft,
    $marginTop
);

$tableY = 30;

$dataY = drawTableHeader(
    $pdf,
    $width,
    $marginLeft,
    $tableY
);

// Configure row dimensions and pagination.
$rowHeight = 14;
$no = 1;
$rowsPerPage = 11;
$rowCount = 0;

foreach ($inspections as $data) {

    if ($rowCount >= $rowsPerPage) {

        $pdf->AddPage('L', 'A4');

        drawReportHeader(
            $pdf,
            $companyLogo,
            $ertLogo,
            $dateInspected,
            $inspectedBy,
            $approvedBy,
            $branch,
            $marginLeft,
            $marginTop
        );

        $tableY = 30;

        $dataY = drawTableHeader(
            $pdf,
            $width,
            $marginLeft,
            $tableY
        );

        $rowCount = 0;
    }

    drawInspectionRow(
        $pdf,
        $width,
        $data,
        $no,
        $marginLeft,
        $dataY,
        $rowHeight
    );

    $dataY += $rowHeight;

    $no++;
    $rowCount++;
}

if (($dataY + 15) > (297 - $marginBottom)) {

    $pdf->AddPage('L', 'A4');

    drawReportHeader(
        $pdf,
        $companyLogo,
        $ertLogo,
        $dateInspected,
        $inspectedBy,
        $approvedBy,
        $branch,
        $marginLeft,
        $marginTop
    );

    $dataY = 38;
}

$pdf->SetXY(
    $marginLeft,
    $dataY + 3
);

$pdf->SetFont(
    'dejavusans',
    'I',
    7
);

$pdf->SetFont(
    'dejavusans',
    '',
    7
);

$pdf->Cell(
    0,
    5,
    'Evaluation Status: APPROVED',
    0,
    1,
    'L'
);

// Send the generated report to the browser.
$pdf->Output(
    'fire-extinguisher-inspection-report.pdf',
    'I'
);
