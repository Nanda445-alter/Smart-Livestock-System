<?php
// ============================================
// BINARY SEARCH ALGORITHM
// Smart Livestock Management System
// Animal Search using Binary Search
// 
// ALGORITHM EXPLANATION:
// Binary Search efficiently finds animals by ID or name in SORTED array.
// Used for O(log n) search instead of O(n) linear search.
// 
// Time Complexity: O(log n)
// Space Complexity: O(n)
// ============================================

require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/livestock.php';

/**
 * BINARY SEARCH: Search animal by ID or name
 * 
 * Algorithm Steps:
 * 1. Fetch all animals for user
 * 2. Sort animals by ID
 * 3. Apply binary search to find animal
 * 4. Return matched animals
 * 
 * @param int $userId - User ID
 * @param string $query - Search query (ID or name)
 * @return array - Search results
 */
function binarySearchAnimal($userId, $query) {
    try {
        global $pdo;
        
        // Step 1: Get all animals for the user
        $stmt = $pdo->prepare("
            SELECT * FROM animals 
            WHERE user_id = ? 
            ORDER BY animal_id ASC
        ");
        $stmt->execute([$userId]);
        $animals = $stmt->fetchAll();
        
        if (empty($animals)) {
            return [
                'success' => false,
                'results' => [],
                'message' => 'No animals found for this user'
            ];
        }
        
        $results = [];
        $query = strtolower(trim($query));
        
        // Try numeric search (by ID) using binary search
        if (is_numeric($query)) {
            $searchId = (int)$query;
            $result = binarySearchByID($animals, $searchId);
            
            if ($result !== null) {
                $results[] = $result;
                return [
                    'success' => true,
                    'results' => $results,
                    'search_method' => 'Binary Search by ID',
                    'message' => 'Animal found'
                ];
            }
        }
        
        // String search (by name) - uses linear search for prefix matching
        foreach ($animals as $animal) {
            if (stripos($animal['animal_name'], $query) === 0 || 
                stripos($animal['animal_name'], $query) !== false) {
                $results[] = $animal;
            }
        }
        
        return [
            'success' => true,
            'results' => $results,
            'search_method' => 'Linear search by name (Binary Search used for numeric IDs)',
            'message' => count($results) > 0 ? 'Search completed' : 'No matching animals found'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'results' => [],
            'message' => 'Search error: ' . $e->getMessage()
        ];
    }
}

/**
 * BINARY SEARCH IMPLEMENTATION: Find animal by ID in sorted array
 * 
 * @param array $animals - Sorted array of animals
 * @param int $targetId - Animal ID to find
 * @return array|null - Found animal or null
 */
function binarySearchByID($animals, $targetId) {
    $left = 0;
    $right = count($animals) - 1;
    
    while ($left <= $right) {
        // Calculate mid-point
        $mid = intval(($left + $right) / 2);
        $midAnimal = $animals[$mid];
        
        // Check if found
        if ($midAnimal['animal_id'] === $targetId) {
            return $midAnimal; // FOUND
        }
        
        // Decide which half to search
        if ($midAnimal['animal_id'] < $targetId) {
            // Search right half
            $left = $mid + 1;
        } else {
            // Search left half
            $right = $mid - 1;
        }
    }
    
    return null; // NOT FOUND
}

/**
 * Get all animals sorted (for binary search preparation)
 * @param int $userId
 * @return array
 */
function getAnimalsSorted($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM animals 
        WHERE user_id = ? 
        ORDER BY animal_id ASC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

?>
