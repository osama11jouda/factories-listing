<?php

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        // Include the config file
        require_once __DIR__ . '/../includes/config.php';
        
        // Create connection using variables from config.php
        $this->connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if (mysqli_connect_errno()) {
            die("Database connection failed: " . mysqli_connect_error());
        }
        
        // Set charset to utf8
        mysqli_set_charset($this->connection, "utf8");
    }
    
    // Singleton pattern - Get database instance
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    // Execute query
    public function query($sql) {
        $result = mysqli_query($this->connection, $sql);
        
        if (!$result) {
            die("Query failed: " . mysqli_error($this->connection));
        }
        
        return $result;
    }
    
    // Fetch results as associative array
    public function fetchArray($result) {
        return mysqli_fetch_assoc($result);
    }
    
    // Get number of rows
    public function numRows($result) {
        return mysqli_num_rows($result);
    }
    
    // Get last insert ID
    public function insertId() {
        return mysqli_insert_id($this->connection);
    }
    
    // Escape string for security
    public function escapeString($string) {
        // Handle null values by converting them to empty strings
        if ($string === null) {
            return '';
        }
        return mysqli_real_escape_string($this->connection, $string);
    }
    
    // Close connection
    public function closeConnection() {
        mysqli_close($this->connection);
    }
    
    // Get connection
    public function getConnection() {
        return $this->connection;
    }
    
    // Begin transaction
    public function beginTransaction() {
        mysqli_begin_transaction($this->connection);
    }
    
    // Commit transaction
    public function commitTransaction() {
        mysqli_commit($this->connection);
    }
    
    // Rollback transaction
    public function rollbackTransaction() {
        mysqli_rollback($this->connection);
    }
    
    // Prevent cloning of the instance
    private function __clone() { }
    
    // Prevent deserialization of the instance
    private function __wakeup() { }
}
?>