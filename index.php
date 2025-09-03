<?php
session_start();
$pageTitle = 'Tierras.mx - Agentes. Visitas. Créditos. Casas.';
include 'header.php';
include 'property-card.php';

// Add Leaflet CSS for map
echo '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>';

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
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <a href="#" class="view-all-link">Ver todas las recomendaciones <i class="fas fa-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
                <p class="section-subtitle">
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        Propiedades similares a las que has visto y guardado
                    <?php else: ?>
                        Inicia sesión para ver recomendaciones personalizadas
                    <?php endif; ?>
                </p>
            </div>

            <div class="properties-grid" id="recommendationsGrid">
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <!-- Recommendations will be populated dynamically -->
                    <div class="recommendations-empty">
                        <div class="recommendations-empty-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h3 class="recommendations-empty-title">Sin recomendaciones aún</h3>
                        <p class="recommendations-empty-text">Navega por las propiedades para ver recomendaciones personalizadas basadas en tus preferencias.</p>
                    </div>
                <?php else: ?>
                    <!-- Login prompt for non-logged users -->
                    <div class="recommendations-login-prompt">
                        <div class="login-prompt-icon">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h3 class="login-prompt-title">Descubre Propiedades Personalizadas</h3>
                        <p class="login-prompt-text">Inicia sesión para recibir recomendaciones basadas en tus búsquedas y preferencias.</p>
                        <div class="login-prompt-actions">
                            <a href="login.html" class="btn btn-primary">Iniciar Sesión</a>
                            <a href="register.html" class="btn btn-ghost">Registrarse</a>
                        </div>
                    </div>
                <?php endif; ?>
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

<?php
// Define data arrays for better maintainability and readability
$intelligenceCards = [
    [
        'icon' => 'fas fa-chart-line',
        'title' => 'Análisis de Mercado',
        'description' => 'Información básica del mercado inmobiliario con indicadores clave y tendencias.',
        'link' => 'basic_intelligence.php',
        'link_text' => 'Ver análisis'
    ],
    [
        'icon' => 'fas fa-brain',
        'title' => 'Inteligencia Avanzada',
        'description' => 'Herramientas avanzadas de IA para agentes con predicciones y recomendaciones.',
        'link' => 'agent_intelligence.php',
        'link_text' => 'Acceder'
    ],
    [
        'icon' => 'fas fa-calculator',
        'title' => 'Calculadoras',
        'description' => 'Herramientas para calcular precios, rentabilidad y valor de propiedades.',
        'link' => '#',
        'link_text' => 'Próximamente'
    ]
];

$articles = [
    [
        'image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        'alt' => 'Casa moderna representando inversión inmobiliaria',
        'title' => 'Cómo Elegir la Mejor Ubicación para tu Inversión',
        'excerpt' => 'Descubre los factores clave para seleccionar propiedades con alto potencial de valorización en México.',
        'link' => '#'
    ],
    [
        'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        'alt' => 'Guía para compradores primerizos',
        'title' => 'Guía para Compradores Primerizos',
        'excerpt' => 'Todo lo que necesitas saber antes de comprar tu primera propiedad en México.',
        'link' => '#'
    ],
    [
        'image' => 'https://images.unsplash.com/photo-1600596542815-4a3d32c18c3b?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        'alt' => 'Tendencias del mercado inmobiliario 2024',
        'title' => 'Tendencias del Mercado 2024',
        'excerpt' => 'Análisis de las tendencias actuales y proyecciones para el mercado inmobiliario mexicano.',
        'link' => '#'
    ]
];

$news = [
    [
        'image' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        'alt' => 'Nuevo desarrollo residencial en CDMX',
        'date' => '15 Sep 2024',
        'category' => 'Mercado',
        'title' => 'Nuevo Desarrollo Residencial en CDMX',
        'excerpt' => 'Se anuncia la construcción de un complejo residencial de lujo en la zona de Polanco.',
        'link' => '#'
    ],
    [
        'image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        'alt' => 'Bajas en tasas de interés hipotecarias',
        'date' => '12 Sep 2024',
        'category' => 'Financiamiento',
        'title' => 'Bajan Tasas de Interés Hipotecarias',
        'excerpt' => 'Las principales instituciones financieras reducen sus tasas para créditos hipotecarios.',
        'link' => '#'
    ],
    [
        'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        'alt' => 'Oportunidades de inversión en Quintana Roo',
        'date' => '10 Sep 2024',
        'category' => 'Inversión',
        'title' => 'Oportunidades en Quintana Roo',
        'excerpt' => 'Análisis de las mejores zonas para invertir en propiedades vacacionales.',
        'link' => '#'
    ]
];

$resources = [
    [
        'icon' => 'fas fa-user-tie',
        'title' => 'Para Agentes',
        'description' => 'Recursos profesionales para agentes inmobiliarios: plantillas, guías y herramientas.',
        'link' => 'recursos_vendedor.php',
        'link_text' => 'Ver recursos'
    ],
    [
        'icon' => 'fas fa-home',
        'title' => 'Para Compradores',
        'description' => 'Guías completas para comprar tu primera propiedad o invertir en bienes raíces.',
        'link' => 'comprar.php',
        'link_text' => 'Ver guías'
    ],
    [
        'icon' => 'fas fa-dollar-sign',
        'title' => 'Para Vendedores',
        'description' => 'Todo lo que necesitas saber para vender tu propiedad al mejor precio.',
        'link' => 'venta.php',
        'link_text' => 'Ver recursos'
    ],
    [
        'icon' => 'fas fa-calculator',
        'title' => 'Herramientas',
        'description' => 'Calculadoras y herramientas útiles para el mercado inmobiliario mexicano.',
        'link' => '#',
        'link_text' => 'Próximamente'
    ]
];

