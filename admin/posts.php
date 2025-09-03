
<?php
require_once 'auth.php';
requireAdminLogin();

$pageTitle = 'Content Management - Tierras.mx Admin';
$admin = getCurrentAdmin();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $message = createPost($_POST);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'update':
                $message = updatePost($_POST);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'delete':
                $message = deletePost($_POST['post_id']);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'publish':
                $message = publishPost($_POST['post_id']);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'unpublish':
                $message = unpublishPost($_POST['post_id']);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
            case 'bulk_delete':
                $message = bulkDeletePosts($_POST['post_ids'] ?? []);
                $messageType = $message['success'] ? 'success' : 'error';
                $message = $message['message'];
                break;
        }
    }
}

// Get posts with pagination and filtering
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$filter = $_GET['filter'] ?? '';
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$posts = getPosts($filter, $search, $category, $perPage, $offset);
$totalPosts = getTotalPosts($filter, $search, $category);
$totalPages = ceil($totalPosts / $perPage);

// Get categories for filter dropdown
$categories = getCategories();

// Get post statistics
$postStats = getPostStats();

function getPosts($filter = '', $search = '', $category = '', $limit = 20, $offset = 0) {
    global $conn;

    $where = [];
    $params = [];
    $types = '';

    if ($filter) {
        switch ($filter) {
            case 'published':
                $where[] = "p.status = 'published'";
                break;
            case 'draft':
                $where[] = "p.status = 'draft'";
                break;
            case 'pending':
                $where[] = "p.status = 'pending'";
                break;
        }
    }

    if ($category) {
        $where[] = "p.category_id = ?";
        $params[] = $category;
        $types .= 'i';
    }

    if ($search) {
        $where[] = "(p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sss';
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT p.*, c.name as category_name, u.username as author_name,
                   COUNT(pt.tag_id) as tag_count
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN users u ON p.author_id = u.id
            LEFT JOIN post_tags pt ON p.id = pt.post_id
            $whereClause
            GROUP BY p.id
            ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $posts = [];
    if ($stmt = $conn->prepare($sql)) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
        $stmt->close();
    }

    return $posts;
}

function getTotalPosts($filter = '', $search = '', $category = '') {
    global $conn;

    $where = [];
    $params = [];
    $types = '';

    if ($filter) {
        switch ($filter) {
            case 'published':
                $where[] = "status = 'published'";
                break;
            case 'draft':
                $where[] = "status = 'draft'";
                break;
            case 'pending':
                $where[] = "status = 'pending'";
                break;
        }
    }

    if ($category) {
        $where[] = "category_id = ?";
        $params[] = $category;
        $types .= 'i';
    }

    if ($search) {
        $where[] = "(title LIKE ? OR content LIKE ? OR excerpt LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sss';
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT COUNT(*) as total FROM posts $whereClause";

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

function getCategories() {
    global $conn;

    $categories = [];
    $sql = "SELECT id, name FROM categories ORDER BY name";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }

    return $categories;
}

function getPostStats() {
    global $conn;

    $stats = [
        'total' => 0,
        'published' => 0,
        'draft' => 0,
        'pending' => 0
    ];

    $sql = "SELECT status, COUNT(*) as count FROM posts GROUP BY status";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $stats['total'] += $row['count'];
            $stats[$row['status']] = $row['count'];
        }
    }

    return $stats;
}

