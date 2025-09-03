<?php
require_once 'auth.php';
requireAdminLogin();

$pageTitle = 'Agent Management - Tierras.mx Admin';
$admin = getCurrentAdmin();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'verify':
                $message = verifyAgent($_POST['agent_id'], $_POST['notes'] ?? '');
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'unverify':
                $message = unverifyAgent($_POST['agent_id'], $_POST['notes'] ?? '');
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'update':
                $message = updateAgent($_POST);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'bulk_verify':
                $message = bulkVerifyAgents($_POST['agent_ids'] ?? []);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'bulk_unverify':
                $message = bulkUnverifyAgents($_POST['agent_ids'] ?? []);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
        }
    }
}

// Get agents with pagination and filtering
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$filter = $_GET['filter'] ?? '';
$search = $_GET['search'] ?? '';

$agents = getAgents($filter, $search, $perPage, $offset);
$totalAgents = getTotalAgents($filter, $search);
$totalPages = ceil($totalAgents / $perPage);

// Get agent statistics
$agentStats = getAgentStats();

function getAgents($filter = '', $search = '', $limit = 20, $offset = 0) {
    global $conn;

    $where = [];
    $params = [];
    $types = '';

    if ($filter) {
        switch ($filter) {
            case 'verified':
                $where[] = "a.is_verified = 1";
                break;
            case 'unverified':
                $where[] = "a.is_verified = 0";
                break;
            case 'featured':
                $where[] = "a.is_featured = 1";
                break;
        }
    }

    if ($search) {
        $where[] = "(a.first_name LIKE ? OR a.last_name LIKE ? OR a.company LIKE ? OR u.email LIKE ? OR a.license_number LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sssss';
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT a.*, u.username, u.email, u.created_at as user_created_at,
                   COUNT(p.id) as total_properties,
                   AVG(p.price) as avg_property_price
            FROM agents a
            INNER JOIN users u ON a.user_id = u.id
            LEFT JOIN properties p ON a.user_id = p.agent_id AND p.approval_status = 'approved'
            $whereClause
            GROUP BY a.id
            ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $agents = [];
    if ($stmt = $conn->prepare($sql)) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $agents[] = $row;
        }
        $stmt->close();
    }

    return $agents;
}

