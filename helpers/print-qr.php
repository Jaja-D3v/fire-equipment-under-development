<?php
require_once __DIR__ . '/../backend/controller/QRCodeGeneratorController.php';
require_once __DIR__ . '/../backend/authentication/SessionChecker.php';
require_once __DIR__ . '/../vendor/autoload.php';

$role = strtolower(trim($_SESSION['Role'] ?? ''));
$equipment = $_GET['equipment'] ?? '';
$equipmentDefinition = getQREquipmentDefinition($equipment);
$pageBranch = trim((string)($_GET['branch'] ?? ''));
$items = json_decode($_GET['items'] ?? '', true);
if ($role !== 'admin') {
    http_response_code(403);
    die('You are not authorized to print QR codes.');
}
if ($equipmentDefinition === false || !is_array($items) || !$items) {
    http_response_code(400);
    die('Invalid equipment or no QR codes selected.');
}
if ($pageBranch === '' || $pageBranch === 'none') {
    http_response_code(403);
    die('Select a valid branch before printing.');
}

$records = [];
foreach ($items as $item) {
    if (!is_array($item)) {
        http_response_code(400);
        die('Invalid selected equipment record.');
    }
    $code = trim((string)($item['code'] ?? ''));
    $recordBranch = trim((string)($item['branch'] ?? ''));
    if ($code === '' || !isQRBranchAllowed($equipment, $recordBranch, $pageBranch)) {
        http_response_code(403);
        die('A selected record is outside the current branch scope.');
    }
    $details = getQRRecordByCodeAndBranch($equipment, $code, $recordBranch);
    if (is_array($details) && !empty($details['ambiguous'])) {
        http_response_code(409);
        die('A selected code and branch match multiple active records.');
    }
    if (!$details) {
        http_response_code(404);
        die('A selected active equipment record could not be found.');
    }
    $records[] = ['code' => $details['code'], 'details' => $details];
}


/*  TCPDF */

$pdf = new TCPDF(
    'L',              // Landscape
    'mm',              // Unit
    [64, 36],         // Page size: 64mm x 36mm
    true,
    'UTF-8',
    false
);


$pdf->SetCreator('Fire Extinguisher Management System');
$pdf->SetAuthor('Fire Extinguisher Management System');
$pdf->SetTitle($equipmentDefinition['pdf_title']);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false, 0);



foreach ($records as $record) {
    $code = $record['code'];
    $details = $record['details'];

    $pdf->AddPage();

    // Label border
    $pdf->SetLineWidth(0.5);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->Rect(1, 1, 62, 34);

    // QR code - left side
    $pdf->write2DBarcode(
        $code,
        'QRCODE',
        3,
        8,
        20,
        20,
        [
            'border' => false,
            'padding' => 0,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => [255, 255, 255],
        ],
        'N'
    );

    // Right-side information
    $x = 25;
    $width = 27;
    $DatafontSize = 10.5;
    $LabelfontSize = 10.2;

    // Title
    // $pdf->SetXY(3, 2);
    // $pdf->SetFont('helvetica', 'B', 8);
    // $pdf->Cell($width, 4, 'Fire Extinguisher', 0, 1, 'L');

    // Scan instruction
    // $pdf->SetXY(3, 5.3);
    // $pdf->SetFont('helvetica', '', 7);
    // $pdf->Cell($width, 4, 'SCAN QR CODE', 0, 1, 'L');

    // Extinguisher code and caption
    $pdf->SetXY(6, 28.5);
    $pdf->SetFont('helvetica', 'B', $DatafontSize);
    $pdf->Cell($width, 3, $details['code'], 0, 1, 'L');

    $pdf->SetXY(3, 6.5);
    $pdf->SetFont('helvetica', 'B', $LabelfontSize);
    // $pdf->Cell($width, 3, 'Extinguisher Code', 0, 1, 'L');

    // Branch and caption
    $pdf->SetXY($x + 15, 6.7);
    $pdf->SetFont('helvetica', 'B', $DatafontSize);
    $pdf->Cell($width, 3, $details['branch'] ?? 'N/A', 0, 1, 'L');

    $pdf->SetXY($x, 6.7);
    $pdf->SetFont('helvetica', 'B', $LabelfontSize);
    $pdf->Cell($width, 3, 'Branch:', 0, 1, 'L');

    // Location and caption
    $pdf->SetXY($x, 15);
    $pdf->SetFont('helvetica', 'B', $DatafontSize);
    $pdf->MultiCell(
        $width,
        3,
        $details['location'] ?? 'N/A',
        0,
        'L'
    );

    $pdf->SetXY($x, 11);
    $pdf->SetFont('helvetica', 'B', $LabelfontSize);
    $pdf->Cell($width, 3, 'Location:', 0, 1, 'L');

    // Company logo
    $logoPath =  __DIR__ . '/../assets/img/KPPI-LOGO.webp';


    if (file_exists($logoPath)) {
        $pdf->Image(
            $logoPath,
            2,      // X position
            1.3,      // Y position
            24,      // Width
            4,      // Height
            'webp'
        );
    }


    // Type - bottom left
    // $pdf->SetXY($x, 25.9);
    // $pdf->SetFont('helvetica', 'B', $DatafontSize);
    // $pdf->Cell(10, 3, $details['type'] ?? 'N/A', 0, 0, 'L');

    $pdf->SetXY($x + 9, 32);
    $pdf->SetFont('helvetica', 'I', 6);
    $pdf->Cell(10, 2, 'SYSTEMS MANAGEMENT', 0, 0, 'L');

    // Class - bottom right
    // $pdf->SetXY($x + 20, 2.5);
    // $pdf->SetFont('helvetica', 'B', $DatafontSize);
    // $pdf->Cell(12, 3, $details['class'] ?? 'N/A', 0, 0, 'L');

    // $pdf->SetXY($x + 20, 6.5);
    // $pdf->SetFont('helvetica', '', $LabelfontSize);
    // $pdf->Cell(12, 2, 'Class', 0, 0, 'L');
}



$pdf->Output(
    $equipmentDefinition['pdf_filename'],
    'I'
);
