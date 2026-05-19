<!DOCTYPE html>
<html>
<head>
    <title>Register - Macro Access</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="form-section" style="width:100%">
            <div class="login-card">
                <h2>Register Admin</h2>
                <form action="index.php?action=register" method="POST">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" class="btn-primary">Create Account</button>
                </form>
                <div class="links">
                    <a href="index.php?action=login">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>