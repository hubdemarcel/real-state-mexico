<?php
require_once 'auth.php';
requireAdminLogin();

$pageTitle = 'User Management - Tierras.mx Admin';
$admin = getCurrentAdmin();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $message = createUser($_POST);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'update':
                $message = updateUser($_POST);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'delete':
                $message = deleteUser($_POST['user_id']);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'bulk_delete':
                $message = bulkDeleteUsers($_POST['user_ids'] ?? []);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
        }
    }
}

// Get users with pagination and filtering
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$filter = $_GET['filter'] ?? '';
$search = $_GET['search'] ?? '';

$users = getUsers($filter, $search, $perPage, $offset);
$totalUsers = getTotalUsers($filter, $search);
$totalPages = ceil($totalUsers / $perPage);

// Get user statistics
$userStats = getUserStats();

function getUsers($filter = '', $search = '', $limit = 20, $offset = 0) {
    global $conn;

    $where = [];
    $params = [];
    $types = '';

    if ($filter) {
        $where[] = "user_type = ?";
        $params[] = $filter;
        $types .= 's';
    }

    if ($search) {
        $where[] = "(username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ssss';
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT id, username, email, user_type, first_name, last_name, phone_number, created_at, updated_at
            FROM users $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $users = [];
    if ($stmt = $conn->prepare($sql)) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
    }

    return $users;
}

function getTotalUsers($filter = '', $search = '') {
    global $conn;

    $where = [];
    $params = [];
    $types = '';

    if ($filter) {
        $where[] = "user_type = ?";
        $params[] = $filter;
        $types .= 's';
    }

    if ($search) {
        $where[] = "(username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ssss';
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT COUNT(*) as total FROM users $whereClause";

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

function getUserStats() {
    global $conn;

    $stats = [
        'total' => 0,
        'buyers' => 0,
        'sellers' => 0,
        'agents' => 0,
        'admins' => 0
    ];

    $sql = "SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $stats['total'] += $row['count'];
            $stats[$row['user_type'] . 's'] = $row['count'];
        }
    }

    return $stats;
}

