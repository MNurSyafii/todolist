<?php
// api/update_task.php
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
$title = sanitize($_POST['title'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$deadline = $_POST['deadline'] ?? null;
$priority = $_POST['priority'] ?? 'medium';

// Validation
if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Task ID is required']);
    exit();
}

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit();
}

if ($deadline && !validateDate($deadline)) {
    echo json_encode(['success' => false, 'message' => 'Invalid deadline format']);
    exit();
}

if (!in_array($priority, ['high', 'medium', 'low'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid priority']);
    exit();
}

// Convert empty deadline to null
if (empty($deadline)) {
    $deadline = null;
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
        SET title = ?, description = ?, deadline = ?, priority = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ? AND user_id = ?
    ");
    
    $stmt->execute([
        $title,
        $description,
        $deadline,
        $priority,
        $id,
        $user['id']
    ]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Task updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or task not found']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>