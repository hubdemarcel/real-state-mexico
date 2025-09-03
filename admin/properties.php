<?php
require_once 'auth.php';
requireAdminLogin();

$pageTitle = 'Property Management - Tierras.mx Admin';
$admin = getCurrentAdmin();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'approve':
                $message = approveProperty($_POST['property_id'], $_POST['notes'] ?? '');
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'reject':
                $message = rejectProperty($_POST['property_id'], $_POST['rejection_reason'], $_POST['notes'] ?? '');
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'update':
                $message = updateProperty($_POST);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'bulk_approve':
                $message = bulkApproveProperties($_POST['property_ids'] ?? []);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'bulk_reject':
                $message = bulkRejectProperties($_POST['property_ids'] ?? [], $_POST['bulk_rejection_reason'] ?? '');
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
        }
    }
}

// Get properties with pagination and filtering
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$filter = $_GET['filter'] ?? '';
$search = $_GET['search'] ?? '';

$properties = getProperties($filter, $search, $perPage, $offset);
$totalProperties = getTotalProperties($filter, $search);
$totalPages = ceil($totalProperties / $perPage);

// Get property statistics
$propertyStats = getPropertyStats();

function getProperties($filter = '', $search = '', $limit = 20, $offset = 0) {
    global $conn;

    $where = [];
    $params = [];
    $types = '';

    if ($filter) {
        switch ($filter) {
            case 'pending':
                $where[] = "p.approval_status = 'pending'";
                break;
            case 'approved':
                $where[] = "p.approval_status = 'approved'";
                break;
            case 'rejected':
                $where[] = "p.approval_status = 'rejected'";
                break;
        }
    }

    if ($search) {
        $where[] = "(p.title LIKE ? OR p.location LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ssss';
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT p.*, u.username as agent_username, u.email as agent_email,
                   CONCAT(u.first_name, ' ', u.last_name) as agent_name
            FROM properties p
            LEFT JOIN users u ON p.agent_id = u.id
            $whereClause
            ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $properties = [];
    if ($stmt = $conn->prepare($sql)) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }
        $stmt->close();
    }

    return $properties;
}

function getTotalProperties($filter = '', $search = '') {
    global $conn;

    $where = [];
    $params = [];
    $types = '';

    if ($filter) {
        switch ($filter) {
            case 'pending':
                $where[] = "approval_status = 'pending'";
                break;
            case 'approved':
                $where[] = "approval_status = 'approved'";
                break;
            case 'rejected':
                $where[] = "approval_status = 'rejected'";
                break;
        }
    }

    if ($search) {
        $where[] = "(title LIKE ? OR location LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT COUNT(*) as total FROM properties $whereClause";

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

function getPropertyStats() {
    global $conn;

    $stats = [
        'total' => 0,
        'pending' => 0,
        'approved' => 0,
        'rejected' => 0
    ];

    $sql = "SELECT approval_status, COUNT(*) as count FROM properties GROUP BY approval_status";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $stats['total'] += $row['count'];
            $stats[$row['approval_status']] = $row['count'];
        }
    }

    return $stats;
}

