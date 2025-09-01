<?php
$pageTitle = 'Casas Abiertas - Tierras.mx';
include 'header.php';
?>

<!-- Open House Banner -->
<div class="open-house-banner">
    <div class="open-house-banner-content">
        <div class="open-house-banner-text">
            <h2 class="open-house-banner-title">Casas Abiertas este fin de semana</h2>
            <p class="open-house-banner-subtitle">Visita propiedades en persona sin cita previa. Consulta los horarios y direcciones para planificar tu visita.</p>
        </div>
        <div class="open-house-banner-actions">
            <button class="btn-banner">Ver calendario</button>
            <button class="btn-banner">Recibir alertas</button>
        </div>
    </div>
</div>

<main class="container">
    <div class="main-content">
        <!-- Filters Sidebar -->
        <aside class="filters-sidebar">
            <div class="filter-section">
                <h3 class="filter-title">
                    Fecha de Casa Abierta
                    <button>Restablecer</button>
                </h3>
                <div class="date-range">
                    <input type="date" class="date-input" id="start-date">
                    <input type="date" class="date-input" id="end-date">
                </div>
            </div>

            <div class="filter-section">
                <h3 class="filter-title">
                    Hora
                    <button>Restablecer</button>
                </h3>
                <div class="checkbox-group">
                    <label class="checkbox-item">
                        <input type="checkbox" checked> Mañana (8am - 12pm)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" checked> Tarde (12pm - 5pm)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox"> Noche (5pm - 8pm)
                    </label>
                </div>
            </div>

            <div class="filter-section">
                <h3 class="filter-title">
                    Tipo de Propiedad
                    <button>Restablecer</button>
                </h3>
                <div class="checkbox-group">
                    <label class="checkbox-item">
                        <input type="checkbox" checked> Casa
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" checked> Departamento
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox"> Terreno
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox"> Local Comercial
                    </label>
                </div>
            </div>

            <div class="filter-section">
                <h3 class="filter-title">
                    Características
                    <button>Restablecer</button>
                </h3>
                <div class="checkbox-group">
                    <label class="checkbox-item">
                        <input type="checkbox" checked> Seguridad 24/7
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" checked> Estacionamiento
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox"> Gas Natural
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox"> Agua Potable
                    </label>
                </div>
            </div>

            <button class="apply-filters">Aplicar Filtros</button>
        </aside>

        <!-- Property Results -->
        <div class="property-results">
            <div class="results-header">
                <div>
                    <h1 class="results-title">Casas Abiertas</h1>
                    <div class="results-count">147 propiedades encontradas</div>
                </div>

                <div class="view-options">
                    <button class="view-option active">
                        <i class="fas fa-th-large"></i>
                        <span class="sr-only">Vista de cuadrícula</span>
                    </button>
                    <button class="view-option">
                        <i class="fas fa-list"></i>
                        <span class="sr-only">Vista de lista</span>
                    </button>
                    <button class="view-option">
                        <i class="fas fa-map-marked-alt"></i>
                        <span class="sr-only">Vista de mapa</span>
                    </button>
                </div>

                <div class="sort-options">
                    <span class="sort-label">Ordenar por:</span>
                    <select class="sort-select">
                        <option>Fecha de Casa Abierta</option>
                        <option>Precio (menor a mayor)</option>
                        <option>Precio (mayor a menor)</option>
                        <option>Más reciente</option>
                    </select>
                </div>
            </div>

            <!-- Property Grid -->
            <div class="properties-grid" id="propertyGrid">
                <!-- Property Card 1 -->
                <div class="property-card" data-id="mx-cdmx-polanco-123-5500000">
                    <div class="property-image">
                        <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                             alt="Departamento moderno en Polanco, CDMX">
                        <div class="property-badge sale">En Venta</div>
                        <div class="property-badge mx">
                            <i class="fas fa-flag"></i> México
                        </div>
                        <div class="open-house-time">
                            <i class="far fa-calendar"></i> Sáb 10:00 AM - 1:00 PM
                        </div>
                        <button class="favorite-btn" aria-label="Guardar propiedad como favorita">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="property-content">
                        <div class="property-price">
                            <i class="fas fa-coins" aria-hidden="true"></i>
                            <span class="mxn-price">$5,500,000 MXN</span>
                        </div>
                        <div class="property-address">
                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                            Polanco, Miguel Hidalgo
                        </div>
                        <div class="location-hierarchy">
                            <span class="location-item">CDMX</span>
                            <span class="location-separator">></span>
                            <span class="location-item">Miguel Hidalgo</span>
                            <span class="location-separator">></span>
                            <span class="location-item">Polanco</span>
                        </div>
                        <div class="property-stats">
                            <div class="stat">
                                <i class="fas fa-bed" aria-hidden="true"></i>
                                2 recámaras
                            </div>
                            <div class="stat">
                                <i class="fas fa-bath" aria-hidden="true"></i>
                                2 baños
                            </div>
                            <div class="stat">
                                <i class="fas fa-ruler-combined" aria-hidden="true"></i>
                                85 m²
                            </div>
                        </div>
                        <div class="property-features">
                            <span class="feature-badge seguridad">
                                <i class="fas fa-shield-alt" aria-hidden="true"></i> Seguridad 24/7
                            </span>
                            <span class="feature-badge">
                                <i class="fas fa-car" aria-hidden="true"></i> Estacionamiento
                            </span>
                        </div>
                        <p class="property-description">Hermoso departamento moderno en Polanco con vista a la ciudad, excelente ubicación cerca de centros comerciales y restaurantes.</p>
                        <a href="#" class="view-property">
                            Ver propiedad <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            <span class="sr-only">en Polanco, Miguel Hidalgo</span>
                        </a>
                    </div>
                </div>

                <!-- Property Card 2 -->
                <div class="property-card" data-id="mx-jalisco-guadalajara-456-3200000">
                    <div class="property-image">
                        <img src="https://images.unsplash.com/photo-1600596542815-4a3d32c18c3b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                             alt="Casa familiar en Providencia, Guadalajara">
                        <div class="property-badge sale">En Venta</div>
                        <div class="property-badge mx">
                            <i class="fas fa-flag"></i> México
                        </div>
                        <div class="open-house-time">
                            <i class="far fa-calendar"></i> Dom 1:00 PM - 4:00 PM
                        </div>
                        <button class="favorite-btn" aria-label="Guardar propiedad como favorita">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="property-content">
                        <div class="property-price">
                            <i class="fas fa-coins" aria-hidden="true"></i>
                            <span class="mxn-price">$3,200,000 MXN</span>
                        </div>
                        <div class="property-address">
                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                            Providencia, Guadalajara
                        </div>
                        <div class="location-hierarchy">
                            <span class="location-item">Jalisco</span>
                            <span class="location-separator">></span>
                            <span class="location-item">Guadalajara</span>
                            <span class="location-separator">></span>
                            <span class="location-item">Providencia</span>
                        </div>
                        <div class="property-stats">
                            <div class="stat">
                                <i class="fas fa-bed" aria-hidden="true"></i>
                                3 recámaras
                            </div>
                            <div class="stat">
                                <i class="fas fa-bath" aria-hidden="true"></i>
                                2.5 baños
                            </div>
                            <div class="stat">
                                <i class="fas fa-ruler-combined" aria-hidden="true"></i>
                                180 m²
                            </div>
                        </div>
                        <div class="property-features">
                            <span class="feature-badge seguridad">
                                <i class="fas fa-shield-alt" aria-hidden="true"></i> Seguridad
                            </span>
                            <span class="feature-badge">
                                <i class="fas fa-utensils" aria-hidden="true"></i> Gas Natural
                            </span>
                            <span class="feature-badge">
                                <i class="fas fa-tint" aria-hidden="true"></i> Agua Potable
                            </span>
                        </div>
                        <p class="property-description">Casa familiar en zona segura de Providencia, con jardín privado y acabados de calidad. Cerca de escuelas y centros comerciales.</p>
                        <a href="#" class="view-property">
                            Ver propiedad <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            <span class="sr-only">en Providencia, Guadalajara</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <ul class="pagination-list">
                    <li class="pagination-item">
                        <a href="#" aria-label="Página anterior">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <li class="pagination-item active"><a href="#">1</a></li>
                    <li class="pagination-item"><a href="#">2</a></li>
                    <li class="pagination-item"><a href="#">3</a></li>
                    <li class="pagination-item"><a href="#">4</a></li>
                    <li class="pagination-item"><a href="#">5</a></li>
                    <li class="pagination-item">
                        <a href="#" aria-label="Página siguiente">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Recommendation System -->
    <section class="recommendation-system" aria-labelledby="recommendations-heading" id="recommendationsSection">
        <div class="container">
            <div class="recommendation-header">
                <h2 id="recommendations-heading" class="section-title">Casas Abiertas que Tal Vez Te Gusten</h2>
                <p class="section-subtitle">Basado en tus búsquedas recientes y propiedades vistas</p>
            </div>

            <div class="recommendation-filters" role="search" aria-label="Filtros de recomendaciones">
                <div class="filter-group">
                    <label for="location-filter" class="filter-label">Ubicación:</label>
                    <select id="location-filter" class="filter-select">
                        <option value="all">Todas las ubicaciones</option>
                        <option value="cdmx">Ciudad de México</option>
                        <option value="guadalajara">Guadalajara</option>
                        <option value="monterrey">Monterrey</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="date-filter" class="filter-label">Fecha:</label>
                    <select id="date-filter" class="filter-select">
                        <option value="all">Todas las fechas</option>
                        <option value="this-weekend">Este fin de semana</option>
                        <option value="next-weekend">Próximo fin de semana</option>
                    </select>
                </div>
            </div>

            <div class="properties-grid" id="recommendationsGrid">
                <div class="recommendations-empty">
                    <div class="recommendations-empty-icon">
                        <i class="fas fa-home" aria-hidden="true"></i>
                    </div>
                    <h3 class="recommendations-empty-title">Sin recomendaciones aún</h3>
                    <p class="recommendations-empty-text">Navega por las casas abiertas para ver recomendaciones personalizadas basadas en tus preferencias.</p>
                </div>
            </div>

            <div class="recommendation-info" aria-hidden="true">
                <h3 class="info-title">¿Cómo funcionan estas recomendaciones?</h3>
                <p class="info-text">Las recomendaciones se basan en tu ubicación y actividad de búsqueda, como las propiedades que has visto y guardado y los filtros que has utilizado. Usamos esta información para mostrarte propiedades similares, para que no te pierdas ninguna oportunidad.</p>
            </div>
        </div>
    </section>
</main>

<?php
$additionalJs = '<script src="assets/js/recommendations.js"></script>';
include 'footer.php';
?>