// Compliance Violations Management JavaScript

// Global variables
let currentEditId = null;
let isEditMode = false;

// DOM loaded event
document.addEventListener('DOMContentLoaded', function () {
    // Set today's date as default for violation date
    const violationDateInput = document.getElementById('violation_date');
    if (violationDateInput) {
        violationDateInput.valueAsDate = new Date();
    }

    // Initialize form submission
    const violationForm = document.getElementById('violationForm');
    if (violationForm) {
        violationForm.addEventListener('submit', handleFormSubmit);
    }

    // Status change handler
    const statusSelect = document.getElementById('status');
    if (statusSelect) {
        statusSelect.addEventListener('change', handleStatusChange);
    }

    // Close modal when clicking outside
    window.addEventListener('click', function (event) {
        const modal = document.getElementById('violationModal');
        const viewModal = document.getElementById('viewModal');

        if (event.target === modal) {
            closeModal();
        }
        if (event.target === viewModal) {
            closeViewModal();
        }
    });
});

// Handle status change - show/hide resolved_by field
function handleStatusChange() {
    const status = document.getElementById('status').value;
    const resolvedByGroup = document.getElementById('resolved_by').closest('.form-group');
    const resolutionNotesGroup = document.getElementById('resolution_notes').closest('.form-group');

    if (status === 'Resolved') {
        resolvedByGroup.style.display = 'block';
        resolutionNotesGroup.style.display = 'block';
        document.getElementById('resolved_by').required = true;
        document.getElementById('resolution_notes').required = true;
    } else {
        resolvedByGroup.style.display = 'none';
        resolutionNotesGroup.style.display = 'none';
        document.getElementById('resolved_by').required = false;
        document.getElementById('resolution_notes').required = false;
        document.getElementById('resolved_by').value = '';
        document.getElementById('resolution_notes').value = '';
    }
}

// Open add modal
function openAddModal() {
    isEditMode = false;
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Report New Violation';
    document.getElementById('violationForm').reset();
    document.getElementById('violationId').value = '';

    // Set today's date as default
    const violationDateInput = document.getElementById('violation_date');
    if (violationDateInput) {
        violationDateInput.valueAsDate = new Date();
    }

    // Handle initial status change
    handleStatusChange();

    document.getElementById('violationModal').style.display = 'block';
}

// Close modal
function closeModal() {
    document.getElementById('violationModal').style.display = 'none';
    isEditMode = false;
    currentEditId = null;
}

// Close view modal
function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
}

// Handle form submission
async function handleFormSubmit(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const action = isEditMode ? 'update' : 'create';
    formData.append('action', action);

    if (isEditMode && currentEditId) {
        formData.append('id', currentEditId);
    }

    try {
        const response = await fetch('compliance.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification(
                isEditMode ? 'Violation updated successfully!' : 'Violation reported successfully!',
                'success'
            );
            closeModal();
            // Reload the page to show updated data
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error saving violation. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error saving violation. Please try again.', 'error');
    }
}

// View violation details
async function viewViolation(id) {
    try {
        const formData = new FormData();
        formData.append('action', 'get');
        formData.append('id', id);

        const response = await fetch('compliance.php', {
            method: 'POST',
            body: formData
        });

        const violation = await response.json();

        if (violation) {
            const viewContent = document.getElementById('viewContent');
            viewContent.innerHTML = `
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Violation ID:</div>
                    <div class=\"detail-value\">#${violation.id}</div>
                </div>
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Date:</div>
                    <div class=\"detail-value\">${formatDate(violation.violation_date)}</div>
                </div>
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Category:</div>
                    <div class=\"detail-value\"><span class=\"category-badge category-${violation.category.toLowerCase()}\">${violation.category}</span></div>
                </div>
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Severity:</div>
                    <div class=\"detail-value\"><span class=\"severity-badge severity-${violation.severity.toLowerCase()}\">${violation.severity}</span></div>
                </div>
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Reported By:</div>
                    <div class=\"detail-value\">${violation.reported_by_name}</div>
                </div>
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Status:</div>
                    <div class=\"detail-value\"><span class=\"status-badge status-${violation.status.toLowerCase()}\">${violation.status}</span></div>
                </div>
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Description:</div>
                    <div class=\"detail-value\">${violation.description}</div>
                </div>
                ${violation.resolved_by_name ? `
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Resolved By:</div>
                    <div class=\"detail-value\">${violation.resolved_by_name}</div>
                </div>
                ` : ''}
                ${violation.resolution_notes ? `
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Resolution Notes:</div>
                    <div class=\"detail-value\">${violation.resolution_notes}</div>
                </div>
                ` : ''}
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Created:</div>
                    <div class=\"detail-value\">${formatDateTime(violation.created_at)}</div>
                </div>
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Last Updated:</div>
                    <div class=\"detail-value\">${formatDateTime(violation.updated_at)}</div>
                </div>
            `;

            document.getElementById('viewModal').style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error loading violation details.', 'error');
    }
}

// Edit violation
async function editViolation(id) {
    try {
        const formData = new FormData();
        formData.append('action', 'get');
        formData.append('id', id);

        const response = await fetch('compliance.php', {
            method: 'POST',
            body: formData
        });

        const violation = await response.json();

        if (violation) {
            isEditMode = true;
            currentEditId = id;

            document.getElementById('modalTitle').textContent = 'Edit Compliance Violation';
            document.getElementById('violationId').value = violation.id;
            document.getElementById('violation_date').value = violation.violation_date.split(' ')[0]; // Get date part only
            document.getElementById('category').value = violation.category;
            document.getElementById('reported_by').value = violation.reported_by;
            document.getElementById('description').value = violation.description;
            document.getElementById('severity').value = violation.severity;
            document.getElementById('status').value = violation.status;
            document.getElementById('resolved_by').value = violation.resolved_by || '';
            document.getElementById('resolution_notes').value = violation.resolution_notes || '';

            // Handle status change to show/hide resolved fields
            handleStatusChange();

            document.getElementById('violationModal').style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error loading violation details.', 'error');
    }
}

// Delete violation
async function deleteViolation(id) {
    if (!confirm('Are you sure you want to delete this compliance violation? This action cannot be undone.')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        const response = await fetch('compliance.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Violation deleted successfully!', 'success');
            // Reload the page to show updated data
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error deleting violation. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error deleting violation. Please try again.', 'error');
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class=\"notification-content\">
            <span class=\"notification-message\">${message}</span>
            <button class=\"notification-close\" onclick=\"this.parentElement.parentElement.remove()\">&times;</button>
        </div>
    `;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        animation: slideInRight 0.3s ease;
        max-width: 400px;
    `;

    // Set background color based on type
    switch (type) {
        case 'success':
            notification.style.background = 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)';
            break;
        case 'error':
            notification.style.background = 'linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%)';
            break;
        case 'warning':
            notification.style.background = 'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)';
            notification.style.color = '#2c3e50';
            break;
        default:
            notification.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
    }

    // Add to document
    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Format date helper
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Format datetime helper
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Add notification styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .notification-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: inherit;
        font-size: 18px;
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.8;
        transition: opacity 0.3s ease;
    }
    
    .notification-close:hover {
        opacity: 1;
    }
`;
document.head.appendChild(style);