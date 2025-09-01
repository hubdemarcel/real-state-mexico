<?php
echo "<!-- Debug timestamp: " . date('Y-m-d H:i:s') . " -->";
$pageTitle = 'Tierras.mx - Agentes. Visitas. Créditos. Casas.';
include 'header.php';
include 'property-card.php';

// Sample properties data
$featuredProperties = [
    [
        'id' => 'mx-cdmx-polanco-123-5500000',
        'title' => 'Departamento moderno en Polanco, CDMX',
        'price' => 5500000,
        'location' => 'Polanco, Miguel Hidalgo',
        'bedrooms' => 2,
        'bathrooms' => 2,
        'size' => '85',
        'image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'type' => 'venta',
        'description' => 'Hermoso departamento moderno en Polanco con vista a la ciudad, excelente ubicación cerca de centros comerciales y restaurantes.',
        'features' => [
            ['icon' => 'shield-alt', 'text' => 'Seguridad 24/7', 'class' => 'seguridad'],
            ['icon' => 'car', 'text' => 'Estacionamiento']
        ]
    ],
    [
        'id' => 'mx-jalisco-guadalajara-456-3200000',
        'title' => 'Casa familiar en Providencia, Guadalajara',
        'price' => 3200000,
        'location' => 'Providencia, Guadalajara',
        'bedrooms' => 3,
        'bathrooms' => '2.5',
        'size' => '180',
        'image' => 'https://images.unsplash.com/photo-1600596542815-4a3d32c18c3b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'type' => 'venta',
        'description' => 'Casa familiar en zona segura de Providencia, con jardín privado y acabados de calidad. Cerca de escuelas y centros comerciales.',
        'features' => [
            ['icon' => 'shield-alt', 'text' => 'Seguridad', 'class' => 'seguridad'],
            ['icon' => 'utensils', 'text' => 'Gas Natural'],
            ['icon' => 'tint', 'text' => 'Agua Potable']
        ]
    ],
    [
        'id' => 'mx-nl-monterrey-santa-catarina-4500000',
        'title' => 'Terreno en Santa Catarina, Monterrey',
        'price' => 4500000,
        'location' => 'Santa Catarina, Monterrey',
        'bedrooms' => 'N/A',
        'bathrooms' => 'N/A',
        'size' => '500',
        'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'type' => 'venta',
        'description' => 'Terreno plano en excelente ubicación de Santa Catarina, ideal para construcción residencial o comercial. Documentación en regla.',
        'features' => [
            ['icon' => 'road', 'text' => 'Acceso Pavimentado'],
            ['icon' => 'bolt', 'text' => 'Luz y Agua']
        ]
    ]
];
?>

<section class="hero">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="hero-content" style="display: flex; align-items: center; padding-top: 50px; padding-bottom: 50px; gap: 40px;">
            <div class="hero-image" style="flex: 1;">
                <img src="https://images.unsplash.com/photo-1580587771525-78b9dba3b914?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Beautiful house" style="width: 100%; height: auto; border-radius: 10px;">
            </div>
            <div class="hero-text-and-search" style="flex: 2;">
                <h1>Encuentra tu Casa Soñada en México</h1>
                <p>Busca millones de propiedades y conéctate con profesionales inmobiliarios locales</p>
                <div class="search-bar">
                    <div class="search-container">
                        <div class="search-filters">
                            <div class="filter-group">
                                <label for="locationSearch" class="filter-label">Ubicación</label>
                                <input type="text" id="locationSearch" class="search-input" placeholder="Ingresa estado, ciudad o colonia">
                            </div>

                            <div class="filter-group">
                                <label for="propertyTypeSelect" class="filter-label">Tipo de Propiedad</label>
                                <select id="propertyTypeSelect" class="filter-select">
                                    <option value="all">Todas</option>
                                    <option value="casa">Casa</option>
                                    <option value="departamento">Departamento</option>
                                    <option value="terreno">Terreno</option>
                                    <option value="local-comercial">Local Comercial</option>
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
                                    <i class="fas fa-search"></i> Buscar
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
            <h2 class="section-title">Propiedades Destacadas</h2>
            <p class="section-subtitle">Descubre las propiedades más populares en tu zona</p>
        </div>

        <div class="properties-grid" id="featuredProperties">
            <?php foreach ($featuredProperties as $property): ?>
                <?php renderPropertyCard($property); ?>
            <?php endforeach; ?>
        </div>

        <div class="view-all-wrapper text-center">
            <button class="btn btn-primary btn-large">Ver Todas las Propiedades</button>
        </div>
    </section>

    <!-- Recommendations Section -->
    <section class="section recommendations-section" id="recommendationsSection">
        <div class="container">
            <div class="section-header">
                <div class="recommendations-header">
                    <h2 class="recommendations-title">Propiedades que Tal Vez Te Gusten</h2>
                    <a href="#" class="view-all-link">Ver todas las recomendaciones <i class="fas fa-arrow-right"></i></a>
                </div>
                <p class="section-subtitle">Propiedades similares a las que has visto y guardado</p>
            </div>

            <div class="properties-grid" id="recommendationsGrid">
                <!-- Recommendations will be populated dynamically -->
                <div class="recommendations-empty">
                    <div class="recommendations-empty-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3 class="recommendations-empty-title">Sin recomendaciones aún</h3>
                    <p class="recommendations-empty-text">Navega por las propiedades para ver recomendaciones personalizadas basadas en tus preferencias.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="section services">
        <div class="section-header">
            <h2 class="section-title">¿Por Qué Elegir Tierras.mx?</h2>
            <p class="section-subtitle">Tu socio de confianza en bienes raíces en México</p>
        </div>

        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="service-title">Búsqueda Avanzada</h3>
                <p class="service-description">Encuentra exactamente lo que buscas con nuestros filtros y herramientas de búsqueda poderosas adaptadas al mercado mexicano.</p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3 class="service-title">Experiencia Local</h3>
                <p class="service-description">Conéctate con agentes locales experimentados que conocen tu mercado a fondo, desde CDMX hasta Cancún.</p>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 class="service-title">Servicio Personalizado</h3>
                <p class="service-description">Obtén recomendaciones personalizadas y guarda tus propiedades favoritas para no perder de vista tus opciones.</p>
            </div>
        </div>
    </section>
</main>

<?php
$additionalJs = '<script src="assets/js/recommendations.js"></script>';
include 'footer.php';
?>