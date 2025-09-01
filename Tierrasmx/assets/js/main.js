document.addEventListener('DOMContentLoaded', function() {
    initializeMobileMenu();
    initializeSearchForm();
    initializeFavoriteButtons();
    initializeScrollEffects();
    initializePropertyCards();
    initializeRecommendationSystem();
});

function initializeMobileMenu() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileNav = document.getElementById('mobileNav');
    
    if (mobileMenuBtn && mobileNav) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileNav.classList.toggle('active');
            
            const icon = mobileMenuBtn.querySelector('i');
            if (mobileNav.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });

        const mobileLinks = mobileNav.querySelectorAll('.nav-link-mobile');
        mobileLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileNav.classList.remove('active');
                const icon = mobileMenuBtn.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            });
        });

        document.addEventListener('click', function(e) {
            if (!mobileMenuBtn.contains(e.target) && !mobileNav.contains(e.target)) {
                mobileNav.classList.remove('active');
                const icon = mobileMenuBtn.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }
}

function initializeSearchForm() {
    const searchInput = document.getElementById('locationSearch');
    const searchSelect = document.getElementById('propertyTypeSelect');
    const searchButton = document.getElementById('searchButton');
    const searchSuggestions = document.getElementById('searchSuggestions');

    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });

        searchInput.addEventListener('input', function() {
            const value = this.value.toLowerCase();
            if (value.length > 1) {
                showSearchSuggestions(value);
            } else {
                hideSearchSuggestions();
            }
        });
    }

    if (searchButton) {
        searchButton.addEventListener('click', function(e) {
            e.preventDefault();
            performSearch();
        });
    }

    document.addEventListener('click', function(e) {
        if (searchInput && !searchInput.contains(e.target) && 
            searchSuggestions && !searchSuggestions.contains(e.target)) {
            hideSearchSuggestions();
        }
    });
}

function performSearch() {
    const searchInput = document.getElementById('locationSearch');
    const searchSelect = document.getElementById('propertyTypeSelect');
    const searchButton = document.getElementById('searchButton');

    const location = searchInput.value.trim();
    const propertyType = searchSelect.value;

    if (!location) {
        showNotification('Por favor ingresa una ubicación para buscar', 'warning');
        searchInput.focus();
        return;
    }

    const originalText = searchButton.innerHTML;
    searchButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
    searchButton.disabled = true;

    setTimeout(() => {
        searchButton.innerHTML = originalText;
        searchButton.disabled = false;

        showNotification(`Buscando ${propertyType ? getPropertyTypeLabel(propertyType) : 'propiedades'} en ${location}...`, 'success');
        
        const propertiesSection = document.querySelector('.section-featured');
        if (propertiesSection) {
            propertiesSection.scrollIntoView({ behavior: 'smooth' });
        }
    }, 1500);
}

function showSearchSuggestions(query) {
    const searchSuggestions = document.getElementById('searchSuggestions');
    if (!searchSuggestions) return;
    
    const suggestions = mexicanLocations.filter(location => 
        location.name.toLowerCase().includes(query)
    ).slice(0, 5);
    
    if (suggestions.length === 0) {
        searchSuggestions.style.display = 'none';
        return;
    }
    
    searchSuggestions.innerHTML = suggestions.map(suggestion => {
        let badge = '';
        if (suggestion.type === 'estado') badge = '<span class="btn btn-ghost btn-sm">Estado</span>';
        if (suggestion.type === 'ciudad') badge = '<span class="btn btn-ghost btn-sm">Ciudad</span>';
        if (suggestion.type === 'colonia') badge = '<span class="btn btn-ghost btn-sm">Colonia</span>';
        
        return `
            <div class="suggestion-item" data-id="${suggestion.id}" 
                 data-type="${suggestion.type}" data-parent="${suggestion.parent || ''}">
                <div class="d-flex justify-content-between align-items-center">
                    <span>${highlightMatch(suggestion.name, query)}</span>
                    ${badge}
                </div>
            </div>
        `;
    }).join('');
    
    searchSuggestions.style.display = 'block';
    
    searchSuggestions.querySelectorAll('.suggestion-item').forEach(item => {
        item.addEventListener('click', function() {
            const name = this.querySelector('span').textContent;
            document.getElementById('locationSearch').value = name;
            hideSearchSuggestions();
        });
    });
}

function hideSearchSuggestions() {
    const searchSuggestions = document.getElementById('searchSuggestions');
    if (searchSuggestions) {
        searchSuggestions.style.display = 'none';
    }
}

function highlightMatch(text, query) {
    const index = text.toLowerCase().indexOf(query);
    if (index === -1) return text;
    
    return `
        ${text.substring(0, index)}
        <span class="suggestion-highlight">${text.substring(index, index + query.length)}</span>
        ${text.substring(index + query.length)}
    `;
}

function getPropertyTypeLabel(type) {
    const labels = {
        'casa': 'Casas',
        'departamento': 'Departamentos',
        'terreno': 'Terrenos',
        'local-comercial': 'Locales Comerciales'
    };
    return labels[type] || type;
}

