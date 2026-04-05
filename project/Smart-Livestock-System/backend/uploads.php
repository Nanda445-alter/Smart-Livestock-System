<?php
// ============================================
// FILE UPLOAD MANAGEMENT MODULE
// Smart Livestock Management System
// Handles file upload operations
// ============================================

require_once __DIR__ . '/config.php';

/**
 * Get all uploads for a user
 * @param int $userId
 * @return array - Array of upload objects
 */
function getAllUploads($userId) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT u.*, a.animal_name
        FROM uploads u
        LEFT JOIN animals a ON u.animal_id = a.animal_id
        WHERE u.user_id = ? AND u.status = 'active'
        ORDER BY u.upload_date DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Get upload by ID
 * @param int $uploadId
 * @return array - Upload data
 */
function getUploadByID($uploadId) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT u.*, a.animal_name
        FROM uploads u
        LEFT JOIN animals a ON u.animal_id = a.animal_id
        WHERE u.upload_id = ? AND u.status = 'active'
    ");
    $stmt->execute([$uploadId]);
    return $stmt->fetch();
}

/**
 * Create new upload record
 * @param int $userId
 * @param array $data - Upload data
 * @return int - Upload ID or false on failure
 */
function createUpload($userId, $data) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO uploads (user_id, animal_id, file_name, file_path, file_type, file_size, description)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $data['animal_id'] ?: null,
            $data['file_name'],
            $data['file_path'],
            $data['file_type'],
            $data['file_size'],
            $data['description'] ?: null
        ]);

        return $pdo->lastInsertId();
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Delete upload (soft delete)
 * @param int $uploadId
 * @return bool - Success status
 */
function deleteUpload($uploadId) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            UPDATE uploads
            SET status = 'deleted'
            WHERE upload_id = ?
        ");
        return $stmt->execute([$uploadId]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get file extension from filename
 * @param string $filename
 * @return string - File extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file type is allowed
 * @param string $filename
 * @return bool - Whether file type is allowed
 */
function isAllowedFileType($filename) {
    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'txt'];
    $extension = getFileExtension($filename);
    return in_array($extension, $allowedExtensions);
}

/**
 * Generate unique filename
 * @param string $originalFilename
 * @param int $userId
 * @return string - Unique filename with path
 */
function generateUniqueFilename($originalFilename, $userId) {
    $extension = getFileExtension($originalFilename);
    $timestamp = time();
    $random = rand(1000, 9999);
    $basename = pathinfo($originalFilename, PATHINFO_FILENAME);

    // Sanitize filename
    $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);

    $uniqueName = $basename . '_' . $timestamp . '_' . $random . '.' . $extension;
    return 'uploads/user_' . $userId . '/' . $uniqueName;
}

/**
 * Ensure upload directory exists
 * @param string $directory
 * @return bool - Success status
 */
function ensureUploadDirectory($directory) {
    if (!file_exists($directory)) {
        return mkdir($directory, 0755, true);
    }
    return true;
}
?>