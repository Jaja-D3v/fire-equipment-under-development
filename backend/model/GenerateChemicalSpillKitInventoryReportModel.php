<?php
require_once __DIR__ . '/../config/db.php';

function getChemicalSpillKitInventoryReportRows($branch, $search = '')
{
    global $conn;

    $sql = "SELECT k.chemical_spill_kit_id, k.chemical_spill_kit_code,
                   k.location, k.branch, k.pads, k.boom_pillow, k.socks,
                   k.disposal_bag, k.goggles, k.face_mask,
                   k.rubber_gloves, k.nitrile_gloves,
                   i.inspection_date AS last_date_inspected,
                   i.inspected_by AS last_inspected_by
            FROM chemical_spill_kit_materials_inventory_tbl AS k
            LEFT JOIN chemical_spill_kit_inspection_tbl AS i
                ON i.inspection_id = (
                    SELECT latest.inspection_id
                    FROM chemical_spill_kit_inspection_tbl AS latest
                    WHERE latest.chemical_spill_kit_id = k.chemical_spill_kit_id
                      AND latest.branch = k.branch
                    ORDER BY latest.inspection_date DESC, latest.inspection_id DESC
                    LIMIT 1
                )
            WHERE k.archived = 0
              AND (? = 'all' OR k.branch = ?)
              AND (? = '' OR k.chemical_spill_kit_code LIKE ?
                          OR k.location LIKE ? OR k.branch LIKE ?)
            ORDER BY k.branch ASC, k.chemical_spill_kit_code ASC,
                     k.chemical_spill_kit_id ASC";

    $stmt = null;
    try {
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new RuntimeException('Could not prepare Chemical Spill Kit report query.');
        }
        $searchLike = '%' . $search . '%';
        mysqli_stmt_bind_param(
            $stmt,
            'ssssss',
            $branch,
            $branch,
            $search,
            $searchLike,
            $searchLike,
            $searchLike
        );
        if (!mysqli_stmt_execute($stmt)) {
            throw new RuntimeException('Could not retrieve Chemical Spill Kit inventory.');
        }
        $result = mysqli_stmt_get_result($stmt);
        if (!$result) {
            throw new RuntimeException('Could not read Chemical Spill Kit report results.');
        }
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_free_result($result);
        return $rows;
    } catch (Throwable $error) {
        error_log('Chemical Spill Kit inventory report: ' . $error->getMessage());
        return false;
    } finally {
        if ($stmt) {
            mysqli_stmt_close($stmt);
        }
    }
}
