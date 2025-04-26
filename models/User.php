<?php
require_once __DIR__ . '/Database.php';

class User {
    private $id;
    private $name;
    private $email;
    private $password;
    private $phone;
    private $company;
    private $registration_date;
    private $is_active;
    private $subscription_expiry;
    private $is_admin;
    private $membership_id;
    private $updated_at;
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Getters and setters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getPhone() { return $this->phone; }
    public function getCompany() { return $this->company; }
    public function getRegistrationDate() { return $this->registration_date; }
    public function isActive() { return $this->is_active; }
    public function getSubscriptionExpiry() { return $this->subscription_expiry; }
    public function isAdmin() { return $this->is_admin; }
    public function getMembershipId() { return $this->membership_id; }
    public function getUpdatedAt() { return $this->updated_at; }
    
    public function setName($name) { $this->name = $name; }
    public function setEmail($email) { $this->email = $email; }
    public function setPassword($password) { $this->password = $password; }
    public function setPhone($phone) { $this->phone = $phone; }
    public function setCompany($company) { $this->company = $company; }
    public function setIsActive($is_active) { $this->is_active = $is_active; }
    public function setSubscriptionExpiry($subscription_expiry) { $this->subscription_expiry = $subscription_expiry; }
    public function setIsAdmin($is_admin) { $this->is_admin = $is_admin; }
    public function setMembershipId($membership_id) { $this->membership_id = $membership_id; }
    
    // Find user by ID
    public function findById($id) {
        $id = $this->db->escapeString($id);
        $result = $this->db->query("SELECT * FROM users WHERE id = '$id' LIMIT 1");
        
        if ($this->db->numRows($result) > 0) {
            $user = $this->db->fetchArray($result);
            $this->setProperties($user);
            return true;
        }
        
        return false;
    }
    
    // Find user by email
    public function findByEmail($email) {
        $email = $this->db->escapeString($email);
        $result = $this->db->query("SELECT * FROM users WHERE email = '$email' LIMIT 1");
        
        if ($this->db->numRows($result) > 0) {
            $user = $this->db->fetchArray($result);
            $this->setProperties($user);
            return true;
        }
        
        return false;
    }
    
    // Create new user
    public function create() {
        $name = $this->db->escapeString($this->name);
        $email = $this->db->escapeString($this->email);
        $password = $this->db->escapeString($this->password);
        $phone = $this->db->escapeString($this->phone);
        $company = $this->db->escapeString($this->company);
        $is_active = $this->is_active ? 1 : 0;
        $is_admin = $this->is_admin ? 1 : 0;
        $membership_id = $this->membership_id ? $this->membership_id : 'NULL';
        
        $sql = "INSERT INTO users (name, email, password, phone, company, is_active, is_admin, membership_id) 
                VALUES ('$name', '$email', '$password', '$phone', '$company', $is_active, $is_admin, $membership_id)";
        
        if ($this->db->query($sql)) {
            $this->id = $this->db->insertId();
            return true;
        }
        
        return false;
    }
    
    // Update existing user
    public function update() {
        $name = $this->db->escapeString($this->name);
        $email = $this->db->escapeString($this->email);
        $phone = $this->db->escapeString($this->phone);
        $company = $this->db->escapeString($this->company);
        $is_active = $this->is_active ? 1 : 0;
        $subscription_expiry = $this->subscription_expiry ? "'{$this->db->escapeString($this->subscription_expiry)}'" : 'NULL';
        $membership_id = $this->membership_id ? $this->membership_id : 'NULL';
        
        $sql = "UPDATE users 
                SET name = '$name', 
                    email = '$email', 
                    phone = '$phone', 
                    company = '$company', 
                    is_active = $is_active, 
                    subscription_expiry = $subscription_expiry, 
                    membership_id = $membership_id 
                WHERE id = {$this->id}";
        
        return $this->db->query($sql);
    }
    
    // Update password
    public function updatePassword() {
        $password = $this->db->escapeString($this->password);
        $sql = "UPDATE users SET password = '$password' WHERE id = {$this->id}";
        return $this->db->query($sql);
    }
    
    // Delete user
    public function delete() {
        $sql = "DELETE FROM users WHERE id = {$this->id}";
        return $this->db->query($sql);
    }
    
    // Get all users
    public static function getAll($limit = null, $offset = 0) {
        $db = Database::getInstance();
        $limitClause = $limit ? "LIMIT $offset, $limit" : "";
        $result = $db->query("SELECT * FROM users ORDER BY registration_date DESC $limitClause");
        
        $users = [];
        while ($row = $db->fetchArray($result)) {
            $user = new self();
            $user->setProperties($row);
            $users[] = $user;
        }
        
        return $users;
    }
    
    // Count all users
    public static function countAll() {
        $db = Database::getInstance();
        $result = $db->query("SELECT COUNT(*) as total FROM users");
        $row = $db->fetchArray($result);
        return $row['total'];
    }
    
    // Verify password
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }
    
    // Hash password
    public function hashPassword() {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
    }
    
    // Check if user has an active membership
    public function hasActiveMembership() {
        return $this->is_active && (!$this->subscription_expiry || strtotime($this->subscription_expiry) >= time());
    }
    
    // Set object properties from array
    private function setProperties($data) {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->phone = $data['phone'] ?? null;
        $this->company = $data['company'] ?? null;
        $this->registration_date = $data['registration_date'] ?? null;
        $this->is_active = isset($data['is_active']) ? (bool)$data['is_active'] : false;
        $this->subscription_expiry = $data['subscription_expiry'] ?? null;
        $this->is_admin = isset($data['is_admin']) ? (bool)$data['is_admin'] : false;
        $this->membership_id = $data['membership_id'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }
}
?>