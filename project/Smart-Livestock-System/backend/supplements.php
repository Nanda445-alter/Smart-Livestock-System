<?php
// ============================================
// SUPPLEMENTS MANAGEMENT MODULE
// Smart Livestock Management System
// Handles supplement database operations
// ============================================

require_once __DIR__ . '/config.php';

/**
 * Get all available supplements
 * @return array - Array of supplement objects
 */
function getAllSupplements() {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM supplements ORDER BY supplement_name");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get supplements for specific animal type
 * @param string $animalType
 * @return array - Filtered supplements
 */
function getSupplementsByAnimalType($animalType) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM supplements 
        WHERE animal_type = ? OR animal_type = 'all'
        ORDER BY supplement_name
    ");
    $stmt->execute([$animalType]);
    return $stmt->fetchAll();
}

/**
 * Get supplement by ID
 * @param int $supplementId
 * @return array - Supplement data
 */
function getSupplementByID($supplementId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM supplements WHERE supplement_id = ?");
    $stmt->execute([$supplementId]);
    return $stmt->fetch();
}

/**
 * Add new supplement
 * @param array $data - Supplement data
 * @return array - Result with success status
 */
function addSupplement($data) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO supplements (
                supplement_name, protein_content, fat_content, minerals,
                cost_per_kg, animal_type, description
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            sanitizeInput($data['supplement_name']),
            $data['protein_content'] ?? 0,
            $data['fat_content'] ?? 0,
            $data['minerals'] ?? 0,
            $data['cost_per_kg'] ?? 0,
            $data['animal_type'] ?? 'all',
            sanitizeInput($data['description'] ?? '')
        ]);
        
        return [
            'success' => true,
            'message' => 'Supplement added successfully'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error adding supplement: ' . $e->getMessage()
        ];
    }
}

?>
