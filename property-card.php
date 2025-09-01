<?php
// Property Card Component
function renderPropertyCard($property) {
    $id = $property['id'] ?? 'default-id';
    $title = $property['title'] ?? 'Propiedad';
    $price = $property['price'] ?? 0;
    $location = $property['location'] ?? 'Ubicación no especificada';
    $bedrooms = $property['bedrooms'] ?? 'N/A';
    $bathrooms = $property['bathrooms'] ?? 'N/A';
    $size = $property['size'] ?? 'N/A';
    $image = $property['image'] ?? 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
    $type = $property['type'] ?? 'venta';
    $description = $property['description'] ?? 'Descripción no disponible';
    $features = $property['features'] ?? [];
    $badge = $type === 'renta' ? 'rent' : 'sale';

    $formattedPrice = number_format($price, 0, ',', '.') . ' MXN';
    ?>
    <div class="property-card" data-id="<?php echo $id; ?>">
        <div class="property-image">
            <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($title); ?>">
            <div class="property-badge <?php echo $badge; ?>">
                <?php echo $type === 'renta' ? 'En Renta' : 'En Venta'; ?>
            </div>
            <button class="save-btn" aria-label="Guardar propiedad" onclick="toggleSaveProperty('<?php echo $id; ?>', this)">
                <i class="far fa-bookmark"></i>
            </button>
            <button class="favorite-btn" aria-label="Agregar a favoritos" onclick="toggleFavoriteProperty('<?php echo $id; ?>', this)">
                <i class="far fa-heart"></i>
            </button>
        </div>
        <div class="property-content">
            <div class="property-price">
                <i class="fas fa-coins"></i>
                <span class="mxn-price">$<?php echo $formattedPrice; ?></span>
            </div>
            <div class="property-address">
                <i class="fas fa-map-marker-alt"></i>
                <?php echo htmlspecialchars($location); ?>
            </div>
            <div class="property-stats">
                <div class="stat">
                    <i class="fas fa-bed"></i>
                    <?php echo $bedrooms; ?> recámaras
                </div>
                <div class="stat">
                    <i class="fas fa-bath"></i>
                    <?php echo $bathrooms; ?> baños
                </div>
                <div class="stat">
                    <i class="fas fa-ruler-combined"></i>
                    <?php echo $size; ?> m²
                </div>
            </div>
            <?php if (!empty($features)): ?>
            <div class="property-features">
                <?php foreach ($features as $feature): ?>
                <span class="feature-badge <?php echo isset($feature['class']) ? $feature['class'] : ''; ?>">
                    <i class="fas fa-<?php echo $feature['icon'] ?? 'check'; ?>"></i>
                    <?php echo htmlspecialchars($feature['text']); ?>
                </span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <p class="property-description"><?php echo htmlspecialchars($description); ?></p>
            <a href="templatepropiedad.html?id=<?php echo $id; ?>" class="view-property">
                Ver propiedad <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    <?php
}
?>