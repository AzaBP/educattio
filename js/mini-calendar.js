/**
 * MiniCalendar - Componente de mini-calendario reutilizable
 *
 * Opciones:
 *   cursoId      → filtra eventos por curso
 *   claseId      → filtra eventos por clase
 *   asignaturaId → filtra eventos por asignatura (resuelve clase internamente)
 *   onEventCreate, onEventClick, onDateSelect → callbacks
 */
class MiniCalendar {
    constructor(containerSelector, options = {}) {
        this.container = typeof containerSelector === 'string'
            ? document.querySelector(containerSelector)
            : containerSelector;

        this.options = {
            cursoId: null,
            claseId: null,
            asignaturaId: null,
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

        // Suscribirse a cambios globales si existe calendarSync
        if (window.calendarSync) {
            window.calendarSync.subscribe((data) => {
                if (['event-created', 'event-updated', 'event-deleted', 'refresh-request'].includes(data.type)) {
                    this.loadEvents();
                }
            });
        }

        // Seleccionar hoy por defecto para mostrar eventos próximos
        const today = new Date();
        this.selectedDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
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
                    <button class="btn-save" style="width:100%; margin-top:1rem;" id="mini-add-event-btn">
                        <i class="fas fa-plus"></i> Añadir evento
                    </button>
                </div>
            </div>
        `;

        this.container.innerHTML = html;
        this.container.miniCalendarObj = this;
        const innerDiv = this.container.querySelector('.mini-calendar');
        if (innerDiv) innerDiv.miniCalendarObj = this;

        // Bind the add button after render
        const addBtn = this.container.querySelector('#mini-add-event-btn');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.openEventModal());
        }
        
        this.attachEventListeners();
        this.updateEventsList();
    }

    renderGrid() {
        const grid = this.container.querySelector('.mini-calendar-grid');
        if (grid) {
            grid.innerHTML = this.getDaysOfWeek() + this.getDaysOfMonth();
            this._attachDayClicks();
        }
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

        // Días del mes anterior (relleno)
        for (let i = startingDayOfWeek - 1; i >= 0; i--) {
            const prevDate = new Date(year, month, -i);
            html += `<div class="mini-calendar-day other-month">${prevDate.getDate()}</div>`;
        }

        // Días del mes actual
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

        // Días del próximo mes (relleno)
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
        return this.events.filter(e => {
            if (!e.fecha) return false;
            // Manejar formatos "YYYY-MM-DD HH:MM:SS" (SQL) o "YYYY-MM-DDTHH:MM:SS" (ISO)
            const datePart = e.fecha.includes(' ') ? e.fecha.split(' ')[0] : e.fecha.split('T')[0];
            return datePart === dateStr;
        });
    }

    /**
     * Construye la URL del endpoint unificado según los filtros disponibles.
     */
    buildEventsUrl() {
        // Detectar si estamos en /php/ o en la raíz para usar rutas relativas correctas
        const base = window.location.pathname.includes('/php/') ? 'api_eventos.php' : 'php/api_eventos.php';
        const params = new URLSearchParams();

        if (this.options.asignaturaId) {
            params.set('asignatura_id', this.options.asignaturaId);
        } else if (this.options.claseId) {
            params.set('clase_id', this.options.claseId);
        } else if (this.options.cursoId) {
            params.set('curso_id', this.options.cursoId);
        }
        // Sin parámetros → todos los eventos del usuario

        return `${base}?${params.toString()}`;
    }

    loadEvents() {
        const url = this.buildEventsUrl();

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    this.events = data.events || [];
                    this.renderGrid();
                    this.updateEventsList();
                }
            })
            .catch(e => console.error('MiniCalendar: Error cargando eventos:', e));
    }

    updateEventsList() {
        if (!this.selectedDate) return;

        const dayEvents = this.eventsOnDate(this.selectedDate);
        const listEl = this.container.querySelector('.mini-events-list');
        if (!listEl) return;

        if (dayEvents.length === 0) {
            listEl.innerHTML = '<div class="mini-event-item no-events">Sin eventos para este día</div>';
        } else {
            listEl.innerHTML = dayEvents.map(e => {
                const hora = e.fecha && e.fecha.includes(' ')
                    ? e.fecha.split(' ')[1].substring(0, 5)
                    : '';
                
                // Mostrar de dónde viene el evento (Migas de pan)
                let breadcrumbParts = [];
                if (e.nombre_centro) breadcrumbParts.push(e.nombre_centro);
                if (e.nombre_clase) breadcrumbParts.push(e.nombre_clase);
                if (e.nombre_asignatura) breadcrumbParts.push(e.nombre_asignatura);
                
                let sourceHtml = '';
                if (breadcrumbParts.length > 0) {
                    const breadcrumbText = breadcrumbParts.join(' <i class="fas fa-chevron-right" style="font-size:0.6rem;opacity:0.5;margin:0 2px;"></i> ');
                    sourceHtml = `<span class="source-breadcrumb">${breadcrumbText}</span>`;
                }

                return `
                    <div class="mini-event-item selectable-event" data-id="${e.id}">
                        <div style="flex-grow: 1;">
                            <div class="mini-event-title">${this._esc(e.titulo)}</div>
                            <small style="color:#6b7280; display:block; margin-top:2px; font-size:0.75rem;">
                                ${hora ? '<i class="far fa-clock"></i> ' + hora + ' · ' : ''}
                                ${sourceHtml}
                            </small>
                        </div>
                    </div>
                `;
            }).join('');

            // Attach clicks to events
            listEl.querySelectorAll('.selectable-event').forEach(item => {
                item.addEventListener('click', () => {
                    const eventId = item.dataset.id;
                    const event = this.events.find(ev => ev.id == eventId);
                    if (event) this.openViewModal(event);
                });
            });
        }
    }

    _esc(text) {
        if (!text) return '';
        return text.toString().replace(/[&<>"]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
    }

    attachEventListeners() {
        this.container.querySelector('.mini-prev')?.addEventListener('click', () => {
            this.currentDate.setMonth(this.currentDate.getMonth() - 1);
            this.render();
            this.loadEvents();
        });

        this.container.querySelector('.mini-next')?.addEventListener('click', () => {
            this.currentDate.setMonth(this.currentDate.getMonth() + 1);
            this.render();
            this.loadEvents();
        });

        this.container.querySelector('.mini-today')?.addEventListener('click', () => {
            this.currentDate = new Date();
            this.render();
            this.loadEvents();
        });

        this._attachDayClicks();
    }

    _attachDayClicks() {
        this.container.querySelectorAll('.mini-calendar-day:not(.other-month)').forEach(dayEl => {
            dayEl.addEventListener('click', () => {
                const date = dayEl.dataset.date;
                if (!date) return;
                this.selectedDate = date;
                this.container.querySelectorAll('.mini-calendar-day').forEach(d => d.classList.remove('selected'));
                dayEl.classList.add('selected');
                this.updateEventsList();
                if (this.options.onDateSelect) this.options.onDateSelect(date);
            });
        });
    }

    openEventModal() {
        if (!this.selectedDate) {
            // Seleccionar hoy automáticamente si no hay fecha seleccionada
            const today = new Date();
            this.selectedDate = this.formatDate(today);
        }

        let modal = document.getElementById('miniEventModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'miniEventModal';
            modal.className = 'modal-overlay';
            document.body.appendChild(modal);
        }

        modal.innerHTML = `
            <div class="modal-window" style="max-width:480px;">
                <div class="modal-header">
                    <h3><i class="fas fa-calendar-plus" style="color:#3b82f6;margin-right:8px;"></i>Nuevo Evento · ${this.selectedDate}</h3>
                    <button class="close-btn" id="miniEventModalCloseBtn"><i class="fas fa-times"></i></button>
                </div>
                <form id="miniEventForm">
                    <div class="form-group">
                        <label>Título *</label>
                        <input type="text" class="form-control" name="titulo" placeholder="Ej: Examen Tema 3" required autofocus>
                    </div>
                    
                    <div id="miniMetadataSelectors">
                        <div class="form-group">
                            <label>Clase</label>
                            <select class="form-control" name="clase_id" id="miniEventClaseSelect">
                                <option value="">General (Personal)</option>
                            </select>
                        </div>
                        <div class="form-group" id="miniEventAsigContainer" style="display:none;">
                            <label>Asignatura</label>
                            <select class="form-control" name="asignatura_id" id="miniEventAsigSelect">
                                <option value="">Toda la clase</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tipo</label>
                        <select class="form-control" name="tipo">
                            <option value="Examen">📝 Examen</option>
                            <option value="Festivo">🎉 Festivo</option>
                            <option value="Excursión">🚌 Excursión</option>
                            <option value="Reunión">👥 Reunión</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="2" placeholder="Opcional..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" id="miniEventModalCancelBtn">Cancelar</button>
                        <button type="submit" class="btn-save">Guardar evento</button>
                    </div>
                </form>
            </div>
        `;

        modal.style.display = 'flex';

        const closeModal = () => { modal.style.display = 'none'; };
        modal.querySelector('#miniEventModalCloseBtn').onclick  = closeModal;
        modal.querySelector('#miniEventModalCancelBtn').onclick = closeModal;
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

        // Cargar metadatos si no estamos en un contexto fijo
        const metadataSelectors = modal.querySelector('#miniMetadataSelectors');
        if (this.options.claseId || this.options.asignaturaId) {
            metadataSelectors.style.display = 'none';
        } else {
            this._loadMetadata(modal);
        }

        modal.querySelector('#miniEventForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(modal.querySelector('#miniEventForm'));
            const selectedOpt = modal.querySelector('#miniEventClaseSelect')?.selectedOptions[0];
            const payload = {
                id:          formData.get('id') || null,
                titulo:      formData.get('titulo'),
                fecha:       this.selectedDate + 'T12:00:00',
                tipo:        formData.get('tipo'),
                descripcion: formData.get('descripcion'),
                clase_id:    this.options.claseId       || formData.get('clase_id') || null,
                curso_id:    this.options.cursoId       || (selectedOpt ? selectedOpt.dataset.cursoId : null),
                asignatura_id: this.options.asignaturaId || formData.get('asignatura_id') || null
            };

            const apiUrl = window.location.pathname.includes('/php/')
                ? 'calendario_api.php'
                : 'php/calendario_api.php';

            fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeModal();
                    this.loadEvents();

                    if (window.calendarSync) {
                        window.calendarSync.notifyEventCreated({ ...payload, id: data.id });
                    }
                    if (window.appCalendar) window.appCalendar.refetchEvents();
                    if (window.detallesCursoCalendar) window.detallesCursoCalendar.refetchEvents();

                    if (this.options.onEventCreate) this.options.onEventCreate(data);
                }
            })
            .catch(() => alert('Error de red al guardar el evento'));
        });
    }

    openViewModal(event) {
        let modal = document.getElementById('miniViewEventModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'miniViewEventModal';
            modal.className = 'modal-overlay';
            document.body.appendChild(modal);
        }

        const hora = event.fecha && event.fecha.includes(' ') ? event.fecha.split(' ')[1].substring(0, 5) : '';

        modal.innerHTML = `
            <div class="modal-window" style="max-width:480px;">
                <div class="modal-header">
                    <h3><i class="fas fa-info-circle" style="color:#3b82f6;margin-right:8px;"></i>Detalles del Evento</h3>
                    <button class="close-btn" id="miniViewModalCloseBtn"><i class="fas fa-times"></i></button>
                </div>
                <div class="event-view-content" style="padding: 10px 0;">
                    <div style="margin-bottom:15px;">
                        <label style="font-weight:700; color:#4b5563; font-size:0.8rem; text-transform:uppercase;">Título</label>
                        <div style="font-size:1.1rem; font-weight:600; color:#111827;">${this._esc(event.titulo)}</div>
                    </div>
                    <div style="display:flex; gap:20px; margin-bottom:15px;">
                        <div>
                            <label style="font-weight:700; color:#4b5563; font-size:0.8rem; text-transform:uppercase;">Fecha</label>
                            <div>${this._esc(this.formatDate(new Date(event.fecha)))}</div>
                        </div>
                        ${hora ? `<div>
                            <label style="font-weight:700; color:#4b5563; font-size:0.8rem; text-transform:uppercase;">Hora</label>
                            <div>${this._esc(hora)}</div>
                        </div>` : ''}
                    </div>
                    <div style="margin-bottom:15px;">
                        <label style="font-weight:700; color:#4b5563; font-size:0.8rem; text-transform:uppercase;">Origen</label>
                        <div style="font-size:0.9rem;">
                            ${event.nombre_centro ? `${this._esc(event.nombre_centro)}` : ''}
                            ${event.nombre_clase ? ` <i class="fas fa-chevron-right" style="font-size:0.65rem;opacity:0.5;"></i> ${this._esc(event.nombre_clase)}` : ''}
                            ${event.nombre_asignatura ? ` <i class="fas fa-chevron-right" style="font-size:0.65rem;opacity:0.5;"></i> ${this._esc(event.nombre_asignatura)}` : ''}
                        </div>
                    </div>
                    <div style="margin-bottom:15px;">
                        <label style="font-weight:700; color:#4b5563; font-size:0.8rem; text-transform:uppercase;">Tipo</label>
                        <div><span class="mini-event-type ${(event.tipo_evento || '').toLowerCase()}">${this._esc(event.tipo_evento || 'Reunión')}</span></div>
                    </div>
                    <div style="margin-bottom:15px;">
                        <label style="font-weight:700; color:#4b5563; font-size:0.8rem; text-transform:uppercase;">Descripción</label>
                        <div style="background:#f9fafc; padding:10px; border-radius:8px; font-size:0.9rem; color:#4b5563; min-height:40px; white-space: pre-wrap;">${this._esc(event.descripcion || 'Sin descripción')}</div>
                    </div>
                </div>
                <div class="modal-footer" style="margin-top:20px; gap:10px;">
                    <button class="btn-cancel btn-delete" id="miniViewModalDeleteBtn" style="flex:none; width:auto; padding: 0 15px; background: #fee2e2; border-color: #fecaca; color: #dc2626;">
                        <i class="fas fa-trash"></i>
                    </button>
                    <div style="flex-grow:1;"></div>
                    <button class="btn-cancel" id="miniViewModalEditBtn">Modificar</button>
                    <button class="btn-save" id="miniViewModalCloseBtnBottom">Cerrar</button>
                </div>
            </div>
        `;

        modal.style.display = 'flex';

        const closeModal = () => { modal.style.display = 'none'; };
        modal.querySelector('#miniViewModalCloseBtn').onclick = closeModal;
        modal.querySelector('#miniViewModalCloseBtnBottom').onclick = closeModal;
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

        // Botón Eliminar
        modal.querySelector('#miniViewModalDeleteBtn').onclick = () => {
            this.deleteEvent(event.id);
            closeModal();
        };

        // Botón Editar
        modal.querySelector('#miniViewModalEditBtn').onclick = () => {
            closeModal();
            this.openEditModal(event);
        };
    }

    openEditModal(event) {
        this.openEventModal();
        const modal = document.getElementById('miniEventModal');
        if (!modal) return;

        modal.querySelector('h3').innerHTML = `<i class="fas fa-edit" style="color:#3b82f6;margin-right:8px;"></i>Modificar Evento`;
        const form = modal.querySelector('#miniEventForm');
        form.querySelector('[name="titulo"]').value = event.titulo;
        form.querySelector('[name="tipo"]').value = event.tipo_evento;
        form.querySelector('[name="descripcion"]').value = event.descripcion || '';
        
        let idInput = form.querySelector('[name="id"]');
        if (!idInput) {
            idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            form.appendChild(idInput);
        }
        idInput.value = event.id;
    }

    deleteEvent(eventId) {
        if (!confirm('¿Estás seguro de que deseas eliminar este evento?')) return;

        const apiUrl = window.location.pathname.includes('/php/')
            ? 'calendario_api.php'
            : 'php/calendario_api.php';

        fetch(apiUrl, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: eventId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.loadEvents();
                if (window.calendarSync) {
                    window.calendarSync.notifyEventDeleted(eventId);
                }
                if (window.appCalendar) window.appCalendar.refetchEvents();
            } else {
                alert('Error al eliminar el evento');
            }
        })
        .catch(() => alert('Error de red al eliminar el evento'));
    }

    _loadMetadata(modal) {
        const claseSelect = modal.querySelector('#miniEventClaseSelect');
        const asigSelect = modal.querySelector('#miniEventAsigSelect');
        const asigContainer = modal.querySelector('#miniEventAsigContainer');
        const base = window.location.pathname.includes('/php/') ? 'obtener_metadatos_evento.php' : 'php/obtener_metadatos_evento.php';

        fetch(`${base}?type=all`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    data.clases.forEach(clase => {
                        const opt = document.createElement('option');
                        opt.value = clase.id;
                        opt.dataset.cursoId = clase.curso_id;
                        opt.textContent = `${clase.nombre_centro} - ${clase.nombre_clase}`;
                        claseSelect.appendChild(opt);
                    });

                    this._allAsignaturas = data.asignaturas;

                    claseSelect.onchange = () => {
                        const selectedClase = claseSelect.value;
                        asigSelect.innerHTML = '<option value="">Toda la clase</option>';
                        if (selectedClase) {
                            const filtered = this._allAsignaturas.filter(a => a.clase_id == selectedClase);
                            if (filtered.length > 0) {
                                filtered.forEach(a => {
                                    const opt = document.createElement('option');
                                    opt.value = a.id;
                                    opt.textContent = a.nombre_asignatura;
                                    asigSelect.appendChild(opt);
                                });
                                asigContainer.style.display = 'block';
                            } else {
                                asigContainer.style.display = 'none';
                            }
                        } else {
                            asigContainer.style.display = 'none';
                        }
                    };
                }
            });
    }
}

// Exponer globalmente
window.MiniCalendar = MiniCalendar;
