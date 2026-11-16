<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/ChemicalSpillKitMaterialModel.php';
require_once __DIR__ . '/DropdownModel.php';

// Attach current companion quantities and newly configured materials to the kit metadata.
function attachChemicalSpillKitMaterials(array $kit)
{
    $existing = getChemicalSpillKitCurrentMaterials((int) $kit['chemical_spill_kit_id']);
    $configured = getChemicalSpillKitConfiguredMaterials($kit['branch']);
    $kit['current_materials'] = mergeChemicalSpillKitMaterials($configured === false ? [] : $configured, $existing);
    return $kit;
}

function getAllChemicalSpillKit($branch, $limit = 10, $offset = 0, $search = '')
{
    global $conn;
    $sql = "SELECT c.chemical_spill_kit_id, c.chemical_spill_kit_code, c.location,
                   c.branch, c.archived,
                   EXISTS (SELECT 1 FROM chemical_spill_kit_inspection_tbl i
                           WHERE i.chemical_spill_kit_id = c.chemical_spill_kit_id
                             AND i.inspection_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                             AND i.inspection_date < DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH)) AS inspected_this_month
            FROM chemical_spill_kit_materials_inventory_tbl c
            WHERE (? = 'all' OR c.branch = ?) AND c.archived = 0
              AND (? = '' OR c.chemical_spill_kit_code LIKE ? OR c.location LIKE ? OR c.branch LIKE ?)
            ORDER BY c.chemical_spill_kit_code ASC LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($conn, $sql);
    try {
        $like = '%' . $search . '%';
        mysqli_stmt_bind_param($stmt, 'ssssssii', $branch, $branch, $search, $like, $like, $like, $limit, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $kits = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $kits[] = attachChemicalSpillKitMaterials($row);
        }
        mysqli_free_result($result);
        return $kits;
    } finally {
        mysqli_stmt_close($stmt);
    }
}

// For inventory count
function getChemicalSpillKitInventoryCount($branch, $search = '')
{
    global $conn;

    $sql = "SELECT COUNT(*) AS total
            FROM chemical_spill_kit_materials_inventory_tbl
            WHERE (? = 'all' OR branch = ?)
              AND archived = 0
              AND (
                  ? = ''
                  OR chemical_spill_kit_code LIKE ?
                  OR location LIKE ?
                  OR branch LIKE ?
              )";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log("CSK inventory count prepare error: " . mysqli_error($conn));
        return 0;
    }

    $searchLike = '%' . $search . '%';

    mysqli_stmt_bind_param(
        $stmt,
        "ssssss",
        $branch,
        $branch,
        $search,
        $searchLike,
        $searchLike,
        $searchLike
    );

    if (!mysqli_stmt_execute($stmt)) {
        error_log("CSK inventory count execute error: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return 0;
    }

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    mysqli_free_result($result);
    mysqli_stmt_close($stmt);

    return (int)($row['total'] ?? 0);
}


// CSK codes are branch-scoped, matching the FE code workflow.
function chemicalSpillKitCodeExists($code, $branch, $lockForInsert = false)
{
    global $conn;

    $sql = "SELECT chemical_spill_kit_id
            FROM chemical_spill_kit_materials_inventory_tbl
            WHERE chemical_spill_kit_code = ?
              AND branch = ?
            LIMIT 1";
    if ($lockForInsert) {
        $sql .= " FOR UPDATE";
    }

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("CSK code check prepare error: " . mysqli_error($conn));
        return null;
    }

    mysqli_stmt_bind_param($stmt, "ss", $code, $branch);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("CSK code check execute error: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return null;
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        mysqli_stmt_close($stmt);
        return null;
    }

    $exists = mysqli_num_rows($result) > 0;
    mysqli_free_result($result);
    mysqli_stmt_close($stmt);
    return $exists;
}

function getNextChemicalSpillKitCode($branch)
{
    global $conn;

    $branch = trim((string)$branch);
    if ($branch === '') {
        return null;
    }

    $sql = "SELECT chemical_spill_kit_code
            FROM chemical_spill_kit_materials_inventory_tbl
            WHERE chemical_spill_kit_code LIKE 'CSK-%'
              AND LOWER(TRIM(branch)) = LOWER(TRIM(?))
            ORDER BY CAST(SUBSTRING(chemical_spill_kit_code, 5) AS UNSIGNED) DESC
            LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("CSK next-code prepare error: " . mysqli_error($conn));
        return null;
    }

    mysqli_stmt_bind_param($stmt, "s", $branch);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("CSK next-code execute error: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return null;
    }

    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    if ($result) {
        mysqli_free_result($result);
    }
    mysqli_stmt_close($stmt);

    $lastNumber = $row ? (int)substr((string)$row['chemical_spill_kit_code'], 4) : 0;
    return 'CSK-' . str_pad((string)($lastNumber + 1), 3, '0', STR_PAD_LEFT);
}

