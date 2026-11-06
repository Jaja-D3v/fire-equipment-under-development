# Adaptive Performance Manager

## Scope

The SMS applies a local visual-performance preference through `js/performance-manager.js` and `css/performance.css`. No device information is sent to the backend. There is no Settings page or profile-menu change.

This implementation changes only audited decorative effects. It does not change application requests, authentication/session logic, database queries, pagination, scanner settings, inspection calculations, or workflows.

High retains the existing visual experience. Balanced and Lite keep the same content, layout, dimensions, positioning, colors, backgrounds, borders, and focus styles while reducing selected animation and shadow costs.

## Files and integration

Created:

- `js/performance-manager.js`: capability hints, classification, local preferences, reduced motion, optional bounded runtime sampling, and the Chart.js adapter.
- `css/performance.css`: explicit selectors for audited decorative effects.
- `PERFORMANCE.md`: this guide.

Existing files receive asset includes only:

| File | Integration |
|---|---|
| `partials/header.php` | Early manager script, performance stylesheet, and one guarded `attachCharts(window.Chart)` call after the existing Chart.js script. Covers the PHP modules using this header. |
| `authentication/login.php` | Manager and stylesheet using `../` paths. |
| `authentication/system-selector.php` | Same standalone-page integration. |
| `error-pages/401.html`, `403.html`, `404.html`, `500.html` | Manager and stylesheet resolved through the existing base URL. |

The early script sets root classes before the main page is displayed. Stylesheet selectors use those classes and enough specificity to coexist with module styles loaded later. No vendor file is modified.

Includes use `?v=1`. Bump this asset version consistently when deploying a later revision if existing browser caches need invalidation.

## Local capability detection

`readCapabilities()` safely reads:

- `navigator.hardwareConcurrency`: reported logical processors.
- `navigator.deviceMemory`: approximate reported memory, when available.
- `navigator.connection` or its available prefixed equivalent: Save-Data and effective connection type.
- Viewport and screen dimensions.

Only valid, finite, positive numeric hardware values participate in classification. Missing values, throwing getters, and invalid values are treated as unknown.

Reported processor count and memory are hints, not measurements of available CPU time or free RAM. Memory reporting may be absent on HTTP deployments or unsupported browsers.

Network hints and screen/viewport dimensions do **not** classify CPU performance. A small screen or slow network alone never forces Lite. No browser/OS name or user-agent detection is used.

Raw capability hints remain in memory. Stored values are the selected preference and, when applicable, the session's runtime mode cap.

## Automatic classification

`classifyDevice()` uses these initial rules in order:

| Result | Rule |
|---|---|
| Lite | Known processors ≤2; or known memory ≤2GB; or both processors ≤4 and memory ≤4GB. |
| High | Both processors ≥8 and memory ≥8GB. |
| Balanced | Everything else, including completely unavailable information. |

Both values must be known for a combined rule. For example, four processors with unavailable memory produces Balanced, not Lite.

These conservative heuristics are configuration choices, not a claim that every device at a threshold has identical performance. They can be adjusted in `classifyDevice()` after evidence from actual devices.

## Preferences and public API

The manager exposes one object: `window.SMSPerformance`.

```javascript
SMSPerformance.setPreference('automatic');
SMSPerformance.setPreference('high');
SMSPerformance.setPreference('balanced');
SMSPerformance.setPreference('lite');

SMSPerformance.getState();
```

`setPreference()` accepts the four lowercase values shown above and returns `true`. Invalid values return `false` without changing the preference.

Manual High, Balanced, or Lite overrides capability classification and any runtime downgrade. Automatic returns control to capability hints and the session runtime cap.

`getState()` returns:

| Field | Meaning |
|---|---|
| `preference` | `automatic`, `high`, `balanced`, or `lite`. |
| `mode` | Effective hardware/runtime/manual tier. |
| `reducedMotion` | Whether the browser currently reports a reduced-motion preference. |
| `reason` | `default`, `capability-hints`, `manual`, or `runtime-frame-delivery`. |
| `persistence` | Where a saved preference was read/written: `localStorage`, `sessionStorage`, or `memory`. Automatic with no saved value initially reports memory. |

The API is intended for approved integrations and developer testing. This release adds no visible preference controls.

### Persistence and blocked storage

The preference key is `sms.performance.preference`.

1. Writes try localStorage first.
2. If that is unavailable or blocked, writes try sessionStorage.
3. If both stores fail, the current page still applies the preference in memory.

Reads prioritize a session fallback over an older local preference that could not be overwritten. A successful local write clears the session fallback when possible. Storage access is wrapped in `try/catch`.

localStorage normally preserves the preference across refreshes and navigation on the same origin, including login and error pages. sessionStorage fallback preserves it within the tab session. The manager listens for relevant storage events.

**Limit:** if both stores are blocked, a manual preference cannot survive a full page navigation or reload. It remains usable on the current page; Automatic runs again on the next page. No cookies, URL rewriting, or backend storage are introduced to bypass that restriction.

