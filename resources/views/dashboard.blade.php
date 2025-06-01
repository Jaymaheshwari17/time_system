<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>

<h2 id="welcomeMessage">Welcome to Dashboard</h2>
<p id="userName"></p>
<button id="logoutBtn">Logout</button>

<script>
    const token = localStorage.getItem('auth_token');

    if (!token) {
        window.location.href = '/login';
    }

    // Call Dashboard API
    fetch('/api/dashboard', {
        method: 'GET',
        headers: {
            Authorization: 'Bearer ' + token
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status) {
            document.getElementById('userName').innerText = "Hello, " + data.user.name;
        } else {
            alert("Session expired, please login again.");
            window.location.href = '/login';
        }
    })
    .catch(() => {
        alert("API error");
        window.location.href = '/login';
    });

    // Logout Function
    document.getElementById('logoutBtn').addEventListener('click', function () {
        fetch('/api/logout', {
            method: 'POST',
            headers: {
                Authorization: 'Bearer ' + token
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status) {
                localStorage.removeItem('auth_token');
                window.location.href = '/login';
            }
        });
    });
</script>

</body>
</html>
