<?php
// ============================================
// REGISTER API ENDPOINT
// Smart Livestock Management System
// ============================================

require_once '../config.php';
require_once '../auth.php';

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $result = registerUser($input);
        
        if ($result['success']) {
            sendJSON($result, 201);
        } else {
            sendJSON($result, 400);
        }
    } else {
        sendJSON(['success' => false, 'message' => 'Invalid request'], 400);
    }
} catch (Exception $e) {
    sendJSON(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