These storage keys are separate from existing authentication/session keys.

## Root classes and reduced motion

`applyMode()` adds exactly one tier class:

- `performance-high`
- `performance-balanced`
- `performance-lite`

It preserves unrelated theme/application classes. It avoids rewriting classes or updating charts when the effective mode and motion preference have not changed.

Reduced motion adds `performance-reduced-motion` independently of the tier. Manual High therefore does not override the user's motion preference.

`watchReducedMotion()` feature-checks `matchMedia()` and uses either a modern change listener or the older `addListener()` interface. A scoped CSS media-query fallback also handles supported browser preferences.

If a browser cannot expose the preference, the manager cannot infer it. Manual Lite remains available through the API.

## Decorative-property audit

The following properties were reviewed against the existing selectors before adding overrides:

| Targets | Property adjusted | Why the override is decorative |
|---|---|---|
| Known login entrance elements, including the login card, logo, headings, benefit content, and input groups | Animation duration/delay | Existing keyframes end at the same visible state. Fill mode and transform values are retained; the manager does not replace positioning transforms. |
| Login success icon | Animation duration/delay | The existing confirmation remains visible; authentication and redirect timing are untouched. |
| System-selector cards, login success overlay/popup, and login error display | Transition duration | Existing state classes, end positions, visibility values, borders, and colors remain unchanged. |
| Dashboard coverage bars and loading-opacity transitions | Transition duration | Final widths and opacity values still come from existing code/CSS; counts and request timing are unchanged. |
| Monitoring loading dots and the dashboard refresh icon | Repeating decorative animation | Existing loading text/status remains present. No timer, request, or disabled state is changed. |
| Dashboard statistic/equipment/panel surfaces, monitoring card surfaces, and the shared topbar | Surface box shadow | These are static container shadows, not input focus rings or inset table-state colors. Dimensions, borders, backgrounds, and child focus rules remain intact. |
| Current Chart.js charts | Animation options | Labels, datasets, scales, tooltips, and requests are retained. Charts with explicit animation completion/progress callbacks are excluded from adaptation. |

The performance stylesheet only declares:

- `animation-duration`
- `animation-delay`
- `animation-iteration-count`
- `animation` on named decorative loading pulses
- `transition-duration`
- `box-shadow` on named static surfaces

There are no blanket `*` selectors, position/display/sizing overrides, transform resets, opacity overrides, color changes, or global focus-shadow removal.

### Effects deliberately left intact

Blur on translucent login, system-selector, and monitoring surfaces contributes to text/background separation. Removing it safely could require changing backgrounds or colors, which is outside the approved scope. These backdrop filters are therefore unchanged.

The manager also leaves Bootstrap/CoreUI modal, collapse, dropdown, and sidebar transitions alone. These can participate in component timing and positioning. Table-state shadows, input focus indicators, QR scanning, notification/session timers, and the uninspected card's real-height expansion are not modified.

## Behavior by tier

| Effect | High | Balanced | Lite |
|---|---|---|---|
| Audited login entrances | Existing timing | 150ms, no stagger delay | 0.01ms, no delay, one iteration |
| Audited decorative transitions | Existing timing | 120ms | 0.01ms |
| Named loading pulses | Existing behavior | Slower 2-second cycle | Static indicator with existing status text |
| Audited surface shadows | Existing shadows | Small 6px-blur shadow | None |
| Chart animations | Original options | Up to 150ms; active/hover timing capped | Disabled |

Near-zero entrance duration, rather than removing the animation definition, preserves the existing final keyframe and fill/transform semantics. It is an effectively instantaneous change, not a new continuous animation.

Reduced motion applies the near-zero decorative timings, stops named pulses, and disables chart animation regardless of the selected tier.

## Central Chart.js adapter

`partials/header.php` calls:

```javascript
if (window.SMSPerformance) {
    window.SMSPerformance.attachCharts(window.Chart);
}
```

`attachCharts()` checks the available API/version and registers one plugin for Chart.js 3 or 4. A missing library, unsupported version, or duplicate attachment is safely skipped. There is no polling and no constructor replacement.

The plugin applies options before each new chart initializes. `applyChartOptions()` saves the chart's original animation/transition configuration, then applies the effective visual policy.

- High restores original values, including the absence of an option.
- Balanced caps animation and active-transition durations without enabling animations that were explicitly disabled.
- Lite and reduced motion set `animation = false`.
- Charts with explicit `onComplete` or `onProgress` animation callbacks are left untouched, including mode-change redraws, to avoid changing future callback-dependent behavior.

`updateCharts()` handles existing registered charts when the effective mode changes. It changes only animation-related options and requests an update without a transition. No data calculation or data-fetch function is called.

The application's Chart.js CDN URL is currently unpinned. The adapter guards its supported API; this implementation does not upgrade or pin existing dependencies.

## Bounded runtime observation

