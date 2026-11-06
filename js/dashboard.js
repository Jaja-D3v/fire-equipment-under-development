document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    // Elements and state shared by the filter, card, and chart functions.
    const endpoint = 'backend/controller/DashboardDataController.php';
    const root = document.getElementById('equipmentDashboard');
    const form = document.getElementById('dashboardFilters');
    if (!root || !form) return;
    const period = document.getElementById('dashboardPeriod');
    const branch = document.getElementById('dashboardBranch');
    const from = document.getElementById('dashboardFrom');
    const to = document.getElementById('dashboardTo');
    const status = document.getElementById('dashboardStatus');
    const signIn = document.getElementById('dashboardSignIn');
    const numberFormatter = new Intl.NumberFormat('en-US');
    let currentData = null;
    let latestRequestId = 0;
    let pendingRequest = null;
    let monthlyChart = null;
    let coverageChart = null;
    let expired = false;

    // The flip card uses the same summaries as the rest of the dashboard.
    const uninspectedCard = document.getElementById('dashboardUninspectedCard');
    const uninspectedFront = document.getElementById('dashboardUninspectedFront');
    const uninspectedBack = document.getElementById('dashboardUninspectedBack');
    const uninspectedReturn = document.getElementById('dashboardUninspectedReturn');
    let uninspectedSignature = '';

    // Hide the inactive face from keyboard and screen-reader navigation.
    function flipUninspected(showBack) {
        if (!uninspectedCard) return;
        uninspectedCard.classList.toggle('is-flipped', showBack);
        uninspectedFront.setAttribute('aria-expanded', String(showBack));
        uninspectedFront.setAttribute('aria-hidden', String(showBack));
        uninspectedFront.inert = showBack;
        uninspectedBack.setAttribute('aria-hidden', String(!showBack));
        uninspectedBack.inert = !showBack;
        (showBack ? uninspectedReturn : uninspectedFront).focus({ preventScroll: true });
    }

    if (uninspectedCard) {
        uninspectedCard.addEventListener('click', function (event) {
            if (!uninspectedCard.classList.contains('is-flipped') && !uninspectedBack.contains(event.target)) flipUninspected(true);
        });
        uninspectedFront.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                flipUninspected(true);
            }
        });
        uninspectedReturn.addEventListener('click', function (event) {
            event.stopPropagation();
            flipUninspected(false);
        });
        uninspectedBack.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                event.preventDefault();
                flipUninspected(false);
            }
        });
    }

    // Rebuild the breakdown only when its equipment counts or labels change.
    function updateUninspectedBreakdown(data) {
        if (!uninspectedCard) return;
        // Reuse the same database summaries as the total; no per-card request or category list.
        const rows = data.equipment.filter(function (item) { return item.not_inspected > 0; });
        const signature = JSON.stringify([data.totals.not_inspected, rows.map(function (item) { return [item.id, item.label, item.not_inspected]; })]);
        if (signature === uninspectedSignature) return;
        uninspectedSignature = signature;
        const list = document.getElementById('dashboardUninspectedList');
        const fragment = document.createDocumentFragment();
        rows.forEach(function (item) {
            const row = document.createElement('li');
            row.className = 'dash-uninspected-row d-flex justify-content-between align-items-center gap-2 px-2 py-1';
            const label = document.createElement('span');
            label.textContent = item.label;
            const count = document.createElement('b');
            count.className = 'dash-uninspected-badge badge rounded-pill flex-shrink-0';
            count.textContent = numberFormatter.format(item.not_inspected);
            row.append(label, count);
            fragment.appendChild(row);
        });
        list.replaceChildren(fragment);
        document.getElementById('dashboardUninspectedEmpty').hidden = rows.length > 0;
        document.getElementById('dashboardUninspectedEmpty').textContent = 'No uninspected equipment in this period.';
        const sum = data.equipment.reduce(function (total, item) { return total + Number(item.not_inspected); }, 0);
        const mismatch = document.getElementById('dashboardUninspectedMismatch');
        mismatch.hidden = sum === Number(data.totals.not_inspected);
        mismatch.textContent = mismatch.hidden ? '' : 'Breakdown totals ' + numberFormatter.format(sum) + '; dashboard total is ' + numberFormatter.format(data.totals.not_inspected) + '.';
    }

    // Show loading or error text without discarding the last successful results.
    function showStatus(message, error) {
        status.textContent = message;
        status.hidden = !message;
        status.classList.toggle('is-error', Boolean(error));
    }

    // Custom dates are required only while the Custom Date Range option is selected.
    function updateCustomDateFields() {
        const custom = period.value === 'custom';
        document.getElementById('dashboardCustomDates').hidden = !custom;
        from.required = custom;
        to.required = custom;
        to.min = from.value || '';
    }

    function createLegendItem(label, color) {
        const item = document.createElement('span');
        item.className = 'dash-legend-item d-inline-flex align-items-center gap-2';
        const dot = document.createElement('span');
        dot.className = 'dash-legend-dot d-inline-block flex-shrink-0';
        dot.style.backgroundColor = color;
        item.appendChild(dot);
        item.appendChild(document.createTextNode(label));
        return item;
    }

    // Update existing equipment cards and clone the PHP template for new equipment types.
    function updateEquipmentCards(data) {
        const container = document.getElementById('dashboardEquipmentCards');
        const present = new Set();
        data.equipment.forEach(function (equipment) {
            let card = Array.from(container.children).find(function (element) {
                return element.dataset.equipment === equipment.id;
            });
            if (!card) {
                card = document.getElementById('dashboardEquipmentTemplate').content.firstElementChild.cloneNode(true);
                card.dataset.equipment = equipment.id;
                container.appendChild(card);
            }
            present.add(equipment.id);
            card.querySelector('h3').textContent = equipment.label;
            const symbol = card.querySelector('.equipment-symbol');
            symbol.style.setProperty('--equipment-accent', equipment.color);
            symbol.querySelector('i').className = 'bi ' + equipment.icon;
            const link = card.querySelector('.equipment-link');
            link.href = equipment.url;
            link.setAttribute('aria-label', 'View ' + equipment.label + ' inventory');
            ['active', 'inspected', 'not_inspected'].forEach(function (key) {
                card.querySelector('[data-module-value="' + key + '"]').textContent = numberFormatter.format(equipment[key]);
            });
            const percent = equipment.active ? Math.round(equipment.inspected * 100 / equipment.active) : 0;
            const track = card.querySelector('.coverage-track');
            track.setAttribute('aria-valuenow', percent);
            track.firstElementChild.style.width = percent + '%';
            track.firstElementChild.style.backgroundColor = equipment.color;
        });
        Array.from(container.children).forEach(function (card) {
            if (!present.has(card.dataset.equipment)) card.remove();
        });
    }

    // Provide the same monthly counts as a readable alternative to the chart.
    function updateMonthlyTable(data) {
        const table = document.createElement('table');
        table.className = 'table table-sm mb-0';
        const head = document.createElement('thead');
        const headRow = document.createElement('tr');
        ['Month'].concat(data.equipment.map(function (item) { return item.label; })).forEach(function (label) {
            const cell = document.createElement('th');
            cell.scope = 'col';
            cell.textContent = label;
            headRow.appendChild(cell);
        });
        head.appendChild(headRow);
        table.appendChild(head);
        const body = document.createElement('tbody');
        data.months.forEach(function (month, index) {
            const row = document.createElement('tr');
            const label = document.createElement('th');
            label.scope = 'row';
            label.textContent = month.label;
            row.appendChild(label);
            data.equipment.forEach(function (equipment) {
                const cell = document.createElement('td');
                cell.textContent = numberFormatter.format(equipment.monthly[index]);
                row.appendChild(cell);
            });
            body.appendChild(row);
        });
        table.appendChild(body);
        const container = document.getElementById('dashboardMonthlyTable');
        container.textContent = '';
        container.appendChild(table);
    }

    // Legends use text nodes so equipment names are displayed safely.
    function updateChartLegends(data) {
        const monthlyLegend = document.getElementById('dashboardMonthlyLegend');
        monthlyLegend.textContent = '';
        data.equipment.forEach(function (equipment) {
            monthlyLegend.appendChild(createLegendItem(equipment.label, equipment.color));
        });

        const inspected = data.totals.inspected;
        const remaining = data.totals.not_inspected;
        const coveragePercent = data.totals.active ? Math.round(inspected * 100 / data.totals.active) : 0;
        document.getElementById('dashboardCoveragePercent').textContent = coveragePercent + '%';

        const coverageLegend = document.getElementById('dashboardCoverageLegend');
        coverageLegend.textContent = '';
        coverageLegend.appendChild(createLegendItem(numberFormatter.format(inspected) + ' inspected', '#269b8d'));
        coverageLegend.appendChild(createLegendItem(numberFormatter.format(remaining) + ' remaining', '#e9edf4'));
    }

    // Distribution uses current inventory, independent of inspection dates.
    function updateEquipmentDistribution(data) {
        const distribution = document.getElementById('dashboardDistribution');
        distribution.textContent = '';

        data.equipment.forEach(function (equipment) {
            const row = document.createElement('div');
            const heading = document.createElement('div');
            heading.className = 'dash-distribution-heading d-flex justify-content-between gap-3 mb-2';

            const label = document.createElement('strong');
            label.textContent = equipment.label;
            const count = document.createElement('span');
            count.textContent = numberFormatter.format(equipment.active);
            heading.append(label, count);

            const track = document.createElement('div');
            track.className = 'coverage-track progress';
            const bar = document.createElement('span');
            bar.className = 'progress-bar';
            bar.style.width = (data.totals.active ? equipment.active * 100 / data.totals.active : 0) + '%';
            bar.style.backgroundColor = equipment.color;
            track.appendChild(bar);

            row.append(heading, track);
            distribution.appendChild(row);
        });
    }

    // Reuse the bar chart instance when a filter changes.
    function updateMonthlyChart(data) {
        const labels = data.months.map(function (month) {
            return month.label;
        });
        const datasets = data.equipment.map(function (equipment) {
            return {
                label: equipment.label,
                data: equipment.monthly,
                backgroundColor: equipment.color,
                borderRadius: 5,
                borderSkipped: false,
                maxBarThickness: 36
            };
        });

        if (monthlyChart) {
            monthlyChart.data.labels = labels;
            monthlyChart.data.datasets = datasets;
            monthlyChart.update();
            return;
        }

        monthlyChart = new Chart(document.getElementById('monthlyInspectionChart'), {
            type: 'bar',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ' + numberFormatter.format(context.raw) + ' equipment';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { maxTicksLimit: 8, maxRotation: 0 }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { color: '#eef1f6' }
                    }
                }
            }
        });
    }

    // Coverage compares inspected and remaining active units for the selected dates.
    function updateCoverageChart(data) {
        const coverageValues = [data.totals.inspected, data.totals.not_inspected];
        if (coverageChart) {
            coverageChart.data.datasets[0].data = coverageValues;
            coverageChart.update();
            return;
        }

        coverageChart = new Chart(document.getElementById('dashboardCoverageChart'), {
            type: 'doughnut',
            data: {
                labels: ['Inspected', 'Not yet inspected'],
                datasets: [{
                    data: coverageValues,
                    backgroundColor: ['#269b8d', '#e9edf4'],
                    borderWidth: 0,
                    hoverOffset: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '78%',
                plugins: { legend: { display: false } }
            }
        });
    }

    // Keep the readable table available even if Chart.js could not load.
    function updateCharts(data) {
        updateChartLegends(data);
        updateEquipmentDistribution(data);
        updateMonthlyTable(data);

        if (typeof Chart === 'undefined') {
            document.getElementById('monthlyInspectionChart').hidden = true;
            document.getElementById('dashboardCoverageChart').hidden = true;
            document.getElementById('dashboardMonthlyLegend').appendChild(
                document.createTextNode('Chart unavailable. View monthly counts below.')
            );
            return;
        }

        updateMonthlyChart(data);
        updateCoverageChart(data);
    }

    // Apply one server response to every dashboard section.
    function renderDashboard(data, syncControls) {
        currentData = data;
        root.querySelectorAll('[data-summary]').forEach(function (element) {
            element.textContent = numberFormatter.format(data.totals[element.dataset.summary]);
        });
        document.getElementById('dashboardHistoryTotal').textContent = numberFormatter.format(data.totals.history_unique);
        document.getElementById('dashboardComparison').textContent = data.comparison.text;
        document.getElementById('dashboardUpdated').textContent = 'Updated ' + data.generated_at;
        document.getElementById('dashboardRangeLabel').textContent = data.filters.from + ' to ' + data.filters.to + ' | Manila time';
        document.getElementById('dashboardScopeLabel').textContent = data.filters.branch === 'all' ? 'All Branches' : data.filters.branch;
        if (syncControls) {
            period.value = data.filters.period;
            if (branch) {
                branch.value = data.filters.branch;
            }
            from.value = data.filters.from;
            to.value = data.filters.to;
            updateCustomDateFields();
        }
        updateUninspectedBreakdown(data);
        updateEquipmentCards(data);
        updateCharts(data);
    }

    // Send only the selected filters. The server enforces the user's branch access.
    function getDashboardParameters() {
        const params = new URLSearchParams();
        params.set('period', period.value);
        if (branch) {
            params.set('branch', branch.value);
        }
        if (period.value === 'custom') {
            params.set('from', from.value);
            params.set('to', to.value);
        }
        return params;
    }

    // Load new statistics without reloading the page.
    async function refreshDashboard() {
        if (expired) {
            return;
        }
        // Cancel an older request so rapid filter changes cannot show stale results.
        const currentRequestId = ++latestRequestId;
        if (pendingRequest) {
            pendingRequest.abort();
        }
        pendingRequest = null;
        root.classList.remove('is-loading');
        root.setAttribute('aria-busy', 'false');
        updateCustomDateFields();
        to.setCustomValidity(period.value === 'custom' && from.value && to.value && from.value > to.value ? 'End date must be on or after start date.' : '');
        showStatus('', false);
        if (!form.reportValidity()) {
            return;
        }
        const params = getDashboardParameters();
        const url = new URL(window.location.href);
        ['period', 'branch', 'from', 'to'].forEach(function (key) { url.searchParams.delete(key); });
        params.forEach(function (value, key) { url.searchParams.set(key, value); });
        window.history.replaceState({}, '', url.toString());
        pendingRequest = new AbortController();
        const timeout = setTimeout(function () {
            if (currentRequestId === latestRequestId && pendingRequest) {
                pendingRequest.abort();
            }
        }, 20000);
        root.classList.add('is-loading');
        root.setAttribute('aria-busy', 'true');
        showStatus('Updating dashboard...', false);
        try {
            const response = await fetch(endpoint + '?' + params.toString(), {
                credentials: 'same-origin',
                cache: 'no-store',
                signal: pendingRequest.signal
            });
            if (currentRequestId !== latestRequestId) {
                return;
            }
            if (response.status === 401 || response.redirected) {
                expired = true;
                signIn.hidden = false;
                showStatus('Your session has expired. Sign in again to refresh the dashboard.', true);
                return;
            }
            let result;
            try {
                result = await response.json();
            } catch (error) {
                throw new Error('Dashboard data could not be loaded. Please try again.');
            }
            if (currentRequestId !== latestRequestId) {
                return;
            }
            if (!response.ok || !result.success || !result.data) {
                throw new Error(result.message || 'Dashboard data could not be loaded.');
            }
            renderDashboard(result.data, true);
            showStatus('', false);
        } catch (error) {
            if (currentRequestId !== latestRequestId) {
                return;
            }
            let message = error.message;
            if (error.name === 'AbortError') {
                message = 'The request timed out. Please try again.';
            }
            if (currentData) {
                message += ' Showing the previous results.';
            }
            showStatus(message, true);
        } finally {
            clearTimeout(timeout);
            if (currentRequestId === latestRequestId) {
                pendingRequest = null;
                root.classList.remove('is-loading');
                root.setAttribute('aria-busy', 'false');
            }
        }
    }

    // Date and branch changes use the same request flow as the Refresh button.
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        refreshDashboard();
    });
    period.addEventListener('change', refreshDashboard);
    if (branch) {
        branch.addEventListener('change', refreshDashboard);
    }
    [from, to].forEach(function (input) {
        input.addEventListener('change', function () {
            if (from.value && to.value) {
                refreshDashboard();
            }
        });
    });
    // Restore filters when the browser navigates through dashboard URLs.
    window.addEventListener('popstate', function () {
        const params = new URLSearchParams(window.location.search);
        period.value = params.get('period') || 'this_month';
        if (branch) {
            branch.value = params.get('branch') || (currentData ? currentData.filters.branch : 'all');
        }
        from.value = params.get('from') || '';
        to.value = params.get('to') || '';
        refreshDashboard();
    });
    updateCustomDateFields();
    // The first page load already includes data, so avoid a duplicate request.
    const initialResponse = JSON.parse(document.getElementById('dashboardBootstrap').textContent);
    if (initialResponse.data) {
        renderDashboard(initialResponse.data, true);
    } else if (!initialResponse.error) {
        refreshDashboard();
    }
});
