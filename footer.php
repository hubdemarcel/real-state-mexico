    <footer class="footer">
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="index.php" class="footer-logo">
                        <img src="Tierrasmx/assets/images/logo.png" alt="Tierras.mx Logo" style="width: 24px; height: 24px; margin-right: 8px;">
                        Tierras.mx
                    </a>
                    <p class="footer-description">Tu socio de confianza para encontrar la propiedad perfecta en México.</p>
                    <div class="social-links">
                        <a href="https://wa.me/523331010164?text=Hola%2C%20quisiera%20m%C3%A1s%20informaci%C3%B3n." class="social-link"><i class="fab fa-whatsapp"></i></a>
                        <a href="https://www.instagram.com/tierras.mx/" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.linkedin.com/in/tierras-m%C3%A9xico-417931362/" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://www.youtube.com/@TierrasMexico" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-column">
                    <h4 class="footer-title">Para Compradores</h4>
                    <ul class="footer-links">
                        <li><a href="comprar_new.php">Buscar Propiedades</a></li>
                        <li><a href="#">Calculadora de Crédito</a></li>
                        <li><a href="#">Guía de Compra</a></li>
                        <li><a href="encuentraunagente.php">Encuentra un Agente</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4 class="footer-title">Para Vendedores</h4>
                    <ul class="footer-links">
                        <li><a href="anunciar.php">Vende tu Propiedad</a></li>
                        <li><a href="#">Valor de tu Propiedad</a></li>
                        <li><a href="recursos_vendedor.php">Guía de Venta</a></li>
                        <li><a href="anunciar.php">Lista tu Propiedad</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4 class="footer-title">Compañía</h4>
                    <ul class="footer-links">
                        <li><a href="sobre_nosotros.php">Sobre Nosotros</a></li>
                        <li><a href="#">Trabajos</a></li>
                        <li><a href="#">Prensa</a></li>
                        <li><a href="#">Contacto</a></li>
                        <li><a href="politica_privacidad.php">Política de Privacidad</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2024 Tierras.mx. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <!-- Leaflet MarkerCluster JavaScript -->
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

    <!-- Properties Map Script -->
    <script>
        // Initialize map when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializePropertiesMap();
        });

        function initializePropertiesMap() {
            const mapElement = document.getElementById('propertiesMap');
            if (!mapElement) return;

            // Initialize map centered on Mexico
            const map = L.map('propertiesMap').setView([23.6345, -102.5528], 5);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 18,
            }).addTo(map);

            // Create marker cluster group
            const markers = L.markerClusterGroup({
                chunkedLoading: true,
                chunkInterval: 200,
                chunkDelay: 50,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true,
                removeOutsideVisibleBounds: true,
                animate: true,
                disableClusteringAtZoom: 16,
                maxClusterRadius: 80,
                iconCreateFunction: function(cluster) {
                    const childCount = cluster.getChildCount();
                    let className = 'marker-cluster-';

                    if (childCount < 10) {
                        className += 'small';
                    } else if (childCount < 100) {
                        className += 'medium';
                    } else {
                        className += 'large';
                    }

                    return new L.DivIcon({
                        html: '<div><span>' + childCount + '</span></div>',
                        className: 'marker-cluster ' + className,
                        iconSize: new L.Point(40, 40)
                    });
                }
            });

            // Load properties data
            fetch('Tierrasmx/assets/data/properties-mx.json')
                .then(response => response.json())
                .then(properties => {
                    addPropertyMarkers(map, markers, properties);
                    map.addLayer(markers);
                })
                .catch(error => {
                    console.error('Error loading properties:', error);
                });
        }

        function addPropertyMarkers(map, markers, properties) {
            properties.forEach(property => {
                if (property.location && property.location.coordinates) {
                    const { lat, lng } = property.location.coordinates;

                    // Create marker with custom icon
                    const markerIcon = L.divIcon({
                        className: 'property-marker',
                        html: `<div class="marker-content">
                            <i class="fas fa-home"></i>
                            <span class="marker-price">${formatPrice(property.price)}</span>
                        </div>`,
                        iconSize: [80, 30],
                        iconAnchor: [40, 30]
                    });

                    const marker = L.marker([lat, lng], { icon: markerIcon });

                    // Create popup content
                    const popupContent = `
                        <div class="property-popup">
                            <h4>${property.title_es}</h4>
                            <div class="popup-price">${formatPrice(property.price)} MXN</div>
                            <div class="popup-location">
                                <i class="fas fa-map-marker-alt"></i>
                                ${property.location.city}, ${property.location.state}
                            </div>
                            <div class="popup-features">
                                ${property.features.bedrooms ? `<span><i class="fas fa-bed"></i> ${property.features.bedrooms} rec</span>` : ''}
                                ${property.features.bathrooms ? `<span><i class="fas fa-bath"></i> ${property.features.bathrooms} baños</span>` : ''}
                                ${property.features.construction_size ? `<span><i class="fas fa-ruler-combined"></i> ${property.features.construction_size} m²</span>` : ''}
                            </div>
                            <a href="templatepropiedad.php?id=${property.id}" class="popup-link">Ver propiedad</a>
                        </div>
                    `;

                    marker.bindPopup(popupContent);

                    // Add marker to cluster group instead of directly to map
                    markers.addLayer(marker);
                }
            });
        }

        function formatPrice(price) {
            return new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(price);
        }
    </script>

    <script src="Tierrasmx/assets/js/utils.js"></script>
    <script src="Tierrasmx/assets/js/localization.js"></script>
    <script src="Tierrasmx/assets/js/main.js"></script>
    <script src="Tierrasmx/assets/js/search.js"></script>
    <script src="Tierrasmx/assets/js/notifications.js"></script>
    <script src="Tierrasmx/assets/js/real_time_updates.js"></script>
    <?php if (isset($additionalJs)) echo $additionalJs; ?>
</body>
</html>