<?php
// ============================================
// FILE MANAGEMENT (OS SIMULATION)
// Smart Livestock Management System
// 
// OS CONCEPT: File System Management
// Simulates file operations: create, read, write, delete
// Manages file permissions and access control
// ============================================

require_once __DIR__ . '/../config.php';

// Upload directory configuration
$UPLOAD_BASE_PATH = __DIR__ . '/../uploads/';

/**
 * CREATE FILE: Upload and register file in system
 * Simulates file creation in OS
 * 
 * @param int $userId
 * @param array $fileData - File information
 * @return array
 */
function createFile($userId, $fileData) {
    global $pdo;
    
    try {
        $fileName = $fileData['name'];
        $fileTmpPath = $fileData['tmp_name'];
        $fileSize = $fileData['size'];
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
        
        // Validate file
        if ($fileSize > (5 * 1024 * 1024)) { // 5MB max
            return [
                'success' => false,
                'message' => 'File size exceeds limit'
            ];
        }
        
        $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        if (!in_array(strtolower($fileType), $allowedTypes)) {
            return [
                'success' => false,
                'message' => 'File type not allowed'
            ];
        }
        
        // Generate unique filename
        $uniqueFileName = uniqid() . '_' . sanitizeFileName($fileName);
        $uploadPath = $UPLOAD_BASE_PATH . 'user_' . $userId . '/';
        
        // Create user directory if not exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $fullPath = $uploadPath . $uniqueFileName;
        
        // Move uploaded file
        if (!move_uploaded_file($fileTmpPath, $fullPath)) {
            return [
                'success' => false,
                'message' => 'Failed to upload file'
            ];
        }
        
        // Register file in database
        $stmt = $pdo->prepare("
            INSERT INTO uploads (
                user_id, animal_id, file_name, file_path, 
                file_type, file_size, description
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $fileData['animal_id'] ?? null,
            $fileName,
            $fullPath,
            $fileType,
            $fileSize,
            $fileData['description'] ?? ''
        ]);
        
        return [
            'success' => true,
            'message' => 'File created successfully',
            'upload_id' => $pdo->lastInsertId(),
            'file_path' => $fullPath
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'File creation error: ' . $e->getMessage()
        ];
    }
}

/**
 * READ FILE: Retrieve file from storage
 * Simulates file read operation
 * 
 * @param int $uploadId
 * @return array|false
 */
function readFile($uploadId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM uploads WHERE upload_id = ? AND status = 'active'
        ");
        $stmt->execute([$uploadId]);
        $fileInfo = $stmt->fetch();
        
        if (!$fileInfo) {
            return false;
        }
        
        // Check if file exists
        if (!file_exists($fileInfo['file_path'])) {
            return false;
        }
        
        return $fileInfo;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * DELETE FILE: Remove file from storage
 * Simulates file deletion - soft delete (mark as deleted)
 * 
 * @param int $uploadId
 * @return array
 */
function deleteFile($uploadId) {
    global $pdo;
    
    try {
        // Get file info
        $stmt = $pdo->prepare("SELECT * FROM uploads WHERE upload_id = ?");
        $stmt->execute([$uploadId]);
        $file = $stmt->fetch();
        
        if (!$file) {
            return [
                'success' => false,
                'message' => 'File not found'
            ];
        }
        
        // Soft delete - mark as deleted
        $stmt = $pdo->prepare("
            UPDATE uploads SET status = 'deleted' WHERE upload_id = ?
        ");
        $stmt->execute([$uploadId]);
        
        // Physical deletion (optional)
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }
        
        return [
            'success' => true,
            'message' => 'File deleted successfully'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'File deletion error: ' . $e->getMessage()
        ];
    }
}

/**
 * LIST FILES: Directory listing for user
 * Simulates file system directory traversal
 * 
 * @param int $userId
 * @return array
 */
function listUserFiles($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM uploads
            WHERE user_id = ? AND status = 'active'
            ORDER BY upload_date DESC
        ");
        $stmt->execute([$userId]);
        $files = $stmt->fetchAll();
        
        return [
            'success' => true,
            'files' => $files,
            'total_files' => count($files)
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error listing files: ' . $e->getMessage()
        ];
    }
}

/**
 * GET FILE SYSTEM STATS
 * Shows total storage used, file count, etc.
 * 
 * @param int $userId
 * @return array
 */
function getFileSystemStats($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_files,
                SUM(file_size) as total_size,
                AVG(file_size) as avg_file_size,
                MAX(file_size) as largest_file
            FROM uploads
            WHERE user_id = ? AND status = 'active'
        ");
        $stmt->execute([$userId]);
        $stats = $stmt->fetch();
        
        $stats['total_size_mb'] = round(($stats['total_size'] ?? 0) / (1024 * 1024), 2);
        
        return [
            'success' => true,
            'statistics' => $stats
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error getting file stats: ' . $e->getMessage()
        ];
    }
}

/**
 * Sanitize filename
 * @param string $fileName
 * @return string
 */
function sanitizeFileName($fileName) {
    return preg_replace("/[^a-zA-Z0-9._-]/", "", $fileName);
}

?>
