<?php
session_start();
require_once '../config/database.php';

class Auth {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function login($username, $password) {
        $query = "SELECT u.*, ug.name as group_name, ug.permissions 
                  FROM users u 
                  LEFT JOIN user_groups ug ON u.group_id = ug.id 
                  WHERE u.username = :username AND u.status = 'active'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['group_id'] = $user['group_id'];
                $_SESSION['permissions'] = json_decode($user['permissions'] ?? '{}', true);
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                return true;
            }
        }
        return false;
    }
    
    public function logout() {
        session_destroy();
        header("Location: login.php");
        exit();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header("Location: login.php");
            exit();
        }
    }
    
    public function requireRole($role) {
        if (!$this->isLoggedIn() || $_SESSION['role'] !== $role) {
            header("Location: unauthorized.php");
            exit();
        }
    }
    
    public function hasPermission($permission) {
        if ($_SESSION['role'] === 'admin') return true;
        if (isset($_SESSION['permissions']['all']) && $_SESSION['permissions']['all']) return true;
        return isset($_SESSION['permissions'][$permission]) && $_SESSION['permissions'][$permission];
    }
    
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            $query = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $_SESSION['user_id']);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }
}

// Global auth instance
$auth = new Auth();
?>