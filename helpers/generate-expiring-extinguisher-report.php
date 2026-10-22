
<?php

require_once __DIR__ . '/../backend/authentication/SessionChecker.php';
require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

if (!class_exists('TCPDF')) {
    http_response_code(500);
    exit('TCPDF is not installed or is not available through Composer autoload.');
}

$role = strtolower(trim(isset($_SESSION['Role']) ? $_SESSION['Role'] : ''));
$sessionBranch = trim(isset($_SESSION['Branch']) ? $_SESSION['Branch'] : '');

$search = trim(isset($_GET['search']) ? $_GET['search'] : '');
$condition = strtolower(trim(isset($_GET['condition']) ? $_GET['condition'] : 'all'));

if ($role === 'admin') {
    if (isset($_GET['branch'])) {
        // Use the URL branch, including "all".
        $branch = trim($_GET['branch']);
    } else {
        // No branch parameter: use the session branch.
        $branch = $sessionBranch;
    }
} else {
    // Non-admin: session branch only.
    $branch = $sessionBranch;
}

if ($branch === '') {
    $branch = 'all';
}

/*
 * Retrieve every active extinguisher expiring within two months.
 * Do not apply the screen pagination limit.
 */
$sql = "
    SELECT
        extinguisher_code,
        type,
        capacity,
        `class`,
        location,
        branch,
        condition_status,
        manufactured_date,
        expiration_date,
        refilled_date,
        placement
    FROM fire_extinguishers_tbl
    WHERE archived = 0
      AND expiration_date >= CURDATE()
      AND expiration_date <= DATE_ADD(CURDATE(), INTERVAL 2 MONTH)
";

$params = array();
$types = '';

if ($branch !== 'all') {
    $sql .= " AND LOWER(TRIM(branch)) = LOWER(TRIM(?))";
    $types .= 's';
    $params[] = $branch;
}

if ($condition === 'good') {
    $sql .= " AND LOWER(TRIM(condition_status)) = 'good'";
} elseif ($condition === 'not-good') {
    $sql .= " AND LOWER(TRIM(condition_status)) <> 'good'";
}

if ($search !== '') {
    $sql .= "
        AND (
            extinguisher_code LIKE ?
            OR location LIKE ?
            OR type LIKE ?
            OR branch LIKE ?
        )
    ";

    $keyword = '%' . $search . '%';
    $types .= 'ssss';

    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
}

$sql .= " ORDER BY expiration_date ASC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    error_log('Expiring report query preparation failed: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Unable to prepare the expiring report.');
}

/* Bind parameters only when the query contains parameters. */
if (!empty($params)) {
    $bindParams = array($stmt, $types);

    foreach ($params as $key => $value) {
        $bindParams[] = &$params[$key];
    }

    if (!call_user_func_array('mysqli_stmt_bind_param', $bindParams)) {
        mysqli_stmt_close($stmt);
        http_response_code(500);
        exit('Unable to bind report filters.');
    }
}

if (!mysqli_stmt_execute($stmt)) {
    error_log('Expiring report query execution failed: ' . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    http_response_code(500);
    exit('Unable to retrieve expiring fire extinguishers.');
}

$result = mysqli_stmt_get_result($stmt);

if ($result === false) {
    mysqli_stmt_close($stmt);
    http_response_code(500);
    exit('Unable to read report results. Check that the PHP MySQL native driver is enabled.');
}

$rows = array();

while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

mysqli_free_result($result);
mysqli_stmt_close($stmt);

if (empty($rows)) {
    http_response_code(404);
    exit('No active fire extinguishers are expiring within the next two months for the selected filters.');
}

/* Create PDF using TCPDF. */
$pdf = new TCPDF('L', 'mm', 'A3', true, 'UTF-8', false);

$pdf->SetMargins(8, 8, 8);
$pdf->SetAutoPageBreak(true, 10);

$pdf->SetCreator('IMS Safety Management System');
$pdf->SetAuthor('KanePackage Philippine Inc.');
$pdf->SetTitle('Fire Extinguishers Expiring Soon Report');
$pdf->SetSubject('Fire extinguisher expiration monitoring report');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(8, 8, 8);
$pdf->SetAutoPageBreak(true, 10);

/* Existing table headers and widths. */
$headers = array(
    'FE Code',
    'Type',
    'Capacity',
    'Class',
    'Placement',
    'Location',
    'Branch',
    'Condition',
    'Manufactured Date',
    'Refilled Date',
    'Expiration Date',
    'Days Remaining'
);

$widths = array(
    33.5, // FE Code
    33.5, // Type
    33.5, // Capacity
    33.5, // Class
    33.5, // Placement
    33.5, // Location
    33.5, // Branch
    33.5, // Condition
    33.5, // Manufactured Date
    33.5, // Refilled Date
    33.5, // Expiration Date
    33.5  // Days Remaining
);

$branchLabel = ($branch === 'all') ? 'All Branches' : $branch;

/*
 * Draw the existing report title, subtitle, and table header
 * whenever a new page is started.
 */
$drawPageHeader = function () use ($pdf, $branchLabel, $headers, $widths) {
    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor(32, 61, 112);
    $pdf->Cell(0, 10, 'FIRE EXTINGUISHERS EXPIRING SOON', 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->Cell(
        0,
        7,
        'Branch: ' . $branchLabel . ' | Generated: ' . date('M j, Y'),
        0,
        1,
        'C'
    );

    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetDrawColor(210, 220, 232);

    foreach ($headers as $index => $header) {
        $pdf->Cell($widths[$index], 9, $header, 1, 0, 'C', true);
    }

    $pdf->Ln();
};

/* Start the first page. */
$drawPageHeader();

$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 0, 0);

$today = new DateTime('today');
$recordsPerPage = 20;
$recordCount = 0;

foreach ($rows as $index => $row) {

    /*
     * Start a new page before record 21, 41, 61, etc.
     */
    if ($recordCount > 0 && $recordCount % $recordsPerPage === 0) {
        $drawPageHeader();

        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
    }

    $expirationDate = new DateTime($row['expiration_date']);
    $daysRemaining = (int) $today->diff($expirationDate)->format('%a');

    $values = array(
        $row['extinguisher_code'],
        $row['type'],
        $row['capacity'],
        $row['class'],
        $row['placement'],
        $row['location'],
        $row['branch'],
        $row['condition_status'],

        !empty($row['manufactured_date'])
            ? date('M j, Y', strtotime($row['manufactured_date']))
            : 'N/A',

        !empty($row['refilled_date'])
            ? date('M j, Y', strtotime($row['refilled_date']))
            : 'N/A',

        !empty($row['expiration_date'])
            ? date('M j, Y', strtotime($row['expiration_date']))
            : 'N/A',

        (string) $daysRemaining
    );

    if ($index % 2 === 0) {
        $pdf->SetFillColor(242, 246, 250);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }

    foreach ($values as $columnIndex => $value) {
        $pdf->MultiCell(
            $widths[$columnIndex],
            10,
            (string) $value,
            1,
            'C',
            true,
            0,
            '',
            '',
            true,
            0,
            false,
            true,
            10,
            'M'
        );
    }

    $pdf->Ln();
    $recordCount++;
}

/* Summary footer. */
$pdf->Ln(3);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 6, 'Total Records: ' . count($rows), 0, 1, 'R');

$filename = 'Expiring_Fire_Extinguishers_' . date('Y-m-d') . '.pdf';

/* Clear buffered output before sending PDF headers and content. */
while (ob_get_level() > 0) {
    ob_end_clean();
}

$pdf->Output($filename, 'I');
exit;
