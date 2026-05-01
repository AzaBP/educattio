/**
 * Calendar Sync Manager
 * Sincronización en tiempo real entre múltiples instancias de calendario
 * Usa BroadcastChannel API con fallback a localStorage
 */

class CalendarSyncManager {
    constructor() {
        this.listeners = new Set();
        this.useBroadcastChannel = this.supportsBroadcastChannel();
        this.channel = null;
        
        if (this.useBroadcastChannel) {
            try {
                this.channel = new BroadcastChannel('educattio-calendar');
                this.channel.onmessage = (event) => this.handleMessage(event.data);
            } catch (e) {
                console.warn('BroadcastChannel no disponible, usando localStorage');
                this.useBroadcastChannel = false;
                this.setupStorageListener();
            }
        } else {
            this.setupStorageListener();
        }
    }

    supportsBroadcastChannel() {
        return typeof BroadcastChannel !== 'undefined';
    }

    setupStorageListener() {
        window.addEventListener('storage', (event) => {
            if (event.key === 'educattio-calendar-event') {
                try {
                    const data = JSON.parse(event.newValue);
                    this.handleMessage(data);
                } catch (e) {
                    console.error('Error parsing calendar event:', e);
                }
            }
        });
    }

    handleMessage(data) {
        if (data.type === 'event-created' || data.type === 'event-updated' || 
            data.type === 'event-deleted' || data.type === 'refresh-request') {
            this.notifyListeners(data);
        }
    }

    subscribe(callback) {
        this.listeners.add(callback);
        return () => this.listeners.delete(callback);
    }

    notifyListeners(data) {
        this.listeners.forEach(callback => {
            try {
                callback(data);
            } catch (e) {
                console.error('Error in calendar sync listener:', e);
            }
        });
    }

    broadcast(data) {
        if (this.useBroadcastChannel && this.channel) {
            this.channel.postMessage(data);
        } else {
            // Fallback a localStorage
            localStorage.setItem('educattio-calendar-event', JSON.stringify(data));
            // Limpiar después de un momento para permitir que otros lo lean
            setTimeout(() => localStorage.removeItem('educattio-calendar-event'), 100);
        }
        
        // También notificar localmente
        this.notifyListeners(data);
    }

    notifyEventCreated(event) {
        this.broadcast({
            type: 'event-created',
            timestamp: Date.now(),
            event: event
        });
    }

    notifyEventUpdated(eventId, event) {
        this.broadcast({
            type: 'event-updated',
            timestamp: Date.now(),
            eventId: eventId,
            event: event
        });
    }

    notifyEventDeleted(eventId) {
        this.broadcast({
            type: 'event-deleted',
            timestamp: Date.now(),
            eventId: eventId
        });
    }

    notifyRefresh() {
        this.broadcast({
            type: 'refresh-request',
            timestamp: Date.now()
        });
    }

    close() {
        if (this.channel) {
            this.channel.close();
        }
    }
}

// Instancia global singleton
window.calendarSync = window.calendarSync || new CalendarSyncManager();
