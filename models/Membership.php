<?php
require_once __DIR__ . '/Database.php';

class Membership {
    private $id;
    private $name;
    private $description;
    private $price;
    private $duration_months;
    private $features;
    private $created_at;
    private $updated_at;
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }
    public function getPrice() { return $this->price; }
    public function getDurationMonths() { return $this->duration_months; }
    public function getFeatures() { return $this->features; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    
    // Setters
    public function setName($name) { $this->name = $name; }
    public function setDescription($description) { $this->description = $description; }
    public function setPrice($price) { $this->price = $price; }
    public function setDurationMonths($duration_months) { $this->duration_months = $duration_months; }
    public function setFeatures($features) { $this->features = $features; }
    
    // Find by ID
    public function findById($id) {
        $id = $this->db->escapeString($id);
        $result = $this->db->query("SELECT * FROM memberships WHERE id = '$id' LIMIT 1");
        
        if ($this->db->numRows($result) > 0) {
            $membership = $this->db->fetchArray($result);
            $this->setProperties($membership);
            return true;
        }
        
        return false;
    }
    
    // Create new membership
    public function create() {
        $name = $this->db->escapeString($this->name);
        $description = $this->db->escapeString($this->description);
        $price = floatval($this->price);
        $duration_months = intval($this->duration_months);
        $features = $this->db->escapeString($this->features);
        
        $sql = "INSERT INTO memberships (name, description, price, duration_months, features) 
                VALUES ('$name', '$description', $price, $duration_months, '$features')";
        
        if ($this->db->query($sql)) {
            $this->id = $this->db->insertId();
            return true;
        }
        
        return false;
    }
    
    // Update existing membership
    public function update() {
        $name = $this->db->escapeString($this->name);
        $description = $this->db->escapeString($this->description);
        $price = floatval($this->price);
        $duration_months = intval($this->duration_months);
        $features = $this->db->escapeString($this->features);
        
        $sql = "UPDATE memberships 
                SET name = '$name', 
                    description = '$description', 
                    price = $price, 
                    duration_months = $duration_months, 
                    features = '$features' 
                WHERE id = {$this->id}";
        
        return $this->db->query($sql);
    }
    
    // Delete membership
    public function delete() {
        $sql = "DELETE FROM memberships WHERE id = {$this->id}";
        return $this->db->query($sql);
    }
    
    // Get all memberships
    public static function getAll() {
        $db = Database::getInstance();
        $result = $db->query("SELECT * FROM memberships ORDER BY price ASC");
        
        $memberships = [];
        while ($row = $db->fetchArray($result)) {
            $membership = new self();
            $membership->setProperties($row);
            $memberships[] = $membership;
        }
        
        return $memberships;
    }
    
    // Get features as array
    public function getFeaturesArray() {
        if (!empty($this->features)) {
            return explode(',', $this->features);
        }
        return [];
    }
    
    // Set object properties from array
    private function setProperties($data) {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->price = $data['price'] ?? null;
        $this->duration_months = $data['duration_months'] ?? null;
        $this->features = $data['features'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }
}
?>