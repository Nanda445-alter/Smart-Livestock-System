<?php
// ============================================
// MEMORY MANAGEMENT (OS SIMULATION)
// Smart Livestock Management System
// 
// OS CONCEPT: Memory Management
// Simulates memory allocation using PHP sessions
// Tracks session memory usage as "virtual memory"
// ============================================

require_once __DIR__ . '/../config.php';

/**
 * ALLOCATE MEMORY: Simulate memory allocation for user session
 * Represents OS memory allocation to process
 * 
 * @param int $userId
 * @param string $dataKey
 * @param mixed $data
 * @return array
 */
function allocateMemory($userId, $dataKey, $data) {
    try {
        // Initialize session memory tracking if not exists
        if (!isset($_SESSION['memory_usage'])) {
            $_SESSION['memory_usage'] = [];
        }
        
        // Estimate data size
        $dataSize = strlen(serialize($data));
        
        // Allocate to session
        $_SESSION[$dataKey] = $data;
        $_SESSION['memory_usage'][$dataKey] = [
            'size' => $dataSize,
            'allocated_at' => time(),
            'type' => gettype($data)
        ];
        
        return [
            'success' => true,
            'message' => 'Memory allocated',
            'allocation_id' => $dataKey,
            'size_bytes' => $dataSize
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Memory allocation error: ' . $e->getMessage()
        ];
    }
}

/**
 * DEALLOCATE MEMORY: Free allocated memory
 * Simulates OS memory deallocation
 * 
 * @param string $dataKey
 * @return array
 */
function deallocateMemory($dataKey) {
    try {
        $freedSize = 0;
        
        if (isset($_SESSION['memory_usage'][$dataKey])) {
            $freedSize = $_SESSION['memory_usage'][$dataKey]['size'];
            unset($_SESSION['memory_usage'][$dataKey]);
        }
        
        if (isset($_SESSION[$dataKey])) {
            unset($_SESSION[$dataKey]);
        }
        
        return [
            'success' => true,
            'message' => 'Memory deallocated',
            'freed_bytes' => $freedSize
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Deallocation error: ' . $e->getMessage()
        ];
    }
}

/**
 * GET MEMORY STATISTICS
 * Shows total allocated memory, fragmentation, etc.
 * 
 * @return array
 */
function getMemoryStatistics() {
    try {
        $totalAllocated = 0;
        $allocationCount = 0;
        $allocations = [];
        
        if (isset($_SESSION['memory_usage'])) {
            foreach ($_SESSION['memory_usage'] as $key => $info) {
                $totalAllocated += $info['size'];
                $allocationCount++;
                $allocations[] = [
                    'key' => $key,
                    'size_bytes' => $info['size'],
                    'size_kb' => round($info['size'] / 1024, 2),
                    'type' => $info['type'],
                    'allocated_at' => date('Y-m-d H:i:s', $info['allocated_at'])
                ];
            }
        }
        
        // Get PHP memory usage
        $memoryPeakUsage = memory_get_peak_usage(true);
        $currentMemoryUsage = memory_get_usage(true);
        
        return [
            'success' => true,
            'session_memory' => [
                'total_allocated_bytes' => $totalAllocated,
                'total_allocated_mb' => round($totalAllocated / (1024 * 1024), 2),
                'allocation_count' => $allocationCount,
                'allocations' => $allocations
            ],
            'php_memory' => [
                'current_usage_mb' => round($currentMemoryUsage / (1024 * 1024), 2),
                'peak_usage_mb' => round($memoryPeakUsage / (1024 * 1024), 2)
            ]
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error getting memory stats: ' . $e->getMessage()
        ];
    }
}

/**
 * MEMORY FRAGMENTATION CHECK
 * Analyzes memory fragmentation (simulated)
 * 
 * @return array
 */
function checkMemoryFragmentation() {
    try {
        $stats = getMemoryStatistics();
        $allocations = $stats['session_memory']['allocations'];
        
        // Calculate fragmentation ratio
        $largestAllocation = 0;
        $totalAllocated = $stats['session_memory']['total_allocated_bytes'];
        
        foreach ($allocations as $alloc) {
            if ($alloc['size_bytes'] > $largestAllocation) {
                $largestAllocation = $alloc['size_bytes'];
            }
        }
        
        $fragmentationRatio = $totalAllocated > 0 ? 
            (($totalAllocated - $largestAllocation) / $totalAllocated) * 100 : 0;
        
        return [
            'success' => true,
            'total_memory_mb' => $stats['session_memory']['total_allocated_mb'],
            'fragmentation_ratio' => round($fragmentationRatio, 2) . '%',
            'allocation_count' => $stats['session_memory']['allocation_count'],
            'status' => $fragmentationRatio > 50 ? 'HIGH' : 'NORMAL'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error checking fragmentation: ' . $e->getMessage()
        ];
    }
}

?>
