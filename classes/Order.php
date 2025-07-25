<?php
require_once '../config/database.php';

class Order {
    private $conn;
    private $table_name = "orders";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function create($data) {
        $orderNumber = $this->generateOrderNumber();
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (order_number, store_id, table_id, customer_name, customer_phone, customer_email,
                   order_type, subtotal, tax_amount, discount_amount, total_amount, notes, created_by) 
                  VALUES (:order_number, :store_id, :table_id, :customer_name, :customer_phone, :customer_email,
                          :order_type, :subtotal, :tax_amount, :discount_amount, :total_amount, :notes, :created_by)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':order_number', $orderNumber);
        $stmt->bindParam(':store_id', $data['store_id']);
        $stmt->bindParam(':table_id', $data['table_id']);
        $stmt->bindParam(':customer_name', $data['customer_name']);
        $stmt->bindParam(':customer_phone', $data['customer_phone']);
        $stmt->bindParam(':customer_email', $data['customer_email']);
        $stmt->bindParam(':order_type', $data['order_type']);
        $stmt->bindParam(':subtotal', $data['subtotal']);
        $stmt->bindParam(':tax_amount', $data['tax_amount']);
        $stmt->bindParam(':discount_amount', $data['discount_amount']);
        $stmt->bindParam(':total_amount', $data['total_amount']);
        $stmt->bindParam(':notes', $data['notes']);
        $stmt->bindParam(':created_by', $data['created_by']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function addOrderItem($orderId, $item) {
        $query = "INSERT INTO order_items 
                  (order_id, product_id, quantity, unit_price, total_price, special_instructions) 
                  VALUES (:order_id, :product_id, :quantity, :unit_price, :total_price, :special_instructions)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':product_id', $item['product_id']);
        $stmt->bindParam(':quantity', $item['quantity']);
        $stmt->bindParam(':unit_price', $item['unit_price']);
        $stmt->bindParam(':total_price', $item['total_price']);
        $stmt->bindParam(':special_instructions', $item['special_instructions']);
        
        return $stmt->execute();
    }
    
    public function getAll($filters = []) {
        $where = "WHERE 1=1";
        $params = [];
        
        if (!empty($filters['store_id'])) {
            $where .= " AND o.store_id = :store_id";
            $params[':store_id'] = $filters['store_id'];
        }
        
        if (!empty($filters['status'])) {
            $where .= " AND o.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $where .= " AND DATE(o.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where .= " AND DATE(o.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        $query = "SELECT o.*, s.name as store_name, rt.table_number,
                         CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                  FROM " . $this->table_name . " o
                  LEFT JOIN stores s ON o.store_id = s.id
                  LEFT JOIN restaurant_tables rt ON o.table_id = rt.id
                  LEFT JOIN users u ON o.created_by = u.id
                  " . $where . "
                  ORDER BY o.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT o.*, s.name as store_name, rt.table_number,
                         CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                  FROM " . $this->table_name . " o
                  LEFT JOIN stores s ON o.store_id = s.id
                  LEFT JOIN restaurant_tables rt ON o.table_id = rt.id
                  LEFT JOIN users u ON o.created_by = u.id
                  WHERE o.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getOrderItems($orderId) {
        $query = "SELECT oi.*, p.name as product_name, p.image as product_image
                  FROM order_items oi
                  LEFT JOIN products p ON oi.product_id = p.id
                  WHERE oi.order_id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status, updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        
        return $stmt->execute();
    }
    
    public function updatePaymentStatus($id, $paymentStatus, $paymentMethod = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET payment_status = :payment_status";
        
        if ($paymentMethod) {
            $query .= ", payment_method = :payment_method";
        }
        
        $query .= ", updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':payment_status', $paymentStatus);
        
        if ($paymentMethod) {
            $stmt->bindParam(':payment_method', $paymentMethod);
        }
        
        return $stmt->execute();
    }
    
    public function getEarningsReport($dateFrom = null, $dateTo = null) {
        $where = "WHERE o.payment_status = 'paid'";
        $params = [];
        
        if ($dateFrom) {
            $where .= " AND DATE(o.created_at) >= :date_from";
            $params[':date_from'] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND DATE(o.created_at) <= :date_to";
            $params[':date_to'] = $dateTo;
        }
        
        $query = "SELECT 
                    SUM(total_amount) as total_earnings,
                    COUNT(*) as total_orders,
                    AVG(total_amount) as average_order_value
                  FROM " . $this->table_name . " o
                  " . $where;
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getMonthlyReport($year = null, $month = null) {
        if (!$year) $year = date('Y');
        if (!$month) $month = date('m');
        
        $query = "SELECT 
                    DATE(created_at) as order_date,
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid_amount,
                    SUM(CASE WHEN payment_status != 'paid' THEN total_amount ELSE 0 END) as unpaid_amount,
                    SUM(total_amount) as total_amount
                  FROM " . $this->table_name . "
                  WHERE YEAR(created_at) = :year AND MONTH(created_at) = :month
                  GROUP BY DATE(created_at)
                  ORDER BY order_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function generateOrderNumber() {
        $prefix = "ORD";
        $query = "SELECT setting_value FROM settings WHERE setting_key = 'order_prefix'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $prefix = $result['setting_value'];
        }
        
        return $prefix . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}
?>