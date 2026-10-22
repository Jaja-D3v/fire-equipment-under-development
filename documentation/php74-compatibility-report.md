# PHP 7.4 Compatibility Audit — EMS_home

Audit date: 2026-10-15  
Scope: Read-only static review of project-owned PHP source, Composer metadata, installed dependency metadata, and database API usage.

## 1. Executive Summary

- **Overall result: FAIL** for the currently locked/installed dependency set. The source-code scan found no confirmed PHP 8-only syntax or built-ins in project-owned PHP, but the dependency set cannot run on PHP 7.4.
- **Project-owned PHP files inspected:** 75 (recursive `*.php` inventory excluding `vendor/`).
- **Confirmed PHP 7.4 incompatibilities:** 3 dependency/runtime-platform blockers (two package requirements and the generated Composer platform check; overlapping evidence, not three independent libraries).
- **Potential issues:** 2 runtime/environment items requiring verification (required extensions and database parameter/runtime behavior).
- **Dependencies requiring investigation:** 2 locked transitive packages with PHP 8+ requirements; see Section 4.
- **Actual PHP 7.4 tests:** No. The available CLI is PHP 8.2.12 (`C:\xampp\php\php.exe`). No PHP 7.4 lint or runtime test was performed.
- **Source findings:** No occurrences of the checked PHP 8+ syntax/features or named PHP 8-only functions were found in the 75 project-owned PHP files. Static inspection is not a guarantee of runtime compatibility.

## 2. Confirmed PHP 7.4 Incompatibilities

### C1 — Locked PSR Simple Cache package requires PHP 8

- **Severity:** HIGH
- **Status:** CONFIRMED
- **File:** `composer.lock` (package entry for `psr/simple-cache` 3.0.0; package metadata is also present at `vendor/psr/simple-cache/composer.json`)
- **Line:** `composer.lock` package metadata; installed package requirement is visible in `vendor/psr/simple-cache/composer.json` under `require.php`.
- **Function/syntax/API:** Package PHP platform requirement `>=8.0.0`.
- **Evidence:** Installed metadata declares `"php": ">=8.0.0"`. The lock selects `psr/simple-cache` 3.0.0, and PhpSpreadsheet 1.29.0 allows that major through `^1.0 || ^2.0 || ^3.0`.
- **Why incompatible:** PHP 7.4 does not satisfy the package's declared minimum version. Composer can reject the dependency set, and package source/runtime assumptions are not supported on 7.4.
- **Recommended fix (not applied):** Resolve dependencies with PHP 7.4 as the Composer platform and select compatible package versions, then validate the regenerated lockfile and installation in a PHP 7.4 environment.

### C2 — Locked ZipStream package requires 64-bit PHP 8.2

- **Severity:** HIGH
- **Status:** CONFIRMED
- **File:** `composer.lock` (package entry for `maennchen/zipstream-php` 3.1.2; installed metadata also at `vendor/maennchen/zipstream-php/composer.json`)
- **Line:** `composer.lock` package metadata, `require.php-64bit`.
- **Function/syntax/API:** Platform requirement `php-64bit: ^8.2`.
- **Evidence:** Lock and installed metadata identify version 3.1.2 with `php-64bit` `^8.2`. PhpSpreadsheet 1.29.0 allows ZipStream `^2.1 || ^3.0`, so the resolved 3.1.2 is selected through its `^3.0` range.
- **Why incompatible:** PHP 7.4 cannot satisfy a requirement for 64-bit PHP 8.2 or newer. This package is a required PhpSpreadsheet dependency for spreadsheet archive support.
- **Recommended fix (not applied):** Resolve a ZipStream release compatible with the PHP 7.4 platform using Composer, then check the full transitive dependency graph and Excel export behavior.

### C3 — Generated Composer platform check rejects PHP below 8.2

