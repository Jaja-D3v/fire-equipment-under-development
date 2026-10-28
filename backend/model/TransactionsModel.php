<?php
require_once __DIR__ . '/../config/db.php';

// Return only the newest inspection for each equipment code and branch.
function getInspectionTransactionsSql()
{
    $allInspections = "(
        SELECT t.transaction_id, t.category AS equipment, t.branch, t.status,
               i.extinguisher_code AS code, i.location,
               i.date_inspected AS inspection_date, i.inspect_id AS inspection_id, t.submitted_at
        FROM transactions_tbl t
        INNER JOIN inspection_checklist_tbl i
            ON i.inspect_id = t.inspection_id
        WHERE t.category = 'Fire Extinguisher'
          AND t.transaction_type = 'inspection'

        UNION ALL

        SELECT t.transaction_id, t.category AS equipment, t.branch, t.status,
               i.chemical_spill_kit_code AS code, i.location,
               i.inspection_date AS inspection_date, i.inspection_id AS inspection_id, t.submitted_at
        FROM transactions_tbl t
        INNER JOIN chemical_spill_kit_inspection_tbl i
            ON i.inspection_id = t.inspection_id
        WHERE t.category = 'Chemical Spill Kit'
          AND t.transaction_type = 'inspection'
    )";

    // Match newer rows by code and branch; IDs break ties and keep the latest transaction status.
    return "(
        SELECT candidate_inspection.*
        FROM $allInspections candidate_inspection
        WHERE NOT EXISTS (
            SELECT 1
            FROM $allInspections newer_inspection
            WHERE newer_inspection.code = candidate_inspection.code
              AND newer_inspection.branch = candidate_inspection.branch
              AND (
                    newer_inspection.inspection_date > candidate_inspection.inspection_date
                    OR (
                        newer_inspection.inspection_date = candidate_inspection.inspection_date
                        AND newer_inspection.inspection_id > candidate_inspection.inspection_id
                    )
                    OR (
                        newer_inspection.inspection_date = candidate_inspection.inspection_date
                        AND newer_inspection.inspection_id = candidate_inspection.inspection_id
                        AND newer_inspection.transaction_id > candidate_inspection.transaction_id
                    )
              )
        )
    ) inspections";
}
// Count every record that matches the selected filters, before pagination is applied.
function getTransactionsInspectionTotal($branch, $allBranches, $search, $equipment, $dateFrom, $dateTo, $status)
{
    global $conn;

    $sql = "SELECT COUNT(*) AS total
            FROM " . getInspectionTransactionsSql() . "
            WHERE (? = 1 OR (? <> '' AND branch = ?))
              AND (? = '' OR equipment = ?)
              AND (? = '' OR LOWER(status) = LOWER(?))
              AND (? = '' OR DATE(inspection_date) >= ?)
              AND (? = '' OR DATE(inspection_date) <= ?)
              AND (? = '' OR code LIKE CONCAT('%', ?, '%')
                           OR location LIKE CONCAT('%', ?, '%'))";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Pending inspections count prepare failed: ' . $conn->error);
        return 0;
    }

    $stmt->bind_param(
        'isssssssssssss',
        $allBranches,
        $branch,
        $branch,
        $equipment,
        $equipment,
        $status,
        $status,
        $dateFrom,
        $dateFrom,
        $dateTo,
        $dateTo,
        $search,
        $search,
        $search
    );

    if (!$stmt->execute()) {
        error_log('Pending inspections count failed: ' . $stmt->error);
        $stmt->close();
        return 0;
    }

    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();
    return (int) $total;
}

// Fetch one page of matching records, sorted by the inspection date.
function getTransactionsInspectionPage($branch, $allBranches, $search, $equipment, $dateFrom, $dateTo, $status, $limit, $offset)
{
    global $conn;

    $sql = "SELECT transaction_id, equipment, branch, status, code, location, inspection_date, submitted_at
            FROM " . getInspectionTransactionsSql() . "
            WHERE (? = 1 OR (? <> '' AND branch = ?))
              AND (? = '' OR equipment = ?)
              AND (? = '' OR LOWER(status) = LOWER(?))
              AND (? = '' OR DATE(inspection_date) >= ?)
              AND (? = '' OR DATE(inspection_date) <= ?)
              AND (? = '' OR code LIKE CONCAT('%', ?, '%')
                           OR location LIKE CONCAT('%', ?, '%'))
            ORDER BY inspection_date DESC, transaction_id DESC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Pending inspections page prepare failed: ' . $conn->error);
        return [];
    }

    $stmt->bind_param(
        'isssssssssssssii',
        $allBranches,
        $branch,
        $branch,
        $equipment,
        $equipment,
        $status,
        $status,
        $dateFrom,
        $dateFrom,
        $dateTo,
        $dateTo,
        $search,
        $search,
        $search,
        $limit,
        $offset
    );

    if (!$stmt->execute()) {
        error_log('Pending inspections page query failed: ' . $stmt->error);
        $stmt->close();
        return [];
    }

    $result = $stmt->get_result();
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    $stmt->close();
    return $records;
}

// Count statuses for the selected branch, without limiting the count to the current page.
function getTransactionsInspectionStatusCounts($branch, $allBranches)
{
    global $conn;

    $sql = "SELECT LOWER(status) AS status_key, COUNT(*) AS total
            FROM " . getInspectionTransactionsSql() . "
            WHERE (? = 1 OR (? <> '' AND branch = ?))
            GROUP BY LOWER(status)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Pending inspection status count prepare failed: ' . $conn->error);
        return ['pending' => 0, 'approved' => 0, 'rejected' => 0];
    }

    $stmt->bind_param('iss', $allBranches, $branch, $branch);
    if (!$stmt->execute()) {
        error_log('Pending inspection status count failed: ' . $stmt->error);
        $stmt->close();
        return ['pending' => 0, 'approved' => 0, 'rejected' => 0];
    }

    $counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
    $stmt->bind_result($statusKey, $total);
    while ($stmt->fetch()) {
        if (isset($counts[$statusKey])) {
            $counts[$statusKey] = (int) $total;
        }
    }

    $stmt->close();
    return $counts;
}