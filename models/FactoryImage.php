<?php
require_once __DIR__ . '/Database.php';

class FactoryImage {
    private $id;
    private $factory_id;
    private $image_path;
    private $is_main;
    private $upload_date;
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getFactoryId() { return $this->factory_id; }
    public function getImagePath() { return $this->image_path; }
    public function isMain() { return $this->is_main; }
    public function getUploadDate() { return $this->upload_date; }
    
    // Setters
    public function setFactoryId($factory_id) { $this->factory_id = $factory_id; }
    public function setImagePath($image_path) { $this->image_path = $image_path; }
    public function setIsMain($is_main) { $this->is_main = $is_main; }
    
    // Find by ID
    public function findById($id) {
        $id = $this->db->escapeString($id);
        $result = $this->db->query("SELECT * FROM factory_images WHERE id = '$id' LIMIT 1");
        
        if ($this->db->numRows($result) > 0) {
            $image = $this->db->fetchArray($result);
            $this->setProperties($image);
            return true;
        }
        
        return false;
    }
    
    // Create new image
    public function create() {
        $factory_id = intval($this->factory_id);
        $image_path = $this->db->escapeString($this->image_path);
        $is_main = $this->is_main ? 1 : 0;
        
        // If this is the main image, unset any existing main images for this factory
        if ($is_main) {
            $this->db->query("UPDATE factory_images SET is_main = 0 WHERE factory_id = $factory_id");
        }
        
        $sql = "INSERT INTO factory_images (factory_id, image_path, is_main) 
                VALUES ($factory_id, '$image_path', $is_main)";
        
        if ($this->db->query($sql)) {
            $this->id = $this->db->insertId();
            return true;
        }
        
        return false;
    }
    
    // Update existing image
    public function update() {
        $factory_id = intval($this->factory_id);
        $image_path = $this->db->escapeString($this->image_path);
        $is_main = $this->is_main ? 1 : 0;
        
        // If this is the main image, unset any existing main images for this factory
        if ($is_main) {
            $this->db->query("UPDATE factory_images SET is_main = 0 WHERE factory_id = $factory_id AND id != {$this->id}");
        }
        
        $sql = "UPDATE factory_images 
                SET factory_id = $factory_id, 
                    image_path = '$image_path', 
                    is_main = $is_main 
                WHERE id = {$this->id}";
        
        return $this->db->query($sql);
    }
    
    // Delete image
    public function delete() {
        // Get the image path to delete the actual file
        $image_path = $this->image_path;
        
        $sql = "DELETE FROM factory_images WHERE id = {$this->id}";
        if ($this->db->query($sql)) {
            // Delete the actual file if it exists
            if (!empty($image_path) && file_exists($_SERVER['DOCUMENT_ROOT'] . $image_path)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $image_path);
            }
            return true;
        }
        
        return false;
    }
    
    // Get images by factory ID
    public static function getByFactoryId($factory_id) {
        $db = Database::getInstance();
        $factory_id = intval($factory_id);
        $result = $db->query("SELECT * FROM factory_images WHERE factory_id = $factory_id ORDER BY is_main DESC, upload_date DESC");
        
        $images = [];
        while ($row = $db->fetchArray($result)) {
            $image = new self();
            $image->setProperties($row);
            $images[] = $image;
        }
        
        return $images;
    }
    
    // Get main image by factory ID
    public static function getMainImageByFactoryId($factory_id) {
        $db = Database::getInstance();
        $factory_id = intval($factory_id);
        $result = $db->query("SELECT * FROM factory_images WHERE factory_id = $factory_id AND is_main = 1 LIMIT 1");
        
        if ($db->numRows($result) > 0) {
            $row = $db->fetchArray($result);
            $image = new self();
            $image->setProperties($row);
            return $image;
        }
        
        // If no main image found, try to get the first image
        $result = $db->query("SELECT * FROM factory_images WHERE factory_id = $factory_id LIMIT 1");
        
        if ($db->numRows($result) > 0) {
            $row = $db->fetchArray($result);
            $image = new self();
            $image->setProperties($row);
            return $image;
        }
        
        return null;
    }
    
    // Set object properties from array
    private function setProperties($data) {
        $this->id = $data['id'] ?? null;
        $this->factory_id = $data['factory_id'] ?? null;
        $this->image_path = $data['image_path'] ?? null;
        $this->is_main = isset($data['is_main']) ? (bool)$data['is_main'] : false;
        $this->upload_date = $data['upload_date'] ?? null;
    }
}
?>