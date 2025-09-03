<?php
require_once 'auth.php';
requireAdminLogin();

$pageTitle = 'Analytics & Reports - Tierras.mx Admin';
$admin = getCurrentAdmin();

// Get date range for analytics
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get analytics data
$analytics = getAnalyticsData($startDate, $endDate);
$userGrowth = getUserGrowthData($startDate, $endDate);
$propertyStats = getPropertyAnalyticsData($startDate, $endDate);
$revenueData = getRevenueAnalyticsData($startDate, $endDate);
$topAgents = getTopAgentsData();
$popularLocations = getPopularLocationsData();

function getAnalyticsData($startDate, $endDate) {
    global $conn;

    $analytics = [
        'total_users' => 0,
        'new_users' => 0,
        'total_properties' => 0,
        'new_properties' => 0,
        'approved_properties' => 0,
        'rejected_properties' => 0,
        'total_posts' => 0,
        'published_posts' => 0,
        'total_revenue' => 0,
        'monthly_revenue' => 0
    ];

    // Total users
    $sql = "SELECT COUNT(*) as total FROM users";
    if ($result = $conn->query($sql)) {
        $analytics['total_users'] = $result->fetch_assoc()['total'];
    }

    // New users in date range
    $sql = "SELECT COUNT(*) as total FROM users WHERE DATE(created_at) BETWEEN ? AND ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['new_users'] = $result->fetch_assoc()['total'];
        $stmt->close();
    }

    // Total properties
    $sql = "SELECT COUNT(*) as total FROM properties";
    if ($result = $conn->query($sql)) {
        $analytics['total_properties'] = $result->fetch_assoc()['total'];
    }

    // New properties in date range
    $sql = "SELECT COUNT(*) as total FROM properties WHERE DATE(created_at) BETWEEN ? AND ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['new_properties'] = $result->fetch_assoc()['total'];
        $stmt->close();
    }

    // Approved properties in date range
    $sql = "SELECT COUNT(*) as total FROM properties WHERE approval_status = 'approved' AND DATE(approved_at) BETWEEN ? AND ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['approved_properties'] = $result->fetch_assoc()['total'];
        $stmt->close();
    }

    // Rejected properties in date range
    $sql = "SELECT COUNT(*) as total FROM properties WHERE approval_status = 'rejected' AND DATE(approved_at) BETWEEN ? AND ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['rejected_properties'] = $result->fetch_assoc()['total'];
        $stmt->close();
    }

    // Total posts
    $sql = "SELECT COUNT(*) as total FROM posts";
    if ($result = $conn->query($sql)) {
        $analytics['total_posts'] = $result->fetch_assoc()['total'];
    }

    // Published posts in date range
    $sql = "SELECT COUNT(*) as total FROM posts WHERE status = 'published' AND DATE(published_at) BETWEEN ? AND ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $analytics['published_posts'] = $result->fetch_assoc()['total'];
        $stmt->close();
    }

    return $analytics;
}

function getUserGrowthData($startDate, $endDate) {
    global $conn;

    $data = [];
    $currentDate = strtotime($startDate);
    $endDateTime = strtotime($endDate);

    while ($currentDate <= $endDateTime) {
        $date = date('Y-m-d', $currentDate);
        $sql = "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $date);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['count'];
            $data[] = [
                'date' => date('M d', $currentDate),
                'users' => (int)$count
            ];
            $stmt->close();
        }
        $currentDate = strtotime('+1 day', $currentDate);
    }

    return $data;
}

function getPropertyAnalyticsData($startDate, $endDate) {
    global $conn;

    $data = [
        'by_type' => [],
        'by_status' => [],
        'by_location' => []
    ];

    // Properties by type
    $sql = "SELECT property_type, COUNT(*) as count FROM properties
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY property_type ORDER BY count DESC";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $data['by_type'][] = $row;
        }
        $stmt->close();
    }

    // Properties by status
    $sql = "SELECT approval_status, COUNT(*) as count FROM properties
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY approval_status ORDER BY count DESC";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $data['by_status'][] = $row;
        }
        $stmt->close();
    }

    // Properties by location
    $sql = "SELECT location, COUNT(*) as count FROM properties
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY location ORDER BY count DESC LIMIT 10";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $data['by_location'][] = $row;
        }
        $stmt->close();
    }

    return $data;
}

