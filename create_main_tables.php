<?php
require_once 'config.php';

/*
 * Main Database Tables Setup
 *
 * This script creates the core tables: users and properties
 * These are referenced by other setup scripts but don't have their own creation scripts
 */

// SQL to create users table
$users_sql = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('user', 'agent', 'admin') DEFAULT 'user',
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone_number VARCHAR(20),
    bio TEXT,
    profile_picture_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_user_type (user_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// SQL to create properties table
$properties_sql = "
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(15,2) NOT NULL,
    location VARCHAR(255) NOT NULL,
    property_type VARCHAR(50) NOT NULL,
    image_url VARCHAR(500),
    agent_id INT,
    bedrooms INT,
    bathrooms DECIMAL(3,1),
    amenities TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_location (location),
    INDEX idx_property_type (property_type),
    INDEX idx_price (price),
    INDEX idx_agent_id (agent_id),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    // Create users table
    if ($conn->query($users_sql) === TRUE) {
        echo "Users table created successfully!<br>";
    } else {
        throw new Exception("Error creating users table: " . $conn->error);
    }

    // Create properties table
    if ($conn->query($properties_sql) === TRUE) {
        echo "Properties table created successfully!<br>";
    } else {
        throw new Exception("Error creating properties table: " . $conn->error);
    }

    echo "Main database tables setup completed successfully!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    $conn->close();
}
?>