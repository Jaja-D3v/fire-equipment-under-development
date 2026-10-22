# IMS Safety Management System / EMS

## System Requirements and Device Compatibility Documentation

**Document Type:** Technical Documentation\
**Audit Reference Date:** October 19, 2026\
**Scope:** Client devices, responsive compatibility, performance, server
requirements, and security considerations

------------------------------------------------------------------------

## 1. Purpose

This document records the initial system requirements and compatibility
recommendations for the IMS Safety Management System / Fire Extinguisher
Management System (EMS). It is intended to guide device selection,
responsive testing, performance optimization, and deployment planning.

The findings are based on the available source-code inspection and the
requirements audit dated October 19, 2026. The application was not
browser-tested or load-tested during that audit. Therefore,
recommendations and estimates are distinguished from verified facts.

## 2. System Overview

The system is a procedural PHP web application that uses MySQLi for
database access and MariaDB-compatible SQL. The interface includes
locally bundled CoreUI/Bootstrap-style assets and JavaScript libraries.
The project also uses TCPDF for PDF and QR-related functionality and
PhpSpreadsheet for spreadsheet-related functionality.

The application includes modules for:

-   Dashboard and inventory management
-   Fire extinguisher information and archived inventory
-   QR code generation and scanning
-   Fire extinguisher inspection
-   Pending, approved, and rejected inspection workflows
-   Expiring fire extinguisher monitoring
-   Inspection and inventory reports
-   Fire extinguisher history and activity timeline
-   Branch-based filtering and role-based access

## 3. Verified Technology Requirements

  -----------------------------------------------------------------------
  Component                           Audit finding
  ----------------------------------- -----------------------------------
  PHP                                 The available CLI environment
                                      reported PHP 8.2.12, 64-bit.
                                      Composer's platform check requires
                                      PHP 8.2 or newer and 64-bit PHP.

  Database                            The supplied SQL dump records
                                      MariaDB 10.4.32 as its source
                                      version. This does not establish
                                      the minimum supported database
                                      version or the version of a live
                                      deployment.

  Database access                     PHP MySQLi is used.

  PDF and QR-related library          TCPDF 6.10.1 is present in the
                                      dependency metadata.

  Spreadsheet library                 PhpSpreadsheet 1.29.0 is present in
                                      the dependency metadata.

  UI assets                           CoreUI assets, Chart.js, SimpleBar,
                                      and local CSS/JavaScript files are
                                      present. Exact versions of every
                                      bundled UI library were not
                                      verified.

  PHP extensions                      The available CLI reported
                                      mysqli/mysqlnd, GD, mbstring, curl,
                                      zip, and XML-related extensions.
                                      The web-server PHP configuration
                                      must be checked separately.

  Browser requirements                A maintained browser with
                                      JavaScript, cookies, and network
                                      access is recommended. QR camera
                                      scanning also requires camera
                                      permission and a secure context
                                      such as HTTPS or localhost.
  -----------------------------------------------------------------------

**Deployment note:** The production environment must be checked
independently. CLI PHP modules and settings do not guarantee that the
web-server PHP SAPI has the same configuration.

## 4. Client Device Specifications

The project does not declare strict client-side CPU, RAM, storage, or
display minimums. The following are practical recommendations, not
certified application minimums.

  -----------------------------------------------------------------------
  Device category         Practical baseline      Recommended
                                                  configuration
  ----------------------- ----------------------- -----------------------
  Windows desktop/laptop  Supported Windows       4+ CPU cores, 8+ GB
                          release, recent 2-core  RAM, SSD, 1366 × 768 or
                          x64 CPU, 4 GB RAM, 1280 larger display
                          × 720 display, current  
                          browser                 

  macOS computer/laptop   macOS release receiving 8 GB RAM, SSD, 1366 ×
                          security updates,       768 or larger display
                          recent supported        
                          processor, 4 GB RAM,    
                          current                 
                          Safari/Chrome/Firefox   

  Android phone           Security-supported      6+ GB RAM, current
                          Android version,        midrange processor,
                          current Chrome, 4 GB    390+ CSS-pixel
                          RAM, approximately 360  viewport, rear camera
                          CSS-pixel viewport      for QR scanning

  iPhone                  iOS release receiving   Recent supported iPhone
                          security updates,       and current iOS/Safari
                          current Safari, 360     
                          CSS-pixel viewport,     
                          rear camera             

  Android tablet          Supported Android       6+ GB RAM;
                          version, current        10-inch-class display
                          Chrome, 4 GB RAM,       for inventory and
                          approximately 768       approval tables
                          CSS-pixel viewport      

  iPad                    Supported iPadOS,       Recent iPad with a
                          current Safari,         10-inch or larger
                          approximately 768       display
                          CSS-pixel viewport      

  Other devices           Maintained              Validate the specific
                          standards-compliant     device and browser
                          browser, JavaScript,    before operational use
                          cookies, and network    
                          access                  
  -----------------------------------------------------------------------

