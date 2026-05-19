<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Forgot Password - Macro Access</title>
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
                <h2>Forgot Password</h2>
                
                <?php if(isset($error)): ?>
                    <div style='background: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;'>
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($message)): ?>
                    <div style='background: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;'>
                        <?= $message ?>
                    </div>
                <?php endif; ?>
                
                <?php if(!isset($message) && !isset($error)): ?>
                <form action="index.php?action=forgot" method="POST">
                    <input type="text" name="username" placeholder="Username" required>
                    <button type="submit" class="btn-primary">Request Password Reset</button>
                </form>
                <?php endif; ?>
                
                <div class="links">
                    <a href="index.php?action=login">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>