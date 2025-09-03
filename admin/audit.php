<?php
require_once 'auth.php';
requireAdminLogin();

$pageTitle = 'Audit Log - Tierras.mx Admin';
$admin = getCurrentAdmin();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'clear_old_logs':
                $message = clearOldAuditLogs($_POST['days'] ?? 30);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'export_logs':
                exportAuditLogs($_POST);
                exit; // Exit after export
        }
    }
}

// Get audit logs with pagination and filtering
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

$filter = $_GET['filter'] ?? '';
$admin_filter = $_GET['admin_filter'] ?? '';
$entity_filter = $_GET['entity_filter'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$audit_logs = getAuditLogs($filter, $admin_filter, $entity_filter, $date_from, $date_to, $perPage, $offset);
$totalLogs = getTotalAuditLogs($filter, $admin_filter, $entity_filter, $date_from, $date_to);
$totalPages = ceil($totalLogs / $perPage);

// Get filter options
$admins = getAdminsForFilter();
$entities = getEntitiesForFilter();
$actions = getActionsForFilter();

function getAuditLogs($filter = '', $admin_filter = '', $entity_filter = '', $date_from = '', $date_to = '', $limit = 50, $offset = 0) {
    global $conn;

    $where = [];
    $params = [];
    $types = '';

    if ($filter) {
        $where[] = "aal.action LIKE ?";
        $params[] = "%$filter%";
        $types .= 's';
    }

    if ($admin_filter) {
        $where[] = "aal.admin_id = ?";
        $params[] = $admin_filter;
        $types .= 'i';
    }

    if ($entity_filter) {
        $where[] = "aal.entity_type = ?";
        $params[] = $entity_filter;
        $types .= 's';
    }

    if ($date_from) {
        $where[] = "DATE(aal.created_at) >= ?";
        $params[] = $date_from;
        $types .= 's';
    }

    if ($date_to) {
        $where[] = "DATE(aal.created_at) <= ?";
        $params[] = $date_to;
        $types .= 's';
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT aal.*, u.username as admin_username, u.first_name, u.last_name
            FROM admin_audit_log aal
            LEFT JOIN users u ON aal.admin_id = u.id
            $whereClause
            ORDER BY aal.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $logs = [];
    if ($stmt = $conn->prepare($sql)) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        $stmt->close();
    }

    return $logs;
}

function getTotalAuditLogs($filter = '', $admin_filter = '', $entity_filter = '', $date_from = '', $date_to = '') {
    global $conn;

    $where = [];
    $params = [];
    $types = '';

    if ($filter) {
        $where[] = "action LIKE ?";
        $params[] = "%$filter%";
        $types .= 's';
    }

    if ($admin_filter) {
        $where[] = "admin_id = ?";
        $params[] = $admin_filter;
        $types .= 'i';
    }

    if ($entity_filter) {
        $where[] = "entity_type = ?";
        $params[] = $entity_filter;
        $types .= 's';
    }

    if ($date_from) {
        $where[] = "DATE(created_at) >= ?";
        $params[] = $date_from;
        $types .= 's';
    }

    if ($date_to) {
        $where[] = "DATE(created_at) <= ?";
        $params[] = $date_to;
        $types .= 's';
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT COUNT(*) as total FROM admin_audit_log $whereClause";

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

function getAdminsForFilter() {
    global $conn;

    $admins = [];
    $sql = "SELECT DISTINCT u.id, u.username, u.first_name, u.last_name
            FROM admin_audit_log aal
            INNER JOIN users u ON aal.admin_id = u.id
            ORDER BY u.username";

    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }
    }

    return $admins;
}

function getEntitiesForFilter() {
    global $conn;

    $entities = [];
    $sql = "SELECT DISTINCT entity_type FROM admin_audit_log ORDER BY entity_type";

    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $entities[] = $row['entity_type'];
        }
    }

    return $entities;
}

function getActionsForFilter() {
    global $conn;

    $actions = [];
    $sql = "SELECT DISTINCT action FROM admin_audit_log ORDER BY action";

    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $actions[] = $row['action'];
        }
    }

    return $actions;
}

