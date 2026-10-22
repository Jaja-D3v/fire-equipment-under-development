# KPFEMS System Documentation

Source-based handover documentation for the KPFEMS / IMS Safety Management System in this repository. The documents describe implementation found in the PHP pages, Controllers, Models, JavaScript, helpers, and checked-in SQL files. They do not imply that the deployed database or runtime was tested.

## Index

- [System overview](system-overview.md) — architecture, request flow, and scope.
- [Roles and permissions](user-roles-and-permissions.md) — verified session roles and branch controls.
- [Dashboard](dashboard.md) — inventory and inspection summaries.
- [Fire extinguisher inventory](fire-extinguisher-inventory.md) — active inventory, search, filters, editing, and actions.
- [Archived extinguishers](archived-extinguishers.md) — archived listing, search, details, and restore flow.
- [QR code management](qr-code-management.md) — QR generation, printing, and scanning.
- [Inspections](inspections.md) — inspection submission and inspector history.
- [Inspection approvals](inspection-approvals.md) — pending and approved inspection review.
- [Rejected inspections](rejected-inspections.md) — rejected results and inspector-facing rejected history.
- [Expiration monitoring](expiration-monitoring.md) — expiring-unit listing and filtering.
- [Activity logs](activity-logs.md) — activity history, date filtering, and pagination.
- [Reports](reports.md) — inspection and inventory PDF generation.
- [Authentication and user access](authentication-and-user-access.md) — login, session handling, and system selection.
- [Maintenance and dropdowns](maintenance-and-dropdowns.md) — maintenance data and shared dropdown sources.
- [Database reference](database-reference.md) — schema evidence and source-level table usage.

## Evidence and limitations

The documentation was prepared from repository source and the checked-in SQL files under SQL_FIles. The EMS dumps are incomplete or differ from fields referenced by current Models; they are not proof of the live schema. The separate kp201.sql dump contains unrelated personnel and authentication data and is intentionally not reproduced here. No credentials, password hashes, personal records, or session secrets are included. No live database or authenticated runtime was queried during this documentation task.