`afterLoad()` can schedule one short `sampleRuntime()` observation 750ms after load.

Sampling is skipped when:

- A manual preference is selected.
- Lite is already selected.
- Reduced motion is active.
- The document is hidden.
- requestAnimationFrame is unavailable.
- A QR-reader element or video is present.
- sessionStorage cannot retain the sample marker.
- This tab session already has a sample marker or runtime cap.

The sample observes up to 32 frame intervals without rendering test content or running synthetic CPU work. It stops on preference/visibility changes and abandons an observation exceeding 2500ms.

If the median interval is at least 45ms, High is capped to Balanced, or Balanced to Lite. It never promotes a device. The key `sms.performance.runtime` stores `checked` or the derived cap in sessionStorage, preventing repeated sampling and mode flapping across normal navigation.

This is a cautious frame-delivery observation, not a hardware benchmark or proof that a mode improves performance. Transient loading, other applications, and display behavior can affect frame delivery.

## Important functions

| Function | Purpose |
|---|---|
| `readCapabilities()` | Read optional local browser hints with guarded access. |
| `classifyDevice()` | Apply the documented hardware-hint rules. |
| `readStorage()` / `writeStorage()` | Catch missing/blocked storage failures. |
| `readPreference()` / `savePreference()` | Resolve and persist the manual/Automatic preference. |
| `readRuntimeCap()` | Read the current tab's optional runtime cap. |
| `watchReducedMotion()` | Observe the user's motion preference with compatibility fallbacks. |
| `applyMode()` | Resolve the tier, maintain root classes, and update chart animation policy. |
| `setPreference()` | Public manual/Automatic override API. |
| `getState()` | Public diagnostic state without exposing raw hardware hints. |
| `attachCharts()` | Attach the central guarded Chart.js adapter once. |
| `applyChartOptions()` | Preserve originals and change only safe chart animation options. |
| `updateCharts()` | Apply mode changes to existing charts without changing data. |
| `sampleRuntime()` / `afterLoad()` | Perform the bounded, optional observation once per tab session. |

## Compatibility

The manager itself uses ES5 syntax and no additional dependencies. Optional APIs, storage, media-query listeners, and Chart.js attachment are guarded. CSS uses ordinary root-class selectors and established animation/transition properties.

This does not make the entire existing application ES5-compatible. Other SMS scripts and external libraries already use newer syntax/APIs such as async functions, AbortController, replaceChildren, and inert. Their compatibility is unchanged.

Android 7.1.1 does not identify the installed Chrome/WebView version. Test the actual Honeywell EDA50 browser/WebView before claiming device compatibility. No OS/user-agent override is built into classification.

## Verification and limits

Automated checks during implementation covered:

- Classification combinations, invalid/missing hardware values, throwing API getters, and missing optional APIs.
- All four override values, invalid-value rejection, refresh/navigation persistence, session fallback, and fully blocked storage.
- Modern and legacy reduced-motion listeners, including manual High.
- Bounded runtime behavior, fast/slow frame simulations, hidden documents, scanner exclusion, and session caps.
- Chart adapter registration, original-option restoration, preserved data, disabled animations, and callback exemptions.
- ES5 parsing of the manager, JavaScript syntax, PHP syntax for modified PHP views, and scoped whitespace checks.
- Chrome preview comparisons at 375px, 768px, and 1440px using the original and adapted styles.
- Measured DOM geometry, final positioning/transforms, colors, backgrounds, borders, visibility, font styling, backdrop filters, and input focus styling.
- Computed decorative timings/shadows, actual Chart.js animation settings, and fixture chart labels/data.
- Bootstrap modal/dropdown interaction, reduced-motion changes, reloads, and navigation to login, system selector, and the four standalone error pages.
- Source comparisons confirming that existing integration files changed only by the performance asset includes and shared chart hook.

Browser tests use preview fixtures and standalone-page markup with authentication/app request scripts excluded. They do not certify authenticated end-to-end workflows, every screen state, every device, or every external library version.

Still required: physical Honeywell EDA50/Android 7.1.1 testing, the actual deployed WebView version, representative real-user workflow checks, and device profiling if measurable speed, memory, battery, or frame-rate improvements are to be claimed.

No measurable performance improvement is claimed by this implementation.

## Adding optimizations safely

1. Identify the exact source selector and property.
2. Verify that it is decorative in every affected state, including focus, hover, loading, errors, and reduced motion.
3. If it changes containing blocks, positioning, readability, colors, or component completion behavior, leave it alone unless a separate change is approved.
4. Add an explicit selector to `performance.css`, not duplicated device checks in page scripts.
5. Preserve High's existing presentation and provide safe missing-API/storage behavior.
6. Compare original and adapted computed styles and layout at desktop/tablet/mobile widths.
7. Verify the owning component's actual behavior and update this audit/documentation.
8. Profile on the real target device before reporting performance gains.

Do not patch global timer, Fetch, scanner, authentication, or chart-data functions to implement visual modes.
