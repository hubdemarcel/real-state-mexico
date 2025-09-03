<?php
require_once 'config.php';

echo "Testing database connection...<br>";

if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
} else {
    echo "Connection successful!<br>";
    echo "Host: " . DB_HOST . "<br>";
    echo "Database: " . DB_NAME . "<br>";
    echo "User: " . DB_USER . "<br>";
}

$conn->close();
?>