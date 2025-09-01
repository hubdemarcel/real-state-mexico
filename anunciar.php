<?php
$pageTitle = 'Anunciar Propiedad - Tierras.mx';
include 'header.php';
?>

<main class="container">
    <section class="section">
        <div class="section-header">
            <h1 class="section-title">Anunciar tu Propiedad</h1>
            <p class="section-subtitle">Llega a miles de compradores potenciales en México</p>
        </div>

        <div class="property-form-container">
            <form class="property-form" id="propertyForm">
                <div class="form-section">
                    <h3 class="form-section-title">Información Básica</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="propertyType" class="form-label">Tipo de Propiedad *</label>
                            <select id="propertyType" name="propertyType" class="form-select" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="casa">Casa</option>
                                <option value="departamento">Departamento</option>
                                <option value="terreno">Terreno</option>
                                <option value="local-comercial">Local Comercial</option>
                                <option value="oficina">Oficina</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="transactionType" class="form-label">Tipo de Transacción *</label>
                            <select id="transactionType" name="transactionType" class="form-select" required>
                                <option value="venta">Venta</option>
                                <option value="renta">Renta</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="propertyTitle" class="form-label">Título del Anuncio *</label>
                        <input type="text" id="propertyTitle" name="propertyTitle" class="form-input"
                               placeholder="Ej: Hermosa casa familiar en zona residencial" required>
                    </div>

                    <div class="form-group">
                        <label for="propertyDescription" class="form-label">Descripción *</label>
                        <textarea id="propertyDescription" name="propertyDescription" class="form-textarea"
                                  placeholder="Describe tu propiedad detalladamente..." rows="6" required></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">Ubicación</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="estado" class="form-label">Estado *</label>
                            <select id="estado" name="estado" class="form-select" required>
                                <option value="">Seleccionar estado</option>
                                <option value="cdmx">Ciudad de México</option>
                                <option value="jalisco">Jalisco</option>
                                <option value="nuevo-leon">Nuevo León</option>
                                <option value="puebla">Puebla</option>
                                <option value="guanajuato">Guanajuato</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="ciudad" class="form-label">Ciudad/Municipio *</label>
                            <input type="text" id="ciudad" name="ciudad" class="form-input" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="colonia" class="form-label">Colonia *</label>
                            <input type="text" id="colonia" name="colonia" class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label for="codigoPostal" class="form-label">Código Postal</label>
                            <input type="text" id="codigoPostal" name="codigoPostal" class="form-input">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="direccion" class="form-label">Dirección Completa</label>
                        <input type="text" id="direccion" name="direccion" class="form-input"
                               placeholder="Calle, número, referencias">
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">Características</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="precio" class="form-label">Precio (MXN) *</label>
                            <input type="number" id="precio" name="precio" class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label for="superficieTotal" class="form-label">Superficie Total (m²)</label>
                            <input type="number" id="superficieTotal" name="superficieTotal" class="form-input">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="superficieConstruida" class="form-label">Superficie Construida (m²)</label>
                            <input type="number" id="superficieConstruida" name="superficieConstruida" class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="recamaras" class="form-label">Recámaras</label>
                            <input type="number" id="recamaras" name="recamaras" class="form-input" min="0">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="banos" class="form-label">Baños</label>
                            <input type="number" id="banos" name="banos" class="form-input" min="0" step="0.5">
                        </div>

                        <div class="form-group">
                            <label for="estacionamientos" class="form-label">Estacionamientos</label>
                            <input type="number" id="estacionamientos" name="estacionamientos" class="form-input" min="0">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">Amenidades y Características</h3>

                    <div class="amenities-grid">
                        <label class="amenity-item">
                            <input type="checkbox" name="amenities[]" value="seguridad">
                            <span class="amenity-label">Seguridad 24/7</span>
                        </label>
                        <label class="amenity-item">
                            <input type="checkbox" name="amenities[]" value="estacionamiento">
                            <span class="amenity-label">Estacionamiento</span>
                        </label>
                        <label class="amenity-item">
                            <input type="checkbox" name="amenities[]" value="gas-natural">
                            <span class="amenity-label">Gas Natural</span>
                        </label>
                        <label class="amenity-item">
                            <input type="checkbox" name="amenities[]" value="agua-potable">
                            <span class="amenity-label">Agua Potable</span>
                        </label>
                        <label class="amenity-item">
                            <input type="checkbox" name="amenities[]" value="alberca">
                            <span class="amenity-label">Alberca</span>
                        </label>
                        <label class="amenity-item">
                            <input type="checkbox" name="amenities[]" value="jardin">
                            <span class="amenity-label">Jardín</span>
                        </label>
                        <label class="amenity-item">
                            <input type="checkbox" name="amenities[]" value="gimnasio">
                            <span class="amenity-label">Gimnasio</span>
                        </label>
                        <label class="amenity-item">
                            <input type="checkbox" name="amenities[]" value="ascensor">
                            <span class="amenity-label">Ascensor</span>
                        </label>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">Fotos de la Propiedad</h3>

                    <div class="photo-upload">
                        <div class="photo-upload-area" id="photoUploadArea">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <p class="upload-text">Arrastra tus fotos aquí o <span class="upload-link">haz clic para seleccionar</span></p>
                            <p class="upload-hint">Máximo 20 fotos, cada una menor a 10MB</p>
                        </div>
                        <input type="file" id="photoInput" name="photos[]" multiple accept="image/*" style="display: none;">
                    </div>

                    <div class="photo-preview" id="photoPreview"></div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">Información de Contacto</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="contactName" class="form-label">Nombre Completo *</label>
                            <input type="text" id="contactName" name="contactName" class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label for="contactPhone" class="form-label">Teléfono *</label>
                            <input type="tel" id="contactPhone" name="contactPhone" class="form-input" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="contactEmail" class="form-label">Correo Electrónico *</label>
                            <input type="email" id="contactEmail" name="contactEmail" class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label for="contactCompany" class="form-label">Empresa/Inmobiliaria</label>
                            <input type="text" id="contactCompany" name="contactCompany" class="form-input">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="previewBtn">Vista Previa</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> Publicar Propiedad
                    </button>
                </div>
            </form>
        </div>
    </section>
