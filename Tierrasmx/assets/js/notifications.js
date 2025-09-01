// ===== REAL-TIME NOTIFICATION SYSTEM =====

class NotificationManager {
    constructor() {
        this.notifications = [];
        this.unreadCount = 0;
        this.checkInterval = 30000; // Check every 30 seconds
        this.init();
    }

    init() {
        this.createNotificationUI();
        this.loadNotifications();
        this.startPolling();
        this.bindEvents();
    }

    createNotificationUI() {
        // Create notification dropdown in header
        const header = document.querySelector('.header-container');
        if (!header) return;

        const notificationHTML = `
            <div class="notification-container">
                <button class="notification-btn" id="notificationBtn" aria-label="Notificaciones">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                </button>
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h4>Notificaciones</h4>
                        <button class="mark-all-read" id="markAllRead">Marcar todas como leídas</button>
                    </div>
                    <div class="notification-list" id="notificationList">
                        <div class="notification-empty">
                            <i class="fas fa-bell-slash"></i>
                            <p>No tienes notificaciones</p>
                        </div>
                    </div>
                    <div class="notification-footer">
                        <a href="#" class="view-all-notifications">Ver todas las notificaciones</a>
                    </div>
                </div>
            </div>
        `;

        // Insert after user menu or search button
        const userMenu = header.querySelector('.user-menu') || header.querySelector('.secondary-actions');
        if (userMenu) {
            userMenu.insertAdjacentHTML('beforeend', notificationHTML);
        }
    }

    bindEvents() {
        // Toggle dropdown
        const notificationBtn = document.getElementById('notificationBtn');
        const dropdown = document.getElementById('notificationDropdown');

        if (notificationBtn && dropdown) {
            notificationBtn.addEventListener('click', (e) => {
                e.preventDefault();
                dropdown.classList.toggle('active');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!notificationBtn.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('active');
                }
            });
        }

        // Mark all as read
        const markAllRead = document.getElementById('markAllRead');
        if (markAllRead) {
            markAllRead.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }

        // Mark individual notifications as read
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('notification-item') || e.target.closest('.notification-item')) {
                const item = e.target.classList.contains('notification-item') ? e.target : e.target.closest('.notification-item');
                const notificationId = item.dataset.id;
                if (notificationId && !item.classList.contains('read')) {
                    this.markAsRead(notificationId);
                }
            }
        });
    }

    async loadNotifications() {
        try {
            const response = await fetch('get_notifications.php');
            const data = await response.json();

            if (data.success) {
                this.notifications = data.notifications;
                this.unreadCount = data.unread_count;
                this.updateUI();
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    updateUI() {
        this.updateBadge();
        this.renderNotifications();
    }

    updateBadge() {
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            if (this.unreadCount > 0) {
                badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    renderNotifications() {
        const list = document.getElementById('notificationList');
        if (!list) return;

        if (this.notifications.length === 0) {
            list.innerHTML = `
                <div class="notification-empty">
                    <i class="fas fa-bell-slash"></i>
                    <p>No tienes notificaciones</p>
                </div>
            `;
            return;
        }

        const notificationsHTML = this.notifications.slice(0, 10).map(notification => {
            const iconClass = this.getNotificationIcon(notification.type);
            const timeAgo = this.getTimeAgo(notification.created_at);

            return `
                <div class="notification-item ${notification.is_read ? 'read' : 'unread'}"
                     data-id="${notification.id}">
                    <div class="notification-icon">
                        <i class="fas ${iconClass}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${notification.title}</div>
                        <div class="notification-message">${notification.message}</div>
                        <div class="notification-time">${timeAgo}</div>
                    </div>
                    ${!notification.is_read ? '<div class="notification-unread-dot"></div>' : ''}
                </div>
            `;
        }).join('');

        list.innerHTML = notificationsHTML;
    }

    getNotificationIcon(type) {
        const icons = {
            'message': 'fa-envelope',
            'property_alert': 'fa-home',
            'favorite': 'fa-heart',
            'saved': 'fa-bookmark',
            'price_change': 'fa-dollar-sign',
            'system': 'fa-info-circle'
        };
        return icons[type] || 'fa-bell';
    }

    getTimeAgo(dateString) {
        const now = new Date();
        const date = new Date(dateString);
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) return 'Ahora';
        if (diffInSeconds < 3600) return `Hace ${Math.floor(diffInSeconds / 60)} min`;
        if (diffInSeconds < 86400) return `Hace ${Math.floor(diffInSeconds / 3600)} h`;
        if (diffInSeconds < 604800) return `Hace ${Math.floor(diffInSeconds / 86400)} días`;

        return date.toLocaleDateString('es-MX');
    }

    async markAsRead(notificationId) {
        try {
            const formData = new FormData();
            formData.append('notification_id', notificationId);

            const response = await fetch('mark_notification_read.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                // Update local state
                const notification = this.notifications.find(n => n.id == notificationId);
                if (notification) {
                    notification.is_read = true;
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                    this.updateUI();
                }
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            // Mark all unread notifications as read
            const unreadNotifications = this.notifications.filter(n => !n.is_read);

            for (const notification of unreadNotifications) {
                await this.markAsRead(notification.id);
            }

            showNotification('Todas las notificaciones marcadas como leídas', 'success');
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            showNotification('Error al marcar notificaciones como leídas', 'error');
        }
    }

    startPolling() {
        setInterval(() => {
            this.loadNotifications();
        }, this.checkInterval);
    }

    // Method to add notification programmatically
    addNotification(notification) {
        this.notifications.unshift(notification);
        if (!notification.is_read) {
            this.unreadCount++;
        }
        this.updateUI();

        // Show browser notification if permission granted
        this.showBrowserNotification(notification);
    }

    showBrowserNotification(notification) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(notification.title, {
                body: notification.message,
                icon: '/favicon.ico'
            });
        }
    }

    // Request notification permission
    static requestPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }
}

// Initialize notification system when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Request notification permission
    NotificationManager.requestPermission();

    // Initialize notification manager
    window.notificationManager = new NotificationManager();
});

// Global function to show notifications (extends existing showNotification)
function showSystemNotification(title, message, type = 'info') {
    if (window.notificationManager) {
        window.notificationManager.addNotification({
            id: Date.now(),
            type: type,
            title: title,
            message: message,
            is_read: false,
            created_at: new Date().toISOString()
        });
    }

    // Also show the existing notification system
    showNotification(message, type);
}