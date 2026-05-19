<?php
session_start();

// ============================================
// PREVENT BACK BUTTON FROM GOING TO LOGIN PAGE
// ============================================
// If user is logged in, prevent caching of login page
if (isset($_SESSION['user_id'])) {
    // Send headers to prevent browser from caching pages
    header("Cache-Control: no-cache, no-store, must-revalidate, private");
    header("Pragma: no-cache");
    header("Expires: 0");
}

require_once './config/Database.php';
require_once './controllers/AuthController.php';

$database = new Database();
$db = $database->connect();

$auth = new AuthController($db);

/*
|--------------------------------------------------------------------------
| DEFAULT PAGE - ROLE BASED
|--------------------------------------------------------------------------
| Not logged in -> login | All users -> dashboard
*/
if (!isset($_SESSION['user_id'])) {
    $defaultAction = 'login';
} else {
    $defaultAction = 'dashboard';  // ALL users default to dashboard
}

$action = $_GET['action'] ?? $defaultAction;

/*
|--------------------------------------------------------------------------
| REDIRECT LOGGED IN USERS AWAY FROM LOGIN/REGISTER/FORGOT PAGES
|--------------------------------------------------------------------------
*/
// If user is already logged in and tries to access login/register/forgot pages,
// redirect them to dashboard
$authPages = ['login', 'register', 'forgot'];
if (isset($_SESSION['user_id']) && in_array($action, $authPages)) {
    header("Location: index.php?action=dashboard");
    exit();
}

/*
|--------------------------------------------------------------------------
| ROUTES THAT DON'T REQUIRE LOGIN
|--------------------------------------------------------------------------
*/
$publicRoutes = ['login', 'register', 'forgot'];

if (
    !in_array($action, $publicRoutes)
    && !isset($_SESSION['user_id'])
) {
    header("Location: index.php?action=login");
    exit();
}

// Role check inline (no function declaration)
$isAdminUser = isset($_SESSION['position']) && $_SESSION['position'] === 'ADMIN';

