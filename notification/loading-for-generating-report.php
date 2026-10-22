<script>
    function generateReport(event, reportUrl) {
        event.preventDefault();

        const reportTab = window.open('', '_blank');

        if (!reportTab) {
            window.location.href = reportUrl;
            return;
        }

        reportTab.document.open();
        reportTab.document.write(`
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Generating Report</title>
             <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

            <style>
                * {
                    box-sizing: border-box;
                }

                body {
                    margin: 0;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #f8fafc;
                    font-family: Arial, sans-serif;
                }

                .loading-overlay {
                    position: fixed;
                    inset: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: rgba(15, 23, 42, 0.35);
                    backdrop-filter: blur(8px);
                    -webkit-backdrop-filter: blur(8px);
                }

                .loading-popup {
                    width: min(380px, calc(100% - 40px));
                    padding: 34px 30px;
                    text-align: center;
                    background: rgba(255, 255, 255, 0.94);
                    border: 1px solid rgba(255, 255, 255, 0.8);
                    border-radius: 26px;
                    box-shadow: 0 30px 80px rgba(15, 23, 42, 0.20);
                }

                .loading-icon {
                    width: 68px;
                    height: 68px;
                    margin: 0 auto 18px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 50%;
                    background: #dbeafe;
                    color: #2563eb;
                    font-size: 30px;
                    font-weight: bold;
                }

                .loading-popup h4 {
                    margin: 0 0 7px;
                    color: #172033;
                    font-size: 21px;
                    font-weight: 700;
                }

                .loading-popup p {
                    margin: 0 0 20px;
                    color: #64748b;
                    font-size: 14px;
                }

                .loader {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 9px;
                    color: #64748b;
                    font-size: 13px;
                }

                .spinner {
                    width: 15px;
                    height: 15px;
                    border: 2px solid #dbeafe;
                    border-top-color: #2563eb;
                    border-radius: 50%;
                    animation: spin 0.8s linear infinite;
                }

                @keyframes spin {
                    to {
                        transform: rotate(360deg);
                    }
                }
            </style>
        </head>

        <body>
            <div class="loading-overlay">
                <div class="loading-popup">

                    <div class="loading-icon">
                        <i class="fa-regular fa-file-pdf"></i>
                    </div>

                    <h4>Generating Report</h4>

                    <p>
                        Please wait while the report is being prepared.
                    </p>

                    <div class="loader">
                        <div class="spinner"></div>
                        <span>Opening report...</span>
                    </div>

                </div>
            </div>
        </body>
        </html>
    `);

        reportTab.document.close();

        setTimeout(function() {
            reportTab.location.href = reportUrl;
        }, 1000);
    }
</script>