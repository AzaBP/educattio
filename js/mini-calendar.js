/**
 * Mini Calendar Component
 * Uso: new MiniCalendar(containerElement, options)
 */
class MiniCalendar {
    constructor(containerSelector, options = {}) {
        this.container = typeof containerSelector === 'string' 
            ? document.querySelector(containerSelector) 
            : containerSelector;
        
        this.options = {
            cursoId: null,
            claseId: null,
            onEventClick: null,
            onDateSelect: null,
            onEventCreate: null,
            ...options
        };

        this.currentDate = new Date();
        this.selectedDate = null;
        this.events = [];
        
        this.init();
    }

    init() {
        this.render();
        this.loadEvents();
        this.attachEventListeners();
        this.setupSyncListener();
    }

    setupSyncListener() {
        if (window.calendarSync) {
            window.calendarSync.subscribe((data) => {
                if (
                    data.type === 'event-created' ||
                    data.type === 'event-updated' ||
                    data.type === 'event-deleted' ||
                    data.type === 'refresh-request'
                ) {
                    // Recargar eventos cuando hay cambios
                    this.loadEvents();
                }
            });
        }
    }

    render() {
        const html = `
            <div class="mini-calendar">
                <div class="mini-calendar-header">
                    <h3>${this.getMonthYear()}</h3>
                    <div class="mini-calendar-nav">
                        <button class="mini-prev" title="Mes anterior">&lt;</button>
                        <button class="mini-today" title="Hoy">Hoy</button>
                        <button class="mini-next" title="Mes siguiente">&gt;</button>
                    </div>
                </div>
                
                <div class="mini-calendar-grid">
                    ${this.getDaysOfWeek()}
                    ${this.getDaysOfMonth()}
                </div>
                
                <div class="mini-calendar-events">
                    <h4>Eventos del día</h4>
                    <div class="mini-events-list">
                        <div class="mini-event-item no-events">Selecciona una fecha</div>
                    </div>
                    <button class="btn-save" style="width:100%; margin-top:1rem;" onclick="this.closest('.mini-calendar').miniCalendarObj.openEventModal()">
                        <i class="fas fa-plus"></i> Añadir evento
                    </button>
                </div>
            </div>
        `;
        
        this.container.innerHTML = html;
        this.container.miniCalendarObj = this;
        // Make the button work by binding the object to the inner div as well
        const innerDiv = this.container.querySelector('.mini-calendar');
        if (innerDiv) innerDiv.miniCalendarObj = this;
    }

    getDaysOfWeek() {
        const days = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        return days.map(day => `<div class="mini-calendar-weekday">${day}</div>`).join('');
    }

    getDaysOfMonth() {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDayOfWeek = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1;

        let html = '';

        // Días del mes anterior
        for (let i = startingDayOfWeek - 1; i >= 0; i--) {
            const prevDate = new Date(year, month, -i);
            html += `<div class="mini-calendar-day other-month">${prevDate.getDate()}</div>`;
        }

        // Días del mes actual
        const today = new Date();
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dateStr = this.formatDate(date);
            const isToday = this.isToday(date);
            const isSelected = this.selectedDate && this.selectedDate === dateStr;
            const hasEvents = this.eventsOnDate(dateStr).length > 0;

            html += `<div class="mini-calendar-day ${isToday ? 'today' : ''} ${isSelected ? 'selected' : ''} ${hasEvents ? 'has-events' : ''}" data-date="${dateStr}" ${hasEvents ? 'title="Tiene eventos"' : ''}>
                ${day}
            </div>`;
        }

        // Días del próximo mes
        const totalCells = startingDayOfWeek + daysInMonth;
        const remainingCells = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
        for (let day = 1; day <= remainingCells; day++) {
            html += `<div class="mini-calendar-day other-month">${day}</div>`;
        }

