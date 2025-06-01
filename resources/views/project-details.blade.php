<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Project Details</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 8px; }
    </style>
</head>
<body>
    <h1>Project Details</h1>

    <form id="projectForm" style="display: none;">
        <input type="hidden" id="project_id" />
        <label>Project Name:</label>
        <input type="text" id="project_name" required><br><br>

        <label>Description:</label>
        <textarea id="description"></textarea><br><br>

        <label>Status:</label>
        <select id="status" required>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select><br><br>

        <button type="submit">Save Project</button>
        <button type="button" onclick="clearForm()">Clear</button>
    </form>

    <h2>Project List</h2>
    <table id="projectsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Project Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

<script>
    const token = localStorage.getItem('auth_token');

    const headers = {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Authorization': 'Bearer ' + token
    };

    // 👇 Access Rights (already stored in localStorage)
    const userAccessRights = JSON.parse(localStorage.getItem('userAccessRights') || '{}');

    // 👇 Show project form only if write access is available
    if (userAccessRights.projectdetails && userAccessRights.projectdetails.write == 1) {
        document.getElementById('projectForm').style.display = 'block';
    }

    function fetchProjects() {
        fetch('/api/projects', { headers })
            .then(res => res.json())
            .then(data => {
                const tbody = document.querySelector('#projectsTable tbody');
                tbody.innerHTML = '';
                data.forEach(project => {
                    let actions = '';

                    if (userAccessRights.projectdetails?.update == 1) {
                        actions += `<button onclick="editProject(${project.id})">Edit</button> `;
                    }

                    if (userAccessRights.projectdetails?.delete == 1) {
                        actions += `<button onclick="deleteProject(${project.id})">Delete</button>`;
                    }

                    tbody.innerHTML += `
                        <tr>
                            <td>${project.id}</td>
                            <td>${project.project_name}</td>
                            <td>${project.description || ''}</td>
                            <td>${project.status}</td>
                            <td>${actions}</td>
                        </tr>
                    `;
                });
            })
            .catch(() => alert('Failed to fetch projects'));
    }

    function clearForm() {
        document.getElementById('project_id').value = '';
        document.getElementById('project_name').value = '';
        document.getElementById('description').value = '';
        document.getElementById('status').value = 'active';
    }

    document.getElementById('projectForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const id = document.getElementById('project_id').value;
        const data = {
            project_name: document.getElementById('project_name').value,
            description: document.getElementById('description').value,
            status: document.getElementById('status').value
        };

        let url = '/api/projects';
        let method = 'POST';

        if (id) {
            url += '/' + id;
            method = 'PUT';
        }

        fetch(url, {
            method,
            headers,
            body: JSON.stringify(data)
        })
        .then(res => {
            if (!res.ok) throw res;
            return res.json();
        })
        .then(response => {
            alert(response.message);
            clearForm();
            fetchProjects();
        })
        .catch(async err => {
            const error = await err.json();
            alert(error.error || 'Something went wrong');
        });
    });

    function editProject(id) {
        fetch('/api/projects/' + id, { headers })
            .then(res => res.json())
            .then(project => {
                document.getElementById('project_id').value = project.id;
                document.getElementById('project_name').value = project.project_name;
                document.getElementById('description').value = project.description || '';
                document.getElementById('status').value = project.status;
            })
            .catch(() => alert('Failed to fetch project details'));
    }

    function deleteProject(id) {
        if (!confirm('Are you sure to delete this project?')) return;

        fetch('/api/projects/' + id, {
            method: 'DELETE',
            headers
        })
        .then(res => res.json())
        .then(response => {
            alert(response.message);
            fetchProjects();
        })
        .catch(() => alert('Failed to delete project'));
    }

    // Initial data load
    fetchProjects();
</script>
</body>
</html>
