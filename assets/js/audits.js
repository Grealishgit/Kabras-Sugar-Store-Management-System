// Audit Reports Management JavaScript

// Global variables
let currentEditId = null;
let isEditMode = false;

// DOM loaded event
document.addEventListener('DOMContentLoaded', function () {
    // Set today's date as default for audit date
    const auditDateInput = document.getElementById('audit_date');
    if (auditDateInput) {
        auditDateInput.valueAsDate = new Date();
    }

    // Initialize form submission
    const auditForm = document.getElementById('auditForm');
    if (auditForm) {
        auditForm.addEventListener('submit', handleFormSubmit);
    }

    // Close modal when clicking outside
    window.addEventListener('click', function (event) {
        const modal = document.getElementById('auditModal');
        const viewModal = document.getElementById('viewModal');

        if (event.target === modal) {
            closeModal();
        }
        if (event.target === viewModal) {
            closeViewModal();
        }
    });
});

// Open add modal
function openAddModal() {
    isEditMode = false;
    currentEditId = null;
    document.getElementById('modalTitle').textContent = 'Add New Audit';
    document.getElementById('auditForm').reset();
    document.getElementById('auditId').value = '';

    // Set today's date as default
    const auditDateInput = document.getElementById('audit_date');
    if (auditDateInput) {
        auditDateInput.valueAsDate = new Date();
    }

    document.getElementById('auditModal').style.display = 'block';
}

// Close modal
function closeModal() {
    document.getElementById('auditModal').style.display = 'none';
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
        const response = await fetch('audits.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification(
                isEditMode ? 'Audit updated successfully!' : 'Audit created successfully!',
                'success'
            );
            closeModal();
            // Reload the page to show updated data
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error saving audit. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error saving audit. Please try again.', 'error');
    }
}

