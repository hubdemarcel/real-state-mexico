<?php
require_once 'config.php';

// SQL to create agents table
$sql = "
CREATE TABLE IF NOT EXISTS agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20),
    bio TEXT,
    profile_picture_url VARCHAR(500),
    company VARCHAR(255),
    license_number VARCHAR(100),
    specialties TEXT,
    experience_years INT DEFAULT 0,
    location VARCHAR(255),
    website VARCHAR(255),
    social_media JSON,
    rating DECIMAL(3,2) DEFAULT 0.00,
    review_count INT DEFAULT 0,
    properties_sold INT DEFAULT 0,
    total_sales DECIMAL(15,2) DEFAULT 0.00,
    is_verified BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    if ($conn->query($sql) === TRUE) {
        echo "Agents table created successfully!<br>";
    } else {
        throw new Exception("Error creating agents table: " . $conn->error);
    }

    echo "Database setup for agents completed successfully!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    $conn->close();
}
?>