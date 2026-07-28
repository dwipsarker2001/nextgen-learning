(function () {
    if (typeof Chart === 'undefined' || !window.dashboardChartData) {
        return;
    }

    var data = window.dashboardChartData;

    var ink = {
        surface: '#fcfcfb',
        primary: '#0b0b0b',
        secondary: '#52514e',
        muted: '#898781',
        grid: '#e1e0d9',
    };

    var seq = {
        blue: '#2a78d6',
        orange: '#eb6834',
    };

    var categorical = ['#2a78d6', '#eb6834', '#1baf7a', '#eda100'];

    var status = {
        success: { color: '#0ca30c', label: 'Success' },
        pending: { color: '#fab219', label: 'Pending' },
        cancel: { color: '#d03b3b', label: 'Cancelled' },
    };

    Chart.defaults.font.family = "'Heebo', 'Roboto', system-ui, -apple-system, sans-serif";
    Chart.defaults.color = ink.muted;

    function hexToRgba(hex, alpha) {
        var r = parseInt(hex.slice(1, 3), 16);
        var g = parseInt(hex.slice(3, 5), 16);
        var b = parseInt(hex.slice(5, 7), 16);
        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
    }

    // Single-series trend line (used for both enrollment and revenue trend, only the hue differs).
    function renderTrendChart(canvasId, chartData, hex, valuePrefix) {
        var el = document.getElementById(canvasId);
        if (!el) {
            return;
        }

        new Chart(el, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    data: chartData.data,
                    borderColor: hex,
                    backgroundColor: hexToRgba(hex, 0.1),
                    borderWidth: 2,
                    tension: 0.35,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: hex,
                    pointBorderColor: ink.surface,
                    pointBorderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: ink.primary,
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        padding: 10,
                        cornerRadius: 6,
                        displayColors: false,
                        callbacks: {
                            label: function (ctx) {
                                var v = ctx.parsed.y;
                                return valuePrefix ? valuePrefix + v.toLocaleString() : v.toLocaleString();
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: ink.muted },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: ink.grid, drawBorder: false },
                        ticks: {
                            color: ink.muted,
                            precision: 0,
                            callback: function (v) {
                                return valuePrefix ? valuePrefix + v.toLocaleString() : v.toLocaleString();
                            },
                        },
                    },
                },
            },
        });
    }

    // Horizontal bar: course popularity, single series.
    function renderPopularityChart(canvasId, chartData) {
        var el = document.getElementById(canvasId);
        if (!el) {
            return;
        }

        new Chart(el, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    data: chartData.data,
                    backgroundColor: seq.blue,
                    borderRadius: 4,
                    maxBarThickness: 22,
                    categoryPercentage: 0.7,
                    barPercentage: 0.8,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: ink.primary,
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        padding: 10,
                        cornerRadius: 6,
                        displayColors: false,
                        callbacks: {
                            label: function (ctx) {
                                return ctx.parsed.x + ' enrollment' + (ctx.parsed.x === 1 ? '' : 's');
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: ink.grid, drawBorder: false },
                        ticks: { color: ink.muted, precision: 0 },
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: ink.secondary },
                    },
                },
            },
        });
    }

    // Donut: enrollment status breakdown, using reserved status colors (never generic categorical).
    function renderStatusChart(canvasId, counts) {
        var el = document.getElementById(canvasId);
        if (!el) {
            return;
        }

        var keys = Object.keys(status).filter(function (k) {
            return counts[k] > 0;
        });
        if (keys.length === 0) {
            keys = Object.keys(status);
        }

        new Chart(el, {
            type: 'doughnut',
            data: {
                labels: keys.map(function (k) { return status[k].label; }),
                datasets: [{
                    data: keys.map(function (k) { return counts[k]; }),
                    backgroundColor: keys.map(function (k) { return status[k].color; }),
                    borderColor: ink.surface,
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: ink.secondary, usePointStyle: true, boxWidth: 8, padding: 14 },
                    },
                    tooltip: {
                        backgroundColor: ink.primary,
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        padding: 10,
                        cornerRadius: 6,
                        callbacks: {
                            label: function (ctx) {
                                var total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                var pct = total ? Math.round((ctx.parsed / total) * 100) : 0;
                                return ' ' + ctx.parsed + ' (' + pct + '%)';
                            },
                        },
                    },
                },
            },
        });
    }

    // Radar: top courses compared across normalized metrics, fixed categorical order.
    function renderRadarChart(canvasId, radarData) {
        var el = document.getElementById(canvasId);
        if (!el) {
            return;
        }

        var datasets = radarData.datasets.map(function (d, i) {
            var hex = categorical[i % categorical.length];
            return {
                label: d.title,
                data: d.values,
                borderColor: hex,
                backgroundColor: hexToRgba(hex, 0.12),
                pointBackgroundColor: hex,
                pointBorderColor: ink.surface,
                pointBorderWidth: 2,
                pointRadius: 4,
                borderWidth: 2,
            };
        });

        new Chart(el, {
            type: 'radar',
            data: { labels: radarData.metrics, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: ink.secondary, usePointStyle: true, boxWidth: 8, padding: 14 },
                    },
                    tooltip: {
                        backgroundColor: ink.primary,
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        padding: 10,
                        cornerRadius: 6,
                        callbacks: {
                            label: function (ctx) {
                                return ctx.dataset.label + ': ' + ctx.parsed.r + '%';
                            },
                        },
                    },
                },
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        angleLines: { color: ink.grid },
                        grid: { color: ink.grid },
                        pointLabels: { color: ink.secondary, font: { size: 12 } },
                        ticks: { display: false },
                    },
                },
            },
        });
    }

    renderTrendChart('enrollmentTrendChart', data.enrollmentTrend, seq.blue, '');
    renderTrendChart('revenueTrendChart', data.revenueTrend, seq.orange, '৳');
    renderPopularityChart('coursePopularityChart', data.coursePopularity);
    renderStatusChart('enrollmentStatusChart', data.enrollmentStatus);
    renderRadarChart('coursePerformanceRadar', data.coursePerformance);
})();
