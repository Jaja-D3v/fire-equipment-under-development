<?php

/**
 * Server-owned equipment definitions for the QR Generator.
 * Function mappings point to explicit model/controller functions; request data
 * is never used as a SQL table or column name.
 */
function getQREquipmentRegistry()
{
    return [
        'fire_extinguisher' => [
            'label' => 'Fire Extinguisher',
            'code_label' => 'Extinguisher Code',
            'type_heading' => 'Type',
            'icon' => 'bi-fire',
            'list_function' => 'getFireExtinguisherQRRows',
            'branches_function' => 'getFireExtinguisherQRBranches',
            'lookup_function' => 'getFireExtinguisherQRRecord',
            'pdf_title' => 'Fire Extinguisher QR Codes',
            'pdf_filename' => 'fire-extinguisher-qr.pdf',
        ],
        'chemical_spill_kit' => [
            'label' => 'Chemical Spill Kit',
            'code_label' => 'Chemical Spill Kit Code',
            'type_heading' => 'Equipment',
            'icon' => 'bi-shield-exclamation',
            'list_function' => 'getChemicalSpillKitQRRows',
            'branches_function' => 'getChemicalSpillKitQRBranches',
            'lookup_function' => 'getChemicalSpillKitQRRecord',
            'pdf_title' => 'Chemical Spill Kit QR Codes',
            'pdf_filename' => 'chemical-spill-kit-qr.pdf',
        ],
    ];
}

function getDefaultQREquipmentId()
{
    return 'fire_extinguisher';
}

function getQREquipmentDefinition($equipment)
{
    $registry = getQREquipmentRegistry();
    if (!is_string($equipment) || !isset($registry[$equipment])) return false;
    return $registry[$equipment];
}
