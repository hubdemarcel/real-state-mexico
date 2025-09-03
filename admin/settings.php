<?php
require_once 'auth.php';
requireAdminLogin();

$pageTitle = 'System Settings - Tierras.mx Admin';
$admin = getCurrentAdmin();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_general':
                $message = updateGeneralSettings($_POST);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'update_email':
                $message = updateEmailSettings($_POST);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'update_security':
                $message = updateSecuritySettings($_POST);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'update_property':
                $message = updatePropertySettings($_POST);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'update_maintenance':
                $message = updateMaintenanceSettings($_POST);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'clear_cache':
                $message = clearSystemCache();
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'backup_database':
                $message = backupDatabase();
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
        }
    }
}

// Get current settings
$settings = getSystemSettings();

function getSystemSettings() {
    global $conn;

    $settings = [
        'site_name' => 'Tierras.mx',
        'site_description' => 'Plataforma inmobiliaria líder en México',
        'contact_email' => 'contacto@tierras.mx',
        'support_email' => 'soporte@tierras.mx',
        'admin_email' => 'admin@tierras.mx',
        'smtp_host' => '',
        'smtp_port' => '587',
        'smtp_username' => '',
        'smtp_password' => '',
        'smtp_encryption' => 'tls',
        'session_timeout' => '3600',
        'max_login_attempts' => '5',
        'password_min_length' => '8',
        'auto_approve_properties' => '0',
        'max_properties_per_user' => '10',
        'property_expiry_days' => '30',
        'maintenance_mode' => '0',
        'maintenance_message' => 'El sitio está en mantenimiento. Volveremos pronto.',
        'allow_registrations' => '1',
        'require_email_verification' => '1'
    ];

    // Try to load settings from database
    $sql = "SELECT setting_key, setting_value FROM system_settings";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }

    return $settings;
}

function updateGeneralSettings($data) {
    global $conn;

    $settings = [
        'site_name' => $data['site_name'] ?? '',
        'site_description' => $data['site_description'] ?? '',
        'contact_email' => $data['contact_email'] ?? '',
        'support_email' => $data['support_email'] ?? '',
        'admin_email' => $data['admin_email'] ?? '',
        'allow_registrations' => isset($data['allow_registrations']) ? '1' : '0',
        'require_email_verification' => isset($data['require_email_verification']) ? '1' : '0'
    ];

    foreach ($settings as $key => $value) {
        saveSetting($key, $value);
    }

    // Log the action
    logAdminAction('update', 'settings', 0, null, ['section' => 'general', 'settings' => $settings]);

    return ['success' => true, 'message' => 'Configuración general actualizada exitosamente.'];
}

function updateEmailSettings($data) {
    global $conn;

    $settings = [
        'smtp_host' => $data['smtp_host'] ?? '',
        'smtp_port' => $data['smtp_port'] ?? '587',
        'smtp_username' => $data['smtp_username'] ?? '',
        'smtp_encryption' => $data['smtp_encryption'] ?? 'tls'
    ];

    // Only save password if it's provided
    if (!empty($data['smtp_password'])) {
        $settings['smtp_password'] = $data['smtp_password'];
    }

    foreach ($settings as $key => $value) {
        saveSetting($key, $value);
    }

    // Log the action
    logAdminAction('update', 'settings', 0, null, ['section' => 'email', 'settings' => array_keys($settings)]);

    return ['success' => true, 'message' => 'Configuración de email actualizada exitosamente.'];
}

function updateSecuritySettings($data) {
    global $conn;

    $settings = [
        'session_timeout' => $data['session_timeout'] ?? '3600',
        'max_login_attempts' => $data['max_login_attempts'] ?? '5',
        'password_min_length' => $data['password_min_length'] ?? '8'
    ];

    foreach ($settings as $key => $value) {
        saveSetting($key, $value);
    }

    // Log the action
    logAdminAction('update', 'settings', 0, null, ['section' => 'security', 'settings' => $settings]);

    return ['success' => true, 'message' => 'Configuración de seguridad actualizada exitosamente.'];
}

function updatePropertySettings($data) {
    global $conn;

    $settings = [
        'auto_approve_properties' => isset($data['auto_approve_properties']) ? '1' : '0',
        'max_properties_per_user' => $data['max_properties_per_user'] ?? '10',
        'property_expiry_days' => $data['property_expiry_days'] ?? '30'
    ];

    foreach ($settings as $key => $value) {
        saveSetting($key, $value);
    }

    // Log the action
    logAdminAction('update', 'settings', 0, null, ['section' => 'property', 'settings' => $settings]);

    return ['success' => true, 'message' => 'Configuración de propiedades actualizada exitosamente.'];
}