switch ($action) {
    // ========================================
    // ARCHIVE RECORD (Admin Only)
    // ========================================
    case 'archive_record':
        if (!isset($_SESSION['user_id']) || !$isAdminUser) {
            header("Location: index.php?action=dashboard");
            exit();
        }
        if (isset($_GET['id'])) {
            $stmt = $db->prepare("UPDATE test_records SET is_archived = 1 WHERE id = ?");
            $stmt->execute([$_GET['id']]);
        }
        $_SESSION['toast_success'] = "Record archived successfully!";
        header("Location: index.php?action=records");
        exit();
        break;

    // ========================================
    // LOGIN
    // ========================================
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = $auth->login();
            if ($error) {
                include 'views/login.php';
            }
        } else {
            include 'views/login.php';
        }
        break;

    // ========================================
    // LOGOUT
    // ========================================
    case 'logout':
        $auth->logout();
        break;


    // ========================================
    // FORGOT PASSWORD
    // ========================================
    case 'forgot':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            
            // Check if user exists
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $error = "Username not found!";
                include 'views/forgot.php';
                break;
            }
            
            // Check if user is ADMIN
            if ($user['position'] === 'ADMIN') {
                $error = "⚠️ ADMIN accounts cannot request password reset via this form. Please contact the database administrator directly.";
                include 'views/forgot.php';
                break;
            }
            
            // Staff request - send notification to admin
            $token = bin2hex(random_bytes(32));
            $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_requested = NOW(), reset_approved = 0 WHERE id = ?");
            $stmt->execute([$token, $user['id']]);
            
            $message = "✅ Your password reset request has been sent to the administrator. Contact your Administrator for your new Password!.";
            include 'views/forgot.php';
            
        } else {
            include 'views/forgot.php';
        }
        break;

    // ========================================
    // DASHBOARD (ALL USERS)
    // ========================================
    case 'dashboard':
        include 'views/dashboard.php';
        break;

    // ========================================
    // ACCOUNT (ALL USERS - edit own account)
    // ========================================
    case 'account':
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit();
        }
        
        // Clear any record-related toast messages on account page
        if (isset($_SESSION['toast_success']) && (strpos($_SESSION['toast_success'], 'Record') !== false || strpos($_SESSION['toast_success'], 'record') !== false)) {
            unset($_SESSION['toast_success']);
        }
        
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        include 'views/account.php';
        break;

    // ========================================
    // UPDATE ACCOUNT (ALL USERS)
    // ========================================
    case 'update_account':
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("UPDATE ACCOUNT POST DATA: " . print_r($_POST, true));
            
            $userId = $_SESSION['user_id'];

            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentUser) {
                header("Location: index.php?action=account&error=user_not_found");
                exit();
            }

            if (!password_verify($_POST['current_password'], $currentUser['password'])) {
                $_SESSION['toast_error'] = "Current password incorrect.";
                header("Location: index.php?action=account");
                exit();
            }

            $newUsername = trim($_POST['username']);
            $newFullName = trim($_POST['full_name']);
            $newPassword = trim($_POST['new_password'] ?? '');
            $isAdminUser = isset($_SESSION['position']) && strtoupper(trim($_SESSION['position'])) === 'ADMIN';

            try {
                if ($isAdminUser && isset($_POST['position'])) {
                    $newPosition = strtoupper(trim($_POST['position']));

                    if (!empty($newPassword)) {
                        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("UPDATE users SET username = ?, full_name = ?, position = ?, password = ? WHERE id = ?");
                        $stmt->execute([$newUsername, $newFullName, $newPosition, $hashed, $userId]);
                    } else {
                        $stmt = $db->prepare("UPDATE users SET username = ?, full_name = ?, position = ? WHERE id = ?");
                        $stmt->execute([$newUsername, $newFullName, $newPosition, $userId]);
                    }
                    $_SESSION['position'] = $newPosition;
                } else {
                    if (!empty($newPassword)) {
                        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("UPDATE users SET username = ?, full_name = ?, password = ? WHERE id = ?");
                        $stmt->execute([$newUsername, $newFullName, $hashed, $userId]);
                    } else {
                        $stmt = $db->prepare("UPDATE users SET username = ?, full_name = ? WHERE id = ?");
                        $stmt->execute([$newUsername, $newFullName, $userId]);
                    }
                }

                $_SESSION['username'] = $newUsername;
                $_SESSION['toast_success'] = "Account updated successfully!";
                header("Location: index.php?action=account&status=updated");
                exit();
                
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                $_SESSION['toast_error'] = "Database error: " . $e->getMessage();
                header("Location: index.php?action=account");
                exit();
            }
        }
        break;

    // ========================================
    // ADD USER (Admin Only)
    // ========================================
    case 'add_user':
        if (!$isAdminUser) {
            header("Location: index.php?action=account&error=unauthorized");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newUsername = $_POST['new_username'];
            $newFullName = $_POST['full_name'];
            $newPosition = $_POST['new_position'];
            $newPass = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $stmt = $db->prepare("INSERT INTO users (username, full_name, position, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$newUsername, $newFullName, $newPosition, $newPass]);

            $_SESSION['toast_success'] = "New user added successfully!";
            header("Location: index.php?action=account");
            exit();
        }
        break;

    // ========================================
    // ADD RECORD (Admin Only)
    // ========================================
    case 'add_record':
        if (!$isAdminUser) {
            header("Location: index.php?action=dashboard&error=unauthorized");
            exit();
        }
        $auth->addRecord();
        break;

    // ========================================
    // ARCHIVED RECORDS (Admin Only)
    // ========================================
    case 'archived_records':
        if (!$isAdminUser) {
            header("Location: index.php?action=dashboard&error=unauthorized");
            exit();
        }

        $from = $_GET['from'] ?? null;
        $to   = $_GET['to'] ?? null;

        if ($from && $to) {
            $stmt = $db->prepare("SELECT * FROM test_records WHERE is_archived = 1 AND date_tested BETWEEN ? AND ? ORDER BY date_tested DESC");
            $stmt->execute([$from, $to]);
        } else {
            $stmt = $db->query("SELECT * FROM test_records WHERE is_archived = 1 ORDER BY date_tested DESC");
        }

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        include 'views/archived.php';
        break;

    // ========================================
    // RESTORE RECORD (Admin Only)
    // ========================================
    case 'restore_record':
        if (!$isAdminUser) {
            header("Location: index.php?action=dashboard&error=unauthorized");
            exit();
        }

        if(isset($_GET['id'])){
            $stmt = $db->prepare("UPDATE test_records SET is_archived = 0 WHERE id = ?");
            $stmt->execute([$_GET['id']]);
        }
        $_SESSION['toast_success'] = "Record restored successfully!";
        header("Location: index.php?action=records");
        exit();
        break;

    // ========================================
    // RECORDS (Admin Only)
    // ========================================
    case 'records':
        if (!$isAdminUser) {
            header("Location: index.php?action=dashboard&error=unauthorized");
            exit();
        }

        $search = $_GET['search'] ?? '';
        if ($search != '') {
            $stmt = $db->prepare("SELECT * FROM test_records WHERE client_name LIKE ? AND is_archived = 0 ORDER BY date_tested DESC");
            $stmt->execute(["%$search%"]);
        } else {
            $stmt = $db->query("SELECT * FROM test_records WHERE is_archived = 0 ORDER BY date_tested DESC");
        }
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        include 'views/records.php';
        break;

    // ========================================
    // REPORTS (ALL USERS)
    // ========================================
    case 'reports':
        $stmt = $db->query("SELECT * FROM test_records ORDER BY date_tested DESC");
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        include 'views/reports.php';
        break;

    // ========================================
    // RESET PASSWORD
    // ========================================
    case 'reset_password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'];
            $token = $_POST['token'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            if ($newPassword !== $confirmPassword) {
                $error = "Passwords do not match!";
                include 'views/reset_password.php';
                break;
            }
            
            // Verify token
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND reset_token IS NOT NULL");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($token, $user['reset_token'])) {
                $error = "Invalid or expired reset link!";
                include 'views/reset_password.php';
                break;
            }
            
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_approved = 1, reset_requested = NULL WHERE id = ?");
            $stmt->execute([$hashed, $userId]);
            
            $message = "Password reset successful! Please login with your new password.";
            include 'views/login.php';
        } else {
            include 'views/reset_password.php';
        }
        break;

    // ========================================
    // ADMIN RESET STAFF PASSWORD
    // ========================================
    case 'admin_reset_password':
        if (!$isAdminUser) {
            header("Location: index.php?action=dashboard&error=unauthorized");
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            if ($newPassword !== $confirmPassword) {
                $_SESSION['toast_error'] = "Passwords do not match!";
                header("Location: index.php?action=account");
                exit();
            }
            
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_approved = 1, reset_requested = NULL WHERE id = ?");
            $stmt->execute([$hashed, $userId]);
            
            $_SESSION['toast_success'] = "Password has been reset successfully!";
            header("Location: index.php?action=account");
            exit();
        }
        break;

    // ========================================
    // EDIT STAFF
    // ========================================
    case 'edit_staff':
        if (!$isAdminUser) {
            header("Location: index.php?action=dashboard&error=unauthorized");
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $staffId = $_POST['staff_id'];
            $username = trim($_POST['username']);
            $fullName = trim($_POST['full_name']);
            $position = trim($_POST['position']);
            
            $stmt = $db->prepare("UPDATE users SET username = ?, full_name = ?, position = ? WHERE id = ?");
            $stmt->execute([$username, $fullName, $position, $staffId]);
            
            $_SESSION['toast_success'] = "Staff updated successfully!";
            header("Location: index.php?action=account");
            exit();
        }
        break;

    // ========================================
    // DELETE STAFF
    // ========================================
    case 'delete_staff':
        if (!$isAdminUser) {
            header("Location: index.php?action=dashboard&error=unauthorized");
            exit();
        }
        
        $staffId = $_GET['id'];
        $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND position != 'ADMIN'");
        $stmt->execute([$staffId]);
        
        $_SESSION['toast_success'] = "Staff deleted successfully!";
        header("Location: index.php?action=account");
        exit();
        break;

    // ========================================
    // RESTORE ALL RECORDS
    // ========================================
    case 'restore_all_records':
        if (!$isAdminUser) {
            header("Location: index.php?action=dashboard&error=unauthorized");
            exit();
        }
        
        $stmt = $db->prepare("UPDATE test_records SET is_archived = 0 WHERE is_archived = 1");
        $stmt->execute();
        
        $_SESSION['toast_success'] = "All archived records have been restored!";
        header("Location: index.php?action=records");
        exit();
        break;

        // ========================================
    // UPDATE RECORD
    // ========================================
    case 'update_record':
        if (!$isAdminUser) {
            header("Location: index.php?action=dashboard&error=unauthorized");
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $recordId = $_POST['record_id'];
            $clientName = $_POST['client_name'];
            $companyName = $_POST['company_name'];
            $birthDate = $_POST['birth_date'];
            $sex = $_POST['sex'];
            $methResult = $_POST['meth_result'];
            $thcResult = $_POST['thc_result'];
            
            // Calculate age from birth date
            $age = null;
            if ($birthDate) {
                $birth = new DateTime($birthDate);
                $today = new DateTime();
                $age = $today->diff($birth)->y;
            }
            
            // Handle photo upload
            $photoPath = null;
            if (isset($_FILES['client_photo']) && $_FILES['client_photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filename = uniqid() . '_' . basename($_FILES['client_photo']['name']);
                $targetFile = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['client_photo']['tmp_name'], $targetFile)) {
                    $photoPath = $targetFile;
                }
            }
            
            if ($photoPath) {
                $stmt = $db->prepare("UPDATE test_records SET client_name = ?, company_name = ?, birth_date = ?, age = ?, sex = ?, meth_result = ?, thc_result = ?, photo_path = ? WHERE id = ?");
                $stmt->execute([$clientName, $companyName, $birthDate, $age, $sex, $methResult, $thcResult, $photoPath, $recordId]);
            } else {
                $stmt = $db->prepare("UPDATE test_records SET client_name = ?, company_name = ?, birth_date = ?, age = ?, sex = ?, meth_result = ?, thc_result = ? WHERE id = ?");
                $stmt->execute([$clientName, $companyName, $birthDate, $age, $sex, $methResult, $thcResult, $recordId]);
            }
            
            $_SESSION['toast_success'] = "Record updated successfully!";
            header("Location: index.php?action=records");
            exit();
        }
        break;

    // ========================================
    // DELETE RESET REQUEST
    // ========================================
    case 'delete_reset_request':
        if (!$isAdminUser) {
            header("Location: index.php?action=dashboard&error=unauthorized");
            exit();
        }
        
        $userId = $_GET['id'];
        $stmt = $db->prepare("UPDATE users SET reset_token = NULL, reset_requested = NULL, reset_approved = 0 WHERE id = ?");
        $stmt->execute([$userId]);
        
        $_SESSION['toast_success'] = "Password reset request has been deleted!";
        header("Location: index.php?action=account");
        exit();
        break;

    // ========================================
    // ANALYTICS (ALL USERS)
    // ========================================
    case 'analytics':
        include 'views/analytics.php';
        break;

    default:
        include 'views/login.php';
        break;
}