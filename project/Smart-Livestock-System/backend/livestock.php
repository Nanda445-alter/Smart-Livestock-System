<?php
// ============================================
// LIVESTOCK MANAGEMENT MODULE
// Smart Livestock Management System
// Handles CRUD operations for animals
// ============================================

require_once __DIR__ . '/config.php';

/**
 * Get all animals for a user
 * @param int $userId
 * @return array - Array of animal objects
 */
function getAllAnimals($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM animals 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Get animal by ID
 * @param int $animalId
 * @return array - Animal data
 */
function getAnimalByID($animalId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM animals WHERE animal_id = ?
    ");
    $stmt->execute([$animalId]);
    return $stmt->fetch();
}

/**
 * Create new animal
 * @param int $userId
 * @param array $data - Animal data
 * @return array - Result with success status
 */
function createAnimal($userId, $data) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO animals (
                user_id, animal_name, animal_type, breed, age, weight,
                milk_yield_daily, health_status, feed_type
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            sanitizeInput($data['animal_name']),
            sanitizeInput($data['animal_type']),
            sanitizeInput($data['breed'] ?? ''),
            $data['age'] ?? 0,
            $data['weight'] ?? 0,
            $data['milk_yield_daily'] ?? 0,
            $data['health_status'] ?? 'healthy',
            sanitizeInput($data['feed_type'] ?? '')
        ]);
        
        return [
            'success' => true,
            'message' => 'Animal added successfully',
            'animal_id' => $pdo->lastInsertId()
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error creating animal: ' . $e->getMessage()
        ];
    }
}

/**
 * Update animal
 * @param int $animalId
 * @param array $data - Animal data
 * @return array - Result with success status
 */
function updateAnimal($animalId, $data) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE animals SET
                animal_name = ?,
                animal_type = ?,
                breed = ?,
                age = ?,
                weight = ?,
                milk_yield_daily = ?,
                health_status = ?,
                feed_type = ?
            WHERE animal_id = ?
        ");
        
        $stmt->execute([
            sanitizeInput($data['animal_name']),
            sanitizeInput($data['animal_type']),
            sanitizeInput($data['breed'] ?? ''),
            $data['age'] ?? 0,
            $data['weight'] ?? 0,
            $data['milk_yield_daily'] ?? 0,
            $data['health_status'] ?? 'healthy',
            sanitizeInput($data['feed_type'] ?? ''),
            $animalId
        ]);
        
        return [
            'success' => true,
            'message' => 'Animal updated successfully'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error updating animal: ' . $e->getMessage()
        ];
    }
}

/**
 * Delete animal
 * @param int $animalId
 * @return array - Result with success status
 */
function deleteAnimal($animalId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM animals WHERE animal_id = ?");
        $stmt->execute([$animalId]);
        
        return [
            'success' => true,
            'message' => 'Animal deleted successfully'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error deleting animal: ' . $e->getMessage()
        ];
    }
}

/**
 * Get animal statistics for dashboard
 * @param int $userId
 * @return array - Statistics data
 */
function getAnimalStatistics($userId) {
    global $pdo;
    
    $stats = [];
    
    // Total animals
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM animals WHERE user_id = ?");
    $stmt->execute([$userId]);
    $stats['totalAnimals'] = $stmt->fetch()['count'];
    
    // Healthy animals
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM animals WHERE user_id = ? AND health_status = 'healthy'");
    $stmt->execute([$userId]);
    $stats['healthyCount'] = $stmt->fetch()['count'];
    
    // Total milk (latest available data)
    $stmt = $pdo->prepare("
        SELECT SUM(quantity_liters) as total FROM milk_production
        WHERE user_id = ? AND production_date = (
            SELECT MAX(production_date) FROM milk_production WHERE user_id = ?
        )
    ");
    $stmt->execute([$userId, $userId]);
    $stats['totalMilkToday'] = $stmt->fetch()['total'] ?? 0;
    
    // Average milk yield
    $stmt = $pdo->prepare("
        SELECT AVG(milk_yield_daily) as avg FROM animals WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $stats['avgMilkYield'] = $stmt->fetch()['avg'] ?? 0;
    
    return $stats;
}

?>