function getTotalAgents($filter = '', $search = '') {
    global $conn;

    $where = [];
    $params = [];
    $types = '';

    if ($filter) {
        switch ($filter) {
            case 'verified':
                $where[] = "a.is_verified = 1";
                break;
            case 'unverified':
                $where[] = "a.is_verified = 0";
                break;
            case 'featured':
                $where[] = "a.is_featured = 1";
                break;
        }
    }

    if ($search) {
        $where[] = "(a.first_name LIKE ? OR a.last_name LIKE ? OR a.company LIKE ? OR u.email LIKE ? OR a.license_number LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sssss';
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT COUNT(*) as total FROM agents a INNER JOIN users u ON a.user_id = u.id $whereClause";

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

function getAgentStats() {
    global $conn;

    $stats = [
        'total' => 0,
        'verified' => 0,
        'unverified' => 0,
        'featured' => 0,
        'avg_rating' => 0,
        'total_properties' => 0
    ];

    // Get agent counts
    $sql = "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END) as verified,
                SUM(CASE WHEN is_verified = 0 THEN 1 ELSE 0 END) as unverified,
                SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured,
                AVG(rating) as avg_rating
            FROM agents";
    if ($result = $conn->query($sql)) {
        $row = $result->fetch_assoc();
        $stats['total'] = $row['total'];
        $stats['verified'] = $row['verified'];
        $stats['unverified'] = $row['unverified'];
        $stats['featured'] = $row['featured'];
        $stats['avg_rating'] = round($row['avg_rating'], 1);
    }

    // Get total properties by agents
    $sql = "SELECT COUNT(*) as total_properties FROM properties p
            INNER JOIN agents a ON p.agent_id = a.user_id
            WHERE p.approval_status = 'approved'";
    if ($result = $conn->query($sql)) {
        $row = $result->fetch_assoc();
        $stats['total_properties'] = $row['total_properties'];
    }

    return $stats;
}

function verifyAgent($agent_id, $notes = '') {
    global $conn;

    // Get current agent data
    $current_data = [];
    $sql = "SELECT * FROM agents WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $current_data = $result->fetch_assoc();
        }
        $stmt->close();
    }

    // Update agent verification status
    $sql = "UPDATE agents SET is_verified = 1, verified_at = CURRENT_TIMESTAMP WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $agent_id);

        if ($stmt->execute()) {
            $stmt->close();

            // Log the action
            logAdminAction('verify', 'agent', $agent_id, $current_data, ['is_verified' => 1, 'admin_notes' => $notes]);

            return ['success' => true, 'message' => 'Agente verificado exitosamente.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al verificar el agente.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function unverifyAgent($agent_id, $notes = '') {
    global $conn;

    // Get current agent data
    $current_data = [];
    $sql = "SELECT * FROM agents WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $current_data = $result->fetch_assoc();
        }
        $stmt->close();
    }

    // Update agent verification status
    $sql = "UPDATE agents SET is_verified = 0, verified_at = NULL WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $agent_id);

        if ($stmt->execute()) {
            $stmt->close();

            // Log the action
            logAdminAction('unverify', 'agent', $agent_id, $current_data, ['is_verified' => 0, 'admin_notes' => $notes]);

            return ['success' => true, 'message' => 'Verificación del agente removida exitosamente.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al remover la verificación del agente.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function updateAgent($data) {
    global $conn;

    if (empty($data['agent_id'])) {
        return ['success' => false, 'message' => 'ID de agente requerido.'];
    }

    // Get current agent data
    $current_data = [];
    $sql = "SELECT * FROM agents WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $data['agent_id']);
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

    if (!empty($data['first_name'])) {
        $updates[] = "first_name = ?";
        $params[] = $data['first_name'];
        $types .= 's';
    }

    if (!empty($data['last_name'])) {
        $updates[] = "last_name = ?";
        $params[] = $data['last_name'];
        $types .= 's';
    }

    if (!empty($data['phone_number'])) {
        $updates[] = "phone_number = ?";
        $params[] = $data['phone_number'];
        $types .= 's';
    }

    if (!empty($data['company'])) {
        $updates[] = "company = ?";
        $params[] = $data['company'];
        $types .= 's';
    }

    if (!empty($data['license_number'])) {
        $updates[] = "license_number = ?";
        $params[] = $data['license_number'];
        $types .= 's';
    }

    if (!empty($data['experience_years'])) {
        $updates[] = "experience_years = ?";
        $params[] = $data['experience_years'];
        $types .= 'i';
    }

    if (!empty($data['location'])) {
        $updates[] = "location = ?";
        $params[] = $data['location'];
        $types .= 's';
    }

    if (!empty($data['website'])) {
        $updates[] = "website = ?";
        $params[] = $data['website'];
        $types .= 's';
    }

    if (isset($data['is_featured'])) {
        $updates[] = "is_featured = ?";
        $params[] = $data['is_featured'] ? 1 : 0;
        $types .= 'i';
    }

    if (!empty($data['specialties'])) {
        $updates[] = "specialties = ?";
        $params[] = $data['specialties'];
        $types .= 's';
    }

    if (!empty($data['bio'])) {
        $updates[] = "bio = ?";
        $params[] = $data['bio'];
        $types .= 's';
    }

    if (empty($updates)) {
        return ['success' => false, 'message' => 'No hay cambios para actualizar.'];
    }

    $updates[] = "updated_at = CURRENT_TIMESTAMP";
    $params[] = $data['agent_id'];
    $types .= 'i';

    $sql = "UPDATE agents SET " . implode(', ', $updates) . " WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $stmt->close();

            // Log the action
            logAdminAction('update', 'agent', $data['agent_id'], $current_data, $data);

            return ['success' => true, 'message' => 'Agente actualizado exitosamente.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al actualizar el agente.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function bulkVerifyAgents($agent_ids) {
    if (empty($agent_ids)) {
        return ['success' => false, 'message' => 'No se seleccionaron agentes.'];
    }

    $success_count = 0;
    $error_count = 0;

    foreach ($agent_ids as $agent_id) {
        $result = verifyAgent($agent_id);
        if ($result['success']) {
            $success_count++;
        } else {
            $error_count++;
        }
    }

    if ($success_count > 0) {
        return ['success' => true, 'message' => "$success_count agente(s) verificado(s) exitosamente." . ($error_count > 0 ? " $error_count error(es)." : "")];
    } else {
        return ['success' => false, 'message' => 'No se pudo verificar ningún agente.'];
    }
}

function bulkUnverifyAgents($agent_ids) {
    if (empty($agent_ids)) {
        return ['success' => false, 'message' => 'No se seleccionaron agentes.'];
    }

    $success_count = 0;
    $error_count = 0;

    foreach ($agent_ids as $agent_id) {
        $result = unverifyAgent($agent_id);
        if ($result['success']) {
            $success_count++;
        } else {
            $error_count++;
        }
    }

    if ($success_count > 0) {
        return ['success' => true, 'message' => "Verificación removida de $success_count agente(s) exitosamente." . ($error_count > 0 ? " $error_count error(es)." : "")];
    } else {
        return ['success' => false, 'message' => 'No se pudo remover la verificación de ningún agente.'];
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

        .verification-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .verification-verified { background: #d1ecf1; color: #0c5460; }
        .verification-unverified { background: #f8d7da; color: #721c24; }
        .verification-featured { background: #d4edda; color: #155724; }

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

        .agent-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .rating-stars {
            color: #f59e0b;
        }

        .rating-stars .far {
            color: #ddd;
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
                        <i class="fas fa-user-tie me-2"></i>Gestión de Agentes
                    </h1>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-light" onclick="bulkVerify()">
                            <i class="fas fa-check-circle me-2"></i>Verificar
                        </button>
                        <button type="button" class="btn btn-light" onclick="bulkUnverify()">
                            <i class="fas fa-times-circle me-2"></i>Quitar Verificación
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
            <a href="agents.php" class="nav-link active">
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
                            <div class="h4 mb-0"><?php echo number_format($agentStats['total']); ?></div>
                            <div class="text-muted">Total Agentes</div>
                        </div>
                        <i class="fas fa-user-tie fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($agentStats['verified']); ?></div>
                            <div class="text-muted">Verificados</div>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($agentStats['featured']); ?></div>
                            <div class="text-muted">Destacados</div>
                        </div>
                        <i class="fas fa-star fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($agentStats['avg_rating'], 1); ?></div>
                            <div class="text-muted">Calificación Promedio</div>
                        </div>
                        <i class="fas fa-star fa-2x text-info"></i>
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
                        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nombre, empresa, email, licencia...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="filter">
                            <option value="">Todos</option>
                            <option value="verified" <?php echo $filter === 'verified' ? 'selected' : ''; ?>>Verificados</option>
                            <option value="unverified" <?php echo $filter === 'unverified' ? 'selected' : ''; ?>>No Verificados</option>
                            <option value="featured" <?php echo $filter === 'featured' ? 'selected' : ''; ?>>Destacados</option>
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
                        <a href="agents.php" class="btn btn-secondary w-100">
                            <i class="fas fa-times me-2"></i>Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Agents Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Lista de Agentes</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-success" onclick="bulkVerify()" id="bulkVerifyBtn" style="display: none;">
                            <i class="fas fa-check-circle me-2"></i>Verificar Seleccionados
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" onclick="bulkUnverify()" id="bulkUnverifyBtn" style="display: none;">
                            <i class="fas fa-times-circle me-2"></i>Quitar Verificación
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form id="bulkActionForm" method="POST">
                    <input type="hidden" name="action" value="">
                    <div class="table-responsive">
                        <table class="table table-hover" id="agentsTable">
                            <thead>
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>Agente</th>
                                    <th>Empresa</th>
                                    <th>Licencia</th>
                                    <th>Propiedades</th>
                                    <th>Calificación</th>
                                    <th>Estado</th>
                                    <th>Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agents as $agent): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input agent-checkbox" name="agent_ids[]" value="<?php echo $agent['id']; ?>">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($agent['profile_picture_url']): ?>
                                                    <img src="<?php echo htmlspecialchars($agent['profile_picture_url']); ?>" alt="Avatar" class="agent-avatar me-3">
                                                <?php else: ?>
                                                    <div class="agent-avatar bg-light d-flex align-items-center justify-content-center me-3">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($agent['first_name'] . ' ' . $agent['last_name']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($agent['email']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($agent['company'] ?: 'Independiente'); ?></td>
                                        <td><?php echo htmlspecialchars($agent['license_number'] ?: 'No especificada'); ?></td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $agent['total_properties']; ?> propiedades</span>
                                            <?php if ($agent['avg_property_price'] > 0): ?>
                                                <br><small class="text-muted">Promedio: $<?php echo number_format($agent['avg_property_price'], 0); ?> MXN</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="rating-stars">
                                                <?php
                                                $rating = $agent['rating'] ?? 0;
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= floor($rating)) {
                                                        echo '<i class="fas fa-star"></i>';
                                                    } elseif ($i - 0.5 <= $rating) {
                                                        echo '<i class="fas fa-star-half-alt"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star"></i>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <small class="text-muted">(<?php echo $agent['review_count'] ?? 0; ?> reseñas)</small>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <span class="verification-badge verification-<?php echo $agent['is_verified'] ? 'verified' : 'unverified'; ?>">
                                                    <i class="fas fa-<?php echo $agent['is_verified'] ? 'check-circle' : 'times-circle'; ?> me-1"></i>
                                                    <?php echo $agent['is_verified'] ? 'Verificado' : 'No Verificado'; ?>
                                                </span>
                                                <?php if ($agent['is_featured']): ?>
                                                    <span class="verification-badge verification-featured">
                                                        <i class="fas fa-star me-1"></i>Destacado
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($agent['user_created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewAgent(<?php echo $agent['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editAgent(<?php echo $agent['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if (!$agent['is_verified']): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="verifyAgent(<?php echo $agent['id']; ?>, '<?php echo htmlspecialchars($agent['first_name'] . ' ' . $agent['last_name']); ?>')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="unverifyAgent(<?php echo $agent['id']; ?>, '<?php echo htmlspecialchars($agent['first_name'] . ' ' . $agent['last_name']); ?>')">
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
            <nav aria-label="Agent pagination" class="mt-4">
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

    <!-- Verify Agent Modal -->
    <div class="modal fade" id="verifyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2 text-success"></i>Verificar Agente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="verify">
                        <input type="hidden" name="agent_id" id="verify_agent_id">
                        <p>¿Estás seguro de que quieres verificar al agente <strong id="verify_agent_name"></strong>?</p>
                        <div class="form-floating">
                            <textarea class="form-control" id="verify_notes" name="notes" style="height: 100px;" placeholder="Notas de verificación (opcional)"></textarea>
                            <label for="verify_notes">Notas del administrador</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Verificar Agente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Unverify Agent Modal -->
    <div class="modal fade" id="unverifyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle me-2 text-warning"></i>Quitar Verificación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="unverify">
                        <input type="hidden" name="agent_id" id="unverify_agent_id">
                        <p>¿Estás seguro de que quieres quitar la verificación al agente <strong id="unverify_agent_name"></strong>?</p>
                        <div class="form-floating">
                            <textarea class="form-control" id="unverify_notes" name="notes" style="height: 100px;" placeholder="Razón para quitar verificación (opcional)"></textarea>
                            <label for="unverify_notes">Notas del administrador</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Quitar Verificación</button>
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
            const checkboxes = document.querySelectorAll('.agent-checkbox');
            const bulkVerifyBtn = document.getElementById('bulkVerifyBtn');
            const bulkUnverifyBtn = document.getElementById('bulkUnverifyBtn');

            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });

            toggleBulkButtons();
        });

        // Individual checkbox change
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('agent-checkbox')) {
                toggleBulkButtons();
                updateSelectAllState();
            }
        });

        function toggleBulkButtons() {
            const checkedBoxes = document.querySelectorAll('.agent-checkbox:checked');
            const bulkVerifyBtn = document.getElementById('bulkVerifyBtn');
            const bulkUnverifyBtn = document.getElementById('bulkUnverifyBtn');

            if (checkedBoxes.length > 0) {
                bulkVerifyBtn.style.display = 'inline-block';
                bulkUnverifyBtn.style.display = 'inline-block';
            } else {
                bulkVerifyBtn.style.display = 'none';
                bulkUnverifyBtn.style.display = 'none';
            }
        }

        function updateSelectAllState() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.agent-checkbox');
            const checkedBoxes = document.querySelectorAll('.agent-checkbox:checked');

            selectAll.checked = checkboxes.length === checkedBoxes.length && checkboxes.length > 0;
            selectAll.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < checkboxes.length;
        }

        function verifyAgent(agentId, agentName) {
            document.getElementById('verify_agent_id').value = agentId;
            document.getElementById('verify_agent_name').textContent = agentName;
            document.getElementById('verify_notes').value = '';

            new bootstrap.Modal(document.getElementById('verifyModal')).show();
        }

        function unverifyAgent(agentId, agentName) {
            document.getElementById('unverify_agent_id').value = agentId;
            document.getElementById('unverify_agent_name').textContent = agentName;
            document.getElementById('unverify_notes').value = '';

            new bootstrap.Modal(document.getElementById('unverifyModal')).show();
        }

        function bulkVerify() {
            const checkedBoxes = document.querySelectorAll('.agent-checkbox:checked');

            if (checkedBoxes.length === 0) {
                alert('Por favor, selecciona al menos un agente.');
                return;
            }

            if (confirm(`¿Estás seguro de que quieres verificar ${checkedBoxes.length} agente(s)?`)) {
                const form = document.getElementById('bulkActionForm');
                form.querySelector('input[name="action"]').value = 'bulk_verify';
                form.submit();
            }
        }

        function bulkUnverify() {
            const checkedBoxes = document.querySelectorAll('.agent-checkbox:checked');

            if (checkedBoxes.length === 0) {
                alert('Por favor, selecciona al menos un agente.');
                return;
            }

            if (confirm(`¿Estás seguro de que quieres quitar la verificación de ${checkedBoxes.length} agente(s)?`)) {
                const form = document.getElementById('bulkActionForm');
                form.querySelector('input[name="action"]').value = 'bulk_unverify';
                form.submit();
            }
        }

        function viewAgent(agentId) {
            window.open(`../agent_public_profile.php?id=${agentId}`, '_blank');
        }

        function editAgent(agentId) {
            // This would open an edit modal or redirect to edit page
            alert('Función de edición próximamente disponible');
        }

        // Initialize DataTable
        $(document).ready(function() {
            $('#agentsTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                },
                "pageLength": 25,
                "order": [[ 7, "desc" ]],
                "columnDefs": [
                    { "orderable": false, "targets": [0, 8] }
                ]
            });
        });
    </script>
</body>
</html>