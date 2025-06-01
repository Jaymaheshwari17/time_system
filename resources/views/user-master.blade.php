<!DOCTYPE html>
<html>

<head>
    <title>User Master</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 5px;
        }
    </style>
</head>

<body>

    <h1>User Master</h1>

    <h3>Add New User</h3>

    <!-- Add User Form -->
    <form id="addUserForm" style="display:none;">
        Name: <input type="text" name="name" required>
        Email: <input type="email" name="email" required>
        Password: <input type="password" name="password" required>
        <select name="is_active" required>
            <option value="1" selected>Active</option>
            <option value="0">Inactive</option>
        </select>

        <h4>Access Rights (Per Module)</h4>
        <div id="accessModules">
            <div>
                <strong>Users</strong><br>
                <label><input type="checkbox" name="access[users][read]" value="1"> Read</label>
                <label><input type="checkbox" name="access[users][write]" value="1"> Write</label>
                <label><input type="checkbox" name="access[users][update]" value="1"> Update</label>
                <label><input type="checkbox" name="access[users][delete]" value="1"> Delete</label>
            </div>

            <div>
                <strong>Timesheet</strong><br>
                <label><input type="checkbox" name="access[timesheet][read]" value="1"> Read</label>
                <label><input type="checkbox" name="access[timesheet][write]" value="1"> Write</label>
                <label><input type="checkbox" name="access[timesheet][update]" value="1"> Update</label>
                <label><input type="checkbox" name="access[timesheet][delete]" value="1"> Delete</label>
            </div>

            
        </div>

        <button type="submit">Add User</button>
    </form>

    <h3>User List</h3>
    <table id="usersTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <!-- Edit User Modal -->
    <div id="editUserModal" style="display:none; border:1px solid black; padding:10px; background:#eee;">
        <h3>Edit User</h3>
        <form id="editUserForm">
            <input type="hidden" name="id" />
            Name: <input type="text" name="name" required><br><br>
            Email: <input type="email" name="email" required><br><br>
            Password: <input type="password" name="password" placeholder="Leave blank to keep current"><br><br>
            <select name="is_active" required>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select><br><br>
            <button type="submit">Update User</button>
            <button type="button" onclick="closeEditModal()">Cancel</button>
        </form>
    </div>

    <script>
        const token = localStorage.getItem('auth_token');

        if (!token) {
            alert("You must login first!");
            window.location.href = '/login';
        }

        const headers = {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token,
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        };

        let userAccessRights = null;

        // Fetch user access rights first
        function fetchAccessRights() {
            return fetch('/api/access-rights', { headers })
                .then(res => {
                    if (!res.ok) throw new Error('Failed to fetch access rights');
                    return res.json();
                })
                .then(data => {
                    userAccessRights = data;
                    // Show Add form only if user has write access on users module
                    if (userAccessRights.users && userAccessRights.users.write == 1) {
                        document.getElementById('addUserForm').style.display = 'block';
                    }
                })
                .catch(err => {
                    alert('Error: ' + err.message);
                });
        }

        // Fetch and display users list
        function fetchUsers() {
            fetch('/api/users', { headers })
                .then(res => {
                    if (!res.ok) throw new Error('Failed to fetch users');
                    return res.json();
                })
                .then(users => {
                    const tbody = document.querySelector('#usersTable tbody');
                    tbody.innerHTML = '';

                    users.forEach(user => {
                        let actions = '';

                        // Show Edit button only if update_access
                        if (userAccessRights.users && userAccessRights.users.update == 1) {
                            actions += `<button onclick="showEditUser(${user.id})">Edit</button> `;
                        }

                        // Show Delete button only if delete_access
                        if (userAccessRights.users && userAccessRights.users.delete == 1) {
                            actions += `<button onclick="deleteUser(${user.id})">Delete</button>`;
                        }

                        tbody.innerHTML += `
                            <tr>
                                <td>${user.id}</td>
                                <td>${user.name}</td>
                                <td>${user.email}</td>
                                <td>${user.is_active == 1 ? 'Active' : 'Inactive'}</td>
                                <td>${actions || 'No Actions'}</td>
                            </tr>
                        `;
                    });
                })
                .catch(err => alert('Error: ' + err.message));
        }

        // Add User form submission
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = {
                name: formData.get('name'),
                email: formData.get('email'),
                password: formData.get('password'),
                is_active: formData.get('is_active') === '1' ? 1 : 0,
                access: {}
            };

            document.querySelectorAll('#accessModules > div').forEach(moduleDiv => {
                const module = moduleDiv.querySelector('strong').textContent.trim().toLowerCase();
                const accessObj = {};
                ['read', 'write', 'update', 'delete'].forEach(type => {
                    const checkbox = moduleDiv.querySelector(`input[name="access[${module}][${type}]"]`);
                    if (checkbox && checkbox.checked) {
                        accessObj[type] = 1;
                    }
                });
                if (Object.keys(accessObj).length > 0) {
                    data.access[module] = accessObj;
                }
            });

            fetch('/api/users', {
                method: 'POST',
                headers,
                body: JSON.stringify(data)
            })
            .then(res => {
                if (!res.ok) throw res;
                return res.json();
            })
            .then(() => {
                alert('User added successfully');
                this.reset();
                fetchUsers();
            })
            .catch(async err => {
                const error = await err.json();
                alert('Error: ' + (error.message || 'Something went wrong'));
            });
        });

        // Show Edit User Modal
        function showEditUser(id) {
            fetch('/api/users/' + id, { headers })
                .then(res => {
                    if (!res.ok) throw new Error('Failed to fetch user');
                    return res.json();
                })
                .then(user => {
                    const form = document.getElementById('editUserForm');
                    form.id.value = user.id;
                    form.name.value = user.name;
                    form.email.value = user.email;
                    form.password.value = '';
                    form.is_active.value = user.is_active;
                    document.getElementById('editUserModal').style.display = 'block';
                })
                .catch(err => alert('Error: ' + err.message));
        }

        // Close Edit Modal
        function closeEditModal() {
            document.getElementById('editUserModal').style.display = 'none';
        }

        // Update User form submission
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const id = this.id.value;
            const data = {
                name: this.name.value,
                email: this.email.value,
                is_active: this.is_active.value === '1' ? 1 : 0,
            };

            if (this.password.value.trim() !== '') {
                data.password = this.password.value;
            }

            fetch('/api/users/' + id, {
                method: 'PUT',
                headers,
                body: JSON.stringify(data)
            })
            .then(res => {
                if (!res.ok) throw res;
                return res.json();
            })
            .then(() => {
                alert('User updated successfully');
                closeEditModal();
                fetchUsers();
            })
            .catch(async err => {
                const error = await err.json();
                alert('Error: ' + (error.message || 'Something went wrong'));
            });
        });

        // Delete user
        function deleteUser(id) {
            if (!confirm('Are you sure you want to delete this user?')) return;

            fetch('/api/users/' + id, {
                method: 'DELETE',
                headers
            })
            .then(res => {
                if (!res.ok) throw new Error('Failed to delete user');
                return res.json();
            })
            .then(() => {
                alert('User deleted successfully');
                fetchUsers();
            })
            .catch(err => alert('Error: ' + err.message));
        }

        // Initialization: Fetch access rights first, then users list
        fetchAccessRights().then(fetchUsers);

    </script>

</body>

</html>
