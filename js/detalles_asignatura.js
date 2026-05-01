document.addEventListener('DOMContentLoaded', () => {
    const miniContainer = document.getElementById('miniCalendarAsignaturaContainer');
    if (!miniContainer || !window.MiniCalendar) {
        return;
    }

    const claseId = miniContainer.dataset.claseId || null;
    const asignaturaId = miniContainer.dataset.asignaturaId || null;

    window.miniCalendarAsignatura = new MiniCalendar(miniContainer, {
        claseId: claseId,
        onEventCreate: () => {
            refreshSubjectEvents(asignaturaId);
        }
    });

    if (window.calendarSync) {
        window.calendarSync.subscribe((data) => {
            if (
                data.type === 'event-created' ||
                data.type === 'event-updated' ||
                data.type === 'event-deleted' ||
                data.type === 'refresh-request'
            ) {
                refreshSubjectEvents(asignaturaId);
            }
        });
    }
});

function abrirMiniEventoAsignatura() {
    if (!window.miniCalendarAsignatura) return;
    if (!window.miniCalendarAsignatura.selectedDate) {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        window.miniCalendarAsignatura.selectedDate = `${year}-${month}-${day}`;
    }
    window.miniCalendarAsignatura.updateEventsList();
    window.miniCalendarAsignatura.openEventModal();
}

function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (!section) return;
    section.classList.toggle('hidden');
}

async function cargarDetallesAsignatura(asignaturaId) {
    try {
        const response = await fetch(`controllers/get_detalles_asignatura.php?id=${asignaturaId}`);
        const data = await response.json();

        if (data.status !== 'success') {
            document.querySelector('.main-content').innerHTML = `<div class="p-4 text-danger">${data.message || 'Error al cargar los datos.'}</div>`;
            return;
        }

        document.getElementById('asignaturaNombre').textContent = data.asignatura.nombre_asignatura;
        document.getElementById('asignaturaMeta').textContent = `${data.curso.nombre_centro} · ${data.clase.nombre_clase}`;
        document.getElementById('backToClassLink').href = `detalles_clase.php?id=${data.clase.id}`;
        document.getElementById('summaryAlumnos').textContent = data.cantidad_alumnos;
        document.getElementById('summaryPeriodos').textContent = data.periodos.length;
        document.getElementById('summaryEventos').textContent = data.eventos.length;

        renderEventos(data.eventos);
        renderTemas(data.temas);
        renderPeriodos(data.periodos);
    } catch (error) {
        console.error(error);
        document.querySelector('.main-content').innerHTML = '<div class="p-4 text-danger">Error al conectarse con el servidor.</div>';
    }
}

function renderEventos(eventos) {
    const container = document.getElementById('eventosList');
    if (!container) return;
    container.innerHTML = '';

    if (!eventos || eventos.length === 0) {
        const empty = document.createElement('div');
        empty.className = 'empty-state';
        empty.textContent = 'No hay eventos programados para esta asignatura.';
        container.appendChild(empty);
        return;
    }

    eventos.forEach(evento => {
        const card = document.createElement('div');
        card.className = 'event-card';
        card.innerHTML = `
            <div class="event-card-header">
                <strong>${escapeHtml(evento.titulo)}</strong>
                <span class="badge bg-secondary">${escapeHtml(evento.tipo_evento)}</span>
            </div>
            <div class="event-card-body">
                <p>${escapeHtml(evento.descripcion || 'Sin descripción')}</p>
                <div class="event-meta">
                    <span><i class="fas fa-calendar-day"></i> ${escapeHtml(evento.fecha_formateada)}</span>
                    <span><i class="fas fa-school"></i> ${escapeHtml(evento.nombre_clase)}</span>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

function renderTemas(temas) {
    const container = document.getElementById('temasContainer');
    const empty = document.getElementById('temasEmpty');
    container.innerHTML = '';

    if (!temas || temas.length === 0) {
        empty.classList.remove('hidden');
        return;
    }
    empty.classList.add('hidden');

    temas.forEach(tema => {
        const card = document.createElement('div');
        card.className = 'tema-card';
        card.innerHTML = `
            <div class="tema-title">
                <strong>${escapeHtml(tema.titulo)}</strong>
            </div>
            <p>${escapeHtml(tema.descripcion || 'Sin descripción')}</p>
        `;
        container.appendChild(card);
    });
}

function renderPeriodos(periodos) {
    const container = document.getElementById('periodosList');
    const empty = document.getElementById('periodosEmpty');
    container.innerHTML = '';

    if (!periodos || periodos.length === 0) {
        empty.classList.remove('hidden');
        return;
    }
    empty.classList.add('hidden');

    periodos.forEach(periodo => {
        const item = document.createElement('div');
        item.className = 'periodo-item';
        item.textContent = periodo.nombre_periodo;
        container.appendChild(item);
    });
}

async function guardarTema(asignaturaId) {
    const titulo = document.getElementById('temaTitulo').value.trim();
    const descripcion = document.getElementById('temaDescripcion').value.trim();
    if (!titulo) return;

    try {
        const response = await fetch('controllers/guardar_tema_asignatura.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ asignatura_id: asignaturaId, titulo, descripcion })
        });
        const data = await response.json();
        if (data.status === 'success') {
            document.getElementById('temaTitulo').value = '';
            document.getElementById('temaDescripcion').value = '';
            toggleSection('nuevoTemaSection');
            cargarDetallesAsignatura(asignaturaId);
        } else {
            alert(data.message || 'No se pudo guardar el tema.');
        }
    } catch (error) {
        console.error(error);
        alert('Error al guardar el tema.');
    }
}

async function guardarPeriodos(asignaturaId) {
    const nombres = document.getElementById('periodosNombres').value.trim();
    if (!nombres) return;

    const nombresArray = nombres.split(',').map(item => item.trim()).filter(Boolean);
    if (nombresArray.length === 0) return;

    try {
        const response = await fetch('controllers/crear_periodos.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ asignatura_id: asignaturaId, nombres_periodos: nombresArray })
        });
        const data = await response.json();
        if (data.status === 'success') {
            document.getElementById('periodosNombres').value = '';
            toggleSection('nuevoPeriodoSection');
            cargarDetallesAsignatura(asignaturaId);
        } else {
            alert(data.message || 'No se pudo guardar los periodos.');
        }
    } catch (error) {
        console.error(error);
        alert('Error al guardar los periodos.');
    }
}

async function refreshSubjectEvents(asignaturaId) {
    if (!asignaturaId) return;

    try {
        const response = await fetch(`controllers/get_detalles_asignatura.php?id=${encodeURIComponent(asignaturaId)}`);
        const data = await response.json();
        if (data.status !== 'success') return;

        const summaryEventos = document.getElementById('summaryEventos');
        if (summaryEventos && Array.isArray(data.eventos)) {
            summaryEventos.textContent = data.eventos.length;
        }

        if (Array.isArray(data.eventos)) {
            renderEventos(data.eventos);
        }
    } catch (error) {
        console.error('Error refrescando eventos de asignatura:', error);
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString().replace(/[&<>"]+/g, function(match) {
        const escape = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' };
        return escape[match];
    });
}