### 4.1 Network and Connectivity

The application relies on access to the web server and database-backed
functionality. No offline mode was identified during the source review.

-   Use a stable local network or broadband connection for desktop
    deployments.
-   Mobile users should have reliable Wi-Fi or mobile data.
-   Remote access should use HTTPS.
-   Network bandwidth and latency targets must be established through
    testing under representative usage.

## 5. Browser and Operating System Compatibility

The project does not define an official browser support matrix. The
following are recommended testing targets rather than verified
compatibility claims:

-   Windows: current stable Chrome, Edge, and Firefox
-   macOS: current stable Safari, Chrome, and Firefox
-   Android: current stable Chrome
-   iPhone and iPad: current supported Safari; test Chrome separately
    where required

Legacy browsers should not be assumed to be supported. Browser versions
should remain within their security-support lifecycle.

QR scanning requires camera permission and a secure origin. PDF
downloads and printing should be verified separately in each target
browser.

## 6. Responsive Design Testing Requirements

The audit identified responsive CSS, a viewport meta tag,
mobile-specific styling, and responsive table wrappers in parts of the
application. These indicate responsive design intent, but do not prove
that the application works correctly on all screen sizes.

### 6.1 Required Viewport Test Matrix

    Viewport width Typical category                Result from initial audit
  ---------------- ------------------------------- ---------------------------
            320 px Narrow phone                    Not browser-tested
            360 px Phone                           Not browser-tested
            375 px Phone                           Not browser-tested
            390 px Phone                           Not browser-tested
            430 px Large phone                     Not browser-tested
            768 px Tablet breakpoint               Not browser-tested
            820 px Tablet                          Not browser-tested
           1024 px Tablet landscape/small laptop   Not browser-tested
           1280 px Desktop                         Not browser-tested
           1366 px Desktop                         Not browser-tested
           1440 px Desktop                         Not browser-tested
           1920 px Wide desktop                    Not browser-tested

### 6.2 Modules to Test

-   Shared navigation and sidebar
-   Dashboard cards and charts
-   Active and archived fire extinguisher inventory
-   Search, filters, dropdowns, and pagination
-   Inspection forms and QR scanning
-   Pending, approved, and rejected inspection queues
-   Expiring fire extinguisher module
-   Fire extinguisher history and timeline
-   Activity log and table scrolling
-   Inventory, inspection, and expiry PDF reports
-   Modals, confirmation dialogs, and maintenance forms

### 6.3 Responsive Acceptance Checklist

For each device and viewport, verify:

-   No unintended page-level horizontal overflow.
-   Navigation and sidebar open/close correctly.
-   Cards, tables, forms, filters, and buttons remain usable.
-   Table scrolling is contained within the intended table region.
-   Pagination and action buttons remain visible and accessible.
-   Modals and dropdowns are not clipped.
-   Text remains readable and important information is not truncated.
-   Keyboard focus is visible and modal focus behavior is correct.
-   Touch controls are comfortably usable.
-   Portrait and landscape orientation changes do not break the layout.
-   Mobile keyboard opening does not hide essential form controls.
-   QR scanning, permission denial, and manual fallback work.
-   PDF viewing, download, and printing behave as expected.

Every test result should record the device model, operating system,
browser version, viewport width, orientation, module, test steps,
pass/fail result, screenshot, and issue reference.

## 7. Performance and Scalability

The initial audit did not run browser timing tests, load tests, database
query plans, PDF benchmarks, or memory measurements. Current application
performance is therefore **not yet measured**.

### 7.1 Areas Requiring Measurement

-   Initial page load and navigation
-   Search, filters, and pagination with large datasets
-   Database queries for dashboards, inventory, inspections, and history
-   PDF generation duration and peak PHP memory usage
-   QR generation and camera scanning
-   CSS, JavaScript, and image transfer sizes
-   Slow-network behavior
-   Concurrent-user performance

