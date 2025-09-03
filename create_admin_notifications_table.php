<?php
require_once 'config.php';

/*
 * Admin Notifications Tables Setup
 *
 * This script creates the tables for the admin notification system:
 * - admin_notifications: Store notifications for admins
 * - admin_notification_settings: Store notification preferences per admin
 */

$sql1 = "
CREATE TABLE IF NOT EXISTS admin_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('user', 'property', 'system', 'security') DEFAULT 'system',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    related_user_id INT DEFAULT NULL,
    related_entity_type VARCHAR(50) DEFAULT NULL,
    related_entity_id INT DEFAULT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    is_system BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_priority (priority),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    INDEX idx_related_user (related_user_id),
    FOREIGN KEY (related_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$sql2 = "
CREATE TABLE IF NOT EXISTS admin_notification_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value VARCHAR(255) DEFAULT '1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_admin_setting (admin_id, setting_key),
    INDEX idx_admin_id (admin_id),
    INDEX idx_setting_key (setting_key),
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    if ($conn->query($sql1) === TRUE) {
        echo "✓ Admin notifications table created successfully!<br>";
    } else {
        throw new Exception("Error creating admin notifications table: " . $conn->error);
    }

    if ($conn->query($sql2) === TRUE) {
        echo "✓ Admin notification settings table created successfully!<br>";
    } else {
        throw new Exception("Error creating admin notification settings table: " . $conn->error);
    }

    // Insert default notification settings for existing admins
    $sql = "SELECT id FROM users WHERE user_type = 'admin'";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $admin_id = $row['id'];

            // Insert default settings
            $default_settings = [
                ['email_notifications', '1'],
                ['property_submissions', '1'],
                ['user_registrations', '1'],
                ['agent_verifications', '1'],
                ['system_alerts', '1'],
                ['security_alerts', '1']
            ];

            foreach ($default_settings as $setting) {
                $insert_sql = "INSERT IGNORE INTO admin_notification_settings (admin_id, setting_key, setting_value) VALUES (?, ?, ?)";
                if ($stmt = $conn->prepare($insert_sql)) {
                    $stmt->bind_param("iss", $admin_id, $setting[0], $setting[1]);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    }

    echo "✓ Default notification settings inserted for existing admins!<br>";
    echo "<br>🎉 Admin notification tables setup completed successfully!<br>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
} finally {
    $conn->close();
}
?>