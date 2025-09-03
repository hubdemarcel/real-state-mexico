<?php
/**
 * Security Functions for Admin Panel
 *
 * This file contains security-related functions including:
 * - CSRF protection
 * - Input validation and sanitization
 * - Rate limiting
 * - Security headers
 * - SQL injection prevention
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        logSecurityEvent('CSRF token validation failed', 'csrf', $_SERVER['REMOTE_ADDR']);
        return false;
    }
    return true;
}

/**
 * Get CSRF token for forms
 */
function getCSRFToken() {
    return generateCSRFToken();
}

/**
 * Validate and sanitize input data
 */
function sanitizeInput($data, $type = 'string') {
    switch ($type) {
        case 'string':
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            break;
        case 'email':
            $data = filter_var($data, FILTER_SANITIZE_EMAIL);
            break;
        case 'url':
            $data = filter_var($data, FILTER_SANITIZE_URL);
            break;
        case 'int':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            break;
        case 'float':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            break;
    }
    return $data;
}

/**
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 */
function validatePasswordStrength($password) {
    // Minimum requirements
    $minLength = 8;
    $hasUpper = preg_match('/[A-Z]/', $password);
    $hasLower = preg_match('/[a-z]/', $password);
    $hasNumber = preg_match('/[0-9]/', $password);
    $hasSpecial = preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password);

    return strlen($password) >= $minLength && $hasUpper && $hasLower && $hasNumber && $hasSpecial;
}

/**
 * Rate limiting function
 */
function checkRateLimit($action, $limit = 10, $timeWindow = 60) {
    $key = "rate_limit_{$action}_" . $_SERVER['REMOTE_ADDR'];

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'reset_time' => time() + $timeWindow];
    }

    $rateData = &$_SESSION[$key];

    // Reset if time window has passed
    if (time() > $rateData['reset_time']) {
        $rateData['count'] = 0;
        $rateData['reset_time'] = time() + $timeWindow;
    }

    // Check if limit exceeded
    if ($rateData['count'] >= $limit) {
        logSecurityEvent("Rate limit exceeded for action: $action", 'rate_limit', $_SERVER['REMOTE_ADDR']);
        return false;
    }

    $rateData['count']++;
    return true;
}

/**
 * Log security events
 */
function logSecurityEvent($message, $type, $ipAddress) {
    global $conn;

    $sql = "INSERT INTO admin_audit_log (admin_id, action, entity_type, entity_id, old_values, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $adminId = $_SESSION['id'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ississs", $adminId, $message, $type, null, null, $ipAddress, $userAgent);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) { // 5MB default
    $errors = [];

    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Error uploading file';
        return $errors;
    }

    // Check file size
    if ($file['size'] > $maxSize) {
        $errors[] = 'File size exceeds maximum allowed size';
    }

    // Check file type
    if (!empty($allowedTypes)) {
        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = 'File type not allowed';
        }
    }

    // Check for malicious content (basic check)
    $fileContent = file_get_contents($file['tmp_name']);
    if (preg_match('/<\?php/i', $fileContent) || preg_match('/<script/i', $fileContent)) {
        $errors[] = 'Potentially malicious file content detected';
    }

    return $errors;
}

/**
 * Generate secure random string
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Hash password securely
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if user has admin privileges
 */
function requireAdminAccess() {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header('Location: ../login.html?status=error&message=Debes iniciar sesión');
        exit();
    }

    if ($_SESSION['user_type'] !== 'admin') {
        logSecurityEvent('Unauthorized access attempt to admin panel', 'unauthorized_access', $_SERVER['REMOTE_ADDR']);
        header('Location: ../user_dashboard.php?status=error&message=No tienes permisos para acceder al panel de administración');
        exit();
    }
}

/**
 * Validate admin session
 */
function validateAdminSession() {
    // Check if session has expired
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) { // 30 minutes
        session_destroy();
        header('Location: ../login.html?status=error&message=Sesión expirada. Por favor, inicia sesión nuevamente');
        exit();
    }

    $_SESSION['last_activity'] = time();

    // Regenerate session ID periodically
    if (!isset($_SESSION['regenerated']) || time() - $_SESSION['regenerated'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['regenerated'] = time();
    }
}

