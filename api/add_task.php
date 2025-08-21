<?php
// api/add_task.php
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
$title = sanitize($_POST['title'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$deadline = $_POST['deadline'] ?? null;
$priority = $_POST['priority'] ?? 'medium';

// Validation
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

try {
    $stmt = $pdo->prepare("
        INSERT INTO tasks (title, description, deadline, priority, user_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $title,
        $description,
        $deadline,
        $priority,
        $user['id']
    ]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Task added successfully',
        'task_id' => $pdo->lastInsertId()
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>