function createPost($data) {
    global $conn;

    // Validate input
    if (empty($data['title']) || empty($data['content'])) {
        return ['success' => false, 'message' => 'Título y contenido son obligatorios.'];
    }

    // Insert post
    $sql = "INSERT INTO posts (title, slug, content, excerpt, status, post_type, category_id, author_id, published_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $slug = createSlug($data['title']);
    $excerpt = !empty($data['excerpt']) ? $data['excerpt'] : substr(strip_tags($data['content']), 0, 200) . '...';
    $status = $data['status'] ?? 'draft';
    $post_type = $data['post_type'] ?? 'post';
    $category_id = !empty($data['category_id']) ? $data['category_id'] : null;
    $author_id = $_SESSION['id'];
    $published_at = ($status === 'published') ? date('Y-m-d H:i:s') : null;

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssssssis", $data['title'], $slug, $data['content'], $excerpt,
                         $status, $post_type, $category_id, $author_id, $published_at);

        if ($stmt->execute()) {
            $post_id = $stmt->insert_id;
            $stmt->close();

            // Handle tags
            if (!empty($data['tags'])) {
                savePostTags($post_id, $data['tags']);
            }

            // Log the action
            logAdminAction('create', 'post', $post_id, null, $data);

            return ['success' => true, 'message' => 'Post creado exitosamente.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al crear el post.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function updatePost($data) {
    global $conn;

    if (empty($data['post_id'])) {
        return ['success' => false, 'message' => 'ID de post requerido.'];
    }

    // Get current post data
    $current_data = [];
    $sql = "SELECT * FROM posts WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $data['post_id']);
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

        // Update slug if title changed
        $updates[] = "slug = ?";
        $params[] = createSlug($data['title']);
        $types .= 's';
    }

    if (!empty($data['content'])) {
        $updates[] = "content = ?";
        $params[] = $data['content'];
        $types .= 's';
    }

    if (isset($data['excerpt'])) {
        $excerpt = !empty($data['excerpt']) ? $data['excerpt'] : substr(strip_tags($data['content']), 0, 200) . '...';
        $updates[] = "excerpt = ?";
        $params[] = $excerpt;
        $types .= 's';
    }

    if (!empty($data['status'])) {
        $updates[] = "status = ?";
        $params[] = $data['status'];
        $types .= 's';

        // Update published_at if status changed to published
        if ($data['status'] === 'published' && $current_data['status'] !== 'published') {
            $updates[] = "published_at = ?";
            $params[] = date('Y-m-d H:i:s');
            $types .= 's';
        }
    }

    if (isset($data['category_id'])) {
        $updates[] = "category_id = ?";
        $params[] = $data['category_id'] ?: null;
        $types .= 'i';
    }

    if (empty($updates)) {
        return ['success' => false, 'message' => 'No hay cambios para actualizar.'];
    }

    $updates[] = "updated_at = CURRENT_TIMESTAMP";
    $params[] = $data['post_id'];
    $types .= 'i';

    $sql = "UPDATE posts SET " . implode(', ', $updates) . " WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $stmt->close();

            // Handle tags
            if (isset($data['tags'])) {
                savePostTags($data['post_id'], $data['tags']);
            }

            // Log the action
            logAdminAction('update', 'post', $data['post_id'], $current_data, $data);

            return ['success' => true, 'message' => 'Post actualizado exitosamente.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al actualizar el post.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function deletePost($post_id) {
    global $conn;

    // Get post data before deletion
    $post_data = [];
    $sql = "SELECT * FROM posts WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $post_data = $result->fetch_assoc();
        }
        $stmt->close();
    }

    // Delete post
    $sql = "DELETE FROM posts WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $post_id);

        if ($stmt->execute()) {
            $stmt->close();

            // Log the action
            logAdminAction('delete', 'post', $post_id, $post_data, null);

            return ['success' => true, 'message' => 'Post eliminado exitosamente.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al eliminar el post.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function publishPost($post_id) {
    global $conn;

    // Get current post data
    $current_data = [];
    $sql = "SELECT * FROM posts WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $current_data = $result->fetch_assoc();
        }
        $stmt->close();
    }

    $sql = "UPDATE posts SET status = 'published', published_at = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $published_at = date('Y-m-d H:i:s');
        $stmt->bind_param("si", $published_at, $post_id);

        if ($stmt->execute()) {
            $stmt->close();

            // Log the action
            logAdminAction('publish', 'post', $post_id, $current_data, ['status' => 'published', 'published_at' => $published_at]);

            return ['success' => true, 'message' => 'Post publicado exitosamente.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al publicar el post.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function unpublishPost($post_id) {
    global $conn;

    // Get current post data
    $current_data = [];
    $sql = "SELECT * FROM posts WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $current_data = $result->fetch_assoc();
        }
        $stmt->close();
    }

    $sql = "UPDATE posts SET status = 'draft', published_at = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $post_id);

        if ($stmt->execute()) {
            $stmt->close();

            // Log the action
            logAdminAction('unpublish', 'post', $post_id, $current_data, ['status' => 'draft', 'published_at' => null]);

            return ['success' => true, 'message' => 'Post despublicado exitosamente.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error al despublicar el post.'];
        }
    }

    return ['success' => false, 'message' => 'Error en la base de datos.'];
}

function bulkDeletePosts($post_ids) {
    if (empty($post_ids)) {
        return ['success' => false, 'message' => 'No se seleccionaron posts.'];
    }

    $success_count = 0;
    $error_count = 0;

    foreach ($post_ids as $post_id) {
        $result = deletePost($post_id);
        if ($result['success']) {
            $success_count++;
        } else {
            $error_count++;
        }
    }

    if ($success_count > 0) {
        return ['success' => true, 'message' => "$success_count post(s) eliminado(s) exitosamente." . ($error_count > 0 ? " $error_count error(es)." : "")];
    } else {
        return ['success' => false, 'message' => 'No se pudo eliminar ningún post.'];
    }
}

