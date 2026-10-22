<?php

require_once __DIR__ . '/../model/DropdownModel.php';


function getAllDropdownBranches(){
    return getAllBranchDropdown();
}

function getAllPlacementDropdownController(){
    return getAllPlacementDropdown();
}

function getAllCapacityDropdownController(){
    return getAllCapacityDropdown();
}

function getAllTypeDropdownController(){
    return getAllTypeDropdown();
}

function getAllClassDropdownController(){
    return getAllClassDropdown();
}