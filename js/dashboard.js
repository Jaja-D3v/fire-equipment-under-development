document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    const root = document.getElementById('equipmentDashboard');
    const form = document.getElementById('dashboardFilters');
    if (!root || !form) return;
    const period = document.getElementById('dashboardPeriod');
    const branch = document.getElementById('dashboardBranch');
    const from = document.getElementById('dashboardFrom');
    const to = document.getElementById('dashboardTo');
    const status = document.getElementById('dashboardStatus');
    const signIn = document.getElementById('dashboardSignIn');
    const number = new Intl.NumberFormat('en-US');
    let currentData = null;
    let requestId = 0;
    let pending = null;
    let monthlyChart = null;
    let coverageChart = null;
    let expired = false;

    function showStatus(message, error) {
        status.textContent = message;
        status.hidden = !message;
        status.classList.toggle('is-error', Boolean(error));
    }

    function updateCustomFields() {
        const custom = period.value === 'custom';
        document.getElementById('dashboardCustomDates').hidden = !custom;
        from.required = custom;
        to.required = custom;
        to.min = from.value || '';
    }

    function legendItem(label, color) {
        const item = document.createElement('span');
        item.className = 'dash-legend-item d-inline-flex align-items-center gap-2';
        const dot = document.createElement('span');
        dot.className = 'dash-legend-dot d-inline-block flex-shrink-0';
        dot.style.backgroundColor = color;
        item.appendChild(dot);
        item.appendChild(document.createTextNode(label));
        return item;
    }

    function updateEquipment(data) {
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
                card.querySelector('[data-module-value="' + key + '"]').textContent = number.format(equipment[key]);
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
                cell.textContent = number.format(equipment.monthly[index]);
                row.appendChild(cell);
            });
            body.appendChild(row);
        });
        table.appendChild(body);
        const container = document.getElementById('dashboardMonthlyTable');
        container.textContent = '';
        container.appendChild(table);
    }

    function updateCharts(data) {
        const legend = document.getElementById('dashboardMonthlyLegend');
        legend.textContent = '';
        data.equipment.forEach(function (item) { legend.appendChild(legendItem(item.label, item.color)); });
        const covered = data.totals.inspected;
        const remaining = data.totals.not_inspected;
        document.getElementById('dashboardCoveragePercent').textContent = (data.totals.active ? Math.round(covered * 100 / data.totals.active) : 0) + '%';
        const coverageLegend = document.getElementById('dashboardCoverageLegend');
        coverageLegend.textContent = '';
        coverageLegend.appendChild(legendItem(number.format(covered) + ' inspected', '#269b8d'));
        coverageLegend.appendChild(legendItem(number.format(remaining) + ' remaining', '#e9edf4'));
        const distribution = document.getElementById('dashboardDistribution');
        distribution.textContent = '';
        data.equipment.forEach(function (item) {
            const row = document.createElement('div');
            const heading = document.createElement('div');
            heading.className = 'dash-distribution-heading d-flex justify-content-between gap-3 mb-2';
            const label = document.createElement('strong');
            label.textContent = item.label;
            const value = document.createElement('span');
            value.textContent = number.format(item.active);
            heading.appendChild(label);
            heading.appendChild(value);
            const track = document.createElement('div');
            track.className = 'coverage-track progress';
            const bar = document.createElement('span');
            bar.className = 'progress-bar';
            bar.style.width = (data.totals.active ? item.active * 100 / data.totals.active : 0) + '%';
            bar.style.backgroundColor = item.color;
            track.appendChild(bar);
            row.appendChild(heading);
            row.appendChild(track);
            distribution.appendChild(row);
        });
        updateMonthlyTable(data);
        if (typeof Chart === 'undefined') {
            document.getElementById('monthlyInspectionChart').hidden = true;
            document.getElementById('dashboardCoverageChart').hidden = true;
            legend.appendChild(document.createTextNode('Chart unavailable. View monthly counts below.'));
            return;
        }
        const labels = data.months.map(function (month) { return month.label; });
        const datasets = data.equipment.map(function (item) {
            return { label: item.label, data: item.monthly, backgroundColor: item.color, borderRadius: 5, borderSkipped: false, maxBarThickness: 36 };
        });
        if (monthlyChart) {
            monthlyChart.data.labels = labels;
            monthlyChart.data.datasets = datasets;
            monthlyChart.update();
        } else {
            monthlyChart = new Chart(document.getElementById('monthlyInspectionChart'), {
                type: 'bar', data: { labels: labels, datasets: datasets },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (context) { return context.dataset.label + ': ' + number.format(context.raw) + ' equipment'; } } } },
                    scales: { x: { grid: { display: false }, ticks: { maxTicksLimit: 8, maxRotation: 0 } }, y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef1f6' } } }
                }
            });
        }
        const coverageValues = [covered, remaining];
        if (coverageChart) {
            coverageChart.data.datasets[0].data = coverageValues;
            coverageChart.update();
        } else {
            coverageChart = new Chart(document.getElementById('dashboardCoverageChart'), {
                type: 'doughnut',
                data: { labels: ['Inspected', 'Not yet inspected'], datasets: [{ data: coverageValues, backgroundColor: ['#269b8d', '#e9edf4'], borderWidth: 0, hoverOffset: 2 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '78%', plugins: { legend: { display: false } } }
            });
        }
    }

    function render(data, syncControls) {
        currentData = data;
        root.querySelectorAll('[data-summary]').forEach(function (element) {
            element.textContent = number.format(data.totals[element.dataset.summary]);
        });
        document.getElementById('dashboardHistoryTotal').textContent = number.format(data.totals.history_unique);
        document.getElementById('dashboardComparison').textContent = data.comparison.text;
        document.getElementById('dashboardUpdated').textContent = 'Updated ' + data.generated_at;
        document.getElementById('dashboardRangeLabel').textContent = data.filters.from + ' to ' + data.filters.to + ' | Manila time';
        document.getElementById('dashboardScopeLabel').textContent = data.filters.branch === 'all' ? 'All Branches' : data.filters.branch;
        if (syncControls) {
            period.value = data.filters.period;
            if (branch) branch.value = data.filters.branch;
            from.value = data.filters.from;
            to.value = data.filters.to;
            updateCustomFields();
        }
        updateEquipment(data);
        updateCharts(data);
    }

    function parameters() {
        const params = new URLSearchParams();
        params.set('period', period.value);
        if (branch) params.set('branch', branch.value);
        if (period.value === 'custom') {
            params.set('from', from.value);
            params.set('to', to.value);
        }
        return params;
    }

    async function refresh() {
        if (expired) return;
        const id = ++requestId;
        if (pending) pending.abort();
        pending = null;
        root.classList.remove('is-loading');
        root.setAttribute('aria-busy', 'false');
        updateCustomFields();
        to.setCustomValidity(period.value === 'custom' && from.value && to.value && from.value > to.value ? 'End date must be on or after start date.' : '');
        showStatus('', false);
        if (!form.reportValidity()) return;
        const params = parameters();
        const url = new URL(window.location.href);
        ['period', 'branch', 'from', 'to'].forEach(function (key) { url.searchParams.delete(key); });
        params.forEach(function (value, key) { url.searchParams.set(key, value); });
        window.history.replaceState({}, '', url.toString());
        pending = new AbortController();
        const timeout = setTimeout(function () { if (id === requestId && pending) pending.abort(); }, 20000);
        root.classList.add('is-loading');
        root.setAttribute('aria-busy', 'true');
        showStatus('Updating dashboard...', false);
        try {
            const response = await fetch('backend/controller/DashboardDataController.php?' + params.toString(), {
                credentials: 'same-origin', cache: 'no-store', signal: pending.signal
            });
            if (id !== requestId) return;
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
            if (id !== requestId) return;
            if (!response.ok || !result.success || !result.data) throw new Error(result.message || 'Dashboard data could not be loaded.');
            render(result.data, true);
            showStatus('', false);
        } catch (error) {
            if (id !== requestId) return;
            showStatus((error.name === 'AbortError' ? 'The request timed out. Please try again.' : error.message) + (currentData ? ' Showing the previous results.' : ''), true);
        } finally {
            clearTimeout(timeout);
            if (id === requestId) {
                pending = null;
                root.classList.remove('is-loading');
                root.setAttribute('aria-busy', 'false');
            }
        }
    }

    form.addEventListener('submit', function (event) { event.preventDefault(); refresh(); });
    period.addEventListener('change', refresh);
    if (branch) branch.addEventListener('change', refresh);
    [from, to].forEach(function (input) {
        input.addEventListener('change', function () { if (from.value && to.value) refresh(); });
    });
    window.addEventListener('popstate', function () {
        const params = new URLSearchParams(window.location.search);
        period.value = params.get('period') || 'this_month';
        if (branch) branch.value = params.get('branch') || (currentData ? currentData.filters.branch : 'all');
        from.value = params.get('from') || '';
        to.value = params.get('to') || '';
        refresh();
    });
    updateCustomFields();
    const bootstrap = JSON.parse(document.getElementById('dashboardBootstrap').textContent);
    if (bootstrap.data) render(bootstrap.data, true);
    else if (!bootstrap.error) refresh();
});
