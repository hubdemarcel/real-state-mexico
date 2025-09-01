<?php
require_once 'config.php';

$agent_id = $_GET['id'] ?? 0;
$pageTitle = 'Perfil de Agente - Tierras.mx';
include 'header.php';

// Get agent information
$agent_info = [];
$agent_properties = [];

if ($agent_id) {
    // Get agent details
    $sql = "SELECT u.username, u.email, a.* FROM users u
            INNER JOIN agents a ON u.id = a.user_id
            WHERE u.id = ? AND u.user_type = 'agent'";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $agent_info = $result->fetch_assoc();
        }
        $stmt->close();
    }

    // Get agent's properties
    if (!empty($agent_info)) {
        $prop_sql = "SELECT * FROM properties WHERE agent_id = ? ORDER BY created_at DESC LIMIT 6";
        if ($stmt = $conn->prepare($prop_sql)) {
            $stmt->bind_param("i", $agent_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $agent_properties[] = $row;
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<main class="container">
    <?php if (empty($agent_info)): ?>
        <div class="agent-not-found">
            <div class="not-found-content">
                <h1>Agente No Encontrado</h1>
                <p>El perfil de agente que buscas no existe o no está disponible.</p>
                <a href="encuentraunagente.php" class="btn btn-primary">Buscar Agentes</a>
            </div>
        </div>
    <?php else: ?>
        <div class="agent-public-profile">
            <!-- Agent Header -->
            <section class="agent-hero">
                <div class="agent-hero-content">
                    <div class="agent-main-info">
                        <div class="agent-avatar">
                            <?php if ($agent_info['profile_picture_url']): ?>
                                <img src="<?php echo htmlspecialchars($agent_info['profile_picture_url']); ?>" alt="Foto de <?php echo htmlspecialchars($agent_info['first_name'] . ' ' . $agent_info['last_name']); ?>">
                            <?php else: ?>
                                <i class="fas fa-user-tie"></i>
                            <?php endif; ?>
                        </div>
                        <div class="agent-details">
                            <h1><?php echo htmlspecialchars($agent_info['first_name'] . ' ' . $agent_info['last_name']); ?></h1>
                            <div class="agent-company"><?php echo htmlspecialchars($agent_info['company'] ?? 'Agente Independiente'); ?></div>
                            <div class="agent-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($agent_info['location'] ?? 'México'); ?>
                            </div>
                            <div class="agent-rating">
                                <div class="rating-stars">
                                    <?php
                                    $rating = $agent_info['rating'] ?? 0;
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
                                <span>(<?php echo $agent_info['review_count'] ?? 0; ?> reseñas)</span>
                            </div>
                        </div>
                    </div>

                    <div class="agent-cta">
                        <a href="contact_agent.php?agent_id=<?php echo $agent_id; ?>" class="btn btn-primary btn-large">
                            <i class="fas fa-envelope"></i> Contactar Agente
                        </a>
                        <a href="tel:<?php echo htmlspecialchars($agent_info['phone_number'] ?? ''); ?>" class="btn btn-secondary btn-large">
                            <i class="fas fa-phone"></i> Llamar
                        </a>
                    </div>
                </div>
            </section>

            <!-- Agent Stats -->
            <section class="agent-stats-section">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo count($agent_properties); ?>+</div>
                            <div class="stat-label">Propiedades</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $agent_info['experience_years'] ?? 0; ?> años</div>
                            <div class="stat-label">Experiencia</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo number_format($agent_info['total_sales'] ?? 0, 0); ?>M</div>
                            <div class="stat-label">Ventas Totales</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $agent_info['properties_sold'] ?? 0; ?></div>
                            <div class="stat-label">Propiedades Vendidas</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Agent Bio -->
            <section class="agent-bio-section">
                <div class="section-container">
                    <h2>Sobre <?php echo htmlspecialchars($agent_info['first_name']); ?></h2>
                    <div class="bio-content">
                        <p><?php echo nl2br(htmlspecialchars($agent_info['bio'] ?? 'Este agente no ha proporcionado una biografía aún.')); ?></p>
                    </div>

                    <?php if ($agent_info['specialties']): ?>
                        <div class="specialties-section">
                            <h3>Especialidades</h3>
                            <div class="specialties-tags">
                                <?php
                                $specialties = explode(',', $agent_info['specialties']);
                                foreach ($specialties as $specialty):
                                    $specialty = trim($specialty);
                                    if (!empty($specialty)):
                                ?>
                                    <span class="specialty-tag"><?php echo htmlspecialchars($specialty); ?></span>
                                <?php endif; endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="contact-info">
                        <h3>Información de Contacto</h3>
                        <div class="contact-details">
                            <?php if ($agent_info['phone_number']): ?>
                                <div class="contact-item">
                                    <i class="fas fa-phone"></i>
                                    <span><?php echo htmlspecialchars($agent_info['phone_number']); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo htmlspecialchars($agent_info['email']); ?></span>
                            </div>
                            <?php if ($agent_info['website']): ?>
                                <div class="contact-item">
                                    <i class="fas fa-globe"></i>
                                    <a href="<?php echo htmlspecialchars($agent_info['website']); ?>" target="_blank"><?php echo htmlspecialchars($agent_info['website']); ?></a>
                                </div>
                            <?php endif; ?>
                            <?php if ($agent_info['license_number']): ?>
                                <div class="contact-item">
                                    <i class="fas fa-id-card"></i>
                                    <span>Licencia: <?php echo htmlspecialchars($agent_info['license_number']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Agent Properties -->
            <?php if (!empty($agent_properties)): ?>
                <section class="agent-properties-section">
                    <div class="section-container">
                        <div class="section-header">
                            <h2>Propiedades de <?php echo htmlspecialchars($agent_info['first_name']); ?></h2>
                            <a href="comprar.php?agent=<?php echo $agent_id; ?>" class="btn btn-secondary">Ver Todas</a>
                        </div>

                        <div class="properties-grid">
                            <?php
                            include 'property-card.php';
                            foreach ($agent_properties as $property):
                                renderPropertyCard($property);
                            endforeach;
                            ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Contact CTA -->
            <section class="contact-cta-section">
                <div class="cta-container">
                    <h2>¿Interesado en una Propiedad?</h2>
                    <p>Contacta a <?php echo htmlspecialchars($agent_info['first_name']); ?> para obtener más información sobre estas y otras propiedades disponibles.</p>
                    <div class="cta-buttons">
                        <a href="contact_agent.php?agent_id=<?php echo $agent_id; ?>" class="btn btn-primary btn-large">
                            <i class="fas fa-envelope"></i> Enviar Mensaje
                        </a>
                        <a href="tel:<?php echo htmlspecialchars($agent_info['phone_number'] ?? ''); ?>" class="btn btn-secondary btn-large">
                            <i class="fas fa-phone"></i> Llamar Ahora
                        </a>
                    </div>
                </div>
            </section>
        </div>
    <?php endif; ?>
</main>

<style>
.agent-not-found {
    text-align: center;
    padding: 4rem 2rem;
    min-height: 50vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.not-found-content h1 {
    color: #333;
    margin-bottom: 1rem;
}

.not-found-content p {
    color: #666;
    margin-bottom: 2rem;
}

.agent-public-profile {
    max-width: 1200px;
    margin: 0 auto;
}

.agent-hero {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 3rem 0;
}

.agent-hero-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
    align-items: center;
}

.agent-main-info {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.agent-avatar {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid white;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    font-size: 4rem;
}

.agent-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.agent-details h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.agent-company {
    font-size: 1.25rem;
    opacity: 0.9;
    margin-bottom: 0.5rem;
}

.agent-location {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    opacity: 0.9;
}

.agent-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.rating-stars {
    color: #f59e0b;
    font-size: 1.25rem;
}

.agent-cta {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.agent-stats-section {
    padding: 3rem 0;
    background: #f8f9fa;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    max-width: 1000px;
    margin: 0 auto;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1.5rem;
    text-align: center;
}

.stat-icon {
    font-size: 3rem;
    color: #007bff;
    width: 80px;
    height: 80px;
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

.agent-bio-section {
    padding: 3rem 0;
}

.section-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 2rem;
}

.agent-bio-section h2 {
    font-size: 2rem;
    color: #333;
    margin-bottom: 2rem;
    text-align: center;
}

.bio-content {
    background: white;
    border-radius: 10px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.bio-content p {
    line-height: 1.6;
    color: #555;
}

.specialties-section h3 {
    font-size: 1.5rem;
    color: #333;
    margin-bottom: 1rem;
}

.specialties-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 2rem;
}

.specialty-tag {
    background: #007bff;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

.contact-info h3 {
    font-size: 1.5rem;
    color: #333;
    margin-bottom: 1rem;
}

.contact-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.contact-item i {
    color: #007bff;
    width: 20px;
}

.contact-item a {
    color: #007bff;
    text-decoration: none;
}

.contact-item a:hover {
    text-decoration: underline;
}

.agent-properties-section {
    padding: 3rem 0;
    background: #f8f9fa;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.section-header h2 {
    font-size: 2rem;
    color: #333;
}

.properties-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.contact-cta-section {
    padding: 3rem 0;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    text-align: center;
}

.cta-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 0 2rem;
}

.cta-container h2 {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.cta-container p {
    opacity: 0.9;
    margin-bottom: 2rem;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: white;
    color: #007bff;
}

.btn-primary:hover {
    background: #f8f9fa;
}

.btn-secondary {
    background: transparent;
    color: white;
    border: 2px solid white;
}

.btn-secondary:hover {
    background: white;
    color: #007bff;
}

@media (max-width: 768px) {
    .agent-hero-content {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 2rem;
    }

    .agent-main-info {
        flex-direction: column;
        gap: 1.5rem;
    }

    .agent-details h1 {
        font-size: 2rem;
    }

    .agent-cta {
        flex-direction: row;
        justify-content: center;
    }

    .btn-large {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .section-container {
        padding: 0 1rem;
    }

    .section-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .properties-grid {
        grid-template-columns: 1fr;
    }

    .cta-buttons {
        flex-direction: column;
    }

    .contact-details {
        gap: 0.5rem;
    }

    .contact-item {
        padding: 0.75rem;
    }
}
</style>

<?php
include 'footer.php';
?>