- **Severity:** HIGH
- **Status:** CONFIRMED
- **File:** `vendor/composer/platform_check.php`
- **Line:** 7
- **Function/syntax/API:** Generated runtime platform guard checks `PHP_VERSION_ID >= 80200`.
- **Code:** `if (!(PHP_VERSION_ID >= 80200)) {`
- **Why incompatible:** A PHP 7.4 runtime has a lower `PHP_VERSION_ID`, so Composer's autoloader throws a `RuntimeException` before dependent application functionality can load.
- **Recommended fix (not applied):** Regenerate/install the vendor tree from a dependency resolution that supports PHP 7.4. Do not edit this generated check manually; it reflects the current installed platform requirements.

## 3. Potential Compatibility Issues

- **mysqli/mysqlnd availability:** Application database code uses `mysqli` and repeatedly calls `mysqli_stmt_get_result()`. That API requires the mysqlnd driver. `mysqli` and mysqlnd availability on the target production PHP 7.4 server is unknown. Verify both before deployment.
- **Prepared-statement parameter behavior:** The inspected source contains numerous `bind_param()` calls, including variadic parameter unpacking (for example `backend/model/ActivityLogModel.php:77` and `backend/model/FireExtinguisherModel.php:1022`). The syntax is compatible with PHP 7.4, but the static scan did not execute every query or validate each runtime parameter count/type against its SQL. Exercise database paths with representative data on a non-production PHP 7.4 setup.
- **No confirmed post-7.4 project-source syntax found:** The scan checked for `str_contains`, `str_starts_with`, `str_ends_with`, `match`, nullsafe `?->`, attributes, enums, readonly declarations, union/intersection types, and later PHP APIs. HTML `readonly` form attributes are not PHP readonly properties and are not findings.

## 4. Composer Dependency Compatibility

The root `composer.json` requires `tecnickcom/tcpdf` exactly `6.10.1` and `phpoffice/phpspreadsheet` `1.29`. `composer.lock` has no root `platform` constraint (`platform: {}`), so dependency resolution was not pinned to PHP 7.4.

| Package | Locked version | PHP requirement in available metadata | Status / evidence |
|---|---:|---|---|
| `phpoffice/phpspreadsheet` | 1.29.0 | `^7.4 || ^8.0` | Compatible in isolation with PHP 7.4; required transitive packages below make the resolved set incompatible. Requires the extensions listed in Section 5. |
| `maennchen/zipstream-php` | 3.1.2 | `php-64bit: ^8.2` | **INCOMPATIBLE** with PHP 7.4; required by PhpSpreadsheet's accepted ZipStream range. |
| `psr/simple-cache` | 3.0.0 | `>=8.0.0` | **INCOMPATIBLE** with PHP 7.4; accepted by the PhpSpreadsheet lock constraint. |
| `tecnickcom/tcpdf` | 6.10.1 | `>=7.1.0` | PHP constraint is compatible with PHP 7.4. Its lock metadata requires `ext-curl`; target extension availability is unknown. |
| `ezyang/htmlpurifier` | 4.19.1 | Explicitly allows PHP 7.4 | Compatible per lock metadata. Suggests optional extensions including iconv; not a PHP version blocker. |
| `markbaker/complex` | 3.0.2 | `^7.2 || ^8.0` | Compatible per lock metadata. |
| `markbaker/matrix` | 3.0.1 | `^7.1 || ^8.0` | Compatible per lock metadata. |
| `psr/http-client` | 1.0.3 | `^7.0 || ^8.0` | Compatible per lock metadata. |
| `psr/http-factory` | 1.1.0 | `>=7.1` | Compatible per lock metadata. |
| `psr/http-message` | 2.0 | `^7.2 || ^8.0` | Compatible per lock metadata. |

The installed `vendor/composer/platform_check.php` is generated to require PHP 8.2+, consistent with the two incompatible transitive requirements. The installed vendor metadata was read only; no package was installed or changed. No separate QR Composer package was identified in the lockfile. QR output uses TCPDF's barcode/QR functionality in application code.

## 5. Required PHP Extensions

Production extension availability was not tested and is **unknown**. Requirements below are derived from source calls and locked package metadata.

