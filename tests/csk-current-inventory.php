<?php
// Run with: php -n tests/csk-current-inventory.php
// -n disables MySQLi. All SQL calls below operate on arrays; no connection is opened.
if (extension_loaded('mysqli')) exit("Run this isolated test with php -n.\n");
define('MYSQLI_ASSOC', 1);
$conn = null;
$fixture = ['parents' => [], 'materials' => [1 => [
    'current_material_id' => 1,
    'chemical_spill_kit_id' => 999,
    'material_name' => 'Orphan material',
    'current_stock' => null,
    'status' => null,
    'remarks' => 'Preserve me',
    'updated_at' => null
]], 'checklists' => [], 'transactions' => []];
$required = [
    'Pads' => 50,
    'Boom/Pillow' => 6,
    'Socks' => 2,
    'Disposal Bag' => 3,
    'Goggles' => 3,
    'Face Mask' => 10,
    'Rubber Gloves' => 3,
    'Nitrile Gloves' => 6
];
$statements = [];
$results = [];
$nextHandle = 1;
$lastId = 0;
$affected = 0;
$snapshot = null;
$failMaterialWrite = false;
$checks = 0;
function check($condition, $message)
{
    global $checks;
    if (!$condition) throw new RuntimeException($message);
    $checks++;
}
function loadModelFunctions($file)
{
    $code = file_get_contents(__DIR__ . '/../backend/model/' . $file);
    $code = preg_replace('/^require_once[^\r\n]*[\r\n]*/m', '', $code);
    eval(substr($code, strpos($code, '<?php') + 5));
}
function getRequiredStockModel($branch)
{
    global $required;
    return $branch === 'Laguna' ? $required : [];
}
function getAllBranchDropdown()
{
    return [['value' => 'Laguna'], ['value' => 'Cebu']];
}
if (!function_exists('mb_strtolower')) {
    function mb_strtolower($value, $encoding)
    {
        return strtolower($value);
    }
}
// Conditional declarations also allow normal PHP syntax checks with MySQLi enabled.
if (!extension_loaded('mysqli')) {
    function mysqli_prepare($connection, $sql)
    {
        global $nextHandle, $statements;
        // Any dependency on a legacy quantity column fails the test immediately.
        check(!preg_match('/\b(pads|boom_pillow|socks|disposal_bag|goggles|face_mask|rubber_gloves|nitrile_gloves)\b/i', $sql), 'Legacy quantity SQL');
        $id = $nextHandle++;
        $statements[$id] = ['sql' => preg_replace('/\s+/', ' ', trim($sql)), 'params' => [], 'rows' => []];
        return $id;
    }
    function mysqli_stmt_bind_param($stmt, $types, &...$params)
    {
        global $statements;
        $statements[$stmt]['params'] = &$params;
        return true;
    }
    function mysqli_stmt_execute($stmt)
    {
        global $statements, $fixture, $lastId, $affected, $failMaterialWrite;
        $sql = $statements[$stmt]['sql'];
        $p = $statements[$stmt]['params'];
        $rows = [];
        $affected = 0;
        if (str_starts_with($sql, 'SELECT') && str_contains($sql, 'FROM chemical_spill_kit_current_inventory_materials_tbl')) {
            $rows = array_values(array_filter($fixture['materials'], fn($m) => $m['chemical_spill_kit_id'] == $p[0]));
        } elseif (str_starts_with($sql, 'SELECT material_name, current_stocks')) {
            $rows = $fixture['checklists'][$p[0]] ?? [];
        } elseif (str_starts_with($sql, 'SELECT t.transaction_id')) {
            foreach ($fixture['transactions'] as $transaction) {
                if ($transaction['inspection_id'] == $p[0] && ($p[1] === 'admin' || strcasecmp($transaction['branch'], $p[2]) === 0)) $rows[] = $transaction;
            }
        } elseif (str_starts_with($sql, 'SELECT chemical_spill_kit_id FROM chemical_spill_kit_materials_inventory_tbl')) {
            foreach ($fixture['parents'] as $kit) {
                $match = str_contains($sql, 'WHERE chemical_spill_kit_code')
                    ? $kit['chemical_spill_kit_code'] === $p[0] && $kit['branch'] === $p[1]
                    : $kit['chemical_spill_kit_id'] == $p[0] && $kit['branch'] === $p[1] && !$kit['archived'];
                if ($match && (count($p) < 3 || $kit['chemical_spill_kit_code'] === $p[2])) $rows[] = $kit;
            }
        } elseif (str_starts_with($sql, 'INSERT INTO chemical_spill_kit_materials_inventory_tbl')) {
            $lastId = count($fixture['parents']) + 100;
            $fixture['parents'][$lastId] = ['chemical_spill_kit_id' => $lastId, 'chemical_spill_kit_code' => $p[0], 'branch' => $p[1], 'location' => $p[2], 'archived' => 0];
            $affected = 1;
        } elseif (str_starts_with($sql, 'UPDATE chemical_spill_kit_materials_inventory_tbl SET location')) {
            if (isset($fixture['parents'][$p[1]]) && $fixture['parents'][$p[1]]['branch'] === $p[2]) {
                $fixture['parents'][$p[1]]['location'] = $p[0];
                $affected = 1;
            }
        } elseif (str_starts_with($sql, 'INSERT INTO chemical_spill_kit_current_inventory_materials_tbl')) {
            if ($failMaterialWrite) throw new RuntimeException('Simulated material write failure');
            $id = max(array_keys($fixture['materials'])) + 1;
            $fixture['materials'][$id] = ['current_material_id' => $id, 'chemical_spill_kit_id' => $p[0], 'material_name' => $p[1], 'current_stock' => $p[2], 'status' => $p[3], 'remarks' => $p[4], 'updated_at' => null];
            $affected = 1;
        } elseif (str_starts_with($sql, 'UPDATE chemical_spill_kit_current_inventory_materials_tbl')) {
            if ($failMaterialWrite) throw new RuntimeException('Simulated material write failure');
            foreach ($fixture['materials'] as &$m) {
                if ($m['chemical_spill_kit_id'] == $p[3] && $m['material_name'] === $p[4]) {
                    $m['current_stock'] = $p[0];
                    $m['status'] = $p[1];
                    $m['remarks'] = $p[2];
                    $affected++;
                }
            }
            unset($m);
        } elseif (str_starts_with($sql, 'INSERT INTO chemical_spill_kit_inspection_tbl')) {
            $lastId = 700 + count($fixture['checklists']);
            $fixture['checklists'][$lastId] = [];
            $affected = 1;
        } elseif (str_starts_with($sql, 'INSERT INTO chemical_spill_kit_materials_checklist_tbl')) {
            $fixture['checklists'][$p[0]][] = ['material_name' => $p[1], 'current_stocks' => $p[2], 'status' => $p[3], 'remarks' => $p[4]];
            $affected = 1;
        } elseif (str_starts_with($sql, 'INSERT INTO transactions_tbl')) {
            $id = count($fixture['transactions']) + 1;
            $fixture['transactions'][$id] = ['transaction_id' => $id, 'inspection_id' => $p[2], 'status' => 'pending', 'branch' => $p[1]];
            $affected = 1;
        } elseif (str_starts_with($sql, 'UPDATE transactions_tbl')) {
            $approved = str_contains($sql, "status = 'approved'");
            $id = $approved ? $p[1] : $p[2];
            if (($fixture['transactions'][$id]['status'] ?? '') === 'pending') {
                $fixture['transactions'][$id]['status'] = $approved ? 'approved' : 'rejected';
                $affected = 1;
            }
        } else {
            throw new RuntimeException('Unexpected mock SQL: ' . $sql);
        }
        $statements[$stmt]['rows'] = $rows;
        return true;
    }
    function mysqli_stmt_get_result($stmt)
    {
        global $statements, $results, $nextHandle;
        $id = $nextHandle++;
        $results[$id] = ['rows' => $statements[$stmt]['rows'], 'index' => 0];
        return $id;
    }
    function mysqli_fetch_assoc($result)
    {
        global $results;
        return $results[$result]['rows'][$results[$result]['index']++] ?? null;
    }
    function mysqli_fetch_all($result, $mode)
    {
        global $results;
        return $results[$result]['rows'];
    }
    function mysqli_num_rows($result)
    {
        global $results;
        return count($results[$result]['rows']);
    }
    function mysqli_free_result($result)
    {
        global $results;
        unset($results[$result]);
    }
    function mysqli_stmt_close($stmt)
    {
        global $statements;
        unset($statements[$stmt]);
    }
    function mysqli_stmt_affected_rows($stmt)
    {
        global $affected;
        return $affected;
    }
    function mysqli_insert_id($connection)
    {
        global $lastId;
        return $lastId;
    }
    function mysqli_begin_transaction($connection)
    {
        global $snapshot, $fixture;
        $snapshot = $fixture;
        return true;
    }
    function mysqli_commit($connection)
    {
        global $snapshot;
        $snapshot = null;
        return true;
    }
    function mysqli_rollback($connection)
    {
        global $snapshot, $fixture;
        if ($snapshot !== null) $fixture = $snapshot;
        $snapshot = null;
        return true;
    }
}
loadModelFunctions('ChemicalSpillKitMaterialModel.php');
loadModelFunctions('ChemicalSpillKitModel.php');
loadModelFunctions('ChemicalSpillKitInspectionModel.php');

