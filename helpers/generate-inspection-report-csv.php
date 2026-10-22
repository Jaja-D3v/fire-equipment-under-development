
<?php

$ids = isset($_GET['ids']) ? $_GET['ids'] : '';

header(
    'Location: ../backend/controller/GenerateInspectionReportCsvController.php?' .
    http_build_query(array('ids' => $ids))
);

exit;
