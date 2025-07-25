<?php
require_once '../config/database.php';

class User {
    private $conn;
    private $table_name = "users";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function login($username, $password) {
        $query = "SELECT u.*, ug.name as group_name, ug.permissions 
                  FROM " . $this->table_name . " u
                  LEFT JOIN user_groups ug ON u.user_group_id = ug.id
                  WHERE u.username = :username AND u.status = 'active'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, email, password, first_name, last_name, phone, user_group_id, status) 
                  VALUES (:username, :email, :password, :first_name, :last_name, :phone, :user_group_id, :status)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':user_group_id', $data['user_group_id']);
        $stmt->bindParam(':status', $data['status']);
        
        return $stmt->execute();
    }
    
    public function getAll() {
        $query = "SELECT u.*, ug.name as group_name 
                  FROM " . $this->table_name . " u
                  LEFT JOIN user_groups ug ON u.user_group_id = ug.id
                  ORDER BY u.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT u.*, ug.name as group_name 
                  FROM " . $this->table_name . " u
                  LEFT JOIN user_groups ug ON u.user_group_id = ug.id
                  WHERE u.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET username = :username, email = :email, first_name = :first_name, 
                      last_name = :last_name, phone = :phone, user_group_id = :user_group_id, 
                      status = :status, updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':user_group_id', $data['user_group_id']);
        $stmt->bindParam(':status', $data['status']);
        
        return $stmt->execute();
    }
    
    public function updatePassword($id, $newPassword) {
        $query = "UPDATE " . $this->table_name . " 
                  SET password = :password, updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':password', password_hash($newPassword, PASSWORD_DEFAULT));
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    public function getUserGroups() {
        $query = "SELECT * FROM user_groups ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>