foreach (['0', '1', '2147483647', 10] as $value) check(chemicalSpillKitQuantityIsValid($value), 'Valid quantity rejected');
foreach (['', '-1', '1.2', '1e2', '2147483648', '999999999999999999999', [], null, true] as $value) check(!chemicalSpillKitQuantityIsValid($value), 'Invalid quantity accepted');
check(chemicalSpillKitMaterialKey(' Boom/Pillow ') === chemicalSpillKitMaterialKey('Boom Pillow'), 'Boom alias mismatch');
function submission($kitId = null)
{
    $config = getChemicalSpillKitConfiguredMaterials('Laguna');
    $existing = $kitId === null ? [] : getChemicalSpillKitCurrentMaterials($kitId);
    return array_map(fn($m) => ['material_name' => $m['material_name'], 'current_stock' => '50', 'remarks' => 'fixture'], array_values(mergeChemicalSpillKitMaterials($config, $existing)));
}
check(addNewChemicalSpillKit('CSK-900', 'Laguna', 'Fixture', submission()), 'Eight-material add failed');
$kitId = 100;
check(count(getChemicalSpillKitCurrentMaterials($kitId)) === 8, 'Eight companion records not saved');
check(!addNewChemicalSpillKit('CSK-900', 'Laguna', 'Fixture', submission()), 'Duplicate code accepted');
$before = $fixture;
check(!updateSpillKitMaterials($kitId, 'Wrong branch', submission($kitId), 'Cebu'), 'Cross-branch edit accepted');
check($fixture === $before, 'Rejected edit changed fixture');
$required['Absorbent Granules'] = 5;
$submitted = submission($kitId);
check(count($submitted) === 9, 'New configured material not retrieved');
check(updateSpillKitMaterials($kitId, 'Updated', $submitted, 'Laguna'), 'Nine-material edit failed');
check(count(getChemicalSpillKitCurrentMaterials($kitId)) === 9, 'New material was not inserted');
// A saved slash spelling must be reused rather than duplicated by an edit.
foreach ($fixture['materials'] as &$m) if ($m['chemical_spill_kit_id'] === $kitId && $m['material_name'] === 'Boom Pillow') $m['material_name'] = 'Boom/Pillow';
unset($m);
check(updateSpillKitMaterials($kitId, 'Alias', submission($kitId), 'Laguna'), 'Alias edit failed');
check(count(getChemicalSpillKitCurrentMaterials($kitId)) === 9, 'Alias created a duplicate');
$invalid = submission($kitId);
$invalid[0]['current_stock'] = '1.5';
$before = $fixture;
check(!updateSpillKitMaterials($kitId, 'Invalid', $invalid, 'Laguna'), 'Decimal edit accepted');
check($fixture === $before, 'Invalid edit changed fixture');
$bad = submission($kitId);
$bad[0]['material_name'] = 'Injected material';
check(validateChemicalSpillKitMaterials($bad, getChemicalSpillKitConfiguredMaterials('Laguna'), getChemicalSpillKitCurrentMaterials($kitId)) === false, 'Unknown material accepted');
$bad = submission($kitId);
$bad[1]['material_name'] = $bad[0]['material_name'];
check(validateChemicalSpillKitMaterials($bad, getChemicalSpillKitConfiguredMaterials('Laguna'), getChemicalSpillKitCurrentMaterials($kitId)) === false, 'Duplicate submitted material accepted');
$required['Boom Pillow'] = 6;
check(getChemicalSpillKitConfiguredMaterials('Laguna') === false, 'Duplicate configured alias accepted');
unset($required['Boom Pillow']);
$beforeCurrent = $fixture['materials'];
$newInspection = insertChemicalInspection('CSK-900', 'Laguna', 'Fixture', 'Tester', '2026-11-09', submission($kitId), $kitId, 'Tester');
check($newInspection !== false, 'Dynamic inspection submission failed');
check(count($fixture['checklists'][$newInspection]) === 9, 'Ninth historical checklist material missing');
check($fixture['materials'] === $beforeCurrent, 'Submitting a pending inspection changed current quantities');
check($fixture['checklists'][$newInspection][1]['material_name'] === 'Boom/Pillow', 'Historical Boom spelling changed');
// Reuse clean pending transaction fixtures for the approval tests below.
$fixture['transactions'] = [];
// A material removed from configuration is retained, including its stored condition.
unset($required['Socks']);
$oldStatus = getChemicalSpillKitCurrentMaterials($kitId)['socks']['status'];
check(updateSpillKitMaterials($kitId, 'Retained', submission($kitId), 'Laguna'), 'Existing unconfigured material lost');
check(getChemicalSpillKitCurrentMaterials($kitId)['socks']['status'] === $oldStatus, 'Unconfigured material condition changed');
$fixture['checklists'][7] = array_map(fn($m) => ['material_name' => $m['material_name'], 'current_stocks' => '3', 'status' => 'No Good', 'remarks' => 'approved snapshot'], array_values(getChemicalSpillKitCurrentMaterials($kitId)));
$history = $fixture['checklists'];
// A newer material must not prevent approval of an older complete checklist snapshot.
$required['Neutralizer'] = 2;
$newMaterialId = max(array_keys($fixture['materials'])) + 1;
$fixture['materials'][$newMaterialId] = ['current_material_id' => $newMaterialId, 'chemical_spill_kit_id' => $kitId, 'material_name' => 'Neutralizer', 'current_stock' => 100, 'status' => 'Good', 'remarks' => 'Newer material', 'updated_at' => null];