function updateMaintenanceSettings($data) {
    global $conn;

    $settings = [
        'maintenance_mode' => isset($data['maintenance_mode']) ? '1' : '0',
        'maintenance_message' => $data['maintenance_message'] ?? ''
    ];

    foreach ($settings as $key => $value) {
        saveSetting($key, $value);
    }

    // Log the action
    logAdminAction('update', 'settings', 0, null, ['section' => 'maintenance', 'settings' => $settings]);

    return ['success' => true, 'message' => 'Configuración de mantenimiento actualizada exitosamente.'];
}

function saveSetting($key, $value) {
    global $conn;

    $sql = "INSERT INTO system_settings (setting_key, setting_value, updated_at)
            VALUES (?, ?, CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $key, $value);
        $stmt->execute();
        $stmt->close();
    }
}

function clearSystemCache() {
    // Clear any cached files
    $cache_dir = __DIR__ . '/../cache/';
    if (is_dir($cache_dir)) {
        $files = glob($cache_dir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    // Log the action
    logAdminAction('clear_cache', 'system', 0, null, null);

    return ['success' => true, 'message' => 'Caché del sistema limpiado exitosamente.'];
}

function backupDatabase() {
    global $conn;

    $backup_file = __DIR__ . '/../backups/backup_' . date('Y-m-d_H-i-s') . '.sql';

    // Ensure backup directory exists
    $backup_dir = dirname($backup_file);
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }

    // For now, just create a simple backup file
    // In a production environment, you would use mysqldump or similar
    $backup_content = "-- Database Backup: " . date('Y-m-d H:i:s') . "\n";
    $backup_content .= "-- Tierras.mx Database\n\n";

    // Get all tables
    $tables_result = $conn->query("SHOW TABLES");
    while ($table_row = $tables_result->fetch_array()) {
        $table = $table_row[0];
        $backup_content .= "-- Table: $table\n";

        // Get table structure
        $create_result = $conn->query("SHOW CREATE TABLE $table");
        $create_row = $create_result->fetch_array();
        $backup_content .= $create_row[1] . ";\n\n";

        // Get table data
        $data_result = $conn->query("SELECT * FROM $table");
        if ($data_result->num_rows > 0) {
            while ($data_row = $data_result->fetch_assoc()) {
                $columns = array_keys($data_row);
                $values = array_map(function($value) use ($conn) {
                    return "'" . $conn->real_escape_string($value) . "'";
                }, array_values($data_row));

                $backup_content .= "INSERT INTO $table (" . implode(',', $columns) . ") VALUES (" . implode(',', $values) . ");\n";
            }
        }
        $backup_content .= "\n";
    }

    file_put_contents($backup_file, $backup_content);

    // Log the action
    logAdminAction('backup', 'database', 0, null, ['backup_file' => basename($backup_file)]);

    return ['success' => true, 'message' => 'Respaldo de base de datos creado exitosamente: ' . basename($backup_file)];
}

// Get system information
$systemInfo = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'database_version' => '',
    'disk_space' => disk_free_space('/') / 1024 / 1024 / 1024, // GB
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'upload_max_filesize' => ini_get('upload_max_filesize')
];

