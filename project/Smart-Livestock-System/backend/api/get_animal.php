<?php
// ============================================
// GET ANIMAL API
// Smart Livestock Management System
// Get single animal data by ID
// ============================================

require_once '../config.php';
require_once '../auth.php';
require_once '../livestock.php';

// Check authentication
$userId = authenticateUser();
if (!$userId) {
    sendJSON(['success' => false, 'message' => 'Unauthorized'], 401);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get animal ID from query parameter
    $animalId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if (!$animalId) {
        sendJSON(['success' => false, 'message' => 'Animal ID is required'], 400);
        exit;
    }

    // Get animal data
    $animal = getAnimalByID($animalId);

    if (!$animal) {
        sendJSON(['success' => false, 'message' => 'Animal not found'], 404);
        exit;
    }

    // Check if animal belongs to user
    if ($animal['user_id'] != $userId) {
        sendJSON(['success' => false, 'message' => 'Unauthorized'], 403);
        exit;
    }

    sendJSON(['success' => true, 'data' => $animal], 200);
} else {
    sendJSON(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>