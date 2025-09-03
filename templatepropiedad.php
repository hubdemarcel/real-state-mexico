<?php
/*
 * Property Detail Template
 *
 * This file displays individual property details based on the property ID
 * passed as a URL parameter. It loads data from the properties JSON file.
 */

// Start session for user authentication
session_start();

// Include header
$pageTitle = 'Propiedad - Tierras.mx';
include 'header.php';

// Get property ID from URL parameter
$propertyId = isset($_GET['id']) ? $_GET['id'] : '';

// Load properties from JSON file
$propertiesFile = 'Tierrasmx/assets/data/properties-mx.json';
$properties = [];
$property = null;

if (file_exists($propertiesFile)) {
    $jsonContent = file_get_contents($propertiesFile);
    $properties = json_decode($jsonContent, true);

    // Find the property by ID
    foreach ($properties as $prop) {
        if ($prop['id'] === $propertyId) {
            $property = $prop;
            break;
        }
    }
}

// If property not found, show error
if (!$property) {
    echo '<div class="container" style="padding: 3rem 0; text-align: center;">';
    echo '<h1>Propiedad no encontrada</h1>';
    echo '<p>La propiedad que buscas no existe o ha sido removida.</p>';
    echo '<a href="comprar.php" class="btn btn-primary">Ver todas las propiedades</a>';
    echo '</div>';
    include 'footer.php';
    exit;
}

// Extract property data
$title = $property['title_es'] ?? 'Propiedad';
$price = $property['price'] ?? 0;
$currency = $property['currency'] ?? 'MXN';
$propertyType = $property['property_type'] ?? 'casa';
$rentalType = $property['rental_type'] ?? null;
$location = $property['location'] ?? [];
$features = $property['features'] ?? [];

// Format price based on rental or sale
if ($rentalType === 'monthly') {
    $formattedPrice = number_format($price, 0, ',', '.') . ' ' . $currency . '/mes';
    $priceLabel = 'Renta Mensual';
    $actionText = 'Solicitar Renta';
} else {
    $formattedPrice = number_format($price, 0, ',', '.') . ' ' . $currency;
    $priceLabel = 'Precio de Venta';
    $actionText = 'Programar Visita';
}

// Get location info
$country = $location['country'] ?? 'México';
$state = $location['state'] ?? '';
$city = $location['city'] ?? '';
$neighborhood = $location['neighborhood'] ?? '';
$address = $location['address'] ?? '';

// Get property type in Spanish
$typeLabels = [
    'departamento' => 'Departamento',
    'casa' => 'Casa',
    'terreno' => 'Terreno',
    'rancho' => 'Rancho',
    'bodega' => 'Bodega'
];
$typeLabel = $typeLabels[$propertyType] ?? ucfirst($propertyType);

// Generate property description based on type
$description = '';
switch ($propertyType) {
    case 'departamento':
        $description = "Este elegante departamento ofrece una experiencia de vida única. ";
        if (isset($features['bedrooms']) && isset($features['bathrooms'])) {
            $description .= "Cuenta con {$features['bedrooms']} recámaras y {$features['bathrooms']} baños completos.";
        }
        break;
    case 'casa':
        $description = "Hermosa casa familiar con amplios espacios. ";
        if (isset($features['bedrooms']) && isset($features['bathrooms'])) {
            $description .= "Dispone de {$features['bedrooms']} recámaras y {$features['bathrooms']} baños.";
        }
        break;
    case 'terreno':
        $description = "Excelente terreno con gran potencial de desarrollo. ";
        if (isset($features['land_size'])) {
            $description .= "Cuenta con {$features['land_size']} metros cuadrados de terreno.";
        }
        break;
    case 'rancho':
        $description = "Rancho agrícola productivo con tierras fértiles. ";
        if (isset($features['land_size'])) {
            $description .= "Cuenta con {$features['land_size']} metros cuadrados de terreno agrícola.";
        }
        break;
    case 'bodega':
        $description = "Bodega industrial moderna con excelente ubicación. ";
        if (isset($features['construction_size'])) {
            $description .= "Cuenta con {$features['construction_size']} metros cuadrados construidos.";
        }
        break;
    default:
        $description = "Propiedad única con excelentes características.";
}
?>

