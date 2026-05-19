<?php
class Admin {
    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }


    // LOGIN
    public function login($username, $password) {
        $query = "SELECT * FROM " . $this->table . "
                  WHERE username = :user";

        $stmt = $this->conn->prepare($query);

        $stmt->execute([
            'user' => $username
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }
}
?>