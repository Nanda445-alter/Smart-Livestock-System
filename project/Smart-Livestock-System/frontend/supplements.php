<?php
// Session protection
require_once '../backend/config.php';
requireLogin();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplements - Smart Livestock</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <h1>🐄 SmartLivestock</h1>
            </div>
            <ul class="nav-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="livestock.php">Livestock</a></li>
                <li><a href="supplements.php" class="active">Supplements</a></li>
                <li><a href="uploads.php">Files</a></li>
                <li><a href="../backend/logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container supplements-container">
        <h2>Smart Supplement Recommendations</h2>
        
        <!-- Recommendation Form -->
        <div class="recommendation-form">
            <h3>Get Personalized Supplement Recommendation (Using Greedy Algorithm)</h3>
            <form id="recommendationForm" onsubmit="getRecommendation(event)">
                <div class="form-group">
                    <label>Select Animal</label>
                    <select id="selectedAnimal" required>
                        <option value="">Loading animals...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Budget (₹)</label>
                    <input type="number" id="budget" min="100" step="100" placeholder="Enter your budget" required>
                </div>

                <button type="submit" class="btn btn-primary">Get Recommendations</button>
            </form>
        </div>

        <!-- Recommendations Results -->
        <div id="recommendationResults" class="recommendation-results" style="display:none;">
            <h3>Recommended Supplements</h3>
            <div class="results-content">
                <div class="result-info">
                    <p><strong>Selected Animal:</strong> <span id="resultAnimal"></span></p>
                    <p><strong>Budget Allocated:</strong> ₹<span id="resultBudget"></span></p>
                    <p><strong>Total Nutrition Score:</strong> <span id="resultNutrition"></span>/100</p>
                </div>

                <table class="data-table supplements-table">
                    <thead>
                        <tr>
                            <th>Supplement Name</th>
                            <th>Protein %</th>
                            <th>Fat %</th>
                            <th>Minerals %</th>
                            <th>Cost (₹/kg)</th>
                            <th>Recommended Quantity (kg)</th>
                            <th>Total Cost (₹)</th>
                        </tr>
                    </thead>
                    <tbody id="recommendationTable">
                    </tbody>
                </table>

                <div class="recommendation-summary">
                    <p><strong>Total Cost:</strong> ₹<span id="totalCost">0</span></p>
                    <p><strong>Remaining Budget:</strong> ₹<span id="remainingBudget">0</span></p>
                    <p id="recommendationReason" class="recommendation-reason"></p>
                </div>

                <button class="btn btn-secondary" onclick="savRecommendation()">Save Recommendation</button>
            </div>
        </div>

        <!-- Available Supplements -->
        <div class="available-supplements">
            <h3>Available Supplements Database</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Supplement</th>
                        <th>Protein %</th>
                        <th>Fat %</th>
                        <th>Minerals %</th>
                        <th>Cost (₹/kg)</th>
                        <th>For Animal Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody id="supplements-list">
                    <tr><td colspan="7" class="text-center">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Smart Livestock Management System</p>
    </footer>

    <script src="js/main.js"></script>
    <script src="js/validation.js"></script>
</body>
</html>
