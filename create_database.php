<?php
/*
 * Database Creation Script
 *
 * This script creates the database for local development
 */

// Connect to MySQL without specifying a database
$conn = new mysqli('localhost', 'root', '');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create the database
$sql = "CREATE DATABASE IF NOT EXISTS real_state_mexico CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "Database 'real_state_mexico' created successfully!";
} else {
    echo "Error creating database: " . $conn->error;
}

$conn->close();
?>