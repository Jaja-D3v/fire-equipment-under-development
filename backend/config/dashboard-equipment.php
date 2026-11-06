<?php
// Server-owned registry: request parameters never supply SQL identifiers.
function getDashboardEquipmentRegistry()
{
    return array(
        'fire_extinguisher' => array(
            'id' => 'fire_extinguisher',
            'label' => 'Fire Extinguisher',
            'icon' => 'bi-fire',
            'color' => '#305dff',
            'url' => 'list-extinguisher.php',
            'summary_function' => 'getDashboardFireExtinguisherSummary'
        ),
        'chemical_spill_kit' => array(
            'id' => 'chemical_spill_kit',
            'label' => 'Chemical Spill Kit',
            'icon' => 'bi-shield-exclamation',
            'color' => '#269b8d',
            'url' => 'chemical-spill-kit-inventory.php',
            'summary_function' => 'getDashboardChemicalSpillKitSummary'
        )
    );
}
