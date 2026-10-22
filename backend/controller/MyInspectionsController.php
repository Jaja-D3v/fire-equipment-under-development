
<?php

require_once __DIR__ . '/../authentication/SessionChecker.php';
require_once __DIR__ . '/../model/MyInspectionsModel.php';

function getMyInspectionsPageData(): array
{
    // Use the logged-in inspector, not a user-supplied GET value.
    $inspectorName = trim((string) ($_SESSION['EmployeeName'] ?? ''));
    $search = trim((string) ($_GET['search'] ?? ''));
    $dateFilter = (string) ($_GET['date'] ?? 'all');
    $allowedDates = ['all', 'today', 'yesterday', '7', '30'];

    if (!in_array($dateFilter, $allowedDates, true)) {
        $dateFilter = 'all';
    }

    $statusFilter = (string) ($_GET['status'] ?? 'all');
    $allowedStatuses = ['all', 'Pending', 'Approved'];

    if (!in_array($statusFilter, $allowedStatuses, true)) {
        $statusFilter = 'all';
    }

    $perPage = 8;
    $requestedPage = filter_input(
        INPUT_GET,
        'page',
        FILTER_VALIDATE_INT
    );

    $currentPage = max(1, (int) ($requestedPage ?: 1));

    // Fail closed if the session has no inspector name.
    if ($inspectorName === '') {
        return [
            'myInspections' => [],
            'summary' => ['total' => 0, 'pending' => 0, 'approved' => 0],
            'totalInspections' => 0,
            'totalPending' => 0,
            'totalApproved' => 0,
            'perPage' => $perPage,
            'currentPage' => 1,
            'totalPages' => 1,
            'offset' => 0,
            'search' => $search,
            'dateFilter' => $dateFilter,
            'statusFilter' => $statusFilter,
            'inspectorMissing' => true,
        ];
    }

    $totalInspections = getMyInspectionsCountModel(
        $inspectorName,
        $search,
        $dateFilter,
        $statusFilter
    );

    $totalPages = max(1, (int) ceil($totalInspections / $perPage));

    // Clamp page if the requested page is beyond the last available page.
    $currentPage = min($currentPage, $totalPages);
    $offset = ($currentPage - 1) * $perPage;

    $myInspections = getMyInspectionsModel(
        $inspectorName,
        $search,
        $dateFilter,
        $statusFilter,
        $perPage,
        $offset
    );

    $summary = getMyInspectionsSummaryModel($inspectorName);

    return [
        'myInspections' => $myInspections,
        'summary' => $summary,
        'totalInspections' => $totalInspections,
        'totalPending' => $summary['pending'],
        'totalApproved' => $summary['approved'],
        'perPage' => $perPage,
        'currentPage' => $currentPage,
        'totalPages' => $totalPages,
        'offset' => $offset,
        'search' => $search,
        'dateFilter' => $dateFilter,
        'statusFilter' => $statusFilter,
        'inspectorMissing' => false,
    ];
}
