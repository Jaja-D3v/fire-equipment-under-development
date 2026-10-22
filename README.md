# IMS Safety Management System

`EMS_home` is the source repository for the IMS Safety Management System, a PHP application for managing fire-extinguisher inventory and related safety inspections.

## Project overview

The application brings extinguisher inventory, QR identification, inspection recording and review, expiration monitoring, activity history, and report generation into one browser-based system. It is intended to help authorized staff keep equipment records organized, record inspection outcomes, and review units that need follow-up.

This README describes behavior supported by the source and checked-in SQL. It does not certify a deployed installation, live database schema, or runtime behavior.

## Key features

- Inventory listing, search, branch and condition filters, create/edit, archive, and restore flows.
- Extinguisher QR-code generation, printing, and scanning.
- Inspection checklist submission, inspector inspection history, and pending, approved, and rejected review pages.
- Expiration monitoring and dashboard summaries.
- Fire-extinguisher history and activity-log views.
- Maintenance dropdown management for shared inventory values.
- PDF report generation with TCPDF and spreadsheet report generation with PhpSpreadsheet.

Availability and authorization vary by page and role; see [Roles and permissions](documentation/user-roles-and-permissions.md) and the module notes in [documentation/README.md](documentation/README.md).

## Technology stack

- PHP with procedural page, Controller, and Model code; database access uses `mysqli` and prepared statements.
- MySQL/MariaDB for operational data. The checked-in dump identifies `ems_db` and was generated from MariaDB 10.4.32; the server version used by a particular installation must be confirmed locally.
- HTML, CSS, and browser JavaScript.
- Bootstrap 5.3.3 and Bootstrap Icons 1.11.3 are loaded from CDNs on the login and shared application pages. The project also includes local CoreUI and SimpleBar assets. Chart.js, SweetAlert2, html5-qrcode, and Font Awesome are referenced by application pages.
- Composer dependencies: TCPDF `6.10.1` and PhpSpreadsheet `1.29` (locked as `1.29.0`).

The current locked dependency tree requires 64-bit PHP 8.2 or newer. The application also uses `mysqli_stmt_get_result()`, which requires the mysqlnd driver. Verify the PHP version and extensions used by both the command line and Apache.

## System requirements

