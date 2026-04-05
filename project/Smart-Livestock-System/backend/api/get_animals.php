<?php
// ============================================
// ANIMALS API ENDPOINT
// Smart Livestock Management System
// GET, CREATE, UPDATE, DELETE operations
// ============================================

require_once '../config.php';
require_once '../livestock.php';

header('Content-Type: application/json');

requireLogin();
$userId = getUserID();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        // Get all animals for user
        $animals = getAllAnimals($userId);
        sendJSON(['success' => true, 'data' => $animals], 200);
    } 
    else if ($method === 'POST') {
        // Create new animal
        $input = json_decode(file_get_contents('php://input'), true);
        
        // OS Concept: Create process for this operation
        require_once '../../os_simulation/process_management.php';
        $process = createProcess($userId, 'animal_create');
        
        $result = createAnimal($userId, $input);
        sendJSON($result, $result['success'] ? 201 : 400);
    }
    else {
        sendJSON(['success' => false, 'message' => 'Invalid method'], 400);
    }
} catch (Exception $e) {
    sendJSON(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
