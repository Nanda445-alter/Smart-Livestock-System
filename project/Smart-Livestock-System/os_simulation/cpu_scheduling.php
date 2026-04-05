<?php
// ============================================
// CPU SCHEDULING SIMULATION (OS)
// Smart Livestock Management System
// 
// OS CONCEPT: CPU Scheduling
// Implements Round Robin scheduling algorithm
// Time Quantum: 100ms per process
// ============================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/process_management.php';

/**
 * ROUND ROBIN SCHEDULING ALGORITHM
 * 
 * Algorithm:
 * 1. Maintain ready queue of processes
 * 2. Allocate time quantum (100ms) to each process
 * 3. If process not completed in quantum, move to end of queue
 * 4. Continue until all processes completed
 * 
 * Time Complexity: O(n) where n = number of processes
 * 
 * @param int $userId
 * @return array - Scheduling results
 */
function roundRobinScheduling($userId) {
    global $pdo;
    
    try {
        // Step 1: Get all ready processes for this user
        $stmt = $pdo->prepare("
            SELECT * FROM process_log
            WHERE user_id = ? AND process_state IN ('ready', 'running')
            ORDER BY priority ASC, arrival_time ASC
        ");
        $stmt->execute([$userId]);
        $processes = $stmt->fetchAll();
        
        if (empty($processes)) {
            return [
                'success' => false,
                'message' => 'No processes to schedule'
            ];
        }
        
        // Step 2: Create queue for scheduling
        $queue = [];
        foreach ($processes as $process) {
            $queue[] = [
                'process_id' => $process['process_id'],
                'remaining_burst_time' => $process['burst_time'],
                'total_burst_time' => $process['burst_time']
            ];
        }
        
        $TIME_QUANTUM = 100; // milliseconds
        $schedulingLog = [];
        $currentTime = 0;
        $processNumber = 1;
        
        // Step 3: Execute Round Robin
        while (!empty($queue)) {
            $currentProcess = array_shift($queue);
            
            // Execute process for one time quantum
            $executionTime = min($currentProcess['remaining_burst_time'], $TIME_QUANTUM);
            $currentTime += $executionTime;
            
            // Log execution
            $schedulingLog[] = [
                'process_number' => $processNumber,
                'process_id' => $currentProcess['process_id'],
                'execution_time' => $executionTime,
                'current_time' => $currentTime,
                'remaining_burst_time' => $currentProcess['remaining_burst_time'] - $executionTime
            ];
            
            $currentProcess['remaining_burst_time'] -= $executionTime;
            
            // If process not completed, add back to queue
            if ($currentProcess['remaining_burst_time'] > 0) {
                $queue[] = $currentProcess;
            } else {
                // Process completed - update in database
                updateProcessState($currentProcess['process_id'], 'completed');
            }
            
            $processNumber++;
        }
        
        // Calculate average waiting time
        $avgWaitingTime = calculateAverageWaitingTime($schedulingLog);
        
        return [
            'success' => true,
            'algorithm' => 'Round Robin',
            'time_quantum' => $TIME_QUANTUM . 'ms',
            'total_execution_time' => $currentTime . 'ms',
            'scheduling_log' => $schedulingLog,
            'average_waiting_time' => round($avgWaitingTime, 2) . 'ms',
            'processes_completed' => count($processes)
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Scheduling error: ' . $e->getMessage()
        ];
    }
}

/**
 * FCFS SCHEDULING ALGORITHM
 * First Come First Served - processes execute in arrival order
 * 
 * @param int $userId
 * @return array
 */
function fcfsScheduling($userId) {
    global $pdo;
    
    try {
        // Get processes ordered by arrival time
        $stmt = $pdo->prepare("
            SELECT * FROM process_log
            WHERE user_id = ? AND process_state IN ('ready', 'running')
            ORDER BY arrival_time ASC
        ");
        $stmt->execute([$userId]);
        $processes = $stmt->fetchAll();
        
        if (empty($processes)) {
            return [
                'success' => false,
                'message' => 'No processes to schedule'
            ];
        }
        
        $schedulingLog = [];
        $currentTime = 0;
        
        foreach ($processes as $process) {
            $startTime = $currentTime;
            $currentTime += $process['burst_time'];
            
            $schedulingLog[] = [
                'process_id' => $process['process_id'],
                'start_time' => $startTime,
                'end_time' => $currentTime,
                'burst_time' => $process['burst_time'],
                'waiting_time' => $startTime
            ];
            
            updateProcessState($process['process_id'], 'completed');
        }
        
        $avgWaitingTime = array_sum(array_column($schedulingLog, 'waiting_time')) / count($schedulingLog);
        
        return [
            'success' => true,
            'algorithm' => 'FCFS (First Come First Served)',
            'total_execution_time' => $currentTime . 'ms',
            'scheduling_log' => $schedulingLog,
            'average_waiting_time' => round($avgWaitingTime, 2) . 'ms',
            'processes_completed' => count($processes)
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'FCFS Scheduling error: ' . $e->getMessage()
        ];
    }
}

/**
 * Calculate average waiting time from scheduling log
 * @param array $schedulingLog
 * @return float
 */
function calculateAverageWaitingTime($schedulingLog) {
    if (empty($schedulingLog)) return 0;
    
    $totalWaiting = 0;
    foreach ($schedulingLog as $log) {
        // Waiting time = time when process completes - burst time
        $completionTime = $log['current_time'];
        $waitingTime = max(0, $completionTime - $log['execution_time']);
        $totalWaiting += $waitingTime;
    }
    
    return $totalWaiting / count($schedulingLog);
}

?>
