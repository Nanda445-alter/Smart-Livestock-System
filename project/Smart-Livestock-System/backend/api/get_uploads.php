<?php
// ============================================
// GET UPLOADS API
// Smart Livestock Management System
// Get all uploaded files for user
// ============================================

require_once '../config.php';
require_once '../auth.php';
require_once '../uploads.php';

// Check authentication
$userId = authenticateUser();
if (!$userId) {
    sendJSON(['success' => false, 'message' => 'Unauthorized'], 401);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get all uploads for user
    $uploads = getAllUploads($userId);
    sendJSON(['success' => true, 'data' => $uploads], 200);
} else {
    sendJSON(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>