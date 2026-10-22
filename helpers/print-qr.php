<?php
require_once __DIR__ . '/../backend/controller/QRCodeGeneratorController.php';
require_once __DIR__ . '/../backend/authentication/SessionChecker.php';
require_once __DIR__ . '/../vendor/autoload.php';

$codes = [];

if (isset($_GET['codes']) && !empty($_GET['codes'])) {
    $codes = array_filter(
        array_map('trim', explode(',', $_GET['codes']))
    );
}

if (empty($codes)) {
    die('No QR codes selected.');
}

$branch = getAuthorizedQRPrintBranch($_GET['branch'] ?? null);
if ($branch === false) {
    http_response_code(403);
    die('You are not authorized to print QR codes for this branch.');
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
$pdf->SetTitle('Fire Extinguisher QR Codes');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false, 0);



foreach ($codes as $code) {

    $details = getFireExtinguisherQRDetails($code, $branch);

    if (!$details) {
        continue;
    }

    $pdf->AddPage();

    // Label border
    $pdf->SetLineWidth(0.5);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->Rect(1, 1, 62, 34);

    // QR code - left side
    $pdf->write2DBarcode(
        $code,
        'QRCODE',
        2,
        3,
        30,
        30,
        [
            'border' => false,
            'padding' => 0,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => [255, 255, 255],
        ],
        'N'
    );

    // Right-side information
    $x = 35;
    $width = 27;
    $DatafontSize = 5.5;
    $LabelfontSize = 4.2;

    // Title
    $pdf->SetXY($x, 2);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell($width, 4, 'Fire Extinguisher', 0, 1, 'L');

    // Scan instruction
    $pdf->SetXY($x, 5.3);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->Cell($width, 4, 'SCAN QR CODE', 0, 1, 'L');

    // Extinguisher code and caption
    $pdf->SetXY($x, 10);
    $pdf->SetFont('helvetica', 'B', $DatafontSize);
    $pdf->Cell($width, 3, $details['extinguisher_code'], 0, 1, 'L');

    $pdf->SetXY($x, 12);
    $pdf->SetFont('helvetica', '', $LabelfontSize);
    $pdf->Cell($width, 3, 'Fire Extinguisher Code', 0, 1, 'L');

    // Branch and caption
    $pdf->SetXY($x, 15);
    $pdf->SetFont('helvetica', 'B', $DatafontSize);
    $pdf->Cell($width, 3, $details['branch'] ?? 'N/A', 0, 1, 'L');

    $pdf->SetXY($x, 17);
    $pdf->SetFont('helvetica', '', $LabelfontSize);
    $pdf->Cell($width, 3, 'Branch', 0, 1, 'L');

    // Location and caption
    $pdf->SetXY($x, 21);
    $pdf->SetFont('helvetica', 'B', $DatafontSize);
    $pdf->MultiCell(
        $width,
        3,
        $details['location'] ?? 'N/A',
        0,
        'L'
    );

    $pdf->SetXY($x, 25);
    $pdf->SetFont('helvetica', '', $LabelfontSize);
    $pdf->Cell($width, 3, 'Location', 0, 1, 'L');

    // Type - bottom left
    $pdf->SetXY($x, 28.7);
    $pdf->SetFont('helvetica', 'B', $DatafontSize);
    $pdf->Cell(10, 3, $details['type'] ?? 'N/A', 0, 0, 'L');

    $pdf->SetXY($x, 31);
    $pdf->SetFont('helvetica', '', $LabelfontSize);
    $pdf->Cell(10, 2, 'Type', 0, 0, 'L');

    // Class - bottom right
    $pdf->SetXY($x + 16, 28.7);
    $pdf->SetFont('helvetica', 'B', $DatafontSize);
    $pdf->Cell(12, 3, $details['class'] ?? 'N/A', 0, 0, 'L');

    $pdf->SetXY($x + 16, 31);
    $pdf->SetFont('helvetica', '', $LabelfontSize);
    $pdf->Cell(12, 2, 'Class', 0, 0, 'L');
}



$pdf->Output(
    'fire-extinguisher-qr.pdf',
    'I'
);