        return html;
    }

    formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    getMonthYear() {
        const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                       'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        return `${months[this.currentDate.getMonth()]} ${this.currentDate.getFullYear()}`;
    }

    isToday(date) {
        const today = new Date();
        return date.toDateString() === today.toDateString();
    }

    eventsOnDate(dateStr) {
        return this.events.filter(e => e.fecha.split('T')[0] === dateStr);
    }

    loadEvents() {
        let url = '../php/calendar_widget.php?action=events';
        if (this.options.cursoId) url += `&curso_id=${this.options.cursoId}`;
        if (this.options.claseId) url += `&clase_id=${this.options.claseId}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    this.events = data.events || [];
                    this.updateEventsList();
                }
            })
            .catch(e => console.error('Error cargando eventos:', e));
    }

    updateEventsList() {
        if (!this.selectedDate) return;
        
        const dayEvents = this.eventsOnDate(this.selectedDate);
        const listEl = this.container.querySelector('.mini-events-list');
        
        if (dayEvents.length === 0) {
            listEl.innerHTML = '<div class="mini-event-item no-events">Sin eventos</div>';
        } else {
            listEl.innerHTML = dayEvents.map(e => `
                <div class="mini-event-item">
                    <div>
                        <div class="mini-event-title">${e.titulo}</div>
                        <small style="color:#6b7280;">${new Date(e.fecha).toLocaleTimeString('es-ES', {hour:'2-digit', minute:'2-digit'})}</small>
                    </div>
                    <span class="mini-event-type ${e.tipo_evento.toLowerCase()}">${e.tipo_evento}</span>
                </div>
            `).join('');
        }
    }

    attachEventListeners() {
        // Navegación
        this.container.querySelector('.mini-prev').addEventListener('click', () => {
            this.currentDate.setMonth(this.currentDate.getMonth() - 1);
            this.render();
            this.attachEventListeners();
            this.loadEvents();
        });

        this.container.querySelector('.mini-next').addEventListener('click', () => {
            this.currentDate.setMonth(this.currentDate.getMonth() + 1);
            this.render();
            this.attachEventListeners();
            this.loadEvents();
        });

        this.container.querySelector('.mini-today').addEventListener('click', () => {
            this.currentDate = new Date();
            this.render();
            this.attachEventListeners();
            this.loadEvents();
        });

        // Seleccionar día
        this.container.querySelectorAll('.mini-calendar-day:not(.other-month)').forEach(dayEl => {
            dayEl.addEventListener('click', () => {
                const date = dayEl.dataset.date;
                this.selectedDate = date;
                this.container.querySelectorAll('.mini-calendar-day').forEach(d => d.classList.remove('selected'));
                dayEl.classList.add('selected');
                this.updateEventsList();
                if (this.options.onDateSelect) {
                    this.options.onDateSelect(date);
                }
            });
        });
    }

    openEventModal() {
        if (!this.selectedDate) {
            alert('Selecciona una fecha primero');
            return;
        }
        
        // Buscar o crear modal en la página
        let modal = document.getElementById('miniEventModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'miniEventModal';
            modal.className = 'modal-overlay';
            document.body.appendChild(modal);
        }

        modal.innerHTML = `
            <div class="modal-window">
                <div class="modal-header">
                    <h3>Nuevo Evento - ${this.selectedDate}</h3>
                    <button class="close-btn" onclick="document.getElementById('miniEventModal').classList.remove('active')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="miniEventForm">
                    <div class="form-group">
                        <label>Título</label>
                        <input type="text" class="form-control" name="titulo" required>
                    </div>
                    <div class="form-group">
                        <label>Tipo</label>
                        <select class="form-control" name="tipo">
                            <option value="Examen">Examen</option>
                            <option value="Festivo">Festivo</option>
                            <option value="Excursión">Excursión</option>
                            <option value="Reunión">Reunión</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" onclick="document.getElementById('miniEventModal').classList.remove('active')">Cancelar</button>
                        <button type="submit" class="btn-save">Guardar</button>
                    </div>
                </form>
            </div>
        `;

        modal.classList.add('active');
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.remove('active');
        });

        document.getElementById('miniEventForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(document.getElementById('miniEventForm'));
            const payload = {
                titulo: formData.get('titulo'),
                fecha: this.selectedDate + 'T12:00:00',
                tipo: formData.get('tipo'),
                descripcion: formData.get('descripcion'),
                clase_id: this.options.claseId || null,
                curso_id: this.options.cursoId || null
            };

            fetch('../php/calendario_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    modal.classList.remove('active');
                    this.loadEvents();
                    this.updateEventsList();
                    
                    // Notificar a otros calendarios
                    if (window.calendarSync) {
                        window.calendarSync.notifyEventCreated({
                            ...payload,
                            id: data.id
                        });
                    }
                    
                    // Actualizar calendario principal si existe
                    if (window.appCalendar) window.appCalendar.refetchEvents();
                    if (window.detallesCursoCalendar) window.detallesCursoCalendar.refetchEvents();
                    
                    if (this.options.onEventCreate) {
                        this.options.onEventCreate(data);
                    }
                } else {
                    alert(data.message || 'Error al guardar el evento');
                }
            })
            .catch(() => {
                alert('Error de red al guardar el evento');
            });
        });
    }
}

// Exponer el componente en global para poder inicializarlo desde otras vistas
window.MiniCalendar = MiniCalendar;
