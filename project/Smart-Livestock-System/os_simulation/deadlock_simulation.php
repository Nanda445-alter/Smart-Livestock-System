<?php
// ============================================
// DEADLOCK SIMULATION & AVOIDANCE (OS)
// Smart Livestock Management System
// 
// OS CONCEPT: Deadlock Prevention
// Simulates resource locks and deadlock detection
// Implements deadlock avoidance strategy
// ============================================

require_once __DIR__ . '/../config.php';

/**
 * REQUEST RESOURCE: Process requests access to resource
 * Simulates OS resource allocation
 * 
 * @param int $processId
 * @param string $resourceName
 * @return array
 */
function requestResource($processId, $resourceName) {
    global $pdo;
    
    try {
        // Check if resource is already locked
        $stmt = $pdo->prepare("
            SELECT * FROM resource_locks 
            WHERE resource_name = ? AND lock_status = 'locked'
            LIMIT 1
        ");
        $stmt->execute([$resourceName]);
        $existingLock = $stmt->fetch();
        
        if ($existingLock) {
            // Resource is locked - check for deadlock
            if (detectDeadlock($processId, $resourceName)) {
                return [
                    'success' => false,
                    'message' => 'Deadlock detected! Request cannot be granted.',
                    'resource_locked_by' => $existingLock['locked_by_process_id']
                ];
            }
            
            // Resource busy - process must wait
            return [
                'success' => false,
                'message' => 'Resource busy. Process waiting.',
                'status' => 'waiting'
            ];
        }
        
        // Resource available - grant access
        $stmt = $pdo->prepare("
            INSERT INTO resource_locks (resource_name, locked_by_process_id, lock_status)
            VALUES (?, ?, 'locked')
        ");
        $stmt->execute([$resourceName, $processId]);
        
        return [
            'success' => true,
            'message' => 'Resource lock acquired',
            'lock_id' => $pdo->lastInsertId(),
            'resource' => $resourceName,
            'status' => 'locked'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Resource request error: ' . $e->getMessage()
        ];
    }
}

/**
 * RELEASE RESOURCE: Process releases resource lock
 * Simulates OS resource deallocation
 * 
 * @param int $processId
 * @param string $resourceName
 * @return array
 */
function releaseResource($processId, $resourceName) {
    global $pdo;
    
    try {
        // Find and release lock
        $stmt = $pdo->prepare("
            UPDATE resource_locks 
            SET lock_status = 'released'
            WHERE locked_by_process_id = ? AND resource_name = ?
        ");
        $stmt->execute([$processId, $resourceName]);
        
        // Delete released lock
        $stmt = $pdo->prepare("
            DELETE FROM resource_locks
            WHERE lock_status = 'released'
        ");
        $stmt->execute();
        
        return [
            'success' => true,
            'message' => 'Resource released',
            'resource' => $resourceName
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Release error: ' . $e->getMessage()
        ];
    }
}

/**
 * DETECT DEADLOCK: Check if circular wait condition exists
 * Uses resource allocation graph cycle detection
 * 
 * Deadlock conditions (all must be true):
 * 1. Mutual Exclusion
 * 2. Hold and Wait
 * 3. No Preemption
 * 4. Circular Wait (DETECTED HERE)
 * 
 * @param int $processId
 * @param string $resourceName
 * @return boolean
 */
function detectDeadlock($processId, $resourceName) {
    global $pdo;
    
    try {
        // Check if process already holds another resource
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as held_resources 
            FROM resource_locks 
            WHERE locked_by_process_id = ? AND lock_status = 'locked'
        ");
        $stmt->execute([$processId]);
        $heldCount = $stmt->fetch()['held_resources'];
        
        // Check if the resource holder is waiting for a resource held by current process
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as circular_wait
            FROM resource_locks rl1, resource_locks rl2
            WHERE rl1.resource_name = ?
            AND rl1.locked_by_process_id != ?
            AND rl2.locked_by_process_id = ?
            AND rl2.resource_name IN (
                SELECT resource_name FROM resource_locks 
                WHERE locked_by_process_id = ?
            )
        ");
        $stmt->execute([$resourceName, $processId, $heldCount > 0 ? $processId : -1, $processId]);
        
        // If circular wait detected, return true
        return $stmt->fetch()['circular_wait'] > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * DEADLOCK AVOIDANCE: Banker's Algorithm (Simplified)
 * Checks if resource allocation is safe before granting
 * 
 * @param int $processId
 * @param string $resourceName
 * @return boolean - true if safe to allocate
 */
function isSafeToAllocate($processId, $resourceName) {
    global $pdo;
    
    try {
        // Get all active locks
        $stmt = $pdo->prepare("
            SELECT * FROM resource_locks 
            WHERE lock_status = 'locked'
        ");
        $stmt->execute();
        $locks = $stmt->fetchAll();
        
        // Simple safety check: limit locks per process
        $processLocks = array_filter($locks, function($lock) use ($processId) {
            return $lock['locked_by_process_id'] === $processId;
        });
        
        // Allow max 3 resources per process
        return count($processLocks) < 3;
    } catch (Exception $e) {
        return true; // Safe by default
    }
}

/**
 * GET LOCK STATISTICS
 * Shows current locks, processes waiting, etc.
 * 
 * @return array
 */
function getLockStatistics() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_locks,
                SUM(CASE WHEN lock_status = 'locked' THEN 1 ELSE 0 END) as active_locks,
                SUM(CASE WHEN lock_status = 'waiting' THEN 1 ELSE 0 END) as waiting_locks
            FROM resource_locks
        ");
        $stmt->execute();
        $stats = $stmt->fetch();
        
        // Get detailed lock info
        $stmt = $pdo->prepare("
            SELECT resource_name, locked_by_process_id, lock_status, lock_time
            FROM resource_locks
            ORDER BY lock_time DESC
        ");
        $stmt->execute();
        $lockDetails = $stmt->fetchAll();
        
        return [
            'success' => true,
            'statistics' => $stats,
            'lock_details' => $lockDetails
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error getting lock statistics: ' . $e->getMessage()
        ];
    }
}

/**
 * DEADLOCK RECOVERY: Release all locks for a process
 * Simulates automatic deadlock recovery
 * 
 * @param int $processId
 * @return array
 */
function deadlockRecovery($processId) {
    global $pdo;
    
    try {
        // Release all locks held by this process
        $stmt = $pdo->prepare("
            DELETE FROM resource_locks 
            WHERE locked_by_process_id = ?
        ");
        $stmt->execute([$processId]);
        
        return [
            'success' => true,
            'message' => 'Deadlock recovery completed. All locks released.',
            'process_id' => $processId
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Recovery error: ' . $e->getMessage()
        ];
    }
}

?>
