
<?php

ob_start();

require_once __DIR__ . '/../authentication/SessionChecker.php';
require_once __DIR__ . '/../model/GenerateInspectionReportCsvModel.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

function reportError($message, $statusCode)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    http_response_code($statusCode);
    exit($message);
}

function formatChecklistValue($value)
{
    if ($value === null || $value === '') {
        return 'N/A';
    }

    return ((int) $value === 1) ? '✔' : '✘';
}

function formatReportDate($value)
{
    if (
        $value === null ||
        trim((string) $value) === '' ||
        strpos((string) $value, '0000-00-00') === 0
    ) {
        return 'N/A';
    }

    $timestamp = strtotime($value);

    return ($timestamp === false)
        ? 'N/A'
        : date('M j, Y', $timestamp);
}

function reportValue($value)
{
    return ($value === null || trim((string) $value) === '')
        ? 'N/A'
        : $value;
}

/* Validate IDs. */
$rawIds = isset($_GET['ids']) ? $_GET['ids'] : '';
$ids = array();

foreach (explode(',', $rawIds) as $id) {
    $id = trim($id);

    if ($id !== '' && ctype_digit($id)) {
        $id = (int) $id;

        if ($id > 0) {
            $ids[] = $id;
        }
    }
}

$ids = array_values(array_unique($ids));

if (empty($ids)) {
    reportError('No inspection records selected.', 400);
}

/* Retrieve data from model. */
$rows = getApprovedInspectionReport($ids);

if ($rows === false) {
    reportError('Unable to retrieve inspection records.', 500);
}

if (empty($rows)) {
    reportError('No approved inspection records found.', 404);
}

/* Create workbook. */
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Inspection Report');

$headers = array(
    'FE Code',
    'Location',
    'Capacity',
    'Type',
    'Class',
    'Verified and Approved By',
    'Action Taken',
    'Target Date of Implementation',
    'Comments',
    'Seal',
    'Pin',
    'Pressure',
    'Hose',
    'Nozzle',
    'Belt',
    'Cylinder Body',
    'Demarcation Line',
    'Signage',
    'Cleaning of Unit',
    'Condition Status',
    'Evaluation Status',
    'Branch',
    'Inspected By',
    'Date Inspected'
);

$sheet->mergeCells('A1:X1');
$sheet->setCellValue('A1', 'FIRE EXTINGUISHER INSPECTION REPORT');

$sheet->getStyle('A1:X1')->applyFromArray(array(
    'font' => array(
        'bold' => true,
        'size' => 16,
        'color' => array('rgb' => 'FFFFFF')
    ),
    'fill' => array(
        'fillType' => Fill::FILL_SOLID,
        'startColor' => array('rgb' => '17365D')
    ),
    'alignment' => array(
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    )
));

$sheet->getRowDimension(1)->setRowHeight(34);

$sheet->mergeCells('A2:X2');
$sheet->setCellValue(
    'A2',
    'Approved Inspections | Generated: ' . date('F d, Y')
);

$sheet->getStyle('A2:X2')->applyFromArray(array(
    'font' => array(
        'italic' => true,
        'size' => 10,
        'color' => array('rgb' => '44546A')
    ),
    'fill' => array(
        'fillType' => Fill::FILL_SOLID,
        'startColor' => array('rgb' => 'D9EAF7')
    ),
    'alignment' => array(
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    )
));

$sheet->getRowDimension(2)->setRowHeight(23);
$sheet->fromArray($headers, null, 'A3');

$sheet->getStyle('A3:X3')->applyFromArray(array(
    'font' => array(
        'bold' => true,
        'color' => array('rgb' => 'FFFFFF'),
        'size' => 10
    ),
    'fill' => array(
        'fillType' => Fill::FILL_SOLID,
        'startColor' => array('rgb' => '2874A6')
    ),
    'alignment' => array(
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true
    ),
    'borders' => array(
        'bottom' => array(
            'borderStyle' => Border::BORDER_MEDIUM,
            'color' => array('rgb' => '17365D')
        )
    )
));

$sheet->getRowDimension(3)->setRowHeight(42);

$checklistColumns = array('J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S');
$rowNumber = 4;

