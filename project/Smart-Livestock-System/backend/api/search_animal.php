<?php
// ============================================
// SEARCH ANIMALS API ENDPOINT
// Uses Binary Search Algorithm for efficient lookup
// ============================================

require_once '../config.php';
require_once '../livestock.php';
require_once '../../algorithms/binary_search.php';

header('Content-Type: application/json');

requireLogin();
$userId = getUserID();

try {
    $query = $_GET['query'] ?? '';
    
    if (empty($query)) {
        sendJSON(['success' => false, 'message' => 'Query required'], 400);
    }
    
    // Use Binary Search for animal lookup
    $result = binarySearchAnimal($userId, $query);
    
    sendJSON($result, $result['success'] ? 200 : 404);
} catch (Exception $e) {
    sendJSON(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
