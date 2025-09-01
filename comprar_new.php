<?php
$pageTitle = 'Propiedades en Venta en México - Tierras.mx';
include 'header.php';
?>

<main class="container">
    <section class="section">
        <div class="section-header">
            <h1 class="section-title">Propiedades en Venta</h1>
            <p class="section-subtitle">Encuentra la propiedad perfecta para ti en México</p>
        </div>

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
                        <label for="price-range" class="filter-label">Rango de Precio</label>
                        <select id="price-range" class="filter-select">
                            <option value="all">Todos los precios</option>
                            <option value="0-2000000">$0 - $2,000,000 MXN</option>
                            <option value="2000000-5000000">$2,000,000 - $5,000,000 MXN</option>
                            <option value="5000000+">$5,000,000+ MXN</option>
                        </select>
                    </div>

                    <div class="filter-group" style="align-self: flex-end;">
                        <button class="search-button">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Property Results -->
        <div class="results-header">
            <div>
                <h2 class="results-title">Propiedades Disponibles</h2>
                <div class="results-count">Explorando propiedades en México</div>
            </div>

            <div class="view-options">
                <button class="view-option active">
                    <i class="fas fa-th-large"></i>
                </button>
                <button class="view-option">
                    <i class="fas fa-list"></i>
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
        <div class="properties-grid" id="propertyGrid">
            <!-- Properties will be loaded here -->
            <div class="loading-state">
                <div class="loading-spinner"></div>
                <p>Cargando propiedades...</p>
            </div>
        </div>

        <!-- Load More Button -->
        <div class="text-center" style="margin-top: 2rem;">
            <button class="btn btn-primary" id="loadMoreBtn">Cargar Más Propiedades</button>
        </div>
    </section>
</main>

<?php
include 'footer.php';
?>