# ChatGPT Go + VS Code EMS Memory Starter Pack

## What these files do
- `AGENTS.md`: coding rules and expected debugging workflow.
- `PROJECT_MEMORY.md`: project overview, known rules, historical concerns, and current progress.
- `DEBUG_LOG.md`: structured record of bugs and verified fixes.

These files do not automatically connect ChatGPT to VS Code. You must attach/paste relevant file contents into ChatGPT or use supported file-upload functionality.

## Installation
1. Extract this ZIP.
2. Open your existing EMS project folder in VS Code.
3. Copy `AGENTS.md`, `PROJECT_MEMORY.md`, and `DEBUG_LOG.md` into the project root.
4. Do not overwrite existing files with the same names. If they already exist, compare and merge the content first.
5. Open each file and adjust any detail that no longer matches the project.

## Start a ChatGPT coding session
1. Open `AGENTS.md` and `PROJECT_MEMORY.md` in VS Code.
2. Attach these files to the ChatGPT conversation if file upload is available, or paste their contents.
3. Provide the relevant source files and exact error output.
4. Ask ChatGPT to inspect first, explain the root cause, then propose a minimal safe fix.
5. Apply the fix in VS Code.
6. Run the relevant checks and test the feature manually.
7. Tell ChatGPT the actual test result.
8. Update `PROJECT_MEMORY.md` and `DEBUG_LOG.md` with verified outcomes.

## Suggested first prompt
Read the attached `AGENTS.md` and `PROJECT_MEMORY.md` first. Treat them as project context, but verify all assumptions against the source files I provide. I use procedural PHP, MySQL/MariaDB, mysqli, JavaScript, and Bootstrap. Do not introduce OOP/classes. Before proposing a fix, explain the root cause and identify the exact files involved. Do not claim a test passed unless I provide the result. After I confirm testing, help me update the memory files.

## Important safety notes
- Do not include passwords, API keys, session cookies, or production credentials in shared files or chat.
- Keep a backup or use Git before major changes.
- Review all generated code before applying it.
