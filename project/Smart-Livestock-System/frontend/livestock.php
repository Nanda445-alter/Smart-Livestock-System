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
    <title>Livestock Management - Smart Livestock</title>
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
                <li><a href="livestock.php" class="active">Livestock</a></li>
                <li><a href="supplements.php">Supplements</a></li>
                <li><a href="uploads.php">Files</a></li>
                <li><a href="../backend/logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container livestock-container">
        <div class="page-header">
            <h2>Livestock Management</h2>
            <button class="btn btn-primary" onclick="openAddModal()">+ Add New Animal</button>
        </div>

        <!-- Search Bar -->
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Search by Animal Name or ID (uses Binary Search algorithm)..." onkeyup="performSearch()">
        </div>

        <!-- Animals Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Breed</th>
                    <th>Age</th>
                    <th>Weight (kg)</th>
                    <th>Daily Milk (L)</th>
                    <th>Health Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="animals-list">
                <tr><td colspan="9" class="text-center">Loading...</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Add/Edit Animal Modal -->
    <div id="animalModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <h2 id="modalTitle">Add New Animal</h2>
            <form id="animalForm" onsubmit="submitAnimal(event)">
                <input type="hidden" id="animalId" value="0">
                
                <div class="form-group">
                    <label>Animal Name</label>
                    <input type="text" id="animalName" required>
                </div>

                <div class="form-group">
                    <label>Animal Type</label>
                    <select id="animalType" required>
                        <option value="">Select Type</option>
                        <option value="cow">Cow</option>
                        <option value="buffalo">Buffalo</option>
                        <option value="goat">Goat</option>
                        <option value="sheep">Sheep</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Breed</label>
                    <input type="text" id="breed">
                </div>

                <div class="form-group">
                    <label>Age (years)</label>
                    <input type="number" id="age" min="0" step="0.1">
                </div>

                <div class="form-group">
                    <label>Weight (kg)</label>
                    <input type="number" id="weight" min="0" step="0.1">
                </div>

                <div class="form-group">
                    <label>Daily Milk Production (liters)</label>
                    <input type="number" id="milkYield" min="0" step="0.1">
                </div>

                <div class="form-group">
                    <label>Health Status</label>
                    <select id="healthStatus">
                        <option value="healthy">Healthy</option>
                        <option value="sick">Sick</option>
                        <option value="recovering">Recovering</option>
                        <option value="under_observation">Under Observation</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Feed Type</label>
                    <input type="text" id="feedType" placeholder="e.g., Grass, Grain, Mixed">
                </div>

                <button type="submit" class="btn btn-primary">Save Animal</button>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Smart Livestock Management System</p>
    </footer>

    <script src="js/main.js"></script>
    <script src="js/validation.js"></script>
</body>
</html>
