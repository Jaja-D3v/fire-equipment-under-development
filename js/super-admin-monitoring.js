(function () {
  "use strict";
  var page = 1;
  var userPage = 1;
  var chartTrend = null;
  var chartStatus = null;
  var chartBranch = null;
  var chartInspectionTrend = null;
  var chartEquipment = null;
  var chartTopUsers = null;
  var chartWeekly = null;
  var chartMonthly = null;
  var debounceTimer = null;
  var range = document.getElementById("saRange");
  var from = document.getElementById("saFrom");
  var to = document.getElementById("saTo");
  var notice = document.getElementById("saNotice");
  function escapeHtml(value) {
    return String(value == null ? "" : value).replace(
      /[&<>"']/g,
      function (char) {
        return {
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#039;",
        }[char];
      },
    );
  }
  function value(id, number) {
    document.getElementById(id).textContent =
      number === null || number === undefined
        ? "N/A"
        : Number(number).toLocaleString();
  }
  function renderRows(id, rows, fields, emptyText) {
    var body = document.getElementById(id);
    if (!rows || !rows.length) {
      body.innerHTML =
        '<tr><td colspan="' +
        fields.length +
        '" class="text-center text-body-secondary py-4">' +
        escapeHtml(emptyText) +
        "</td></tr>";
      return;
    }
    body.innerHTML = rows
      .map(function (row) {
        return (
          "<tr>" +
          fields
            .map(function (field) {
              var cell = field(row);
              if (cell && typeof cell === "object") {
                return '<td><span class="sa-status-badge ' + escapeHtml(cell.variant || "neutral") + '">' + escapeHtml(cell.label) + "</span></td>";
              }
              return "<td>" + escapeHtml(cell) + "</td>";
            })
            .join("") +
          "</tr>"
        );
      })
      .join("");
  }
  function renderPager(prefix, currentPage, total, pageSize) {
    var totalPages = total === null ? null : Math.max(1, Math.ceil(total / pageSize));
    var pages = document.getElementById(prefix + "Pages");
    var info = document.getElementById(prefix + "PageInfo");
    var start = totalPages === null ? currentPage : Math.max(1, Math.min(currentPage - 2, totalPages - 4));
    var end = totalPages === null ? currentPage : Math.min(totalPages, start + 4);
    var buttons = "";
    for (var pageNumber = start; pageNumber <= end; pageNumber += 1) {
      var isCurrent = pageNumber === currentPage;
      buttons += '<button type="button" class="sa-page-number' + (isCurrent ? ' is-current' : '') + '" data-page="' + pageNumber + '"' + (isCurrent ? ' aria-current="page"' : '') + ' aria-label="Page ' + pageNumber + '">' + pageNumber + '</button>';
    }
    pages.innerHTML = buttons;
    info.textContent = totalPages === null ? "Page " + currentPage : "Page " + currentPage + " of " + totalPages;
  }
  function setChartState(canvasId, hasData) {
    var canvas = document.getElementById(canvasId);
    if (canvas && canvas.parentElement) {
      canvas.parentElement.classList.toggle("has-no-data", !hasData);
    }
  }
  function updateFilterStates() {
    ["saUserSearch", "saBranchFilter", "saRoleFilter", "saSearch", "saUserFilter", "saActionFilter", "saModuleFilter"].forEach(function (id) {
      var input = document.getElementById(id);
      if (input) {
        input.classList.toggle("is-active-filter", String(input.value || "").trim() !== "");
      }
    });
  }
  function load() {
    var params = new URLSearchParams({
      range: range.value,
      page: String(page),
      user_page: String(userPage),
      limit: "10",
      search: document.getElementById("saSearch").value,
      user: document.getElementById("saUserFilter").value,
      action: document.getElementById("saActionFilter").value,
      module: document.getElementById("saModuleFilter").value,
      user_search: document.getElementById("saUserSearch").value,
      branch: document.getElementById("saBranchFilter").value,
      role: document.getElementById("saRoleFilter").value,
    });
    if (range.value === "custom") {
      params.set("from", from.value);
      params.set("to", to.value);
    }
    updateFilterStates();
    notice.className = "sa-feedback is-loading";
    notice.textContent = "Loading monitoring data…";
    fetch(
      "backend/controller/SuperAdminMonitoringEndpoint.php?" +
        params.toString(),
      { credentials: "same-origin", headers: { Accept: "application/json" } },
    )
      .then(function (response) {
        return response.json().then(function (body) {
          if (!response.ok || !body.success) {
            throw new Error(body.message || "Data unavailable");
          }
          return body.data;
        });
      })
      .then(function (data) {
        var overview = data.overview || {};
        var inspections = data.inspections || {};
        value("saUsers", overview.registered_users);
        value("saActive", overview.active_users);
        value("saInspections", inspections.submitted);
        value("saBranches", overview.active_branches);
        value("saSubmitted", inspections.submitted);
        value("saApproved", inspections.approved);
        value("saPending", inspections.pending);
        value("saRejected", inspections.rejected);
        document.getElementById("saStatusNote").textContent =
          inspections.approval_status_note || "Approval status unavailable.";
        renderRows(
          "saBranchRows",
          data.branches,
          [
            function (r) {
              return r.branch;
            },
            function (r) {
              return r.users === null ? "N/A" : r.users;
            },
            function () {
              return "N/A (logs have no branch)";
            },
            function (r) {
              return r.inspections || 0;
            },
            function (r) {
              return r.last_activity || "N/A";
            },
            function (r) {
              return Number(r.inspections || 0) > 0
                ? { label: "Inspection recorded", variant: "positive" }
                : { label: "No inspection recorded", variant: "neutral" };
            },
          ],
          "No branch records for this range.",
        );
        renderRows(
          "saUserRows",
          data.users.rows,
          [
            function (r) {
              return r.employee_name;
            },
            function (r) {
              return r.login_id;
            },
            function (r) {
              return r.role;
            },
            function (r) {
              return r.branch || "N/A";
            },
            function (r) {
              return r.last_login || "N/A";
            },
            function (r) {
              return r.last_activity || "N/A";
            },
            function (r) {
              return r.activity_count === null
                ? "N/A (ambiguous name)"
                : r.activity_count;
            },
          ],
          "No matching user records.",
        );
        var userTotal = data.users.total;
        document.getElementById("saUserCount").textContent =
          userTotal === null
            ? "Total: N/A"
            : "Page " +
              userPage +
              " · " +
              userTotal.toLocaleString() +
              " users";
        document.getElementById("saUserCount").textContent =
          userTotal === null ? "Users: N/A" : userTotal.toLocaleString() + " users";
        renderPager("saUser", userPage, userTotal, 10);
        document.getElementById("saUserPrev").disabled = userPage <= 1;
        document.getElementById("saUserNext").disabled =
          userTotal === null || userPage * 10 >= userTotal;
        function updateOptions(id, rows, selected) {
          var select = document.getElementById(id);
          var current = select.value;
          rows.forEach(function (row) {
            var option = Array.prototype.find.call(
              select.options,
              function (item) {
                return item.value === row.value;
              },
            );
            if (!option) {
              option = document.createElement("option");
              option.value = row.value;
              option.textContent = row.value;
              select.appendChild(option);
            }
          });
          if (selected !== undefined && !current) {
            select.value = selected;
          }
        }
        updateOptions("saBranchFilter", data.user_filters.branches);
        updateOptions("saRoleFilter", data.user_filters.roles);
        renderRows(
          "saLogRows",
          data.logs.rows,
          [
            function (r) {
              return r.user_name;
            },
            function (r) {
              return r.action;
            },
            function (r) {
              return r.description;
            },
            function (r) {
              return r.created_at;
            },
          ],
          "No activity log records for this range.",
        );
        var total = data.logs.total;
        document.getElementById("saLogCount").textContent =
          total === null
            ? "Total: N/A"
            : "Page " + page + " · " + total.toLocaleString() + " records";
        document.getElementById("saLogCount").textContent =
          total === null ? "Records: N/A" : total.toLocaleString() + " records";
        renderPager("saLog", page, total, 10);
        document.getElementById("saPrev").disabled = page <= 1;
        document.getElementById("saNext").disabled =
          total === null || page * 10 >= total;
        if (window.Chart) {
          var isDarkTheme = document.documentElement.getAttribute("data-coreui-theme") === "dark";
          window.Chart.defaults.color = isDarkTheme ? "#b9c8d9" : "#77869a";
          window.Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
          window.Chart.defaults.font.size = 11;
          window.Chart.defaults.plugins.legend.labels.usePointStyle = true;
          window.Chart.defaults.plugins.legend.labels.boxWidth = 8;
          window.Chart.defaults.plugins.legend.labels.padding = 16;
          window.Chart.defaults.plugins.tooltip.backgroundColor = "#20334a";
          window.Chart.defaults.plugins.tooltip.cornerRadius = 9;
          window.Chart.defaults.plugins.tooltip.padding = 10;
          window.Chart.defaults.scale.grid.color = isDarkTheme ? "rgba(185, 200, 217, .12)" : "#edf1f5";
          window.Chart.defaults.elements.line.borderWidth = 2;
          var trend = data.trend || [];
          setChartState("saTrendChart", trend.length > 0);
          if (chartTrend) {
            chartTrend.destroy();
          }
          chartTrend = new Chart(document.getElementById("saTrendChart"), {
            type: "line",
            data: {
              labels: trend.map(function (r) {
                return r.activity_date;
              }),
              datasets: [
                {
                  label: "Activity log entries",
                  data: trend.map(function (r) {
                    return Number(r.log_total);
                  }),
                  borderColor: "#2878d0",
                  backgroundColor: "rgba(40,120,208,.08)",
                  fill: true,
                  tension: 0.32,
                  pointRadius: 2,
                },
                {
                  label: "Inspection submissions",
                  data: trend.map(function (r) {
                    return Number(r.inspection_total);
                  }),
                  borderColor: "#45a47b",
                  backgroundColor: "rgba(69,164,123,.05)",
                  fill: false,
                  tension: 0.32,
                  pointRadius: 2,
                },
              ],
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { position: "bottom" } },
              scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
          });
          function periodChart(canvas, rows, key, current) {
            setChartState(canvas, rows && rows.length > 0);
            if (current) {
              current.destroy();
            }
            return new Chart(document.getElementById(canvas), {
              type: "bar",
              data: {
                labels: (rows || []).map(function (r) {
                  return r.period_key;
                }),
                datasets: [
                  {
                    label: "Activity log entries",
                    data: (rows || []).map(function (r) {
                      return Number(r.log_total);
                    }),
                    backgroundColor: "#78a9e8",
                  },
                  {
                    label: "Inspection submissions",
                    data: (rows || []).map(function (r) {
                      return Number(r.inspection_total);
                    }),
                    backgroundColor: "#72ba98",
                  },
                ],
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: "bottom" } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
              },
            });
          }
          chartWeekly = periodChart(
            "saWeeklyChart",
            data.weekly_trend,
            "period_key",
            chartWeekly,
          );
          chartMonthly = periodChart(
            "saMonthlyChart",
            data.monthly_activity_trend,
            "period_key",
            chartMonthly,
          );
          if (chartStatus) {
            chartStatus.destroy();
          }
          setChartState("saStatusChart", Number(inspections.approved || 0) + Number(inspections.pending || 0) + Number(inspections.rejected || 0) > 0);
          chartStatus = new Chart(document.getElementById("saStatusChart"), {
            type: "doughnut",
            data: {
              labels: ["Approved", "Pending", "Rejected"],
              datasets: [
                {
                  data: [
                    inspections.approved || 0,
                    inspections.pending || 0,
                    inspections.rejected || 0,
                  ],
                  backgroundColor: ["#45a47b", "#e5ad45", "#d86a71"],
                  borderWidth: 0,
                },
              ],
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { position: "bottom" } },
            },
          });
          if (chartBranch) {
            chartBranch.destroy();
          }
          setChartState("saBranchChart", (data.branches || []).some(function (row) { return Number(row.inspections || 0) > 0; }));
          chartBranch = new Chart(document.getElementById("saBranchChart"), {
            type: "bar",
            data: {
              labels: (data.branches || []).map(function (r) {
                return r.branch;
              }),
              datasets: [
                {
                  label: "Inspection submissions",
                  data: (data.branches || []).map(function (r) {
                    return Number(r.inspections || 0);
                  }),
                  backgroundColor: "#78a9e8",
                  borderRadius: 6,
                },
              ],
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              indexAxis: "y",
              plugins: { legend: { display: false } },
              scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
            },
          });
          if (chartInspectionTrend) {
            chartInspectionTrend.destroy();
          }
          setChartState("saInspectionTrend", (data.inspection_trend || []).length > 0);
          chartInspectionTrend = new Chart(
            document.getElementById("saInspectionTrend"),
            {
              type: "line",
              data: {
                labels: (data.inspection_trend || []).map(function (r) {
                  return r.month_key;
                }),
                datasets: [
                  {
                    label: "Submissions",
                    data: (data.inspection_trend || []).map(function (r) {
                      return Number(r.total);
                    }),
                    borderColor: "#45a47b",
                    backgroundColor: "rgba(69,164,123,.10)",
                    fill: true,
                    tension: 0.3,
                  },
                ],
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
              },
            },
          );
          if (chartEquipment) {
            chartEquipment.destroy();
          }
          setChartState("saEquipmentChart", (data.equipment_types || []).some(function (row) { return Number(row.total || 0) > 0; }));
          chartEquipment = new Chart(
            document.getElementById("saEquipmentChart"),
            {
              type: "bar",
              data: {
                labels: (data.equipment_types || []).map(function (r) {
                  return r.equipment_type;
                }),
                datasets: [
                  {
                    label: "Inspections",
                    data: (data.equipment_types || []).map(function (r) {
                      return Number(r.total);
                    }),
                    backgroundColor: "#91b7e8",
                    borderRadius: 6,
                  },
                ],
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
              },
            },
          );
          if (chartTopUsers) {
            chartTopUsers.destroy();
          }
          setChartState("saTopUsersChart", (data.top_users || []).length > 0);
          chartTopUsers = new Chart(
            document.getElementById("saTopUsersChart"),
            {
              type: "bar",
              data: {
                labels: (data.top_users || []).map(function (r) {
                  return r.user_name;
                }),
                datasets: [
                  {
                    label: "Activity log entries",
                    data: (data.top_users || []).map(function (r) {
                      return Number(r.total);
                    }),
                    backgroundColor: "#9bbce8",
                    borderRadius: 6,
                  },
                ],
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: "y",
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
              },
            },
          );
        }
        notice.className = "sa-feedback is-ready";
        notice.textContent =
          "Showing recorded data from " +
          data.range.start +
          " through the selected date range. User activity coverage depends on existing logs.";
      })
      .catch(function (error) {
        notice.className = "sa-feedback is-error";
        notice.textContent =
          error.message || "Monitoring data is temporarily unavailable.";
      });
  }
  range.addEventListener("change", function () {
    from.classList.toggle("d-none", range.value !== "custom");
    to.classList.toggle("d-none", range.value !== "custom");
    page = 1;
    userPage = 1;
    load();
  });
  [from, to].forEach(function (input) {
    input.addEventListener("change", function () {
      page = 1;
      load();
    });
  });
  ["saSearch", "saUserFilter", "saActionFilter", "saModuleFilter"].forEach(
    function (id) {
      document.getElementById(id).addEventListener("input", function () {
        window.clearTimeout(debounceTimer);
        page = 1;
        debounceTimer = window.setTimeout(load, 250);
      });
    },
  );
  ["saUserSearch", "saBranchFilter", "saRoleFilter"].forEach(function (id) {
    document
      .getElementById(id)
      .addEventListener(
        id === "saUserSearch" ? "input" : "change",
        function () {
          window.clearTimeout(debounceTimer);
          userPage = 1;
          debounceTimer = window.setTimeout(load, 250);
        },
      );
  });
  document.getElementById("saPrev").addEventListener("click", function () {
    page = Math.max(1, page - 1);
    load();
  });
  document.getElementById("saNext").addEventListener("click", function () {
    if (!document.getElementById("saNext").disabled) {
      page += 1;
      load();
    }
  });
  document.getElementById("saUserPrev").addEventListener("click", function () {
    userPage = Math.max(1, userPage - 1);
    load();
  });
  document.getElementById("saUserNext").addEventListener("click", function () {
    if (!document.getElementById("saUserNext").disabled) {
      userPage += 1;
      load();
    }
  });
  document.getElementById("saClearUserFilters").addEventListener("click", function () {
    document.getElementById("saUserSearch").value = "";
    document.getElementById("saBranchFilter").value = "";
    document.getElementById("saRoleFilter").value = "";
    userPage = 1;
    load();
  });
  document.getElementById("saClearLogFilters").addEventListener("click", function () {
    ["saSearch", "saUserFilter", "saActionFilter", "saModuleFilter"].forEach(function (id) {
      document.getElementById(id).value = "";
    });
    page = 1;
    load();
  });
  document.getElementById("saLogPages").addEventListener("click", function (event) {
    var button = event.target.closest("button[data-page]");
    if (button) {
      page = Number(button.getAttribute("data-page"));
      load();
    }
  });
  document.getElementById("saUserPages").addEventListener("click", function (event) {
    var button = event.target.closest("button[data-page]");
    if (button) {
      userPage = Number(button.getAttribute("data-page"));
      load();
    }
  });
  load();
})();
