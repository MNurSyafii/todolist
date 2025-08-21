<?php
// index.php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$user = getCurrentUser();
$filter = $_GET['filter'] ?? 'all';
$tasks = getTasks($user['id'], $filter);
$stats = getTaskStats($user['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TodoList App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .glass {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .glass-white {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .task-card {
            transition: all 0.3s ease;
        }
        .task-card:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.3);
        }
        .modal {
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body class="min-h-screen p-4">
    <!-- Header -->
    <header class="glass rounded-2xl p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h1>
                <p class="text-white/80">Let's organize your tasks and boost productivity</p>
            </div>
            <div class="flex items-center space-x-4 mt-4 md:mt-0">
                <button 
                    onclick="openAddModal()" 
                    class="bg-white/20 hover:bg-white/30 text-white px-6 py-3 rounded-lg font-semibold transition duration-200 flex items-center space-x-2"
                >
                    <i class="fas fa-plus"></i>
                    <span>Add Task</span>
                </button>
                <a href="logout.php" class="text-white/80 hover:text-white transition duration-200">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="glass-white rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-white"><?php echo $stats['total']; ?></div>
            <div class="text-white/80 text-sm">Total Tasks</div>
        </div>
        <div class="glass-white rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-green-300"><?php echo $stats['completed']; ?></div>
            <div class="text-white/80 text-sm">Completed</div>
        </div>
        <div class="glass-white rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-yellow-300"><?php echo $stats['pending']; ?></div>
            <div class="text-white/80 text-sm">Pending</div>
        </div>
        <div class="glass-white rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-red-300"><?php echo $stats['overdue']; ?></div>
            <div class="text-white/80 text-sm">Overdue</div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="glass rounded-xl p-2 mb-6">
        <div class="flex space-x-2">
            <a href="?filter=all" class="px-4 py-2 rounded-lg text-white transition duration-200 <?php echo $filter === 'all' ? 'bg-white/30' : 'hover:bg-white/20'; ?>">
                All Tasks
            </a>
            <a href="?filter=pending" class="px-4 py-2 rounded-lg text-white transition duration-200 <?php echo $filter === 'pending' ? 'bg-white/30' : 'hover:bg-white/20'; ?>">
                Pending
            </a>
            <a href="?filter=completed" class="px-4 py-2 rounded-lg text-white transition duration-200 <?php echo $filter === 'completed' ? 'bg-white/30' : 'hover:bg-white/20'; ?>">
                Completed
            </a>
        </div>
    </div>

    <!-- Tasks Container -->
    <div class="glass rounded-2xl p-6">
        <h2 class="text-xl font-semibold text-white mb-4">
            <?php 
            echo $filter === 'all' ? 'All Tasks' : 
                ($filter === 'pending' ? 'Pending Tasks' : 'Completed Tasks');
            ?>
        </h2>

        <?php if (empty($tasks)): ?>
            <div class="text-center py-12">
                <i class="fas fa-tasks text-6xl text-white/30 mb-4"></i>
                <p class="text-white/60 text-lg">No tasks found</p>
                <p class="text-white/40">Start by adding your first task!</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($tasks as $task): ?>
                    <div class="task-card glass-white rounded-xl p-6 <?php echo getPriorityClass($task['priority']); ?>">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                            <div class="flex-1 mb-4 md:mb-0">
                                <div class="flex items-center space-x-3 mb-2">
                                    <button 
                                        onclick="toggleStatus(<?php echo $task['id']; ?>, <?php echo $task['status']; ?>)"
                                        class="text-2xl transition duration-200 <?php echo $task['status'] ? 'text-green-400 hover:text-green-300' : 'text-white/40 hover:text-white/60'; ?>"
                                    >
                                        <i class="<?php echo $task['status'] ? 'fas fa-check-circle' : 'far fa-circle'; ?>"></i>
                                    </button>
                                    <h3 class="text-lg font-semibold text-white <?php echo $task['status'] ? 'line-through opacity-60' : ''; ?>">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </h3>
                                    <?php echo getPriorityBadge($task['priority']); ?>
                                </div>
                                
                                <?php if ($task['description']): ?>
                                    <p class="text-white/80 mb-2 <?php echo $task['status'] ? 'line-through opacity-60' : ''; ?>">
                                        <?php echo htmlspecialchars($task['description']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="flex items-center space-x-4 text-sm text-white/60">
                                    <?php if ($task['deadline']): ?>
                                        <div class="flex items-center space-x-1 <?php echo isOverdue($task['deadline']) && !$task['status'] ? 'text-red-300' : ''; ?>">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span><?php echo formatDate($task['deadline']); ?></span>
                                            <?php if (isOverdue($task['deadline']) && !$task['status']): ?>
                                                <span class="text-red-300 font-semibold">(Overdue)</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-clock"></i>
                                        <span>Created <?php echo date('M d', strtotime($task['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex space-x-2">
                                <button 
                                    onclick="openEditModal(<?php echo htmlspecialchars(json_encode($task)); ?>)"
                                    class="bg-blue-500/20 hover:bg-blue-500/30 text-blue-200 px-3 py-2 rounded-lg transition duration-200"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button 
                                    onclick="deleteTask(<?php echo $task['id']; ?>)"
                                    class="bg-red-500/20 hover:bg-red-500/30 text-red-200 px-3 py-2 rounded-lg transition duration-200"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add/Edit Task Modal -->
    <div id="taskModal" class="fixed inset-0 bg-black/50 modal hidden items-center justify-center z-50" onclick="closeModal(event)">
        <div class="glass rounded-2xl p-8 mx-4 w-full max-w-md" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center mb-6">
                <h3 id="modalTitle" class="text-xl font-semibold text-white">Add New Task</h3>
                <button onclick="closeModal()" class="text-white/60 hover:text-white text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="taskForm">
                <input type="hidden" id="taskId" value="">
                
                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-white/90 text-sm font-medium mb-2">Title *</label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            class="w-full px-4 py-3 glass rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30"
                            placeholder="Enter task title"
                            required
                        >
                    </div>

                    <div>
                        <label for="description" class="block text-white/90 text-sm font-medium mb-2">Description</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="3"
                            class="w-full px-4 py-3 glass rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30"
                            placeholder="Enter task description (optional)"
                        ></textarea>
                    </div>

                    <div>
                        <label for="deadline" class="block text-white/90 text-sm font-medium mb-2">Deadline</label>
                        <input 
                            type="date" 
                            id="deadline" 
                            name="deadline" 
                            class="w-full px-4 py-3 glass rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/30"
                        >
                    </div>

                    <div>
                        <label for="priority" class="block text-white/90 text-sm font-medium mb-2">Priority</label>
                        <select 
                            id="priority" 
                            name="priority" 
                            class="w-full px-4 py-3 glass rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/30"
                        >
                            <option value="low">Low Priority</option>
                            <option value="medium" selected>Medium Priority</option>
                            <option value="high">High Priority</option>
                        </select>
                    </div>
                </div>

                <div class="flex space-x-4 mt-6">
                    <button 
                        type="submit" 
                        class="flex-1 bg-white/20 hover:bg-white/30 text-white font-semibold py-3 rounded-lg transition duration-200"
                    >
                        <span id="submitText">Add Task</span>
                    </button>
                    <button 
                        type="button" 
                        onclick="closeModal()" 
                        class="flex-1 bg-red-500/20 hover:bg-red-500/30 text-white font-semibold py-3 rounded-lg transition duration-200"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Task';
            document.getElementById('submitText').textContent = 'Add Task';
            document.getElementById('taskForm').reset();
            document.getElementById('taskId').value = '';
            document.getElementById('taskModal').classList.remove('hidden');
            document.getElementById('taskModal').classList.add('flex');
        }

        function openEditModal(task) {
            document.getElementById('modalTitle').textContent = 'Edit Task';
            document.getElementById('submitText').textContent = 'Update Task';
            document.getElementById('taskId').value = task.id;
            document.getElementById('title').value = task.title;
            document.getElementById('description').value = task.description || '';
            document.getElementById('deadline').value = task.deadline || '';
            document.getElementById('priority').value = task.priority;
            document.getElementById('taskModal').classList.remove('hidden');
            document.getElementById('taskModal').classList.add('flex');
        }

        function closeModal(event) {
            if (event && event.target !== event.currentTarget) return;
            document.getElementById('taskModal').classList.add('hidden');
            document.getElementById('taskModal').classList.remove('flex');
        }

        // Task operations
        document.getElementById('taskForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const taskId = document.getElementById('taskId').value;
            const url = taskId ? 'api/update_task.php' : 'api/add_task.php';
            
            if (taskId) {
                formData.append('id', taskId);
            }

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the task');
            });
        });

        function toggleStatus(id, currentStatus) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('status', currentStatus ? 0 : 1);

            fetch('api/toggle_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the task');
            });
        }

        function deleteTask(id) {
            if (!confirm('Are you sure you want to delete this task?')) return;

            const formData = new FormData();
            formData.append('id', id);

            fetch('api/delete_task.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the task');
            });
        }

        // Set minimum date to today
        document.getElementById('deadline').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>