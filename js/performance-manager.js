/* SMS visual performance preferences. ES5 syntax keeps this bootstrap usable in older WebViews. */
(function (window, document) {
    'use strict';
    if (window.SMSPerformance) return;

    var preferenceKey = 'sms.performance.preference';
    var runtimeKey = 'sms.performance.runtime';
    var preference = 'automatic';
    var mode = 'balanced';
    var reason = 'default';
    var persistence = 'memory';
    var reducedMotion = false;
    var mediaQuery = null;
    var chartLibrary = null;
    var appliedMode = '';
    var appliedMotion = null;
    var capabilities = readCapabilities();
    var runtimeCap = readRuntimeCap();

    function validPreference(value) {
        return value === 'automatic' || value === 'high' || value === 'balanced' || value === 'lite';
    }

    // Storage may be absent or throw even when the property exists.
    function readStorage(name, key) {
        try {
            var storage = window[name];
            return storage ? storage.getItem(key) : null;
        } catch (error) {
            return null;
        }
    }

    function writeStorage(name, key, value) {
        try {
            var storage = window[name];
            if (!storage) return false;
            storage.setItem(key, value);
            return true;
        } catch (error) {
            return false;
        }
    }

    // A session fallback takes precedence over an older preference that could not be overwritten.
    function readPreference() {
        var value = readStorage('sessionStorage', preferenceKey);
        if (validPreference(value)) {
            persistence = 'sessionStorage';
            return value;
        }
        value = readStorage('localStorage', preferenceKey);
        if (validPreference(value)) {
            persistence = 'localStorage';
            return value;
        }
        return 'automatic';
    }

    function savePreference(value) {
        if (writeStorage('localStorage', preferenceKey, value)) {
            persistence = 'localStorage';
            try {
                window.sessionStorage.removeItem(preferenceKey);
            } catch (error) {
                // An unavailable session store must not block an applied preference.
            }
        } else if (writeStorage('sessionStorage', preferenceKey, value)) {
            persistence = 'sessionStorage';
        } else {
            persistence = 'memory';
        }
    }

    function positiveNumber(value) {
        return typeof value === 'number' && isFinite(value) && value > 0 ? value : null;
    }

    // Hints stay in memory on this page. Network and viewport size never determine CPU capability.
    function readCapabilities() {
        var hints = { cores: null, memory: null, saveData: false, effectiveType: '', width: 0, height: 0, screenWidth: null, screenHeight: null };
        try {
            hints.cores = positiveNumber(window.navigator.hardwareConcurrency);
        } catch (error) {}
        try {
            hints.memory = positiveNumber(window.navigator.deviceMemory);
        } catch (error) {}
        try {
            var connection = window.navigator.connection || window.navigator.mozConnection || window.navigator.webkitConnection;
            if (connection) {
                hints.saveData = connection.saveData === true;
                hints.effectiveType = typeof connection.effectiveType === 'string' ? connection.effectiveType : '';
            }
        } catch (error) {}
        try {
            hints.width = window.innerWidth || document.documentElement.clientWidth || 0;
            hints.height = window.innerHeight || document.documentElement.clientHeight || 0;
            if (window.screen) {
                hints.screenWidth = positiveNumber(window.screen.width);
                hints.screenHeight = positiveNumber(window.screen.height);
            }
        } catch (error) {}
        return hints;
    }

    // These are conservative hints, not measurements of free RAM or CPU speed.
    function classifyDevice() {
        var cores = capabilities.cores;
        var memory = capabilities.memory;
        if ((cores !== null && cores <= 2) || (memory !== null && memory <= 2)
            || (cores !== null && memory !== null && cores <= 4 && memory <= 4)) {
            return 'lite';
        }
        if (cores !== null && memory !== null && cores >= 8 && memory >= 8) {
            return 'high';
        }
        return 'balanced';
    }

    function readRuntimeCap() {
        var value = readStorage('sessionStorage', runtimeKey);
        return value === 'balanced' || value === 'lite' ? value : null;
    }

    function rank(value) {
        return value === 'high' ? 2 : value === 'balanced' ? 1 : 0;
    }

    // Replace only manager-owned classes; all application/theme classes are kept.
    function applyMode() {
        mode = preference === 'automatic' ? classifyDevice() : preference;
        reason = preference === 'automatic'
            ? (capabilities.cores === null && capabilities.memory === null ? 'default' : 'capability-hints') : 'manual';
        if (preference === 'automatic' && runtimeCap && rank(runtimeCap) < rank(mode)) {
            mode = runtimeCap;
            reason = 'runtime-frame-delivery';
        }
        if (appliedMode === mode && appliedMotion === reducedMotion) return;
        appliedMode = mode;
        appliedMotion = reducedMotion;
        var root = document.documentElement;
        var originalClasses = String(root.className || '').split(/\s+/);
        var classes = [];
        var index;
        for (index = 0; index < originalClasses.length; index++) {
            var name = originalClasses[index];
            if (name && !/^performance-(high|balanced|lite|reduced-motion)$/.test(name)) classes.push(name);
        }
        classes.push('performance-' + mode);
        if (reducedMotion) classes.push('performance-reduced-motion');
        root.className = classes.join(' ');
        updateCharts();
    }

    // Motion is independent of the selected tier, including manual High.
    function watchReducedMotion() {
        if (typeof window.matchMedia !== 'function') return;
        try {
            mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
            reducedMotion = mediaQuery.matches === true;
            var onChange = function () {
                reducedMotion = mediaQuery.matches === true;
                applyMode();
            };
            if (typeof mediaQuery.addEventListener === 'function') {
                mediaQuery.addEventListener('change', onChange);
            } else if (typeof mediaQuery.addListener === 'function') {
                mediaQuery.addListener(onChange);
            }
        } catch (error) {
            // Missing preference support leaves the explicit Lite override available.
        }
    }

    function copyObject(source) {
        var target = {};
        var key;
        if (source && typeof source === 'object') {
            for (key in source) {
                if (Object.prototype.hasOwnProperty.call(source, key)) target[key] = source[key];
            }
        }
        return target;
    }

    function restoreOption(options, key, saved) {
        if (saved['has' + key]) options[key] = saved[key];
        else delete options[key];
    }

    // Current charts use animation only for presentation. Preserve any future completion callbacks.
    function applyChartOptions(chart) {
        if (!chart || !chart.config || !chart.config.options) return false;
        var options = chart.config.options;
        var saved = chart.$smsPerformance;
        if (!saved) {
            saved = {
                hasanimation: Object.prototype.hasOwnProperty.call(options, 'animation'),
                animation: options.animation,
                hastransitions: Object.prototype.hasOwnProperty.call(options, 'transitions'),
                transitions: options.transitions
            };
            chart.$smsPerformance = saved;
        }
        var animation = saved.animation;
        if (animation && (typeof animation.onComplete === 'function' || typeof animation.onProgress === 'function')) return false;

        restoreOption(options, 'animation', saved);
        restoreOption(options, 'transitions', saved);
        if (reducedMotion || mode === 'lite') {
            options.animation = false;
        } else if (mode === 'balanced' && animation !== false) {
            options.animation = copyObject(animation);
            options.animation.duration = typeof animation === 'object' && animation !== null
                && typeof animation.duration === 'number' ? Math.min(animation.duration, 150) : 150;
            options.animation.delay = 0;
            options.transitions = copyObject(saved.transitions);
            options.transitions.active = copyObject(options.transitions.active);
            options.transitions.active.animation = copyObject(options.transitions.active.animation);
            var activeDuration = options.transitions.active.animation.duration;
            options.transitions.active.animation.duration = typeof activeDuration === 'number' ? Math.min(activeDuration, 150) : 150;
        }
        return true;
    }

    // Mode changes alter animation options, never datasets, labels, scales, or network requests.
    function updateCharts() {
        if (!chartLibrary || !chartLibrary.instances) return;
        var key;
        for (key in chartLibrary.instances) {
            if (!Object.prototype.hasOwnProperty.call(chartLibrary.instances, key)) continue;
            var chart = chartLibrary.instances[key];
            if (!applyChartOptions(chart)) continue;
            if (typeof chart.stop === 'function') chart.stop();
            if (typeof chart.update === 'function') chart.update('none');
        }
    }

    // Called once after the existing CDN script. No polling or constructor replacement.
    function attachCharts(Chart) {
        if (!Chart || chartLibrary === Chart || typeof Chart.register !== 'function') return false;
        var major = parseInt(String(Chart.version || '').split('.')[0], 10);
        if (major !== 3 && major !== 4) return false;
        chartLibrary = Chart;
        Chart.register({
            id: 'smsAdaptivePerformance',
            beforeInit: function (chart) {
                applyChartOptions(chart);
            }
        });
        updateCharts();
        return true;
    }

    // One short observation per tab session; no synthetic workload or continuous monitor.
    function sampleRuntime() {
        if (preference !== 'automatic' || reducedMotion || mode === 'lite'
            || typeof window.requestAnimationFrame !== 'function' || document.hidden === true) return;
        if (document.getElementById('qr-reader') || document.querySelector('video')) return;
        if (readStorage('sessionStorage', runtimeKey) !== null) return;
        if (!writeStorage('sessionStorage', runtimeKey, 'checked')) return;

        var frames = [];
        var previous = null;
        var started = null;
        function frame(timestamp) {
            if (preference !== 'automatic' || document.hidden === true || reducedMotion) return;
            if (typeof timestamp !== 'number') return;
            if (started === null) started = timestamp;
            if (timestamp - started > 2500) return;
            if (previous !== null) frames.push(timestamp - previous);
            previous = timestamp;
            if (frames.length < 32) {
                window.requestAnimationFrame(frame);
                return;
            }
            frames.sort(function (a, b) { return a - b; });
            var median = (frames[15] + frames[16]) / 2;
            // Require sustained very poor frame delivery, not one loading spike.
            if (median >= 45) {
                runtimeCap = mode === 'high' ? 'balanced' : 'lite';
                writeStorage('sessionStorage', runtimeKey, runtimeCap);
                applyMode();
            }
        }
        window.requestAnimationFrame(frame);
    }

    function afterLoad() {
        if (preference === 'automatic' && mode !== 'lite' && !reducedMotion) {
            window.setTimeout(sampleRuntime, 750);
        }
    }

    function setPreference(value) {
        if (!validPreference(value)) return false;
        preference = value;
        savePreference(value);
        applyMode();
        return true;
    }

    function getState() {
        return { preference: preference, mode: mode, reducedMotion: reducedMotion, reason: reason, persistence: persistence };
    }

    window.SMSPerformance = {
        setPreference: setPreference,
        getState: getState,
        attachCharts: attachCharts
    };

    preference = readPreference();
    watchReducedMotion();
    applyMode();

    if (window.addEventListener) {
        window.addEventListener('storage', function (event) {
            if (event.key === preferenceKey || event.key === null) {
                preference = readPreference();
                applyMode();
            }
        });
        if (document.readyState === 'complete') afterLoad();
        else window.addEventListener('load', afterLoad);
    }
}(window, document));
