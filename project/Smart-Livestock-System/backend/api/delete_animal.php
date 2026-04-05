<?php
// ============================================
// DELETE ANIMAL API
// Smart Livestock Management System
// Delete an animal by ID
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

if ($method === 'POST') {
    // Get animal ID from POST data
    $input = json_decode(file_get_contents('php://input'), true);
    $animalId = isset($input['animal_id']) ? (int)$input['animal_id'] : 0;

    if (!$animalId) {
        sendJSON(['success' => false, 'message' => 'Animal ID is required'], 400);
        exit;
    }

    // Check if animal exists and belongs to user
    $animal = getAnimalByID($animalId);
    if (!$animal) {
        sendJSON(['success' => false, 'message' => 'Animal not found'], 404);
        exit;
    }

    if ($animal['user_id'] != $userId) {
        sendJSON(['success' => false, 'message' => 'Unauthorized'], 403);
        exit;
    }

    // OS Concept: Create process for this operation
    require_once '../../os_simulation/process_management.php';
    $process = createProcess($userId, 'animal_delete');

    // Delete the animal
    $result = deleteAnimal($animalId);

    if ($result) {
        // Update process status
        updateProcessStatus($process['process_id'], 'completed');

        sendJSON(['success' => true, 'message' => 'Animal deleted successfully'], 200);
    } else {
        // Update process status
        updateProcessStatus($process['process_id'], 'failed');

        sendJSON(['success' => false, 'message' => 'Failed to delete animal'], 500);
    }
} else {
    sendJSON(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>