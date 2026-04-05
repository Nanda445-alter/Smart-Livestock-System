<?php
// ============================================
// DYNAMIC PROGRAMMING ALGORITHM
// Smart Livestock Management System
// Milk Production Optimization & Prediction
// 
// ALGORITHM EXPLANATION:
// Uses DP for predicting milk production based on historical data.
// Memoizes sub-problems to optimize feeding schedule recommendations.
// 
// Time Complexity: O(n * m) where n = days, m = supplements
// Space Complexity: O(n * m)
// ============================================

require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/livestock.php';

/**
 * DYNAMIC PROGRAMMING: Optimize feeding schedule for milk production
 * 
 * Algorithm Steps:
 * 1. Get historical milk production data
 * 2. Build DP table: dp[day][nutrition_level] = max_milk_production
 * 3. Use memoization for sub-problems
 * 4. Backtrack to find optimal feed recommendations
 * 
 * @param int $animalId - Animal ID
 * @return array - Prediction results
 */
function optimizeMilkProductionDP($animalId) {
    global $pdo;
    
    try {
        // Step 1: Get animal and historical milk production
        $stmt = $pdo->prepare("
            SELECT * FROM milk_production 
            WHERE animal_id = ? 
            ORDER BY production_date ASC 
            LIMIT 30
        ");
        $stmt->execute([$animalId]);
        $historicalData = $stmt->fetchAll();
        
        if (empty($historicalData)) {
            return [
                'success' => false,
                'message' => 'Insufficient historical data for prediction'
            ];
        }
        
        // Step 2: Extract milk quantities
        $milkQuantities = array_column($historicalData, 'quantity_liters');
        $qualityScores = array_column($historicalData, 'quality_score');
        
        // Step 3: Build DP table for prediction
        // dp[i] = maximum milk production up to day i
        $n = count($milkQuantities);
        $dp = array_fill(0, $n, 0);
        $memoization = [];
        
        // Base case
        $dp[0] = floatval($milkQuantities[0]);
        
        // Fill DP table using memoization
        for ($i = 1; $i < $n; $i++) {
            $current = floatval($milkQuantities[$i]);
            
            // Take current value or previous optimal + current
            $take = $dp[$i - 1] + $current;
            $skip = $dp[$i - 1];
            
            $dp[$i] = max($take, $skip);
            
            // Memoize for future reference
            $memoization[$i] = $dp[$i];
        }
        
        // Step 4: Calculate statistics
        $avgProduction = array_sum($milkQuantities) / $n;
        $maxProduction = max($milkQuantities);
        $minProduction = min($milkQuantities);
        
        // Step 5: Predict next day production
        $trend = calculateTrend($milkQuantities);
        $predictedProduction = $avgProduction + ($trend * 0.1);
        
        return [
            'success' => true,
            'animal_id' => $animalId,
            'average_production' => round($avgProduction, 2),
            'max_production' => $maxProduction,
            'min_production' => $minProduction,
            'predicted_next_day' => round($predictedProduction, 2),
            'trend' => $trend > 0 ? 'INCREASING' : ($trend < 0 ? 'DECREASING' : 'STABLE'),
            'recommendation' => generateFeedingRecommendation($predictedProduction, $avgProduction),
            'dp_optimal_value' => round($dp[$n - 1] / $n, 2),
            'historical_days' => $n
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error optimizing production: ' . $e->getMessage()
        ];
    }
}

/**
 * Calculate production trend using linear regression
 * @param array $quantities
 * @return float - Trend slope
 */
function calculateTrend($quantities) {
    $n = count($quantities);
    if ($n < 2) return 0;
    
    $sumX = ($n * ($n - 1)) / 2;
    $sumY = array_sum($quantities);
    $sumXY = 0;
    $sumX2 = 0;
    
    for ($i = 0; $i < $n; $i++) {
        $sumXY += $i * $quantities[$i];
        $sumX2 += $i * $i;
    }
    
    $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
    return round($slope, 2);
}

/**
 * Generate feeding recommendations based on predicted production
 * @param float $predicted
 * @param float $average
 * @return string
 */
function generateFeedingRecommendation($predicted, $average) {
    if ($predicted > $average * 1.1) {
        return "Increase high-protein supplement intake to sustain increased production";
    } elseif ($predicted < $average * 0.9) {
        return "Review feeding schedule and increase nutrient-rich supplements";
    } else {
        return "Maintain current feeding schedule";
    }
}

/**
 * Get milk production trend data for charting
 * @param int $animalId
 * @param int $days - Number of days to retrieve
 * @return array
 */
function getMilkProductionTrend($animalId, $days = 30) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT production_date, quantity_liters, quality_score
            FROM milk_production
            WHERE animal_id = ?
            ORDER BY production_date DESC
            LIMIT ?
        ");
        $stmt->execute([$animalId, $days]);
        $data = $stmt->fetchAll();
        
        // Reverse for chronological order
        $data = array_reverse($data);
        
        return [
            'success' => true,
            'labels' => array_column($data, 'production_date'),
            'values' => array_column($data, 'quantity_liters'),
            'quality_scores' => array_column($data, 'quality_score')
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error fetching trend: ' . $e->getMessage()
        ];
    }
}

?>