// Used by QR lookup and inventory edit; quantity columns are deliberately not selected.
function getChemicalSpillKitByCode($code, $branch)
{
    global $conn;
    // Only Super Admin may resolve a QR code across branches; writes still pass a concrete branch.
    $unscopedRead = strtolower(trim($_SESSION['Role'] ?? '')) === 'super admin'
        && ($branch === '' || strcasecmp($branch, 'all') === 0);
    $sql = 'SELECT chemical_spill_kit_id, chemical_spill_kit_code, location, branch, archived
            FROM chemical_spill_kit_materials_inventory_tbl
            WHERE chemical_spill_kit_code = ? AND archived = 0';
    $sql .= $unscopedRead ? ' LIMIT 2' : ' AND branch = ? LIMIT 1';
    $stmt = mysqli_prepare($conn, $sql);
    try {
        if ($unscopedRead) {
            mysqli_stmt_bind_param($stmt, 's', $code);
        } else {
            mysqli_stmt_bind_param($stmt, 'ss', $code, $branch);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $kit = mysqli_fetch_assoc($result);
        $ambiguous = $unscopedRead && is_array(mysqli_fetch_assoc($result));
        mysqli_free_result($result);
        if ($ambiguous) return ['ambiguous' => true];
        return $kit ? attachChemicalSpillKitMaterials($kit) : null;
    } finally {
        mysqli_stmt_close($stmt);
    }
}

function archiveChemicalSpillKit($id, $branch)
{
    global $conn;

    $sql = "UPDATE chemical_spill_kit_materials_inventory_tbl
            SET archived = 1
            WHERE chemical_spill_kit_id = ?
              AND archived = 0
              AND (? = 'all' OR branch = ?)";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("CSK archive prepare error: " . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, "iss", $id, $branch, $branch);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("CSK archive execute error: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    $archived = mysqli_stmt_affected_rows($stmt) === 1;
    mysqli_stmt_close($stmt);
    return $archived;
}

// THIS IS FOR UPDATING INVENTORY MATERIALS 
// Only active configured branches can create inventory records.
function chemicalSpillKitBranchIsActive($branch)
{
    foreach (getAllBranchDropdown() as $option) {
        if (strcasecmp(trim($option['value']), trim($branch)) === 0) return true;
    }
    return false;
}

function updateSpillKitMaterials($id, $location, $submitted, $branch = '')
{
    global $conn;
    $stmt = null;
    try {
        mysqli_begin_transaction($conn);
        // Lock by both kit and branch before reading/updating its companion records.
        $stmt = mysqli_prepare($conn,
            'SELECT chemical_spill_kit_id FROM chemical_spill_kit_materials_inventory_tbl
             WHERE chemical_spill_kit_id = ? AND branch = ? AND archived = 0 FOR UPDATE');
        mysqli_stmt_bind_param($stmt, 'is', $id, $branch);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $kit = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);
        $stmt = null;
        if (!$kit || trim($location) === '') throw new RuntimeException('Kit or location is unavailable.');
        $configured = getChemicalSpillKitConfiguredMaterials($branch);
        if ($configured === false) throw new RuntimeException('Required stock configuration is invalid.');
        $existing = getChemicalSpillKitCurrentMaterials($id, true);
        $materials = validateChemicalSpillKitMaterials($submitted, $configured, $existing);
        if ($materials === false) throw new RuntimeException('Material names or quantities are invalid.');
        $stmt = mysqli_prepare($conn,
            'UPDATE chemical_spill_kit_materials_inventory_tbl SET location = ?
             WHERE chemical_spill_kit_id = ? AND branch = ?');
        mysqli_stmt_bind_param($stmt, 'sis', $location, $id, $branch);
        mysqli_stmt_execute($stmt);
        saveChemicalSpillKitCurrentMaterials($id, $materials, $existing);
        mysqli_commit($conn);
        return true;
    } catch (Throwable $error) {
        mysqli_rollback($conn);
        error_log('CSK inventory edit failed: ' . $error->getMessage());
        return false;
    } finally {
        if ($stmt) mysqli_stmt_close($stmt);
    }
}

function addNewChemicalSpillKit($code, $branch, $location, $submitted)
{
    global $conn;
    $stmt = null;
    try {
        mysqli_begin_transaction($conn);
        if (!preg_match('/^CSK-\d+$/D', $code) || trim($location) === ''
            || !chemicalSpillKitBranchIsActive($branch)
            || chemicalSpillKitCodeExists($code, $branch, true) !== false) {
            throw new RuntimeException('Invalid or duplicate kit identity.');
        }
        $configured = getChemicalSpillKitConfiguredMaterials($branch);
        if (!$configured) throw new RuntimeException('Configure Required Stock for this branch first.');
        $materials = validateChemicalSpillKitMaterials($submitted, $configured);
        if ($materials === false) throw new RuntimeException('Material names or quantities are invalid.');
        // Keep legacy quantity columns intact; no quantities are written to the parent.
        $stmt = mysqli_prepare($conn,
            'INSERT INTO chemical_spill_kit_materials_inventory_tbl
             (chemical_spill_kit_code, branch, location, archived, is_inspected_this_month)
             VALUES (?, ?, ?, 0, 0)');
        mysqli_stmt_bind_param($stmt, 'sss', $code, $branch, $location);
        mysqli_stmt_execute($stmt);
        $kitId = mysqli_insert_id($conn);
        saveChemicalSpillKitCurrentMaterials($kitId, $materials, []);
        mysqli_commit($conn);
        return true;
    } catch (Throwable $error) {
        mysqli_rollback($conn);
        error_log('CSK inventory add failed: ' . $error->getMessage());
        return false;
    } finally {
        if ($stmt) mysqli_stmt_close($stmt);
    }
}