function clearOldAuditLogs($days) {
    global $conn;

    $sql = "DELETE FROM admin_audit_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $days);
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            $stmt->close();

            // Log this action
            logAdminAction('clear_logs', 'audit_log', 0, null, ['days' => $days, 'deleted_count' => $affected_rows]);

            return ['success' => true, 'message' => "$affected_rows registros de auditoría eliminados exitosamente."];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al eliminar registros antiguos.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function exportAuditLogs($data) {
    global $conn;

    $format = $data['export_format'] ?? 'csv';
    $date_from = $data['export_date_from'] ?? '';
    $date_to = $data['export_date_to'] ?? '';

    // Get logs for export
    $where = [];
    $params = [];
    $types = '';

    if ($date_from) {
        $where[] = "DATE(aal.created_at) >= ?";
        $params[] = $date_from;
        $types .= 's';
    }

    if ($date_to) {
        $where[] = "DATE(aal.created_at) <= ?";
        $params[] = $date_to;
        $types .= 's';
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT aal.*, u.username as admin_username
            FROM admin_audit_log aal
            LEFT JOIN users u ON aal.admin_id = u.id
            $whereClause
            ORDER BY aal.created_at DESC";

    $logs = [];
    if ($stmt = $conn->prepare($sql)) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        $stmt->close();
    }

    if ($format === 'csv') {
        exportAsCSV($logs);
    } elseif ($format === 'json') {
        exportAsJSON($logs);
    }
}

function exportAsCSV($logs) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="audit_logs_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');

    // CSV headers
    fputcsv($output, [
        'ID', 'Admin', 'Action', 'Entity Type', 'Entity ID',
        'Old Values', 'New Values', 'IP Address', 'User Agent', 'Created At'
    ]);

    // CSV data
    foreach ($logs as $log) {
        fputcsv($output, [
            $log['id'],
            $log['admin_username'] ?? 'Unknown',
            $log['action'],
            $log['entity_type'],
            $log['entity_id'] ?? '',
            $log['old_values'] ?? '',
            $log['new_values'] ?? '',
            $log['ip_address'] ?? '',
            substr($log['user_agent'] ?? '', 0, 100), // Truncate user agent
            $log['created_at']
        ]);
    }

    fclose($output);
}

function exportAsJSON($logs) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="audit_logs_' . date('Y-m-d') . '.json"');

    echo json_encode($logs, JSON_PRETTY_PRINT);
}

// Get audit statistics
$auditStats = getAuditStats();