$fixture['transactions'][1] = ['transaction_id' => 1, 'inspection_id' => 7, 'status' => 'pending', 'transaction_branch' => 'Laguna', 'branch' => 'Laguna', 'chemical_spill_kit_id' => $kitId, 'chemical_spill_kit_code' => 'CSK-900'];
$_SESSION = ['Role' => 'Inspector', 'Branch' => 'Cebu'];
check(!updateChemicalSpillKitEvaluation(7, 'Approved', 'Tester'), 'Cross-branch approval accepted');
$_SESSION = ['Role' => 'Admin', 'Branch' => 'Laguna'];
$before = $fixture;
$failMaterialWrite = true;
check(!updateChemicalSpillKitEvaluation(7, 'Approved', 'Tester'), 'Failed synchronization approved');
check($fixture === $before, 'Failed approval did not roll back');
$failMaterialWrite = false;
check(updateChemicalSpillKitEvaluation(7, 'Approved', 'Tester'), 'Approval failed');
check($fixture['transactions'][1]['status'] === 'approved', 'Transaction was not approved');
foreach (getChemicalSpillKitCurrentMaterials($kitId) as $key => $m) {
    if ($key === 'neutralizer') check($m['current_stock'] === 100 && $m['remarks'] === 'Newer material', 'Older approval changed a newer material');
    else check($m['current_stock'] === 3 && $m['remarks'] === 'approved snapshot', 'Approved quantity/remarks not synchronized');
}
check($fixture['checklists'] === $history, 'Historical quantities changed');
check(!updateChemicalSpillKitEvaluation(7, 'Approved', 'Tester'), 'Repeated approval accepted');
$fixture['transactions'][2] = array_merge($fixture['transactions'][1], ['transaction_id' => 2, 'inspection_id' => 8, 'status' => 'pending']);
$beforeMaterials = $fixture['materials'];
check(updateChemicalSpillKitEvaluation(8, 'Rejected', 'Tester', 'Rejected fixture'), 'Rejection failed');
check($fixture['materials'] === $beforeMaterials, 'Rejection changed inventory');
check($fixture['materials'][1]['remarks'] === 'Preserve me', 'Orphan material changed');
$before = $fixture;
$failMaterialWrite = true;
check(!addNewChemicalSpillKit('CSK-901', 'Laguna', 'Failure', submission()), 'Failed add succeeded');
check($fixture === $before, 'Failed add left a parent or material record');
$failMaterialWrite = false;
check(!array_key_exists('pads', $fixture['parents'][$kitId]), 'Legacy parent quantities written');
echo 'PASS: ' . $checks . " isolated assertions; no database connections or writes.\n";
