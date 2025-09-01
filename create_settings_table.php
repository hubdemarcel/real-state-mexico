<?php
require_once 'config.php';

// Create settings table
$sql = "CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'settings' created successfully.\n";
} else {
    echo "Error creating settings table: " . $conn->error . "\n";
}

// Insert logo path
$logo_path = 'assets/images/logo.png';
$insert_sql = "INSERT INTO settings (setting_key, setting_value) VALUES ('site_logo', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";

$stmt = $conn->prepare($insert_sql);
$stmt->bind_param("s", $logo_path);

if ($stmt->execute()) {
    echo "Logo path inserted/updated successfully.\n";
} else {
    echo "Error inserting logo: " . $stmt->error . "\n";
}

$stmt->close();
$conn->close();
?>