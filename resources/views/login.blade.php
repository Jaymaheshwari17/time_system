<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

<h2>Login</h2>
<form id="loginForm">
    <input type="email" name="email" placeholder="Email" required /><br><br>
    <input type="password" name="password" placeholder="Password" required /><br><br>
    <button type="submit">Login</button>
</form>

<p id="responseMessage"></p>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '/api/login',
            type: 'POST',
            data: {
                email: $('input[name=email]').val(),
                password: $('input[name=password]').val()
            },
            success: function(response) {
                localStorage.setItem('auth_token', response.token);
                localStorage.setItem('user_name', response.user.name);
                window.location.href = '/user-master';
            },
            error: function(xhr) {
                $('#responseMessage').text(xhr.responseJSON.message);
            }
        });
    });
</script>

</body>
</html>
