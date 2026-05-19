<?php
require_once './models/Admin.php';

class AuthController {
    private $admin;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->admin = new Admin($db);
    }

    // --- AUTHENTICATION ---
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->admin->login($_POST['username'], $_POST['password']);
            if ($result) {
                $_SESSION['user_id'] = $result['id'];
                $_SESSION['user'] = $result['username'];
                $_SESSION['position'] = $result['position'];
                header("Location: index.php?action=dashboard");
                exit();
            }
            return "Invalid Credentials";
        }
    }

    public function logout() {
    // Unset all session variables
    session_unset();
    
    // Destroy the session completely
    session_destroy();
    
    // Delete session cookie to prevent any remnants
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Redirect to login with cache-busting headers
    header("Location: index.php?action=login");
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    exit();
}

    // --- ACCOUNT MANAGEMENT ---
    // Added to fix your fillable "Account" page
    public function updateAccount() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $position = $_POST['position'];
            $userId = $_SESSION['user_id'];
            
            $stmt = $this->db->prepare("UPDATE users SET username = ?, position = ? WHERE id = ?");
            $stmt->execute([$username, $position, $userId]);
            
            $_SESSION['user'] = $username;
            $_SESSION['position'] = $position;
            header("Location: index.php?action=account&status=updated");
            exit();
        }
    }

    public function addUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $_POST['new_username'];
            $pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $pos = $_POST['new_position'];

            $stmt = $this->db->prepare("INSERT INTO users (username, password, position) VALUES (?, ?, ?)");
            $stmt->execute([$user, $pass, $pos]);
            header("Location: index.php?action=account&status=user_added");
            exit();
        }
    }

    // --- RECORDS MANAGEMENT ---
    // Added to handle the "Add" button in records.php
    public function addRecord() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client = $_POST['client_name'];
            $age = $_POST['age'];
            $sex = $_POST['sex'];
            $dob = $_POST['birth_date'];
            $company = $_POST['company_name'];
            $meth = $_POST['meth_result'];
            $thc = $_POST['thc_result'];
            
            // Handle Photo Upload
            $photoPath = null;
            if (!empty($_FILES['client_photo']['name'])) {
                $photoPath = 'uploads/' . time() . '_' . $_FILES['client_photo']['name'];
                move_uploaded_file($_FILES['client_photo']['tmp_name'], $photoPath);
            }

            $stmt = $this->db->prepare("INSERT INTO test_records (client_name, age, sex, birth_date, company_name, meth_result, thc_result, photo_path, date_tested) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$client, $age, $sex, $dob, $company, $meth, $thc, $photoPath]);
            
            header("Location: index.php?action=records&status=success");
            exit();
        }
    }

    // --- ANALYTICS LOGIC ---
    // Added to make analytics.php dynamic and handle empty states
    public function getAnalyticsMetrics() {
        // Fetch total records count
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM test_records");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // If no data, return zeros to trigger "Empty State" in view
        if ($total == 0) {
            return [
                'metrics' => ['total_shown' => 0, 'total_responses' => 0, 'response_rate' => 0, 'dismissed' => 0, 'qualitative' => 0, 'score' => 0],
                'chartData' => [0, 0, 0]
            ];
        }

        // Logic for donut chart (Positive vs Negative)
        $stmtPos = $this->db->query("SELECT COUNT(*) as pos FROM test_records WHERE meth_result = 'POSITIVE' OR thc_result = 'POSITIVE'");
        $pos = $stmtPos->fetch(PDO::FETCH_ASSOC)['pos'];

        $stmtNeg = $this->db->query("SELECT COUNT(*) as neg FROM test_records WHERE meth_result = 'NEGATIVE' AND thc_result = 'NEGATIVE'");
        $neg = $stmtNeg->fetch(PDO::FETCH_ASSOC)['neg'];

        return [
            'metrics' => [
                'total_shown' => $total,
                'total_responses' => $pos + $neg,
                'response_rate' => round((($pos + $neg) / $total) * 100, 1),
                'dismissed' => 0,
                'qualitative' => 0,
                'score' => round(($pos / $total) * 100)
            ],
            'chartData' => [$pos, 0, $neg] // [Promoters, Passives, Detractors]
        ];
    }
}