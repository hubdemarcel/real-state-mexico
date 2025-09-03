<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html?status=error&message=Debes iniciar sesión para acceder al panel de vendedor.');
    exit();
}

if ($_SESSION['user_type'] !== 'seller') {
    header('Location: user_dashboard.php?status=error&message=Esta página es solo para vendedores.');
    exit();
}

$pageTitle = 'Panel de Vendedor - Tierras.mx';
include 'header.php';
include 'property-card.php';

// Get seller information
$user_id = $_SESSION['id'];
$username = $_SESSION['username'];

// Get seller profile info
$sellerProfile = [
    'email' => '',
    'first_name' => '',
    'last_name' => '',
    'phone' => '',
    'bio' => ''
];

$seller_sql = "SELECT email, first_name, last_name, phone_number, bio FROM users WHERE id = ?";
if ($stmt = $conn->prepare($seller_sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $seller_data = $result->fetch_assoc();
        $sellerProfile = [
            'email' => $seller_data['email'] ?? '',
            'first_name' => $seller_data['first_name'] ?? '',
            'last_name' => $seller_data['last_name'] ?? '',
            'phone' => $seller_data['phone_number'] ?? '',
            'bio' => $seller_data['bio'] ?? ''
        ];
    }
    $stmt->close();
}

// Get seller's properties
$sellerProperties = [];
$properties_sql = "SELECT p.* FROM properties p WHERE p.agent_id = ?";
if ($stmt = $conn->prepare($properties_sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $sellerProperties[] = $row;
    }
    $stmt->close();
}

// Get client leads (messages from potential buyers)
$clientLeads = [];
$leads_sql = "SELECT um.*, u.username as client_name, u.email as client_email
              FROM user_messages um
              INNER JOIN users u ON um.sender_id = u.id
              WHERE um.receiver_id = ? ORDER BY um.sent_at DESC LIMIT 20";
if ($stmt = $conn->prepare($leads_sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $clientLeads[] = $row;
    }
    $stmt->close();
}

// Get property performance analytics
$analytics = [
    'total_views' => 0,
    'total_inquiries' => 0,
    'properties_sold_this_month' => 0,
    'average_response_time' => 0,
    'conversion_rate' => 0
];

// For demo purposes, we'll use placeholder data
$analytics = [
    'total_views' => count($sellerProperties) * 35,
    'total_inquiries' => count($clientLeads),
    'properties_sold_this_month' => 1,
    'average_response_time' => 3.2,
    'conversion_rate' => 12.5
];

$conn->close();
?>

