document.addEventListener('DOMContentLoaded', () => {
    const searchButton = document.querySelector('.search-button');
    const propertiesGrid = document.getElementById('featuredProperties');
    let locationsData = [];

    const formatPrice = (price) => {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            minimumFractionDigits: 0
        }).format(price);
    };

    // Load locations data for autocomplete
    async function loadLocations() {
        try {
            const response = await fetch('Tierrasmx/assets/data/locations-mx.json');
            locationsData = await response.json();
        } catch (error) {
            console.error('Error loading locations:', error);
        }
    }

    // Initialize autocomplete for location input
    function initLocationAutocomplete() {
        const locationInput = document.getElementById('location-search');
        if (!locationInput) return;

        let suggestionsContainer = document.createElement('div');
        suggestionsContainer.className = 'autocomplete-suggestions';
        suggestionsContainer.style.cssText = `
            position: absolute; background: white; border: 1px solid #ddd; max-height: 200px; overflow-y: auto; z-index: 1000; width: 100%; display: none;
        `;
        locationInput.parentNode.style.position = 'relative';
        locationInput.parentNode.appendChild(suggestionsContainer);

        locationInput.addEventListener('input', (e) => {
            const value = e.target.value.toLowerCase();
            if (value.length < 2) {
                suggestionsContainer.style.display = 'none';
                return;
            }

            const suggestions = [];
            locationsData.estados?.forEach(estado => {
                estado.ciudades?.forEach(ciudad => {
                    if (ciudad.nombre.toLowerCase().includes(value)) {
                        suggestions.push(`${ciudad.nombre}, ${estado.nombre}`);
                    }
                    ciudad.colonias?.forEach(colonia => {
                        if (colonia.toLowerCase().includes(value)) {
                            suggestions.push(`${colonia}, ${ciudad.nombre}, ${estado.nombre}`);
                        }
                    });
                });
            });

            if (suggestions.length > 0) {
                suggestionsContainer.innerHTML = suggestions.slice(0, 10).map(s => `<div class="suggestion-item" style="padding: 8px; cursor: pointer; border-bottom: 1px solid #eee;">${s}</div>`).join('');
                suggestionsContainer.style.display = 'block';

                suggestionsContainer.querySelectorAll('.suggestion-item').forEach(item => {
                    item.addEventListener('click', () => {
                        locationInput.value = item.textContent;
                        suggestionsContainer.style.display = 'none';
                    });
                });
            } else {
                suggestionsContainer.style.display = 'none';
            }
        });

        document.addEventListener('click', (e) => {
            if (!locationInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                suggestionsContainer.style.display = 'none';
            }
        });
    }

    async function fetchProperties(params = {}) {
        try {
            const url = new URL('get_properties.php', window.location.origin);
            url.search = new URLSearchParams(params).toString();

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            renderProperties(data.properties || data, data.total || 0, params.page || 1);
        } catch (error) {
            console.error("Could not fetch properties:", error);
            if(propertiesGrid) {
                propertiesGrid.innerHTML = '<p>No se pudieron cargar las propiedades. Intente de nuevo más tarde.</p>';
            }
        }
    }

    function renderProperties(properties, total = 0, currentPage = 1) {
        if (!propertiesGrid) return;
        propertiesGrid.innerHTML = '';

        if (properties.length === 0) {
            propertiesGrid.innerHTML = '<div class="no-results" style="text-align: center; padding: 2rem; grid-column: 1 / -1;"><p>No se encontraron propiedades con los criterios seleccionados.</p></div>';
            return;
        }

        properties.forEach(prop => {
            const placeholderImage = 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
            const description = prop.title || prop.title_es || 'Descripción no disponible';
            const badge = prop.property_type === 'renta' ? 'rent' : 'sale';
            const features = prop.features ? Object.keys(prop.features).filter(key => prop.features[key] === true) : [];

            const propertyCardHTML = `
                <div class="property-card" data-id="${prop.id}">
                    <div class="property-image">
                        <img src="${prop.image_url || prop.image || placeholderImage}" alt="${prop.title || prop.title_es}">
                        <div class="property-badge ${badge}">${prop.property_type}</div>
                        <button class="favorite-btn" aria-label="Guardar propiedad como favorita">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="property-content">
                        <div class="property-price">
                            <i class="fas fa-coins"></i>
                            <span class="mxn-price">${formatPrice(prop.price)}</span>
                        </div>
                        <div class="property-address">
                            <i class="fas fa-map-marker-alt"></i>
                            ${prop.location}
                        </div>
                        <div class="property-stats">
                            <div class="stat">
                                <i class="fas fa-bed"></i>
                                ${prop.bedrooms || 'N/A'} recámaras
                            </div>
                            <div class="stat">
                                <i class="fas fa-bath"></i>
                                ${prop.bathrooms || 'N/A'} baños
                            </div>
                            <div class="stat">
                                <i class="fas fa-ruler-combined"></i>
                                ${prop.construction_size || prop.size || 'N/A'} m²
                            </div>
                        </div>
                        ${features.length > 0 ? `
                        <div class="property-features">
                            ${features.map(feature => `<span class="feature-badge"><i class="fas fa-check"></i>${feature}</span>`).join('')}
                        </div>
                        ` : ''}
                        <p class="property-description">${description}</p>
                        <a href="templatepropiedad.html?id=${prop.id}" class="view-property">
                            Ver propiedad <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            `;
            propertiesGrid.innerHTML += propertyCardHTML;
        });

        // Add pagination
        if (total > properties.length) {
            const totalPages = Math.ceil(total / 12); // Assuming 12 per page
            const paginationHTML = `
                <div class="pagination" style="grid-column: 1 / -1; text-align: center; margin-top: 2rem;">
                    ${currentPage > 1 ? `<button class="page-btn" data-page="${currentPage - 1}">Anterior</button>` : ''}
                    <span>Página ${currentPage} de ${totalPages}</span>
                    ${currentPage < totalPages ? `<button class="page-btn" data-page="${currentPage + 1}">Siguiente</button>` : ''}
                </div>
            `;
            propertiesGrid.innerHTML += paginationHTML;

            // Add pagination event listeners
            document.querySelectorAll('.page-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const page = parseInt(e.target.dataset.page);
                    handleSearch(page);
                });
            });
        }
    }

    function handleSearch(page = 1) {
        const locationInput = document.getElementById('location-search').value.toLowerCase().trim();
        const propertyType = document.getElementById('property-type').value;
        const minPrice = document.getElementById('min-price')?.value || '';
        const maxPrice = document.getElementById('max-price')?.value || '';
        const bedrooms = document.getElementById('bedrooms')?.value || '';
        const bathrooms = document.getElementById('bathrooms')?.value || '';
        const amenities = Array.from(document.querySelectorAll('input[name="amenities"]:checked')).map(cb => cb.value).join(',');

        const params = {
            location: locationInput,
            property_type: propertyType,
            min_price: minPrice,
            max_price: maxPrice,
            bedrooms: bedrooms,
            bathrooms: bathrooms,
            amenities: amenities,
            page: page,
            limit: 12
        };

        // Track search if user is logged in and it's not just pagination
        if (page === 1) {
            trackSearch(locationInput || 'Búsqueda general', params);
        }

        fetchProperties(params);
    }

    function trackSearch(searchQuery, searchFilters) {
        const formData = new FormData();
        formData.append('search_query', searchQuery);
        formData.append('search_filters', JSON.stringify(searchFilters));

        fetch('track_search.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Search tracked successfully');
            } else {
                console.log('Search tracking failed:', data.message);
            }
        })
        .catch(error => {
            console.error('Error tracking search:', error);
        });
    }

    if (searchButton) {
        searchButton.addEventListener('click', (e) => {
            e.preventDefault();
            handleSearch();
        });
    }

    // Initialize on load
    loadLocations().then(() => {
        initLocationAutocomplete();
    });

    // Fetch all properties on page load
    fetchProperties({ page: 1, limit: 12 });
});