function approveProperty($property_id, $notes = '') {
    global $conn;

    // Get current property data
    $current_data = [];
    $sql = "SELECT * FROM properties WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $property_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $current_data = $result->fetch_assoc();
        }
        $stmt->close();
    }

    // Update property
    $sql = "UPDATE properties SET
            approval_status = 'approved',
            approved_by = ?,
            approved_at = CURRENT_TIMESTAMP,
            admin_notes = ?
            WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $admin_id = $_SESSION['id'];
        $stmt->bind_param("isi", $admin_id, $notes, $property_id);

        if ($stmt->execute()) {
            $stmt->close();

            // Log the action
            logAdminAction('approve', 'property', $property_id, $current_data,
                          ['approval_status' => 'approved', 'admin_notes' => $notes]);

            return ['success' => true, 'message' => 'Propiedad aprobada exitosamente.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al aprobar la propiedad.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function rejectProperty($property_id, $rejection_reason, $notes = '') {
    global $conn;

    // Get current property data
    $current_data = [];
    $sql = "SELECT * FROM properties WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $property_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $current_data = $result->fetch_assoc();
        }
        $stmt->close();
    }

    // Update property
    $sql = "UPDATE properties SET
            approval_status = 'rejected',
            approved_by = ?,
            approved_at = CURRENT_TIMESTAMP,
            rejection_reason = ?,
            admin_notes = ?
            WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $admin_id = $_SESSION['id'];
        $stmt->bind_param("issi", $admin_id, $rejection_reason, $notes, $property_id);

        if ($stmt->execute()) {
            $stmt->close();

            // Log the action
            logAdminAction('reject', 'property', $property_id, $current_data,
                          ['approval_status' => 'rejected', 'rejection_reason' => $rejection_reason, 'admin_notes' => $notes]);

            return ['success' => true, 'message' => 'Propiedad rechazada exitosamente.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al rechazar la propiedad.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function updateProperty($data) {
    global $conn;

    if (empty($data['property_id'])) {
        return ['success' => false, 'message' => 'ID de propiedad requerido.'];
    }

    // Get current property data
    $current_data = [];
    $sql = "SELECT * FROM properties WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $data['property_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $current_data = $result->fetch_assoc();
        }
        $stmt->close();
    }

    // Build update query
    $updates = [];
    $params = [];
    $types = '';

    if (!empty($data['title'])) {
        $updates[] = "title = ?";
        $params[] = $data['title'];
        $types .= 's';
    }

    if (!empty($data['description'])) {
        $updates[] = "description = ?";
        $params[] = $data['description'];
        $types .= 's';
    }

    if (!empty($data['price'])) {
        $updates[] = "price = ?";
        $params[] = $data['price'];
        $types .= 'd';
    }

    if (!empty($data['location'])) {
        $updates[] = "location = ?";
        $params[] = $data['location'];
        $types .= 's';
    }

    if (!empty($data['property_type'])) {
        $updates[] = "property_type = ?";
        $params[] = $data['property_type'];
        $types .= 's';
    }

    if (isset($data['bedrooms'])) {
        $updates[] = "bedrooms = ?";
        $params[] = $data['bedrooms'];
        $types .= 'i';
    }

    if (!empty($data['bathrooms'])) {
        $updates[] = "bathrooms = ?";
        $params[] = $data['bathrooms'];
        $types .= 'd';
    }

    if (!empty($data['admin_notes'])) {
        $updates[] = "admin_notes = ?";
        $params[] = $data['admin_notes'];
        $types .= 's';
    }

    if (empty($updates)) {
        return ['success' => false, 'message' => 'No hay cambios para actualizar.'];
    }

    $updates[] = "updated_at = CURRENT_TIMESTAMP";
    $params[] = $data['property_id'];
    $types .= 'i';

    $sql = "UPDATE properties SET " . implode(', ', $updates) . " WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $stmt->close();

            // Log the action
            logAdminAction('update', 'property', $data['property_id'], $current_data, $data);

            return ['success' => true, 'message' => 'Propiedad actualizada exitosamente.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al actualizar la propiedad.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function bulkApproveProperties($property_ids) {
    if (empty($property_ids)) {
        return ['success' => false, 'message' => 'No se seleccionaron propiedades.'];
    }

    $success_count = 0;
    $error_count = 0;

    foreach ($property_ids as $property_id) {
        $result = approveProperty($property_id);
        if ($result['success']) {
            $success_count++;
        } else {
            $error_count++;
        }
    }

    if ($success_count > 0) {
        return ['success' => true, 'message' => "$success_count propiedad(es) aprobada(s) exitosamente." . ($error_count > 0 ? " $error_count error(es)." : "")];
    } else {
        return ['success' => false, 'message' => 'No se pudo aprobar ninguna propiedad.'];
    }
}

