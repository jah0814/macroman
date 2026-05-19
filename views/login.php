<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Login - Macro Access</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="brand-section">
            <div class="logo-placeholder">🔬</div>
            <h1>MACRO ACCESS</h1>
            <p>DRUG TESTING CENTER</p>
        </div>
        <div class="form-section">
            <div class="login-card">
                <h2>Log In</h2>
                <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
                <form action="index.php?action=login" method="POST">
                    <input type="text" name="username" placeholder="Username" required>
                    
                    <div class="password-wrap">
                        <input type="password" name="password" id="loginPassword" placeholder="Password" required>
                        <i class="fa-regular fa-eye toggle-password" data-target="loginPassword"></i>
                    </div>
                    
                    <button type="submit" class="btn-primary">Login</button>
                </form>
                <div class="links">
                    <a href="index.php?action=forgot">Forgot Password?</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.addEventListener('click', function(e) {
            const toggleBtn = e.target.closest('.toggle-password');
            if (!toggleBtn) return;
            e.preventDefault();
            
            const targetId = toggleBtn.getAttribute('data-target');
            if (!targetId) return;
            
            const input = document.getElementById(targetId);
            if (!input) return;
            
            if (input.type === 'password') {
                input.type = 'text';
                toggleBtn.classList.remove('fa-eye');
                toggleBtn.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                toggleBtn.classList.remove('fa-eye-slash');
                toggleBtn.classList.add('fa-eye');
            }
        });
    });
    </script>
</body>
</html>