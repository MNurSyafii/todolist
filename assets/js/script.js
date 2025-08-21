// assets/js/script.js
// Custom JavaScript untuk TodoList App

document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Initialize tooltips, animations, and other UI enhancements
    initializeAnimations();
    initializeKeyboardShortcuts();
    initializeFormValidation();
    initializeTooltips();
    
    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (alert) {
                fadeOut(alert);
            }
        });
    }, 5000);
}

// Animation Functions
function initializeAnimations() {
    // Animate task cards on load
    const taskCards = document.querySelectorAll('.task-card');
    taskCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Animate stats cards
    const statsCards = document.querySelectorAll('.stats-card');
    statsCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'scale(0.9)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'scale(1)';
        }, index * 50);
    });
}

// Keyboard Shortcuts
function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + N: Add new task
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            openAddModal();
        }
        
        // Escape: Close modal
        if (e.key === 'Escape') {
            closeModal();
        }
        
        // Ctrl/Cmd + Enter: Submit form in modal
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            const modal = document.getElementById('taskModal');
            if (modal && !modal.classList.contains('hidden')) {
                e.preventDefault();
                const form = document.getElementById('taskForm');
                if (form) {
                    form.dispatchEvent(new Event('submit'));
                }
            }
        }
    });
}

// Form Validation
function initializeFormValidation() {
    const form = document.getElementById('taskForm');
    if (!form) return;
    
    const titleInput = document.getElementById('title');
    const deadlineInput = document.getElementById('deadline');
    
    // Real-time validation
    titleInput?.addEventListener('input', function() {
        validateTitle(this);
    });
    
    deadlineInput?.addEventListener('change', function() {
        validateDeadline(this);
    });
}

function validateTitle(input) {
    const value = input.value.trim();
    const errorElement = input.parentNode.querySelector('.error-message');
    
    if (value.length < 1) {
        showFieldError(input, 'Title is required');
        return false;
    } else if (value.length > 255) {
        showFieldError(input, 'Title must be less than 255 characters');
        return false;
    } else {
        hideFieldError(input);
        return true;
    }
}

function validateDeadline(input) {
    const value = input.value;
    if (!value) return true; // Optional field
    
    const today = new Date().toISOString().split('T')[0];
    if (value < today) {
        showFieldError(input, 'Deadline cannot be in the past');
        return false;
    } else {
        hideFieldError(input);
        return true;
    }
}

function showFieldError(input, message) {
    hideFieldError(input); // Remove existing error
    
    const errorElement = document.createElement('div');
    errorElement.className = 'error-message text-red-300 text-sm mt-1';
    errorElement.textContent = message;
    
    input.parentNode.appendChild(errorElement);
    input.classList.add('border-red-400');
}

function hideFieldError(input) {
    const errorElement = input.parentNode.querySelector('.error-message');
    if (errorElement) {
        errorElement.remove();
    }
    input.classList.remove('border-red-400');
}

// Toast Notifications
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
            <button onclick="hideToast(this)" class="text-white/60 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Auto hide after 5 seconds
    setTimeout(() => hideToast(toast), 5000);
}

function hideToast(element) {
    const toast = element.closest ? element.closest('.toast') : element;
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 300);
}

// Enhanced Task Operations
function toggleStatusEnhanced(id, currentStatus) {
    const button = event.target.closest('button');
    const card = button.closest('.task-card');
    
    // Add loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
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
            // Animate the change
            card.style.transition = 'all 0.3s ease';
            card.classList.add('task-completed');
            
            showToast(data.message, 'success');
            
            // Reload after animation
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            showToast('Error: ' + (data.message || 'Unknown error'), 'error');
            // Reset button
            button.innerHTML = currentStatus ? 
                '<i class="fas fa-check-circle"></i>' : 
                '<i class="far fa-circle"></i>';
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while updating the task', 'error');
        // Reset button
        button.innerHTML = currentStatus ? 
            '<i class="fas fa-check-circle"></i>' : 
            '<i class="far fa-circle"></i>';
        button.disabled = false;
    });
}

