// ===== SIMPLIFIED RECOMMENDATION SYSTEM =====
function initializeRecommendationSystem() {
    console.log("Initializing recommendation system...");
    
    // Check if user has viewed properties
    const viewedProperties = getLocalStorageItem('viewedProperties', []);
    console.log("Viewed properties count:", viewedProperties.length);
    
    // Show recommendations if user has viewed properties
    if (viewedProperties.length > 0) {
        showRecommendationsSection();
        generatePropertyRecommendations(viewedProperties);
    }
    
    // Start tracking property views
    trackPropertyViews();
}

// Track when user views a property
function trackPropertyViews() {
    const propertyCards = document.querySelectorAll('.property-card');
    
    propertyCards.forEach(card => {
        // Create observer to detect when property is visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Only count as "viewed" if visible for 2+ seconds
                    setTimeout(() => {
                        if (entry.isIntersecting) {
                            const propertyId = card.dataset.id || generatePropertyId(card);
                            console.log(`Property viewed: ${propertyId}`);
                            markPropertyAsViewed(propertyId, card);
                        }
                    }, 2000);
                }
            });
        }, { 
            threshold: 0.5 // 50% of element visible
        });
        
        observer.observe(card);
    });
}

// Mark a property as viewed
function markPropertyAsViewed(propertyId, cardElement) {
    // Add viewed indicator
    if (!cardElement.classList.contains('viewed-property')) {
        cardElement.classList.add('viewed-property');
        
        // Add viewed badge
        if (!cardElement.querySelector('.viewed-badge')) {
            const badge = document.createElement('div');
            badge.className = 'viewed-badge';
            badge.innerHTML = '<i class="fas fa-eye"></i> Visto';
            cardElement.querySelector('.property-image').appendChild(badge);
        }
    }
    
    // Save to localStorage
    let viewedProperties = getLocalStorageItem('viewedProperties', []);
    
    // Remove if already exists (to update timestamp)
    viewedProperties = viewedProperties.filter(p => p.id !== propertyId);
    
    // Add with timestamp
    viewedProperties.push({
        id: propertyId,
        timestamp: Date.now(),
        details: getPropertyDetails(cardElement)
    });
    
    // Keep only the 5 most recent
    viewedProperties = viewedProperties.slice(-5);
    
    setLocalStorageItem('viewedProperties', viewedProperties);
    
    // Show recommendations section
    showRecommendationsSection();
    
    // Log for debugging
    console.log("Updated viewed properties:", viewedProperties);
}

// Get property details for tracking
function getPropertyDetails(cardElement) {
    return {
        price: cardElement.querySelector('.property-price')?.textContent || '',
        address: cardElement.querySelector('.property-address')?.textContent.replace('📍', '').trim() || '',
        bedrooms: cardElement.querySelector('.stat:nth-child(1)')?.textContent || '',
        bathrooms: cardElement.querySelector('.stat:nth-child(2)')?.textContent || '',
        size: cardElement.querySelector('.stat:nth-child(3)')?.textContent || '',
        type: cardElement.querySelector('.property-badge.sale') ? 'venta' : 
              cardElement.querySelector('.property-badge.rent') ? 'renta' : 'otros',
        location: {
            estado: cardElement.querySelector('.location-item:nth-child(1)')?.textContent || '',
            ciudad: cardElement.querySelector('.location-item:nth-child(3)')?.textContent || '',
            colonia: cardElement.querySelector('.location-item:nth-child(5)')?.textContent || ''
        }
    };
}

// Show the recommendations section
function showRecommendationsSection() {
    const recommendationsSection = document.getElementById('recommendationsSection');
    if (recommendationsSection) {
        recommendationsSection.classList.remove('hidden');
        
        // Add animation
        setTimeout(() => {
            recommendationsSection.style.opacity = '1';
        }, 50);
    }
}

// Generate property recommendations
function generatePropertyRecommendations(viewedProperties) {
    const recommendationsGrid = document.getElementById('recommendationsGrid');
    if (!recommendationsGrid) return;
    
    // Clear existing recommendations
    recommendationsGrid.innerHTML = '';
    
    // Get the most recently viewed property
    const mostRecentView = viewedProperties[viewedProperties.length - 1];
    
    // Generate 3 similar properties
    const recommendations = [];
    
    // Create similar properties based on location
    for (let i = 1; i <= 3; i++) {
        const { location } = mostRecentView.details;
        const basePrice = parseInt(mostRecentView.details.price.replace(/[^0-9]/g, ''));
        
        // Price variation (±15%)
        const priceVariation = basePrice * 0.15;
        const price = basePrice + (Math.random() * priceVariation * 2 - priceVariation);
        
        // Format price as MXN
        const formattedPrice = new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            minimumFractionDigits: 0
        }).format(price);
        
        recommendations.push({
            id: `rec-${Date.now()}-${i}`,
            title: `Propiedad similar en ${location.colonia || location.ciudad}`,
            price: formattedPrice,
            address: `${location.colonia || location.ciudad}, ${location.estado || 'México'}`,
            bedrooms: Math.floor(1 + Math.random() * 3),
            bathrooms: Math.floor(1 + Math.random() * 2),
            size: Math.floor(60 + Math.random() * 100)
        });
    }
    
    // Render recommendations
    recommendations.forEach(property => {
        const card = document.createElement('div');
        card.className = 'property-card similar-property';
        card.innerHTML = `
            <div class="property-image">
                <img src="https://images.unsplash.com/photo-1600488689109-5fab0f5b6c86?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                     alt="${property.title}">
                <div class="property-badge sale">En Venta</div>
                <div class="property-badge mx">México</div>
            </div>
            <div class="property-content">
                <div class="property-price">
                    <i class="fas fa-coins"></i> 
                    <span class="mxn-price">${property.price}</span>
                </div>
                <div class="property-address">
                    <i class="fas fa-map-marker-alt"></i>
                    ${property.address}
                </div>
                <div class="property-stats">
                    <div class="stat">
                        <i class="fas fa-bed"></i>
                        ${property.bedrooms} recámaras
                    </div>
                    <div class="stat">
                        <i class="fas fa-bath"></i>
                        ${property.bathrooms} baños
                    </div>
                    <div class="stat">
                        <i class="fas fa-ruler-combined"></i>
                        ${property.size} m²
                    </div>
                </div>
                <p class="property-description">Propiedad en ${property.address} con características similares a la que has visto recientemente.</p>
            </div>
        `;
        recommendationsGrid.appendChild(card);
    });
}

// ===== HELPER FUNCTIONS =====
function getLocalStorageItem(key, defaultValue) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : defaultValue;
    } catch (e) {
        console.error(`Error parsing localStorage item ${key}:`, e);
        return defaultValue;
    }
}

function setLocalStorageItem(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
        return true;
    } catch (e) {
        console.error(`Error storing item ${key} in localStorage:`, e);
        return false;
    }
}

function generatePropertyId(card) {
    const address = card.querySelector('.property-address')?.textContent || '';
    const price = card.querySelector('.property-price')?.textContent || '';
    return `prop-${address.replace(/\W+/g, '-')}-${price.replace(/\W+/g, '')}`;
}