<?php
require_once 'config.php';

/*
 * Market Data Tables Setup
 *
 * This script creates tables for storing external market data
 * from INEGI, web scraping, and other sources for intelligence features
 */

// SQL to create market_data table for storing external market statistics
$market_data_sql = "
CREATE TABLE IF NOT EXISTS market_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location VARCHAR(255) NOT NULL,
    location_type ENUM('city', 'state', 'neighborhood', 'region') DEFAULT 'city',
    data_source VARCHAR(100) NOT NULL,
    data_type ENUM('price_per_sqm', 'avg_price', 'transaction_volume', 'inventory', 'demand_index', 'rental_yield') NOT NULL,
    value DECIMAL(15,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'MXN',
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    period_type ENUM('daily', 'weekly', 'monthly', 'quarterly', 'yearly') DEFAULT 'monthly',
    confidence_level DECIMAL(3,2) DEFAULT 1.00,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_location (location),
    INDEX idx_data_type (data_type),
    INDEX idx_period (period_start, period_end),
    INDEX idx_source (data_source),
    UNIQUE KEY unique_market_data (location, data_type, period_start, period_end, data_source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// SQL to create location_coordinates table for geospatial data
$location_coordinates_sql = "
CREATE TABLE IF NOT EXISTS location_coordinates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location_name VARCHAR(255) NOT NULL UNIQUE,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    location_type ENUM('city', 'state', 'neighborhood', 'region') DEFAULT 'city',
    population INT,
    area_sqm DECIMAL(15,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_coordinates (latitude, longitude),
    INDEX idx_location_type (location_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// SQL to create data_collection_log table for tracking data updates
$data_collection_log_sql = "
CREATE TABLE IF NOT EXISTS data_collection_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_source VARCHAR(100) NOT NULL,
    location VARCHAR(255),
    records_collected INT DEFAULT 0,
    records_updated INT DEFAULT 0,
    records_failed INT DEFAULT 0,
    status ENUM('success', 'partial', 'failed') DEFAULT 'success',
    error_message TEXT,
    execution_time_seconds DECIMAL(8,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_source (data_source),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// SQL to create property_market_trends table for trend analysis
$property_market_trends_sql = "
CREATE TABLE IF NOT EXISTS property_market_trends (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location VARCHAR(255) NOT NULL,
    property_type VARCHAR(50) NOT NULL,
    trend_type ENUM('price_growth', 'demand_change', 'inventory_change', 'rental_yield') NOT NULL,
    value DECIMAL(8,4) NOT NULL,
    period_months INT DEFAULT 12,
    confidence_level DECIMAL(3,2) DEFAULT 1.00,
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_location_type (location, property_type),
    INDEX idx_trend_type (trend_type),
    INDEX idx_calculated_at (calculated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    // Create market_data table
    if ($conn->query($market_data_sql) === TRUE) {
        echo "Market data table created successfully!<br>";
    } else {
        throw new Exception("Error creating market_data table: " . $conn->error);
    }

    // Create location_coordinates table
    if ($conn->query($location_coordinates_sql) === TRUE) {
        echo "Location coordinates table created successfully!<br>";
    } else {
        throw new Exception("Error creating location_coordinates table: " . $conn->error);
    }

    // Create data_collection_log table
    if ($conn->query($data_collection_log_sql) === TRUE) {
        echo "Data collection log table created successfully!<br>";
    } else {
        throw new Exception("Error creating data_collection_log table: " . $conn->error);
    }

    // Create property_market_trends table
    if ($conn->query($property_market_trends_sql) === TRUE) {
        echo "Property market trends table created successfully!<br>";
    } else {
        throw new Exception("Error creating property_market_trends table: " . $conn->error);
    }

    echo "<br>Market data infrastructure setup completed successfully!<br>";
    echo "Tables created: market_data, location_coordinates, data_collection_log, property_market_trends";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    $conn->close();
}
?>