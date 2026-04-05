<?php
// ============================================
// DOWNLOAD FILE API
// Smart Livestock Management System
// Download an uploaded file
// ============================================

require_once '../config.php';
require_once '../auth.php';
require_once '../uploads.php';

// Check authentication
$userId = authenticateUser();
if (!$userId) {
    header('HTTP/1.1 401 Unauthorized');
    echo 'Unauthorized';
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get file path from query parameter
    $filePath = isset($_GET['path']) ? trim($_GET['path']) : '';

    if (!$filePath) {
        header('HTTP/1.1 400 Bad Request');
        echo 'File path is required';
        exit;
    }

    // Security: Prevent directory traversal
    $filePath = str_replace(['../', '..\\'], '', $filePath);

    // Build full path
    $fullPath = __DIR__ . '/../../' . $filePath;

    // Check if file exists
    if (!file_exists($fullPath) || !is_file($fullPath)) {
        header('HTTP/1.1 404 Not Found');
        echo 'File not found';
        exit;
    }

    // Check if file belongs to user (extract user_id from path)
    if (preg_match('/uploads\/user_(\d+)\//', $filePath, $matches)) {
        $fileUserId = (int)$matches[1];
        if ($fileUserId !== $userId) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Access denied';
            exit;
        }
    } else {
        header('HTTP/1.1 403 Forbidden');
        echo 'Invalid file path';
        exit;
    }

    // Get file info
    $fileName = basename($fullPath);
    $fileSize = filesize($fullPath);
    $fileType = mime_content_type($fullPath);

    // Set headers for download
    header('Content-Type: ' . $fileType);
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    // Clear output buffer
    if (ob_get_level()) {
        ob_clean();
    }

    // Output file content
    readfile($fullPath);
    exit;
} else {
    header('HTTP/1.1 405 Method Not Allowed');
    echo 'Method not allowed';
}
?>