// View audit details
async function viewAudit(id) {
    try {
        const formData = new FormData();
        formData.append('action', 'get');
        formData.append('id', id);

        const response = await fetch('audits.php', {
            method: 'POST',
            body: formData
        });

        const audit = await response.json();

        if (audit) {
            const viewContent = document.getElementById('viewContent');
            viewContent.innerHTML = `
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Audit ID:</div>
                    <div class=\"detail-value\">#${audit.id}</div>
                </div>
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Date:</div>
                    <div class=\"detail-value\">${formatDate(audit.audit_date)}</div>
                </div>
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Type:</div>
                    <div class=\"detail-value\"><span class=\"type-badge type-${audit.audit_type.toLowerCase()}\">${audit.audit_type}</span></div>
                </div>
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Conducted By:</div>
                    <div class=\"detail-value\">${audit.conducted_by_name}</div>
                </div>
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Status:</div>
                    <div class=\"detail-value\"><span class=\"status-badge status-${audit.status.toLowerCase()}\">${audit.status}</span></div>
                </div>
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Completion Date:</div>
                    <div class=\"detail-value\">${audit.completion_date ? formatDate(audit.completion_date) : 'N/A'}</div>
                </div>
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Comments:</div>
                    <div class=\"detail-value\">${audit.comments || 'No comments'}</div>
                </div>
                <div class=\"detail-row\">
                    <div class=\"detail-label\">Follow-up Actions:</div>
                    <div class=\"detail-value\">${audit.follow_up_actions || 'No follow-up actions'}</div>
                </div>
            `;

            document.getElementById('viewModal').style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error loading audit details.', 'error');
    }
}

// Edit audit
async function editAudit(id) {
    try {
        const formData = new FormData();
        formData.append('action', 'get');
        formData.append('id', id);

        const response = await fetch('audits.php', {
            method: 'POST',
            body: formData
        });

        const audit = await response.json();

        if (audit) {
            isEditMode = true;
            currentEditId = id;

            document.getElementById('modalTitle').textContent = 'Edit Audit';
            document.getElementById('auditId').value = audit.id;
            document.getElementById('audit_date').value = audit.audit_date.split(' ')[0]; // Get date part only
            document.getElementById('audit_type').value = audit.audit_type;
            document.getElementById('conducted_by').value = audit.conducted_by;
            document.getElementById('status').value = audit.status;
            document.getElementById('comments').value = audit.comments || '';
            document.getElementById('follow_up_actions').value = audit.follow_up_actions || '';
            document.getElementById('completion_date').value = audit.completion_date ? audit.completion_date.split(' ')[0] : '';

            document.getElementById('auditModal').style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error loading audit details.', 'error');
    }
}

// Delete audit
async function deleteAudit(id) {
    if (!confirm('Are you sure you want to delete this audit? This action cannot be undone.')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        const response = await fetch('audits.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Audit deleted successfully!', 'success');
            // Reload the page to show updated data
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error deleting audit. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error deleting audit. Please try again.', 'error');
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

// COMPLIANCE AUDIT FUNCTIONS

// Global variables for compliance audits
let currentComplianceEditId = null;
let isComplianceEditMode = false;

// Open add compliance modal
function openAddComplianceModal() {
    isComplianceEditMode = false;
    currentComplianceEditId = null;
    document.getElementById('complianceModalTitle').textContent = 'Add New Compliance Audit';
    document.getElementById('complianceAuditForm').reset();
    document.getElementById('complianceAuditId').value = '';

    // Set today's date as default
    const complianceAuditDateInput = document.getElementById('compliance_audit_date');
    if (complianceAuditDateInput) {
        complianceAuditDateInput.valueAsDate = new Date();
    }

    document.getElementById('complianceAuditModal').style.display = 'block';
}

// Close compliance modal
function closeComplianceModal() {
    document.getElementById('complianceAuditModal').style.display = 'none';
    isComplianceEditMode = false;
    currentComplianceEditId = null;
}

// Close view compliance modal
function closeViewComplianceModal() {
    document.getElementById('viewComplianceModal').style.display = 'none';
}

// Handle compliance audit form submission
async function handleComplianceFormSubmit(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const action = isComplianceEditMode ? 'update_compliance' : 'create_compliance';
    formData.append('action', action);

    if (isComplianceEditMode && currentComplianceEditId) {
        formData.append('id', currentComplianceEditId);
    }

    try {
        const response = await fetch('audits.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification(
                isComplianceEditMode ? 'Compliance audit updated successfully!' : 'Compliance audit created successfully!',
                'success'
            );
            closeComplianceModal();
            // Reload the page to show updated data
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error saving compliance audit. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error saving compliance audit. Please try again.', 'error');
    }
}

// View compliance audit details
async function viewComplianceAudit(id) {
    try {
        const formData = new FormData();
        formData.append('action', 'get_compliance');
        formData.append('id', id);

        const response = await fetch('audits.php', {
            method: 'POST',
            body: formData
        });

        const complianceAudit = await response.json();

        if (complianceAudit) {
            const viewContent = document.getElementById('viewComplianceContent');
            viewContent.innerHTML = `
                <div class="detail-row">
                    <div class="detail-label">Compliance Audit ID:</div>
                    <div class="detail-value">#${complianceAudit.id}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date:</div>
                    <div class="detail-value">${formatDate(complianceAudit.audit_date)}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Type:</div>
                    <div class="detail-value"><span class="type-badge type-${complianceAudit.audit_type.toLowerCase()}">${complianceAudit.audit_type}</span></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Conducted By:</div>
                    <div class="detail-value">${complianceAudit.conducted_by_name}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value"><span class="status-badge status-${complianceAudit.status.toLowerCase()}">${complianceAudit.status}</span></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Comments:</div>
                    <div class="detail-value">${complianceAudit.comments || 'No comments'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Created:</div>
                    <div class="detail-value">${formatDateTime(complianceAudit.created_at)}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Last Updated:</div>
                    <div class="detail-value">${formatDateTime(complianceAudit.updated_at)}</div>
                </div>
            `;

            document.getElementById('viewComplianceModal').style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error loading compliance audit details.', 'error');
    }
}

// Edit compliance audit
async function editComplianceAudit(id) {
    try {
        const formData = new FormData();
        formData.append('action', 'get_compliance');
        formData.append('id', id);

        const response = await fetch('audits.php', {
            method: 'POST',
            body: formData
        });

        const complianceAudit = await response.json();

        if (complianceAudit) {
            isComplianceEditMode = true;
            currentComplianceEditId = id;

            document.getElementById('complianceModalTitle').textContent = 'Edit Compliance Audit';
            document.getElementById('complianceAuditId').value = complianceAudit.id;
            document.getElementById('compliance_audit_date').value = complianceAudit.audit_date.split(' ')[0]; // Get date part only
            document.getElementById('compliance_audit_type').value = complianceAudit.audit_type;
            document.getElementById('compliance_conducted_by').value = complianceAudit.conducted_by;
            document.getElementById('compliance_status').value = complianceAudit.status;
            document.getElementById('compliance_comments').value = complianceAudit.comments || '';

            document.getElementById('complianceAuditModal').style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error loading compliance audit details.', 'error');
    }
}

// Delete compliance audit
async function deleteComplianceAudit(id) {
    if (!confirm('Are you sure you want to delete this compliance audit? This action cannot be undone.')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'delete_compliance');
        formData.append('id', id);

        const response = await fetch('audits.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Compliance audit deleted successfully!', 'success');
            // Reload the page to show updated data
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error deleting compliance audit. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error deleting compliance audit. Please try again.', 'error');
    }
}

// Format date-time helper
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

// Add event listeners for compliance audit forms
document.addEventListener('DOMContentLoaded', function () {
    // Initialize compliance audit form submission
    const complianceAuditForm = document.getElementById('complianceAuditForm');
    if (complianceAuditForm) {
        complianceAuditForm.addEventListener('submit', handleComplianceFormSubmit);
    }

    // Close modals when clicking outside
    window.addEventListener('click', function (event) {
        const complianceModal = document.getElementById('complianceAuditModal');
        const viewComplianceModal = document.getElementById('viewComplianceModal');

        if (event.target === complianceModal) {
            closeComplianceModal();
        }
        if (event.target === viewComplianceModal) {
            closeViewComplianceModal();
        }
    });
});