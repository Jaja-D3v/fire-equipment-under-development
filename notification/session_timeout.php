<div
    class="modal fade"
    id="sessionTimeoutModal"
    tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-4 p-md-5">
                <div
                    class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning"
                    style="width: 70px; height: 70px;">
                    <i class="bi bi-clock-history fs-2"></i>
                </div>

                <h4 class="fw-bold mb-2">Are you still here?</h4>

                <p class="text-body-secondary mb-4">
                    Your session has been inactive for 4 minutes.
                    Would you like to continue your session?
                </p>

                <div class="mb-4">
                    <span id="sessionCountdown" class="fs-1 fw-bold text-danger">60</span>
                    <div class="small text-body-secondary">seconds remaining</div>
                </div>

                <button
                    type="button"
                    class="btn btn-primary px-4"
                    onclick="extendSession()">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Extend Session
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        'use strict';

     
        // CONFIGURATION
        const INACTIVITY_LIMIT = 4 * 60 * 1000; 
        const WARNING_TIME = 60; 
        const WARNING_MS = WARNING_TIME * 1000;

        const HEARTBEAT_INTERVAL = 60 * 1000;
        const FETCH_TIMEOUT = 10 * 1000; 

        const AUTH_URL = './backend/authentication/Auth.php';
        const LOGOUT_URL = './backend/authentication/logout.php';
        const LOGIN_URL = './authentication/login.php';

        const ACTIVITY_KEY = 'ems_last_activity';
        const LOGOUT_KEY = 'ems_logout';

        let inactivityTimer = null;
        let countdownTimer = null;
        let heartbeatTimer = null;
        let activityThrottle = null;

        let lastUserActivity = Date.now();

        let warningActive = false;
        let loggingOut = false;
        let extending = false;

        // LOCAL STORAGE HELPERS

        function storageGet(key) {
            try {
                return localStorage.getItem(key);
            } catch (e) {
                return null;
            }
        }

        function storageSet(key, value) {
            try {
                localStorage.setItem(key, String(value));
            } catch (e) {
                
            }
        }

        function storageRemove(key) {
            try {
                localStorage.removeItem(key);
            } catch (e) {
               
            }
        }

        // MODAL HELPERS
        function isModalOpen() {
            return warningActive;
        }

        function getModalElements() {

            const modalElement =
                document.getElementById('sessionTimeoutModal');

            const countdownElement =
                document.getElementById('sessionCountdown');

            if (!modalElement || !countdownElement) {
                return null;
            }

            return {
                modalElement,
                countdownElement
            };
        }

        // BACKEND AUTH CHECK

        function pingBackend() {

            const controller = new AbortController();

            const timeoutId = setTimeout(function() {
                controller.abort();
            }, FETCH_TIMEOUT);

            return fetch(AUTH_URL, {
                    method: 'GET',
                    credentials: 'same-origin',
                    cache: 'no-store',
                    signal: controller.signal,
                    headers: {
                        'Cache-Control': 'no-cache, no-store, must-revalidate',
                        'Pragma': 'no-cache'
                    }
                })

                .then(function(response) {

                    if (response.status === 401 ||
                        response.status === 403) {

                        throw new Error('EXPIRED');
                    }

                    if (!response.ok) {
                        throw new Error('TRANSIENT');
                    }

                    return response.json();

                })

                .catch(function(error) {

                    if (error && error.message === 'EXPIRED') {
                        throw error;
                    }

                    throw new Error('TRANSIENT');

                })

                .finally(function() {
                    clearTimeout(timeoutId);
                });
        }

        // INACTIVITY

        function getIdleTime() {
            return Date.now() - lastUserActivity;
        }

        function resetInactivityTimer() {

            clearTimeout(inactivityTimer);

            const remaining =
                Math.max(
                    0,
                    INACTIVITY_LIMIT - getIdleTime()
                );

            inactivityTimer = setTimeout(
                showSessionWarning,
                remaining
            );
        }

        // SAVE USER ACTIVITY

        function saveUserActivity() {

            if (loggingOut) {
                return;
            }

            if (isModalOpen()) {
                return;
            }

            const now = Date.now();

            lastUserActivity = now;

            // Save immediately to localStorage
            storageSet(ACTIVITY_KEY, now);

            // Reset frontend inactivity timer
            resetInactivityTimer();

            console.log(
                '[SESSION TIMEOUT] 👆 Activity detected:',
                new Date(now).toLocaleTimeString()
            );
        }

        // USER ACTIVITY DETECTION

        const activityEvents = [
            'mousedown',
            'mousemove',
            'keydown',
            'scroll',
            'wheel',
            'touchstart',
            'click'
        ];

        function onUserActivity() {

            if (loggingOut || isModalOpen()) {
                return;
            }

            /*
             * Prevent localStorage from being written
             * hundreds of times per second because of
             * mousemove / scroll.
             *
             * Maximum: once every 1 second.
             */

            if (!activityThrottle) {

                activityThrottle = setTimeout(function() {

                    activityThrottle = null;

                    saveUserActivity();

                }, 1000);
            }
        }

        activityEvents.forEach(function(eventName) {

            document.addEventListener(
                eventName,
                onUserActivity, {
                    passive: true,
                    capture: true
                }
            );

        });

        // WARNING MODAL

        function closeWarning() {

            clearInterval(countdownTimer);

            countdownTimer = null;

            warningActive = false;

            const elements = getModalElements();

            if (
                elements &&
                typeof coreui !== 'undefined'
            ) {

                const modal =
                    coreui.Modal.getInstance(
                        elements.modalElement
                    );

                if (modal) {
                    modal.hide();
                }
            }
        }

        function showSessionWarning() {

            if (loggingOut || isModalOpen()) {
                return;
            }

            /*
             * Check if user became active before
             * the timer fired.
             */

            const idleTime = getIdleTime();

            if (idleTime < INACTIVITY_LIMIT) {

                resetInactivityTimer();

                return;
            }

            const elements = getModalElements();

            if (
                !elements ||
                typeof coreui === 'undefined'
            ) {

                console.error(
                    '[SESSION TIMEOUT] Modal/CoreUI not found.'
                );

                logoutUser();

                return;
            }

            const modalElement =
                elements.modalElement;

            const countdownElement =
                elements.countdownElement;

            warningActive = true;

            /*
             * Warning starts exactly after 4 minutes.
             * Then user gets another 60 seconds.
             */

            const warningDeadline =
                lastUserActivity +
                INACTIVITY_LIMIT +
                WARNING_MS;

            function updateCountdown() {

                const remainingSeconds =
                    Math.max(
                        0,
                        Math.ceil(
                            (warningDeadline - Date.now()) / 1000
                        )
                    );

                countdownElement.textContent =
                    remainingSeconds;

                if (remainingSeconds <= 0) {

                    clearInterval(countdownTimer);

                    countdownTimer = null;

                    logoutUser();
                }
            }

            updateCountdown();

            const modal =
                coreui.Modal.getOrCreateInstance(
                    modalElement, {
                        backdrop: 'static',
                        keyboard: false
                    }
                );

            modal.show();

            countdownTimer =
                setInterval(
                    updateCountdown,
                    1000
                );
        }

        // EXTEND SESSION

        window.extendSession = function() {

            if (extending || loggingOut) {
                return;
            }

            extending = true;

            console.log(
                '[SESSION TIMEOUT] 🔄 Extending session...'
            );

            pingBackend()

                .then(function(data) {

                    if (data && data.success) {

                        console.log(
                            '[SESSION TIMEOUT] ✅ Session extended.'
                        );

                        /*
                         * Close modal first because
                         * saveUserActivity() ignores activity
                         * while modal is open.
                         */

                        closeWarning();

                        // Treat Extend as user activity
                        saveUserActivity();

                    } else {

                        console.error(
                            '[SESSION TIMEOUT] ❌ Unable to extend session.'
                        );

                        logoutUser();
                    }

                })

                .catch(function(error) {

                    if (error.message === 'EXPIRED') {

                        logoutUser();

                    } else {

                        console.warn(
                            '[SESSION TIMEOUT] ⚠️ Network error while extending.',
                            error
                        );

                        /*
                         * Keep modal open.
                         * User can click Extend again.
                         */
                    }

                })

                .finally(function() {

                    extending = false;

                });
        };

        // HEARTBEAT / ACTIVITY CHECK

        function checkRecentActivity() {

            if (loggingOut) {
                return;
            }

            if (document.visibilityState !== 'visible') {

                console.log(
                    '[SESSION TIMEOUT] ⏸️ Tab hidden. Skipping check.'
                );

                return;
            }

            if (isModalOpen()) {

                console.log(
                    '[SESSION TIMEOUT] ⚠️ Warning modal open. Skipping check.'
                );

                return;
            }

            /*
             * Get the newest activity timestamp
             * from localStorage.
             *
             * This also allows another tab to update
             * the activity timestamp.
             */

            const storedActivity =
                Number(storageGet(ACTIVITY_KEY));

            if (
                storedActivity &&
                storedActivity > lastUserActivity
            ) {

                lastUserActivity = storedActivity;
            }

            const idleTime = Date.now() - lastUserActivity;

            console.log(
                '[SESSION TIMEOUT] ⏱️ Idle:',
                Math.floor(idleTime / 1000),
                'seconds'
            );

            /*
             * IMPORTANT:
             *
             * Only call the backend if there was
             * activity within the last 1 minute.
             */

            if (idleTime < HEARTBEAT_INTERVAL) {

                console.log(
                    '[SESSION TIMEOUT] 💓 Recent activity detected. Calling backend...'
                );

                pingBackend()

                    .then(function(data) {

                        if (data && data.success) {

                            console.log(
                                '[SESSION TIMEOUT] ✅ Backend session reset.'
                            );

                        } else {

                            console.error(
                                '[SESSION TIMEOUT] ❌ Backend session expired.'
                            );

                            logoutUser();
                        }

                    })

                    .catch(function(error) {

                        if (error.message === 'EXPIRED') {

                            logoutUser();

                        } else {

                            /*
                             * Do NOT immediately logout on a
                             * temporary network problem.
                             *
                             * The next 1-minute check will retry.
                             */

                            console.warn(
                                '[SESSION TIMEOUT] ⚠️ Backend check failed. Will retry next minute.'
                            );
                        }
                    });

            } else {

                console.log(
                    '[SESSION TIMEOUT] 💤 No recent activity. Backend not called.'
                );
            }

            /*
             * Always check frontend inactivity deadline.
             */

            if (idleTime >= INACTIVITY_LIMIT) {

                showSessionWarning();

            } else {

                resetInactivityTimer();
            }
        }

        // LOGOUT

        function logoutUser() {

            if (loggingOut) {
                return;
            }

            loggingOut = true;

            console.log(
                '[SESSION TIMEOUT] 🚪 Logging out user...'
            );

            clearInterval(countdownTimer);
            clearTimeout(inactivityTimer);
            clearInterval(heartbeatTimer);
            clearTimeout(activityThrottle);

            storageRemove(ACTIVITY_KEY);

            /*
             * Notify other tabs.
             */

            storageSet(
                LOGOUT_KEY,
                Date.now()
            );

            /*
             * Destroy PHP session.
             */

            try {

                if (navigator.sendBeacon) {

                    navigator.sendBeacon(
                        LOGOUT_URL
                    );

                } else {

                    fetch(LOGOUT_URL, {
                        method: 'POST',
                        credentials: 'same-origin',
                        keepalive: true
                    });
                }

            } catch (error) {

                // Ignore logout network errors
            }

            window.location.href = LOGIN_URL;
        }

        // MULTI-TAB SYNCHRONIZATION

        window.addEventListener(
            'storage',
            function(event) {

                /*
                 * Another tab logged out.
                 */

                if (
                    event.key === LOGOUT_KEY &&
                    event.newValue
                ) {

                    loggingOut = true;

                    clearInterval(countdownTimer);
                    clearTimeout(inactivityTimer);
                    clearInterval(heartbeatTimer);

                    window.location.href =
                        LOGIN_URL;

                    return;
                }

                /*
                 * Activity from another tab.
                 */

                if (
                    event.key === ACTIVITY_KEY &&
                    event.newValue
                ) {

                    const sharedActivity =
                        Number(event.newValue);

                    if (
                        sharedActivity &&
                        sharedActivity > lastUserActivity
                    ) {

                        lastUserActivity =
                            sharedActivity;

                        console.log(
                            '[SESSION TIMEOUT] 🔄 Activity received from another tab.'
                        );

                        /*
                         * If our warning modal was open,
                         * close it because another tab is active.
                         */

                        if (isModalOpen()) {
                            closeWarning();
                        }

                        resetInactivityTimer();
                    }
                }
            }
        );

        // TAB VISIBILITY

        document.addEventListener(
            'visibilitychange',
            function() {

                if (
                    document.visibilityState !== 'visible'
                ) {

                    console.log(
                        '[SESSION TIMEOUT] 🌙 Tab hidden.'
                    );

                    return;
                }

                console.log(
                    '[SESSION TIMEOUT] 👁️ Tab visible.'
                );

                /*
                 * Get latest activity from localStorage.
                 */

                const storedActivity =
                    Number(storageGet(ACTIVITY_KEY));

                if (
                    storedActivity &&
                    storedActivity > lastUserActivity
                ) {

                    lastUserActivity =
                        storedActivity;
                }

                const idleTime =
                    Date.now() - lastUserActivity;

                /*
                 * If already beyond warning period,
                 * logout.
                 */

                if (
                    idleTime >=
                    INACTIVITY_LIMIT + WARNING_MS
                ) {

                    logoutUser();

                    return;
                }

                /*
                 * If already at 4 minutes,
                 * show warning.
                 */

                if (
                    idleTime >= INACTIVITY_LIMIT
                ) {

                    showSessionWarning();

                    return;
                }

                /*
                 * Otherwise continue normally.
                 */

                resetInactivityTimer();
            }
        );

        // INITIALIZATION

        const storedActivity =
            Number(storageGet(ACTIVITY_KEY));

        /*
         * Ignore very old activity timestamp.
         */

        if (
            storedActivity &&
            (Date.now() - storedActivity) <
            (INACTIVITY_LIMIT + WARNING_MS)
        ) {

            lastUserActivity =
                storedActivity;

        } else {

            lastUserActivity =
                Date.now();

            storageSet(
                ACTIVITY_KEY,
                lastUserActivity
            );
        }

        /*
         * Start inactivity timer.
         */

        resetInactivityTimer();

        /*
         * If page opened while already inactive,
         * immediately show warning.
         */

        if (
            getIdleTime() >=
            INACTIVITY_LIMIT
        ) {

            showSessionWarning();
        }

        /*
         * IMPORTANT:
         *
         * Every 1 minute:
         *
         *   recent activity (< 1 min)
         *          ↓
         *      Auth.php
         *
         *   no recent activity (>= 1 min)
         *          ↓
         *      no backend call
         *
         */

        heartbeatTimer =
            setInterval(
                checkRecentActivity,
                HEARTBEAT_INTERVAL
            );

    })();
</script>