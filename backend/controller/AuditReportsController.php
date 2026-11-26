<?php
require_once __DIR__ . '/../authentication/only-admin.php';
require_once __DIR__ . '/../model/AuditReportsModel.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header('Allow: GET');
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Only GET requests are supported.']);
        exit;
    }
    $action = $_GET['action'] ?? 'options';
    if (!is_string($action) || !in_array($action, ['options', 'generate'], true)) {
        throw new InvalidArgumentException('Invalid report action.');
    }
    $options = auditReportOptions();
    if ($action === 'options') {
        echo json_encode(['success' => true, 'options' => $options], JSON_THROW_ON_ERROR);
        exit;
    }
    $equipment = $_GET['equipment'] ?? '';
    $year = $_GET['year'] ?? '';
    $branch = $_GET['branch'] ?? '';
    if (!is_string($equipment) || !in_array($equipment, ['fire-extinguisher', 'chemical-spill-kit'], true)) {
        throw new InvalidArgumentException('This equipment type is not available for approved inspection reporting.');
    }
    if (!is_string($year) || !preg_match('/^\\d{4}$/D', $year)
        || !in_array((int) $year, $options['years'], true)) {
        throw new InvalidArgumentException('Choose an available reporting year.');
    }
    if (!is_string($branch) || ($branch !== '__all__' && !in_array($branch, $options['branches'], true))) {
        throw new InvalidArgumentException('Choose an authorized branch.');
    }
    // The existing admin role has all-branch access; other roles are denied by only-admin.php.
    if ($equipment === 'fire-extinguisher') {
        $report = auditGetAnnualReport((int) $year, $branch);
        $equipmentLabel = 'Fire Extinguisher';
        $filenameEquipment = 'Fire_Extinguisher';
    } else {
        $report = auditGetChemicalSpillKitAnnualReport((int) $year, $branch);
        $equipmentLabel = 'Chemical Spill Kit';
        $filenameEquipment = 'Chemical_Spill_Kit';
    }
    $report['equipment'] = $equipmentLabel;
    $report['year'] = (int) $year;
    $report['branch'] = $branch === '__all__' ? 'All Branches' : $branch;
    $report['generated_at'] = date(DATE_ATOM);
    require_once __DIR__ . '/../../helpers/generate-audit-report.php';
    $pdf = generateAuditReportPdf($report);
    // One response is an immutable report snapshot: preview and download use these same bytes.
    echo json_encode(['success' => true, 'report' => $report,
        'filename' => 'Annual_Safety_Equipment_Inspection_' . $filenameEquipment . '_' . $year . '.pdf',
        'pdf' => base64_encode($pdf)], JSON_THROW_ON_ERROR);
} catch (InvalidArgumentException $error) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $error->getMessage()]);
} catch (Throwable $error) {
    error_log('Audit report: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to generate the report. Please contact your administrator to check the report data.']);
}
