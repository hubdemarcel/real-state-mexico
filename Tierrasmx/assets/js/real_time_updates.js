// ===== REAL-TIME UPDATES SYSTEM =====

class RealTimeUpdates {
    constructor() {
        this.eventSource = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 5000; // 5 seconds
        this.lastUpdate = new Date().toISOString();
        this.init();
    }

    init() {
        // Temporarily disabled due to database connection limits
        console.log('Real-time updates disabled to prevent database connection limit issues');
        return;

        // Only initialize if user is logged in
        if (this.isUserLoggedIn()) {
            this.connect();
            this.bindEvents();
        }
    }

    isUserLoggedIn() {
        // Check if user session exists (you might need to adjust this based on your session handling)
        return document.querySelector('.user-menu') !== null ||
               document.querySelector('.notification-container') !== null;
    }

    connect() {
        if (this.eventSource) {
            this.eventSource.close();
        }

        try {
            this.eventSource = new EventSource(`real_time_updates.php?last_check=${this.lastUpdate}`);

            this.eventSource.onopen = (event) => {
                console.log('Real-time connection established');
                this.isConnected = true;
                this.reconnectAttempts = 0;
                this.showConnectionStatus('connected');
            };

            this.eventSource.onmessage = (event) => {
                const data = JSON.parse(event.data);
                this.handleMessage(data);
            };

            this.eventSource.onerror = (event) => {
                console.error('Real-time connection error:', event);
                this.isConnected = false;
                this.showConnectionStatus('disconnected');
                this.handleReconnect();
            };

            // Handle specific events
            this.eventSource.addEventListener('notification_update', (event) => {
                const data = JSON.parse(event.data);
                this.handleNotificationUpdate(data);
            });

            this.eventSource.addEventListener('message_update', (event) => {
                const data = JSON.parse(event.data);
                this.handleMessageUpdate(data);
            });

            this.eventSource.addEventListener('property_alert', (event) => {
                const data = JSON.parse(event.data);
                this.handlePropertyAlert(data);
            });

            this.eventSource.addEventListener('heartbeat', (event) => {
                const data = JSON.parse(event.data);
                this.lastUpdate = data.timestamp;
            });

        } catch (error) {
            console.error('Failed to create EventSource:', error);
            this.handleReconnect();
        }
    }

    handleMessage(data) {
        console.log('Real-time message received:', data);
    }

    handleNotificationUpdate(data) {
        console.log('New notifications:', data.count);

        // Update notification badge
        if (window.notificationManager) {
            window.notificationManager.loadNotifications();
        }

        // Show browser notification if permission granted
        if (data.count > 0) {
            this.showBrowserNotification(
                'Nuevas notificaciones',
                `Tienes ${data.count} notificación(es) nueva(s)`,
                'notification'
            );
        }
    }

    handleMessageUpdate(data) {
        console.log('New messages:', data.count);

        // Show browser notification
        if (data.count > 0) {
            this.showBrowserNotification(
                'Nuevos mensajes',
                `Tienes ${data.count} mensaje(s) nuevo(s)`,
                'message'
            );
        }

        // Update UI if on dashboard
        if (window.location.pathname.includes('dashboard')) {
            this.updateMessageCount(data.count);
        }
    }

    handlePropertyAlert(data) {
        console.log('New properties matching criteria:', data.count);

        // Show browser notification
        if (data.count > 0) {
            this.showBrowserNotification(
                'Nuevas propiedades',
                `Se encontraron ${data.count} propiedad(es) que coinciden con tus criterios`,
                'property'
            );
        }

        // Update property listings if on search page
        if (window.location.pathname.includes('comprar') ||
            window.location.pathname.includes('search')) {
            this.refreshPropertyListings();
        }
    }

    showBrowserNotification(title, body, type) {
        if ('Notification' in window && Notification.permission === 'granted') {
            const notification = new Notification(title, {
                body: body,
                icon: '/favicon.ico',
                tag: type // Prevents duplicate notifications
            });

            notification.onclick = function() {
                window.focus();
                this.close();
            };

            // Auto-close after 5 seconds
            setTimeout(() => {
                notification.close();
            }, 5000);
        }
    }

    showConnectionStatus(status) {
        // Update connection status indicator (optional)
        const statusIndicator = document.getElementById('connection-status');
        if (statusIndicator) {
            statusIndicator.className = `connection-status ${status}`;
            statusIndicator.textContent = status === 'connected' ? '🟢' : '🔴';
        }
    }

    handleReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Attempting to reconnect (${this.reconnectAttempts}/${this.maxReconnectAttempts})...`);

            setTimeout(() => {
                this.connect();
            }, this.reconnectDelay);
        } else {
            console.error('Max reconnection attempts reached');
            this.showConnectionStatus('failed');
        }
    }

    updateMessageCount(count) {
        const messageBadge = document.querySelector('.message-badge');
        if (messageBadge) {
            messageBadge.textContent = count;
            messageBadge.style.display = count > 0 ? 'block' : 'none';
        }
    }

    refreshPropertyListings() {
        // Trigger a refresh of property listings
        const refreshBtn = document.getElementById('refresh-properties');
        if (refreshBtn) {
            refreshBtn.click();
        } else {
            // Fallback: reload the page
            if (confirm('Se encontraron nuevas propiedades. ¿Quieres actualizar la página?')) {
                window.location.reload();
            }
        }
    }

    bindEvents() {
        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                // Page is hidden, reduce update frequency
                this.pauseUpdates();
            } else {
                // Page is visible, resume updates
                this.resumeUpdates();
            }
        });

        // Handle beforeunload
        window.addEventListener('beforeunload', () => {
            this.disconnect();
        });
    }

    pauseUpdates() {
        if (this.eventSource) {
            this.eventSource.close();
            this.isConnected = false;
        }
    }

    resumeUpdates() {
        if (!this.isConnected) {
            this.connect();
        }
    }

    disconnect() {
        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
            this.isConnected = false;
        }
    }

    // Method to manually trigger updates
    forceUpdate() {
        this.lastUpdate = new Date(Date.now() - 60000).toISOString(); // 1 minute ago
        if (this.eventSource) {
            this.eventSource.close();
            this.connect();
        }
    }
}

// Initialize real-time updates when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Request notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // Initialize real-time updates
    window.realTimeUpdates = new RealTimeUpdates();
});

// Export for global access
window.RealTimeUpdates = RealTimeUpdates;