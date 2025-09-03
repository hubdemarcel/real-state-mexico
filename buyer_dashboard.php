<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is a buyer
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html?status=error&message=Debes iniciar sesión para acceder al panel de comprador.');
    exit();
}

if ($_SESSION['user_type'] !== 'buyer') {
    header('Location: user_dashboard.php?status=error&message=Esta página es solo para compradores.');
    exit();
}

$pageTitle = 'Panel de Comprador - Tierras.mx';
include 'header.php';
include 'property-card.php';

// Get user information
$user_id = $_SESSION['id'];
$username = $_SESSION['username'];
$user_type = $_SESSION['user_type'];

// Get user profile info
$userProfile = [
    'email' => '',
    'first_name' => '',
    'last_name' => '',
    'phone' => '',
    'bio' => ''
];

$user_sql = "SELECT email, first_name, last_name, phone_number, bio FROM users WHERE id = ?";
if ($stmt = $conn->prepare($user_sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $userProfile = [
            'email' => $user_data['email'] ?? '',
            'first_name' => $user_data['first_name'] ?? '',
            'last_name' => $user_data['last_name'] ?? '',
            'phone' => $user_data['phone_number'] ?? '',
            'bio' => $user_data['bio'] ?? ''
        ];
    }
    $stmt->close();
}

// Get saved properties
$savedProperties = [];
$saved_sql = "SELECT p.* FROM properties p
              INNER JOIN user_saved_properties usp ON p.id = usp.property_id
              WHERE usp.user_id = ? ORDER BY usp.saved_at DESC";
if ($stmt = $conn->prepare($saved_sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $savedProperties[] = $row;
    }
    $stmt->close();
}

// Get favorite properties
$favoriteProperties = [];
$fav_sql = "SELECT p.* FROM properties p
            INNER JOIN user_favorites uf ON p.id = uf.property_id
            WHERE uf.user_id = ? ORDER BY uf.favorited_at DESC";
if ($stmt = $conn->prepare($fav_sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $favoriteProperties[] = $row;
    }
    $stmt->close();
}

// Get search history
$searchHistory = [];
$history_sql = "SELECT search_query, searched_at FROM user_search_history
                WHERE user_id = ? ORDER BY searched_at DESC LIMIT 10";
if ($stmt = $conn->prepare($history_sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $searchHistory[] = [
            'query' => $row['search_query'] ?? 'Búsqueda general',
            'date' => date('d/m/Y H:i', strtotime($row['searched_at']))
        ];
    }
    $stmt->close();
}

// Get user alerts
$userAlerts = [];
$alerts_sql = "SELECT alert_type, criteria, created_at FROM user_alerts
               WHERE user_id = ? AND is_active = 1 ORDER BY created_at DESC";
if ($stmt = $conn->prepare($alerts_sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $criteria = json_decode($row['criteria'], true);
        $userAlerts[] = [
            'title' => ucfirst($row['alert_type']) . ' Alert',
            'criteria' => is_array($criteria) ? implode(', ', $criteria) : $row['criteria'],
            'date' => date('d/m/Y', strtotime($row['created_at']))
        ];
    }
    $stmt->close();
}

// Get user messages
$userMessages = [];
$messages_sql = "SELECT um.*, u.username as sender_name FROM user_messages um
                 INNER JOIN users u ON um.sender_id = u.id
                 WHERE um.receiver_id = ? ORDER BY um.sent_at DESC LIMIT 10";
if ($stmt = $conn->prepare($messages_sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $userMessages[] = [
            'sender' => $row['sender_name'],
            'subject' => $row['subject'] ?? 'Sin asunto',
            'preview' => substr($row['message'], 0, 100) . (strlen($row['message']) > 100 ? '...' : ''),
            'date' => date('d/m/Y H:i', strtotime($row['sent_at'])),
            'is_read' => $row['is_read']
        ];
    }
    $stmt->close();
}

$conn->close();
?>

