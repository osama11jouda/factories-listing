<?php
require_once __DIR__ . '/Database.php';

class Address {
    private $id;
    private $street_address;
    private $city;
    private $state_province;
    private $postal_code;
    private $country;
    private $latitude;
    private $longitude;
    private $created_at;
    private $updated_at;
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getStreetAddress() { return $this->street_address; }
    public function getCity() { return $this->city; }
    public function getStateProvince() { return $this->state_province; }
    public function getPostalCode() { return $this->postal_code; }
    public function getCountry() { return $this->country; }
    public function getLatitude() { return $this->latitude; }
    public function getLongitude() { return $this->longitude; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    
    // Setters
    public function setStreetAddress($street_address) { $this->street_address = $street_address; }
    public function setCity($city) { $this->city = $city; }
    public function setStateProvince($state_province) { $this->state_province = $state_province; }
    public function setPostalCode($postal_code) { $this->postal_code = $postal_code; }
    public function setCountry($country) { $this->country = $country; }
    public function setLatitude($latitude) { $this->latitude = $latitude; }
    public function setLongitude($longitude) { $this->longitude = $longitude; }
    
    // Find by ID
    public function findById($id) {
        $id = $this->db->escapeString($id);
        $result = $this->db->query("SELECT * FROM addresses WHERE id = '$id' LIMIT 1");
        
        if ($this->db->numRows($result) > 0) {
            $address = $this->db->fetchArray($result);
            $this->setProperties($address);
            return true;
        }
        
        return false;
    }
    
    // Create new address
    public function create() {
        $street_address = $this->db->escapeString($this->street_address);
        $city = $this->db->escapeString($this->city);
        $state_province = $this->db->escapeString($this->state_province);
        $postal_code = $this->db->escapeString($this->postal_code);
        $country = $this->db->escapeString($this->country);
        $latitude = $this->latitude ? floatval($this->latitude) : 'NULL';
        $longitude = $this->longitude ? floatval($this->longitude) : 'NULL';
        
        $sql = "INSERT INTO addresses (street_address, city, state_province, postal_code, country, latitude, longitude) 
                VALUES ('$street_address', '$city', '$state_province', '$postal_code', '$country', $latitude, $longitude)";
        
        if ($this->db->query($sql)) {
            $this->id = $this->db->insertId();
            return true;
        }
        
        return false;
    }
    
    // Update existing address
    public function update() {
        $street_address = $this->db->escapeString($this->street_address);
        $city = $this->db->escapeString($this->city);
        $state_province = $this->db->escapeString($this->state_province);
        $postal_code = $this->db->escapeString($this->postal_code);
        $country = $this->db->escapeString($this->country);
        $latitude = $this->latitude ? floatval($this->latitude) : 'NULL';
        $longitude = $this->longitude ? floatval($this->longitude) : 'NULL';
        
        $sql = "UPDATE addresses 
                SET street_address = '$street_address', 
                    city = '$city', 
                    state_province = '$state_province', 
                    postal_code = '$postal_code', 
                    country = '$country', 
                    latitude = $latitude, 
                    longitude = $longitude 
                WHERE id = {$this->id}";
        
        return $this->db->query($sql);
    }
    
    // Delete address
    public function delete() {
        $sql = "DELETE FROM addresses WHERE id = {$this->id}";
        return $this->db->query($sql);
    }
    
    // Get formatted address
    public function getFormattedAddress() {
        $parts = [];
        
        if ($this->street_address) {
            $parts[] = $this->street_address;
        }
        
        if ($this->city) {
            $parts[] = $this->city;
        }
        
        if ($this->state_province) {
            $parts[] = $this->state_province;
        }
        
        if ($this->postal_code) {
            $parts[] = $this->postal_code;
        }
        
        if ($this->country) {
            $parts[] = $this->country;
        }
        
        return implode(", ", $parts);
    }
    
    // Set object properties from array
    private function setProperties($data) {
        $this->id = $data['id'] ?? null;
        $this->street_address = $data['street_address'] ?? null;
        $this->city = $data['city'] ?? null;
        $this->state_province = $data['state_province'] ?? null;
        $this->postal_code = $data['postal_code'] ?? null;
        $this->country = $data['country'] ?? null;
        $this->latitude = $data['latitude'] ?? null;
        $this->longitude = $data['longitude'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }
}
?>