- XAMPP or an equivalent local stack providing Apache, PHP, and MySQL/MariaDB.
- 64-bit PHP 8.2 or newer for the current locked Composer dependency tree.
- PHP extensions/drivers: `mysqli` with mysqlnd; `curl` for TCPDF; and the PhpSpreadsheet requirements `ctype`, `dom`, `fileinfo`, `gd`, `iconv`, `libxml`, `mbstring`, `simplexml`, `xml`, `xmlreader`, `xmlwriter`, `zip`, and `zlib`.
- Composer, when installing or restoring the locked PHP dependencies.
- Network access for pages that load frontend libraries from public CDNs, unless those assets are otherwise made available locally.
- An EMS operational database and a compatible identity database/account source for login (see [Database setup](#database-setup)).

## Installation guide

1. Clone the project using your organization's repository URL, or place the project directory at `C:\xampp\htdocs\EMS_home`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Configure the database connections described in [Configuration](#configuration). Do not use a production database for local setup.
4. Import the EMS SQL dump into a fresh local database as described in [Database setup](#database-setup). Confirm schema compatibility before using the application because the dump may not contain every field or table referenced by current code.
5. From the project root, restore dependencies if needed:

   ```sh
   composer install
   ```

   The repository includes `composer.json`, `composer.lock`, and a `vendor/` directory. `composer install` uses the lock file to resolve the declared versions.

6. Open the login page at `http://localhost/EMS_home/authentication/login.php`. There is no root `index.php` entry point in this repository.

## Database setup

### EMS operational database

`backend/config/db.php` selects the database named `ems_db`. The checked-in import file is [`SQL_FIles/FEMS-DB.sql`](SQL_FIles/FEMS-DB.sql). It creates/selects `ems_db`, defines these tables, and includes data rows:

- `fire_extinguishers_tbl`
- `inspection_checklist_tbl`
- `activity_logs_tbl`
- `maintenance_dropdown_tbl`

Use phpMyAdmin or a compatible MySQL/MariaDB client to import this file into a fresh, disposable local environment. The dump contains inserts and schema changes; avoid importing it into a database containing records you need to keep. No database import was performed while preparing this README.

The SQL file is a checked-in snapshot, not proof of the current deployed schema. Compare it with the fields and tables referenced by the Models before relying on it; [Database reference](documentation/database-reference.md) records known source/dump gaps.

### Identity database

`backend/config/db_user.php` selects a separate database named `kp201`. `backend/model/AuthModel.php` queries `kane_users_login` and joins `kane_jobinfo`. This repository does not include a `kp201` schema dump, so a compatible identity database and authorized account must be provisioned separately. No sample login credentials are provided.

## Configuration

- `backend/config/db.php` configures the EMS operational database connection.
- `backend/config/db_user.php` configures the separate identity database connection used by authentication.

Set the host, database user, password, and database name in the appropriate local configuration for your environment. The checked-in files contain development connection values; this README intentionally does not repeat them. Keep credentials out of source control and do not expose these configuration files through a public web server.

## User roles and permissions

The inspected application logic principally recognizes `Admin` and `Inspector` roles in the session. Access checks are implemented across page guards, Controllers, and Models. Inspector operations commonly use the session-assigned branch; some Admin screens provide branch selection, with exact behavior depending on the module. A visible or hidden control alone is not an authorization boundary.

Review [Roles and permissions](documentation/user-roles-and-permissions.md) and [Authentication and user access](documentation/authentication-and-user-access.md) for source-based details and limitations. Account provisioning and the live role/branch mapping depend on the separate identity source.

## How to use

1. Sign in using an account provisioned in the configured identity system.
2. Use the dashboard and inventory pages to review extinguisher records and branch-level summaries.
3. Generate or print QR codes for inventory units; scan a unit to open its inspection workflow.
4. Submit an inspection checklist. Authorized reviewers can view pending inspections and approve or reject them with corrective-action details.
5. Review rejected inspections, inspection history, and expiration monitoring for follow-up.
6. Use the available report actions to generate inventory or inspection reports.

The exact pages available depend on the signed-in role. For detailed page descriptions, see [the documentation index](documentation/README.md).

## Project structure

```text
EMS_home/
├── authentication/       # Login and system-selection pages
├── backend/
│   ├── authentication/   # Session guards and logout helpers
│   ├── config/           # EMS and identity database connections
│   ├── controller/       # Procedural request handlers
│   └── model/             # mysqli queries and data operations
├── assets/               # Images and branding
├── css/                  # Application styles
├── documentation/        # Source-based module and schema notes
├── error-pages/          # HTTP error pages
├── helpers/              # QR and report helpers
├── js/                   # Browser-side page behavior
├── notification/         # Shared notification markup/scripts
├── partials/              # Shared page components and modals
├── SQL_FIles/
│   └── FEMS-DB.sql       # Checked-in EMS SQL snapshot
├── vendors/              # Local frontend vendor assets
├── vendor/               # Composer dependencies
├── dashboard.php         # Dashboard page
├── list-extinguisher.php # Active extinguisher inventory
├── inspection-pending.php
├── inspection-approved.php
└── inspection-rejected.php
```

## Troubleshooting

- **Database connection fails:** Confirm the Apache PHP configuration has `mysqli` enabled, the database server is running, and both database connection files point to the intended local databases.
- **Login cannot find an account or branch:** Authentication queries the separate `kp201` database and expects `kane_users_login` and `kane_jobinfo`; the EMS dump does not provide that identity data.
- **A Model reports missing columns or tables:** The checked-in EMS dump may be older or incomplete relative to current source. Compare the live local schema with `documentation/database-reference.md` before changing schema or application code.
- **`mysqli_stmt_get_result()` is unavailable:** Enable/use PHP built with mysqlnd for the Apache runtime.
- **Composer reports an incompatible PHP version or missing extension:** Confirm the active CLI PHP is 64-bit PHP 8.2 or newer and enable the required extensions listed above. XAMPP's CLI and Apache PHP configurations can differ.
- **Styles or browser libraries are missing:** Check network access to the CDNs referenced by the page, or arrange an approved local asset source.
- **The page is not found:** Confirm the folder is named `EMS_home` under XAMPP's `htdocs` and use the `authentication/login.php` path shown above.

## Security notes

- Replace development database connection values with environment-appropriate credentials; do not commit secrets or share them in screenshots or logs.
- Use least-privilege database accounts rather than broad administrative credentials for a shared or deployed environment.
- Keep the identity database and EMS database access restricted to trusted services and users.
- Preserve server-side session, role, and branch checks. Review each request handler independently; UI visibility does not provide access control.
- Protect configuration and SQL dump files from public exposure. Treat the checked-in SQL dump as data-bearing and import it only into an appropriate local environment.
- Authentication, deployment hardening, HTTPS, and the live database configuration require environment-specific review.

## Contributors

Contributor names are not established by the checked-in project metadata. Maintainers/contributors: **[Add verified names or team]**.

## License

The repository does not currently specify a project license. Third-party packages and assets may have their own licenses; refer to their included license files and package metadata.
