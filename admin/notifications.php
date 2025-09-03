<?php
require_once 'auth.php';
requireAdminLogin();

$pageTitle = 'Notifications - Tierras.mx Admin';
$admin = getCurrentAdmin();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_notification':
                $message = createAdminNotification($_POST);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'mark_read':
                $message = markNotificationRead($_POST['notification_id']);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'mark_all_read':
                $message = markAllNotificationsRead();
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'delete_notification':
                $message = deleteNotification($_POST['notification_id']);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'update_settings':
                $message = updateNotificationSettings($_POST);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
        }
    }
}

// Get notifications with pagination and filtering
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$filter = $_GET['filter'] ?? '';
$status = $_GET['status'] ?? '';

$notifications = getAdminNotifications($filter, $status, $perPage, $offset);
$totalNotifications = getTotalAdminNotifications($filter, $status);
$totalPages = ceil($totalNotifications / $perPage);

// Get notification statistics
$notificationStats = getNotificationStats();

// Get notification settings
$notificationSettings = getNotificationSettings();

function getAdminNotifications($filter = '', $status = '', $limit = 20, $offset = 0) {
    global $conn;

    $where = [];
    $params = [];
    $types = '';

    if ($filter) {
        $where[] = "an.type = ?";
        $params[] = $filter;
        $types .= 's';
    }

    if ($status === 'read') {
        $where[] = "an.is_read = 1";
    } elseif ($status === 'unread') {
        $where[] = "an.is_read = 0";
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT an.*, u.username as related_user
            FROM admin_notifications an
            LEFT JOIN users u ON an.related_user_id = u.id
            $whereClause
            ORDER BY an.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $notifications = [];
    if ($stmt = $conn->prepare($sql)) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        $stmt->close();
    }

    return $notifications;
}

function getTotalAdminNotifications($filter = '', $status = '') {
    global $conn;

    $where = [];
    $params = [];
    $types = '';

    if ($filter) {
        $where[] = "type = ?";
        $params[] = $filter;
        $types .= 's';
    }

    if ($status === 'read') {
        $where[] = "is_read = 1";
    } elseif ($status === 'unread') {
        $where[] = "is_read = 0";
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT COUNT(*) as total FROM admin_notifications $whereClause";

    if ($stmt = $conn->prepare($sql)) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['total'];
    }

    return 0;
}

function getNotificationStats() {
    global $conn;

    $stats = [
        'total' => 0,
        'unread' => 0,
        'today' => 0,
        'this_week' => 0,
        'by_type' => []
    ];

    // Total notifications
    $sql = "SELECT COUNT(*) as total FROM admin_notifications";
    if ($result = $conn->query($sql)) {
        $stats['total'] = $result->fetch_assoc()['total'];
    }

    // Unread notifications
    $sql = "SELECT COUNT(*) as total FROM admin_notifications WHERE is_read = 0";
    if ($result = $conn->query($sql)) {
        $stats['unread'] = $result->fetch_assoc()['total'];
    }

    // Today's notifications
    $sql = "SELECT COUNT(*) as total FROM admin_notifications WHERE DATE(created_at) = CURDATE()";
    if ($result = $conn->query($sql)) {
        $stats['today'] = $result->fetch_assoc()['total'];
    }

    // This week's notifications
    $sql = "SELECT COUNT(*) as total FROM admin_notifications WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    if ($result = $conn->query($sql)) {
        $stats['this_week'] = $result->fetch_assoc()['total'];
    }

    // Notifications by type
    $sql = "SELECT type, COUNT(*) as count FROM admin_notifications GROUP BY type ORDER BY count DESC";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $stats['by_type'][] = $row;
        }
    }

    return $stats;
}

function getNotificationSettings() {
    global $conn;

    $settings = [
        'email_notifications' => '1',
        'property_submissions' => '1',
        'user_registrations' => '1',
        'agent_verifications' => '1',
        'system_alerts' => '1',
        'security_alerts' => '1'
    ];

    // Try to load settings from database
    $sql = "SELECT setting_key, setting_value FROM admin_notification_settings WHERE admin_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $_SESSION['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        $stmt->close();
    }

    return $settings;
}

