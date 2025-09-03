// Dashboard JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Navigation functionality
    const navLinks = document.querySelectorAll('.dashboard-nav-link');
    const sections = document.querySelectorAll('.dashboard-section');

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            // Remove active class from all links
            navLinks.forEach(l => l.classList.remove('active'));

            // Add active class to clicked link
            this.classList.add('active');

            // Hide all sections
            sections.forEach(section => section.style.display = 'none');

            // Show selected section
            const targetId = this.getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.style.display = 'block';
            }
        });
    });
});

// Alert management functions
function createAlert() {
    const alertSection = document.getElementById('alerts');
    const existingForm = alertSection.querySelector('.alert-form');

    if (existingForm) {
        existingForm.remove();
        return;
    }

    const formHTML = `
        <div class="alert-form">
            <h4>Crear Nueva Alerta</h4>
            <form onsubmit="submitAlert(event)">
                <div class="form-group">
                    <label for="alert_type">Tipo de Alerta</label>
                    <select id="alert_type" name="alert_type" required>
                        <option value="price">Por Precio</option>
                        <option value="location">Por Ubicación</option>
                        <option value="property_type">Por Tipo de Propiedad</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="criteria">Criterios</label>
                    <textarea id="criteria" name="criteria" placeholder="Ej: CDMX, precio máximo 5M, casa" required></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Crear Alerta</button>
                    <button type="button" class="btn btn-secondary" onclick="cancelAlertForm()">Cancelar</button>
                </div>
            </form>
        </div>
    `;

    alertSection.insertBefore(
        document.createRange().createContextualFragment(formHTML),
        alertSection.querySelector('.alerts-list') || alertSection.querySelector('.empty-state').nextSibling
    );
}

function submitAlert(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const alertType = formData.get('alert_type');
    const criteria = formData.get('criteria');

    fetch('create_alert.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Alerta creada exitosamente', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al crear la alerta', 'error');
    });
}

function cancelAlertForm() {
    const form = document.querySelector('.alert-form');
    if (form) {
        form.remove();
    }
}

function editAlert(alertId) {
    // For now, just show a message. In a full implementation, this would open an edit form
    showNotification('Función de edición próximamente disponible', 'info');
}

function deleteAlert(alertId) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta alerta?')) {
        return;
    }

    const formData = new FormData();
    formData.append('alert_id', alertId);

    fetch('delete_alert.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Alerta eliminada exitosamente', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al eliminar la alerta', 'error');
    });
}

// Property management functions
function toggleSaveProperty(propertyId, button) {
    // Implement save/unsave functionality
    const isSaved = button.querySelector('i').classList.contains('fas');
    const icon = button.querySelector('i');

    if (isSaved) {
        icon.classList.remove('fas');
        icon.classList.add('far');
        showNotification('Propiedad removida de guardadas', 'info');
    } else {
        icon.classList.remove('far');
        icon.classList.add('fas');
        showNotification('Propiedad guardada', 'success');
    }
}

function toggleFavoriteProperty(propertyId, button) {
    // Implement favorite/unfavorite functionality
    const isFavorited = button.querySelector('i').classList.contains('fas');
    const icon = button.querySelector('i');

    if (isFavorited) {
        icon.classList.remove('fas');
        icon.classList.add('far');
        showNotification('Propiedad removida de favoritas', 'info');
    } else {
        icon.classList.remove('far');
        icon.classList.add('fas');
        showNotification('Propiedad agregada a favoritas', 'success');
    }
}

// Notification system
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas ${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">&times;</button>
    `;

    // Add to page
    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function getNotificationIcon(type) {
    switch (type) {
        case 'success': return 'fa-check-circle';
        case 'error': return 'fa-exclamation-circle';
        case 'warning': return 'fa-exclamation-triangle';
        case 'info': default: return 'fa-info-circle';
    }
}

// Make functions globally available
window.createAlert = createAlert;
window.editAlert = editAlert;
window.deleteAlert = deleteAlert;
window.cancelAlertForm = cancelAlertForm;
window.submitAlert = submitAlert;
window.toggleSaveProperty = toggleSaveProperty;
window.toggleFavoriteProperty = toggleFavoriteProperty;