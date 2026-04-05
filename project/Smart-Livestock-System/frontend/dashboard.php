<?php
// Session protection
require_once '../backend/config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Smart Livestock</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <h1>🐄 SmartLivestock</h1>
            </div>
            <ul class="nav-menu">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="livestock.php">Livestock</a></li>
                <li><a href="supplements.php">Supplements</a></li>
                <li><a href="uploads.php">Files</a></li>
                <li><a href="../backend/logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container dashboard-container">
        <h2>Dashboard</h2>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Animals</h3>
                <p class="stat-value" id="total-animals">0</p>
            </div>
            <div class="stat-card">
                <h3>Total Milk Production (Today)</h3>
                <p class="stat-value" id="total-milk">0</p>
                <p class="stat-unit">Liters</p>
            </div>
            <div class="stat-card">
                <h3>Average Milk Yield</h3>
                <p class="stat-value" id="avg-milk">0</p>
                <p class="stat-unit">Liters/Animal</p>
            </div>
            <div class="stat-card">
                <h3>Healthy Animals</h3>
                <p class="stat-value" id="healthy-count">0</p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-container">
                <h3>Milk Production Trend (Last 7 Days)</h3>
                <canvas id="milkChart"></canvas>
            </div>
            <div class="chart-container">
                <h3>Animal Distribution by Type</h3>
                <canvas id="animalTypeChart"></canvas>
            </div>
        </div>

        <!-- Recent Animals -->
        <div class="recent-section">
            <h3>Recent Animals</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Age (years)</th>
                        <th>Weight (kg)</th>
                        <th>Daily Milk (L)</th>
                        <th>Health Status</th>
                    </tr>
                </thead>
                <tbody id="animals-table">
                    <tr><td colspan="6" class="text-center">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Smart Livestock Management System</p>
    </footer>

    <script src="js/main.js"></script>
    <script src="js/chart.js"></script>
</body>
</html>