/**
 * Set security headers
 */
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: DENY');

    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');

    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');

    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Content Security Policy (basic)
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com;");

    // HSTS (HTTP Strict Transport Security) - only if HTTPS
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

/**
 * Sanitize SQL input to prevent injection
 */
function sanitizeSQLInput($input) {
    global $conn;
    if (is_array($input)) {
        return array_map(function($value) use ($conn) {
            return $conn->real_escape_string($value);
        }, $input);
    }
    return $conn->real_escape_string($input);
}

/**
 * Validate and sanitize form data
 */
function validateFormData($data, $rules) {
    $errors = [];
    $sanitized = [];

    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? '';

        // Check required fields
        if (isset($rule['required']) && $rule['required'] && empty($value)) {
            $errors[$field] = 'Este campo es obligatorio';
            continue;
        }

        // Skip validation if field is empty and not required
        if (empty($value) && !isset($rule['required'])) {
            $sanitized[$field] = '';
            continue;
        }

        // Type validation
        if (isset($rule['type'])) {
            switch ($rule['type']) {
                case 'email':
                    if (!validateEmail($value)) {
                        $errors[$field] = 'Email inválido';
                        continue;
                    }
                    break;
                case 'int':
                    if (!filter_var($value, FILTER_VALIDATE_INT)) {
                        $errors[$field] = 'Debe ser un número entero';
                        continue;
                    }
                    break;
                case 'float':
                    if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
                        $errors[$field] = 'Debe ser un número decimal';
                        continue;
                    }
                    break;
                case 'url':
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        $errors[$field] = 'URL inválida';
                        continue;
                    }
                    break;
            }
        }

        // Length validation
        if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
            $errors[$field] = "Debe tener al menos {$rule['min_length']} caracteres";
            continue;
        }

        if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
            $errors[$field] = "No puede tener más de {$rule['max_length']} caracteres";
            continue;
        }

        // Pattern validation
        if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
            $errors[$field] = $rule['pattern_message'] ?? 'Formato inválido';
            continue;
        }

        // Custom validation
        if (isset($rule['custom']) && is_callable($rule['custom'])) {
            $customError = $rule['custom']($value);
            if ($customError) {
                $errors[$field] = $customError;
                continue;
            }
        }

        // Sanitize the value
        $sanitizeType = $rule['sanitize'] ?? 'string';
        $sanitized[$field] = sanitizeInput($value, $sanitizeType);
    }

    return ['errors' => $errors, 'sanitized' => $sanitized];
}

/**
 * Check for suspicious activity
 */
function detectSuspiciousActivity() {
    $suspicious = false;
    $reasons = [];

    // Check for rapid requests
    if (!checkRateLimit('admin_request', 100, 60)) { // 100 requests per minute
        $suspicious = true;
        $reasons[] = 'High request frequency';
    }

    // Check for suspicious user agents
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (empty($userAgent) || preg_match('/(bot|crawl|spider|scan)/i', $userAgent)) {
        $suspicious = true;
        $reasons[] = 'Suspicious user agent';
    }

    // Check for SQL injection attempts
    foreach ($_GET as $key => $value) {
        if (is_string($value) && preg_match('/(\bunion\b|\bselect\b|\binsert\b|\bupdate\b|\bdelete\b|\bdrop\b|\bcreate\b|\balter\b)/i', $value)) {
            $suspicious = true;
            $reasons[] = 'Potential SQL injection in GET parameter: ' . $key;
        }
    }

    foreach ($_POST as $key => $value) {
        if (is_string($value) && preg_match('/(\bunion\b|\bselect\b|\binsert\b|\bupdate\b|\bdelete\b|\bdrop\b|\bcreate\b|\balter\b)/i', $value)) {
            $suspicious = true;
            $reasons[] = 'Potential SQL injection in POST parameter: ' . $key;
        }
    }

    if ($suspicious) {
        logSecurityEvent('Suspicious activity detected: ' . implode(', ', $reasons), 'suspicious_activity', $_SERVER['REMOTE_ADDR']);

        // Could implement additional measures like temporary blocking
        // For now, just log the activity
    }

    return $suspicious;
}

/**
 * Initialize security measures
 */
function initializeSecurity() {
    setSecurityHeaders();
    validateAdminSession();
    detectSuspiciousActivity();
}

// Initialize security on every admin page load
initializeSecurity();
?>