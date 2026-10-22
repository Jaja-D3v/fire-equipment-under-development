# EMS Project Instructions for ChatGPT-Assisted Development

## Project purpose
This is the IMS Safety Management System / fire equipment management system. Confirm the current repository structure and actual implementation before changing code.

## Technology and architecture
- Use procedural PHP; do not introduce OOP, classes, or a framework unless the project owner explicitly approves.
- The stack may include PHP, MySQL/MariaDB, mysqli, JavaScript, HTML, CSS, and Bootstrap.
- Keep Model and Controller responsibilities separate, following the project's existing conventions.
- Preserve the current folder structure and naming conventions. Do not assume folder or file names without inspecting the repository.

## Database rules
- Verify the actual schema, column names, data types, indexes, and relationships before writing SQL.
- Use mysqli prepared statements for user-provided values.
- Check that bind_param type strings, placeholders, and variables match exactly.
- Preserve existing access controls, role restrictions, branch restrictions, and archive filters where applicable.
- Do not invent columns or assume a migration has already been applied.
- Never run destructive SQL, delete production data, or reset a database without explicit approval.

## Forms, filters, and pagination
- Use POST for create, update, and delete form submissions, following existing routing conventions.
- Validate input server-side. Preserve or improve CSRF protection where the app supports it.
- Keep search, date, status, and branch filters consistent between the UI, query, and pagination.
- Reset pagination to page 1 when a filter or search term changes.
- Follow existing pagination conventions and avoid changing unrelated modules.

## UI
- Use Bootstrap and the project's existing design patterns.
- Ensure layouts work on mobile, tablet, and desktop.
- Avoid unnecessary redesigns while fixing functional bugs.
- Keep the current light, clean visual style unless asked otherwise.

## Debugging workflow
1. Read the relevant memory and project instructions.
2. Inspect the relevant controller, model, view, JavaScript, and schema before proposing a fix.
3. State the likely root cause and evidence.
4. Propose the smallest safe change.
5. Identify all affected files and any SQL/schema changes.
6. Explain how to test the change.
7. Do not claim that code was run or tested unless the user confirms it or provides test output.
8. After the user confirms a fix, update PROJECT_MEMORY.md and DEBUG_LOG.md with verified facts only.

## Security and privacy
- Never ask the user to share passwords, API keys, session cookies, or production credentials.
- Redact secrets from logs and screenshots.
- Ask before destructive operations or broad refactors.
- Treat code and database changes as unverified until tested.

## Response format for coding help
When helping with a bug, provide:
1. Root cause
2. Exact file(s) to change
3. Complete replacement code or a clearly located patch
4. Explanation of important changes
5. Test steps
6. Memory update suggestion after the test result is known


## Required Model–Controller–Frontend Architecture

The existing EMS project uses simple procedural PHP functions. Preserve this architecture exactly. Do not introduce OOP, classes, repositories, services, dependency injection, or unnecessary architectural layers.

### 1. Model
- All SQL queries and database operations belong in Model functions.
- Model functions execute queries, fetch results, and return the data or an appropriate failure result.
- Use prepared statements whenever parameters are supplied by users or requests.
- Verify the actual database schema before writing or changing queries.
- Follow the existing database connection and error-handling conventions.

Example naming pattern:
- `getAllfireExtinguisherModel()`
- `getFireExtinguisherByIdModel()`

### 2. Controller
- Controller functions call the corresponding Model functions.
- For simple data retrieval, the Controller should return the Model result directly when no additional processing is needed.
- Keep request handling, input validation, authorization checks, and application flow in the appropriate existing Controller functions.
- Do not duplicate SQL queries inside Controllers.
- Use distinct function names to avoid collisions with Model functions.

Example naming pattern:
- `getAllfireExtinguisherController()`
- `getFireExtinguisherByIdController()`

### 3. Frontend
- Frontend PHP pages call Controller functions to obtain data.
- The frontend displays the returned data and handles presentation.
- Do not write database queries directly in frontend pages when the existing architecture provides a Model and Controller for that operation.

### 4. Required flow

Frontend → Controller → Model → Database

The Model returns the result to the Controller, and the Controller returns it to the frontend.

### 5. When modifying existing code
- Inspect the existing Model, Controller, and frontend functions first.
- Reuse existing functions and naming conventions where appropriate.
- Do not rename functions or reorganize folders without a clear reason and explicit approval.
- When adding a feature, follow the same pattern as similar existing features.
- Keep changes minimal and explain which layer each change belongs to.
- If existing code differs from these rules, report the discrepancy before performing a broad refactor.