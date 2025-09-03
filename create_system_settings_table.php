<?php
require_once 'config.php';

/*
 * System Settings Table Setup
 *
 * This script creates the system_settings table for storing application configuration
 */

$sql = "
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    if ($conn->query($sql) === TRUE) {
        echo "✓ System settings table created successfully!<br>";

        // Insert default settings
        $default_settings = [
            ['site_name', 'Tierras.mx', 'Nombre del sitio web'],
            ['site_description', 'Plataforma inmobiliaria líder en México', 'Descripción del sitio'],
            ['contact_email', 'contacto@tierras.mx', 'Email de contacto principal'],
            ['support_email', 'soporte@tierras.mx', 'Email de soporte técnico'],
            ['admin_email', 'admin@tierras.mx', 'Email del administrador'],
            ['smtp_host', '', 'Servidor SMTP'],
            ['smtp_port', '587', 'Puerto SMTP'],
            ['smtp_username', '', 'Usuario SMTP'],
            ['smtp_password', '', 'Contraseña SMTP'],
            ['smtp_encryption', 'tls', 'Tipo de encriptación SMTP'],
            ['session_timeout', '3600', 'Tiempo de expiración de sesión en segundos'],
            ['max_login_attempts', '5', 'Máximo número de intentos de login'],
            ['password_min_length', '8', 'Longitud mínima de contraseña'],
            ['auto_approve_properties', '0', 'Auto-aprobar propiedades (1=Sí, 0=No)'],
            ['max_properties_per_user', '10', 'Máximo número de propiedades por usuario'],
            ['property_expiry_days', '30', 'Días para expiración de propiedades'],
            ['maintenance_mode', '0', 'Modo de mantenimiento (1=Activado, 0=Desactivado)'],
            ['maintenance_message', 'El sitio está en mantenimiento. Volveremos pronto.', 'Mensaje de mantenimiento'],
            ['allow_registrations', '1', 'Permitir nuevos registros (1=Sí, 0=No)'],
            ['require_email_verification', '1', 'Requerir verificación de email (1=Sí, 0=No)']
        ];

        $insert_sql = "INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_description) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);

        foreach ($default_settings as $setting) {
            $stmt->bind_param("sss", $setting[0], $setting[1], $setting[2]);
            $stmt->execute();
        }
        $stmt->close();

        echo "✓ Default settings inserted successfully!<br>";
        echo "<br>🎉 System settings setup completed successfully!<br>";

    } else {
        throw new Exception("Error creating system settings table: " . $conn->error);
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
} finally {
    $conn->close();
}
?>