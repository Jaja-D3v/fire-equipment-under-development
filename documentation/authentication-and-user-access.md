# Authentication and User Access

## Overview
authentication/login.php is the current login page;  authentication/system-selector.php provides system selection. backend/authentication contains session utilities and logout.

## Login flow
The login form posts to backend/controller/AuthController.php. js/login.js supports browser-side login behavior. AuthController.php calls AuthModel.php functions to retrieve/validate user information. Authentication code uses backend/config/db_user.php and the separate kp201 account source; EMS operational data uses backend/config/db.php.

## Session access
Application pages commonly require backend/authentication/SessionChecker.php. backend/authentication/Auth.php contains session/auth helpers; Logout.php handles logout. Shared session_timeout.php provides timeout warning and activity behavior. System selector and login behavior depend on session role and branch values.

## User management status
This repository does not expose a clearly identified EMS user-management CRUD page/Model. Account records are sourced from the separate employee/system database integration. Do not describe a full user administration module based solely on authentication files.

## Limitations
Credentials and hashes are intentionally excluded. Authentication was not tested, and account provisioning, password storage policy, and deployed role mappings require review of the live identity system.
