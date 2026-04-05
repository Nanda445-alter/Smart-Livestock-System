<?php
// ============================================
// DELETE FILE API
// Smart Livestock Management System
// Delete an uploaded file
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
    // Get upload ID from POST data
    $input = json_decode(file_get_contents('php://input'), true);
    $uploadId = isset($input['upload_id']) ? (int)$input['upload_id'] : 0;

    if (!$uploadId) {
        sendJSON(['success' => false, 'message' => 'Upload ID is required'], 400);
        exit;
    }

    // Check if upload exists and belongs to user
    $upload = getUploadByID($uploadId);
    if (!$upload) {
        sendJSON(['success' => false, 'message' => 'File not found'], 404);
        exit;
    }

    if ($upload['user_id'] != $userId) {
        sendJSON(['success' => false, 'message' => 'Unauthorized'], 403);
        exit;
    }

    // OS Concept: Create process for this operation
    require_once '../../os_simulation/process_management.php';
    $process = createProcess($userId, 'file_delete');

    // Delete the upload record (soft delete)
    $result = deleteUpload($uploadId);

    if ($result) {
        // Update process status
        updateProcessStatus($process['process_id'], 'completed');

        // Optionally delete the physical file
        $filePath = __DIR__ . '/../../' . $upload['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        sendJSON(['success' => true, 'message' => 'File deleted successfully'], 200);
    } else {
        // Update process status
        updateProcessStatus($process['process_id'], 'failed');

        sendJSON(['success' => false, 'message' => 'Failed to delete file'], 500);
    }
} else {
    sendJSON(['success' => false, 'message' => 'Method not allowed'], 405);
}
?>