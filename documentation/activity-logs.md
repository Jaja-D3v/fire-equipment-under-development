# Activity Logs

## Overview
activity-log.php displays activity history using ActivityLogController.php and ActivityLogModel.php. It requires a signed-in session and includes shared navigation and session timeout handling.

## Data and filters
The Model inserts rows through addActivityLog() and retrieves paginated results with getActivityLogs(), along with a matching count from getActivityLogsCount(). The page reads page/date parameters and presents the activity count, date filtering, records, and pagination. The SQL source defines activity_logs_tbl with user_name, action, description, and created_at fields.

## Use
1. Open Activity Logs from the navigation.
2. Select the available date/month filter.
3. Review activity, details, user, and timestamp fields.
4. Use pagination to inspect older records.

## Limitations
The source does not establish that every business action is logged. No live activity data was queried. The UI's current design does not change the logging schema or retention policy.
