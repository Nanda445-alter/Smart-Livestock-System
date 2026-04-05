<?php
// ============================================
// SUPPLEMENTS API ENDPOINT
// Get available supplements
// ============================================

require_once '../config.php';
require_once '../supplements.php';

header('Content-Type: application/json');

requireLogin();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        $supplements = getAllSupplements();
        sendJSON(['success' => true, 'data' => $supplements], 200);
    } else {
        sendJSON(['success' => false, 'message' => 'Invalid method'], 400);
    }
} catch (Exception $e) {
    sendJSON(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