function bulkRejectProperties($property_ids, $rejection_reason) {
    if (empty($property_ids)) {
        return ['success' => false, 'message' => 'No se seleccionaron propiedades.'];
    }

    if (empty($rejection_reason)) {
        return ['success' => false, 'message' => 'Se requiere una razón de rechazo.'];
    }

    $success_count = 0;
    $error_count = 0;

    foreach ($property_ids as $property_id) {
        $result = rejectProperty($property_id, $rejection_reason);
        if ($result['success']) {
            $success_count++;
        } else {
            $error_count++;
        }
    }

    if ($success_count > 0) {
        return ['success' => true, 'message' => "$success_count propiedad(es) rechazada(s) exitosamente." . ($error_count > 0 ? " $error_count error(es)." : "")];
    } else {
        return ['success' => false, 'message' => 'No se pudo rechazar ninguna propiedad.'];
    }
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

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d1ecf1; color: #0c5460; }
        .status-rejected { background: #f8d7da; color: #721c24; }

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

        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .property-image {
            width: 60px;
            height: 45px;
            object-fit: cover;
            border-radius: 4px;
        }

        .action-buttons {
            display: flex;
            gap: 0.25rem;
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
                        <i class="fas fa-home me-2"></i>Gestión de Propiedades
                    </h1>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-light" onclick="bulkApprove()">
                            <i class="fas fa-check-circle me-2"></i>Aprobar
                        </button>
                        <button type="button" class="btn btn-light" onclick="bulkReject()">
                            <i class="fas fa-times-circle me-2"></i>Rechazar
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
            <a href="properties.php" class="nav-link active">
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
                            <div class="h4 mb-0"><?php echo number_format($propertyStats['total']); ?></div>
                            <div class="text-muted">Total Propiedades</div>
                        </div>
                        <i class="fas fa-home fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($propertyStats['pending']); ?></div>
                            <div class="text-muted">Pendientes</div>
                        </div>
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($propertyStats['approved']); ?></div>
                            <div class="text-muted">Aprobadas</div>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($propertyStats['rejected']); ?></div>
                            <div class="text-muted">Rechazadas</div>
                        </div>
                        <i class="fas fa-times-circle fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Buscar</label>
                        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Título, ubicación, agente...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="filter">
                            <option value="">Todas</option>
                            <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Pendientes</option>
                            <option value="approved" <?php echo $filter === 'approved' ? 'selected' : ''; ?>>Aprobadas</option>
                            <option value="rejected" <?php echo $filter === 'rejected' ? 'selected' : ''; ?>>Rechazadas</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-admin w-100">
                            <i class="fas fa-search me-2"></i>Buscar
                        </button>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <a href="properties.php" class="btn btn-secondary w-100">
                            <i class="fas fa-times me-2"></i>Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Properties Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Lista de Propiedades</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-success" onclick="bulkApprove()" id="bulkApproveBtn" style="display: none;">
                            <i class="fas fa-check-circle me-2"></i>Aprobar Seleccionadas
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="bulkReject()" id="bulkRejectBtn" style="display: none;">
                            <i class="fas fa-times-circle me-2"></i>Rechazar Seleccionadas
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form id="bulkActionForm" method="POST">
                    <input type="hidden" name="action" value="">
                    <div class="table-responsive">
                        <table class="table table-hover" id="propertiesTable">
                            <thead>
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>ID</th>
                                    <th>Imagen</th>
                                    <th>Título</th>
                                    <th>Ubicación</th>
                                    <th>Precio</th>
                                    <th>Agente</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($properties as $property): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input property-checkbox" name="property_ids[]" value="<?php echo $property['id']; ?>">
                                        </td>
                                        <td><?php echo $property['id']; ?></td>
                                        <td>
                                            <?php if ($property['image_url']): ?>
                                                <img src="<?php echo htmlspecialchars($property['image_url']); ?>" alt="Property" class="property-image">
                                            <?php else: ?>
                                                <div class="property-image bg-light d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-home text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars(substr($property['title'], 0, 30)); ?><?php echo strlen($property['title']) > 30 ? '...' : ''; ?></td>
                                        <td><?php echo htmlspecialchars($property['location']); ?></td>
                                        <td>$<?php echo number_format($property['price'], 0); ?> MXN</td>
                                        <td><?php echo htmlspecialchars($property['agent_name'] ?: $property['agent_username'] ?: 'Sin agente'); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $property['approval_status']; ?>">
                                                <?php
                                                switch($property['approval_status']) {
                                                    case 'pending': echo 'Pendiente'; break;
                                                    case 'approved': echo 'Aprobada'; break;
                                                    case 'rejected': echo 'Rechazada'; break;
                                                    default: echo 'Desconocido'; break;
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($property['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewProperty(<?php echo $property['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editProperty(<?php echo $property['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($property['approval_status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="approveProperty(<?php echo $property['id']; ?>, '<?php echo htmlspecialchars($property['title']); ?>')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="rejectProperty(<?php echo $property['id']; ?>, '<?php echo htmlspecialchars($property['title']); ?>')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Property pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter=<?php echo urlencode($filter); ?>&search=<?php echo urlencode($search); ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo urlencode($filter); ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter=<?php echo urlencode($filter); ?>&search=<?php echo urlencode($search); ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </main>

    <!-- Approve Property Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2 text-success"></i>Aprobar Propiedad
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="property_id" id="approve_property_id">
                        <p>¿Estás seguro de que quieres aprobar la propiedad <strong id="approve_property_title"></strong>?</p>
                        <div class="form-floating">
                            <textarea class="form-control" id="approve_notes" name="notes" style="height: 100px;" placeholder="Notas adicionales (opcional)"></textarea>
                            <label for="approve_notes">Notas del administrador</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Aprobar Propiedad</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Property Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle me-2 text-danger"></i>Rechazar Propiedad
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="property_id" id="reject_property_id">
                        <p>¿Estás seguro de que quieres rechazar la propiedad <strong id="reject_property_title"></strong>?</p>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="reject_reason" name="rejection_reason" style="height: 80px;" placeholder="Razón del rechazo" required></textarea>
                            <label for="reject_reason">Razón del rechazo *</label>
                        </div>
                        <div class="form-floating">
                            <textarea class="form-control" id="reject_notes" name="notes" style="height: 80px;" placeholder="Notas adicionales (opcional)"></textarea>
                            <label for="reject_notes">Notas del administrador</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Rechazar Propiedad</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Reject Modal -->
    <div class="modal fade" id="bulkRejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle me-2 text-danger"></i>Rechazar Propiedades Seleccionadas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="bulk_reject">
                        <p>¿Estás seguro de que quieres rechazar las propiedades seleccionadas?</p>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="bulk_rejection_reason" name="bulk_rejection_reason" style="height: 100px;" placeholder="Razón del rechazo para todas las propiedades" required></textarea>
                            <label for="bulk_rejection_reason">Razón del rechazo *</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Rechazar Propiedades</button>
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

        // Select all functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.property-checkbox');
            const bulkApproveBtn = document.getElementById('bulkApproveBtn');
            const bulkRejectBtn = document.getElementById('bulkRejectBtn');

            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });

            toggleBulkButtons();
        });

        // Individual checkbox change
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('property-checkbox')) {
                toggleBulkButtons();
                updateSelectAllState();
            }
        });

        function toggleBulkButtons() {
            const checkedBoxes = document.querySelectorAll('.property-checkbox:checked');
            const bulkApproveBtn = document.getElementById('bulkApproveBtn');
            const bulkRejectBtn = document.getElementById('bulkRejectBtn');

            if (checkedBoxes.length > 0) {
                bulkApproveBtn.style.display = 'inline-block';
                bulkRejectBtn.style.display = 'inline-block';
            } else {
                bulkApproveBtn.style.display = 'none';
                bulkRejectBtn.style.display = 'none';
            }
        }

        function updateSelectAllState() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.property-checkbox');
            const checkedBoxes = document.querySelectorAll('.property-checkbox:checked');

            selectAll.checked = checkboxes.length === checkedBoxes.length && checkboxes.length > 0;
            selectAll.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < checkboxes.length;
        }

        function approveProperty(propertyId, propertyTitle) {
            document.getElementById('approve_property_id').value = propertyId;
            document.getElementById('approve_property_title').textContent = propertyTitle;
            document.getElementById('approve_notes').value = '';

            new bootstrap.Modal(document.getElementById('approveModal')).show();
        }

        function rejectProperty(propertyId, propertyTitle) {
            document.getElementById('reject_property_id').value = propertyId;
            document.getElementById('reject_property_title').textContent = propertyTitle;
            document.getElementById('reject_reason').value = '';
            document.getElementById('reject_notes').value = '';

            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }

        function bulkApprove() {
            const checkedBoxes = document.querySelectorAll('.property-checkbox:checked');

            if (checkedBoxes.length === 0) {
                alert('Por favor, selecciona al menos una propiedad.');
                return;
            }

            if (confirm(`¿Estás seguro de que quieres aprobar ${checkedBoxes.length} propiedad(es)?`)) {
                const form = document.getElementById('bulkActionForm');
                form.querySelector('input[name="action"]').value = 'bulk_approve';
                form.submit();
            }
        }

        function bulkReject() {
            const checkedBoxes = document.querySelectorAll('.property-checkbox:checked');

            if (checkedBoxes.length === 0) {
                alert('Por favor, selecciona al menos una propiedad.');
                return;
            }

            // Show bulk reject modal
            new bootstrap.Modal(document.getElementById('bulkRejectModal')).show();
        }

        function viewProperty(propertyId) {
            window.open(`../property_detail.php?id=${propertyId}`, '_blank');
        }

        function editProperty(propertyId) {
            // This would open an edit modal or redirect to edit page
            alert('Función de edición próximamente disponible');
        }

        // Initialize DataTable
        $(document).ready(function() {
            $('#propertiesTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                },
                "pageLength": 25,
                "order": [[ 1, "desc" ]],
                "columnDefs": [
                    { "orderable": false, "targets": [0, 9] }
                ]
            });
        });
    </script>
</body>
</html>