<!-- Property Detail Section -->
<section class="property-detail" style="padding: 3rem 0;">
    <div class="container">
        <div class="property-container" style="display: grid; grid-template-columns: 3fr 1fr; gap: 2rem;">

            <!-- Property Main Content -->
            <div class="property-main" style="background-color: white; border-radius: 0.75rem; overflow: hidden; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);">

                <!-- Property Gallery -->
                <div class="property-gallery" style="position: relative;">
                    <div class="gallery-main" style="height: 500px; overflow: hidden; position: relative;">
                        <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="<?php echo htmlspecialchars($title); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="property-badge" style="position: absolute; top: 1rem; left: 1rem; padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 600; color: #ffffff; z-index: 2; background-color: <?php echo $rentalType === 'monthly' ? '#2563eb' : '#059669'; ?>;">
                            <i class="fas fa-<?php echo $rentalType === 'monthly' ? 'calendar-check' : 'home'; ?>"></i> <?php echo $rentalType === 'monthly' ? 'En Renta' : 'En Venta'; ?>
                        </div>
                        <button class="favorite-btn" aria-label="Guardar propiedad" style="position: absolute; top: 1rem; right: 1rem; width: 3rem; height: 3rem; background-color: rgba(255, 255, 255, 0.85); border: none; border-radius: 50%; cursor: pointer; transition: all 0.3s ease; z-index: 2; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                            <i class="far fa-heart" style="color: #374151; font-size: 1.25rem;"></i>
                        </button>
                    </div>
                </div>

                <!-- Property Header -->
                <div class="property-header" style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb;">
                    <div class="property-status" style="display: inline-block; background-color: rgba(5, 150, 105, 0.1); color: #059669; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 500; margin-bottom: 0.75rem;">Disponible</div>
                    <div class="property-price" style="font-size: 2.25rem; font-weight: 700; color: #2563eb; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-coins"></i>
                        <span class="mxn-price" style="color: #059669; font-weight: 800;"><?php echo $formattedPrice; ?></span>
                    </div>
                    <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 1rem;">
                        <?php echo $priceLabel; ?>
                    </div>
                    <h1 class="property-title" style="font-size: 1.75rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($title); ?></h1>
                    <div class="property-address" style="display: flex; align-items: center; color: #6b7280; margin-bottom: 1rem; font-size: 1.125rem;">
                        <i class="fas fa-map-marker-alt" style="margin-right: 0.5rem; color: #2563eb;"></i>
                        <?php echo htmlspecialchars($neighborhood ? $neighborhood . ', ' : ''); ?><?php echo htmlspecialchars($city); ?><?php echo htmlspecialchars($state ? ', ' . $state : ''); ?>
                    </div>
                </div>

                <!-- Property Actions -->
                <div class="property-actions" style="padding: 1rem 1.5rem; background-color: #f9fafb; border-top: 1px solid #e5e7eb; display: flex; flex-wrap: wrap; gap: 1rem;">
                    <a href="#" class="btn-action" style="flex: 1; min-width: 150px; background-color: #2563eb; color: white; padding: 0.75rem; border-radius: 0.375rem; font-weight: 500; transition: all 0.3s ease; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; text-decoration: none;">
                        <i class="fas fa-<?php echo $rentalType === 'monthly' ? 'calendar-plus' : 'calendar-check'; ?>"></i> <?php echo $actionText; ?>
                    </a>
                    <a href="#" class="btn-action secondary" style="flex: 1; min-width: 150px; background-color: #f3f4f6; color: #374151; padding: 0.75rem; border-radius: 0.375rem; font-weight: 500; transition: all 0.3s ease; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; text-decoration: none;">
                        <i class="fas fa-download"></i> Descargar PDF
                    </a>
                </div>

                <!-- Property Details -->
                <div class="property-details" style="padding: 2rem;">
                    <h2 class="section-title" style="font-size: 1.5rem; font-weight: 600; color: #1f2937; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-home"></i> Descripción
                    </h2>
                    <p class="property-description" style="line-height: 1.6; color: #374151; margin-bottom: 2rem; font-size: 1.05rem;">
                        <?php echo htmlspecialchars($description); ?>
                    </p>

                    <h2 class="section-title" style="font-size: 1.5rem; font-weight: 600; color: #1f2937; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-list"></i> Características
                    </h2>

                    <div class="property-features" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 2rem;">
                        <div class="feature-category">
                            <h3 class="feature-category-title" style="font-size: 1.125rem; font-weight: 600; color: #1f2937; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-info-circle"></i> Información General
                            </h3>
                            <ul class="feature-list" style="list-style: none;">
                                <li style="margin-bottom: 0.5rem; display: flex; align-items: flex-start; gap: 0.5rem; color: #374151;">
                                    <i class="fas fa-check-circle" style="color: #2563eb; font-size: 0.875rem; margin-top: 0.25rem;"></i>
                                    <strong>Tipo:</strong> <?php echo htmlspecialchars($typeLabel); ?>
                                </li>
                                <li style="margin-bottom: 0.5rem; display: flex; align-items: flex-start; gap: 0.5rem; color: #374151;">
                                    <i class="fas fa-check-circle" style="color: #2563eb; font-size: 0.875rem; margin-top: 0.25rem;"></i>
                                    <strong>Precio:</strong> <?php echo $formattedPrice; ?>
                                </li>
                                <li style="margin-bottom: 0.5rem; display: flex; align-items: flex-start; gap: 0.5rem; color: #374151;">
                                    <i class="fas fa-check-circle" style="color: #2563eb; font-size: 0.875rem; margin-top: 0.25rem;"></i>
                                    <strong>Ubicación:</strong> <?php echo htmlspecialchars($city . ($state ? ', ' . $state : '')); ?>
                                </li>
                                <?php if (isset($features['land_size'])): ?>
                                    <li style="margin-bottom: 0.5rem; display: flex; align-items: flex-start; gap: 0.5rem; color: #374151;">
                                        <i class="fas fa-check-circle" style="color: #2563eb; font-size: 0.875rem; margin-top: 0.25rem;"></i>
                                        <strong>Terreno:</strong> <?php echo number_format($features['land_size'], 0, ',', '.'); ?> m²
                                    </li>
                                <?php endif; ?>
                                <?php if (isset($features['construction_size'])): ?>
                                    <li style="margin-bottom: 0.5rem; display: flex; align-items: flex-start; gap: 0.5rem; color: #374151;">
                                        <i class="fas fa-check-circle" style="color: #2563eb; font-size: 0.875rem; margin-top: 0.25rem;"></i>
                                        <strong>Construcción:</strong> <?php echo number_format($features['construction_size'], 0, ',', '.'); ?> m²
                                    </li>
                                    <?php if ($rentalType === 'monthly' && isset($features['minimum_stay'])): ?>
                                        <li style="margin-bottom: 0.5rem; display: flex; align-items: flex-start; gap: 0.5rem; color: #374151;">
                                            <i class="fas fa-check-circle" style="color: #2563eb; font-size: 0.875rem; margin-top: 0.25rem;"></i>
                                            <strong>Estancia Mínima:</strong> <?php echo $features['minimum_stay']; ?> días
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($rentalType === 'monthly' && isset($features['pets_allowed'])): ?>
                                        <li style="margin-bottom: 0.5rem; display: flex; align-items: flex-start; gap: 0.5rem; color: #374151;">
                                            <i class="fas fa-check-circle" style="color: #2563eb; font-size: 0.875rem; margin-top: 0.25rem;"></i>
                                            <strong>Mascotas:</strong> <?php echo $features['pets_allowed'] ? 'Permitidas' : 'No permitidas'; ?>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <?php if (!empty($features) && count($features) > 2): ?>
                            <div class="feature-category">
                                <h3 class="feature-category-title" style="font-size: 1.125rem; font-weight: 600; color: #1f2937; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-star"></i> Características Destacadas
                                </h3>
                                <ul class="feature-list" style="list-style: none;">
                                    <?php foreach ($features as $key => $value): ?>
                                        <?php if ($key !== 'land_size' && $key !== 'construction_size' && $value): ?>
                                            <li style="margin-bottom: 0.5rem; display: flex; align-items: flex-start; gap: 0.5rem; color: #374151;">
                                                <i class="fas fa-check-circle" style="color: #2563eb; font-size: 0.875rem; margin-top: 0.25rem;"></i>
                                                <strong><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $key))); ?>:</strong> <?php echo htmlspecialchars(is_bool($value) ? ($value ? 'Sí' : 'No') : $value); ?>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Property Sidebar -->
            <div class="property-sidebar" style="background-color: white; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); height: fit-content;">
                <div class="sidebar-price" style="font-size: 1.75rem; font-weight: 700; color: #2563eb; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-coins"></i>
                    <span class="mxn-price" style="color: #059669; font-weight: 800;"><?php echo $formattedPrice; ?></span>
                </div>
                <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 1.5rem;">
                    <?php echo $priceLabel; ?>
                </div>
                <div class="sidebar-status" style="display: inline-block; background-color: rgba(5, 150, 105, 0.1); color: #059669; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 500; margin-bottom: 1.5rem;">Disponible</div>

                <div class="sidebar-agent" style="display: flex; align-items: flex-start; gap: 1rem; padding: 1.5rem 0; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; margin: 1.5rem 0;">
                    <div class="sidebar-agent-avatar" style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; flex-shrink: 0; border: 2px solid #2563eb;">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Agente" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div class="sidebar-agent-info" style="flex: 1;">
                        <div class="sidebar-agent-name" style="font-weight: 600; color: #1f2937; margin-bottom: 0.25rem;">Juan Martínez</div>
                        <div class="sidebar-agent-company" style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">RE/MAX Capital</div>
                        <div class="sidebar-agent-rating" style="display: flex; align-items: center; gap: 0.25rem; color: #f59e0b; font-size: 0.875rem;">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            (245 reseñas)
                        </div>
                    </div>
                </div>

                <div class="sidebar-actions" style="display: flex; flex-direction: column; gap: 1rem;">
                    <a href="#" class="btn-action" style="background-color: #2563eb; color: white; padding: 0.75rem; border-radius: 0.375rem; font-weight: 500; transition: all 0.3s ease; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; text-decoration: none;">
                        <i class="fas fa-phone"></i> Llamar al Agente
                    </a>
                    <a href="#" class="btn-action" style="background-color: #059669; color: white; padding: 0.75rem; border-radius: 0.375rem; font-weight: 500; transition: all 0.3s ease; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; text-decoration: none;">
                        <i class="fas fa-<?php echo $rentalType === 'monthly' ? 'calendar-plus' : 'calendar-check'; ?>"></i> <?php echo $actionText; ?>
                    </a>
                </div>

                <div class="sidebar-contact" style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1.5rem;">
                    <div class="contact-method" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; transition: all 0.3s ease;">
                        <i class="fas fa-mobile-alt" style="color: #2563eb; font-size: 1.25rem;"></i>
                        <div>
                            <div class="contact-label" style="font-weight: 500; color: #1f2937;">Teléfono</div>
                            <div class="contact-value" style="color: #6b7280;">55 1234 5678</div>
                        </div>
                    </div>
                    <div class="contact-method" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; transition: all 0.3s ease;">
                        <i class="fas fa-envelope" style="color: #2563eb; font-size: 1.25rem;"></i>
                        <div>
                            <div class="contact-label" style="font-weight: 500; color: #1f2937;">Correo</div>
                            <div class="contact-value" style="color: #6b7280;">juan.martinez@remax.com</div>
                        </div>
                    </div>
                </div>

                <div class="sidebar-additional" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
                    <div class="additional-info" style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; color: #374151;">
                        <i class="fas fa-calendar-check" style="color: #2563eb; font-size: 1.25rem;"></i>
                        <div>
                            <div style="font-weight: 500; color: #1f2937;">Última actualización</div>
                            <div style="color: #6b7280;"><?php echo date('d/m/Y'); ?></div>
                        </div>
                    </div>
                    <div class="additional-info" style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; color: #374151;">
                        <i class="fas fa-id-card" style="color: #2563eb; font-size: 1.25rem;"></i>
                        <div>
                            <div style="font-weight: 500; color: #1f2937;">Número de listado</div>
                            <div style="color: #6b7280;"><?php echo htmlspecialchars($propertyId); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Property Recommendations -->
