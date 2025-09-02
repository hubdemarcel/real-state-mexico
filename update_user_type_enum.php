<?php
require_once 'config.php';

/*
 * Migration Script: Update user_type enum
 *
 * This script updates the user_type column in the users table
 * to include 'buyer', 'seller', 'agent', 'admin' instead of 'user', 'agent', 'admin'
 */

$alter_sql = "
ALTER TABLE users
MODIFY COLUMN user_type ENUM('buyer', 'seller', 'agent', 'admin') DEFAULT 'buyer';
";

try {
    if ($conn->query($alter_sql) === TRUE) {
        echo "User type enum updated successfully!<br>";
        echo "New values: 'buyer', 'seller', 'agent', 'admin'<br>";
        echo "Default: 'buyer'<br>";
    } else {
        throw new Exception("Error updating user_type enum: " . $conn->error);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    $conn->close();
}
?>