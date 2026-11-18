
(() => {
    'use strict';

    /*
     * MAIN CONFIGURATION
     *
     * This section initializes the main elements and variables
     * required by the Audit & Reports Module.
     *
     * The entire script is wrapped inside an Immediately Invoked
     * Function Expression (IIFE) to prevent its variables and
     * functions from interfering with other JavaScript scripts
     * running on the same page.
     */

    // PHP controller responsible for processing audit report requests.
    const endpoint = 'backend/controller/AuditReportsController.php';

    // Retrieves the audit report form containing the equipment,
    // year, and branch selection fields.
    const form = document.getElementById('audit-form');

    // Retrieves the container holding the form fields.
    // This is used to temporarily disable user inputs while
    // the report is being generated.
    const fields = document.getElementById('audit-fields');

    // Retrieves the alert/message container used to display
    // success messages, errors, and report generation status.
    const message = document.getElementById('audit-message');

    // Retrieves the PDF preview element where the generated
    // audit report will be displayed.
    const preview = document.getElementById('audit-preview');

    // Stores the temporary browser URL of the generated PDF.
    // This URL is used for previewing, opening, and downloading
    // the report without requesting another copy from the server.
    let pdfUrl = null;

    // Stores the default options retrieved from the PHP controller,
    // including available equipment types, report years, and branches.
    let defaults = null;


    /*
     * FUNCTION: showMessage()
     *
     * PURPOSE:
     * Displays notification messages to the user through the
     * audit message container.
     *
     * PARAMETERS:
     * text - The message that will be displayed.
     * kind - The Bootstrap alert type used to determine the
     *        appearance of the message.
     *
     * DEFAULT VALUE:
     * 'danger' - Used when an error message is displayed.
     *
     * HOW IT WORKS:
     * 1. Updates the text content of the message container.
     * 2. Changes its CSS classes based on the specified alert type.
     *
     * EXAMPLE:
     * showMessage('Report ready.', 'success');
     * showMessage('Unable to generate report.', 'danger');
     *
     * COMMON ALERT TYPES:
     * success - Successful operation.
     * danger  - Error or failed operation.
     * info    - Informational message or process status.
     */
    function showMessage(text, kind = 'danger') {
        message.textContent = text;
        message.className = 'alert alert-' + kind;
    }


    /*
     * FUNCTION: clearReport()
     *
     * PURPOSE:
     * Clears the previously generated report and resets the
     * report preview, summary, and action buttons.
     *
     * WHEN IT IS USED:
     * - Before generating a new report.
     * - When the user changes any report filter.
     * - When report generation encounters an error.
     * - When the form is reset.
     *
     * HOW IT WORKS:
     *
     * 1. Hides the PDF preview by adding the 'd-none' class.
     *
     * 2. Removes the current PDF source from the preview element
     *    to prevent the previous report from remaining visible.
     *
     * 3. Displays the empty-state placeholders for the preview
     *    and report summary.
     *
     * 4. Hides the report summary and action buttons because
     *    there is currently no valid generated report to display.
     *
     * 5. Removes the href attributes from the download and open
     *    links so users cannot accidentally access an outdated PDF.
     *
     * 6. Revokes the previous PDF object URL if one exists.
     *    This releases the browser resource associated with
     *    the previously generated PDF.
     *
     * 7. Sets pdfUrl back to null to indicate that there is
     *    currently no active PDF preview URL.
     *
     * IMPORTANT:
     * This function only resets the report display.
     * It does not delete or modify any records in the database.
     */
    function clearReport() {
        preview.classList.add('d-none');
        preview.removeAttribute('src');
        document.getElementById('audit-preview-empty').classList.remove('d-none');
        document.getElementById('audit-summary-empty').classList.remove('d-none');
        document.getElementById('audit-summary').classList.add('d-none');
        document.getElementById('audit-actions').classList.add('d-none');
        ['audit-download', 'audit-open'].forEach(id => document.getElementById(id).removeAttribute('href'));
        if (pdfUrl) URL.revokeObjectURL(pdfUrl);
        pdfUrl = null;
    }


    /*
     * ASYNC FUNCTION: request()
     *
     * PURPOSE:
     * Sends an HTTP request to the PHP AuditReportsController
     * and retrieves the requested report data or available
     * form options from the backend.
     *
     * PARAMETER:
     * params - An object containing the request parameters
     *          that will be sent to the PHP controller.
     *
     * HOW IT WORKS:
     *
     * 1. Uses fetch() to send a GET request to the PHP controller.
     *
     * 2. URLSearchParams converts the supplied parameters into
     *    URL query string format.
     *
     * 3. cache: 'no-store' prevents the browser from using a
     *    cached response. This helps ensure that the report
     *    request uses fresh data.
     *
     * 4. credentials: 'same-origin' allows the request to include
     *    same-origin credentials, such as the administrator's
     *    existing session cookies.
     *
     * 5. Checks whether the request was redirected or whether
     *    the server did not return an application/json response.
     *    This helps identify expired sessions or unavailable access.
     *
     * 6. Converts the server response into a JavaScript JSON object.
     *
     * 7. Checks the HTTP response status and the success property
     *    returned by the PHP controller.
     *
     * 8. Throws an error if the request fails or the controller
     *    reports an unsuccessful operation.
     *
     * 9. Returns the processed JSON result when the request succeeds.
     *
     * IMPORTANT:
     * The function is asynchronous because it waits for the
     * server response before continuing the report generation
     * or form initialization process.
     *
     * This function is reused for two operations:
     * - action: 'options'   - Retrieves available form options.
     * - action: 'generate' - Requests the generated audit report.
     */
    async function request(params) {
        const response = await fetch(endpoint + '?' + new URLSearchParams(params), {cache: 'no-store', credentials: 'same-origin'});

        if (response.redirected || !response.headers.get('content-type')?.includes('application/json')) {
            throw new Error('Your session has expired or access is unavailable. Please sign in as an administrator.');
        }

        const result = await response.json();

        if (!response.ok || !result.success) throw new Error(result.message || 'Unable to load report.');

        return result;
    }


    /*
     * FUNCTION: addOption()
     *
     * PURPOSE:
     * Dynamically creates and inserts an option into a
     * specified HTML select/dropdown element.
     *
     * PARAMETERS:
     * id       - The ID of the select element where the option
     *            will be inserted.
     *
     * value    - The actual value assigned to the option.
     *            This is the value submitted when the form
     *            is processed.
     *
     * label    - The text displayed to the user in the dropdown.
     *
     * disabled - Determines whether the option can be selected.
     *            Its default value is false.
     *
     * HOW IT WORKS:
     *
     * 1. Creates a new HTML Option object using the supplied
     *    label and value.
     *
     * 2. Sets the disabled property based on the supplied parameter.
     *    This is useful for equipment types that are currently
     *    unavailable.
     *
     * 3. Finds the target dropdown using its element ID.
     *
     * 4. Inserts the newly created option into that dropdown.
     *
     * EXAMPLE:
     * addOption('audit-equipment', 'fire-extinguisher',
     *           'Fire Extinguisher');
     *
     * This creates a dropdown option with the displayed text
     * 'Fire Extinguisher' and the submitted value
     * 'fire-extinguisher'.
     *
     * IMPORTANT:
     * This function avoids manually writing each dropdown
     * option in HTML because the options are retrieved
     * dynamically from the PHP controller.
     */
    function addOption(id, value, label, disabled = false) {
        const option = new Option(label, value);
        option.disabled = disabled;
        document.getElementById(id).add(option);
    }


    /*
     * EVENT LISTENER: FORM SUBMISSION
     *
     * PURPOSE:
     * Handles the generation of the annual safety equipment
     * inspection monitoring report when the administrator
     * submits the audit report form.
     *
     * HOW IT WORKS:
     *
     * 1. Prevents the browser's default form submission behavior.
     *    This prevents a normal page reload.
     *
     * 2. Collects all form values using FormData and converts
     *    them into a JavaScript object.
     *
     * 3. Disables the report fields to prevent the administrator
     *    from changing the selected filters while the report
     *    is being generated.
     *
     * 4. Calls clearReport() to remove any previously generated
     *    report before processing the new request.
     *
     * 5. Displays a loading message to inform the administrator
     *    that report generation is in progress.
     *
     * 6. Adds the aria-busy attribute to indicate that the form
     *    is currently processing a request.
     *
     * 7. Sends the selected equipment, year, and branch to the
     *    PHP controller using the request() function.
     *
     * 8. Processes the PDF data returned by the backend.
     *
     * 9. Displays the generated PDF preview and report summary.
     *
     * 10. Enables the open and download actions.
     *
     * 11. Displays the appropriate success or informational message.
     *
     * ERROR HANDLING:
     * If an error occurs during report generation, the catch
     * block clears the report and displays the error message.
     *
     * CLEANUP:
     * The finally block restores the form fields and removes
     * the loading indicator regardless of whether the request
     * succeeds or fails.
     */
    form.addEventListener('submit', async event => {

        // Prevent the default browser behavior of submitting
        // the form and refreshing the entire page.
        event.preventDefault();

        // Collect the selected form values and convert them
        // into an object that can be passed to the PHP controller.
        const params = Object.fromEntries(new FormData(form));

        // Temporarily disable the form fields to prevent changes
        // while the report generation request is running.
        fields.disabled = true;

        // Remove any previous report preview, summary, and PDF links.
        clearReport();

        // Inform the administrator that the report is being generated.
        showMessage('Generating your report…', 'info');

        // Indicates that the form is currently processing a request.
        form.setAttribute('aria-busy', 'true');

        try {

            /*
             * REQUEST REPORT GENERATION
             *
             * Sends the selected equipment, year, and branch
             * together with the generate action to the PHP controller.
             *
             * The returned result contains the PDF data,
             * report summary, and suggested filename.
             */
            const result = await request({action: 'generate', ...params});


            /*
             * CONVERT BASE64 DATA INTO PDF
             *
             * The PHP controller returns the generated PDF
             * as a Base64-encoded string.
             *
             * atob() decodes the Base64 string into binary characters.
             *
             * Uint8Array.from() converts those characters into
             * numerical byte values required to create a PDF Blob.
             *
             * The character => character.charCodeAt(0) callback
             * converts each decoded character into its corresponding
             * character code.
             */
            const bytes = Uint8Array.from(atob(result.pdf), character => character.charCodeAt(0));


            /*
             * CREATE PDF OBJECT URL
             *
             * Blob creates a browser object containing the
             * generated PDF data.
             *
             * URL.createObjectURL() creates a temporary browser URL
             * that can be used to display, open, and download the PDF.
             *
             * The generated URL is stored in pdfUrl for reuse
             * throughout the current report session.
             */
            pdfUrl = URL.createObjectURL(new Blob([bytes], {type: 'application/pdf'}));


            /*
             * DISPLAY PDF PREVIEW
             *
             * Assigns the generated PDF URL to the preview element.
             *
             * Removes the 'd-none' class to make the PDF preview visible.
             *
             * Hides the empty preview placeholder because an actual
             * generated report is now available.
             */
            preview.src = pdfUrl;
            preview.classList.remove('d-none');
            document.getElementById('audit-preview-empty').classList.add('d-none');


            /*
             * UPDATE REPORT SUMMARY
             *
             * Retrieves the report summary returned by the PHP controller.
             *
             * The report object contains information such as:
             * - Equipment type
             * - Selected report year
             * - Selected branch
             * - Total registered equipment
             * - Completed inspections
             * - Missing inspections
             *
             * These values are assigned to the summary object
             * for easier updating of the corresponding HTML elements.
             */
            const report = result.report;

            const values = {equipment: report.equipment, year: report.year, branch: report.branch,
                total: report.total_equipment, completed: report.completed, missing: report.missing};


            /*
             * DISPLAY SUMMARY VALUES
             *
             * Object.entries() converts the summary object into
             * an array of key-value pairs.
             *
             * forEach() processes each pair individually.
             *
             * The key is used to locate the corresponding HTML
             * summary element using the 'summary-' prefix.
             *
             * The value is inserted as text content into that element.
             *
             * Example:
             * summary-total     = Total registered equipment
             * summary-completed = Completed inspections
             * summary-missing   = Missing inspections
             *
             * This allows the report summary to be updated dynamically
             * without manually writing each value separately.
             */
            Object.entries(values).forEach(([key, value]) => {
                document.getElementById('summary-' + key).textContent = value;
            });


            /*
             * SHOW REPORT SUMMARY AND ACTION BUTTONS
             *
             * Hides the empty summary placeholder because report
             * information is already available.
             *
             * Displays the actual report summary section.
             *
             * Displays the report action buttons, allowing the
             * administrator to open or download the generated PDF.
             */
            document.getElementById('audit-summary-empty').classList.add('d-none');
            document.getElementById('audit-summary').classList.remove('d-none');
            document.getElementById('audit-actions').classList.remove('d-none');


            /*
             * CONFIGURE OPEN AND DOWNLOAD LINKS
             *
             * The audit-open link receives the temporary PDF URL.
             * This allows the administrator to open the generated
             * report using the browser's PDF viewer.
             *
             * The audit-download link receives the same PDF URL.
             * Its download property uses the filename returned
             * by the PHP controller.
             *
             * Both actions use the same generated PDF file.
             */
            document.getElementById('audit-open').href = pdfUrl;

            const download = document.getElementById('audit-download');

            download.href = pdfUrl;
            download.download = result.filename;


            /*
             * DISPLAY FINAL REPORT STATUS
             *
             * Checks the total number of equipment records
             * included in the generated report.
             *
             * If equipment records exist:
             * Displays a success message confirming that the
             * report is ready for preview and download.
             *
             * If no equipment records exist:
             * Displays an informational message explaining that
             * no active equipment matches the selected branch.
             * An empty report is still made available.
             */
            showMessage(report.total_equipment ? 'Report ready. Preview and download contain the same report.'
                : 'No active equipment matches this branch. An empty report is available.', report.total_equipment ? 'success' : 'info');

        } catch (error) {

            /*
             * ERROR HANDLING
             *
             * Executes when an error occurs during the report
             * generation process.
             *
             * First, clearReport() removes any incomplete or
             * previously displayed report.
             *
             * Then, showMessage() displays the error message
             * returned by the request or generated by JavaScript.
             *
             * This ensures that an unsuccessful report request
             * does not leave an outdated PDF preview visible.
             */
            clearReport();
            showMessage(error.message);

        } finally {

            /*
             * FINAL CLEANUP
             *
             * This block executes regardless of whether the
             * report generation process succeeds or fails.
             *
             * 1. Re-enables the report fields so the administrator
             *    can generate another report or change the filters.
             *
             * 2. Removes the aria-busy attribute because the
             *    form is no longer processing a request.
             *
             * This prevents the form from remaining disabled
             * after the request has finished.
             */
            fields.disabled = false;
            form.removeAttribute('aria-busy');
        }
    });


    /*
     * EVENT LISTENER: FORM CHANGE
     *
     * PURPOSE:
     * Detects changes made by the administrator to any form field.
     *
     * WHEN IT IS TRIGGERED:
     * Whenever the equipment type, report year, or branch selection
     * is changed.
     *
     * HOW IT WORKS:
     *
     * 1. Calls clearReport() to remove the previously generated
     *    report because its filters may no longer match the
     *    administrator's current selections.
     *
     * 2. Adds the 'd-none' class to the message container
     *    to hide the previous notification.
     *
     * IMPORTANT:
     * This prevents the administrator from mistakenly viewing
     * or downloading a report generated using previous filters.
     *
     * The report is not automatically regenerated.
     * The administrator must submit the form again.
     */
    form.addEventListener('change', () => {
        clearReport();
        message.classList.add('d-none');
    });


    /*
     * EVENT LISTENER: FORM RESET
     *
     * PURPOSE:
     * Handles the reset action and restores the predefined
     * default selections of the Audit & Reports form.
     *
     * HOW IT WORKS:
     *
     * 1. Prevents the browser's default reset behavior.
     *    This allows the application to control exactly which
     *    values should be restored.
     *
     * 2. Checks whether the default options have already been
     *    retrieved from the PHP controller.
     *
     * 3. If defaults are not yet available, the function stops
     *    to avoid accessing undefined default values.
     *
     * 4. Restores the equipment selection to Fire Extinguisher.
     *
     * 5. Restores the report year to the current year provided
     *    by the PHP controller.
     *
     * 6. Restores the branch selection to '__all__',
     *    representing All Branches.
     *
     * 7. Clears the previous PDF preview, report summary,
     *    and action links.
     *
     * 8. Hides any previously displayed notification message.
     *
     * IMPORTANT:
     * This resets the form selections and report display only.
     * It does not reset or delete database records.
     */
    form.addEventListener('reset', event => {

        // Prevent the browser from automatically resetting
        // the form fields to their original HTML values.
        event.preventDefault();

        // Stop the reset operation if the default options
        // have not yet been loaded from the backend.
        if (!defaults) return;

        // Restore the default equipment type.
        document.getElementById('audit-equipment').value = 'fire-extinguisher';

        // Restore the report year to the current year
        // supplied by the PHP controller.
        document.getElementById('audit-year').value = defaults.current_year;

        // Restore the branch selection to All Branches.
        document.getElementById('audit-branch').value = '__all__';

        // Remove the previously generated report and its summary.
        clearReport();

        // Hide any previous notification message.
        message.classList.add('d-none');
    });


    /*
     * INITIALIZATION: LOAD FORM OPTIONS
     *
     * PURPOSE:
     * Retrieves the available dropdown options from the
     * PHP AuditReportsController when the page is initialized.
     *
     * REQUEST:
     * action: 'options'
     *
     * The backend provides the following information:
     * - Available equipment types.
     * - Available report years.
     * - Registered branches.
     * - Current year.
     *
     * HOW IT WORKS:
     *
     * 1. Calls request() to retrieve the available form options.
     *
     * 2. Stores the returned options in the defaults variable.
     *    This information is also used by the reset event listener.
     *
     * 3. Loops through the available equipment types and adds
     *    each one to the equipment dropdown.
     *
     * 4. Uses item.available to determine whether an equipment
     *    option should be enabled or disabled.
     *
     * 5. Loops through the available report years and inserts
     *    them into the year dropdown.
     *
     * 6. Adds the All Branches option to the branch dropdown.
     *
     * 7. Loops through the registered branches and inserts
     *    each branch into the dropdown.
     *
     * 8. Enables the form fields after all available options
     *    have been successfully loaded.
     *
     * IMPORTANT:
     * This initialization process ensures that the dropdown
     * options are based on the data provided by the backend
     * rather than manually maintained static HTML options.
     */


    /*
     * CALLBACK: defaults.equipment.forEach()
     *
     * Processes each equipment type returned by the PHP controller.
     *
     * item.value     - The equipment identifier submitted to the backend.
     * item.label     - The equipment name displayed in the dropdown.
     * item.available - Determines whether the equipment type
     *                  can currently be selected.
     *
     * Each equipment type is added using addOption().
     */
    request({action: 'options'}).then(result => {

        // Store the backend-provided default options for later use.
        defaults = result.options;

        // Populate the equipment dropdown with available equipment types.
        defaults.equipment.forEach(item => addOption('audit-equipment', item.value, item.label, !item.available));


        /*
         * CALLBACK: defaults.years.forEach()
         *
         * Processes each report year returned by the backend.
         *
         * Each year is added to the year dropdown.
         * The year itself is used as both the option value
         * and the displayed label.
         */
        defaults.years.forEach(year => addOption('audit-year', year, year));


        /*
         * ADD ALL BRANCHES OPTION
         *
         * Inserts a special dropdown option representing
         * all registered branches.
         *
         * The value '__all__' acts as the identifier for
         * the All Branches selection.
         */
        addOption('audit-branch', '__all__', 'All Branches');


        /*
         * CALLBACK: defaults.branches.forEach()
         *
         * Processes the list of registered branches returned
         * by the PHP controller.
         *
         * Each branch is inserted into the branch dropdown.
         * The branch name is used as both its submitted value
         * and its displayed label.
         */
        defaults.branches.forEach(branch => addOption('audit-branch', branch, branch));


        // Enables the report form after all dropdown options
        // have been successfully retrieved and populated.
        fields.disabled = false;

    }).catch(error => {

        /*
         * ERROR HANDLING: INITIALIZATION FAILURE
         *
         * Executes when the request for available form options
         * fails during page initialization.
         *
         * Possible causes include:
         * - Expired administrator session.
         * - Insufficient access permissions.
         * - PHP controller error.
         * - Server or network request failure.
         *
         * Displays the error message so the administrator
         * knows why the report form could not be initialized.
         */
        showMessage(error.message);

    });


    /*
     * EVENT LISTENER: PAGE HIDE
     *
     * PURPOSE:
     * Handles the cleanup of the temporary PDF object URL
     * when the administrator leaves the current page.
     *
     * EVENT:
     * pagehide - Triggered when the browser hides or unloads
     *            the current page.
     *
     * HOW IT WORKS:
     *
     * 1. Checks event.persisted to determine whether the page
     *    is being preserved in the browser's back-forward cache.
     *
     * 2. If the page is not being preserved and a PDF object
     *    URL exists, URL.revokeObjectURL() releases the resource.
     *
     * WHY THIS IS IMPORTANT:
     * PDF object URLs are temporary browser resources.
     * Revoking the URL when it is no longer needed helps
     * prevent unnecessary resource usage.
     *
     * IMPORTANT:
     * The event.persisted condition prevents revoking the
     * PDF URL when the page is being preserved for a possible
     * return through the browser's back-forward navigation.
     */
    window.addEventListener('pagehide', event => {
        if (!event.persisted && pdfUrl) URL.revokeObjectURL(pdfUrl);
    });

})();
