<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Address.php';
require_once __DIR__ . '/User.php';

class Factory {
    private $id;
    private $title;
    private $description;
    private $address_id;
    private $price;
    private $area;
    private $type;
    private $status;
    private $featured;
    private $date_added;
    private $updated_at;
    private $contact_info;
    private $user_id;
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getTitle() { return $this->title; }
    public function getDescription() { return $this->description; }
    public function getAddressId() { return $this->address_id; }
    public function getPrice() { return $this->price; }
    public function getArea() { return $this->area; }
    public function getType() { return $this->type; }
    public function getStatus() { return $this->status; }
    public function isFeatured() { return $this->featured; }
    public function getDateAdded() { return $this->date_added; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getContactInfo() { return $this->contact_info; }
    public function getUserId() { return $this->user_id; }
    
    // Setters
    public function setTitle($title) { $this->title = $title; }
    public function setDescription($description) { $this->description = $description; }
    public function setAddressId($address_id) { $this->address_id = $address_id; }
    public function setPrice($price) { $this->price = $price; }
    public function setArea($area) { $this->area = $area; }
    public function setType($type) { $this->type = $type; }
    public function setStatus($status) { $this->status = $status; }
    public function setFeatured($featured) { $this->featured = $featured; }
    public function setContactInfo($contact_info) { $this->contact_info = $contact_info; }
    public function setUserId($user_id) { $this->user_id = $user_id; }
    
    // Find by ID
    public function findById($id) {
        $id = $this->db->escapeString($id);
        $result = $this->db->query("SELECT * FROM factories WHERE id = '$id' LIMIT 1");
        
        if ($this->db->numRows($result) > 0) {
            $factory = $this->db->fetchArray($result);
            $this->setProperties($factory);
            return true;
        }
        
        return false;
    }
    
    // Create new factory
    public function create() {
        $title = $this->db->escapeString($this->title);
        $description = $this->db->escapeString($this->description);
        $address_id = $this->address_id ? intval($this->address_id) : 'NULL';
        $price = $this->price ? floatval($this->price) : 'NULL';
        $area = $this->area ? floatval($this->area) : 'NULL';
        $type = $this->db->escapeString($this->type);
        $status = $this->status ? "'{$this->db->escapeString($this->status)}'" : "'available'";
        $featured = $this->featured ? 1 : 0;
        $contact_info = $this->db->escapeString($this->contact_info);
        $user_id = $this->user_id ? intval($this->user_id) : 'NULL';
        
        $sql = "INSERT INTO factories (title, description, address_id, price, area, type, status, featured, contact_info, user_id) 
                VALUES ('$title', '$description', $address_id, $price, $area, '$type', $status, $featured, '$contact_info', $user_id)";
        
        if ($this->db->query($sql)) {
            $this->id = $this->db->insertId();
            return true;
        }
        
        return false;
    }
    
    // Update existing factory
    public function update() {
        $title = $this->db->escapeString($this->title);
        $description = $this->db->escapeString($this->description);
        $address_id = $this->address_id ? intval($this->address_id) : 'NULL';
        $price = $this->price ? floatval($this->price) : 'NULL';
        $area = $this->area ? floatval($this->area) : 'NULL';
        $type = $this->db->escapeString($this->type);
        $status = $this->status ? "'{$this->db->escapeString($this->status)}'" : "'available'";
        $featured = $this->featured ? 1 : 0;
        $contact_info = $this->db->escapeString($this->contact_info);
        $user_id = $this->user_id ? intval($this->user_id) : 'NULL';
        
        $sql = "UPDATE factories 
                SET title = '$title', 
                    description = '$description', 
                    address_id = $address_id, 
                    price = $price, 
                    area = $area, 
                    type = '$type', 
                    status = $status, 
                    featured = $featured, 
                    contact_info = '$contact_info', 
                    user_id = $user_id 
                WHERE id = {$this->id}";
        
        return $this->db->query($sql);
    }
    
    // Delete factory
    public function delete() {
        $sql = "DELETE FROM factories WHERE id = {$this->id}";
        return $this->db->query($sql);
    }
    
    // Get all factories
    public static function getAll($limit = null, $offset = 0, $filters = []) {
        $db = Database::getInstance();
        
        $whereClauses = [];
        
        if (!empty($filters['type'])) {
            $type = $db->escapeString($filters['type']);
            $whereClauses[] = "type = '$type'";
        }
        
        if (!empty($filters['status'])) {
            $status = $db->escapeString($filters['status']);
            $whereClauses[] = "status = '$status'";
        }
        
        if (!empty($filters['featured'])) {
            $whereClauses[] = "featured = 1";
        }
        
        if (!empty($filters['min_price'])) {
            $min_price = floatval($filters['min_price']);
            $whereClauses[] = "price >= $min_price";
        }
        
        if (!empty($filters['max_price'])) {
            $max_price = floatval($filters['max_price']);
            $whereClauses[] = "price <= $max_price";
        }
        
        if (!empty($filters['min_area'])) {
            $min_area = floatval($filters['min_area']);
            $whereClauses[] = "area >= $min_area";
        }
        
        if (!empty($filters['max_area'])) {
            $max_area = floatval($filters['max_area']);
            $whereClauses[] = "area <= $max_area";
        }
        
        if (!empty($filters['user_id'])) {
            $user_id = intval($filters['user_id']);
            $whereClauses[] = "user_id = $user_id";
        }
        
        $whereClause = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";
        $limitClause = $limit ? "LIMIT $offset, $limit" : "";
        
        $result = $db->query("SELECT * FROM factories $whereClause ORDER BY date_added DESC $limitClause");
        
        $factories = [];
        while ($row = $db->fetchArray($result)) {
            $factory = new self();
            $factory->setProperties($row);
            $factories[] = $factory;
        }
        
        return $factories;
    }
    
    // Count factories with optional filters
    public static function countAll($filters = []) {
        $db = Database::getInstance();
        
        $whereClauses = [];
        
        if (!empty($filters['type'])) {
            $type = $db->escapeString($filters['type']);
            $whereClauses[] = "type = '$type'";
        }
        
        if (!empty($filters['status'])) {
            $status = $db->escapeString($filters['status']);
            $whereClauses[] = "status = '$status'";
        }
        
        if (!empty($filters['featured'])) {
            $whereClauses[] = "featured = 1";
        }
        
        if (!empty($filters['min_price'])) {
            $min_price = floatval($filters['min_price']);
            $whereClauses[] = "price >= $min_price";
        }
        
        if (!empty($filters['max_price'])) {
            $max_price = floatval($filters['max_price']);
            $whereClauses[] = "price <= $max_price";
        }
        
        if (!empty($filters['min_area'])) {
            $min_area = floatval($filters['min_area']);
            $whereClauses[] = "area >= $min_area";
        }
        
        if (!empty($filters['max_area'])) {
            $max_area = floatval($filters['max_area']);
            $whereClauses[] = "area <= $max_area";
        }
        
        if (!empty($filters['user_id'])) {
            $user_id = intval($filters['user_id']);
            $whereClauses[] = "user_id = $user_id";
        }
        
        $whereClause = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";
        
        $result = $db->query("SELECT COUNT(*) as total FROM factories $whereClause");
        $row = $db->fetchArray($result);
        return $row['total'];
    }
    
    // Get factory address
    public function getAddress() {
        if ($this->address_id) {
            $address = new Address();
            if ($address->findById($this->address_id)) {
                return $address;
            }
        }
        return null;
    }
    
    // Get factory owner
    public function getOwner() {
        if ($this->user_id) {
            $user = new User();
            if ($user->findById($this->user_id)) {
                return $user;
            }
        }
        return null;
    }
    
    // Get factory images
    public function getImages() {
        require_once __DIR__ . '/FactoryImage.php';
        return FactoryImage::getByFactoryId($this->id);
    }
    
    // Get main image
    public function getMainImage() {
        require_once __DIR__ . '/FactoryImage.php';
        return FactoryImage::getMainImageByFactoryId($this->id);
    }
    
    // Set object properties from array
    private function setProperties($data) {
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->address_id = $data['address_id'] ?? null;
        $this->price = $data['price'] ?? null;
        $this->area = $data['area'] ?? null;
        $this->type = $data['type'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->featured = isset($data['featured']) ? (bool)$data['featured'] : false;
        $this->date_added = $data['date_added'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        $this->contact_info = $data['contact_info'] ?? null;
        $this->user_id = $data['user_id'] ?? null;
    }
}
?>