function createAdminNotification($data) {
    global $conn;

    if (empty($data['title']) || empty($data['message'])) {
        return ['success' => false, 'message' => 'Título y mensaje son obligatorios.'];
    }

    $sql = "INSERT INTO admin_notifications (title, message, type, priority, related_user_id, related_entity_type, related_entity_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $related_user_id = !empty($data['related_user_id']) ? $data['related_user_id'] : null;
    $related_entity_type = !empty($data['related_entity_type']) ? $data['related_entity_type'] : null;
    $related_entity_id = !empty($data['related_entity_id']) ? $data['related_entity_id'] : null;

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssiss", $data['title'], $data['message'], $data['type'],
                         $data['priority'], $related_user_id, $related_entity_type, $related_entity_id);

        if ($stmt->execute()) {
            $stmt->close();

            // Log the action
            logAdminAction('create', 'notification', $stmt->insert_id, null, $data);

            return ['success' => true, 'message' => 'Notificación creada exitosamente.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al crear la notificación.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function markNotificationRead($notification_id) {
    global $conn;

    $sql = "UPDATE admin_notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $notification_id);

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Notificación marcada como leída.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al marcar la notificación como leída.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function markAllNotificationsRead() {
    global $conn;

    $sql = "UPDATE admin_notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE is_read = 0";

    if ($stmt = $conn->prepare($sql)) {
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            return ['success' => true, 'message' => "$affected_rows notificaciones marcadas como leídas."];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al marcar las notificaciones como leídas.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function deleteNotification($notification_id) {
    global $conn;

    // Get notification data before deletion
    $notification_data = [];
    $sql = "SELECT * FROM admin_notifications WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $notification_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $notification_data = $result->fetch_assoc();
        }
        $stmt->close();
    }

    $sql = "DELETE FROM admin_notifications WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $notification_id);

        if ($stmt->execute()) {
            $stmt->close();

            // Log the action
            logAdminAction('delete', 'notification', $notification_id, $notification_data, null);

            return ['success' => true, 'message' => 'Notificación eliminada exitosamente.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al eliminar la notificación.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function updateNotificationSettings($data) {
    global $conn;

    $settings = [
        'email_notifications' => isset($data['email_notifications']) ? '1' : '0',
        'property_submissions' => isset($data['property_submissions']) ? '1' : '0',
        'user_registrations' => isset($data['user_registrations']) ? '1' : '0',
        'agent_verifications' => isset($data['agent_verifications']) ? '1' : '0',
        'system_alerts' => isset($data['system_alerts']) ? '1' : '0',
        'security_alerts' => isset($data['security_alerts']) ? '1' : '0'
    ];

    foreach ($settings as $key => $value) {
        $sql = "INSERT INTO admin_notification_settings (admin_id, setting_key, setting_value)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iss", $_SESSION['id'], $key, $value);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Log the action
    logAdminAction('update', 'notification_settings', 0, null, $settings);

    return ['success' => true, 'message' => 'Configuración de notificaciones actualizada exitosamente.'];
}

// Auto-generate system notifications (this would typically be called by cron jobs or triggers)
function generateSystemNotifications() {
    global $conn;

    // Check for pending property approvals
    $sql = "SELECT COUNT(*) as count FROM properties WHERE approval_status = 'pending'";
    if ($result = $conn->query($sql)) {
        $pending_count = $result->fetch_assoc()['count'];
        if ($pending_count > 0) {
            createSystemNotification(
                'Propiedades Pendientes de Aprobación',
                "Hay $pending_count propiedades esperando aprobación.",
                'property',
                'medium',
                null,
                'property',
                null
            );
        }
    }

    // Check for new user registrations today
    $sql = "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()";
    if ($result = $conn->query($sql)) {
        $new_users_count = $result->fetch_assoc()['count'];
        if ($new_users_count > 0) {
            createSystemNotification(
                'Nuevos Usuarios Registrados',
                "$new_users_count nuevos usuarios se registraron hoy.",
                'user',
                'low',
                null,
                'user',
                null
            );
        }
    }

    // Check for failed login attempts
    // This would require a failed login attempts table
    // For now, we'll skip this
}

function createSystemNotification($title, $message, $type, $priority, $related_user_id, $related_entity_type, $related_entity_id) {
    global $conn;

    // Check if similar notification already exists today
    $sql = "SELECT id FROM admin_notifications
            WHERE title = ? AND DATE(created_at) = CURDATE()";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $title);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $stmt->close();
            return; // Don't create duplicate notifications
        }
        $stmt->close();
    }

    $sql = "INSERT INTO admin_notifications (title, message, type, priority, related_user_id, related_entity_type, related_entity_id, is_system)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssiss", $title, $message, $type, $priority, $related_user_id, $related_entity_type, $related_entity_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Generate some sample notifications for demo
if (isset($_GET['generate_sample']) && $_GET['generate_sample'] === 'true') {
    generateSystemNotifications();
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

        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid var(--admin-primary);
        }

        .notification-item {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-left: 4px solid var(--admin-info);
            transition: all 0.3s;
        }

        .notification-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .notification-item.unread {
            border-left-color: var(--admin-warning);
            background: linear-gradient(90deg, rgba(255, 193, 7, 0.1) 0%, white 50%);
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .notification-title {
            font-weight: 600;
            color: var(--admin-primary);
            margin: 0;
        }

        .notification-meta {
            font-size: 0.875rem;
            color: #666;
        }

        .notification-message {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .notification-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .priority-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .priority-high { background: #f8d7da; color: #721c24; }
        .priority-medium { background: #fff3cd; color: #856404; }
        .priority-low { background: #d1ecf1; color: #0c5460; }

        .type-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .type-user { background: #e7f3ff; color: #0066cc; }
        .type-property { background: #f0f9ff; color: #0284c7; }
        .type-system { background: #f3e8ff; color: #7c3aed; }
        .type-security { background: #fef2f2; color: #dc2626; }

        .btn-admin {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            border: none;
            color: white;
        }

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
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
                        <i class="fas fa-bell me-2"></i>Centro de Notificaciones
                    </h1>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-light" onclick="markAllRead()">
                            <i class="fas fa-check-double me-2"></i>Marcar Todas como Leídas
                        </button>
                        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createNotificationModal">
                            <i class="fas fa-plus-circle me-2"></i>Crear Notificación
                        </button>
                    </div>
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
            <a href="notifications.php" class="nav-link active">
                <i class="fas fa-bell"></i>
                <span>Notificaciones</span>
            </a>
            <a href="settings.php" class="nav-link">
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

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($notificationStats['total']); ?></div>
                            <div class="text-muted">Total Notificaciones</div>
                        </div>
                        <i class="fas fa-bell fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($notificationStats['unread']); ?></div>
                            <div class="text-muted">No Leídas</div>
                        </div>
                        <i class="fas fa-envelope fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($notificationStats['today']); ?></div>
                            <div class="text-muted">Hoy</div>
                        </div>
                        <i class="fas fa-calendar-day fa-2x text-success"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($notificationStats['this_week']); ?></div>
                            <div class="text-muted">Esta Semana</div>
                        </div>
                        <i class="fas fa-calendar-week fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="filter">
                            <option value="">Todos los tipos</option>
                            <option value="user" <?php echo $filter === 'user' ? 'selected' : ''; ?>>Usuario</option>
                            <option value="property" <?php echo $filter === 'property' ? 'selected' : ''; ?>>Propiedad</option>
                            <option value="system" <?php echo $filter === 'system' ? 'selected' : ''; ?>>Sistema</option>
                            <option value="security" <?php echo $filter === 'security' ? 'selected' : ''; ?>>Seguridad</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="status">
                            <option value="">Todos</option>
                            <option value="unread" <?php echo $status === 'unread' ? 'selected' : ''; ?>>No Leídas</option>
                            <option value="read" <?php echo $status === 'read' ? 'selected' : ''; ?>>Leídas</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-admin">
                                <i class="fas fa-search me-2"></i>Filtrar
                            </button>
                            <a href="notifications.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Notificaciones</h5>
                    <a href="?generate_sample=true" class="btn btn-sm btn-info">
                        <i class="fas fa-magic me-2"></i>Generar Notificaciones de Ejemplo
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay notificaciones</h5>
                        <p class="text-muted">Las notificaciones aparecerán aquí cuando haya actividad en el sistema.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                            <div class="notification-header">
                                <div>
                                    <h6 class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                    <div class="notification-meta">
                                        <span class="type-badge type-<?php echo $notification['type']; ?>">
                                            <?php echo ucfirst($notification['type']); ?>
                                        </span>
                                        <span class="priority-badge priority-<?php echo $notification['priority']; ?>">
                                            <?php echo ucfirst($notification['priority']); ?>
                                        </span>
                                        <span><?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?></span>
                                        <?php if ($notification['related_user']): ?>
                                            <span>• Usuario: <?php echo htmlspecialchars($notification['related_user']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="notification-actions">
                                    <?php if (!$notification['is_read']): ?>
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="markRead(<?php echo $notification['id']; ?>)">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteNotification(<?php echo $notification['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="notification-message">
                                <?php echo nl2br(htmlspecialchars($notification['message'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Notifications pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter=<?php echo urlencode($filter); ?>&status=<?php echo urlencode($status); ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo urlencode($filter); ?>&status=<?php echo urlencode($status); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter=<?php echo urlencode($filter); ?>&status=<?php echo urlencode($status); ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

        <!-- Notification Types Summary -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-pie me-2"></i>Notificaciones por Tipo
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($notificationStats['by_type'] as $type_stat): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo ucfirst($type_stat['type']); ?></span>
                                <span class="badge bg-primary"><?php echo $type_stat['count']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-cog me-2"></i>Configuración de Notificaciones
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_settings">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" <?php echo $notificationSettings['email_notifications'] === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="email_notifications">
                                        Notificaciones por email
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="property_submissions" name="property_submissions" <?php echo $notificationSettings['property_submissions'] === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="property_submissions">
                                        Nuevas propiedades para aprobar
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="user_registrations" name="user_registrations" <?php echo $notificationSettings['user_registrations'] === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="user_registrations">
                                        Nuevos registros de usuarios
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agent_verifications" name="agent_verifications" <?php echo $notificationSettings['agent_verifications'] === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="agent_verifications">
                                        Solicitudes de verificación de agentes
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="system_alerts" name="system_alerts" <?php echo $notificationSettings['system_alerts'] === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="system_alerts">
                                        Alertas del sistema
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="security_alerts" name="security_alerts" <?php echo $notificationSettings['security_alerts'] === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="security_alerts">
                                        Alertas de seguridad
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-admin btn-sm">
                                <i class="fas fa-save me-2"></i>Guardar Configuración
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Create Notification Modal -->
    <div class="modal fade" id="createNotificationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Crear Nueva Notificación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_notification">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="notification_title" name="title" required>
                                    <label for="notification_title">Título *</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="notification_priority" name="priority" required>
                                        <option value="low">Baja</option>
                                        <option value="medium">Media</option>
                                        <option value="high">Alta</option>
                                    </select>
                                    <label for="notification_priority">Prioridad</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="notification_message" name="message" style="height: 100px;" required></textarea>
                            <label for="notification_message">Mensaje *</label>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="notification_type" name="type" required>
                                        <option value="system">Sistema</option>
                                        <option value="user">Usuario</option>
                                        <option value="property">Propiedad</option>
                                        <option value="security">Seguridad</option>
                                    </select>
                                    <label for="notification_type">Tipo</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="related_user_id" name="related_user_id" placeholder="ID de usuario (opcional)">
                                    <label for="related_user_id">ID Usuario Relacionado</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="related_entity_type" name="related_entity_type" placeholder="Tipo de entidad (opcional)">
                                    <label for="related_entity_type">Tipo de Entidad</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="related_entity_id" name="related_entity_id" placeholder="ID de entidad (opcional)">
                                    <label for="related_entity_id">ID de Entidad</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-admin">Crear Notificación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.admin-sidebar');
            sidebar.classList.toggle('show');
        }

        function markRead(notificationId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="mark_read">
                <input type="hidden" name="notification_id" value="${notificationId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        function markAllRead() {
            if (confirm('¿Estás seguro de que quieres marcar todas las notificaciones como leídas?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="mark_all_read">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteNotification(notificationId) {
            if (confirm('¿Estás seguro de que quieres eliminar esta notificación?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_notification">
                    <input type="hidden" name="notification_id" value="${notificationId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>