// Get database version
if ($result = $conn->query("SELECT VERSION() as version")) {
    $systemInfo['database_version'] = $result->fetch_assoc()['version'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --admin-primary: #667eea;
            --admin-secondary: #764ba2;
            --admin-success: #28a745;
            --admin-warning: #ffc107;
            --admin-danger: #dc3545;
            --admin-info: #17a2b8;
            --admin-dark: #343a40;
            --admin-light: #f8f9fa;
        }

        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .admin-header {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .admin-sidebar {
            background: white;
            min-height: calc(100vh - 76px);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 250px;
            left: 0;
            top: 76px;
            z-index: 1000;
            overflow-y: auto;
        }

        .admin-main {
            margin-left: 250px;
            padding: 2rem;
            min-height: calc(100vh - 76px);
        }

        .sidebar-nav .nav-link {
            color: #666;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            color: white;
        }

        .sidebar-nav .nav-link i {
            width: 20px;
            text-align: center;
        }

        .settings-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .settings-section h4 {
            color: var(--admin-primary);
            border-bottom: 2px solid var(--admin-light);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .form-floating > label {
            padding: 1rem 0.75rem;
        }

        .btn-admin {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            border: none;
            color: white;
        }

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .system-info-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 500;
            color: #666;
        }

        .info-value {
            color: var(--admin-primary);
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }

            .admin-sidebar.show {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: block !important;
            }
        }

        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--admin-primary);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h4 mb-0">
                        <i class="fas fa-cog me-2"></i>Configuración del Sistema
                    </h1>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-light" onclick="clearCache()">
                        <i class="fas fa-broom me-2"></i>Limpiar Caché
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar Toggle (Mobile) -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Admin Sidebar -->
    <nav class="admin-sidebar">
        <div class="sidebar-nav">
            <a href="index.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="users.php" class="nav-link">
                <i class="fas fa-users"></i>
                <span>Usuarios</span>
            </a>
            <a href="properties.php" class="nav-link">
                <i class="fas fa-home"></i>
                <span>Propiedades</span>
            </a>
            <a href="agents.php" class="nav-link">
                <i class="fas fa-user-tie"></i>
                <span>Agentes</span>
            </a>
            <a href="posts.php" class="nav-link">
                <i class="fas fa-newspaper"></i>
                <span>Contenido</span>
            </a>
            <a href="analytics.php" class="nav-link">
                <i class="fas fa-chart-bar"></i>
                <span>Analytics</span>
            </a>
            <a href="settings.php" class="nav-link active">
                <i class="fas fa-cog"></i>
                <span>Configuración</span>
            </a>
            <a href="audit.php" class="nav-link">
                <i class="fas fa-history"></i>
                <span>Auditoría</span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- General Settings -->
        <div class="settings-section">
            <h4>
                <i class="fas fa-globe me-2"></i>Configuración General
            </h4>
            <form method="POST">
                <input type="hidden" name="action" value="update_general">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                            <label for="site_name">Nombre del Sitio *</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email']); ?>" required>
                            <label for="contact_email">Email de Contacto *</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="support_email" name="support_email" value="<?php echo htmlspecialchars($settings['support_email']); ?>">
                            <label for="support_email">Email de Soporte</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($settings['admin_email']); ?>">
                            <label for="admin_email">Email del Administrador</label>
                        </div>
                    </div>
                </div>
                <div class="form-floating mb-3">
                    <textarea class="form-control" id="site_description" name="site_description" style="height: 80px;"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                    <label for="site_description">Descripción del Sitio</label>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="allow_registrations" name="allow_registrations" <?php echo $settings['allow_registrations'] === '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="allow_registrations">
                                Permitir nuevos registros
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="require_email_verification" name="require_email_verification" <?php echo $settings['require_email_verification'] === '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="require_email_verification">
                                Requerir verificación de email
                            </label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-admin">
                    <i class="fas fa-save me-2"></i>Guardar Configuración General
                </button>
            </form>
        </div>

        <!-- Email Settings -->
        <div class="settings-section">
            <h4>
                <i class="fas fa-envelope me-2"></i>Configuración de Email
            </h4>
            <form method="POST">
                <input type="hidden" name="action" value="update_email">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host']); ?>" placeholder="smtp.gmail.com">
                            <label for="smtp_host">SMTP Host</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port']); ?>">
                            <label for="smtp_port">Puerto</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                <option value="tls" <?php echo $settings['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                <option value="ssl" <?php echo $settings['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                <option value="none" <?php echo $settings['smtp_encryption'] === 'none' ? 'selected' : ''; ?>>Ninguno</option>
                            </select>
                            <label for="smtp_encryption">Encriptación</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="smtp_username" name="smtp_username" value="<?php echo htmlspecialchars($settings['smtp_username']); ?>">
                            <label for="smtp_username">Usuario SMTP</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="smtp_password" name="smtp_password" placeholder="Dejar vacío para mantener">
                            <label for="smtp_password">Contraseña SMTP</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-admin">
                    <i class="fas fa-save me-2"></i>Guardar Configuración de Email
                </button>
            </form>
        </div>

        <!-- Security Settings -->
        <div class="settings-section">
            <h4>
                <i class="fas fa-shield-alt me-2"></i>Configuración de Seguridad
            </h4>
            <form method="POST">
                <input type="hidden" name="action" value="update_security">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" id="session_timeout" name="session_timeout" value="<?php echo htmlspecialchars($settings['session_timeout']); ?>" min="300" max="86400">
                            <label for="session_timeout">Tiempo de Sesión (segundos)</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" id="max_login_attempts" name="max_login_attempts" value="<?php echo htmlspecialchars($settings['max_login_attempts']); ?>" min="1" max="20">
                            <label for="max_login_attempts">Máx. Intentos de Login</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" id="password_min_length" name="password_min_length" value="<?php echo htmlspecialchars($settings['password_min_length']); ?>" min="6" max="32">
                            <label for="password_min_length">Longitud Mínima de Contraseña</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-admin">
                    <i class="fas fa-save me-2"></i>Guardar Configuración de Seguridad
                </button>
            </form>
        </div>

        <!-- Property Settings -->
        <div class="settings-section">
            <h4>
                <i class="fas fa-home me-2"></i>Configuración de Propiedades
            </h4>
            <form method="POST">
                <input type="hidden" name="action" value="update_property">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" id="max_properties_per_user" name="max_properties_per_user" value="<?php echo htmlspecialchars($settings['max_properties_per_user']); ?>" min="1" max="100">
                            <label for="max_properties_per_user">Máx. Propiedades por Usuario</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" id="property_expiry_days" name="property_expiry_days" value="<?php echo htmlspecialchars($settings['property_expiry_days']); ?>" min="1" max="365">
                            <label for="property_expiry_days">Días para Expiración</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check mb-3" style="margin-top: 2rem;">
                            <input class="form-check-input" type="checkbox" id="auto_approve_properties" name="auto_approve_properties" <?php echo $settings['auto_approve_properties'] === '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="auto_approve_properties">
                                Auto-aprobar propiedades
                            </label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-admin">
                    <i class="fas fa-save me-2"></i>Guardar Configuración de Propiedades
                </button>
            </form>
        </div>

        <!-- Maintenance Settings -->
        <div class="settings-section">
            <h4>
                <i class="fas fa-tools me-2"></i>Modo de Mantenimiento
            </h4>
            <form method="POST">
                <input type="hidden" name="action" value="update_maintenance">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" <?php echo $settings['maintenance_mode'] === '1' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="maintenance_mode">
                        Activar modo de mantenimiento
                    </label>
                </div>
                <div class="form-floating mb-3">
                    <textarea class="form-control" id="maintenance_message" name="maintenance_message" style="height: 100px;"><?php echo htmlspecialchars($settings['maintenance_message']); ?></textarea>
                    <label for="maintenance_message">Mensaje de Mantenimiento</label>
                </div>
                <button type="submit" class="btn btn-admin">
                    <i class="fas fa-save me-2"></i>Guardar Configuración de Mantenimiento
                </button>
            </form>
        </div>

        <!-- System Information -->
        <div class="settings-section">
            <h4>
                <i class="fas fa-info-circle me-2"></i>Información del Sistema
            </h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="system-info-card">
                        <h6 class="mb-3">Servidor</h6>
                        <div class="info-item">
                            <span class="info-label">Versión PHP:</span>
                            <span class="info-value"><?php echo $systemInfo['php_version']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Servidor Web:</span>
                            <span class="info-value"><?php echo htmlspecialchars($systemInfo['server_software']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Versión MySQL:</span>
                            <span class="info-value"><?php echo $systemInfo['database_version']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Espacio en Disco:</span>
                            <span class="info-value"><?php echo number_format($systemInfo['disk_space'], 2); ?> GB</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="system-info-card">
                        <h6 class="mb-3">Configuración PHP</h6>
                        <div class="info-item">
                            <span class="info-label">Límite de Memoria:</span>
                            <span class="info-value"><?php echo $systemInfo['memory_limit']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tiempo Máx. Ejecución:</span>
                            <span class="info-value"><?php echo $systemInfo['max_execution_time']; ?>s</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tamaño Máx. Subida:</span>
                            <span class="info-value"><?php echo $systemInfo['upload_max_filesize']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Estado del Sistema:</span>
                            <span class="info-value text-success">Operativo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Actions -->
        <div class="settings-section">
            <h4>
                <i class="fas fa-tasks me-2"></i>Acciones del Sistema
            </h4>
            <div class="row">
                <div class="col-md-4">
                    <button type="button" class="btn btn-warning w-100 mb-3" onclick="clearCache()">
                        <i class="fas fa-broom me-2"></i>Limpiar Caché
                    </button>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-info w-100 mb-3" onclick="backupDatabase()">
                        <i class="fas fa-database me-2"></i>Respaldar Base de Datos
                    </button>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-danger w-100 mb-3" onclick="resetSettings()">
                        <i class="fas fa-undo me-2"></i>Restaurar Configuración
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.admin-sidebar');
            sidebar.classList.toggle('show');
        }

        function clearCache() {
            if (confirm('¿Estás seguro de que quieres limpiar el caché del sistema?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="clear_cache">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function backupDatabase() {
            if (confirm('¿Estás seguro de que quieres crear un respaldo de la base de datos?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="backup_database">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function resetSettings() {
            if (confirm('¿Estás seguro de que quieres restaurar la configuración a los valores predeterminados? Esta acción no se puede deshacer.')) {
                alert('Función de restauración próximamente disponible');
            }
        }

        // Auto-save settings on change (optional enhancement)
        document.addEventListener('change', function(e) {
            if (e.target.closest('.settings-section')) {
                // Could implement auto-save here
                console.log('Setting changed:', e.target.name);
            }
        });
    </script>
</body>
</html>