<section class="property-recommendations" style="padding: 3rem 0; background-color: #f9fafb;">
    <div class="container">
        <div class="recommendations-header" style="text-align: center; margin-bottom: 2rem;">
            <h2 class="recommendations-title" style="font-size: 2.25rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem;">Propiedades Similares</h2>
            <p class="recommendations-subtitle" style="font-size: 1.25rem; color: #6b7280;">Descubre más propiedades que podrían interesarte</p>
        </div>

        <div class="recommendations-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <?php
            // Get similar properties (same type or same city)
            $similarProperties = array_filter($properties, function($prop) use ($propertyId, $propertyType, $city) {
                return $prop['id'] !== $propertyId &&
                       ($prop['property_type'] === $propertyType || $prop['location']['city'] === $city);
            });
            $similarProperties = array_slice($similarProperties, 0, 3);

            foreach ($similarProperties as $similarProp):
                $similarPrice = number_format($similarProp['price'], 0, ',', '.') . ' ' . $similarProp['currency'];
                $similarTitle = $similarProp['title_es'] ?? 'Propiedad';
                $similarLocation = $similarProp['location']['city'] ?? '';
            ?>
                <div class="property-card" style="background-color: #ffffff; border-radius: 0.75rem; overflow: hidden; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); transition: all 0.3s ease; border: 1px solid #e5e7eb;">
                    <div class="property-image" style="position: relative; height: 180px; overflow: hidden;">
                        <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                             alt="<?php echo htmlspecialchars($similarTitle); ?>"
                             style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="property-badge" style="position: absolute; top: 0.75rem; left: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600; color: #ffffff; z-index: 2; background-color: #059669;">En Venta</div>
                    </div>
                    <div class="property-content" style="padding: 1.5rem;">
                        <div class="property-price" style="font-size: 1.5rem; font-weight: 700; color: #2563eb; margin-bottom: 0.5rem;">
                            <i class="fas fa-coins" style="margin-right: 0.5rem;"></i>
                            <span class="mxn-price"><?php echo $similarPrice; ?></span>
                        </div>
                        <div class="property-address" style="display: flex; align-items: center; color: #6b7280; margin-bottom: 1rem; font-size: 0.95rem;">
                            <i class="fas fa-map-marker-alt" style="margin-right: 0.25rem; color: #2563eb;"></i>
                            <?php echo htmlspecialchars($similarLocation); ?>
                        </div>
                        <p class="property-description" style="color: #374151; line-height: 1.5; margin-bottom: 1rem; min-height: 60px;">
                            <?php echo htmlspecialchars(substr($similarTitle, 0, 100) . (strlen($similarTitle) > 100 ? '...' : '')); ?>
                        </p>
                        <a href="templatepropiedad.php?id=<?php echo $similarProp['id']; ?>" class="view-property" style="display: inline-flex; align-items: center; color: #2563eb; text-decoration: none; font-weight: 500; transition: color 0.2s ease;">
                            Ver propiedad <i class="fas fa-arrow-right" style="margin-left: 0.25rem; transition: transform 0.2s;"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="comprar.php" class="btn btn-ghost" style="padding: 0.5rem 1.5rem; border-radius: 0.375rem;">
                <i class="fas fa-sync-alt"></i> Ver más propiedades
            </a>
        </div>
    </div>
</section>

<script>
// Gallery functionality
document.addEventListener('DOMContentLoaded', function() {
    // Favorite button functionality
    const favoriteBtn = document.querySelector('.favorite-btn');
    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const icon = this.querySelector('i');
            const isFavorited = icon.classList.contains('fas');

            if (isFavorited) {
                icon.classList.remove('fas', 'fa-heart');
                icon.classList.add('far', 'fa-heart');
                showNotification('Removido de favoritos', 'info');
            } else {
                icon.classList.remove('far', 'fa-heart');
                icon.classList.add('fas', 'fa-heart');
                showNotification('Añadido a favoritos', 'success');
            }

            // Animation
            this.style.transform = 'scale(1.2)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    }
});

// Notification system
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    const notification = document.createElement('div');
    notification.className = 'notification';
    if (type !== 'info') notification.classList.add(type);

    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    notification.innerHTML = `
        <div class="notification-icon">
            <i class="fas ${icons[type]}"></i>
        </div>
        <span>${message}</span>
    `;

    document.body.appendChild(notification);

    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}
</script>

<?php include 'footer.php'; ?>