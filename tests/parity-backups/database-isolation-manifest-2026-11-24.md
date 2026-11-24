# Fire Extinguisher parity-test database isolation

Created: 2026-11-24 (Asia/Manila)

## Original configuration

Both projects used `localhost`, MySQL user `root`, an empty password, and database `ems_db`.

## Temporary configuration

- `C:\xampp\htdocs\kpsafehub\backend\config\db.php` now selects `ems_db_reference`.
- `C:\xampp\htdocs\fire-equipment-under-development\backend\config\db.php` now selects `ems_db_development`.

## Original database backup

- File: `tests/parity-backups/ems_db-before-parity-2026-11-24.sql`
- SHA-256: `68E1AE085AD0397214052EC41AD0E1B86A3ED47C9D3251C2B1A28BE079CA920B`

The backup was restored into both clones before any functional tests. Both configuration files were restored to `ems_db` after the controlled test run.
