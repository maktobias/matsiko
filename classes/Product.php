<?php
require_once '../config/database.php';

class Product {
    private $conn;
    private $table_name = "products";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, description, category_id, price, cost_price, image, ingredients, 
                   allergens, preparation_time, status, is_featured) 
                  VALUES (:name, :description, :category_id, :price, :cost_price, :image, 
                          :ingredients, :allergens, :preparation_time, :status, :is_featured)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':cost_price', $data['cost_price']);
        $stmt->bindParam(':image', $data['image']);
        $stmt->bindParam(':ingredients', $data['ingredients']);
        $stmt->bindParam(':allergens', $data['allergens']);
        $stmt->bindParam(':preparation_time', $data['preparation_time']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':is_featured', $data['is_featured']);
        
        return $stmt->execute();
    }
    
    public function getAll($categoryId = null, $status = null) {
        $where = "WHERE 1=1";
        $params = [];
        
        if ($categoryId) {
            $where .= " AND p.category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }
        
        if ($status) {
            $where .= " AND p.status = :status";
            $params[':status'] = $status;
        }
        
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  " . $where . "
                  ORDER BY p.name";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, description = :description, category_id = :category_id,
                      price = :price, cost_price = :cost_price, image = :image,
                      ingredients = :ingredients, allergens = :allergens,
                      preparation_time = :preparation_time, status = :status,
                      is_featured = :is_featured, updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':cost_price', $data['cost_price']);
        $stmt->bindParam(':image', $data['image']);
        $stmt->bindParam(':ingredients', $data['ingredients']);
        $stmt->bindParam(':allergens', $data['allergens']);
        $stmt->bindParam(':preparation_time', $data['preparation_time']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':is_featured', $data['is_featured']);
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    public function getFeatured() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.is_featured = 1 AND p.status = 'available'
                  ORDER BY p.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Category methods
    public function createCategory($data) {
        $query = "INSERT INTO categories (name, description, image, parent_id, status, sort_order) 
                  VALUES (:name, :description, :image, :parent_id, :status, :sort_order)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':image', $data['image']);
        $stmt->bindParam(':parent_id', $data['parent_id']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':sort_order', $data['sort_order']);
        
        return $stmt->execute();
    }
    
    public function getAllCategories($parentId = null) {
        $where = "WHERE 1=1";
        $params = [];
        
        if ($parentId !== null) {
            if ($parentId == 0) {
                $where .= " AND parent_id IS NULL";
            } else {
                $where .= " AND parent_id = :parent_id";
                $params[':parent_id'] = $parentId;
            }
        }
        
        $query = "SELECT c.*, 
                         (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
                  FROM categories c
                  " . $where . "
                  ORDER BY sort_order, name";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCategoryById($id) {
        $query = "SELECT * FROM categories WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateCategory($id, $data) {
        $query = "UPDATE categories 
                  SET name = :name, description = :description, image = :image,
                      parent_id = :parent_id, status = :status, sort_order = :sort_order
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':image', $data['image']);
        $stmt->bindParam(':parent_id', $data['parent_id']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':sort_order', $data['sort_order']);
        
        return $stmt->execute();
    }
    
    public function deleteCategory($id) {
        // Check if category has products
        $query = "SELECT COUNT(*) as count FROM products WHERE category_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            return false; // Cannot delete category with products
        }
        
        $query = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>