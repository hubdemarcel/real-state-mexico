<?php
require_once 'config.php';

/*
 * CMS Database Tables Setup
 *
 * This script creates the core tables for the Content Management System:
 * - posts: Blog posts, news articles, property guides
 * - categories: Content categories
 * - tags: Content tags
 * - post_tags: Many-to-many relationship between posts and tags
 * - admin_audit_log: Track admin actions
 */

try {
    // SQL to create categories table
    $categories_sql = "
    CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        slug VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        parent_id INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_slug (slug),
        INDEX idx_parent_id (parent_id),
        FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // SQL to create posts table
    $posts_sql = "
    CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content LONGTEXT NOT NULL,
        excerpt TEXT,
        featured_image VARCHAR(500),
        status ENUM('draft', 'published', 'pending', 'archived') DEFAULT 'draft',
        post_type ENUM('post', 'page', 'property_guide', 'news') DEFAULT 'post',
        category_id INT,
        author_id INT NOT NULL,
        published_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_slug (slug),
        INDEX idx_status (status),
        INDEX idx_post_type (post_type),
        INDEX idx_category_id (category_id),
        INDEX idx_author_id (author_id),
        INDEX idx_published_at (published_at),
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
        FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // SQL to create tags table
    $tags_sql = "
    CREATE TABLE IF NOT EXISTS tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        slug VARCHAR(50) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_slug (slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // SQL to create post_tags table (many-to-many relationship)
    $post_tags_sql = "
    CREATE TABLE IF NOT EXISTS post_tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        tag_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_post_tag (post_id, tag_id),
        INDEX idx_post_id (post_id),
        INDEX idx_tag_id (tag_id),
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // SQL to create admin_audit_log table
    $audit_log_sql = "
    CREATE TABLE IF NOT EXISTS admin_audit_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        action VARCHAR(100) NOT NULL,
        entity_type VARCHAR(50) NOT NULL,
        entity_id INT,
        old_values JSON,
        new_values JSON,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_admin_id (admin_id),
        INDEX idx_action (action),
        INDEX idx_entity_type (entity_type),
        INDEX idx_created_at (created_at),
        FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // SQL to add approval status to properties table
    $alter_properties_sql = "
    ALTER TABLE properties
    ADD COLUMN IF NOT EXISTS approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS approved_by INT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS approved_at TIMESTAMP NULL,
    ADD COLUMN IF NOT EXISTS rejection_reason TEXT,
    ADD COLUMN IF NOT EXISTS admin_notes TEXT,
    ADD INDEX IF NOT EXISTS idx_approval_status (approval_status),
    ADD INDEX IF NOT EXISTS idx_approved_by (approved_by),
    ADD FOREIGN KEY IF NOT EXISTS (approved_by) REFERENCES users(id) ON DELETE SET NULL;
    ";

    // Execute table creation queries
    $queries = [
        'categories' => $categories_sql,
        'posts' => $posts_sql,
        'tags' => $tags_sql,
        'post_tags' => $post_tags_sql,
        'admin_audit_log' => $audit_log_sql,
        'alter_properties' => $alter_properties_sql
    ];

    foreach ($queries as $table_name => $sql) {
        if ($conn->query($sql) === TRUE) {
            echo "✓ {$table_name} table setup completed successfully!<br>";
        } else {
            throw new Exception("Error creating {$table_name} table: " . $conn->error);
        }
    }

    // Insert default categories
    $default_categories = [
        ['Real Estate News', 'real-estate-news', 'Latest news and updates in the real estate market'],
        ['Property Guides', 'property-guides', 'Comprehensive guides for buying and selling properties'],
        ['Market Analysis', 'market-analysis', 'Real estate market trends and analysis'],
        ['Legal Updates', 'legal-updates', 'Legal updates and regulations in real estate'],
        ['Tips & Advice', 'tips-advice', 'Tips and advice for property owners and investors']
    ];

    $insert_category_sql = "INSERT IGNORE INTO categories (name, slug, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_category_sql);

    foreach ($default_categories as $category) {
        $stmt->bind_param("sss", $category[0], $category[1], $category[2]);
        $stmt->execute();
    }
    $stmt->close();

    echo "<br>✓ Default categories inserted successfully!<br>";
    echo "<br>🎉 CMS database tables setup completed successfully!<br>";
    echo "<br><strong>Next steps:</strong><br>";
    echo "1. Create an admin user account<br>";
    echo "2. Access the admin panel at /admin/<br>";
    echo "3. Start managing content and users<br>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
} finally {
    $conn->close();
}
?>