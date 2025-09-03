<?php
// Include header and property card component
include __DIR__ . '/../header.php';
include __DIR__ . '/../property-card.php';
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
                        <p><?php echo htmlspecialchars($userProfile['phone_number']); ?></p>
                        <p class="user-type">Comprador</p>
                    </div>
                </div>
                <div class="profile-bio">
                    <h4>Sobre mí</h4>
                    <p><?php echo htmlspecialchars($userProfile['bio'] ?? 'No hay biografía disponible.'); ?></p>
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
                                <h4><?php echo htmlspecialchars(ucfirst($alert['type']) . ' Alert'); ?></h4>
                                <p><?php echo htmlspecialchars($alert['criteria']); ?></p>
                            </div>
                            <div class="alert-actions">
                                <button class="btn btn-sm" onclick="editAlert(<?php echo $alert['id'] ?? 0; ?>)">Editar</button>
                                <button class="btn btn-sm btn-danger" onclick="deleteAlert(<?php echo $alert['id'] ?? 0; ?>)">Eliminar</button>
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

<!-- Include CSS and JS -->
<link rel="stylesheet" href="/assets/css/dashboard.css">
<script src="/assets/js/dashboard.js"></script>

<?php include __DIR__ . '/../footer.php'; ?>