<?php
// api/toggle_status.php
header('Content-Type: application/json');
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user = getCurrentUser();
$id = (int)($_POST['id'] ?? 0);
$status = (int)($_POST['status'] ?? 0);

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Task ID is required']);
    exit();
}

// Validate status (0 or 1)
if ($status !== 0 && $status !== 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit();
}

// Check if task belongs to current user
$task = getTaskById($id, $user['id']);
if (!$task) {
    echo json_encode(['success' => false, 'message' => 'Task not found or access denied']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        UPDATE tasks 
        SET status = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ? AND user_id = ?
    ");
    
    $stmt->execute([$status, $id, $user['id']]);
    
    if ($stmt->rowCount() > 0) {
        $statusText = $status ? 'completed' : 'pending';
        echo json_encode([
            'success' => true, 
            'message' => "Task marked as $statusText",
            'new_status' => $status
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or task not found']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>