function initializeFavoriteButtons() {
    const favoriteButtons = document.querySelectorAll('.favorite-btn');

    favoriteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const icon = this.querySelector('i');
            const propertyCard = this.closest('.property-card');
            const propertyId = propertyCard.dataset.id;
            const isFavorited = icon.classList.contains('fas');

            if (isFavorited) {
                icon.classList.remove('fas', 'fa-heart');
                icon.classList.add('far', 'fa-heart');
                propertyCard.querySelector('.saved-badge')?.remove();

                let savedProperties = getLocalStorageItem('savedProperties', []);
                savedProperties = savedProperties.filter(p => p.id !== propertyId);
                setLocalStorageItem('savedProperties', savedProperties);

                showNotification('Removido de favoritos', 'info');
            } else {
                icon.classList.remove('far', 'fa-heart');
                icon.classList.add('fas', 'fa-heart');

                if (!propertyCard.querySelector('.saved-badge')) {
                    const badge = document.createElement('div');
                    badge.className = 'saved-badge';
                    badge.innerHTML = '<i class="fas fa-heart"></i> Guardado';
                    propertyCard.querySelector('.property-image').appendChild(badge);
                }

                const propertyDetails = getPropertyDetails(propertyCard);
                let savedProperties = getLocalStorageItem('savedProperties', []);

                savedProperties = savedProperties.filter(p => p.id !== propertyId);

                savedProperties.push({
                    id: propertyId,
                    timestamp: Date.now(),
                    details: propertyDetails
                });

                savedProperties = savedProperties.slice(-10);
                setLocalStorageItem('savedProperties', savedProperties);

                showNotification('Añadido a favoritos', 'success');
            }

            this.style.transform = 'scale(1.2)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    });
}

// Function to toggle save property (server-side)
function toggleSaveProperty(propertyId, buttonElement) {
    const icon = buttonElement.querySelector('i');
    const isSaved = icon.classList.contains('fas');

    const formData = new FormData();
    formData.append('property_id', propertyId);

    const url = isSaved ? 'unsave_property.php' : 'save_property.php';

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (isSaved) {
                icon.classList.remove('fas');
                icon.classList.add('far');
                buttonElement.closest('.property-card').querySelector('.saved-badge')?.remove();
                showNotification('Propiedad eliminada de guardadas', 'info');
            } else {
                icon.classList.remove('far');
                icon.classList.add('fas');
                showNotification('Propiedad guardada', 'success');
            }
        } else {
            if (data.message === 'Usuario no autenticado') {
                showNotification('Debes iniciar sesión para guardar propiedades', 'warning');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 2000);
            } else {
                showNotification(data.message, 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al procesar la solicitud', 'error');
    });

    buttonElement.style.transform = 'scale(1.2)';
    setTimeout(() => {
        buttonElement.style.transform = 'scale(1)';
    }, 150);
}

// Function to toggle favorite property (server-side)
function toggleFavoriteProperty(propertyId, buttonElement) {
    const icon = buttonElement.querySelector('i');
    const isFavorited = icon.classList.contains('fas');

    const formData = new FormData();
    formData.append('property_id', propertyId);

    const url = isFavorited ? 'unfavorite_property.php' : 'favorite_property.php';

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (isFavorited) {
                icon.classList.remove('fas');
                icon.classList.add('far');
                showNotification('Propiedad eliminada de favoritos', 'info');
            } else {
                icon.classList.remove('far');
                icon.classList.add('fas');
                showNotification('Propiedad agregada a favoritos', 'success');
            }
        } else {
            if (data.message === 'Usuario no autenticado') {
                showNotification('Debes iniciar sesión para agregar a favoritos', 'warning');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 2000);
            } else {
                showNotification(data.message, 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al procesar la solicitud', 'error');
    });

    buttonElement.style.transform = 'scale(1.2)';
    setTimeout(() => {
        buttonElement.style.transform = 'scale(1)';
    }, 150);
}

function initializeScrollEffects() {
    let lastScrollTop = 0;
    const header = document.querySelector('.header');
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            header.style.transform = 'translateY(-100%)';
        } else {
            header.style.transform = 'translateY(0)';
        }
        
        lastScrollTop = scrollTop;
    });

    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    const animateElements = document.querySelectorAll('.property-card, .service-card');
    animateElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
}

function initializePropertyCards() {
    const propertyCards = document.querySelectorAll('.property-card');
    
    propertyCards.forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.closest('.favorite-btn')) {
                return;
            }
            
            const price = this.querySelector('.property-price').textContent;
            const address = this.querySelector('.property-address').textContent.trim();
            
            markPropertyAsViewed(this.dataset.id, this);
            
            showNotification(`Abriendo detalles para propiedad en ${address}`, 'info');
        });
    });
}

function markPropertyAsViewed(propertyId, cardElement) {
    if (!cardElement.classList.contains('viewed-property')) {
        cardElement.classList.add('viewed-property');
        
        if (!cardElement.querySelector('.viewed-badge') && 
            !cardElement.querySelector('.saved-badge')) {
            const badge = document.createElement('div');
            badge.className = 'viewed-badge';
            badge.innerHTML = '<i class="fas fa-eye"></i> Visto';
            cardElement.querySelector('.property-image').appendChild(badge);
        }
    }
    
    let viewedProperties = getLocalStorageItem('viewedProperties', []);
    
    viewedProperties = viewedProperties.filter(p => p.id !== propertyId);
    
    viewedProperties.push({
        id: propertyId,
        timestamp: Date.now(),
        details: getPropertyDetails(cardElement)
    });
    
    viewedProperties = viewedProperties.slice(-10);
    
    setLocalStorageItem('viewedProperties', viewedProperties);
    
    if (viewedProperties.length === 1) {
        showRecommendationsSection();
    }
}

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
