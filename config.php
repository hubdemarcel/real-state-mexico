<?php
/*
 * Database Configuration
 *
 * This file contains the configuration settings for your database connection.
 * Database credentials are loaded from environment variables for security.
 * If environment variables are not set, fallback values are used.
 */

// ** MySQL settings - Production Configuration ** //
/** The name of the database for your website */
define('DB_NAME', 'u453889947_tierras_stagin');

/** MySQL database username */
define('DB_USER', 'u453889947_tierras_user');

/** MySQL database password */
define('DB_PASSWORD', 'Host2024db1');

/** MySQL hostname */
define('DB_HOST', 'mysql.hostinger.com');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/*
 * Create a new database connection
 */
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>