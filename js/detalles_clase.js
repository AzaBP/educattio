// --- EDICIÓN DE ALUMNO ---
function parseDatosPersonales(datos) {
    if (!datos) return { telefono: '', contacto: '', alergias: '', enfermedades: '' };
    try {
        return typeof datos === 'string' ? JSON.parse(datos) : datos;
    } catch (e) {
        return { telefono: '', contacto: '', alergias: '', enfermedades: '' };
    }
}

async function abrirEditarAlumno(id) {
    const alumno = window.ULTIMOS_ALUMNOS?.find(a => a.id == id);
    if (!alumno) return;
    const datos = parseDatosPersonales(alumno.datos_personales);
    document.getElementById('editAlumnoId').value = alumno.id;
    document.getElementById('editNombreAlumno').value = alumno.nombre_alumno;
    document.getElementById('editTelefonoAlumno').value = datos.telefono || '';
    document.getElementById('editContactoAlumno').value = datos.contacto || '';
    document.getElementById('editAlergiasAlumno').value = datos.alergias || '';
    document.getElementById('editFotoAlumno').value = alumno.foto || '';
    document.getElementById('editObsAlumno').value = alumno.observaciones || '';
    renderizarIconosEditar();
    document.getElementById('modalEditarAlumno').style.display = 'flex';
}

function cerrarModalEditarAlumno() {
    document.getElementById('modalEditarAlumno').style.display = 'none';
}

function renderizarIconosEditar() {
    const seleccionado = document.getElementById('editFotoAlumno').value;
    document.getElementById('lista-iconos-editar').innerHTML = iconosDisponibles.map(icono => `
        <img src="../icons/${icono}" 
             class="avatar-option ${icono === seleccionado ? 'selected' : ''}" 
             onclick="seleccionarIconoEditar('${icono}')" alt="Icono">
    `).join('');
}

function seleccionarIconoEditar(icono) {
    document.getElementById('editFotoAlumno').value = icono;
    renderizarIconosEditar();
}

async function guardarEdicionAlumno(e) {
    e.preventDefault();
    const id = document.getElementById('editAlumnoId').value;
    const nombre = document.getElementById('editNombreAlumno').value;
    const obs = document.getElementById('editObsAlumno').value;
    const foto = document.getElementById('editFotoAlumno').value;
    const telefono = document.getElementById('editTelefonoAlumno').value;
    const contacto = document.getElementById('editContactoAlumno').value;
    const alergias = document.getElementById('editAlergiasAlumno').value;
    try {
        const response = await fetch('controllers/editar_alumno.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, nombre_alumno: nombre, observaciones: obs, foto, telefono, contacto, alergias })
        });
        const res = await response.json();
        if (res.status === 'success') {
            cerrarModalEditarAlumno();
            cargarDatosClase();
        } else { alert("Error: " + res.message); }
    } catch (error) { console.error(error); }
}
document.addEventListener('DOMContentLoaded', () => {
    cargarDatosClase();
    
    // Inicializar mini-calendario para la clase
    if (window.MiniCalendar && CLASE_ACTUAL_ID) {
        window.miniCalendarClase = new MiniCalendar('#miniCalendarClaseContainer', {
            claseId: CLASE_ACTUAL_ID,
            onEventCreate: () => {
                // Recargar si es necesario
                console.log('Evento creado en la clase');
            }
        });
    }
});

window.abrirMiniEventoClase = function() {
    if (!window.miniCalendarClase) return;
    if (!window.miniCalendarClase.selectedDate) {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        window.miniCalendarClase.selectedDate = `${year}-${month}-${day}`;
    }
    window.miniCalendarClase.updateEventsList();
    window.miniCalendarClase.openEventModal();
}

async function cargarDatosClase() {
    try {
        const response = await fetch(`controllers/obtener_detalles_clase.php?id=${CLASE_ACTUAL_ID}`);
        const data = await response.json();

        if (data.status === 'success') {
            window.ULTIMOS_ALUMNOS = data.alumnos;
            renderizarAsignaturas(data.asignaturas);
            renderizarAlumnos(data.alumnos);
        }
    } catch (error) {
        console.error("Error al cargar datos:", error);
    }
}

