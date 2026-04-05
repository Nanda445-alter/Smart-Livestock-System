<?php
// ============================================
// DASHBOARD STATISTICS API
// Returns animal stats and milk production data
// ============================================

require_once '../config.php';
require_once '../livestock.php';
require_once '../../algorithms/dynamic_programming.php';

header('Content-Type: application/json');

requireLogin();
$userId = getUserID();

try {
    // Get animal statistics
    $stats = getAnimalStatistics($userId);
    
    // Get recent animals
    $recentAnimals = getAllAnimals($userId);
    $recentAnimals = array_slice($recentAnimals, 0, 5);
    
    // Get milk production trend
    if (!empty($recentAnimals)) {
        $firstAnimal = $recentAnimals[0];
        $trend = getMilkProductionTrend($firstAnimal['animal_id'], 7);
        if ($trend['success']) {
            $stats['milkTrend'] = $trend;
        }
    }
    
    // Get animal type distribution
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT animal_type, COUNT(*) as count 
        FROM animals 
        WHERE user_id = ? 
        GROUP BY animal_type
    ");
    $stmt->execute([$userId]);
    $typeData = $stmt->fetchAll();
    
    $stats['animalTypes'] = [
        'labels' => array_column($typeData, 'animal_type'),
        'values' => array_column($typeData, 'count')
    ];
    
    $stats['recentAnimals'] = $recentAnimals;
    
    sendJSON(['success' => true, 'data' => $stats], 200);
} catch (Exception $e) {
    sendJSON(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
