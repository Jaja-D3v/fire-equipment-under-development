<?php
require_once __DIR__ . '/ChemicalSpillKitRequiredStockModel.php';

// The two existing Boom spellings identify the same material in every CSK workflow.
function chemicalSpillKitMaterialKey($name)
{
    $key = mb_strtolower(trim((string) $name), 'UTF-8');
    return $key === 'boom/pillow' ? 'boom pillow' : $key;
}

function chemicalSpillKitInventoryMaterialName($name)
{
    return chemicalSpillKitMaterialKey($name) === 'boom pillow' ? 'Boom Pillow' : trim((string) $name);
}

// current_stock is a signed INT. Empty, decimal, negative and exponential values are invalid.
function chemicalSpillKitQuantityIsValid($value)
{
    if (!is_string($value) && !is_int($value)) {
        return false;
    }
    $value = trim((string) $value);
    return preg_match('/^[0-9]+$/D', $value) === 1
        && strlen($value) <= 10 && (float) $value <= 2147483647;
}

// Required Stock is the existing branch-based material configuration; no fixed material list.
function getChemicalSpillKitConfiguredMaterials($branch)
{
    $required = getRequiredStockModel($branch);
    if ($required === false) return false;
    $materials = [];
    foreach ($required as $name => $stock) {
        $key = chemicalSpillKitMaterialKey($name);
        if ($key === '' || isset($materials[$key]) || !chemicalSpillKitQuantityIsValid($stock)) {
            return false;
        }
        $materials[$key] = [
            'material_name' => chemicalSpillKitInventoryMaterialName($name),
            'checklist_name' => $name,
            'required_stock' => (int) $stock
        ];
    }
    return $materials;
}

// Always read quantities from the companion table, including unknown (NULL) quantities.
function getChemicalSpillKitCurrentMaterials($kitId, $forUpdate = false)
{
    global $conn;
    $sql = 'SELECT current_material_id, material_name, current_stock, status, remarks, updated_at
            FROM chemical_spill_kit_current_inventory_materials_tbl
            WHERE chemical_spill_kit_id = ? ORDER BY current_material_id ASC';
    if ($forUpdate) $sql .= ' FOR UPDATE';
    $stmt = mysqli_prepare($conn, $sql);
    try {
        mysqli_stmt_bind_param($stmt, 'i', $kitId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $materials = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $key = chemicalSpillKitMaterialKey($row['material_name']);
            if ($key === '' || isset($materials[$key])) {
                throw new RuntimeException('Duplicate or empty CSK current material name.');
            }
            $materials[$key] = $row;
        }
        mysqli_free_result($result);
        return $materials;
    } finally {
        mysqli_stmt_close($stmt);
    }
}

// Show configured materials that have no companion row yet, and retain older existing materials.
function mergeChemicalSpillKitMaterials(array $configured, array $existing)
{
    $materials = [];
    foreach ($configured as $key => $definition) {
        $materials[$key] = array_merge($definition, [
            'current_stock' => null, 'status' => null, 'remarks' => null
        ], $existing[$key] ?? []);
    }
    foreach ($existing as $key => $material) {
        if (!isset($materials[$key])) {
            $materials[$key] = array_merge($material, ['required_stock' => null]);
        }
    }
    return $materials;
}

// Validate names against server-side configuration/current rows, never hidden fields alone.
function validateChemicalSpillKitMaterials($submitted, array $configured, array $existing = [])
{
    if (!is_array($submitted)) {
        return false;
    }
    $definitions = mergeChemicalSpillKitMaterials($configured, $existing);
    if (!$definitions || count($submitted) !== count($definitions)) {
        return false;
    }
    $materials = [];
    foreach ($submitted as $row) {
        if (!is_array($row) || !is_string($row['material_name'] ?? null)) {
            return false;
        }
        $key = chemicalSpillKitMaterialKey($row['material_name']);
        if (!isset($definitions[$key]) || isset($materials[$key])
            || !chemicalSpillKitQuantityIsValid($row['current_stock'] ?? null)
            || !is_string($row['remarks'] ?? '') || strlen($row['remarks'] ?? '') > 65535) {
            return false;
        }
        $stock = (int) trim((string) $row['current_stock']);
        // Existing materials removed from configuration keep their stored condition.
        if (isset($configured[$key])) {
            $status = $stock >= $configured[$key]['required_stock'] ? 'Good' : 'No Good';
        } else {
            $status = $existing[$key]['status'] ?? null;
        }
        $materials[$key] = [
            'material_name' => $definitions[$key]['material_name'],
            'current_stock' => $stock,
            'status' => $status,
            'remarks' => trim($row['remarks'] ?? '')
        ];
    }
    return $materials;
}

// Caller holds the kit lock and transaction. Preserve stored names to avoid alias duplicates.
function saveChemicalSpillKitCurrentMaterials($kitId, array $materials, array $existing)
{
    global $conn;
    $update = mysqli_prepare($conn,
        'UPDATE chemical_spill_kit_current_inventory_materials_tbl
         SET current_stock = ?, status = ?, remarks = ?
         WHERE chemical_spill_kit_id = ? AND material_name = ?');
    $insert = null;
    try {
        $insert = mysqli_prepare($conn,
            'INSERT INTO chemical_spill_kit_current_inventory_materials_tbl
             (chemical_spill_kit_id, material_name, current_stock, status, remarks)
             VALUES (?, ?, ?, ?, ?)');
        foreach ($materials as $key => $material) {
            $stock = $material['current_stock'];
            $status = $material['status'];
            $remarks = $material['remarks'];
            if (isset($existing[$key])) {
                $name = $existing[$key]['material_name'];
                mysqli_stmt_bind_param($update, 'issis', $stock, $status, $remarks, $kitId, $name);
                mysqli_stmt_execute($update);
            } else {
                $name = chemicalSpillKitInventoryMaterialName($material['material_name']);
                mysqli_stmt_bind_param($insert, 'isiss', $kitId, $name, $stock, $status, $remarks);
                mysqli_stmt_execute($insert);
            }
        }
    } finally {
        mysqli_stmt_close($update);
        if ($insert) mysqli_stmt_close($insert);
    }
}
