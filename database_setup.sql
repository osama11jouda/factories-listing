CREATE DATABASE IF NOT EXISTS factories_listing;
USE factories_listing;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    company VARCHAR(100),
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT FALSE,
    subscription_expiry DATE,
    is_admin BOOLEAN DEFAULT FALSE
);

-- Factories table
CREATE TABLE IF NOT EXISTS factories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255),
    price DECIMAL(15, 2),
    area DECIMAL(10, 2),
    type ENUM('sale', 'rent') NOT NULL,
    status ENUM('available', 'pending', 'sold') DEFAULT 'available',
    featured BOOLEAN DEFAULT FALSE,
    date_added DATETIME DEFAULT CURRENT_TIMESTAMP,
    contact_info TEXT
);

-- Factory images table
CREATE TABLE IF NOT EXISTS factory_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    factory_id INT,
    image_path VARCHAR(255),
    is_main BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (factory_id) REFERENCES factories(id) ON DELETE CASCADE
);

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255),
    message TEXT,
    submission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE
);