function getRevenueAnalyticsData($startDate, $endDate) {
    global $conn;

    $data = [];
    $currentDate = strtotime($startDate);
    $endDateTime = strtotime($endDate);

    while ($currentDate <= $endDateTime) {
        $date = date('Y-m-d', $currentDate);
        // This would be replaced with actual revenue tracking
        // For now, we'll simulate based on approved properties
        $sql = "SELECT COUNT(*) as count FROM properties
                WHERE approval_status = 'approved' AND DATE(approved_at) = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $date);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['count'];
            $revenue = $count * 1500; // Simulated revenue per property
            $data[] = [
                'date' => date('M d', $currentDate),
                'revenue' => $revenue
            ];
            $stmt->close();
        }
        $currentDate = strtotime('+1 day', $currentDate);
    }

    return $data;
}

function getTopAgentsData() {
    global $conn;

    $agents = [];
    $sql = "SELECT a.first_name, a.last_name, a.company,
                   COUNT(p.id) as properties_count,
                   AVG(p.price) as avg_price,
                   a.rating
            FROM agents a
            LEFT JOIN properties p ON a.user_id = p.agent_id AND p.approval_status = 'approved'
            GROUP BY a.id
            ORDER BY properties_count DESC, a.rating DESC
            LIMIT 10";

    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $agents[] = $row;
        }
    }

    return $agents;
}

