# Archived Fire Extinguishers

## Overview
archived-extinguisher.php lists records marked archived and provides search, branch selection, record details, and restore actions. Access is session-protected.

## Search and listing
The page reads search and page parameters, retrieves authorized archived rows through FireExtinguisherController.php, and applies the page's search behavior to extinguisher code and location. Search results are counted and paginated. The archived Model query restricts rows to archived = 1 and applies branch scope from the existing access logic.

## Use
1. Open Archived Fire Extinguishers.
2. Enter a code or location keyword and submit Search.
3. Select a branch if the Admin control is available.
4. Use pagination to move through matching archived units.
5. Open a record's details or use Restore where authorized.

## Restore and data
Restore is a Controller/Model operation that clears the archive state for a selected extinguisher. Detail retrieval and restore requests are made through existing Controller endpoints used by the page. Do not treat archive as a hard delete.

## Related files
archived-extinguisher.php; backend/controller/FireExtinguisherController.php; backend/model/FireExtinguisherModel.php; backend/model/DropdownModel.php.

## Limitations
No runtime permission or restore test was performed. SQL dump completeness is described in database-reference.md.
