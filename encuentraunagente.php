<?php
$pageTitle = 'Encuentra un Agente Inmobiliario - Tierras.mx';
include 'header.php';
include 'agent-card.php';

// Sample agents data
$agents = [
    [
        'id' => 'agent-001',
        'name' => 'Juan Martínez',
        'company' => 'RE/MAX Capital',
        'location' => 'Polanco, CDMX',
        'rating' => 4.8,
        'reviewCount' => 245,
        'avatar' => 'https://randomuser.me/api/portraits/men/32.jpg',
        'specialties' => ['Casas Residenciales', 'Departamentos', 'Primera Vez', 'Inversión'],
        'phone' => '+52 55 1234 5678',
        'email' => 'juan.martinez@remax.mx',
        'properties' => 87,
        'experience' => 12
    ],
    [
        'id' => 'agent-002',
        'name' => 'María González',
        'company' => 'Century 21',
        'location' => 'Condesa, CDMX',
        'rating' => 4.9,
        'reviewCount' => 189,
        'avatar' => 'https://randomuser.me/api/portraits/women/44.jpg',
        'specialties' => ['Departamentos', 'Lujo', 'Inversión'],
        'phone' => '+52 55 8765 4321',
        'email' => 'maria.gonzalez@c21.mx',
        'properties' => 65,
        'experience' => 8
    ],
    [
        'id' => 'agent-003',
        'name' => 'Carlos Rodríguez',
        'company' => 'Keller Williams',
        'location' => 'Santa Fe, CDMX',
        'rating' => 4.7,
        'reviewCount' => 156,
        'avatar' => 'https://randomuser.me/api/portraits/men/22.jpg',
        'specialties' => ['Comerciales', 'Oficinas', 'Locales'],
        'phone' => '+52 55 5555 1234',
        'email' => 'carlos.rodriguez@kw.mx',
        'properties' => 42,
        'experience' => 15
    ]
];
?>

<main class="container">
    <section class="section">
        <div class="section-header">
            <h1 class="section-title">Encuentra un Agente Inmobiliario de Confianza</h1>
            <p class="section-subtitle">Conoce a profesionales que pueden guiarte en cada paso de tu proceso</p>
        </div>

        <!-- Search and Filters -->
        <div class="agents-filters">
            <div class="search-bar">
                <div class="search-container">
                    <div class="search-filters">
                        <div class="filter-group">
                            <label for="location-search" class="filter-label">Ubicación</label>
                            <input type="text" id="location-search" class="search-input" placeholder="Ingresa ciudad o zona">
                        </div>

                        <div class="filter-group">
                            <label for="specialty-filter" class="filter-label">Especialidad</label>
                            <select id="specialty-filter" class="filter-select">
                                <option value="all">Todas las especialidades</option>
                                <option value="residencial">Casas Residenciales</option>
                                <option value="departamentos">Departamentos</option>
                                <option value="comercial">Comercial</option>
                                <option value="lujo">Propiedades de Lujo</option>
                            </select>
                        </div>

                        <div class="filter-group" style="align-self: flex-end;">
                            <button class="search-button">
                                <i class="fas fa-search"></i> Buscar Agentes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agents Grid -->
        <div class="agents-grid">
            <?php foreach ($agents as $agent): ?>
                <?php renderAgentCard($agent); ?>
            <?php endforeach; ?>
        </div>

        <!-- Load More -->
        <div class="text-center" style="margin-top: 3rem;">
            <button class="btn btn-primary btn-large">
                <i class="fas fa-plus"></i> Cargar Más Agentes
            </button>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section services" style="background-color: var(--gray-50);">
        <div class="section-header">
            <h2 class="section-title">¿Por Qué Elegir un Agente Certificado?</h2>
            <p class="section-subtitle">Los mejores agentes inmobiliarios en México te ofrecen</p>
        </div>

        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3 class="service-title">Experiencia Local</h3>
                <p class="service-description">Conocen a fondo el mercado inmobiliario de tu zona y las mejores oportunidades disponibles.</p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3 class="service-title">Servicio Personalizado</h3>
                <p class="service-description">Te guían en cada paso del proceso, desde la búsqueda hasta el cierre de la transacción.</p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="service-title">Confianza y Seguridad</h3>
                <p class="service-description">Agentes certificados y verificados que garantizan transacciones seguras y transparentes.</p>
            </div>
        </div>
    </section>
</main>

<style>
.agents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.agent-card {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: var(--transition);
    border: 1px solid var(--gray-200);
}

.agent-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.agent-header {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-100);
}

.agent-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    border: 3px solid var(--primary);
}

.agent-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.agent-info {
    flex: 1;
}

.agent-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: 0.25rem;
}

.agent-company {
    font-size: 0.875rem;
    color: var(--gray-600);
    margin-bottom: 0.5rem;
}

.agent-location {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--gray-600);
    font-size: 0.875rem;
    margin-bottom: 0.75rem;
}

.agent-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.rating-stars {
    color: #f59e0b;
    font-size: 1rem;
}

.rating-count {
    color: var(--gray-600);
    font-size: 0.875rem;
}

.agent-stats {
    display: flex;
    justify-content: space-around;
    padding: 1rem 1.5rem;
    background-color: var(--gray-50);
    border-bottom: 1px solid var(--gray-100);
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary);
    display: block;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--gray-600);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.agent-specialties {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0 1.5rem 1rem;
}

.specialty-tag {
    background-color: var(--gray-100);
    color: var(--gray-700);
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: 500;
}

.agent-actions {
    display: flex;
    gap: 0.5rem;
    padding: 1rem 1.5rem 1.5rem;
}

.agent-actions .btn-action {
    flex: 1;
    padding: 0.75rem;
    font-size: 0.875rem;
    justify-content: center;
}

.agents-filters {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    padding: 2rem;
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .agents-grid {
        grid-template-columns: 1fr;
    }

    .agent-header {
        flex-direction: column;
        text-align: center;
    }

    .agent-stats {
        flex-direction: column;
        gap: 1rem;
    }

    .agent-actions {
        flex-direction: column;
    }
}
</style>

<?php
include 'footer.php';
?>