document.addEventListener("DOMContentLoaded", function () {

    const canvas = document.getElementById("monthlyInspectionChart");
    if (!canvas) return;
    const ctx = canvas.getContext("2d");

    new Chart(ctx, {

        type: "bar",

        data: {

            labels: labels,

            datasets: [{
                label: "Inspections",
                data: values,
                borderRadius: 8,
                borderSkipped: false,
                maxBarThickness: 55,

                backgroundColor: function (context) {

                    const chart = context.chart;
                    const {
                        ctx,
                        chartArea
                    } = chart;

                    if (!chartArea) {
                        return "#dc3545";
                    }

                    const gradient = ctx.createLinearGradient(
                        0,
                        chartArea.top,
                        0,
                        chartArea.bottom
                    );

                    gradient.addColorStop(0, "#dc3545");
                    gradient.addColorStop(1, "#ff6b6b");

                    return gradient;
                }

            }]

        },


        options: {

            responsive: true,

            maintainAspectRatio: false,

            interaction: {
                mode: "index",
                intersect: false
            },


            plugins: {

                legend: {
                    display: false
                },

                tooltip: {

                    backgroundColor: "#212529",
                    padding: 12,
                    displayColors: false,
                    callbacks: {

                        title: function (context) {

                            return context[0].label;

                        },

                        label: function (context) {

                            return " " + context.raw + " inspections";

                        }

                    }

                }

            },


            scales: {

                x: {

                    grid: {
                        display: false
                    },

                    border: {
                        display: false
                    },

                    ticks: {

                        font: {
                            size: 12,
                            weight: "500"
                        }

                    }

                },


                y: {

                    beginAtZero: true,

                    border: {
                        display: false
                    },

                    grid: {

                        color: "rgba(0, 0, 0, 0.06)",

                        drawTicks: false

                    },

                    ticks: {

                        precision: 0,

                        padding: 10

                    }

                }

            }

        }

    });

});

// for date format 

document.addEventListener('DOMContentLoaded', function () {

    const today = new Date();

    const formattedDate = today.toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    });

    document.getElementById('analyticsDate').textContent = formattedDate;

});