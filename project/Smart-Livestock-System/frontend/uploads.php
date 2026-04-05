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
    <title>File Management - Smart Livestock</title>
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
                <li><a href="supplements.php">Supplements</a></li>
                <li><a href="uploads.php" class="active">Files</a></li>
                <li><a href="../backend/logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container uploads-container">
        <div class="page-header">
            <h2>File Management</h2>
            <button class="btn btn-primary" onclick="openUploadModal()">+ Upload File</button>
        </div>

        <!-- Upload Form Modal -->
        <div id="uploadModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeUploadModal()">&times;</span>
                <h2>Upload File</h2>
                <form id="uploadForm" enctype="multipart/form-data" onsubmit="submitUpload(event)">
                    <div class="form-group">
                        <label>Select Animal (Optional)</label>
                        <select id="uploadAnimal">
                            <option value="">Not linked to any animal</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>File Description</label>
                        <input type="text" id="uploadDescription" placeholder="e.g., Health report, Medical records">
                    </div>

                    <div class="form-group">
                        <label>Select File</label>
                        <input type="file" id="uploadFile" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                        <p class="help-text">Allowed: PDF, JPG, PNG, DOC, DOCX (Max 5MB)</p>
                    </div>

                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
            </div>
        </div>

        <!-- Files List -->
        <div class="files-section">
            <h3>My Files</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Type</th>
                        <th>Size (KB)</th>
                        <th>Linked Animal</th>
                        <th>Uploaded On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="files-list">
                    <tr><td colspan="6" class="text-center">Loading...</td></tr>
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
