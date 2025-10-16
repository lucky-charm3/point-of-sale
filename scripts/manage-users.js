// Add a function to safely get HTML attribute values
function escapeHtml(unsafe) {
    return unsafe.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

function fetchUsers(search="") {
    const usersTableBody = document.getElementById('usersTableBody');
    if (!usersTableBody) return;

    fetch(`../apis/users-api.php?action=getAll&search=${encodeURIComponent(search)}`)
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                usersTableBody.innerHTML = '';
                response.data.forEach(user => {
                    let actions = [];
                
                actions.push({
                    type: 'view',
                    label: 'View Details',
                    onclick: `viewUser(${user.id}); actionDropdown.closeAll();`
                });

                if (window.userRole === 'admin' || window.userRole === 'manager') {
                    actions.push({
                        type: 'edit',
                        label: 'Edit User',
                        onclick: `openEditUserModal(${user.id}); actionDropdown.closeAll();`
                    });
                }

                if (window.userRole === 'admin') {
                    actions.push({
                        type: 'delete',
                        label: 'Delete User',
                        onclick: `confirmDelete(${user.id}, 'user', () => deleteProduct(${user.id})); actionDropdown.closeAll();`
                    });
                }

                const actionDropdownHTML = actionDropdown.createDropdown(actions);

                    usersTableBody.innerHTML += `
                        <tr>
                            <td>#${user.id}</td>
                            <td>${user.username}</td>
                            <td>${user.email}</td>
                            <td>${user.phone}</td>
                            <td>${user.role}</td>
                            <td>${actionDropdownHTML}</td>
                        </tr>
                    `;
                });
            } else {
                showToast(response.message, 'error');
            }
        })
        .catch(err => {
            console.error('Error fetching users:', err);
            showToast('An unexpected error occurred.', 'error');
        });
}

function fetchCashiers(search = "") {
    const usersTableBody = document.getElementById('usersTableBody');
    if (!usersTableBody) return;

    fetch(`../apis/users-api.php?action=getCashiers&search=${encodeURIComponent(search)}`)
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                usersTableBody.innerHTML = '';
                response.data.forEach(user => {
                    let actions = [{
                    type: 'view',
                    label: 'View Details',
                    onclick: `viewUser(${user.id}); actionDropdown.closeAll();`
                },
                {
                        type: 'edit',
                        label: 'Edit Cashier',
                        onclick: `openEditUserModal(${user.id}); actionDropdown.closeAll();`
                    },
                    {
                        type: 'delete',
                        label: 'Delete Cashier',
                        onclick: `confirmDelete(${user.id}, 'user', () => deleteUser(${user.id})); actionDropdown.closeAll();`
                    }
            ];

                const actionDropdownHTML = actionDropdown.createDropdown(actions);

                    usersTableBody.innerHTML += `
                        <tr>
                            <td>#${user.id}</td>
                            <td>${user.username}</td>
                            <td>${user.email}</td>
                            <td>${user.phone}</td>
                            <td>${user.role}</td>
                            <td>${actionDropdownHTML}</td>
                        </tr>
                    `;
                });
            } else {
                showToast(response.message, 'error');
            }
        })
        .catch(err => {
            console.error('Error fetching cashiers:', err);
            showToast('An unexpected error occurred.', 'error');
        });
}

function openAddUserModal() {
    document.getElementById('addModalTitle').innerText = 'Add New User';
    let roleOptions = '';
    if (window.userRole === 'manager') {
        roleOptions = `<option value="cashier">Cashier</option>`;
    } else {
        roleOptions = `
            <option value="admin">Admin</option>
            <option value="manager">Manager</option>
            <option value="cashier">Cashier</option>
        `;
    }
    document.getElementById('addModalBody').innerHTML = `
        <div class="form-group">
            <label for="addUsername">Username</label>
            <input type="text" id="addUsername" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="addEmail">Email</label>
            <input type="email" id="addEmail" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="addPhone">Phone</label>
            <input type="text" id="addPhone" name="phone" class="form-control">
        </div>
        <div class="form-group">
            <label for="addPassword">Password</label>
            <input type="password" id="addPassword" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="addRole">Role</label>
            <select id="addRole" name="role" class="form-control" required>
                ${roleOptions}
            </select>
        </div>
    `;
    document.getElementById('addForm').onsubmit = (e) => { e.preventDefault(); addUser(); };
    openModal("addModal");
}