### 7.2 Static Findings to Validate

-   The supplied database dump shows limited secondary-index coverage on
    main operational tables. Check the live schema and query plans
    before creating indexes.
-   Some filters wrap columns in expressions such as
    `LOWER(TRIM(column))`. These may prevent ordinary indexes from being
    used efficiently. Confirm with query plans before changing the
    queries or schema.
-   Pagination is present in some model paths, but deep offset
    pagination may become slower as datasets grow.
-   Dashboard code performs multiple metric queries; actual query count
    and impact were not measured.
-   The main stylesheet is large by line count. Measure compressed
    transfer size and caching before deciding whether to optimize it.
-   PDF generation performance depends on report size, server resources,
    and concurrency.

### 7.3 Proposed Performance Targets

The following are suggested targets to validate, not achieved results:

-   Initial authenticated page response: p75 under 2 seconds on the
    agreed office-network profile.
-   Common filter or paginated action: under 1 second of server time.
-   PDF report containing 1,000 rows: under 10 seconds and under 256 MB
    peak PHP memory.
-   Establish a separate agreed p95 response-time target before
    production deployment.

Measure these targets using representative hardware and realistic
datasets. Record p50/p75/p95 where applicable, HTTP timings, transferred
bytes, query plans, PDF duration, memory peak, and error rate.

### 7.4 Suggested Benchmark Procedure

1.  Use a staging copy, not production, and seed representative data.
2.  Test at a scale appropriate to the expected workload; the initial
    audit suggested starting with at least 10,000 extinguishers and
    100,000 inspection records if that scale is relevant.
3.  Measure common routes and queries using browser developer tools and
    server/database logs.
4.  Review query execution plans before adding or changing indexes.
5.  Generate PDFs at several sizes and record elapsed time, output size,
    and peak memory.
6.  Ramp concurrent users gradually and monitor web workers, database
    connections, slow queries, and errors.

## 8. Server and Hosting Requirements

The estimates below are initial planning guidance. Actual capacity
depends on concurrent users, transaction rate, data retention,
attachment volume, report size, and branch count.

  -----------------------------------------------------------------------
  Deployment type         Starting estimate       Notes
  ----------------------- ----------------------- -----------------------
  Local XAMPP development 2 CPU cores, 4 GB RAM,  PHP 8.2+ x64; keep
                          10 GB free SSD          development credentials
                                                  isolated from
                                                  production.

  Small internal          2--4 vCPU, 8 GB RAM,    Use a maintained web
  deployment              100 GB SSD              server/PHP setup,
                                                  HTTPS, a restricted
                                                  database account, and
                                                  scheduled backups.

  Multi-branch production 4--8 vCPU, 16 GB RAM,   Treat as a starting
                          SSD-backed database     estimate only; measure
                                                  workload and scale as
                                                  required. Consider
                                                  separate
                                                  application/database
                                                  hosting.
  -----------------------------------------------------------------------

### 8.1 PHP and Deployment Configuration

Suggested starting values for a modest deployment, subject to testing:

-   `memory_limit`: 256M
-   `max_execution_time`: 60 seconds for ordinary requests
-   `upload_max_filesize`: 10M if uploads are enabled
-   `post_max_size`: 12M if uploads are enabled

These are not universal requirements. Match settings to the
application's actual workflows and measured resource needs. Large PDF
jobs should use bounded exports or asynchronous processing rather than
relying only on higher global execution limits.

Production configuration should include:

-   `display_errors` disabled for public responses
-   Server-side error logging and log rotation
-   HTTPS and secure session cookies
-   Least-privilege database credentials
-   Restricted access to configuration and backup files
-   Scheduled backups and a tested restore procedure
-   Defined database and PHP versions verified against the application

The supplied SQL dump records MariaDB 10.4.32, but the minimum supported
database version is not declared. Test compatibility before selecting or
upgrading the production database version.

## 9. Security and Reliability Requirements

