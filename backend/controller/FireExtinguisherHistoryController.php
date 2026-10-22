
<?php

require_once __DIR__ . '/../model/FireExtinguisherHistoryModel.php';

function getFireExtinguisherHistoryController()
{
    $role = strtolower(trim($_SESSION['Role'] ?? ''));
    $code = isset($_GET['extinguisher_code']) && is_string($_GET['extinguisher_code'])
        ? trim($_GET['extinguisher_code'])
        : '';
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');

    $branches = getAllDropdownBranches();
    $branchValues = [];
    foreach ($branches as $branchOption) {
        if (isset($branchOption['value']) && is_string($branchOption['value'])) {
            $branchValues[] = trim($branchOption['value']);
        }
    }
    $branchValues = array_values(array_unique(array_filter($branchValues, 'strlen')));

    $sessionBranch = isset($_SESSION['Branch']) && is_string($_SESSION['Branch'])
        ? trim($_SESSION['Branch'])
        : '';
    $selectedBranch = '';

    if ($role === 'admin') {
        $requestedBranch = isset($_GET['branch']) && is_string($_GET['branch'])
            ? trim($_GET['branch'])
            : null;

        if ($requestedBranch === 'all') {
            $selectedBranch = 'all';
        } elseif ($requestedBranch !== null && in_array($requestedBranch, $branchValues, true)) {
            $selectedBranch = $requestedBranch;
        } elseif ($sessionBranch !== '' && in_array($sessionBranch, $branchValues, true)) {
            $selectedBranch = $sessionBranch;
        } else {
            $selectedBranch = 'all';
        }

        $effectiveBranch = $selectedBranch;
    } elseif ($role === 'inspector') {
        $effectiveBranch = ($sessionBranch !== '' && strtolower($sessionBranch) !== 'all')
            ? $sessionBranch
            : '';
    } else {
        $effectiveBranch = '';
    }

    $branchUnavailable = ($role === 'inspector' && $effectiveBranch === '') ||
        !in_array($role, ['admin', 'inspector'], true);
    $extinguisherCodes = $branchUnavailable
        ? []
        : getAllFireExtinguisherCodes($effectiveBranch);

    $details = null;
    $records = [];
    $timeline = [];
    $errors = [];

    $isValidDate = function ($date) {
        if (!is_string($date)) {
            return false;
        }

        $parsed = DateTime::createFromFormat('!Y-m-d', $date);

        return $parsed && $parsed->format('Y-m-d') === $date;
    };

    if ($code === '') {
        $errors[] = 'Please select a fire extinguisher.';
    }

    if ($branchUnavailable) {
        $errors[] = 'Your account does not have an available branch for history access.';
    }

    if (
        !$isValidDate($dateFrom) ||
        !$isValidDate($dateTo)
    ) {
        $errors[] = 'Please enter valid dates.';
    } elseif ($dateFrom > $dateTo) {
        $errors[] = 'Date From must not be later than Date To.';
    }


    if (empty($errors)) {
        try {
            // Retrieve details only if a code was selected.
            if ($code !== '') {
                $details = getFireExtinguisherHistoryDetails($code, $effectiveBranch);

                if (!$details) {
                    $errors[] = 'Fire extinguisher not found.';
                }
            }

            // Retrieve history whether a code is selected or not.
            if (empty($errors)) {
                $records = getFireExtinguisherInspectionHistory(
                    $code,
                    $dateFrom,
                    $dateTo,
                    $effectiveBranch
                );

                $timeline = getFireExtinguisherHistoryTimeline($records);
            }
        } catch (Throwable $e) {
            error_log(
                'Fire Extinguisher History: ' . $e->getMessage()
            );

            $errors[] = 'Unable to load fire extinguisher history.';
        }
    }


    return [
        'code' => $code,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'branch' => $selectedBranch,
        'branches' => $branchValues,
        'branchUnavailable' => $branchUnavailable,
        'extinguisherCodes' => $extinguisherCodes,
        'details' => $details,
        'records' => $records,
        'timeline' => $timeline,
        'errors' => $errors
    ];
}