function getPopularLocationsData() {
    global $conn;

    $locations = [];
    $sql = "SELECT location, COUNT(*) as count,
                   AVG(price) as avg_price,
                   MIN(price) as min_price,
                   MAX(price) as max_price
            FROM properties
            WHERE approval_status = 'approved'
            GROUP BY location
            ORDER BY count DESC
            LIMIT 10";

    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $locations[] = $row;
        }
    }

    return $locations;
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            transition: transform 0.3s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .metric-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--admin-primary);
            margin-bottom: 0.5rem;
        }

        .metric-label {
            color: #666;
            font-size: 0.9rem;
        }

        .trend-up {
            color: var(--admin-success);
        }

        .trend-down {
            color: var(--admin-danger);
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
                        <i class="fas fa-chart-bar me-2"></i>Analytics & Reportes
                    </h1>
                </div>
                <div class="col-md-6 text-end">
                    <form method="GET" class="d-inline-flex gap-2">
                        <input type="date" class="form-control form-control-sm" name="start_date" value="<?php echo $startDate; ?>">
                        <input type="date" class="form-control form-control-sm" name="end_date" value="<?php echo $endDate; ?>">
                        <button type="submit" class="btn btn-light btn-sm">
                            <i class="fas fa-search me-2"></i>Filtrar
                        </button>
                    </form>
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
            <a href="analytics.php" class="nav-link active">
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
        <!-- Key Metrics -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="metric-card">
                    <div class="metric-value"><?php echo number_format($analytics['total_users']); ?></div>
                    <div class="metric-label">Total Usuarios</div>
                    <small class="text-muted">+<?php echo $analytics['new_users']; ?> nuevos</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="metric-card">
                    <div class="metric-value"><?php echo number_format($analytics['total_properties']); ?></div>
                    <div class="metric-label">Total Propiedades</div>
                    <small class="text-muted">+<?php echo $analytics['new_properties']; ?> nuevas</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="metric-card">
                    <div class="metric-value"><?php echo number_format($analytics['approved_properties']); ?></div>
                    <div class="metric-label">Propiedades Aprobadas</div>
                    <small class="text-success">Aprobación: <?php echo $analytics['total_properties'] > 0 ? round(($analytics['approved_properties'] / $analytics['total_properties']) * 100, 1) : 0; ?>%</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="metric-card">
                    <div class="metric-value"><?php echo number_format($analytics['total_posts']); ?></div>
                    <div class="metric-label">Total Posts</div>
                    <small class="text-muted">+<?php echo $analytics['published_posts']; ?> publicados</small>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="chart-container">
                    <h5 class="mb-3">
                        <i class="fas fa-users me-2"></i>Crecimiento de Usuarios
                    </h5>
                    <canvas id="userGrowthChart" width="400" height="200"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-container">
                    <h5 class="mb-3">
                        <i class="fas fa-home me-2"></i>Propiedades por Estado
                    </h5>
                    <canvas id="propertyStatusChart" width="200" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="mb-3">
                        <i class="fas fa-chart-line me-2"></i>Ingresos
                    </h5>
                    <canvas id="revenueChart" width="400" height="200"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="mb-3">
                        <i class="fas fa-building me-2"></i>Propiedades por Tipo
                    </h5>
                    <canvas id="propertyTypeChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="mb-3">
                        <i class="fas fa-trophy me-2"></i>Top Agentes
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Agente</th>
                                    <th>Propiedades</th>
                                    <th>Calificación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topAgents as $agent): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($agent['first_name'] . ' ' . $agent['last_name']); ?></td>
                                        <td><?php echo $agent['properties_count']; ?></td>
                                        <td>
                                            <span class="badge bg-warning">
                                                <?php echo number_format($agent['rating'], 1); ?> ★
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="mb-3">
                        <i class="fas fa-map-marker-alt me-2"></i>Ubicaciones Populares
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Ubicación</th>
                                    <th>Propiedades</th>
                                    <th>Precio Promedio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($popularLocations as $location): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($location['location']); ?></td>
                                        <td><?php echo $location['count']; ?></td>
                                        <td>$<?php echo number_format($location['avg_price'], 0); ?> MXN</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="chart-container">
                    <h5 class="mb-3">
                        <i class="fas fa-download me-2"></i>Exportar Reportes
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-admin" onclick="exportReport('pdf')">
                            <i class="fas fa-file-pdf me-2"></i>Exportar PDF
                        </button>
                        <button class="btn btn-admin" onclick="exportReport('excel')">
                            <i class="fas fa-file-excel me-2"></i>Exportar Excel
                        </button>
                        <button class="btn btn-admin" onclick="exportReport('csv')">
                            <i class="fas fa-file-csv me-2"></i>Exportar CSV
                        </button>
                    </div>
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

        // User Growth Chart
        const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        const userGrowthData = <?php echo json_encode($userGrowth); ?>;
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: userGrowthData.map(item => item.date),
                datasets: [{
                    label: 'Nuevos Usuarios',
                    data: userGrowthData.map(item => item.users),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Property Status Chart
        const propertyStatusCtx = document.getElementById('propertyStatusChart').getContext('2d');
        const propertyStatusData = <?php echo json_encode($propertyStats['by_status']); ?>;
        new Chart(propertyStatusCtx, {
            type: 'doughnut',
            data: {
                labels: propertyStatusData.map(item => {
                    switch(item.approval_status) {
                        case 'pending': return 'Pendientes';
                        case 'approved': return 'Aprobadas';
                        case 'rejected': return 'Rechazadas';
                        default: return item.approval_status;
                    }
                }),
                datasets: [{
                    data: propertyStatusData.map(item => item.count),
                    backgroundColor: ['#ffc107', '#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueData = <?php echo json_encode($revenueData); ?>;
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: revenueData.map(item => item.date),
                datasets: [{
                    label: 'Ingresos (MXN)',
                    data: revenueData.map(item => item.revenue),
                    backgroundColor: '#28a745',
                    borderColor: '#28a745',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Property Type Chart
        const propertyTypeCtx = document.getElementById('propertyTypeChart').getContext('2d');
        const propertyTypeData = <?php echo json_encode($propertyStats['by_type']); ?>;
        new Chart(propertyTypeCtx, {
            type: 'pie',
            data: {
                labels: propertyTypeData.map(item => item.property_type || 'Sin tipo'),
                datasets: [{
                    data: propertyTypeData.map(item => item.count),
                    backgroundColor: [
                        '#667eea', '#764ba2', '#f093fb', '#f5576c',
                        '#4facfe', '#00f2fe', '#43e97b', '#38f9d7'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        function exportReport(format) {
            const startDate = '<?php echo $startDate; ?>';
            const endDate = '<?php echo $endDate; ?>';

            // This would typically call an export API
            alert(`Exportando reporte en formato ${format.toUpperCase()}...\nPeríodo: ${startDate} - ${endDate}`);

            // For now, we'll simulate the export
            // In a real implementation, this would redirect to an export endpoint
        }
    </script>
</body>
</html>