The initial static audit identified the following items for verification
and remediation.

  ---------------------------------------------------------------------------
  Priority                Finding                 Recommended action
  ----------------------- ----------------------- ---------------------------
  High                    Local database          Use protected
                          configuration uses a    environment/configuration
                          root account with a     values and a dedicated
                          blank password          least-privilege production
                                                  database account.

  High                    CSRF tokens were not    Verify all state-changing
                          found in the scanned    handlers; add and validate
                          first-party source      CSRF tokens if absent.
                          patterns                

  High                    Session ID regeneration Verify login handling and
                          was not found in the    regenerate the session ID
                          scanned authentication  after successful
                          paths                   authentication if missing.

  High                    Role and branch access  Test direct requests,
                          restrictions need       branch tampering, reports,
                          endpoint-level          archive/restore, and
                          verification            approval actions for each
                                                  role.

  High                    No verified backup and  Define backup frequency,
                          restore procedure was   retention, encryption,
                          found in the inspected  recovery objectives, and
                          artifacts               regularly test restores.

  Medium                  Secondary indexes may   Review live schema and
                          be insufficient for     query execution plans
                          large datasets          before adding indexes.

  Medium                  Output escaping may be  Review database and request
                          inconsistent across PHP values in HTML, attributes,
                          pages                   and JavaScript contexts.

  Medium                  Production logging and  Configure protected logs,
                          web-server settings     disable public error
                          were not verified       display, and monitor
                                                  failures.

  Medium                  Report endpoints        Validate server-side
                          require authorization   permissions and filters;
                          and resource-limit      bound export size and
                          checks                  benchmark memory/time.
  ---------------------------------------------------------------------------

These are static-review findings, not proof that a vulnerability can be
exploited. Security testing and penetration testing were not performed
in the initial audit.

## 10. Recommended Implementation Plan

### Phase 1 --- Security and Deployment Readiness

1.  Replace development database credentials for any non-local
    environment.
2.  Verify session regeneration and logout invalidation.
3.  Verify CSRF protection on all state-changing requests.
4.  Test role and branch authorization for pages, handlers, and report
    exports.
5.  Establish backup and recovery procedures.
6.  Verify PHP extensions, database compatibility, HTTPS, and production
    configuration.

### Phase 2 --- Cross-Device Testing

1.  Test all 12 viewport widths in Section 6.
2.  Test the major modules listed in Section 6.2.
3.  Record each defect with reproduction steps and screenshots.
4.  Fix confirmed overflow, clipping, inaccessible actions, and touch
    usability issues.
5.  Repeat regression tests on desktop, tablet, and mobile.

### Phase 3 --- Performance Testing

1.  Capture a baseline using representative data.
2.  Measure common pages, filters, dashboard metrics, and reports.
3.  Inspect query plans and add indexes only when justified.
4.  Optimize slow queries and repeated data retrieval.
5.  Measure PDF generation duration and peak memory.
6.  Test expected concurrent use and define capacity limits.

### Phase 4 --- Release Verification

1.  Confirm supported PHP/database/browser versions.
2.  Complete regression and authorization testing.
3.  Verify logs, backups, and restore procedure.
4.  Document known limitations and operational support procedures.
5.  Approve release only after agreed acceptance criteria pass.

## 11. Acceptance Criteria

The system should be considered ready for a defined deployment only
when:

-   The declared PHP version and required extensions are verified in the
    web-server environment.
-   Database compatibility is tested against the actual deployment
    version.
-   All required modules pass the agreed viewport and device test
    matrix.
-   No critical layout or usability defect blocks essential tasks.
-   QR scanning and its fallback behavior work on the target mobile
    devices over HTTPS.
-   Common page and filter performance meets agreed measured targets.
-   PDF reports complete within agreed time and memory limits at the
    expected data volume.
-   Authentication, session handling, CSRF protection, role checks, and
    branch restrictions are verified.
-   Backups and recovery have been successfully tested.
-   Known issues and supported browser/device combinations are
    documented.

## 12. Limitations of the Initial Audit

The initial audit was read-only and did not include:

-   Browser or physical-device testing
-   Automated viewport screenshots or accessibility testing
-   Live database connections, SQL execution, or query-plan analysis
-   HTTP load tests or concurrent-user testing
-   PDF runtime or peak-memory measurements
-   QR camera tests on physical devices
-   Penetration testing or exploit verification
-   Production hosting, TLS, PHP web-SAPI, or backup-restore inspection

As a result, responsive compatibility, actual performance, production
capacity, and production readiness remain unverified until the
corresponding tests are completed.

------------------------------------------------------------------------

**Document status:** Initial requirements and compatibility guidance.\
**Next action:** Perform browser-based responsive testing, security
verification, and performance benchmarking; update this document with
measured results and resolved issues.