function createUser($data) {
    global $conn;

    // Validate input
    if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
        return ['success' => false, 'message' => 'Todos los campos son obligatorios.'];
    }

    if (!isValidEmail($data['email'])) {
        return ['success' => false, 'message' => 'Email inválido.'];
    }

    // Check if username or email already exists
    $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    if ($stmt = $conn->prepare($check_sql)) {
        $stmt->bind_param("ss", $data['username'], $data['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'El usuario o email ya existe.'];
        }
        $stmt->close();
    }

    // Hash password
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

    // Insert user
    $sql = "INSERT INTO users (username, email, password, user_type, first_name, last_name, phone_number)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $user_type = $data['user_type'] ?? 'buyer';
        $first_name = $data['first_name'] ?? '';
        $last_name = $data['last_name'] ?? '';
        $phone = $data['phone_number'] ?? '';

        $stmt->bind_param("sssssss", $data['username'], $data['email'], $hashed_password,
                         $user_type, $first_name, $last_name, $phone);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            $stmt->close();

            // Log the action
            logAdminAction('create', 'user', $user_id, null, $data);

            return ['success' => true, 'message' => 'Usuario creado exitosamente.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al crear el usuario.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function updateUser($data) {
    global $conn;

    if (empty($data['user_id'])) {
        return ['success' => false, 'message' => 'ID de usuario requerido.'];
    }

    // Get current user data for logging
    $current_data = [];
    $sql = "SELECT * FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $data['user_id']);
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

    if (!empty($data['email'])) {
        $updates[] = "email = ?";
        $params[] = $data['email'];
        $types .= 's';
    }

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

    if (!empty($data['user_type'])) {
        $updates[] = "user_type = ?";
        $params[] = $data['user_type'];
        $types .= 's';
    }

    if (!empty($data['password'])) {
        $updates[] = "password = ?";
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        $types .= 's';
    }

    if (empty($updates)) {
        return ['success' => false, 'message' => 'No hay cambios para actualizar.'];
    }

    $updates[] = "updated_at = CURRENT_TIMESTAMP";
    $params[] = $data['user_id'];
    $types .= 'i';

    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $stmt->close();

            // Log the action
            logAdminAction('update', 'user', $data['user_id'], $current_data, $data);

            return ['success' => true, 'message' => 'Usuario actualizado exitosamente.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al actualizar el usuario.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function deleteUser($user_id) {
    global $conn;

    // Get user data before deletion for logging
    $user_data = [];
    $sql = "SELECT * FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
        }
        $stmt->close();
    }

    // Delete user
    $sql = "DELETE FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $stmt->close();

            // Log the action
            logAdminAction('delete', 'user', $user_id, $user_data, null);

            return ['success' => true, 'message' => 'Usuario eliminado exitosamente.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al eliminar el usuario.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function bulkDeleteUsers($user_ids) {
    if (empty($user_ids)) {
        return ['success' => false, 'message' => 'No se seleccionaron usuarios.'];
    }

    $success_count = 0;
    $error_count = 0;

    foreach ($user_ids as $user_id) {
        $result = deleteUser($user_id);
        if ($result['success']) {
            $success_count++;
        } else {
            $error_count++;
        }
    }

    if ($success_count > 0) {
        return ['success' => true, 'message' => "$success_count usuario(s) eliminado(s) exitosamente." . ($error_count > 0 ? " $error_count error(es)." : "")];
    } else {
        return ['success' => false, 'message' => 'No se pudo eliminar ningún usuario.'];
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

        .user-type-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .user-type-buyer { background: #e3f2fd; color: #1976d2; }
        .user-type-seller { background: #e8f5e8; color: #388e3c; }
        .user-type-agent { background: #fff3e0; color: #f57c00; }
        .user-type-admin { background: #ffebee; color: #d32f2f; }

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

        .form-floating > label {
            padding: 1rem 0.75rem;
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
                        <i class="fas fa-users me-2"></i>Gestión de Usuarios
                    </h1>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="fas fa-user-plus me-2"></i>Crear Usuario
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
            <a href="users.php" class="nav-link active">
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
                            <div class="h4 mb-0"><?php echo number_format($userStats['total']); ?></div>
                            <div class="text-muted">Total Usuarios</div>
                        </div>
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($userStats['buyers']); ?></div>
                            <div class="text-muted">Compradores</div>
                        </div>
                        <i class="fas fa-shopping-cart fa-2x text-info"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($userStats['sellers']); ?></div>
                            <div class="text-muted">Vendedores</div>
                        </div>
                        <i class="fas fa-store fa-2x text-success"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($userStats['agents']); ?></div>
                            <div class="text-muted">Agentes</div>
                        </div>
                        <i class="fas fa-user-tie fa-2x text-warning"></i>
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
                        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Usuario, email, nombre...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Usuario</label>
                        <select class="form-select" name="filter">
                            <option value="">Todos</option>
                            <option value="buyer" <?php echo $filter === 'buyer' ? 'selected' : ''; ?>>Compradores</option>
                            <option value="seller" <?php echo $filter === 'seller' ? 'selected' : ''; ?>>Vendedores</option>
                            <option value="agent" <?php echo $filter === 'agent' ? 'selected' : ''; ?>>Agentes</option>
                            <option value="admin" <?php echo $filter === 'admin' ? 'selected' : ''; ?>>Administradores</option>
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
                        <a href="users.php" class="btn btn-secondary w-100">
                            <i class="fas fa-times me-2"></i>Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Lista de Usuarios</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-danger" onclick="bulkDelete()" id="bulkDeleteBtn" style="display: none;">
                            <i class="fas fa-trash me-2"></i>Eliminar Seleccionados
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form id="bulkActionForm" method="POST">
                    <input type="hidden" name="action" value="bulk_delete">
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead>
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Teléfono</th>
                                    <th>Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input user-checkbox" name="user_ids[]" value="<?php echo $user['id']; ?>">
                                        </td>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))); ?></td>
                                        <td>
                                            <span class="user-type-badge user-type-<?php echo $user['user_type']; ?>">
                                                <?php
                                                switch($user['user_type']) {
                                                    case 'buyer': echo 'Comprador'; break;
                                                    case 'seller': echo 'Vendedor'; break;
                                                    case 'agent': echo 'Agente'; break;
                                                    case 'admin': echo 'Admin'; break;
                                                    default: echo ucfirst($user['user_type']); break;
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['phone_number'] ?? ''); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editUser(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
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
            <nav aria-label="User pagination" class="mt-4">
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

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Crear Nuevo Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="create_username" name="username" required>
                                    <label for="create_username">Usuario *</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="create_email" name="email" required>
                                    <label for="create_email">Email *</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="create_password" name="password" required>
                                    <label for="create_password">Contraseña *</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="create_user_type" name="user_type" required>
                                        <option value="buyer">Comprador</option>
                                        <option value="seller">Vendedor</option>
                                        <option value="agent">Agente</option>
                                        <option value="admin">Administrador</option>
                                    </select>
                                    <label for="create_user_type">Tipo de Usuario *</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="create_first_name" name="first_name">
                                    <label for="create_first_name">Nombre</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="create_last_name" name="last_name">
                                    <label for="create_last_name">Apellido</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="tel" class="form-control" id="create_phone" name="phone_number">
                            <label for="create_phone">Teléfono</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-admin">Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Editar Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="edit_username" readonly>
                                    <label for="edit_username">Usuario (no editable)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="edit_email" name="email" required>
                                    <label for="edit_email">Email *</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="edit_password" name="password" placeholder="Dejar vacío para mantener">
                                    <label for="edit_password">Nueva Contraseña</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="edit_user_type" name="user_type" required>
                                        <option value="buyer">Comprador</option>
                                        <option value="seller">Vendedor</option>
                                        <option value="agent">Agente</option>
                                        <option value="admin">Administrador</option>
                                    </select>
                                    <label for="edit_user_type">Tipo de Usuario *</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="edit_first_name" name="first_name">
                                    <label for="edit_first_name">Nombre</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="edit_last_name" name="last_name">
                                    <label for="edit_last_name">Apellido</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="tel" class="form-control" id="edit_phone" name="phone_number">
                            <label for="edit_phone">Teléfono</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-admin">Actualizar Usuario</button>
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
            const checkboxes = document.querySelectorAll('.user-checkbox');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });

            toggleBulkDeleteButton();
        });

        // Individual checkbox change
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('user-checkbox')) {
                toggleBulkDeleteButton();
                updateSelectAllState();
            }
        });

        function toggleBulkDeleteButton() {
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

            if (checkedBoxes.length > 0) {
                bulkDeleteBtn.style.display = 'inline-block';
            } else {
                bulkDeleteBtn.style.display = 'none';
            }
        }

        function updateSelectAllState() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.user-checkbox');
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');

            selectAll.checked = checkboxes.length === checkedBoxes.length && checkboxes.length > 0;
            selectAll.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < checkboxes.length;
        }

        function bulkDelete() {
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');

            if (checkedBoxes.length === 0) {
                alert('Por favor, selecciona al menos un usuario.');
                return;
            }

            if (confirm(`¿Estás seguro de que quieres eliminar ${checkedBoxes.length} usuario(s)? Esta acción no se puede deshacer.`)) {
                document.getElementById('bulkActionForm').submit();
            }
        }

        function editUser(userId) {
            // Fetch user data and populate edit modal
            fetch(`api/get_user.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const user = data.user;
                        document.getElementById('edit_user_id').value = user.id;
                        document.getElementById('edit_username').value = user.username;
                        document.getElementById('edit_email').value = user.email;
                        document.getElementById('edit_user_type').value = user.user_type;
                        document.getElementById('edit_first_name').value = user.first_name || '';
                        document.getElementById('edit_last_name').value = user.last_name || '';
                        document.getElementById('edit_phone').value = user.phone_number || '';

                        new bootstrap.Modal(document.getElementById('editUserModal')).show();
                    } else {
                        alert('Error al cargar datos del usuario.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar datos del usuario.');
                });
        }

        function deleteUser(userId, username) {
            if (confirm(`¿Estás seguro de que quieres eliminar al usuario "${username}"? Esta acción no se puede deshacer.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="${userId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Initialize DataTable
        $(document).ready(function() {
            $('#usersTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                },
                "pageLength": 25,
                "order": [[ 1, "desc" ]],
                "columnDefs": [
                    { "orderable": false, "targets": [0, 8] }
                ]
            });
        });
    </script>
</body>
</html>