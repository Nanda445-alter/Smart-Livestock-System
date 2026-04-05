<?php
// ============================================
// PROCESS MANAGEMENT (OS SIMULATION)
// Smart Livestock Management System
// 
// OS CONCEPT: Process Management
// Simulates OS process states: ready, running, waiting, blocked, completed
// Each user request is treated as a process with lifecycle management
// 
// Process States:
// - READY: Process waiting for CPU
// - RUNNING: Process executing
// - WAITING: Process waiting for I/O
// - BLOCKED: Process blocked on resource
// - COMPLETED: Process finished
// ============================================

require_once __DIR__ . '/../backend/config.php';

/**
 * CREATE PROCESS: Register new user request as process
 * Simulates process creation in OS
 * 
 * @param int $userId - User ID
 * @param string $requestType - Type of request (animal_add, supplement_query, etc)
 * @return array - Process info
 */
function createProcess($userId, $requestType) {
    global $pdo;
    
    try {
        // Process state starts as READY
        $stmt = $pdo->prepare("
            INSERT INTO process_log (
                user_id, request_type, process_state, 
                arrival_time, priority, burst_time
            ) VALUES (?, ?, 'ready', NOW(), ?, ?)
        ");
        
        // Priority: 0-5 (lower = higher priority)
        $priority = rand(0, 5);
        $estimatedBurstTime = rand(50, 500); // milliseconds
        
        $stmt->execute([$userId, $requestType, $priority, $estimatedBurstTime]);
        
        $processId = $pdo->lastInsertId();
        
        return [
            'success' => true,
            'process_id' => $processId,
            'state' => 'ready',
            'message' => 'Process created'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error creating process: ' . $e->getMessage()
        ];
    }
}

/**
 * CHANGE PROCESS STATE: Transition process through states
 * 
 * @param int $processId
 * @param string $newState - New state (running, waiting, blocked, completed)
 * @return array
 */
function updateProcessState($processId, $newState) {
    global $pdo;
    
    try {
        $validStates = ['ready', 'running', 'waiting', 'blocked', 'completed'];
        
        if (!in_array($newState, $validStates)) {
            return [
                'success' => false,
                'message' => 'Invalid process state'
            ];
        }
        
        $stmt = $pdo->prepare("
            UPDATE process_log 
            SET process_state = ?
            WHERE process_id = ?
        ");
        
        if ($newState === 'running') {
            // Update start_time when running
            $stmt = $pdo->prepare("
                UPDATE process_log 
                SET process_state = 'running', start_time = NOW()
                WHERE process_id = ?
            ");
            $stmt->execute([$processId]);
        } elseif ($newState === 'completed') {
            // Update completion_time when completed
            $stmt = $pdo->prepare("
                UPDATE process_log 
                SET process_state = 'completed', completion_time = NOW()
                WHERE process_id = ?
            ");
            $stmt->execute([$processId]);
        } else {
            $stmt->execute([$newState, $processId]);
        }
        
        return [
            'success' => true,
            'message' => 'Process state updated'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error updating process: ' . $e->getMessage()
        ];
    }
}

/**
 * GET PROCESS STATISTICS
 * Shows total processes, their states, and utilization
 * 
 * @param int $userId
 * @return array
 */
function getProcessStatistics($userId) {
    global $pdo;
    
    try {
        // Total processes
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_processes,
                   SUM(CASE WHEN process_state = 'completed' THEN 1 ELSE 0 END) as completed,
                   SUM(CASE WHEN process_state = 'running' THEN 1 ELSE 0 END) as running,
                   SUM(CASE WHEN process_state = 'waiting' THEN 1 ELSE 0 END) as waiting,
                   SUM(CASE WHEN process_state = 'ready' THEN 1 ELSE 0 END) as ready,
                   AVG(burst_time) as avg_burst_time
            FROM process_log
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $stats = $stmt->fetch();
        
        return [
            'success' => true,
            'statistics' => $stats
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error fetching process statistics: ' . $e->getMessage()
        ];
    }
}

/**
 * GET ALL PROCESSES for a user
 * @param int $userId
 * @return array
 */
function getAllProcesses($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM process_log
            WHERE user_id = ?
            ORDER BY process_id DESC
            LIMIT 50
        ");
        $stmt->execute([$userId]);
        
        return [
            'success' => true,
            'processes' => $stmt->fetchAll()
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error fetching processes: ' . $e->getMessage()
        ];
    }
}

?>
