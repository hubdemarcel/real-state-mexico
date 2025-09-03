<?php
require_once 'config.php';

/*
 * Subscription/Premium System Setup
 *
 * Creates tables for managing user subscriptions and premium features
 */

 // SQL to create subscriptions table
$subscriptions_sql = "
CREATE TABLE IF NOT EXISTS user_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subscription_type ENUM('free', 'premium', 'enterprise') DEFAULT 'free',
    status ENUM('active', 'inactive', 'cancelled', 'expired') DEFAULT 'active',
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date TIMESTAMP NULL,
    auto_renew BOOLEAN DEFAULT FALSE,
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_subscription_type (subscription_type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// SQL to create premium_features table
$premium_features_sql = "
CREATE TABLE IF NOT EXISTS premium_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    feature_name VARCHAR(100) NOT NULL UNIQUE,
    feature_description TEXT,
    required_subscription ENUM('premium', 'enterprise') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_feature_name (feature_name),
    INDEX idx_required_subscription (required_subscription)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// Insert default premium features
$insert_features_sql = "
INSERT IGNORE INTO premium_features (feature_name, feature_description, required_subscription) VALUES
('agent_intelligence', 'AI-powered market insights and recommendations for agents', 'premium'),
('advanced_analytics', 'Advanced market analytics and forecasting', 'premium'),
('export_reports', 'Export detailed reports in PDF/Excel format', 'premium'),
('priority_support', 'Priority customer support', 'premium'),
('unlimited_properties', 'Unlimited property listings', 'enterprise'),
('api_access', 'Full API access for integrations', 'enterprise'),
('white_label', 'White-label solution', 'enterprise');
";

try {
    // Create subscriptions table
    if ($conn->query($subscriptions_sql) === TRUE) {
        echo "User subscriptions table created successfully!<br>";
    } else {
        throw new Exception("Error creating subscriptions table: " . $conn->error);
    }

    // Create premium features table
    if ($conn->query($premium_features_sql) === TRUE) {
        echo "Premium features table created successfully!<br>";
    } else {
        throw new Exception("Error creating premium features table: " . $conn->error);
    }

    // Insert default premium features
    if ($conn->query($insert_features_sql) === TRUE) {
        echo "Premium features inserted successfully!<br>";
    } else {
        throw new Exception("Error inserting premium features: " . $conn->error);
    }

    echo "Premium subscription system setup completed successfully!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    $conn->close();
}
?>