<main class="container">
    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <section class="dashboard-header">
            <div class="dashboard-welcome">
                <h1>¡Bienvenido, <?php echo htmlspecialchars($username); ?>!</h1>
                <p>Encuentra tu propiedad ideal y administra tus búsquedas</p>
            </div>
            <div class="dashboard-actions">
                <a href="#profile" class="btn btn-primary">Editar Perfil</a>
                <a href="comprar.html" class="btn btn-secondary">Buscar Propiedades</a>
            </div>
        </section>

        <!-- Dashboard Navigation -->
        <nav class="dashboard-nav">
            <ul class="dashboard-nav-list">
                <li><a href="#profile" class="dashboard-nav-link active">Perfil</a></li>
                <li><a href="#saved" class="dashboard-nav-link">Guardadas</a></li>
                <li><a href="#favorites" class="dashboard-nav-link">Favoritas</a></li>
                <li><a href="#history" class="dashboard-nav-link">Historial</a></li>
                <li><a href="#alerts" class="dashboard-nav-link">Alertas</a></li>
                <li><a href="#messages" class="dashboard-nav-link">Mensajes</a></li>
                <li><a href="basic_intelligence.php" class="dashboard-nav-link">📊 Mercado</a></li>
            </ul>
        </nav>

        <!-- Profile Section -->
        <section id="profile" class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Mi Perfil</h2>
                <p class="section-subtitle">Actualiza tu información personal</p>
            </div>
            <div class="profile-card">
                <div class="profile-info">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="profile-details">
                        <h3><?php echo htmlspecialchars($userProfile['first_name'] . ' ' . $userProfile['last_name']); ?></h3>
                        <p><?php echo htmlspecialchars($userProfile['email']); ?></p>
                        <p><?php echo htmlspecialchars($userProfile['phone']); ?></p>
                        <p class="user-type">Comprador</p>
                    </div>
                </div>
                <div class="profile-bio">
                    <h4>Sobre mí</h4>
                    <p><?php echo htmlspecialchars($userProfile['bio']); ?></p>
                </div>
                <div class="profile-actions">
                    <a href="edit_profile.php" class="btn btn-primary">Editar Perfil</a>
                    <a href="edit_profile.php" class="btn btn-secondary">Cambiar Contraseña</a>
                </div>
            </div>
        </section>

        <!-- Saved Properties Section -->
        <section id="saved" class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Propiedades Guardadas</h2>
                <p class="section-subtitle">Propiedades que has guardado para revisar más tarde</p>
            </div>
            <?php if (empty($savedProperties)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-bookmark"></i>
                    </div>
                    <h3 class="empty-state-title">No tienes propiedades guardadas</h3>
                    <p class="empty-state-text">Guarda propiedades que te interesen para acceder a ellas fácilmente.</p>
                    <a href="comprar.html" class="btn btn-primary">Buscar Propiedades</a>
                </div>
            <?php else: ?>
                <div class="properties-grid">
                    <?php foreach ($savedProperties as $property): ?>
                        <?php renderPropertyCard($property); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Favorites Section -->
        <section id="favorites" class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Propiedades Favoritas</h2>
                <p class="section-subtitle">Tus propiedades favoritas</p>
            </div>
            <?php if (empty($favoriteProperties)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="empty-state-title">No tienes propiedades favoritas</h3>
                    <p class="empty-state-text">Marca como favoritas las propiedades que más te gusten.</p>
                    <a href="comprar.html" class="btn btn-primary">Buscar Propiedades</a>
                </div>
            <?php else: ?>
                <div class="properties-grid">
                    <?php foreach ($favoriteProperties as $property): ?>
                        <?php renderPropertyCard($property); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Search History Section -->
        <section id="history" class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Historial de Búsqueda</h2>
                <p class="section-subtitle">Tus búsquedas recientes</p>
            </div>
            <?php if (empty($searchHistory)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="empty-state-title">No hay historial de búsqueda</h3>
                    <p class="empty-state-text">Tus búsquedas aparecerán aquí para que puedas repetirlas fácilmente.</p>
                    <a href="comprar.html" class="btn btn-primary">Buscar Propiedades</a>
                </div>
            <?php else: ?>
                <div class="search-history-list">
                    <?php foreach ($searchHistory as $search): ?>
                        <div class="search-history-item">
                            <div class="search-query">
                                <i class="fas fa-search"></i>
                                <span><?php echo htmlspecialchars($search['query']); ?></span>
                            </div>
                            <div class="search-date">
                                <?php echo htmlspecialchars($search['date']); ?>
                            </div>
                            <button class="btn btn-sm">Repetir Búsqueda</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Alerts Section -->
        <section id="alerts" class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Alertas de Propiedades</h2>
                <p class="section-subtitle">Recibe notificaciones sobre nuevas propiedades</p>
            </div>
            <?php if (empty($userAlerts)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3 class="empty-state-title">No tienes alertas activas</h3>
                    <p class="empty-state-text">Configura alertas para recibir notificaciones sobre nuevas propiedades que coincidan con tus criterios.</p>
                    <button class="btn btn-primary" onclick="createAlert()">Crear Alerta</button>
                </div>
            <?php else: ?>
                <div class="alerts-list">
                    <?php foreach ($userAlerts as $alert): ?>
                        <div class="alert-item">
                            <div class="alert-info">
                                <h4><?php echo htmlspecialchars($alert['title']); ?></h4>
                                <p><?php echo htmlspecialchars($alert['criteria']); ?></p>
                            </div>
                            <div class="alert-actions">
                                <button class="btn btn-sm" onclick="editAlert(<?php echo $alert['id']; ?>)">Editar</button>
                                <button class="btn btn-sm btn-danger" onclick="deleteAlert(<?php echo $alert['id']; ?>)">Eliminar</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Messages Section -->
        <section id="messages" class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Mensajes</h2>
                <p class="section-subtitle">Comunícate con agentes y otros usuarios</p>
            </div>
            <?php if (empty($userMessages)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3 class="empty-state-title">No tienes mensajes</h3>
                    <p class="empty-state-text">Los mensajes de agentes y otros usuarios aparecerán aquí.</p>
                    <a href="encuentraunagente.html" class="btn btn-primary">Contactar Agente</a>
                </div>
            <?php else: ?>
                <div class="messages-list">
                    <?php foreach ($userMessages as $message): ?>
                        <div class="message-item <?php echo $message['is_read'] ? '' : 'unread'; ?>">
                            <div class="message-sender">
                                <i class="fas fa-user"></i>
                                <span><?php echo htmlspecialchars($message['sender']); ?></span>
                            </div>
                            <div class="message-content">
                                <h4><?php echo htmlspecialchars($message['subject']); ?></h4>
                                <p><?php echo htmlspecialchars($message['preview']); ?></p>
                            </div>
                            <div class="message-date">
                                <?php echo htmlspecialchars($message['date']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
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
    color: #007bff;
    border-bottom-color: #007bff;
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

.profile-card {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.profile-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.profile-avatar {
    font-size: 4rem;
    color: #007bff;
}

.profile-details h3 {
    margin: 0;
    color: #333;
}

.profile-details p {
    margin: 0.25rem 0;
    color: #666;
}

.user-type {
    color: #007bff !important;
    font-weight: 500;
}

.profile-bio h4 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.profile-actions {
    display: flex;
    gap: 1rem;
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

.properties-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.search-history-list,
.alerts-list,
.messages-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.search-history-item,
.alert-item,
.message-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border: 1px solid #eee;
    border-radius: 8px;
    background: #f9f9f9;
}

.search-query {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.message-item.unread {
    background: #e3f2fd;
    border-color: #007bff;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.alert-form {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 1rem;
}

.alert-form .form-group {
    margin-bottom: 1rem;
}

.alert-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.alert-form input,
.alert-form select,
.alert-form textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
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

    .profile-info {
        flex-direction: column;
        text-align: center;
    }

    .search-history-item,
    .alert-item,
    .message-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .properties-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function createAlert() {
    const alertSection = document.getElementById('alerts');
    const existingForm = alertSection.querySelector('.alert-form');

    if (existingForm) {
        existingForm.remove();
        return;
    }

    const formHTML = `
        <div class="alert-form">
            <h4>Crear Nueva Alerta</h4>
            <form onsubmit="submitAlert(event)">
                <div class="form-group">
                    <label for="alert_type">Tipo de Alerta</label>
                    <select id="alert_type" name="alert_type" required>
                        <option value="price">Por Precio</option>
                        <option value="location">Por Ubicación</option>
                        <option value="property_type">Por Tipo de Propiedad</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="criteria">Criterios</label>
                    <textarea id="criteria" name="criteria" placeholder="Ej: CDMX, precio máximo 5M, casa" required></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Crear Alerta</button>
                    <button type="button" class="btn btn-secondary" onclick="cancelAlertForm()">Cancelar</button>
                </div>
            </form>
        </div>
    `;

    alertSection.insertBefore(
        document.createRange().createContextualFragment(formHTML),
        alertSection.querySelector('.alerts-list') || alertSection.querySelector('.empty-state').nextSibling
    );
}

function submitAlert(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const alertType = formData.get('alert_type');
    const criteria = formData.get('criteria');

    fetch('create_alert.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Alerta creada exitosamente', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al crear la alerta', 'error');
    });
}

function cancelAlertForm() {
    const form = document.querySelector('.alert-form');
    if (form) {
        form.remove();
    }
}

function editAlert(alertId) {
    // For now, just show a message. In a full implementation, this would open an edit form
    showNotification('Función de edición próximamente disponible', 'info');
}

function deleteAlert(alertId) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta alerta?')) {
        return;
    }

    const formData = new FormData();
    formData.append('alert_id', alertId);

    fetch('delete_alert.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Alerta eliminada exitosamente', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al eliminar la alerta', 'error');
    });
}

// Make createAlert function globally available
window.createAlert = createAlert;
window.editAlert = editAlert;
window.deleteAlert = deleteAlert;
window.cancelAlertForm = cancelAlertForm;
window.submitAlert = submitAlert;
</script>

<?php
include 'footer.php';
?>