function openEditUserModal(id) {
    fetch(`../apis/users-api.php?action=getById&id=${id}`)
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                const user = response.data;
                document.getElementById('editModalTitle').innerText = 'Edit User';
                document.getElementById('editId').value = user.id;

                let roleSelectHtml = '';
                if (window.userRole === 'manager' && user.role !== 'cashier') {
                    roleSelectHtml = `<input type="text" class="form-control" value="${escapeHtml(user.role)}" disabled>`;
                } else if (window.userRole === 'manager') {
                    roleSelectHtml = `<select id="editRole" name="role" class="form-control" required><option value="cashier" ${user.role === 'cashier' ? 'selected' : ''}>Cashier</option></select>`;
                } else {
                    roleSelectHtml = `
                        <select id="editRole" name="role" class="form-control" required>
                            <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                            <option value="manager" ${user.role === 'manager' ? 'selected' : ''}>Manager</option>
                            <option value="cashier" ${user.role === 'cashier' ? 'selected' : ''}>Cashier</option>
                            <option value="inventory" ${user.role === 'inventory' ? 'selected' : ''}>Inventory</option>
                        </select>
                    `;
                }

                document.getElementById('editModalBody').innerHTML = `
                    <div class="form-group">
                        <label for="editUsername">Username</label>
                        <input type="text" id="editUsername" name="username" value="${escapeHtml(user.username)}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editEmail">Email</label>
                        <input type="email" id="editEmail" name="email" value="${escapeHtml(user.email)}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editPhone">Phone</label>
                        <input type="text" id="editPhone" name="phone" value="${escapeHtml(user.phone)}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="editPassword">New Password (leave blank to keep current)</label>
                        <input type="password" id="editPassword" name="password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="editRole">Role</label>
                         ${roleSelectHtml}
                    </div>
                `;
                document.getElementById('editForm').onsubmit = (e) => { e.preventDefault(); updateUser(id); };
                openModal('editModal');
            } else {
                 showToast(response.message, 'error');
            }
        });
}

function addUser() {
    const formData = new FormData(document.getElementById('addForm'));
    const data = Object.fromEntries(formData.entries());

    fetch('../apis/users-api.php?action=add', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        closeModal("addModal");
        showToast(result.success ? 'User added successfully' : result.message, result.success ? 'success' : 'error');
        if (result.success) fetchUsers();
    })
    .catch(err => showToast('An unexpected error occurred.', 'error'));
}

function updateUser(id) {
    const formData = new FormData(document.getElementById('editForm'));
    const data = Object.fromEntries(formData.entries());
    data.id = id;

    fetch('../apis/users-api.php?action=update', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        closeModal('editModal');
        showToast(result.success ? 'User updated successfully' : result.message || 'Error updating user', result.success ? 'success' : 'error');
        if (result.success) fetchUsers();
    })
    .catch(err => showToast('An unexpected error occurred.', 'error'));
}

function viewUser(id) {
    fetch(`../apis/users-api.php?action=getById&id=${id}`)
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                const user = response.data;
                document.getElementById('viewModalTitle').innerText = 'User Details';
                document.getElementById('viewModalBody').innerHTML = `
                    <p><strong>ID:</strong> #${user.id}</p>
                    <p><strong>Username:</strong> ${user.username}</p>
                    <p><strong>Email:</strong> ${user.email}</p>
                    <p><strong>Phone:</strong> ${user.phone || 'N/A'}</p>
                    <p><strong>Role:</strong> ${user.role}</p>
                    <p><strong>Created:</strong> ${new Date(user.created_at).toLocaleDateString()}</p>
                `;
                openModal('viewModal');
            } else {
                 showToast(response.message, 'error');
            }
        });
}

function deleteUser(id) {
    fetch(`../apis/users-api.php?action=delete&id=${id}`)
        .then(res => res.json())
        .then(result => {
            closeModal('deleteModal');
            showToast(result.success ? 'User deleted successfully' : result.message, result.success ? 'success' : 'error');
            if (result.success) {
                fetchUsers();
            }
        })
        .catch(err => {
            closeModal('deleteModal');
            console.error('An error occurred during deletion:', err);
            showToast('An unexpected error occurred.', 'error');
        });
}


document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchUsers');
    if (searchInput) {
            fetchUsers();
        searchInput.addEventListener('input', () => {
            fetchUsers(searchInput.value);
        });
    }
     if (document.getElementById('searchCashiers')) {
        fetchCashiers();
        document.getElementById('searchCashiers').addEventListener('input', (event) => {
            fetchCashiers(event.target.value);
        });
    }
});