// Helper function for safe image rendering with error handling
function renderImage($src, $alt, $class = '', $lazy = true) {
    $lazyAttr = $lazy ? 'loading="lazy"' : '';
    $fallback = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjI2NCIgdmlld0JveD0iMCAwIDQwMCAyNjQiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI0MDAiIGhlaWdodD0iMjY0IiBmaWxsPSIjRjNGNEY2Ii8+Cjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iMC4zNWVtIiBmaWxsPSIjOUI5QkE0IiBmb250LXNpemU9IjE0Ij5JbWFnZW4gbm8gZGlzcG9uaWJsZTwvdGV4dD4KPHN2Zz4=';
    return "<img src=\"{$src}\" alt=\"{$alt}\" class=\"{$class}\" {$lazyAttr} onerror=\"this.src='{$fallback}'; this.onerror=null;\">";
}
?>

<!-- Market Intelligence Section -->
<section class="section market-intelligence">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Inteligencia de Mercado</h2>
            <p class="section-subtitle">Datos y análisis del mercado inmobiliario mexicano</p>
        </div>

        <div class="intelligence-grid" role="list">
            <?php foreach ($intelligenceCards as $card): ?>
                <article class="intelligence-card" role="listitem">
                    <div class="intelligence-icon" aria-hidden="true">
                        <i class="<?php echo htmlspecialchars($card['icon']); ?>" aria-hidden="true"></i>
                    </div>
                    <h3 class="intelligence-title"><?php echo htmlspecialchars($card['title']); ?></h3>
                    <p class="intelligence-description"><?php echo htmlspecialchars($card['description']); ?></p>
                    <a href="<?php echo htmlspecialchars($card['link']); ?>" class="intelligence-link" <?php echo $card['link'] === '#' ? 'aria-disabled="true"' : ''; ?>>
                        <?php echo htmlspecialchars($card['link_text']); ?> <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Articles Section -->
<section class="section articles-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Artículos</h2>
            <p class="section-subtitle">Consejos y guías sobre el mercado inmobiliario</p>
        </div>

        <div class="articles-grid" role="list">
            <?php foreach ($articles as $article): ?>
                <article class="article-card" role="listitem">
                    <div class="article-image">
                        <?php echo renderImage($article['image'], $article['alt'], 'article-image'); ?>
                    </div>
                    <div class="article-content">
                        <h3 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h3>
                        <p class="article-excerpt"><?php echo htmlspecialchars($article['excerpt']); ?></p>
                        <a href="<?php echo htmlspecialchars($article['link']); ?>" class="article-link" <?php echo $article['link'] === '#' ? 'aria-disabled="true"' : ''; ?>>
                            Leer más <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- News Section -->
<section class="section news-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Noticias</h2>
            <p class="section-subtitle">Últimas noticias del sector inmobiliario</p>
        </div>

        <div class="news-grid" role="list">
            <?php foreach ($news as $item): ?>
                <article class="news-card" role="listitem">
                    <div class="news-image">
                        <?php echo renderImage($item['image'], $item['alt'], 'news-image'); ?>
                    </div>
                    <div class="news-content">
                        <div class="news-meta">
                            <time class="news-date" datetime="<?php echo htmlspecialchars(date('Y-m-d', strtotime($item['date']))); ?>">
                                <?php echo htmlspecialchars($item['date']); ?>
                            </time>
                            <span class="news-category"><?php echo htmlspecialchars($item['category']); ?></span>
                        </div>
                        <h3 class="news-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p class="news-excerpt"><?php echo htmlspecialchars($item['excerpt']); ?></p>
                        <a href="<?php echo htmlspecialchars($item['link']); ?>" class="news-link" <?php echo $item['link'] === '#' ? 'aria-disabled="true"' : ''; ?>>
                            Leer noticia <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Resources Section -->
<section class="section resources-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Recursos</h2>
            <p class="section-subtitle">Herramientas y guías para tu experiencia inmobiliaria</p>
        </div>

        <div class="resources-grid" role="list">
            <?php foreach ($resources as $resource): ?>
                <article class="resource-card" role="listitem">
                    <div class="resource-icon" aria-hidden="true">
                        <i class="<?php echo htmlspecialchars($resource['icon']); ?>" aria-hidden="true"></i>
                    </div>
                    <h3 class="resource-title"><?php echo htmlspecialchars($resource['title']); ?></h3>
                    <p class="resource-description"><?php echo htmlspecialchars($resource['description']); ?></p>
                    <a href="<?php echo htmlspecialchars($resource['link']); ?>" class="resource-link" <?php echo $resource['link'] === '#' ? 'aria-disabled="true"' : ''; ?>>
                        <?php echo htmlspecialchars($resource['link_text']); ?> <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

    <!-- Properties Map Section -->
    <section class="section properties-map-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Propiedades en México</h2>
                <p class="section-subtitle">Explora todas nuestras propiedades disponibles en el mapa interactivo</p>
            </div>

            <div class="map-container">
                <div id="propertiesMap" class="properties-map"></div>
            </div>

            <div class="map-legend">
                <div class="legend-item">
                    <div class="legend-marker" style="background-color: #2563eb;"></div>
                    <span>Propiedad en Venta</span>
                </div>
                <div class="legend-item">
                    <div class="legend-marker" style="background-color: #059669;"></div>
                    <span>Propiedad en Renta</span>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
$additionalJs = '<script src="Tierrasmx/assets/js/recommendations.js"></script>';
include 'footer.php';
?>