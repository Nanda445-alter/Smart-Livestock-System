<?php
// ============================================
// GREEDY ALGORITHM
// Smart Livestock Management System
// Supplement Recommendation using Greedy Strategy
// 
// ALGORITHM EXPLANATION:
// This implements a GREEDY ALGORITHM that selects supplements
// with the highest nutrition-to-cost ratio until budget is exhausted.
// 
// Time Complexity: O(n log n) - due to sorting
// Space Complexity: O(n)
// ============================================

require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/livestock.php';
require_once __DIR__ . '/../backend/supplements.php';

/**
 * GREEDY ALGORITHM: Recommend supplements maximizing nutrition within budget
 * 
 * Algorithm Steps:
 * 1. Fetch all available supplements for the animal type
 * 2. Calculate nutrition score (protein + fat + minerals) / cost for each supplement
 * 3. Sort supplements by nutrition-to-cost ratio (GREEDY CHOICE)
 * 4. Iteratively select supplements with highest ratio until budget exhausted
 * 5. Return recommendations with total nutrition score
 * 
 * @param int $animalId - Animal ID
 * @param float $budget - Budget in currency
 * @return array - Recommendations array
 */
function getGreedyRecommendations($animalId, $budget) {
    global $pdo;
    
    try {
        // Step 1: Get animal details
        $animal = getAnimalByID($animalId);
        if (!$animal) {
            return [
                'success' => false,
                'message' => 'Animal not found'
            ];
        }
        
        // Step 2: Get all available supplements for this animal type
        $supplements = getSupplementsByAnimalType($animal['animal_type']);
        if (empty($supplements)) {
            return [
                'success' => false,
                'message' => 'No supplements available for this animal type'
            ];
        }
        
        // Step 3: Calculate nutrition score for each supplement
        // NUTRITION SCORE = (Protein% + Fat% + Minerals%) / Cost per kg
        // This represents "nutrition per rupee"
        $supplementScores = [];
        foreach ($supplements as $supp) {
            $nutritionContent = $supp['protein_content'] + $supp['fat_content'] + $supp['minerals'];
            $nutritionPerCost = $supp['cost_per_kg'] > 0 ? $nutritionContent / $supp['cost_per_kg'] : 0;
            
            $supplementScores[] = [
                'supplement_id' => $supp['supplement_id'],
                'supplement_name' => $supp['supplement_name'],
                'protein_content' => $supp['protein_content'],
                'fat_content' => $supp['fat_content'],
                'minerals' => $supp['minerals'],
                'cost_per_kg' => $supp['cost_per_kg'],
                'nutrition_content' => $nutritionContent,
                'nutrition_per_cost' => $nutritionPerCost
            ];
        }
        
        // Step 4: GREEDY CHOICE - Sort by nutrition-per-cost (descending)
        // This is the greedy step: we pick the "best value" supplements first
        usort($supplementScores, function($a, $b) {
            return $b['nutrition_per_cost'] <=> $a['nutrition_per_cost'];
        });
        
        // Step 5: Select supplements until budget exhausted
        $recommendations = [];
        $remainingBudget = $budget;
        $totalNutritionScore = 0;
        $totalCost = 0;
        
        foreach ($supplementScores as $supp) {
            if ($remainingBudget <= 0) {
                break;
            }
            
            // Greedy selection: take maximum quantity of best-value supplement
            $maxQuantityAffordable = $remainingBudget / $supp['cost_per_kg'];
            $recommendedQuantity = min($maxQuantityAffordable, 10); // Max 10 kg per supplement
            
            if ($recommendedQuantity > 0.1) { // Minimum 100g recommendation
                $supplementCost = $recommendedQuantity * $supp['cost_per_kg'];
                
                $recommendations[] = [
                    'supplement_id' => $supp['supplement_id'],
                    'supplement_name' => $supp['supplement_name'],
                    'protein_content' => $supp['protein_content'],
                    'fat_content' => $supp['fat_content'],
                    'minerals' => $supp['minerals'],
                    'cost_per_kg' => $supp['cost_per_kg'],
                    'quantity_kg' => $recommendedQuantity,
                    'total_cost' => $supplementCost,
                    'nutrition_score' => $supp['nutrition_content']
                ];
                
                $remainingBudget -= $supplementCost;
                $totalCost += $supplementCost;
                $totalNutritionScore += ($supp['nutrition_content'] * $recommendedQuantity);
            }
        }
        
        // Normalize nutrition score to 0-100 scale
        $normalizedNutritionScore = min(100, ($totalNutritionScore / 100));
        
        return [
            'success' => true,
            'animal' => $animal,
            'recommendations' => $recommendations,
            'budget' => $budget,
            'total_cost' => $totalCost,
            'remaining_budget' => max(0, $budget - $totalCost),
            'total_nutrition_score' => $normalizedNutritionScore,
            'recommendation_reason' => "Selected supplements with highest nutrition-to-cost ratio. Total: " . count($recommendations) . " supplements."
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error generating recommendations: ' . $e->getMessage()
        ];
    }
}

/**
 * Save recommendation to database
 * @param int $userId - User ID
 * @param int $animalId - Animal ID
 * @param array $recommendationData - Recommendation data
 * @return array - Result
 */
function saveRecommendation($userId, $animalId, $recommendationData) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO recommendations (
                animal_id, user_id, budget, recommended_supplements, 
                total_nutrition_score, total_cost, recommendation_reason
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $animalId,
            $userId,
            $recommendationData['budget'],
            json_encode($recommendationData['recommendations']),
            $recommendationData['total_nutrition_score'],
            $recommendationData['total_cost'],
            $recommendationData['recommendation_reason']
        ]);
        
        return [
            'success' => true,
            'message' => 'Recommendation saved successfully'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error saving recommendation: ' . $e->getMessage()
        ];
    }
}

?>
