// ============================================
// Smart Livestock Management System
// Chart Visualization JavaScript
// ============================================

/**
 * Initialize and display various charts
 * Using Chart.js library
 */

// Store chart instances for later updates
let chartInstances = {};

/**
 * Create milk production trend chart
 * @param {Array} dates - Array of dates
 * @param {Array} quantities - Array of milk quantities
 */
function createMilkTrendChart(dates, quantities) {
    const ctx = document.getElementById('milkChart');
    
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (chartInstances.milkChart) {
        chartInstances.milkChart.destroy();
    }
    
    chartInstances.milkChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates || [],
            datasets: [{
                label: 'Daily Milk Production (Liters)',
                data: quantities || [],
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#27ae60',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                },
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Liters'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            }
        }
    });
}

/**
 * Create animal type distribution chart
 * @param {Array} types - Animal types
 * @param {Array} counts - Count for each type
 */
function createAnimalTypeChart(types, counts) {
    const ctx = document.getElementById('animalTypeChart');
    
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (chartInstances.animalTypeChart) {
        chartInstances.animalTypeChart.destroy();
    }
    
    const colors = ['#2c3e50', '#27ae60', '#e74c3c', '#f39c12', '#3498db'];
    
    chartInstances.animalTypeChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: types || [],
            datasets: [{
                data: counts || [],
                backgroundColor: colors,
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 12
                        },
                        padding: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Create health status distribution chart
 * @param {Array} statuses - Health statuses
 * @param {Array} counts - Count for each status
 */
function createHealthStatusChart(statuses, counts) {
    const ctx = document.getElementById('healthStatusChart');
    
    if (!ctx) return;
    
    if (chartInstances.healthStatusChart) {
        chartInstances.healthStatusChart.destroy();
    }
    
    const statusColors = {
        'healthy': '#27ae60',
        'sick': '#e74c3c',
        'recovering': '#f39c12',
        'under_observation': '#3498db'
    };
    
    const colors = statuses.map(status => statusColors[status] || '#95a5a6');
    
    chartInstances.healthStatusChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: statuses || [],
            datasets: [{
                label: 'Number of Animals',
                data: counts || [],
                backgroundColor: colors,
                borderColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: 'x',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Count'
                    }
                }
            }
        }
    });
}

/**
 * Create supplement nutrition comparison chart
 * @param {Array} supplements - Supplement names
 * @param {Array} proteins - Protein content
 * @param {Array} fats - Fat content
 * @param {Array} minerals - Mineral content
 */
function createSupplementChart(supplements, proteins, fats, minerals) {
    const ctx = document.getElementById('supplementChart');
    
    if (!ctx) return;
    
    if (chartInstances.supplementChart) {
        chartInstances.supplementChart.destroy();
    }
    
    chartInstances.supplementChart = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: supplements || [],
            datasets: [
                {
                    label: 'Protein %',
                    data: proteins || [],
                    borderColor: '#2c3e50',
                    backgroundColor: 'rgba(44, 62, 80, 0.2)',
                    borderWidth: 2
                },
                {
                    label: 'Fat %',
                    data: fats || [],
                    borderColor: '#27ae60',
                    backgroundColor: 'rgba(39, 174, 96, 0.2)',
                    borderWidth: 2
                },
                {
                    label: 'Minerals %',
                    data: minerals || [],
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.2)',
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

/**
 * Create age distribution chart
 * @param {Array} ageRanges - Age ranges
 * @param {Array} counts - Count for each range
 */
function createAgeDistributionChart(ageRanges, counts) {
    const ctx = document.getElementById('ageDistributionChart');
    
    if (!ctx) return;
    
    if (chartInstances.ageDistributionChart) {
        chartInstances.ageDistributionChart.destroy();
    }
    
    chartInstances.ageDistributionChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ageRanges || [],
            datasets: [{
                label: 'Number of Animals',
                data: counts || [],
                backgroundColor: '#3498db',
                borderColor: '#2980b9',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

/**
 * Create cost analysis chart
 * @param {Array} supplements - Supplement names
 * @param {Array} costs - Cost per kg
 */
function createCostAnalysisChart(supplements, costs) {
    const ctx = document.getElementById('costChart');
    
    if (!ctx) return;
    
    if (chartInstances.costChart) {
        chartInstances.costChart.destroy();
    }
    
    chartInstances.costChart = new Chart(ctx, {
        type: 'horizontalBar',
        data: {
            labels: supplements || [],
            datasets: [{
                label: 'Cost per Kg (₹)',
                data: costs || [],
                backgroundColor: '#f39c12',
                borderColor: '#e67e22',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Cost (₹)'
                    }
                }
            }
        }
    });
}

/**
 * Update chart with new data
 * @param {string} chartName - Name of chart to update
 * @param {Object} newData - New data for chart
 */
function updateChart(chartName, newData) {
    if (chartInstances[chartName]) {
        chartInstances[chartName].data.labels = newData.labels || chartInstances[chartName].data.labels;
        chartInstances[chartName].data.datasets[0].data = newData.values || chartInstances[chartName].data.datasets[0].data;
        chartInstances[chartName].update();
    }
}
