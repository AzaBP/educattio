document.addEventListener('DOMContentLoaded', function() {
    let calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;
    
    let calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        contentHeight: 'auto',
        editable: true,
        selectable: true,
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch(`../php/calendario_api.php?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
        },
        eventClick: function(info) {
            openModal(info.event);
        },
        select: function(info) {
            openModal(null, info.startStr);
        },
        eventDrop: function(info) {
            updateEventDate(info.event.id, info.event.start);
        },
        eventResize: function(info) {
            updateEventDate(info.event.id, info.event.start);
        }
    });
    calendar.render();
    window.appCalendar = calendar;

    // Configurar escucha de cambios en otros calendarios
    if (window.calendarSync) {
        window.calendarSync.subscribe((data) => {
            if (
                data.type === 'event-created' ||
                data.type === 'event-updated' ||
                data.type === 'event-deleted' ||
                data.type === 'refresh-request'
            ) {
                calendar.refetchEvents();
            }
        });
    }

    window.openModal = function(event, startDate) {
        const modal = document.getElementById('eventModal');
        const form = document.getElementById('eventForm');
        const modalTitle = document.getElementById('modalTitle');
        
        if (event) {
            modalTitle.innerText = 'Editar Evento';
            document.getElementById('eventId').value = event.id;
            document.getElementById('eventTitle').value = event.title;
            const startStr = event.start.toISOString().slice(0, 16);
            document.getElementById('eventDate').value = startStr;
            document.getElementById('eventType').value = event.extendedProps.tipo || 'Reunión';
            document.getElementById('eventDesc').value = event.extendedProps.description || '';
            if (event.extendedProps.clase_id) {
                document.getElementById('eventClass').value = event.extendedProps.clase_id;
            }
        } else {
            modalTitle.innerText = 'Nuevo Evento';
            form.reset();
            document.getElementById('eventId').value = '';
            if (startDate) {
                const local = new Date(startDate).toISOString().slice(0, 16);
                document.getElementById('eventDate').value = local;
            }
        }
        modal.style.display = 'flex';
    };
    
    window.closeModal = function() {
        document.getElementById('eventModal').style.display = 'none';
    };
    
    window.deleteEvent = function(eventId) {
        if (confirm('¿Eliminar este evento?')) {
            fetch('../php/calendario_api.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: eventId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.appCalendar.refetchEvents();
                    closeModal();
                    
                    // Notificar sincronización
                    if (window.calendarSync) {
                        window.calendarSync.notifyEventDeleted(eventId);
                    }
                }
            });
        }
    };
    
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('eventId').value;
        const titulo = document.getElementById('eventTitle').value;
        const fecha = document.getElementById('eventDate').value;
        const tipo = document.getElementById('eventType').value;
        const desc = document.getElementById('eventDesc').value;
        const clase_id = document.getElementById('eventClass').value || null;
        
        const payload = { id: id || undefined, titulo, fecha, tipo, descripcion: desc, clase_id };
        fetch('../php/calendario_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.appCalendar.refetchEvents();
                closeModal();
                
                // Notificar sincronización
                if (window.calendarSync) {
                    const eventId = document.getElementById('eventId').value;
                    if (eventId) {
                        window.calendarSync.notifyEventUpdated(eventId, payload);
                    } else {
                        window.calendarSync.notifyEventCreated({...payload, id: data.id});
                    }
                }
            } else {
                alert(data.message || 'Error al guardar el evento');
            }
        })
        .catch(() => {
            alert('Error de red al guardar el evento');
        });
    });
    
    function updateEventDate(eventId, newStart) {
        const dateStr = new Date(newStart).toISOString();
        fetch('../php/calendario_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: eventId, fecha: dateStr })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                window.appCalendar.refetchEvents();
                return;
            }
            if (window.calendarSync) {
                window.calendarSync.notifyEventUpdated(eventId, { fecha: dateStr });
            }
        });
    }
    
    // Cargar clases del usuario
    fetch('../php/controllers/get_clases.php')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.clases) {
                const select = document.getElementById('eventClass');
                data.clases.forEach(clase => {
                    const opt = document.createElement('option');
                    opt.value = clase.id;
                    opt.textContent = clase.nombre_clase + ' - ' + clase.materia_principal;
                    select.appendChild(opt);
                });
            }
        });
    
    // Cerrar modal al hacer clic fuera
    document.getElementById('eventModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
});