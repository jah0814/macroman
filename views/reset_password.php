<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Reset Password - Macro Access</title>
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
                <h2>Reset Password</h2>
                <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
                <?php if(isset($message)) echo "<p style='color:green'>$message</p>"; ?>
                <form action="index.php?action=reset_password" method="POST">
                    <input type="hidden" name="user_id" value="<?= $_GET['user_id'] ?? '' ?>">
                    <input type="hidden" name="token" value="<?= $_GET['token'] ?? '' ?>">
                    <div class="password-wrap">
                        <input type="password" name="new_password" id="newPassword" placeholder="New Password" required>
                        <i class="fa-regular fa-eye toggle-password" data-target="newPassword"></i>
                    </div>
                    <div class="password-wrap">
                        <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm Password" required>
                        <i class="fa-regular fa-eye toggle-password" data-target="confirmPassword"></i>
                    </div>
                    <button type="submit" class="btn-primary">Reset Password</button>
                </form>
                <div class="links">
                    <a href="index.php?action=login">Back to Login</a>
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