<?php
// ============================================
// UPLOAD FILE API
// Smart Livestock Management System
// Handle file uploads
// ============================================

require_once '../config.php';
require_once '../auth.php';
require_once '../uploads.php';

// Check authentication
$userId = authenticateUser();
if (!$userId) {
    sendJSON(['success' => false, 'message' => 'Unauthorized'], 401);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        sendJSON(['success' => false, 'message' => 'No file uploaded or upload error'], 400);
        exit;
    }

    $file = $_FILES['file'];
    $animalId = isset($_POST['animal_id']) ? (int)$_POST['animal_id'] : null;
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;

    // Validate file
    if (!isAllowedFileType($file['name'])) {
        sendJSON(['success' => false, 'message' => 'File type not allowed. Allowed types: PDF, JPG, PNG, GIF, DOC, DOCX, TXT'], 400);
        exit;
    }

    // Check file size (max 10MB)
    $maxSize = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $maxSize) {
        sendJSON(['success' => false, 'message' => 'File size too large. Maximum size: 10MB'], 400);
        exit;
    }

    // Generate unique filename and path
    $uniqueFilename = generateUniqueFilename($file['name'], $userId);
    $uploadDir = __DIR__ . '/../../' . dirname($uniqueFilename);

    // Ensure upload directory exists
    if (!ensureUploadDirectory($uploadDir)) {
        sendJSON(['success' => false, 'message' => 'Failed to create upload directory'], 500);
        exit;
    }

    // Move uploaded file
    $fullPath = __DIR__ . '/../../' . $uniqueFilename;
    if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
        sendJSON(['success' => false, 'message' => 'Failed to save uploaded file'], 500);
        exit;
    }

    // OS Concept: Create process for this operation
    require_once '../../os_simulation/process_management.php';
    $process = createProcess($userId, 'file_upload');

    // Save upload record to database
    $uploadData = [
        'animal_id' => $animalId,
        'file_name' => $file['name'],
        'file_path' => $uniqueFilename,
        'file_type' => getFileExtension($file['name']),
        'file_size' => $file['size'],
        'description' => $description
    ];

    $uploadId = createUpload($userId, $uploadData);

    if ($uploadId) {
        // Update process status
        updateProcessStatus($process['process_id'], 'completed');

        sendJSON([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => [
                'upload_id' => $uploadId,
                'file_path' => $uniqueFilename
            ]
        ], 200);
    } else {
        // Update process status
        updateProcessStatus($process['process_id'], 'failed');

        // Delete uploaded file on database error
        unlink($fullPath);

        sendJSON(['success' => false, 'message' => 'Failed to save upload record'], 500);
    }
} else {
    sendJSON(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>