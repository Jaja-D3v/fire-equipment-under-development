# Expiration Monitoring

## Overview
expiring-extinguisher.php lists extinguishers approaching expiration and offers search, condition, branch, and pagination controls as implemented in the page.

## Data flow
The page requires a session and FireExtinguisherController.php. Its Controller calls getExpiry() in FireExtinguisherModel.php for row data and totals. The Model filters active, non-archived units and applies the implemented expiration window, search, branch, condition, and pagination predicates. The current project memory/source review identifies the window as today through approximately two months ahead; confirm exact date arithmetic in the current Model before relying on boundary dates.

## Actions
The page includes extinguisher detail/edit workflows and a report path. JavaScript fetches Controller endpoints for data such as detail lookup and next code; action availability depends on user permissions.

## Use
1. Open Expiring Fire Extinguishers.
2. Choose branch and condition where controls are available.
3. Search or navigate pages.
4. Review a unit's details and take the permitted inventory action.

## Limitations
The live server date/time zone and deployed database contents were not tested. SQL schema differences are noted in database-reference.md.