function deleteTaskEnhanced(id) {
    if (!confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
        return;
    }

    const button = event.target.closest('button');
    const card = button.closest('.task-card');
    
    // Add loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    const formData = new FormData();
    formData.append('id', id);

    fetch('api/delete_task.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Animate removal
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'translateX(-100%)';
            
            showToast(data.message, 'success');
            
            // Remove from DOM and reload
            setTimeout(() => {
                location.reload();
            }, 300);
        } else {
            showToast('Error: ' + (data.message || 'Unknown error'), 'error');
            // Reset button
            button.innerHTML = '<i class="fas fa-trash"></i>';
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting the task', 'error');
        // Reset button
        button.innerHTML = '<i class="fas fa-trash"></i>';
        button.disabled = false;
    });
}

// Enhanced Form Submission
function submitTaskForm(form) {
    // Validate form
    const titleValid = validateTitle(document.getElementById('title'));
    const deadlineValid = validateDeadline(document.getElementById('deadline'));
    
    if (!titleValid || !deadlineValid) {
        showToast('Please fix the errors before submitting', 'error');
        return false;
    }
    
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.querySelector('span').textContent;
    
    // Add loading state
    submitButton.disabled = true;
    submitButton.querySelector('span').innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    const formData = new FormData(form);
    const taskId = document.getElementById('taskId').value;
    const url = taskId ? 'api/update_task.php' : 'api/add_task.php';
    
    if (taskId) {
        formData.append('id', taskId);
    }

    return fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeModal();
            
            // Reload page after short delay
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            showToast('Error: ' + (data.message || 'Unknown error'), 'error');
        }
        return data.success;
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while saving the task', 'error');
        return false;
    })
    .finally(() => {
        // Reset button
        submitButton.disabled = false;
        submitButton.querySelector('span').textContent = originalText;
    });
}

// Tooltips
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(event) {
    const element = event.target;
    const text = element.getAttribute('data-tooltip');
    
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
        white-space: nowrap;
    `;
    
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.bottom + 5 + 'px';
    
    element._tooltip = tooltip;
}

function hideTooltip(event) {
    const element = event.target;
    if (element._tooltip) {
        element._tooltip.remove();
        element._tooltip = null;
    }
}

// Utility Functions
function fadeOut(element) {
    element.style.transition = 'opacity 0.5s ease';
    element.style.opacity = '0';
    
    setTimeout(() => {
        if (element.parentNode) {
            element.parentNode.removeChild(element);
        }
    }, 500);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Auto-save draft functionality (future enhancement)
function initializeAutoSave() {
    const titleInput = document.getElementById('title');
    const descInput = document.getElementById('description');
    
    if (!titleInput || !descInput) return;
    
    const debouncedSave = debounce(saveDraft, 1000);
    
    titleInput.addEventListener('input', debouncedSave);
    descInput.addEventListener('input', debouncedSave);
}

function saveDraft() {
    const title = document.getElementById('title')?.value;
    const description = document.getElementById('description')?.value;
    
    if (title || description) {
        // Store in memory (since localStorage is not available)
        window.taskDraft = { title, description };
    }
}

function loadDraft() {
    if (window.taskDraft) {
        const titleInput = document.getElementById('title');
        const descInput = document.getElementById('description');
        
        if (titleInput && !titleInput.value) {
            titleInput.value = window.taskDraft.title || '';
        }
        
        if (descInput && !descInput.value) {
            descInput.value = window.taskDraft.description || '';
        }
    }
}

// Initialize auto-save when modal opens
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('taskModal');
    if (modal) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    if (!modal.classList.contains('hidden')) {
                        setTimeout(initializeAutoSave, 100);
                        setTimeout(loadDraft, 100);
                    }
                }
            });
        });
        
        observer.observe(modal, { attributes: true });
    }
});