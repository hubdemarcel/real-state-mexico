<?php
/*
 * Database Configuration with Connection Pooling
 *
 * This file contains optimized database connection settings with connection pooling
 * and retry logic to handle hosting provider connection limits.
 */

// ** MySQL settings - Production Configuration ** //
/** The name of the database for your website */
define('DB_NAME', 'u453889947_tierras_stagin');

/** MySQL database username */
define('DB_USER', 'u453889947_tierras_user');

/** MySQL database password */
define('DB_PASSWORD', 'Host2024db1');

/** MySQL hostname */
define('DB_HOST', '147.79.122.206');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** Connection Pool Settings */
if (!defined('MAX_CONNECTIONS')) {
    define('MAX_CONNECTIONS', 5);
    define('CONNECTION_TIMEOUT', 30);
    define('RETRY_ATTEMPTS', 3);
    define('RETRY_DELAY', 1000000); // 1 second in microseconds
}

// Global connection pool
global $connection_pool;
if (!isset($connection_pool)) {
    $connection_pool = [];
}

/**
 * Get a database connection from the pool or create a new one
 */
function getDatabaseConnection() {
    global $connection_pool;

    // Try to find an available connection
    foreach ($connection_pool as $key => $conn_data) {
        if ($conn_data['available'] && isConnectionValid($conn_data['connection'])) {
            $connection_pool[$key]['available'] = false;
            $connection_pool[$key]['last_used'] = time();
            return $conn_data['connection'];
        }
    }

    // If no available connection and pool is not full, create a new one
    if (count($connection_pool) < MAX_CONNECTIONS) {
        $conn = createNewConnection();
        if ($conn) {
            $connection_pool[] = [
                'connection' => $conn,
                'available' => false,
                'created' => time(),
                'last_used' => time()
            ];
            return $conn;
        }
    }

    // If pool is full, wait for an available connection
    $start_time = time();
    while (time() - $start_time < CONNECTION_TIMEOUT) {
        foreach ($connection_pool as $key => $conn_data) {
            if ($conn_data['available'] && isConnectionValid($conn_data['connection'])) {
                $connection_pool[$key]['available'] = false;
                $connection_pool[$key]['last_used'] = time();
                return $conn_data['connection'];
            }
        }
        usleep(100000); // Wait 0.1 seconds
    }

    // If timeout reached, create a new connection anyway (last resort)
    $conn = createNewConnection();
    if ($conn) {
        // Remove oldest connection if pool is full
        if (count($connection_pool) >= MAX_CONNECTIONS) {
            array_shift($connection_pool);
        }
        $connection_pool[] = [
            'connection' => $conn,
            'available' => false,
            'created' => time(),
            'last_used' => time()
        ];
        return $conn;
    }

    return false;
}

/**
 * Create a new database connection with retry logic
 */
function createNewConnection() {
    $attempts = 0;

    while ($attempts < RETRY_ATTEMPTS) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            if ($conn->connect_error) {
                $error_code = $conn->connect_errno;

                // Check if it's a connection limit error
                if ($error_code == 1040 || strpos($conn->connect_error, 'max_connections') !== false ||
                    strpos($conn->connect_error, 'max_connections_per_hour') !== false) {

                    error_log("Database connection limit reached. Attempt: " . ($attempts + 1) . " of " . RETRY_ATTEMPTS);
                    $attempts++;

                    if ($attempts < RETRY_ATTEMPTS) {
                        usleep(RETRY_DELAY * pow(2, $attempts)); // Exponential backoff
                        continue;
                    } else {
                        error_log("Max retry attempts reached. Connection limit exceeded.");
                        return false;
                    }
                } else {
                    error_log("Database Connection Failed:");
                    error_log("Host: " . DB_HOST);
                    error_log("Database: " . DB_NAME);
                    error_log("User: " . DB_USER);
                    error_log("Error: " . $conn->connect_error);
                    return false;
                }
            } else {
                // Set charset
                $conn->set_charset(DB_CHARSET);

                // Log successful connection (only occasionally to reduce log spam)
                static $last_log_time = 0;
                if (time() - $last_log_time > 300) { // Log every 5 minutes
                    error_log("Database Connection Successful:");
                    error_log("Host: " . DB_HOST);
                    error_log("Database: " . DB_NAME);
                    $last_log_time = time();
                }

                return $conn;
            }
        } catch (Exception $e) {
            error_log("Database connection exception: " . $e->getMessage());
            $attempts++;

            if ($attempts < RETRY_ATTEMPTS) {
                usleep(RETRY_DELAY * pow(2, $attempts));
            } else {
                return false;
            }
        }
    }

    return false;
}

/**
 * Check if a database connection is still valid
 */
function isConnectionValid($conn) {
    if (!$conn) return false;

    // Try a simple query to test the connection
    if ($conn->ping()) {
        return true;
    }

    return false;
}

/**
 * Return a connection to the pool
 */
function returnConnection($conn) {
    global $connection_pool;

    foreach ($connection_pool as $key => $conn_data) {
        if ($conn_data['connection'] === $conn) {
            $connection_pool[$key]['available'] = true;
            $connection_pool[$key]['last_used'] = time();
            break;
        }
    }
}

/**
 * Clean up old connections
 */
function cleanupConnections() {
    global $connection_pool;

    $current_time = time();
    foreach ($connection_pool as $key => $conn_data) {
        // Remove connections that haven't been used in the last hour
        if ($current_time - $conn_data['last_used'] > 3600) {
            if ($conn_data['connection']) {
                $conn_data['connection']->close();
            }
            unset($connection_pool[$key]);
        }
    }
}

// Register cleanup function to run at shutdown
register_shutdown_function('cleanupConnections');

// Get the database connection
$conn = getDatabaseConnection();

if (!$conn) {
    // If we can't get a connection, show a user-friendly error
    http_response_code(503);
    die("Servicio temporalmente no disponible. Por favor, inténtelo de nuevo en unos minutos.");
}

// For backward compatibility, keep the global $conn variable
?>