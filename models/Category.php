<?php
require_once __DIR__ . '/Database.php';

class Category {
    private $id;
    private $name;
    private $description;
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
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    
    // Setters
    public function setName($name) { $this->name = $name; }
    public function setDescription($description) { $this->description = $description; }
    
    // Find by ID
    public function findById($id) {
        $id = $this->db->escapeString($id);
        $result = $this->db->query("SELECT * FROM categories WHERE id = '$id' LIMIT 1");
        
        if ($this->db->numRows($result) > 0) {
            $category = $this->db->fetchArray($result);
            $this->setProperties($category);
            return true;
        }
        
        return false;
    }
    
    // Check if category with the same name already exists
    public function existsByName($name) {
        $name = $this->db->escapeString($name);
        $result = $this->db->query("SELECT * FROM categories WHERE name = '$name' LIMIT 1");
        return $this->db->numRows($result) > 0;
    }
    
    // Create new category
    public function create() {
        if ($this->existsByName($this->name)) {
            return false; // Category with the same name already exists
        }
        
        $name = $this->db->escapeString($this->name);
        $description = $this->description ? "'" . $this->db->escapeString($this->description) . "'" : 'NULL';
        
        $sql = "INSERT INTO categories (name, description) 
                VALUES ('$name', $description)";
        
        if ($this->db->query($sql)) {
            $this->id = $this->db->insertId();
            return true;
        }
        
        return false;
    }
    
    // Update existing category
    public function update() {
        $name = $this->db->escapeString($this->name);
        $description = $this->description ? "'" . $this->db->escapeString($this->description) . "'" : 'NULL';
        
        $sql = "UPDATE categories 
                SET name = '$name', 
                    description = $description
                WHERE id = {$this->id}";
        
        return $this->db->query($sql);
    }
    
    // Delete category
    public function delete() {
        // First, delete relationships with factories
        $sql = "DELETE FROM factory_categories WHERE category_id = {$this->id}";
        $this->db->query($sql);
        
        // Then delete the category
        $sql = "DELETE FROM categories WHERE id = {$this->id}";
        return $this->db->query($sql);
    }
    
    // Get all categories
    public static function getAll() {
        $db = Database::getInstance();
        $result = $db->query("SELECT * FROM categories ORDER BY name");
        
        $categories = [];
        while ($row = $db->fetchArray($result)) {
            $category = new self();
            $category->setProperties($row);
            $categories[] = $category;
        }
        
        return $categories;
    }
    
    // Get categories by factory ID
    public static function getCategoriesByFactoryId($factory_id) {
        $db = Database::getInstance();
        $factory_id = intval($factory_id);
        $result = $db->query("
            SELECT c.* FROM categories c 
            JOIN factory_categories fc ON c.id = fc.category_id 
            WHERE fc.factory_id = $factory_id 
            ORDER BY c.name
        ");
        
        $categories = [];
        while ($row = $db->fetchArray($result)) {
            $category = new self();
            $category->setProperties($row);
            $categories[] = $category;
        }
        
        return $categories;
    }
    
    // Add category to factory
    public static function addToFactory($category_id, $factory_id) {
        $db = Database::getInstance();
        $category_id = intval($category_id);
        $factory_id = intval($factory_id);
        
        // Check if the relationship already exists
        $result = $db->query("SELECT * FROM factory_categories WHERE category_id = $category_id AND factory_id = $factory_id");
        if ($db->numRows($result) > 0) {
            return true; // Relationship already exists
        }
        
        return $db->query("INSERT INTO factory_categories (category_id, factory_id) VALUES ($category_id, $factory_id)");
    }
    
    // Remove category from factory
    public static function removeFromFactory($category_id, $factory_id) {
        $db = Database::getInstance();
        $category_id = intval($category_id);
        $factory_id = intval($factory_id);
        
        return $db->query("DELETE FROM factory_categories WHERE category_id = $category_id AND factory_id = $factory_id");
    }
    
    // Set categories for a factory (deletes existing links and adds new ones)
    public static function setFactoryCategories($factory_id, $category_ids) {
        $db = Database::getInstance();
        $factory_id = intval($factory_id);
        
        // Start transaction
        $db->beginTransaction();
        
        // Delete existing categories for this factory
        $db->query("DELETE FROM factory_categories WHERE factory_id = $factory_id");
        
        // Add new categories
        if (!empty($category_ids)) {
            foreach ($category_ids as $category_id) {
                $category_id = intval($category_id);
                $db->query("INSERT INTO factory_categories (category_id, factory_id) VALUES ($category_id, $factory_id)");
            }
        }
        
        // Commit transaction
        return $db->commitTransaction();
    }
    
    // Set object properties from array
    private function setProperties($data) {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }
}
?>