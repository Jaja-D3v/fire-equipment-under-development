<?php
require_once __DIR__ . '/../model/ActivityLogModel.php';

function createActivityLog($user_name, $action, $description)
{
    return addActivityLog(
        $user_name,
        $action,
        $description
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action === 'getAll') {
        header('Content-Type: application/json');
        $result = getActivityLogs();
        if (!$result) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to fetch activity logs.'
            ]);
            exit;
        }

        $logs = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $logs[] = $row;
        }
        echo json_encode([
            'success' => true,
            'data' => $logs
        ]);

        exit;
    }
}

function getActivityLogsPaginated(
    $limit = 10,
    $offset = 0,
    $month = '',
    $date = ''
) {
    return getActivityLogs(
        $limit,
        $offset,
        $month,
        $date
    );
}


function getActivityLogsTotal(
    $month = '',
    $date = ''
) {
    return getActivityLogsCount(
        $month,
        $date
    );
}