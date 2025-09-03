<?php
require_once 'config.php';

$admin_username = 'admin';
$admin_email = 'admin@tierras.mx';
$admin_password = 'test';

// Hash the password using Argon2ID
$hashed_password = password_hash($admin_password, PASSWORD_ARGON2ID);

$sql = "INSERT INTO users (username, email, password, user_type, first_name, last_name, created_at)
        VALUES (?, ?, ?, 'admin', 'Admin', 'User', NOW())
        ON DUPLICATE KEY UPDATE
        password = VALUES(password),
        user_type = VALUES(user_type),
        first_name = VALUES(first_name),
        last_name = VALUES(last_name)";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("sss", $admin_username, $admin_email, $hashed_password);

    if ($stmt->execute()) {
        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Admin User Created - Tierras.mx</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>
                body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
                .success-container { background: white; border-radius: 15px; padding: 2rem; max-width: 500px; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
                .success-icon { font-size: 4rem; color: #28a745; text-align: center; margin-bottom: 1rem; }
                .credentials { background: #f8f9fa; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
                .credentials strong { color: #495057; }
            </style>
        </head>
        <body>
            <div class='success-container'>
                <div class='success-icon'>
                    <i class='fas fa-check-circle'></i>
                </div>
                <h2 class='text-center mb-4'>✅ Admin User Created Successfully!</h2>

                <div class='credentials'>
                    <h5>Login Credentials:</h5>
                    <p><strong>Username:</strong> {$admin_username}</p>
                    <p><strong>Email:</strong> {$admin_email}</p>
                    <p><strong>Password:</strong> {$admin_password}</p>
                    <p><strong>User Type:</strong> Administrator</p>
                </div>

                <div class='alert alert-info'>
                    <i class='fas fa-info-circle me-2'></i>
                    <strong>Access Admin Panel:</strong>
                    <a href='http://localhost:8000/admin/' class='alert-link'>http://localhost:8000/admin/</a>
                </div>

                <div class='alert alert-warning'>
                    <i class='fas fa-exclamation-triangle me-2'></i>
                    <strong>Security Note:</strong> Please change the password after first login for security purposes.
                </div>

                <div class='text-center mt-4'>
                    <a href='http://localhost:8000/admin/' class='btn btn-primary btn-lg'>
                        <i class='fas fa-sign-in-alt me-2'></i>Go to Admin Panel
                    </a>
                </div>
            </div>
        </body>
        </html>";
    } else {
        echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px; font-family: Arial, sans-serif;'>";
        echo "❌ Error creating admin user: " . $stmt->error;
        echo "</div>";
    }
    $stmt->close();
} else {
    echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px; font-family: Arial, sans-serif;'>";
    echo "❌ Database error: " . $conn->error;
    echo "</div>";
}

$conn->close();
?>