<?php
$pageTitle = 'Propiedades en Venta en México - Tierras.mx';
include 'header.php';
include 'property-card.php';

// Sample properties for sale
$saleProperties = [
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
    ],
    [
        'id' => 'mx-cdmx-roma-789-2800000',
        'title' => 'Departamento en Roma, CDMX',
        'price' => 2800000,
        'location' => 'Roma, Cuauhtémoc',
        'bedrooms' => 1,
        'bathrooms' => 1,
        'size' => '65',
        'image' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'type' => 'venta',
        'description' => 'Departamento moderno en la Roma con balcón y vista a la ciudad. Ideal para jóvenes profesionales. Cerca de restaurantes y bares.',
        'features' => [
            ['icon' => 'shield-alt', 'text' => 'Seguridad', 'class' => 'seguridad'],
            ['icon' => 'utensils', 'text' => 'Gas Natural']
        ]
    ],
    [
        'id' => 'mx-jalisco-zapopan-101-6200000',
        'title' => 'Casa en Puerta de Hierro, Zapopan',
        'price' => 6200000,
        'location' => 'Puerta de Hierro, Zapopan',
        'bedrooms' => 4,
        'bathrooms' => '3.5',
        'size' => '280',
        'image' => 'https://images.unsplash.com/photo-1600488689109-5fab0f5b6c86?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'type' => 'venta',
        'description' => 'Casa de lujo en Puerta de Hierro con alberca, jardín y estacionamiento para 3 autos. Acabados de primera y seguridad 24/7.',
        'features' => [
            ['icon' => 'shield-alt', 'text' => 'Seguridad 24/7', 'class' => 'seguridad'],
            ['icon' => 'car', 'text' => '3 Estacionamientos'],
            ['icon' => 'swimming-pool', 'text' => 'Alberca']
        ]
    ],
    [
        'id' => 'mx-nl-san-pedro-202-8500000',
        'title' => 'Departamento en San Pedro, Monterrey',
        'price' => 8500000,
        'location' => 'San Pedro Garza García',
        'bedrooms' => 3,
        'bathrooms' => '3.5',
        'size' => '195',
        'image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'type' => 'venta',
        'description' => 'Departamento de lujo en San Pedro con vista panorámica, amenities completos y seguridad 24/7. Ideal para familias.',
        'features' => [
            ['icon' => 'shield-alt', 'text' => 'Seguridad', 'class' => 'seguridad'],
            ['icon' => 'car', 'text' => '2 Estacionamientos'],
            ['icon' => 'concierge-bell', 'text' => 'Servicios']
        ]
    ]
];
?>

<!-- Search Bar -->
<div class="search-bar">
    <div class="search-container">
        <div class="search-filters">
            <div class="filter-group">
                <label for="location-search" class="filter-label">Ubicación</label>
                <input type="text" id="location-search" class="search-input" placeholder="Ingresa estado, ciudad o colonia">
            </div>

            <div class="filter-group">
                <label for="property-type" class="filter-label">Tipo de Propiedad</label>
                <select id="property-type" class="filter-select">
                    <option value="all">Todas</option>
                    <option value="casa">Casa</option>
                    <option value="departamento">Departamento</option>
                    <option value="terreno">Terreno</option>
                    <option value="local-comercial">Local Comercial</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="min-price" class="filter-label">Precio Mínimo</label>
                <input type="number" id="min-price" class="filter-input" placeholder="Precio mínimo">
            </div>
            <div class="filter-group">
                <label for="max-price" class="filter-label">Precio Máximo</label>
                <input type="number" id="max-price" class="filter-input" placeholder="Precio máximo">
            </div>

            <div class="filter-group">
                <label for="bedrooms" class="filter-label">Habitaciones</label>
                <input type="number" id="bedrooms" class="filter-input" placeholder="Mínimo" min="0">
            </div>

            <div class="filter-group">
                <label for="bathrooms" class="filter-label">Baños</label>
                <input type="number" id="bathrooms" class="filter-input" placeholder="Mínimo" min="0">
            </div>

            <div class="filter-group" style="align-self: flex-end;">
                <button class="search-button">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <main class="main-content">
        <!-- Filters Sidebar -->
        <aside class="filters-sidebar">
            <div class="filter-section">
                <h3 class="filter-title">
                    Precio
                    <button>Restablecer</button>
                </h3>
                <div class="price-range">
                    <input type="number" id="sidebar-min-price" class="price-input" placeholder="$ Mínimo">
                    <input type="number" id="sidebar-max-price" class="price-input" placeholder="$ Máximo">
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
                        <input type="checkbox" name="amenities" value="seguridad" checked> Seguridad 24/7
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="amenities" value="estacionamiento" checked> Estacionamiento
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="amenities" value="gas_natural"> Gas Natural
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="amenities" value="agua_potable"> Agua Potable
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="amenities" value="alberca"> Alberca
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="amenities" value="jardin"> Jardín
                    </label>
                </div>
            </div>

            <div class="filter-section">
                <h3 class="filter-title">
                    Habitaciones
                    <button>Restablecer</button>
                </h3>
                <div class="checkbox-group">
                    <label class="checkbox-item">
                        <input type="checkbox"> 1+ habitación
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" checked> 2+ habitaciones
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" checked> 3+ habitaciones
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox"> 4+ habitaciones
                    </label>
                </div>
            </div>

            <button class="apply-filters">Aplicar Filtros</button>
        </aside>

        <!-- Property Results -->
        <div class="property-results">
            <div class="results-header">
                <div>
                    <h1 class="results-title">Propiedades en Venta</h1>
                    <div class="results-count"><?php echo count($saleProperties); ?> propiedades encontradas</div>
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
                </div>

                <div class="sort-options">
                    <span class="sort-label">Ordenar por:</span>
                    <select class="sort-select">
                        <option>Relevancia</option>
                        <option>Precio (menor a mayor)</option>
                        <option>Precio (mayor a menor)</option>
                        <option>Más reciente</option>
                    </select>
                </div>
            </div>

            <!-- Property Grid -->
            <div class="properties-grid" id="featuredProperties">
                <div class="loading" style="text-align: center; padding: 2rem; grid-column: 1 / -1;">
                    <i class="fas fa-spinner fa-spin"></i> Cargando propiedades...
                </div>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <ul class="pagination-list">
                    <li class="pagination-item active"><a href="#">1</a></li>
                    <li class="pagination-item"><a href="#">2</a></li>
                    <li class="pagination-item"><a href="#">3</a></li>
                    <li class="pagination-item"><a href="#">4</a></li>
                    <li class="pagination-item"><a href="#">5</a></li>
                </ul>
            </div>
        </div>
    </main>

    <!-- Recommendation System -->
    <section class="recommendation-system" id="recommendationsSection">
        <div class="container">
            <div class="recommendation-header">
                <h2 class="section-title">Propiedades que Tal Vez Te Gusten</h2>
                <p class="section-subtitle">Basado en tus búsquedas recientes y propiedades vistas</p>
            </div>

            <div class="recommendation-filters">
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
                    <label for="price-filter" class="filter-label">Precio:</label>
                    <select id="price-filter" class="filter-select">
                        <option value="all">Todos los precios</option>
                        <option value="0-2000000">$0 - $2,000,000 MXN</option>
                        <option value="2000000-5000000">$2,000,000 - $5,000,000 MXN</option>
                        <option value="5000000+">$5,000,000+ MXN</option>
                    </select>
                </div>
            </div>

            <div class="properties-grid" id="recommendationsGrid">
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
</div>

<?php
include 'footer.php';
?>