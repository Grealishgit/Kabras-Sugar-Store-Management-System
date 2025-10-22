// users.js - Handles search/filter and user modals for admin/users.php

document.addEventListener('DOMContentLoaded', function () {
    // Modal elements
    const userModal = document.getElementById('userModal');
    const deleteModal = document.getElementById('deleteModal');
    let editingUserId = null;

    // Open Add User Modal
    // Open Add User Modal
    window.openAddUserModal = function () {
        editingUserId = null;
        document.getElementById('modalTitle').textContent = 'Add New User';
        document.getElementById('userForm').reset();
        userModal.style.display = 'flex'; // <-- changed from 'block'
    };

    // Open Edit User Modal
    window.editUser = function (userId) {
        editingUserId = userId;
        document.getElementById('modalTitle').textContent = 'Edit User';
        // Fetch user data via AJAX
        fetch('../handlers/UserHandler.php?id=' + userId)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const user = data.user;
                    document.getElementById('userId').value = user.id;
                    document.getElementById('userName').value = user.name;
                    document.getElementById('userEmail').value = user.email;
                    document.getElementById('userPhone').value = user.phone || '';
                    document.getElementById('userNationalId').value = user.national_id || '';
                    document.getElementById('userRole').value = user.role;
                    document.getElementById('userPassword').value = '';
                    userModal.style.display = 'flex';
                } else {
                    alert('User not found');
                }
            });
    };
    // Show user info modal (read-only, no password)
    window.viewUser = function (userId) {
        fetch('../handlers/UserHandler.php?id=' + userId)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const user = data.user;
                    let info = `<strong>Name:</strong> ${user.name}<br>`;
                    info += `<strong>Email:</strong> ${user.email}<br>`;
                    info += `<strong>Phone:</strong> ${user.phone || 'N/A'}<br>`;
                    info += `<strong>National ID:</strong> ${user.national_id || 'N/A'}<br>`;
                    info += `<strong>Role:</strong> ${user.role}`;
                    document.getElementById('modalTitle').textContent = 'User Details';
                    document.getElementById('userForm').style.display = 'none';
                    if (!document.getElementById('userInfo')) {
                        const infoDiv = document.createElement('div');
                        infoDiv.id = 'userInfo';
                        infoDiv.className = 'user-info-modal';
                        document.querySelector('.modal-content').appendChild(infoDiv);
                    }
                    document.getElementById('userInfo').innerHTML = info;
                    userModal.style.display = 'flex';
                } else {
                    alert('User not found');
                }
            });
    };

    // Open Delete Confirmation Modal
    window.deleteUser = function (userId) {
        editingUserId = userId;
        deleteModal.style.display = 'flex'; // <-- changed
    };


    // Close User Modal
    window.closeUserModal = function () {
        userModal.style.display = 'none';
        document.getElementById('userForm').style.display = '';
        if (document.getElementById('userInfo')) {
            document.getElementById('userInfo').remove();
        }
    };

    // Close Delete Modal
    window.closeDeleteModal = function () {
        deleteModal.style.display = 'none';
    };

    // Confirm Delete
    window.confirmDelete = function () {
        if (!editingUserId) return;
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', editingUserId);
        fetch('../handlers/UserHandler.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`tr[data-user-id="${editingUserId}"]`).remove();
                    alert('User deleted');
                } else {
                    alert('Delete failed');
                }
                closeDeleteModal();
            });
    };
    // Edit modal submit handler
    document.getElementById('userForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        if (editingUserId) {
            formData.append('action', 'update');
            formData.append('id', editingUserId);
            fetch('../handlers/UserHandler.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('User updated');
                        location.reload();
                    } else {
                        alert('Update failed');
                    }
                    closeUserModal();
                });
        } else {
            formData.append('action', 'add');
            fetch('../handlers/UserHandler.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('User added');
                    location.reload();
                } else {
                    alert('Add user failed');
                }
                closeUserModal();
            });
        }
    });

    // Search and Filter
    const searchInput = document.getElementById('searchUsers');
    const userRows = document.querySelectorAll('.user-row');
    searchInput.addEventListener('input', function () {
        const query = searchInput.value.toLowerCase();
        userRows.forEach(row => {
            const name = row.querySelector('.user-name').textContent.toLowerCase();
            const email = row.querySelector('.user-email').textContent.toLowerCase();
            const role = row.getAttribute('data-role').toLowerCase();
            if (name.includes(query) || email.includes(query) || role.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const filter = btn.getAttribute('data-filter').toLowerCase();
            userRows.forEach(row => {
                if (filter === 'all' || row.getAttribute('data-role').toLowerCase() === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    // Close modals on outside click
    window.onclick = function (event) {
        if (event.target === userModal) userModal.style.display = 'none';
        if (event.target === deleteModal) deleteModal.style.display = 'none';
    };
});
