<?php
// includes/functions.php

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function getPriorityClass($priority) {
    switch($priority) {
        case 'high':
            return 'border-l-4 border-red-500 bg-red-50/30';
        case 'medium':
            return 'border-l-4 border-yellow-500 bg-yellow-50/30';
        case 'low':
            return 'border-l-4 border-green-500 bg-green-50/30';
        default:
            return 'border-l-4 border-gray-500 bg-gray-50/30';
    }
}

function getPriorityBadge($priority) {
    switch($priority) {
        case 'high':
            return '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-200/80 rounded-full">High</span>';
        case 'medium':
            return '<span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-200/80 rounded-full">Medium</span>';
        case 'low':
            return '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-200/80 rounded-full">Low</span>';
        default:
            return '<span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-200/80 rounded-full">Unknown</span>';
    }
}

function formatDate($date) {
    if (!$date) return 'No deadline';
    return date('M d, Y', strtotime($date));
}

function isOverdue($deadline) {
    if (!$deadline) return false;
    return date('Y-m-d') > $deadline;
}

function getTasks($userId, $filter = 'all') {
    global $pdo;
    
    $sql = "SELECT * FROM tasks WHERE user_id = ?";
    $params = [$userId];
    
    switch($filter) {
        case 'completed':
            $sql .= " AND status = 1";
            break;
        case 'pending':
            $sql .= " AND status = 0";
            break;
    }
    
    $sql .= " ORDER BY 
                CASE priority 
                    WHEN 'high' THEN 1 
                    WHEN 'medium' THEN 2 
                    WHEN 'low' THEN 3 
                END ASC, 
                deadline ASC, 
                created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getTaskById($id, $userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    return $stmt->fetch();
}

function getTaskStats($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 0 AND deadline < CURDATE() THEN 1 ELSE 0 END) as overdue
        FROM tasks 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}
?>