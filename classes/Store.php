<?php
require_once '../config/database.php';

class Store {
    private $conn;
    private $table_name = "stores";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, address, phone, manager_id, status) 
                  VALUES (:name, :address, :phone, :manager_id, :status)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':manager_id', $data['manager_id']);
        $stmt->bindParam(':status', $data['status']);
        
        return $stmt->execute();
    }
    
    public function getAll() {
        $query = "SELECT s.*, CONCAT(u.first_name, ' ', u.last_name) as manager_name
                  FROM " . $this->table_name . " s
                  LEFT JOIN users u ON s.manager_id = u.id
                  ORDER BY s.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT s.*, CONCAT(u.first_name, ' ', u.last_name) as manager_name
                  FROM " . $this->table_name . " s
                  LEFT JOIN users u ON s.manager_id = u.id
                  WHERE s.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, address = :address, phone = :phone, 
                      manager_id = :manager_id, status = :status
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':manager_id', $data['manager_id']);
        $stmt->bindParam(':status', $data['status']);
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    // Restaurant Table methods
    public function createTable($data) {
        $query = "INSERT INTO restaurant_tables 
                  (store_id, table_number, capacity, status, qr_code) 
                  VALUES (:store_id, :table_number, :capacity, :status, :qr_code)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':store_id', $data['store_id']);
        $stmt->bindParam(':table_number', $data['table_number']);
        $stmt->bindParam(':capacity', $data['capacity']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':qr_code', $data['qr_code']);
        
        return $stmt->execute();
    }
    
    public function getAllTables($storeId = null) {
        $where = "WHERE 1=1";
        $params = [];
        
        if ($storeId) {
            $where .= " AND rt.store_id = :store_id";
            $params[':store_id'] = $storeId;
        }
        
        $query = "SELECT rt.*, s.name as store_name
                  FROM restaurant_tables rt
                  LEFT JOIN stores s ON rt.store_id = s.id
                  " . $where . "
                  ORDER BY s.name, rt.table_number";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTableById($id) {
        $query = "SELECT rt.*, s.name as store_name
                  FROM restaurant_tables rt
                  LEFT JOIN stores s ON rt.store_id = s.id
                  WHERE rt.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateTable($id, $data) {
        $query = "UPDATE restaurant_tables 
                  SET store_id = :store_id, table_number = :table_number, 
                      capacity = :capacity, status = :status, qr_code = :qr_code
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':store_id', $data['store_id']);
        $stmt->bindParam(':table_number', $data['table_number']);
        $stmt->bindParam(':capacity', $data['capacity']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':qr_code', $data['qr_code']);
        
        return $stmt->execute();
    }
    
    public function updateTableStatus($id, $status) {
        $query = "UPDATE restaurant_tables SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }
    
    public function deleteTable($id) {
        $query = "DELETE FROM restaurant_tables WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    public function getAvailableTables($storeId) {
        $query = "SELECT * FROM restaurant_tables 
                  WHERE store_id = :store_id AND status = 'available'
                  ORDER BY table_number";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':store_id', $storeId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTableStatusSummary($storeId = null) {
        $where = "";
        $params = [];
        
        if ($storeId) {
            $where = "WHERE store_id = :store_id";
            $params[':store_id'] = $storeId;
        }
        
        $query = "SELECT 
                    status,
                    COUNT(*) as count
                  FROM restaurant_tables
                  " . $where . "
                  GROUP BY status";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function generateQRCode($tableId) {
        // Simple QR code URL - in production, you'd use a proper QR code library
        $baseUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
        $qrUrl = $baseUrl . "/menu.php?table=" . $tableId;
        
        $query = "UPDATE restaurant_tables SET qr_code = :qr_code WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $tableId);
        $stmt->bindParam(':qr_code', $qrUrl);
        
        return $stmt->execute() ? $qrUrl : false;
    }
}
?>