<main class="container">
    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <section class="dashboard-header">
            <div class="dashboard-welcome">
                <h1>¡Bienvenido, <?php echo htmlspecialchars($username); ?>!</h1>
                <p>Administra tus propiedades en venta y conecta con compradores</p>
            </div>
            <div class="dashboard-actions">
                <a href="#add-property" class="btn btn-primary">Agregar Propiedad</a>
                <a href="#analytics" class="btn btn-secondary">Ver Analytics</a>
            </div>
        </section>

        <!-- Dashboard Navigation -->
        <nav class="dashboard-nav">
            <ul class="dashboard-nav-list">
                <li><a href="#overview" class="dashboard-nav-link active">Resumen</a></li>
                <li><a href="#properties" class="dashboard-nav-link">Mis Propiedades</a></li>
                <li><a href="#leads" class="dashboard-nav-link">Interesados</a></li>
                <li><a href="#analytics" class="dashboard-nav-link">Analytics</a></li>
                <li><a href="#profile" class="dashboard-nav-link">Mi Perfil</a></li>
                <li><a href="#messages" class="dashboard-nav-link">Mensajes</a></li>
                <li><a href="basic_intelligence.php" class="dashboard-nav-link">📊 Mercado</a></li>
            </ul>
        </nav>

        <!-- Overview Section -->
        <section id="overview" class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Resumen de Actividad</h2>
                <p class="section-subtitle">Tu rendimiento en las últimas semanas</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($analytics['total_views']); ?></div>
                        <div class="stat-label">Vistas Totales</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $analytics['total_inquiries']; ?></div>
                        <div class="stat-label">Consultas Recibidas</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $analytics['properties_sold_this_month']; ?></div>
                        <div class="stat-label">Propiedades Vendidas (Mes)</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $analytics['average_response_time']; ?>h</div>
                        <div class="stat-label">Tiempo de Respuesta</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Properties Section -->
        <section id="properties" class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Mis Propiedades</h2>
                <p class="section-subtitle">Administra tus propiedades listadas</p>
                <a href="add_property.php" class="btn btn-primary">Agregar Nueva Propiedad</a>
            </div>

            <?php if (empty($sellerProperties)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3 class="empty-state-title">No tienes propiedades listadas</h3>
                    <p class="empty-state-text">Comienza agregando tu primera propiedad para atraer compradores potenciales.</p>
                    <a href="add_property.php" class="btn btn-primary">Agregar Primera Propiedad</a>
                </div>
            <?php else: ?>
                <div class="properties-grid">
                    <?php foreach ($sellerProperties as $property): ?>
                        <div class="property-management-card">
                            <?php renderPropertyCard($property); ?>
                            <div class="property-actions">
                                <button class="btn btn-sm btn-secondary" onclick="editProperty(<?php echo $property['id']; ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteProperty(<?php echo $property['id']; ?>)">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                                <button class="btn btn-sm" onclick="viewAnalytics(<?php echo $property['id']; ?>)">
                                    <i class="fas fa-chart-bar"></i> Analytics
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Client Leads Section -->
        <section id="leads" class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Compradores Interesados</h2>
                <p class="section-subtitle">Contactos interesados en tus propiedades</p>
            </div>

            <?php if (empty($clientLeads)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="empty-state-title">No hay compradores interesados aún</h3>
                    <p class="empty-state-text">Los compradores interesados en tus propiedades aparecerán aquí.</p>
                </div>
            <?php else: ?>
                <div class="leads-list">
                    <?php foreach ($clientLeads as $lead): ?>
                        <div class="lead-card <?php echo $lead['is_read'] ? '' : 'unread'; ?>">
                            <div class="lead-header">
                                <div class="lead-client">
                                    <i class="fas fa-user"></i>
                                    <span><?php echo htmlspecialchars($lead['client_name']); ?></span>
                                </div>
                                <div class="lead-date">
                                    <?php echo date('d/m/Y H:i', strtotime($lead['sent_at'])); ?>
                                </div>
                            </div>
                            <div class="lead-content">
                                <h4><?php echo htmlspecialchars($lead['subject'] ?? 'Sin asunto'); ?></h4>
                                <p><?php echo htmlspecialchars(substr($lead['message'], 0, 150) . (strlen($lead['message']) > 150 ? '...' : '')); ?></p>
                            </div>
                            <div class="lead-actions">
                                <button class="btn btn-sm" onclick="viewLead(<?php echo $lead['id']; ?>)">
                                    <i class="fas fa-eye"></i> Ver Detalles
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="respondToLead(<?php echo $lead['id']; ?>)">
                                    <i class="fas fa-reply"></i> Responder
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Analytics Section -->
        <section id="analytics" class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Analytics de Rendimiento</h2>
                <p class="section-subtitle">Estadísticas detalladas de tus propiedades</p>
            </div>

            <div class="analytics-grid">
                <div class="analytics-card">
                    <h3>Tasa de Conversión</h3>
                    <div class="analytics-value"><?php echo $analytics['conversion_rate']; ?>%</div>
                    <div class="analytics-trend positive">
                        <i class="fas fa-arrow-up"></i> +1.8% vs mes anterior
                    </div>
                </div>

                <div class="analytics-card">
                    <h3>Propiedades Más Vistas</h3>
                    <div class="analytics-list">
                        <?php
                        $topProperties = array_slice($sellerProperties, 0, 3);
                        foreach ($topProperties as $property):
                        ?>
                            <div class="analytics-item">
                                <span><?php echo htmlspecialchars(substr($property['title'], 0, 30)); ?>...</span>
                                <span><?php echo rand(15, 80); ?> vistas</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="analytics-card">
                    <h3>Ingresos Mensuales</h3>
                    <div class="analytics-value">$<?php echo number_format(rand(50000, 200000)); ?> MXN</div>
                    <div class="analytics-trend positive">
                        <i class="fas fa-arrow-up"></i> +8% vs mes anterior
                    </div>
                </div>
            </div>
        </section>

        <!-- Profile Section -->
        <section id="profile" class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Mi Perfil de Vendedor</h2>
                <p class="section-subtitle">Actualiza tu información profesional</p>
            </div>

            <div class="profile-card">
                <div class="profile-info">
                    <div class="profile-avatar">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="profile-details">
                        <h3><?php echo htmlspecialchars($sellerProfile['first_name'] . ' ' . $sellerProfile['last_name']); ?></h3>
                        <p><?php echo htmlspecialchars($sellerProfile['email']); ?></p>
                        <p><?php echo htmlspecialchars($sellerProfile['phone']); ?></p>
                        <p class="user-type">Vendedor</p>
                    </div>
                </div>

                <div class="profile-bio">
                    <h4>Sobre mí</h4>
                    <p><?php echo htmlspecialchars($sellerProfile['bio'] ?? 'No hay biografía disponible.'); ?></p>
                </div>

                <div class="profile-actions">
                    <a href="edit_profile.php" class="btn btn-primary">Editar Perfil</a>
                    <a href="edit_profile.php" class="btn btn-secondary">Cambiar Contraseña</a>
                </div>
            </div>
        </section>

        <!-- Messages Section -->
        <section id="messages" class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Mensajes</h2>
                <p class="section-subtitle">Comunícate con tus compradores</p>
            </div>

            <div class="messages-container">
                <div class="messages-sidebar">
                    <div class="messages-list">
                        <?php foreach ($clientLeads as $lead): ?>
                            <div class="message-preview <?php echo $lead['is_read'] ? '' : 'unread'; ?>" onclick="openConversation(<?php echo $lead['sender_id']; ?>)">
                                <div class="message-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="message-info">
                                    <div class="message-sender"><?php echo htmlspecialchars($lead['client_name']); ?></div>
                                    <div class="message-snippet"><?php echo htmlspecialchars(substr($lead['message'], 0, 50) . '...'); ?></div>
                                </div>
                                <div class="message-time"><?php echo date('H:i', strtotime($lead['sent_at'])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="messages-content">
                    <div class="message-thread">
                        <div class="text-center" style="padding: 2rem;">
                            <i class="fas fa-comments" style="font-size: 3rem; color: #ddd;"></i>
                            <p>Selecciona una conversación para ver los mensajes</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<style>
.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 0;
}

.dashboard-header {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    color: white;
    padding: 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dashboard-welcome h1 {
    margin: 0;
    font-size: 2rem;
}

.dashboard-welcome p {
    margin: 0.5rem 0 0 0;
    opacity: 0.9;
}

.dashboard-actions {
    display: flex;
    gap: 1rem;
}

.dashboard-nav {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    overflow-x: auto;
}

.dashboard-nav-list {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
}

.dashboard-nav-link {
    padding: 1rem 1.5rem;
    text-decoration: none;
    color: #666;
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
    white-space: nowrap;
}

.dashboard-nav-link.active,
.dashboard-nav-link:hover {
    color: #28a745;
    border-bottom-color: #28a745;
}

.dashboard-section {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    padding: 2rem;
}

.section-header {
    margin-bottom: 2rem;
}

.section-title {
    margin: 0;
    font-size: 1.5rem;
    color: #333;
}

.section-subtitle {
    margin: 0.5rem 0 0 0;
    color: #666;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    font-size: 2rem;
    color: #28a745;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 10px;
}

.stat-content .stat-value {
    font-size: 2rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 0.25rem;
}

.stat-content .stat-label {
    color: #666;
    font-size: 0.9rem;
}

.properties-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.property-management-card {
    position: relative;
}

.property-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

.leads-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.lead-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid #28a745;
}

.lead-card.unread {
    border-left-color: #ffc107;
    background: #fffbf0;
}

.lead-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.lead-client {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

.lead-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.analytics-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.analytics-card h3 {
    margin: 0 0 1rem 0;
    color: #333;
}

.analytics-value {
    font-size: 2rem;
    font-weight: bold;
    color: #28a745;
    margin-bottom: 0.5rem;
}

.analytics-trend {
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.analytics-trend.positive {
    color: #28a745;
}

.analytics-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.analytics-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.profile-card {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.profile-info {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.profile-avatar {
    font-size: 4rem;
    color: #28a745;
    width: 100px;
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 50%;
}

.profile-details h3 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.user-type {
    color: #28a745 !important;
    font-weight: 500;
}

.detail-row {
    padding: 0.75rem 0;
    border-bottom: 1px solid #eee;
}

.detail-row strong {
    color: #333;
}

.profile-bio h4 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.messages-container {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 1rem;
    height: 600px;
}

.messages-sidebar {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.messages-list {
    max-height: 600px;
    overflow-y: auto;
}

.message-preview {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background 0.2s;
}

.message-preview:hover {
    background: #f8f9fa;
}

.message-preview.unread {
    background: #e8f5e8;
    border-left: 3px solid #28a745;
}

.message-avatar {
    width: 40px;
    height: 40px;
    background: #28a745;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.messages-content {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.message-thread {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
}

.empty-state-icon {
    font-size: 3rem;
    color: #ddd;
    margin-bottom: 1rem;
}

.empty-state-title {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.empty-state-text {
    margin: 0 0 1.5rem 0;
    color: #666;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background: #28a745;
    color: white;
}

.btn-primary:hover {
    background: #1e7e34;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }

    .dashboard-actions {
        flex-direction: column;
        width: 100%;
    }

    .dashboard-nav-list {
        flex-wrap: wrap;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .properties-grid {
        grid-template-columns: 1fr;
    }

    .analytics-grid {
        grid-template-columns: 1fr;
    }

    .profile-info {
        flex-direction: column;
        text-align: center;
    }

    .messages-container {
        grid-template-columns: 1fr;
        height: auto;
    }

    .lead-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .lead-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Navigation functionality
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.dashboard-nav-link');
    const sections = document.querySelectorAll('.dashboard-section');

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            // Remove active class from all links
            navLinks.forEach(l => l.classList.remove('active'));

            // Add active class to clicked link
            this.classList.add('active');

            // Hide all sections
            sections.forEach(section => section.style.display = 'none');

            // Show selected section
            const targetId = this.getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.style.display = 'block';
            }
        });
    });
});

// Property management functions
function editProperty(propertyId) {
    window.location.href = `edit_property.php?id=${propertyId}`;
}

function deleteProperty(propertyId) {
    if (confirm('¿Estás seguro de que quieres eliminar esta propiedad?')) {
        // Implement delete functionality
        alert('Función de eliminación próximamente disponible');
    }
}

function viewAnalytics(propertyId) {
    window.location.href = `property_analytics.php?id=${propertyId}`;
}

// Lead management functions
function viewLead(leadId) {
    // Implement view lead functionality
    alert('Función de ver interesado próximamente disponible');
}

function respondToLead(leadId) {
    // Implement respond to lead functionality
    alert('Función de responder próximamente disponible');
}

// Message functions
function openConversation(clientId) {
    // Implement open conversation functionality
    alert('Función de abrir conversación próximamente disponible');
}
</script>

<?php
include 'footer.php';
?>