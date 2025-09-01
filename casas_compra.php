<?php
$pageTitle = 'Comprar Casa - Tierras.mx';
include 'header.php';
?>

<section class="hero">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="hero-content" style="display: flex; align-items: center; padding-top: 50px; padding-bottom: 50px; gap: 40px;">
            <div class="hero-image" style="flex: 1;">
                <img src="https://images.unsplash.com/photo-1580587771525-78b9dba3b914?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Beautiful house" style="width: 100%; height: auto; border-radius: 10px;">
            </div>
            <div class="hero-text-and-search" style="flex: 2;">
                <h1>Encuentra tu Casa Perfecta en México</h1>
                <p>Descubre miles de casas en venta en las mejores ubicaciones de México. Desde departamentos modernos hasta casas familiares, encuentra tu hogar ideal.</p>
                <div class="search-bar">
                    <div class="search-container">
                        <div class="search-filters">
                            <div class="filter-group">
                                <label for="locationSearch" class="filter-label">Ubicación</label>
                                <input type="text" id="locationSearch" class="search-input" placeholder="Ingresa estado, ciudad o colonia">
                            </div>

                            <div class="filter-group">
                                <label for="propertyTypeSelect" class="filter-label">Tipo de Casa</label>
                                <select id="propertyTypeSelect" class="filter-select">
                                    <option value="all">Todas las casas</option>
                                    <option value="casa">Casa</option>
                                    <option value="departamento">Departamento</option>
                                    <option value="duplex">Dúplex</option>
                                    <option value="penthouse">Penthouse</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="price-range" class="filter-label">Rango de Precio</label>
                                <select id="price-range" class="filter-select">
                                    <option value="all">Todos los precios</option>
                                    <option value="0-2000000">$0 - $2,000,000 MXN</option>
                                    <option value="2000000-5000000">$2,000,000 - $5,000,000 MXN</option>
                                    <option value="5000000+">$5,000,000+ MXN</option>
                                </select>
                            </div>

                            <div class="filter-group" style="align-self: flex-end;">
                                <button id="searchButton" class="search-button">
                                    <i class="fas fa-search"></i> Buscar Casas
                                </button>
                            </div>
                        </div>
                        <div id="searchSuggestions"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="container">
    <section class="section section-featured">
        <div class="section-header">
            <h2 class="section-title">Casas Destacadas en México</h2>
            <p class="section-subtitle">Descubre las casas más populares en tu zona</p>
        </div>

        <div class="properties-grid" id="featuredProperties">
            <!-- Property cards will be loaded here -->
            <div class="property-card">
                <div class="property-image">
                    <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Casa moderna">
                    <div class="property-badge sale">En Venta</div>
                    <div class="property-badge mx">México</div>
                    <button class="favorite-btn">
                        <i class="far fa-heart"></i>
                    </button>
                </div>
                <div class="property-content">
                    <div class="property-price">
                        <i class="fas fa-coins"></i>
                        <span class="mxn-price">$3,500,000 MXN</span>
                    </div>
                    <div class="property-address">
                        <i class="fas fa-map-marker-alt"></i>
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
                            <i class="fas fa-bed"></i>
                            3 recámaras
                        </div>
                        <div class="stat">
                            <i class="fas fa-bath"></i>
                            2.5 baños
                        </div>
                        <div class="stat">
                            <i class="fas fa-ruler-combined"></i>
                            180 m²
                        </div>
                    </div>
                    <div class="property-features">
                        <span class="feature-badge seguridad">
                            <i class="fas fa-shield-alt"></i> Seguridad 24/7
                        </span>
                        <span class="feature-badge">
                            <i class="fas fa-car"></i> Estacionamiento
                        </span>
                    </div>
                    <p class="property-description">Hermosa casa familiar en Polanco con jardín privado y acabados de calidad. Excelente ubicación cerca de escuelas y centros comerciales.</p>
                    <a href="#" class="view-property">Ver propiedad <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="view-all-wrapper text-center">
            <button class="btn btn-primary btn-large">Ver Todas las Casas</button>
        </div>
    </section>

    <!-- Services Section -->
    <section class="section services">
        <div class="section-header">
            <h2 class="section-title">¿Por Qué Comprar Casa en México?</h2>
            <p class="section-subtitle">Beneficios de invertir en el mercado inmobiliario mexicano</p>
        </div>

        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="service-title">Valorización Garantizada</h3>
                <p class="service-description">El mercado inmobiliario mexicano ofrece una excelente rentabilidad con crecimiento constante en los últimos años.</p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-home"></i>
                </div>
                <h3 class="service-title">Estabilidad Familiar</h3>
                <p class="service-description">Construye el hogar de tus sueños y brinda estabilidad a tu familia en un país con excelente calidad de vida.</p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-coins"></i>
                </div>
                <h3 class="service-title">Inversión Inteligente</h3>
                <p class="service-description">Las casas en México representan una inversión sólida con potencial de apreciación y generación de ingresos por alquiler.</p>
            </div>
        </div>
    </section>
</main>

<?php
$additionalJs = '<script src="assets/js/search.js" defer></script>';
include 'footer.php';
?>