document.addEventListener('DOMContentLoaded', function() {
    let calendarEl = document.getElementById('calendar');
    let calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        editable: true,
        selectable: true,
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch(`../php/calendario_api.php?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
        },
        eventClick: function(info) {
            // Abrir modal para editar
            openModal(info.event);
        },
        select: function(info) {
            // Abrir modal para crear nuevo evento en esa fecha
            openModal(null, info.startStr, info.endStr);
        },
        eventDrop: function(info) {
            // Actualizar fecha al arrastrar
            updateEventDate(info.event.id, info.event.startStr);
        },
        eventResize: function(info) {
            updateEventDate(info.event.id, info.event.startStr);
        }
    });
    calendar.render();

    window.openModal = function(event, startDate, endDate) {
        const modal = document.getElementById('eventModal');
        const form = document.getElementById('eventForm');
        const modalTitle = document.getElementById('modalTitle');
        
        if (event) {
            // Editar
            modalTitle.innerText = 'Editar Evento';
            document.getElementById('eventId').value = event.id;
            document.getElementById('eventTitle').value = event.title;
            document.getElementById('eventDate').value = event.startStr.slice(0,16);
            document.getElementById('eventType').value = event.extendedProps.tipo || 'Examen';
            document.getElementById('eventDesc').value = event.extendedProps.description || '';
            if (event.extendedProps.clase_id) {
                document.getElementById('eventClass').value = event.extendedProps.clase_id;
            } else {
                document.getElementById('eventClass').value = '';
            }
        } else {
            // Nuevo
            modalTitle.innerText = 'Nuevo Evento';
            form.reset();
            document.getElementById('eventId').value = '';
            let localDate = startDate ? startDate.slice(0,16) : '';
            if (localDate) document.getElementById('eventDate').value = localDate;
        }
        modal.style.display = 'flex';
    };
    
    window.closeModal = function() {
        document.getElementById('eventModal').style.display = 'none';
    };
    
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('eventId').value;
        const titulo = document.getElementById('eventTitle').value;
        const fecha = document.getElementById('eventDate').value;
        const tipo = document.getElementById('eventType').value;
        const desc = document.getElementById('eventDesc').value;
        const clase_id = document.getElementById('eventClass').value || null;
        
        const payload = { id, titulo, fecha, tipo, descripcion: desc, clase_id };
        fetch('../php/calendario_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                calendar.refetchEvents();
                closeModal();
            } else {
                alert('Error al guardar el evento');
            }
        });
    });
    
    function updateEventDate(eventId, newStart) {
        fetch('../php/calendario_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: eventId, fecha: newStart })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) calendar.refetchEvents();
        });
    }
    
    // Eliminar evento (opcional, añadir botón en modal)
});