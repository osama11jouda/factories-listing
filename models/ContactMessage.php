<?php
require_once __DIR__ . '/Database.php';

class ContactMessage {
    private $id;
    private $name;
    private $email;
    private $subject;
    private $message;
    private $submission_date;
    private $is_read;
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getSubject() { return $this->subject; }
    public function getMessage() { return $this->message; }
    public function getSubmissionDate() { return $this->submission_date; }
    public function isRead() { return $this->is_read; }
    
    // Setters
    public function setName($name) { $this->name = $name; }
    public function setEmail($email) { $this->email = $email; }
    public function setSubject($subject) { $this->subject = $subject; }
    public function setMessage($message) { $this->message = $message; }
    public function setIsRead($is_read) { $this->is_read = $is_read; }
    
    // Find by ID
    public function findById($id) {
        $id = $this->db->escapeString($id);
        $result = $this->db->query("SELECT * FROM contact_messages WHERE id = '$id' LIMIT 1");
        
        if ($this->db->numRows($result) > 0) {
            $message = $this->db->fetchArray($result);
            $this->setProperties($message);
            return true;
        }
        
        return false;
    }
    
    // Create new message
    public function create() {
        $name = $this->db->escapeString($this->name);
        $email = $this->db->escapeString($this->email);
        $subject = $this->db->escapeString($this->subject);
        $message = $this->db->escapeString($this->message);
        
        $sql = "INSERT INTO contact_messages (name, email, subject, message) 
                VALUES ('$name', '$email', '$subject', '$message')";
        
        if ($this->db->query($sql)) {
            $this->id = $this->db->insertId();
            return true;
        }
        
        return false;
    }
    
    // Mark message as read
    public function markAsRead() {
        $this->is_read = true;
        $sql = "UPDATE contact_messages SET is_read = 1 WHERE id = {$this->id}";
        return $this->db->query($sql);
    }
    
    // Delete message
    public function delete() {
        $sql = "DELETE FROM contact_messages WHERE id = {$this->id}";
        return $this->db->query($sql);
    }
    
    // Get all messages
    public static function getAll($limit = null, $offset = 0, $onlyUnread = false) {
        $db = Database::getInstance();
        
        $whereClause = $onlyUnread ? "WHERE is_read = 0" : "";
        $limitClause = $limit ? "LIMIT $offset, $limit" : "";
        
        $result = $db->query("SELECT * FROM contact_messages $whereClause ORDER BY submission_date DESC $limitClause");
        
        $messages = [];
        while ($row = $db->fetchArray($result)) {
            $message = new self();
            $message->setProperties($row);
            $messages[] = $message;
        }
        
        return $messages;
    }
    
    // Count all messages
    public static function countAll($onlyUnread = false) {
        $db = Database::getInstance();
        
        $whereClause = $onlyUnread ? "WHERE is_read = 0" : "";
        
        $result = $db->query("SELECT COUNT(*) as total FROM contact_messages $whereClause");
        $row = $db->fetchArray($result);
        return $row['total'];
    }
    
    // Set object properties from array
    private function setProperties($data) {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->subject = $data['subject'] ?? null;
        $this->message = $data['message'] ?? null;
        $this->submission_date = $data['submission_date'] ?? null;
        $this->is_read = isset($data['is_read']) ? (bool)$data['is_read'] : false;
    }
}
?>