foreach ($rows as $row) {
    $checks = array(
        $row['is_seal_ok'],
        $row['is_pin_ok'],
        $row['is_pressure_ok'],
        $row['is_hose_ok'],
        $row['is_nozzle_ok'],
        $row['is_belt_ok'],
        $row['is_cylinder_body_ok'],
        $row['is_demarcation_line_ok'],
        $row['is_signage_ok'],
        $row['is_cleaning_of_unit_ok']
    );

    $allGood = true;

    foreach ($checks as $value) {
        if ($value === null || $value === '' || (int) $value !== 1) {
            $allGood = false;
            break;
        }
    }

    $data = array(
        $row['extinguisher_code'],
        $row['location'],
        $row['capacity'],
        $row['type'],
        $row['class'],
        reportValue($row['verified_and_approved_by']),
        reportValue($row['action_taken']),
        formatReportDate($row['target_date_of_implementation']),
        reportValue($row['remarks']),
        formatChecklistValue($row['is_seal_ok']),
        formatChecklistValue($row['is_pin_ok']),
        formatChecklistValue($row['is_pressure_ok']),
        formatChecklistValue($row['is_hose_ok']),
        formatChecklistValue($row['is_nozzle_ok']),
        formatChecklistValue($row['is_belt_ok']),
        formatChecklistValue($row['is_cylinder_body_ok']),
        formatChecklistValue($row['is_demarcation_line_ok']),
        formatChecklistValue($row['is_signage_ok']),
        formatChecklistValue($row['is_cleaning_of_unit_ok']),
        $allGood ? 'Good' : 'Not Good',
        $row['evaluation_status'],
        $row['branch'],
        $row['inspected_by'],
        formatReportDate($row['date_inspected'])
    );

    foreach ($data as $index => $value) {
        $column = Coordinate::stringFromColumnIndex($index + 1);

        $sheet->setCellValueExplicit(
            $column . $rowNumber,
            $value === null ? '' : (string) $value,
            DataType::TYPE_STRING
        );
    }

    if ($rowNumber % 2 === 0) {
        $sheet->getStyle('A' . $rowNumber . ':X' . $rowNumber)
            ->getFill()->setFillType(Fill::FILL_SOLID);

        $sheet->getStyle('A' . $rowNumber . ':X' . $rowNumber)
            ->getFill()->getStartColor()->setRGB('F2F6FA');
    }

    $sheet->getStyle('T' . $rowNumber)->applyFromArray(array(
        'fill' => array(
            'fillType' => Fill::FILL_SOLID,
            'startColor' => array(
                'rgb' => $allGood ? 'E2F0D9' : 'FCE4D6'
            )
        ),
        'font' => array(
            'bold' => true,
            'color' => array(
                'rgb' => $allGood ? '38761D' : 'C00000'
            )
        )
    ));

    /* Branch column V: blue background, white text. */
    $sheet->getStyle('V' . $rowNumber)->applyFromArray(array(
        'fill' => array(
            'fillType' => Fill::FILL_SOLID,
            'startColor' => array('rgb' => 'b0e2ff')
        ),
        'font' => array(
            'bold' => true,
            'color' => array('rgb' => '005687')
        )
    ));

    foreach ($checklistColumns as $column) {
        $cell = $column . $rowNumber;
        $value = $sheet->getCell($cell)->getValue();

        if ($value === '✔') {
            $color = '008000';
        } elseif ($value === '✘') {
            $color = '0070C0';
        } else {
            continue;
        }

        $sheet->getStyle($cell)->applyFromArray(array(
            'font' => array(
                'bold' => true,
                'color' => array('rgb' => $color),
                'size' => 12
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            )
        ));
    }

    $rowNumber++;
}

$lastRow = $rowNumber - 1;

$sheet->getStyle('A3:X' . $lastRow)->applyFromArray(array(
    'borders' => array(
        'allBorders' => array(
            'borderStyle' => Border::BORDER_THIN,
            'color' => array('rgb' => 'D9E2F3')
        )
    )
));


$sheet->getStyle('A4:X' . $lastRow)
    ->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_CENTER)
    ->setWrapText(true);


foreach (range('A', 'X') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

$sheet->calculateColumnWidths();

$maxWidths = array(
    'A' => 18,
    'B' => 24,
    'C' => 12,
    'D' => 18,
    'E' => 12,
    'F' => 28,
    'G' => 32,
    'H' => 24,
    'I' => 38,
    'J' => 10,
    'K' => 10,
    'L' => 12,
    'M' => 10,
    'N' => 10,
    'O' => 10,
    'P' => 18,
    'Q' => 20,
    'R' => 12,
    'S' => 18,
    'T' => 18,
    'U' => 20,
    'V' => 18,
    'W' => 24,
    'X' => 18
);

foreach ($maxWidths as $column => $width) {
    if ($sheet->getColumnDimension($column)->getWidth() > $width) {
        $sheet->getColumnDimension($column)->setAutoSize(false);
        $sheet->getColumnDimension($column)->setWidth($width);
    }
}

foreach ($checklistColumns as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(false);
    $sheet->getColumnDimension($column)->setWidth(11);
}

$sheet->setAutoFilter('A3:X' . $lastRow);
$sheet->freezePane('A4');

$sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A3);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);
$sheet->getPageSetup()->setFitToPage(true);
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 3);

while (ob_get_level() > 0) {
    ob_end_clean();
}

if (headers_sent()) {
    exit('Cannot generate Excel report because output has already started.');
}

$filename = 'Inspection Report ' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

$spreadsheet->disconnectWorksheets();
unset($spreadsheet);

exit;