function renderizarAsignaturas(asignaturas) {
    const contenedor = document.getElementById('contenedor-asignaturas');
    const botonAdd = contenedor.querySelector('.col-md-4').outerHTML;
    contenedor.innerHTML = botonAdd;

    asignaturas.forEach(asig => {
        let htmlPeriodos = '';
        asig.periodos.forEach(p => {
            htmlPeriodos += `
                <div class="btn-group m-1 shadow-sm" role="group">
                    <a href="cuaderno_evaluacion.php?asig_id=${asig.id}&periodo_id=${p.id}" 
                       class="btn btn-outline-primary btn-sm fw-bold border-end-0">
                       ${p.nombre_periodo}
                    </a>
                    <button type="button" class="btn btn-outline-danger btn-sm" 
                            onclick="event.stopPropagation(); eliminarPeriodo(${p.id}, '${p.nombre_periodo}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>`;
        });

        let puedeSugerir = asig.periodos.length === 0 || (asig.periodos.length === 1 && asig.periodos[0].nombre_periodo === 'Final');
        let htmlSugerencias = '';
        if (puedeSugerir) {
            htmlSugerencias = `
                <div class="mt-3 text-end d-flex justify-content-end align-items-center flex-wrap gap-2" style="font-size: 0.85rem;">
                    <span class="text-muted me-1"><i class="fas fa-magic"></i> Añadir:</span>
                    <button class="btn btn-sm btn-link text-decoration-none text-muted p-0 hover-primary" onclick="event.stopPropagation(); enviarPeriodos(${asig.id}, ['1ª Evaluación', '2ª Evaluación', '3ª Evaluación'])">3 Trimestres</button>
                    <span class="text-muted" style="opacity: 0.5;">|</span>
                    <button class="btn btn-sm btn-link text-decoration-none text-muted p-0 hover-primary" onclick="event.stopPropagation(); enviarPeriodos(${asig.id}, ['1er Cuatrimestre', '2º Cuatrimestre'])">2 Cuatrimestres</button>
                    <span class="text-muted" style="opacity: 0.5;">|</span>
                    <button class="btn btn-sm btn-link text-decoration-none text-primary p-0 fw-bold" onclick="event.stopPropagation(); crearPeriodoPersonalizado(${asig.id})">Personalizado <i class="fas fa-plus"></i></button>
                </div>`;
        } else {
            htmlSugerencias = `
                <div class="mt-3 text-end">
                    <button class="btn btn-sm btn-link text-decoration-none text-muted p-0" style="font-size: 0.85rem;" onclick="event.stopPropagation(); crearPeriodoPersonalizado(${asig.id})">
                        <i class="fas fa-plus"></i> Añadir otro periodo
                    </button>
                </div>`;
        }

        const card = document.createElement('div');
        card.className = 'col-md-4 mb-4';
        card.innerHTML = `
            <div class="card card-clickable h-100 shadow-sm border-0" style="border-radius: 18px; cursor: pointer;">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="card-title fw-bold m-0" style="font-family: 'Georgia', serif; color: #1f2937;">${asig.nombre_asignatura}</h5>
                            <p class="text-muted mb-0" style="font-size: 0.95rem;">Ver temas y evaluaciones</p>
                        </div>
                        <span class="badge bg-primary">Asignatura</span>
                    </div>

                    <div class="d-flex flex-wrap mb-3">
                        ${htmlPeriodos}
                    </div>

                    <div class="mt-auto">
                        ${htmlSugerencias}
                        <hr class="my-2 opacity-25">
                        <div class="text-start">
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-secondary" onclick="event.stopPropagation(); abrirMatriculaAsig(${asig.id}, '${asig.nombre_asignatura.replace(/'/g, "\\'")}')">
                                <i class="fas fa-users-cog"></i> Gestionar Alumnos
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;

        card.querySelector('.card-clickable').addEventListener('click', () => {
            window.location.href = `detalles_asignatura.php?id=${asig.id}`;
        });
        contenedor.appendChild(card);
    });
}

function renderizarAlumnos(alumnos) {
    const cuerpo = document.getElementById('cuerpo-tabla-alumnos');
    cuerpo.innerHTML = '';
    alumnos.forEach((alum, index) => {
        const datos = parseDatosPersonales(alum.datos_personales);
        const contactoHtml = datos.contacto || datos.telefono ? `
            <div style="display:flex;flex-direction:column;gap:4px;">
                ${datos.contacto ? `<span class="fw-bold">${escapeHtml(datos.contacto)}</span>` : ''}
                ${datos.telefono ? `<span class="text-muted" style="font-size:0.92rem;">${escapeHtml(datos.telefono)}</span>` : ''}
            </div>` : '<span class="text-muted">Sin datos</span>';
        const saludHtml = datos.alergias || datos.enfermedades ? `
            <div style="display:flex;flex-direction:column;gap:4px;">
                ${datos.alergias ? `<span><strong>Alergias:</strong> ${escapeHtml(datos.alergias)}</span>` : ''}
                ${datos.enfermedades ? `<span><strong>Enf.:</strong> ${escapeHtml(datos.enfermedades)}</span>` : ''}
            </div>` : '<span class="text-muted">Ninguna información</span>';

        const tr = document.createElement('tr');
        tr.innerHTML = `
                <td>${index + 1}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:38px;height:38px;border-radius:50%;background:#f0f0f0;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                            ${alum.foto ? `<img src="../icons/${alum.foto}" alt="${alum.nombre_alumno}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">` : `<span style='font-weight:bold;color:#888;'>${alum.nombre_alumno.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase()}</span>`}
                        </div>
                        <div>
                            <div class="fw-bold">${escapeHtml(alum.nombre_alumno)}</div>
                            <div class="text-muted" style="font-size:0.9rem;">${alum.observaciones ? escapeHtml(alum.observaciones) : 'Sin observaciones'}</div>
                        </div>
                    </div>
                </td>
                <td>${contactoHtml}</td>
                <td>${saludHtml}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary" onclick="abrirMatriculaAlum(${alum.id}, '${alum.nombre_alumno.replace(/'/g, "\\'")}')">
                        <i class="fas fa-book"></i> Asignaturas
                    </button>
                    <button class="btn btn-sm btn-outline-secondary ms-1" onclick="abrirEditarAlumno(${alum.id})">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                </td>`;
        cuerpo.appendChild(tr);
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString().replace(/[&<>"]+/g, function(match) {
        const escape = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' };
        return escape[match];
    });
}

// --- FUNCIONES DE GUARDADO ---

async function guardarAsignatura(e) {
    e.preventDefault();
    const nombre = document.getElementById('nombreAsignatura').value;
    try {
        const response = await fetch('controllers/crear_asignatura.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre_asignatura: nombre, clase_id: CLASE_ACTUAL_ID })
        });
        const res = await response.json();
        if (res.status === 'success') {
            cerrarModalAsignatura();
            document.getElementById('formAsignatura').reset();
            cargarDatosClase();
        } else { alert("Error: " + res.message); }
    } catch (error) { console.error(error); }
}

async function guardarAlumno(e) {
    e.preventDefault();
    const nombre = document.getElementById('nombreAlumno').value;
    const obs = document.getElementById('obsAlumno').value;
    const foto = document.getElementById('fotoNuevoAlumno').value;
    const telefono = document.getElementById('telefonoAlumno').value;
    const contacto = document.getElementById('contactoAlumno').value;
    const alergias = document.getElementById('alergiasAlumno').value;
    try {
        const response = await fetch('controllers/crear_alumno.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre_alumno: nombre, observaciones: obs, foto: foto, clase_id: CLASE_ACTUAL_ID, telefono, contacto, alergias })
        });
        const res = await response.json();
        if (res.status === 'success') {
            cerrarModalAlumno();
            document.getElementById('formAlumno').reset();
            cargarDatosClase();
        } else { alert("Error: " + res.message); }
    } catch (error) { console.error(error); }
}

// --- FUNCIONES DE PERIODOS ---

async function enviarPeriodos(asignaturaId, arrayNombres) {
    try {
        const response = await fetch('controllers/crear_periodos.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombres_periodos: arrayNombres, asignatura_id: asignaturaId })
        });
        const res = await response.json();
        if (res.status === 'success') {
            cargarDatosClase();
        } else { alert("Error: " + res.message); }
    } catch (error) { console.error(error); }
}

function crearPeriodoPersonalizado(asignaturaId) {
    const nombre = prompt("Introduce el nombre del nuevo periodo (ej: Tema 1, Examen Final...):");
    if (nombre && nombre.trim() !== '') {
        enviarPeriodos(asignaturaId, [nombre.trim()]);
    }
}

// Función para eliminar un periodo
async function eliminarPeriodo(idPeriodo, nombrePeriodo) {
    if (!confirm(`¿Estás seguro de que quieres eliminar el periodo "${nombrePeriodo}"? ADVERTENCIA: Se borrarán todas las pruebas y notas asociadas a este periodo.`)) {
        return;
    }

    try {
        const response = await fetch('controllers/eliminar_periodo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: idPeriodo })
        });
        const res = await response.json();
        
        if (res.status === 'success') {
            cargarDatosClase(); // Recargamos las tarjetas
        } else {
            alert("Error al eliminar: " + res.message);
        }
    } catch (error) {
        console.error("Error:", error);
    }
}

// --- FUNCIONES DE MATRICULACIÓN SELECTIVA (OPCIÓN C) ---
let gestionActual = { tipo: '', id: null };

async function abrirMatriculaAsig(asigId, nombre) {
    gestionActual = { tipo: 'asig', id: asigId };
    document.getElementById('tituloModalGestion').innerText = nombre;
    document.getElementById('subtituloModal').innerText = "Selecciona qué alumnos cursan esta asignatura:";
    mostrarCargandoMatricula();
    
    const res = await fetch('controllers/gestion_matricula.php', {
        method: 'POST',
        body: JSON.stringify({ accion: 'get_alumnos_por_asig', asig_id: asigId, clase_id: CLASE_ACTUAL_ID })
    });
    const data = await res.json();
    renderizarChecksMatricula(data, 'id', 'nombre_alumno');
}

async function abrirMatriculaAlum(alumId, nombre) {
    gestionActual = { tipo: 'alum', id: alumId };
    document.getElementById('tituloModalGestion').innerText = nombre;
    document.getElementById('subtituloModal').innerText = "Selecciona en qué asignaturas está matriculado:";
    mostrarCargandoMatricula();

    const res = await fetch('controllers/gestion_matricula.php', {
        method: 'POST',
        body: JSON.stringify({ accion: 'get_asig_por_alumno', alumno_id: alumId, clase_id: CLASE_ACTUAL_ID })
    });
    const data = await res.json();
    renderizarChecksMatricula(data, 'id', 'nombre_asignatura');
}

function renderizarChecksMatricula(items, idKey, textKey) {
    const contenedor = document.getElementById('lista-checks-gestion');
    contenedor.innerHTML = items.map(item => `
        <label class="list-group-item d-flex justify-content-between align-items-center cursor-pointer">
            ${item[textKey]}
            <input class="form-check-input me-1" type="checkbox" value="${item[idKey]}" ${item.matriculado > 0 ? 'checked' : ''}>
        </label>
    `).join('');
    document.getElementById('modalGestion').style.display = 'flex';
}

document.getElementById('btnGuardarGestion').onclick = async () => {
    const checks = document.querySelectorAll('#lista-checks-gestion input:checked');
    const ids = Array.from(checks).map(c => c.value);
    
    const body = { accion: 'guardar_matricula', lista_ids: ids };
    if (gestionActual.tipo === 'asig') body.asig_id = gestionActual.id;
    else body.alumno_id = gestionActual.id;

    const res = await fetch('controllers/gestion_matricula.php', { method: 'POST', body: JSON.stringify(body) });
    const data = await res.json();
    if (data.status === 'success') {
        cerrarModalGestion();
        alert("Cambios guardados correctamente");
    }
};

function cerrarModalGestion() { document.getElementById('modalGestion').style.display = 'none'; }
function mostrarCargandoMatricula() { document.getElementById('lista-checks-gestion').innerHTML = '<div class="p-3 text-center">Cargando...</div>'; }

// Función para eliminar un periodo
async function eliminarPeriodo(id, nombre) {
    if (!confirm(`¿Borrar "${nombre}"? Perderás todas las notas de este periodo.`)) return;
    const res = await fetch('controllers/eliminar_periodo.php', { method: 'POST', body: JSON.stringify({ id: id }) });
    const data = await res.json();
    if (data.status === 'success') cargarDatosClase();
}

// Control Modales
function abrirModalNuevaAsignatura() { document.getElementById('modalAsignatura').style.display = 'flex'; }
function cerrarModalAsignatura() { document.getElementById('modalAsignatura').style.display = 'none'; }
const iconosDisponibles = [
    'alumna_01.png', 'alumna_02.png', 'alumna_03.png', 'alumna_04.png', 'alumna_05.png', 'alumna_06.png',
    'alumno_01.png', 'alumno_02.png', 'alumno_03.png', 'alumno_04.png'
];

function abrirModalNuevoAlumno() {
    document.getElementById('modalAlumno').style.display = 'flex';
    document.getElementById('fotoNuevoAlumno').value = '';
    renderizarIconosNuevo();
}

function renderizarIconosNuevo() {
    const seleccionado = document.getElementById('fotoNuevoAlumno').value;
    document.getElementById('lista-iconos-nuevo').innerHTML = iconosDisponibles.map(icono => `
        <img src="../icons/${icono}" 
             class="avatar-option ${icono === seleccionado ? 'selected' : ''}" 
             onclick="seleccionarIconoNuevo('${icono}')" alt="Icono">
    `).join('');
}

function seleccionarIconoNuevo(icono) {
    document.getElementById('fotoNuevoAlumno').value = icono;
    renderizarIconosNuevo();
}
function cerrarModalAlumno() { document.getElementById('modalAlumno').style.display = 'none'; }