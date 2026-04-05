<?php
// ============================================
// RECOMMENDATIONS API ENDPOINT
// Uses Greedy Algorithm for supplement optimization
// ============================================

require_once '../config.php';
require_once '../../algorithms/greedy_supplement.php';

header('Content-Type: application/json');

requireLogin();
$userId = getUserID();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $animalId = $input['animal_id'] ?? null;
        $budget = floatval($input['budget'] ?? 0);
        
        if (!$animalId || $budget <= 0) {
            sendJSON(['success' => false, 'message' => 'Invalid parameters'], 400);
        }
        
        // Get Greedy recommendations
        $recommendations = getGreedyRecommendations($animalId, $budget);
        
        if ($recommendations['success']) {
            // Optionally save recommendation
            saveRecommendation($userId, $animalId, $recommendations);
        }
        
        sendJSON($recommendations, $recommendations['success'] ? 200 : 400);
    } else {
        sendJSON(['success' => false, 'message' => 'Invalid method'], 400);
    }
} catch (Exception $e) {
    sendJSON(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
