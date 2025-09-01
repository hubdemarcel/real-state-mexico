<?php
$pageTitle = 'Construcciones Nuevas - Tierras.mx';
include 'header.php';
?>

<section class="new-homes-banner">
    <div class="new-homes-banner-content">
        <h1 class="new-homes-banner-title">Encuentra tu Nueva Construcción en México</h1>
        <p class="new-homes-banner-text">Descubre las últimas propiedades de construcción nueva en las mejores zonas de México con acabados modernos y garantía de calidad.</p>
        <a href="#" class="btn-banner">Conoce los beneficios de comprar una casa nueva</a>
    </div>
</section>

<main class="container">
    <section class="section section-new-homes">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Construcciones Nuevas en México</h2>
                <p class="section-subtitle">Propiedades de construcción nueva con acabados modernos y garantía</p>
            </div>

            <!-- No Results State - This would be hidden when results exist -->
            <div class="no-results" id="noResults">
                <div class="no-results-icon">
                    <i class="fas fa-home"></i>
                </div>
                <h3 class="no-results-title">No se encontraron construcciones nuevas</h3>
                <p class="no-results-text">No pudimos encontrar resultados de construcciones nuevas en tu ubicación. Intenta cambiando tu búsqueda.</p>

                <div class="suggested-searches">
                    <div class="suggested-search">Construcciones Nuevas en CDMX</div>
                    <div class="suggested-search">Construcciones Nuevas en Guadalajara</div>
                    <div class="suggested-search">Construcciones Nuevas en Monterrey</div>
                    <div class="suggested-search">Construcciones Nuevas en Cancún</div>
                </div>

                <button class="btn-primary-large">
                    <i class="fas fa-search"></i> Realizar una nueva búsqueda
                </button>
            </div>

            <!-- Property Grid - This would be hidden when no results -->
            <div class="properties-grid" id="propertyGrid" style="display: none;">
                <!-- Property Card 1 - Mexico City -->
                <div class="property-card" data-id="mx-cdmx-santa-fe-123-8500000">
                    <div class="property-image">
                        <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                             alt="Departamento nuevo en Santa Fe, CDMX">
                        <div class="property-badge new">Nueva Construcción</div>
                        <div class="property-badge mx">México</div>
                        <button class="favorite-btn" aria-label="Guardar propiedad como favorita">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="property-content">
                        <div class="property-price">
                            <i class="fas fa-coins"></i>
                            <span class="mxn-price">$8,500,000 MXN</span>
                        </div>
                        <div class="property-address">
                            <i class="fas fa-map-marker-alt"></i>
                            Santa Fe, Cuajimalpa
                        </div>
                        <div class="location-hierarchy">
                            <span class="location-item">CDMX</span>
                            <span class="location-separator">></span>
                            <span class="location-item">Cuajimalpa</span>
                            <span class="location-separator">></span>
                            <span class="location-item">Santa Fe</span>
                        </div>
                        <div class="property-stats">
                            <div class="stat">
                                <i class="fas fa-bed"></i>
                                3 recámaras
                            </div>
                            <div class="stat">
                                <i class="fas fa-bath"></i>
                                3.5 baños
                            </div>
                            <div class="stat">
                                <i class="fas fa-ruler-combined"></i>
                                160 m²
                            </div>
                        </div>
                        <div class="property-features">
                            <span class="feature-badge new-home">
                                <i class="fas fa-building"></i> Entrega 2024
                            </span>
                            <span class="feature-badge new-home">
                                <i class="fas fa-shield-alt"></i> Garantía 5 años
                            </span>
                            <span class="feature-badge">
                                <i class="fas fa-car"></i> 2 Estacionamientos
                            </span>
                        </div>
                        <p class="property-description">Departamento nuevo en Santa Fe con acabados de lujo, vista panorámica y amenities exclusivos. Entrega programada para Q4 2024.</p>
                        <a href="#" class="view-property">
                            Ver propiedad <i class="fas fa-arrow-right"></i>
                            <span class="sr-only">en Santa Fe, Cuajimalpa</span>
                        </a>
                    </div>
                </div>

                <!-- Property Card 2 - Guadalajara -->
                <div class="property-card" data-id="mx-jalisco-zapopan-456-4200000">
                    <div class="property-image">
                        <img src="https://images.unsplash.com/photo-1600596542815-4a3d32c18c3b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                             alt="Casa nueva en Puerta de Hierro, Guadalajara">
                        <div class="property-badge new">Nueva Construcción</div>
                        <div class="property-badge mx">México</div>
                        <button class="favorite-btn" aria-label="Guardar propiedad como favorita">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="property-content">
                        <div class="property-price">
                            <i class="fas fa-coins"></i>
                            <span class="mxn-price">$4,200,000 MXN</span>
                        </div>
                        <div class="property-address">
                            <i class="fas fa-map-marker-alt"></i>
                            Puerta de Hierro, Zapopan
                        </div>
                        <div class="location-hierarchy">
                            <span class="location-item">Jalisco</span>
                            <span class="location-separator">></span>
                            <span class="location-item">Zapopan</span>
                            <span class="location-separator">></span>
                            <span class="location-item">Puerta de Hierro</span>
                        </div>
                        <div class="property-stats">
                            <div class="stat">
                                <i class="fas fa-bed"></i>
                                4 recámaras
                            </div>
                            <div class="stat">
                                <i class="fas fa-bath"></i>
                                3.5 baños
                            </div>
                            <div class="stat">
                                <i class="fas fa-ruler-combined"></i>
                                240 m²
                            </div>
                        </div>
                        <div class="property-features">
                            <span class="feature-badge new-home">
                                <i class="fas fa-building"></i> Entrega 2024
                            </span>
                            <span class="feature-badge new-home">
                                <i class="fas fa-shield-alt"></i> Garantía 5 años
                            </span>
                            <span class="feature-badge">
                                <i class="fas fa-swimming-pool"></i> Alberca
                            </span>
                        </div>
                        <p class="property-description">Casa nueva en Puerta de Hierro con jardín privado, alberca y seguridad 24/7. Acabados de primera y diseño moderno. Entrega Q3 2024.</p>
                        <a href="#" class="view-property">
                            Ver propiedad <i class="fas fa-arrow-right"></i>
                            <span class="sr-only">en Puerta de Hierro, Zapopan</span>
                        </a>
                    </div>
                </div>

                <!-- Property Card 3 - Monterrey -->
                <div class="property-card" data-id="mx-nl-san-pedro-789-12000000">
                    <div class="property-image">
                        <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                             alt="Departamento nuevo en San Pedro, Monterrey">
                        <div class="property-badge new">Nueva Construcción</div>
                        <div class="property-badge mx">México</div>
                        <button class="favorite-btn" aria-label="Guardar propiedad como favorita">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="property-content">
                        <div class="property-price">
                            <i class="fas fa-coins"></i>
                            <span class="mxn-price">$12,000,000 MXN</span>
                        </div>
                        <div class="property-address">
                            <i class="fas fa-map-marker-alt"></i>
                            San Pedro Garza García
                        </div>
                        <div class="location-hierarchy">
                            <span class="location-item">Nuevo León</span>
                            <span class="location-separator">></span>
                            <span class="location-item">Monterrey</span>
                            <span class="location-separator">></span>
                            <span class="location-item">San Pedro</span>
                        </div>
                        <div class="property-stats">
                            <div class="stat">
                                <i class="fas fa-bed"></i>
                                3 recámaras
                            </div>
                            <div class="stat">
                                <i class="fas fa-bath"></i>
                                3.5 baños
                            </div>
                            <div class="stat">
                                <i class="fas fa-ruler-combined"></i>
                                210 m²
                            </div>
                        </div>
                        <div class="property-features">
                            <span class="feature-badge new-home">
                                <i class="fas fa-building"></i> Entrega 2024
                            </span>
                            <span class="feature-badge new-home">
                                <i class="fas fa-shield-alt"></i> Garantía 5 años
                            </span>
                            <span class="feature-badge">
                                <i class="fas fa-car"></i> 3 Estacionamientos
                            </span>
                        </div>
                        <p class="property-description">Departamento de lujo en San Pedro con vista panorámica, amenities completos y seguridad 24/7. Entrega programada para Q2 2024.</p>
                        <a href="#" class="view-property">
                            Ver propiedad <i class="fas fa-arrow-right"></i>
                            <span class="sr-only">en San Pedro Garza García</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recommendations Section -->
    <section class="section recommendations-section" id="recommendationsSection">
        <div class="container">
            <div class="recommendations-header">
                <h2 class="recommendations-title">Construcciones Nuevas que Tal Vez Te Gusten</h2>
                <a href="#" class="view-all-link">Ver todas las recomendaciones <i class="fas fa-arrow-right"></i></a>
            </div>
            <p class="section-subtitle">Basado en tus búsquedas recientes y propiedades vistas</p>

            <div class="properties-grid" id="recommendationsGrid">
                <!-- Recommendations will be populated dynamically -->
                <div class="recommendations-empty">
                    <div class="recommendations-empty-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3 class="recommendations-empty-title">Sin recomendaciones aún</h3>
                    <p class="recommendations-empty-text">Navega por las construcciones nuevas para ver recomendaciones personalizadas basadas en tus preferencias.</p>
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