function getAuditStats() {
    global $conn;

    $stats = [
        'total_logs' => 0,
        'today_logs' => 0,
        'week_logs' => 0,
        'month_logs' => 0,
        'top_actions' => [],
        'top_admins' => []
    ];

    // Total logs
    $sql = "SELECT COUNT(*) as total FROM admin_audit_log";
    if ($result = $conn->query($sql)) {
        $stats['total_logs'] = $result->fetch_assoc()['total'];
    }

    // Today's logs
    $sql = "SELECT COUNT(*) as total FROM admin_audit_log WHERE DATE(created_at) = CURDATE()";
    if ($result = $conn->query($sql)) {
        $stats['today_logs'] = $result->fetch_assoc()['total'];
    }

    // Week's logs
    $sql = "SELECT COUNT(*) as total FROM admin_audit_log WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    if ($result = $conn->query($sql)) {
        $stats['week_logs'] = $result->fetch_assoc()['total'];
    }

    // Month's logs
    $sql = "SELECT COUNT(*) as total FROM admin_audit_log WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    if ($result = $conn->query($sql)) {
        $stats['month_logs'] = $result->fetch_assoc()['total'];
    }

    // Top actions
    $sql = "SELECT action, COUNT(*) as count FROM admin_audit_log GROUP BY action ORDER BY count DESC LIMIT 5";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $stats['top_actions'][] = $row;
        }
    }

    // Top admins
    $sql = "SELECT u.username, COUNT(*) as count FROM admin_audit_log aal
            LEFT JOIN users u ON aal.admin_id = u.id
            GROUP BY aal.admin_id ORDER BY count DESC LIMIT 5";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $stats['top_admins'][] = $row;
        }
    }

    return $stats;
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
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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

        .audit-entry {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-left: 4px solid var(--admin-info);
        }

        .audit-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .audit-action {
            font-weight: 600;
            color: var(--admin-primary);
        }

        .audit-timestamp {
            color: #666;
            font-size: 0.875rem;
        }

        .audit-details {
            font-size: 0.9rem;
            color: #666;
        }

        .audit-changes {
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 4px;
            margin-top: 0.5rem;
            font-family: monospace;
            font-size: 0.8rem;
        }

        .table th {
            background: var(--admin-light);
            border-top: none;
            font-weight: 600;
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

        .action-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .action-create { background: #d1ecf1; color: #0c5460; }
        .action-update { background: #fff3cd; color: #856404; }
        .action-delete { background: #f8d7da; color: #721c24; }
        .action-approve { background: #d1ecf1; color: #0c5460; }
        .action-reject { background: #f8d7da; color: #721c24; }

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
                        <i class="fas fa-history me-2"></i>Registro de Auditoría
                    </h1>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="fas fa-download me-2"></i>Exportar
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
            <a href="settings.php" class="nav-link">
                <i class="fas fa-cog"></i>
                <span>Configuración</span>
            </a>
            <a href="audit.php" class="nav-link active">
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
                            <div class="h4 mb-0"><?php echo number_format($auditStats['total_logs']); ?></div>
                            <div class="text-muted">Total Registros</div>
                        </div>
                        <i class="fas fa-history fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($auditStats['today_logs']); ?></div>
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
                            <div class="h4 mb-0"><?php echo number_format($auditStats['week_logs']); ?></div>
                            <div class="text-muted">Esta Semana</div>
                        </div>
                        <i class="fas fa-calendar-week fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($auditStats['month_logs']); ?></div>
                            <div class="text-muted">Este Mes</div>
                        </div>
                        <i class="fas fa-calendar-alt fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Buscar Acción</label>
                        <input type="text" class="form-control" name="filter" value="<?php echo htmlspecialchars($filter); ?>" placeholder="Acción...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Administrador</label>
                        <select class="form-select" name="admin_filter">
                            <option value="">Todos</option>
                            <?php foreach ($admins as $admin_option): ?>
                                <option value="<?php echo $admin_option['id']; ?>" <?php echo $admin_filter == $admin_option['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($admin_option['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Entidad</label>
                        <select class="form-select" name="entity_filter">
                            <option value="">Todas</option>
                            <?php foreach ($entities as $entity): ?>
                                <option value="<?php echo $entity; ?>" <?php echo $entity_filter === $entity ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($entity); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Desde</label>
                        <input type="date" class="form-control" name="date_from" value="<?php echo $date_from; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hasta</label>
                        <input type="date" class="form-control" name="date_to" value="<?php echo $date_to; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-admin">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                            <a href="audit.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Audit Logs Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Registros de Auditoría</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                            <i class="fas fa-trash me-2"></i>Limpiar Antiguos
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="auditTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Administrador</th>
                                <th>Acción</th>
                                <th>Entidad</th>
                                <th>Detalles</th>
                                <th>Fecha/Hora</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($audit_logs as $log): ?>
                                <tr>
                                    <td><?php echo $log['id']; ?></td>
                                    <td><?php echo htmlspecialchars($log['admin_username'] ?: 'Sistema'); ?></td>
                                    <td>
                                        <span class="action-badge action-<?php echo strtolower($log['action']); ?>">
                                            <?php echo ucfirst($log['action']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo ucfirst($log['entity_type']); ?><?php echo $log['entity_id'] ? ' #' . $log['entity_id'] : ''; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info" onclick="showLogDetails(<?php echo $log['id']; ?>)">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($log['ip_address'] ?: 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Audit logs pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter=<?php echo urlencode($filter); ?>&admin_filter=<?php echo urlencode($admin_filter); ?>&entity_filter=<?php echo urlencode($entity_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo urlencode($filter); ?>&admin_filter=<?php echo urlencode($admin_filter); ?>&entity_filter=<?php echo urlencode($entity_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter=<?php echo urlencode($filter); ?>&admin_filter=<?php echo urlencode($admin_filter); ?>&entity_filter=<?php echo urlencode($entity_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

        <!-- Top Actions and Admins Summary -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Acciones Más Comunes
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($auditStats['top_actions'] as $action): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo ucfirst($action['action']); ?></span>
                                <span class="badge bg-primary"><?php echo $action['count']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-users me-2"></i>Administradores Más Activos
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($auditStats['top_admins'] as $admin_stat): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo htmlspecialchars($admin_stat['username'] ?: 'Sistema'); ?></span>
                                <span class="badge bg-success"><?php echo $admin_stat['count']; ?> acciones</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Clear Old Logs Modal -->
    <div class="modal fade" id="clearLogsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-trash me-2 text-warning"></i>Limpiar Registros Antiguos
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="clear_old_logs">
                        <p>¿Cuántos días de registros antiguos quieres eliminar?</p>
                        <div class="form-floating mb-3">
                            <select class="form-select" id="clear_days" name="days" required>
                                <option value="30">30 días</option>
                                <option value="60">60 días</option>
                                <option value="90">90 días</option>
                                <option value="180">180 días</option>
                                <option value="365">1 año</option>
                            </select>
                            <label for="clear_days">Período de retención</label>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Esta acción eliminará permanentemente los registros de auditoría antiguos y no se puede deshacer.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Eliminar Registros</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-download me-2"></i>Exportar Registros de Auditoría
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="export_logs">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="export_format" name="export_format" required>
                                        <option value="csv">CSV</option>
                                        <option value="json">JSON</option>
                                    </select>
                                    <label for="export_format">Formato de Exportación</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control" id="export_date_from" name="export_date_from">
                                    <label for="export_date_from">Desde (opcional)</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="export_date_to" name="export_date_to">
                            <label for="export_date_to">Hasta (opcional)</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-admin">Exportar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.admin-sidebar');
            sidebar.classList.toggle('show');
        }

        function showLogDetails(logId) {
            // This would typically fetch and display detailed log information
            alert('Función de detalles próximamente disponible. Log ID: ' + logId);
        }

        // Initialize DataTable
        $(document).ready(function() {
            $('#auditTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                },
                "pageLength": 25,
                "order": [[ 0, "desc" ]],
                "columnDefs": [
                    { "orderable": false, "targets": [4] }
                ]
            });
        });
    </script>
</body>
</html>