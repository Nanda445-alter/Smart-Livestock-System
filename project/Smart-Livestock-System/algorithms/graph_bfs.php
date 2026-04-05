<?php
// ============================================
// GRAPH & BFS ALGORITHM
// Smart Livestock Management System
// Farm Network Disease Spread Analysis
// 
// ALGORITHM EXPLANATION:
// Uses Graph Theory and BFS to simulate disease spread through farm network.
// Models farms as vertices and connections as edges.
// 
// Time Complexity: O(V + E) where V = farms, E = connections
// Space Complexity: O(V + E)
// ============================================

require_once __DIR__ . '/../backend/config.php';

/**
 * BUILD GRAPH: Create adjacency list from farm connections
 * @param array $farms
 * @param array $connections
 * @return array - Adjacency list
 */
function buildGraphAdjacencyList($farms, $connections) {
    $graph = [];
    
    // Initialize all farms
    foreach ($farms as $farm) {
        $graph[$farm['farm_id']] = [];
    }
    
    // Add edges (undirected graph)
    foreach ($connections as $conn) {
        $farm1 = $conn['farm1_id'];
        $farm2 = $conn['farm2_id'];
        
        // Add edge in both directions (undirected)
        $graph[$farm1][] = $farm2;
        $graph[$farm2][] = $farm1;
    }
    
    return $graph;
}

/**
 * BFS ALGORITHM: Simulate disease spread through farm network
 * 
 * Algorithm Steps:
 * 1. Start from infected farm
 * 2. Use BFS queue to visit adjacent farms
 * 3. Mark farms as infected at each distance level
 * 4. Return infection spread pattern
 * 
 * @param array $graph - Adjacency list
 * @param int $startFarmId - Initial infected farm
 * @return array - BFS traversal and infection spread
 */
function bfsDiseaseSpreading($graph, $startFarmId) {
    $visited = [];
    $queue = [];
    $spreadPattern = [];
    
    // Step 1: Initialize
    $queue[] = $startFarmId;
    $visited[$startFarmId] = true;
    $spreadPattern[] = [
        'farm_id' => $startFarmId,
        'distance' => 0,
        'day' => 1
    ];
    
    // Step 2: BFS traversal
    $day = 1;
    while (!empty($queue)) {
        $currentFarm = array_shift($queue);
        
        // Step 3: Visit all adjacent farms
        if (isset($graph[$currentFarm])) {
            foreach ($graph[$currentFarm] as $adjacentFarm) {
                if (!isset($visited[$adjacentFarm])) {
                    // Mark as visited (infected)
                    $visited[$adjacentFarm] = true;
                    $queue[] = $adjacentFarm;
                    
                    $day++;
                    $spreadPattern[] = [
                        'farm_id' => $adjacentFarm,
                        'distance' => count($visited) - 1,
                        'day' => $day
                    ];
                }
            }
        }
    }
    
    return [
        'success' => true,
        'start_farm' => $startFarmId,
        'total_infected' => count($visited),
        'spread_pattern' => $spreadPattern,
        'visited_farms' => array_keys($visited)
    ];
}

/**
 * Get farms network for user
 * @param int $userId
 * @return array
 */
function getUnserFarmsNetwork($userId) {
    global $pdo;
    
    try {
        // Get farms
        $stmt = $pdo->prepare("
            SELECT * FROM farms_network WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $farms = $stmt->fetchAll();
        
        // Get connections
        $stmt = $pdo->prepare("
            SELECT * FROM farm_connections 
            WHERE farm1_id IN (SELECT farm_id FROM farms_network WHERE user_id = ?)
            OR farm2_id IN (SELECT farm_id FROM farms_network WHERE user_id = ?)
        ");
        $stmt->execute([$userId, $userId]);
        $connections = $stmt->fetchAll();
        
        return [
            'success' => true,
            'farms' => $farms,
            'connections' => $connections
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error fetching farm network: ' . $e->getMessage()
        ];
    }
}

/**
 * Simulate disease spread for a user's farms
 * @param int $userId
 * @param int $startFarmId
 * @return array
 */
function simulateDiseaseSpread($userId, $startFarmId) {
    try {
        // Get network
        $network = getUnserFarmsNetwork($userId);
        if (!$network['success']) {
            return $network;
        }
        
        // Build graph
        $graph = buildGraphAdjacencyList($network['farms'], $network['connections']);
        
        // Run BFS
        $result = bfsDiseaseSpreading($graph, $startFarmId);
        
        return [
            'success' => true,
            'network_analysis' => $result,
            'total_farms' => count($network['farms']),
            'total_connections' => count($network['connections'])
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error simulating disease spread: ' . $e->getMessage()
        ];
    }
}

?>