function savePostTags($post_id, $tags_string) {
    global $conn;

    // Delete existing tags
    $sql = "DELETE FROM post_tags WHERE post_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $stmt->close();
    }

    // Add new tags
    if (!empty($tags_string)) {
        $tags = array_map('trim', explode(',', $tags_string));

        foreach ($tags as $tag_name) {
            if (!empty($tag_name)) {
                // Get or create tag
                $tag_id = getOrCreateTag($tag_name);

                if ($tag_id) {
                    // Link tag to post
                    $sql = "INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("ii", $post_id, $tag_id);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }
        }
    }
}

function getOrCreateTag($tag_name) {
    global $conn;

    // Check if tag exists
    $sql = "SELECT id FROM tags WHERE name = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $tag_name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row['id'];
        }
        $stmt->close();
    }

    // Create new tag
    $slug = createSlug($tag_name);
    $sql = "INSERT INTO tags (name, slug) VALUES (?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $tag_name, $slug);
        if ($stmt->execute()) {
            $tag_id = $stmt->insert_id;
            $stmt->close();
            return $tag_id;
        }
        $stmt->close();
    }

    return false;
}

function createSlug($string) {
    // Convert to lowercase
    $slug = strtolower($string);

    // Replace non-alphanumeric characters with hyphens
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);

    // Replace spaces and multiple hyphens with single hyphen
    $slug = preg_replace('/[\s-]+/', '-', $slug);

    // Remove leading/trailing hyphens
    $slug = trim($slug, '-');

    // Ensure uniqueness by adding number if needed
    $original_slug = $slug;
    $counter = 1;

    global $conn;
    while (true) {
        $sql = "SELECT id FROM posts WHERE slug = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $slug);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                $stmt->close();
                break;
            }
            $stmt->close();
        }

        $slug = $original_slug . '-' . $counter;
        $counter++;
    }

    return $slug;
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

        .status-published { background: #d1ecf1; color: #0c5460; }
        .status-draft { background: #fff3cd; color: #856404; }
        .status-pending { background: #f8d7da; color: #721c24; }

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

        .post-content-preview {
            max-height: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
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
                        <i class="fas fa-newspaper me-2"></i>Gestión de Contenido
                    </h1>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createPostModal">
                        <i class="fas fa-plus-circle me-2"></i>Crear Post
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
            <a href="posts.php" class="nav-link active">
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
                            <div class="h4 mb-0"><?php echo number_format($postStats['total']); ?></div>
                            <div class="text-muted">Total Posts</div>
                        </div>
                        <i class="fas fa-newspaper fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($postStats['published']); ?></div>
                            <div class="text-muted">Publicados</div>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($postStats['draft']); ?></div>
                            <div class="text-muted">Borradores</div>
                        </div>
                        <i class="fas fa-edit fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="h4 mb-0"><?php echo number_format($postStats['pending']); ?></div>
                            <div class="text-muted">Pendientes</div>
                        </div>
                        <i class="fas fa-clock fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Buscar</label>
                        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Título, contenido...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="filter">
                            <option value="">Todos</option>
                            <option value="published" <?php echo $filter === 'published' ? 'selected' : ''; ?>>Publicados</option>
                            <option value="draft" <?php echo $filter === 'draft' ? 'selected' : ''; ?>>Borradores</option>
                            <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Pendientes</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Categoría</label>
                        <select class="form-select" name="category">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-admin">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                            <a href="posts.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Posts Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Lista de Posts</h5>
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
                        <table class="table table-hover" id="postsTable">
                            <thead>
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>Título</th>
                                    <th>Categoría</th>
                                    <th>Autor</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($posts as $post): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input post-checkbox" name="post_ids[]" value="<?php echo $post['id']; ?>">
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars(substr($post['title'], 0, 50)); ?><?php echo strlen($post['title']) > 50 ? '...' : ''; ?></div>
                                                <div class="post-content-preview text-muted small">
                                                    <?php echo htmlspecialchars(substr(strip_tags($post['content']), 0, 100)); ?><?php echo strlen(strip_tags($post['content'])) > 100 ? '...' : ''; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($post['category_name'] ?: 'Sin categoría'); ?></td>
                                        <td><?php echo htmlspecialchars($post['author_name'] ?: 'Desconocido'); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $post['status']; ?>">
                                                <?php
                                                switch($post['status']) {
                                                    case 'published': echo 'Publicado'; break;
                                                    case 'draft': echo 'Borrador'; break;
                                                    case 'pending': echo 'Pendiente'; break;
                                                    default: echo ucfirst($post['status']); break;
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($post['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewPost(<?php echo $post['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editPost(<?php echo $post['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($post['status'] === 'draft' || $post['status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="publishPost(<?php echo $post['id']; ?>)">
                                                        <i class="fas fa-upload"></i>
                                                    </button>
                                                <?php elseif ($post['status'] === 'published'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="unpublishPost(<?php echo $post['id']; ?>)">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars($post['title']); ?>')">
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
            <nav aria-label="Posts pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter=<?php echo urlencode($filter); ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo urlencode($filter); ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter=<?php echo urlencode($filter); ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </main>

    <!-- Create Post Modal -->
    <div class="modal fade" id="createPostModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Crear Nuevo Post
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="create_title" name="title" required>
                                    <label for="create_title">Título *</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="create_status" name="status">
                                        <option value="draft">Borrador</option>
                                        <option value="published">Publicado</option>
                                        <option value="pending">Pendiente</option>
                                    </select>
                                    <label for="create_status">Estado</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="create_category" name="category_id">
                                        <option value="">Sin categoría</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="create_category">Categoría</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="create_post_type" name="post_type">
                                        <option value="post">Post</option>
                                        <option value="page">Página</option>
                                        <option value="property_guide">Guía de Propiedades</option>
                                        <option value="news">Noticia</option>
                                    </select>
                                    <label for="create_post_type">Tipo de Contenido</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="create_excerpt" name="excerpt" style="height: 80px;" placeholder="Resumen del post (opcional)"></textarea>
                            <label for="create_excerpt">Extracto</label>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="create_content" name="content" style="height: 300px;" required></textarea>
                            <label for="create_content">Contenido *</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="create_tags" name="tags" placeholder="Etiquetas separadas por comas">
                            <label for="create_tags">Etiquetas</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-admin">Crear Post</button>
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
            const checkboxes = document.querySelectorAll('.post-checkbox');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });

            toggleBulkDeleteButton();
        });

        // Individual checkbox change
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('post-checkbox')) {
                toggleBulkDeleteButton();
                updateSelectAllState();
            }
        });

        function toggleBulkDeleteButton() {
            const checkedBoxes = document.querySelectorAll('.post-checkbox:checked');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

            if (checkedBoxes.length > 0) {
                bulkDeleteBtn.style.display = 'inline-block';
            } else {
                bulkDeleteBtn.style.display = 'none';
            }
        }

        function updateSelectAllState() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.post-checkbox');
            const checkedBoxes = document.querySelectorAll('.post-checkbox:checked');

            selectAll.checked = checkboxes.length === checkedBoxes.length && checkboxes.length > 0;
            selectAll.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < checkboxes.length;
        }

        function bulkDelete() {
            const checkedBoxes = document.querySelectorAll('.post-checkbox:checked');

            if (checkedBoxes.length === 0) {
                alert('Por favor, selecciona al menos un post.');
                return;
            }

            if (confirm(`¿Estás seguro de que quieres eliminar ${checkedBoxes.length} post(s)? Esta acción no se puede deshacer.`)) {
                document.getElementById('bulkActionForm').submit();
            }
        }

        function publishPost(postId) {
            if (confirm('¿Estás seguro de que quieres publicar este post?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="publish">
                    <input type="hidden" name="post_id" value="${postId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function unpublishPost(postId) {
            if (confirm('¿Estás seguro de que quieres despublicar este post?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="unpublish">
                    <input type="hidden" name="post_id" value="${postId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deletePost(postId, postTitle) {
            if (confirm(`¿Estás seguro de que quieres eliminar el post "${postTitle}"? Esta acción no se puede deshacer.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="post_id" value="${postId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function viewPost(postId) {
            window.open(`../blog.php?id=${postId}`, '_blank');
        }

        function editPost(postId) {
            // This would open an edit modal or redirect to edit page
            alert('Función de edición próximamente disponible');
        }

        // Initialize DataTable
        $(document).ready(function() {
            $('#postsTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                },
                "pageLength": 25,
                "order": [[ 5, "desc" ]],
                "columnDefs": [
                    { "orderable": false, "targets": [0, 6] }
                ]
            });
        });
    </script>
</body>
</html>