| Extension / driver | Feature using it | Requirement / availability |
|---|---|---|
| `mysqli` | Application database connections and queries (`backend/config/db.php`, `backend/config/db_user.php`, models/controllers) | Required by application; production availability unknown. |
| `mysqlnd` | `mysqli_stmt_get_result()` calls used throughout models | Required for this API; production availability unknown. |
| `ext-ctype`, `ext-dom`, `ext-fileinfo`, `ext-gd`, `ext-iconv`, `ext-libxml`, `ext-mbstring`, `ext-simplexml`, `ext-xml`, `ext-xmlreader`, `ext-xmlwriter`, `ext-zip`, `ext-zlib` | PhpSpreadsheet 1.29.0 declared requirements for Excel/report features | Required when the Composer dependency is installed/used; production availability unknown. |
| `ext-curl` | TCPDF 6.10.1 declared requirement in lock metadata | Declared package requirement; production availability unknown. |
| `ext-mbstring`, `ext-zlib` | ZipStream 3.1.2 declared requirements | Required by the currently locked package; production availability unknown. |
| `ext-iconv`, `ext-bcmath`, `ext-tidy` | HTMLPurifier suggestions in lock metadata | Suggested/optional for specific functionality; not declared as mandatory by the package. |

## 6. SQL and Database Concerns

### PHP-level database API

- The project uses `mysqli`, prepared statements, and `bind_param()`, all available in PHP 7.4.
- `mysqli_stmt_get_result()` is available when using mysqlnd; confirm that driver on the production build.
- Static inspection found no PHP 8-only mysqli API usage. A full query-by-query execution and bind parameter audit requires a compatible test database and representative workflows; none was run.

### MySQL/MariaDB server-version concerns

- No database connection was made and no SQL was executed.
- No SQL statement was identified here as a PHP 7.4 incompatibility. MySQL/MariaDB feature compatibility depends on the production server version/schema and was not verified.
- Database version, schema, SQL modes, and server-side behavior remain unverified.

## 7. Files and Areas Not Fully Verified

- PHP 7.4 parsing/runtime behavior was not executed; only PHP 8.2.12 is available in the project environment.
- The target production extension set, 64-bit architecture, web SAPI configuration, and Composer autoload deployment state are unknown.
- Application behavior requiring database contents, browser interaction, QR scanning devices, report output, or external runtime configuration was not exercised.
- SQL compatibility with the deployed MySQL/MariaDB version and schema was not tested.
- `vendor/` dependency implementation source was excluded from project-owned source findings; package versions, declared requirements, installed platform check, and metadata were reviewed as dependency evidence.
- `vendors/` contains no PHP files. The project-owned source inventory contained 75 PHP files outside `vendor/`.

## 8. Recommended Testing Checklist

After resolving the PHP-version dependency blockers and preparing a non-production PHP 7.4 environment with the required extensions, test:

- Login, session creation, timeout, and logout.
- Inventory create, read, update, archive, restore, and delete workflows.
- QR generation, printing, and scanning.
- Inspection submission and checklist validation.
- Inspection approval and rejection flows.
- Expiration filtering and notifications.
- Search, filtering, and pagination.
- Maintenance dropdowns and activity logs.
- PDF report generation (TCPDF).
- Excel report generation (PhpSpreadsheet/ZipStream).
- Database operations and prepared statements using representative data, checking for warnings, exceptions, and parameter mismatches.
- Composer autoload initialization under PHP 7.4 before testing application routes.

## 9. Final Assessment

EMS_home is **not ready for PHP 7.4 with the currently locked and installed Composer dependencies**. The project-owned PHP source scan did not identify confirmed syntax or built-in API incompatibilities, and several direct package constraints (PhpSpreadsheet 1.29.0 and TCPDF 6.10.1) allow PHP 7.4. However, the locked ZipStream 3.1.2 and PSR Simple Cache 3.0.0 requirements exclude PHP 7.4, and the generated Composer platform check explicitly refuses versions below PHP 8.2. These are sufficient blockers independent of the application source scan.

This assessment is based on static source and metadata review. No PHP 7.4 test, database execution, or production configuration verification was performed. No project source, database, environment/configuration, or dependency files were modified during the audit; this report is the only intended new file.
