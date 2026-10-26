<?php

require_once __DIR__ . '/../model/ChemicalSpillKitModel.php';

function getAllListChemicalSpill($branch){

    return getAllChemicalSpillKit($branch);

}


// this is for get by code 
function getChemicalSpillKitInventopryByCode($code , $branch){

    return getChemicalSpillKitByCode($code, $branch);
    
}

$result = getChemicalSpillKitInventopryByCode('CSK-001', 'Laguna');




