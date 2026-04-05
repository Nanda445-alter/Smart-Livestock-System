<?php
// ============================================
// LOGIN API ENDPOINT
// Smart Livestock Management System
// ============================================

require_once '../config.php';
require_once '../auth.php';

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        
        $result = loginUser($username, $password);
        
        if ($result['success']) {
            sendJSON($result, 200);
        } else {
            sendJSON($result, 401);
        }
    } else if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logout') {
        logoutUser();
    } else {
        sendJSON(['success' => false, 'message' => 'Invalid request'], 400);
    }
} catch (Exception $e) {
    sendJSON(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