</main>

<style>
.property-form-container {
    max-width: 800px;
    margin: 0 auto;
}

.property-form {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.form-section {
    padding: 2rem;
    border-bottom: 1px solid var(--gray-200);
}

.form-section:last-child {
    border-bottom: none;
}

.form-section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--gray-700);
}

.form-input,
.form-select,
.form-textarea {
    padding: 0.75rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-md);
    font-size: 0.875rem;
    transition: var(--transition);
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 120px;
}

.amenities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.amenity-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: var(--radius-md);
    transition: var(--transition);
}

.amenity-item:hover {
    background-color: var(--gray-50);
}

.amenity-item input[type="checkbox"] {
    width: 1.25rem;
    height: 1.25rem;
    accent-color: var(--primary);
}

.amenity-label {
    font-size: 0.875rem;
    color: var(--gray-700);
}

.photo-upload-area {
    border: 2px dashed var(--gray-300);
    border-radius: var(--radius-lg);
    padding: 3rem 2rem;
    text-align: center;
    cursor: pointer;
    transition: var(--transition);
}

.photo-upload-area:hover {
    border-color: var(--primary);
    background-color: var(--gray-50);
}

.upload-icon {
    font-size: 3rem;
    color: var(--gray-400);
    margin-bottom: 1rem;
}

.upload-text {
    color: var(--gray-600);
    margin-bottom: 0.5rem;
}

.upload-link {
    color: var(--primary);
    font-weight: 500;
}

.upload-hint {
    font-size: 0.75rem;
    color: var(--gray-500);
}

.photo-preview {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.photo-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: var(--radius-md);
    overflow: hidden;
}

.photo-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-remove {
    position: absolute;
    top: 0.25rem;
    right: 0.25rem;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    border: none;
    border-radius: 50%;
    width: 1.5rem;
    height: 1.5rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
}

.form-actions {
    padding: 2rem;
    background-color: var(--gray-50);
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: var(--radius-md);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background-color: var(--primary);
    color: white;
}

.btn-primary:hover {
    background-color: var(--primary-dark);
}

.btn-secondary {
    background-color: white;
    color: var(--gray-700);
    border: 1px solid var(--gray-300);
}

.btn-secondary:hover {
    background-color: var(--gray-50);
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }

    .form-section {
        padding: 1.5rem;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const photoUploadArea = document.getElementById('photoUploadArea');
    const photoInput = document.getElementById('photoInput');
    const photoPreview = document.getElementById('photoPreview');
    const propertyForm = document.getElementById('propertyForm');

    // Photo upload functionality
    photoUploadArea.addEventListener('click', () => {
        photoInput.click();
    });

    photoInput.addEventListener('change', handlePhotoUpload);

    function handlePhotoUpload(e) {
        const files = Array.from(e.target.files);

        files.forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const photoItem = createPhotoItem(e.target.result, file.name);
                    photoPreview.appendChild(photoItem);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    function createPhotoItem(src, name) {
        const photoItem = document.createElement('div');
        photoItem.className = 'photo-item';

        photoItem.innerHTML = `
            <img src="${src}" alt="${name}">
            <button type="button" class="photo-remove" aria-label="Remover foto">
                <i class="fas fa-times"></i>
            </button>
        `;

        photoItem.querySelector('.photo-remove').addEventListener('click', function() {
            photoItem.remove();
        });

        return photoItem;
    }

    // Form submission
    propertyForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publicando...';
        submitBtn.disabled = true;

        // Simulate form submission
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;

            // Show success message
            showNotification('¡Propiedad publicada exitosamente!', 'success');

            // Reset form
            propertyForm.reset();
            photoPreview.innerHTML = '';
        }, 2000);
    });

    // Preview functionality
    document.getElementById('previewBtn').addEventListener('click', function() {
        // Basic form validation
        const requiredFields = propertyForm.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = '#ef4444';
                isValid = false;
            } else {
                field.style.borderColor = '#d1d5db';
            }
        });

        if (!isValid) {
            showNotification('Por favor completa todos los campos requeridos', 'error');
            return;
        }

        showNotification('Vista previa próximamente disponible', 'info');
    });
});

function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;

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

    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        color: var(--gray-900);
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        font-weight: 500;
        max-width: 400px;
        border-left: 4px solid #2563eb;
        animation: slideIn 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.75rem;
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

<?php
include 'footer.php';
?>