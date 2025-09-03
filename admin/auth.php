<?php
/**
 * Admin Authentication and Access Control Functions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if current user is logged in as admin
 */
function isAdminLoggedIn() {
    return isset($_SESSION['loggedin']) &&
           $_SESSION['loggedin'] === true &&
           isset($_SESSION['user_type']) &&
           $_SESSION['user_type'] === 'admin';
}

/**
 * Require admin login - redirect to login page if not authenticated
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Get current admin user info
 */
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'first_name' => $_SESSION['first_name'] ?? '',
        'last_name' => $_SESSION['last_name'] ?? '',
        'full_name' => trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''))
    ];
}

/**
 * Log admin action to audit log
 */
function logAdminAction($action, $entity_type, $entity_id = null, $old_values = null, $new_values = null) {
    if (!isAdminLoggedIn()) {
        return false;
    }

    require_once '../config.php';

    $admin_id = $_SESSION['id'];
    $sql = "INSERT INTO admin_audit_log (admin_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $old_json = $old_values ? json_encode($old_values) : null;
        $new_json = $new_values ? json_encode($new_values) : null;

        $stmt->bind_param("ississss", $admin_id, $action, $entity_type, $entity_id, $old_json, $new_json, $ip, $user_agent);
        $result = $stmt->execute();
        $stmt->close();
        returnConnection($conn);
        return $result;
    }

    returnConnection($conn);
    return false;
}

/**
 * Check if admin has permission for specific action
 * Currently all admins have full access, but this can be extended for role-based permissions
 */
function hasAdminPermission($permission) {
    if (!isAdminLoggedIn()) {
        return false;
    }

    // For now, all admins have full permissions
    // This can be extended to check specific permissions from database
    $permissions = [
        'manage_users',
        'manage_properties',
        'manage_agents',
        'manage_content',
        'view_analytics',
        'manage_settings',
        'export_data'
    ];

    return in_array($permission, $permissions);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Admin logout function
 */
function adminLogout() {
    // Log logout action
    if (isAdminLoggedIn()) {
        logAdminAction('logout', 'user', $_SESSION['id'], null, ['logout_time' => date('Y-m-d H:i:s')]);
    }

    // Clear session
    session_unset();
    session_destroy();

    // Redirect to login
    header('Location: login.php?status=logged_out');
    exit();
}

/**
 * Get admin statistics for dashboard
 */
function getAdminStats() {
    if (!isAdminLoggedIn()) {
        return null;
    }

    require_once '../config.php';

    $stats = [
        'total_users' => 0,
        'total_properties' => 0,
        'pending_properties' => 0,
        'total_agents' => 0,
        'total_posts' => 0,
        'recent_activities' => []
    ];

    // Get user counts by type
    $user_sql = "SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type";
    if ($result = $conn->query($user_sql)) {
        while ($row = $result->fetch_assoc()) {
            switch ($row['user_type']) {
                case 'buyer':
                    $stats['total_buyers'] = $row['count'];
                    break;
                case 'seller':
                    $stats['total_sellers'] = $row['count'];
                    break;
                case 'agent':
                    $stats['total_agents'] = $row['count'];
                    break;
                case 'admin':
                    $stats['total_admins'] = $row['count'];
                    break;
            }
            $stats['total_users'] += $row['count'];
        }
    }

    // Get property statistics
    $property_sql = "SELECT
                        COUNT(*) as total_properties,
                        SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending_properties,
                        SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved_properties
                     FROM properties";
    if ($result = $conn->query($property_sql)) {
        $row = $result->fetch_assoc();
        $stats['total_properties'] = $row['total_properties'];
        $stats['pending_properties'] = $row['pending_properties'];
        $stats['approved_properties'] = $row['approved_properties'];
    }

    // Get post count
    $post_sql = "SELECT COUNT(*) as total_posts FROM posts WHERE status = 'published'";
    if ($result = $conn->query($post_sql)) {
        $row = $result->fetch_assoc();
        $stats['total_posts'] = $row['total_posts'];
    }

    // Get recent activities from audit log
    $activity_sql = "SELECT a.*, u.username as admin_name
                     FROM admin_audit_log a
                     INNER JOIN users u ON a.admin_id = u.id
                     ORDER BY a.created_at DESC LIMIT 10";
    if ($result = $conn->query($activity_sql)) {
        while ($row = $result->fetch_assoc()) {
            $stats['recent_activities'][] = $row;
        }
    }

    returnConnection($conn);
    return $stats;
}

/**
 * Check if admin session is still valid (not expired)
 */
function validateAdminSession() {
    if (!isAdminLoggedIn()) {
        return false;
    }

    // Check if session has expired (24 hours)
    $session_lifetime = 24 * 60 * 60; // 24 hours in seconds
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $session_lifetime) {
        adminLogout();
        return false;
    }

    // Update login time to extend session
    $_SESSION['login_time'] = time();
    return true;
}
?>