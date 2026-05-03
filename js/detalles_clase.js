// --- ALUMNOS ---
const iconosDisponibles = [
    'alumna_01.png', 'alumna_02.png', 'alumna_03.png', 'alumna_04.png', 'alumna_05.png', 'alumna_06.png',
    'alumno_01.png', 'alumno_02.png', 'alumno_03.png', 'alumno_04.png'
];

function parseDatosPersonales(datos) {
    if (!datos) return { telefono: '', contacto: '', alergias: '', enfermedades: '' };
    try {
        return typeof datos === 'string' ? JSON.parse(datos) : datos;
    } catch (e) {
        return { telefono: '', contacto: '', alergias: '', enfermedades: '' };
    }
}

function abrirModalNuevoAlumno() {
    document.getElementById('formAlumno').reset();
    document.getElementById('fotoNuevoAlumno').value = '';
    renderizarIconosNuevo();
    showModal('modalAlumno');
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

async function abrirEditarAlumno(id) {
    const alumno = window.ULTIMOS_ALUMNOS?.find(a => a.id == id);
    if (!alumno) {
        alert("No se encontró la información del alumno.");
        return;
    }

    const datos = parseDatosPersonales(alumno.datos_personales);
    document.getElementById('editAlumnoId').value = alumno.id;
    document.getElementById('editNombreAlumno').value = alumno.nombre_alumno;
    document.getElementById('editTelefonoAlumno').value = datos.telefono || '';
    document.getElementById('editContactoAlumno').value = datos.contacto || '';
    document.getElementById('editAlergiasAlumno').value = datos.alergias || '';
    document.getElementById('editFotoAlumno').value = alumno.foto || '';
    document.getElementById('editObsAlumno').value = alumno.observaciones || '';
    
    renderizarIconosEditar();
    desactivarModoEdicion(); // Empezamos en modo vista
    showModal('modalEditarAlumno');
}

function activarModoEdicion() {
    document.getElementById('tituloFichaAlumno').innerText = "Editar Alumno";
    document.getElementById('btnActivarEdicion').style.display = 'none';
    document.getElementById('footerAccionesEdicion').style.display = 'flex';
    document.getElementById('footerAccionesVista').style.display = 'none';
    document.getElementById('btnEliminarAlumno').style.display = 'block';
    document.getElementById('contenedorIconosEditar').style.display = 'block';
    
    // Habilitar inputs
    const inputs = ['editNombreAlumno', 'editTelefonoAlumno', 'editContactoAlumno', 'editAlergiasAlumno', 'editObsAlumno'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.readOnly = false;
    });
}

function desactivarModoEdicion() {
    document.getElementById('tituloFichaAlumno').innerText = "Ficha del Alumno";
    document.getElementById('btnActivarEdicion').style.display = 'block';
    document.getElementById('footerAccionesEdicion').style.display = 'none';
    document.getElementById('footerAccionesVista').style.display = 'flex';
    document.getElementById('btnEliminarAlumno').style.display = 'none';
    document.getElementById('contenedorIconosEditar').style.display = 'none';
    
    // Deshabilitar inputs
    const inputs = ['editNombreAlumno', 'editTelefonoAlumno', 'editContactoAlumno', 'editAlergiasAlumno', 'editObsAlumno'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.readOnly = true;
    });
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

async function guardarAlumno(e) {
    if (e) e.preventDefault();
    const btn = e.submitter || document.querySelector('#modalAlumno button[type="submit"]');
    if (btn) btn.disabled = true;

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
            body: JSON.stringify({ 
                nombre_alumno: nombre, 
                observaciones: obs, 
                foto: foto, 
                clase_id: CLASE_ACTUAL_ID, 
                telefono, 
                contacto, 
                alergias 
            })
        });
        const res = await response.json();
        if (res.status === 'success') {
            hideModal('modalAlumno');
            document.getElementById('formAlumno').reset();
            cargarDatosClase();
        } else { alert("Error: " + res.message); }
    } catch (error) { 
        console.error(error); 
    } finally {
        if (btn) btn.disabled = false;
    }
}

async function guardarEdicionAlumno(e) {
    if (e) e.preventDefault();
    const btn = e.submitter || document.querySelector('#modalEditarAlumno button[type="submit"]');
    if (btn) btn.disabled = true;

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
            body: JSON.stringify({ 
                id: id, 
                nombre_alumno: nombre, 
                observaciones: obs, 
                foto: foto, 
                clase_id: CLASE_ACTUAL_ID, 
                telefono, 
                contacto, 
                alergias 
            })
        });
        const res = await response.json();
        if (res.status === 'success') {
            hideModal('modalEditarAlumno');
            cargarDatosClase();
        } else { alert("Error: " + res.message); }
    } catch (error) { 
        console.error(error); 
    } finally {
        if (btn) btn.disabled = false;
    }
}

async function eliminarAlumnoModal() {
    const id = document.getElementById('editAlumnoId').value;
    if (!id) return;
    await ejecutarEliminacionAlumno(id, () => hideModal('modalEditarAlumno'));
}

async function eliminarAlumnoFila(id) {
    await ejecutarEliminacionAlumno(id);
}

async function ejecutarEliminacionAlumno(id, callback = null) {
    if (!confirm('¿Seguro que quieres eliminar este alumno? Se borrarán sus datos, notas y matriculaciones de forma permanente.')) return;
    
    try {
        const res = await fetch('controllers/eliminar_alumno.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        const data = await res.json();
        if (data.status === 'success') {
            if (callback) callback();
            cargarDatosClase();
        } else { 
            alert('Error al eliminar: ' + (data.message || 'Error desconocido')); 
        }
    } catch (e) { 
        console.error(e);
        alert('Error de conexión al intentar eliminar el alumno.');
    }
}

// --- TABLA Y RENDERIZADO ---

function renderizarAlumnos(alumnos) {
    const cuerpo = document.getElementById('cuerpo-tabla-alumnos');
    cuerpo.innerHTML = '';
    
    if (!alumnos || alumnos.length === 0) {
        cuerpo.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No hay alumnos matriculados en esta clase.</td></tr>';
        return;
    }

    alumnos.forEach((alum, index) => {
        const datos = parseDatosPersonales(alum.datos_personales);
        const contactoHtml = (datos.contacto || datos.telefono) ? `
            <div style="display:flex;flex-direction:column;gap:4px;">
                ${datos.contacto ? `<span class="fw-bold">${escapeHtml(datos.contacto)}</span>` : ''}
                ${datos.telefono ? `<span class="text-muted" style="font-size:0.92rem;">${escapeHtml(datos.telefono)}</span>` : ''}
            </div>` : '<span class="text-muted">Sin datos</span>';
            
        const saludHtml = (datos.alergias) ? `
            <div style="display:flex;flex-direction:column;gap:4px;">
                <span>${escapeHtml(datos.alergias)}</span>
            </div>` : '<span class="text-muted">Ninguna información</span>';

        const tr = document.createElement('tr');
        tr.innerHTML = `
                <td>${index + 1}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:38px;height:38px;border-radius:50%;background:#f0f0f0;display:flex;align-items:center;justify-content:center;overflow:hidden;border:1px solid #e2e8f0;">
                            ${alum.foto ? `<img src="../icons/${alum.foto}" alt="${alum.nombre_alumno}" style="width:100%;height:100%;object-fit:cover;">` : `<span style='font-weight:bold;color:#888;'>${alum.nombre_alumno.substring(0, 1).toUpperCase()}</span>`}
                        </div>
                        <div>
                            <div class="fw-bold">${escapeHtml(alum.nombre_alumno)}</div>
                            <div class="text-muted" style="font-size:0.85rem;">${alum.observaciones ? escapeHtml(alum.observaciones) : 'Alumno de la clase'}</div>
                        </div>
                    </div>
                </td>
                <td>${contactoHtml}</td>
                <td>${saludHtml}</td>
                <td class="text-end">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-secondary" title="Exportar Informe" onclick="exportarAlumnoPDF(event, ${alum.id})">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary" title="Gestionar Asignaturas" onclick="abrirMatriculaAlum(${alum.id}, '${alum.nombre_alumno.replace(/'/g, "\\'")}')">
                            <i class="fas fa-book"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" title="Ver Ficha" onclick="abrirEditarAlumno(${alum.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" title="Eliminar Alumno" onclick="eliminarAlumnoFila(${alum.id})">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>`;
        cuerpo.appendChild(tr);
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString().replace(/[&<>"]+/g, function (match) {
        const escape = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' };
        return escape[match];
    });
}

// --- PDF EXPORT ---

function exportarAlumnosPDF() {
    if (!window.ULTIMOS_ALUMNOS || window.ULTIMOS_ALUMNOS.length === 0) {
        alert("No hay alumnos para exportar.");
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const fecha = new Date().toLocaleDateString();

    // Estética Premium (Simlar a incidencias)
    doc.setFillColor(15, 23, 42); 
    doc.rect(0, 0, 210, 40, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(22);
    doc.setFont("helvetica", "bold");
    doc.text('EDUCATTIO', 105, 20, { align: 'center' });
    doc.setFontSize(14);
    doc.text(`LISTADO DE ALUMNOS - ${document.querySelector('.course-title-animate').innerText}`, 105, 32, { align: 'center' });

    const head = [['#', 'NOMBRE DEL ALUMNO', 'CONTACTO', 'ALERGIAS / SALUD', 'OBSERVACIONES']];
    const body = window.ULTIMOS_ALUMNOS.map((alum, i) => {
        const datos = parseDatosPersonales(alum.datos_personales);
        return [
            i + 1,
            alum.nombre_alumno,
            `${datos.contacto || ''}\n${datos.telefono || ''}`,
            datos.alergias || 'Ninguna',
            alum.observaciones || '---'
        ];
    });

    doc.autoTable({
        startY: 50,
        head: head,
        body: body,
        theme: 'striped',
        headStyles: { fillColor: [30, 41, 59], fontSize: 10 },
        styles: { fontSize: 9, cellPadding: 4 }
    });

    doc.save(`lista_alumnos_${document.querySelector('.course-title-animate').innerText.replace(/\s+/g, '_')}.pdf`);
}

async function exportarAlumnoPDF(event, id) {
    const alum = window.ULTIMOS_ALUMNOS?.find(a => a.id == id);
    if (!alum) return;
    
    // Notificar al usuario que se está generando
    const btn = event.currentTarget;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;

    try {
        const response = await fetch(`controllers/obtener_notas_alumno.php?alumno_id=${id}`);
        const data = await response.json();

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        const datos = parseDatosPersonales(alum.datos_personales);
        const fecha = new Date().toLocaleDateString();

        // Encabezado Educattio
        doc.setFillColor(30, 41, 59); 
        doc.rect(0, 0, 210, 45, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(22);
        doc.setFont("helvetica", "bold");
        doc.text('EDUCATTIO', 105, 20, { align: 'center' });
        doc.setFontSize(14);
        doc.text('INFORME DE EVALUACIÓN DEL ALUMNO', 105, 32, { align: 'center' });

        // Datos del Alumno
        doc.setTextColor(30, 41, 59);
        doc.setFontSize(16);
        doc.text(alum.nombre_alumno.toUpperCase(), 15, 60);
        
        doc.setFontSize(10);
        doc.setTextColor(100);
        doc.text(`Clase: ${document.querySelector('.course-title-animate').innerText}`, 15, 67);
        doc.text(`Fecha del informe: ${fecha}`, 15, 72);

        // Tabla de Información Personal
        const infoPersonal = [
            ['Teléfono', datos.telefono || '---'],
            ['Contacto', datos.contacto || '---'],
            ['Salud', datos.alergias || 'Sin observaciones']
        ];
        doc.autoTable({
            startY: 80,
            body: infoPersonal,
            theme: 'plain',
            styles: { fontSize: 10, cellPadding: 2 },
            columnStyles: { 0: { fontStyle: 'bold', cellWidth: 30 } },
            margin: { left: 15 }
        });

        let currentY = doc.lastAutoTable.finalY + 15;

        // Notas por Asignatura
        if (data.status === 'success' && data.notas.length > 0) {
            doc.setFontSize(14);
            doc.setTextColor(30, 41, 59);
            doc.text('RESUMEN ACADÉMICO', 15, currentY);
            currentY += 5;

            data.notas.forEach(asig => {
                // Título de la asignatura
                doc.setFontSize(11);
                doc.setFont("helvetica", "bold");
                doc.text(asig.asignatura, 15, currentY + 10);
                
                const tableBody = asig.items.map(item => [
                    item.nombre_periodo || '---',
                    item.titulo,
                    item.nota !== null ? parseFloat(item.nota).toFixed(2) : 'N/P'
                ]);

                doc.autoTable({
                    startY: currentY + 12,
                    head: [['Periodo', 'Prueba / Ítem', 'Nota']],
                    body: tableBody,
                    theme: 'striped',
                    headStyles: { fillColor: [71, 85, 105], fontSize: 9 },
                    styles: { fontSize: 8 },
                    margin: { left: 15, right: 15 },
                    didDrawPage: (data) => { currentY = data.cursor.y; }
                });
                
                currentY = doc.lastAutoTable.finalY + 10;
                
                // Si nos quedamos sin espacio, nueva página
                if (currentY > 260) {
                    doc.addPage();
                    currentY = 20;
                }
            });
        } else {
            doc.setFontSize(10);
            doc.text('No hay notas registradas para este alumno.', 15, currentY + 10);
        }

        doc.save(`Informe_${alum.nombre_alumno.replace(/\s+/g, '_')}.pdf`);

    } catch (e) {
        console.error(e);
        alert("Error al generar el PDF");
    } finally {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    }
}

// --- ASIGNATURAS Y CALENDARIO ---

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

        let htmlSugerencias = '';
        if (asig.periodos.length === 0 || (asig.periodos.length === 1 && asig.periodos[0].nombre_periodo === 'Final')) {
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
            <div class="premium-card-wrapper" style="position: relative; height: 100%;">
                <div class="card-options-container">
                    <button class="menu-dots-btn" onclick="toggleMenu(event, 'asig-${asig.id}')">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div id="dropdown-asig-${asig.id}" class="dropdown-options-menu">
                        <a href="javascript:void(0)" onclick="editarAsignatura(${asig.id})"><i class="fas fa-edit"></i> Modificar</a>
                        <a href="javascript:void(0)" onclick="eliminarAsignatura(${asig.id})" class="delete-option"><i class="fas fa-trash"></i> Eliminar</a>
                    </div>
                </div>
                <div class="premium-card h-100" style="--accent-color: ${asig.color_asignatura || '#4facfe'}; cursor: pointer;">
                    <div class="card-banner" style="background: ${asig.color_asignatura || '#4facfe'} !important;">
                        <div class="card-icon"><i class="fas ${asig.icono_asignatura || 'fa-book'}"></i></div>
                        <div class="card-badge">Asignatura</div>
                    </div>
                    <div class="card-content">
                        <h3 class="m-0">${asig.nombre_asignatura}</h3>
                        <p class="text-muted small">Ver temas y evaluaciones</p>
                        <div class="d-flex flex-wrap mb-3">${htmlPeriodos}</div>
                        ${htmlSugerencias}
                        <div class="card-footer mt-auto">
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" style="color: var(--accent-color); font-weight:600;" 
                                    onclick="event.stopPropagation(); abrirMatriculaAsig(${asig.id}, '${asig.nombre_asignatura.replace(/'/g, "\\'")}')">
                                <i class="fas fa-users-cog"></i> Alumnos
                            </button>
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                </div>
            </div>`;

        card.querySelector('.premium-card').addEventListener('click', (e) => {
            if (e.target.closest('button') || e.target.closest('.btn-group')) return;
            window.location.href = `detalles_asignatura.php?id=${asig.id}`;
        });
        contenedor.appendChild(card);
    });
}

// --- OTROS ---

// --- ASIGNATURAS: ICONOS Y COLORES ---
const iconosAsig = ['fa-book', 'fa-calculator', 'fa-flask', 'fa-palette', 'fa-music', 'fa-running', 'fa-globe', 'fa-laptop-code', 'fa-microscope', 'fa-brain'];
const coloresAsig = ['#4facfe', '#43e97b', '#fa709a', '#f6d365', '#667eea', '#f093fb', '#5ee7df', '#fccb90'];

function renderizarPickersAsig(prefijo, colorSel, iconoSel) {
    const colorCont = document.getElementById(`${prefijo}-color-asig-container`);
    const iconoCont = document.getElementById(`${prefijo}-icono-asig-container`);
    
    if (colorCont) {
        colorCont.innerHTML = coloresAsig.map(c => `
            <div class="color-circle ${c === colorSel ? 'selected' : ''}" 
                 style="background-color: ${c}" 
                 onclick="seleccionarColorAsig('${prefijo}', '${c}')"></div>
        `).join('');
    }
    
    if (iconoCont) {
        iconoCont.innerHTML = iconosAsig.map(i => `
            <div class="icon-btn ${i === iconoSel ? 'selected' : ''}" 
                 onclick="seleccionarIconoAsig('${prefijo}', '${i}')">
                <i class="fas ${i}"></i>
            </div>
        `).join('');
    }
}

window.seleccionarColorAsig = (prefijo, color) => {
    const hidden = document.getElementById(prefijo === 'nuevo' ? 'nuevoColorAsig' : 'editColorAsig');
    if (hidden) hidden.value = color;
    renderizarPickersAsig(prefijo, color, document.getElementById(prefijo === 'nuevo' ? 'nuevoIconoAsig' : 'editIconoAsig').value);
};

window.seleccionarIconoAsig = (prefijo, icono) => {
    const hidden = document.getElementById(prefijo === 'nuevo' ? 'nuevoIconoAsig' : 'editIconoAsig');
    if (hidden) hidden.value = icono;
    renderizarPickersAsig(prefijo, document.getElementById(prefijo === 'nuevo' ? 'nuevoColorAsig' : 'editColorAsig').value, icono);
};

function abrirModalNuevaAsignatura() {
    document.getElementById('formNuevaAsignatura').reset();
    document.getElementById('nuevoColorAsig').value = coloresAsig[0];
    document.getElementById('nuevoIconoAsig').value = iconosAsig[0];
    renderizarPickersAsig('nuevo', coloresAsig[0], iconosAsig[0]);
    showModal('modalNuevaAsignatura');
}

async function editarAsignatura(id) {
    try {
        const res = await fetch(`controllers/get_detalles_asignatura.php?id=${id}`);
        const data = await res.json();
        if (data.status === 'success') {
            const asig = data.asignatura;
            document.getElementById('editAsigId').value = asig.id;
            document.getElementById('editNombreAsignatura').value = asig.nombre_asignatura;
            document.getElementById('editColorAsig').value = asig.color_asignatura || coloresAsig[0];
            document.getElementById('editIconoAsig').value = asig.icono_asignatura || iconosAsig[0];
            
            renderizarPickersAsig('edit', asig.color_asignatura || coloresAsig[0], asig.icono_asignatura || iconosAsig[0]);
            showModal('modalEditarAsignatura');
        }
    } catch (e) { console.error(e); }
}

async function eliminarAsignatura(id) {
    if (!confirm('¿Seguro que quieres eliminar esta asignatura? Se borrarán todos los temas y notas.')) return;
    try {
        const res = await fetch('controllers/eliminar_asignatura.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        const data = await res.json();
        if (data.status === 'success') cargarDatosClase();
        else alert('Error: ' + data.message);
    } catch (e) { console.error(e); }
}

async function eliminarAsignaturaModal() {
    const id = document.getElementById('editAsigId').value;
    if (!id || !confirm('¿Seguro que quieres eliminar esta asignatura?')) return;
    try {
        const res = await fetch('controllers/eliminar_asignatura.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        const data = await res.json();
        if (data.status === 'success') {
            hideModal('modalEditarAsignatura');
            cargarDatosClase();
        } else { alert('Error: ' + data.message); }
    } catch (e) { console.error(e); }
}

async function guardarAsignatura(e, isEdit) {
    if (e) e.preventDefault();
    const id = isEdit ? document.getElementById('editAsigId').value : null;
    const nombre = isEdit ? document.getElementById('editNombreAsignatura').value : document.getElementById('nuevoNombreAsignatura').value;
    const color = isEdit ? document.getElementById('editColorAsig').value : document.getElementById('nuevoColorAsig').value;
    const icono = isEdit ? document.getElementById('editIconoAsig').value : document.getElementById('nuevoIconoAsig').value;
    
    const url = isEdit ? 'controllers/editar_asignatura.php' : 'controllers/crear_asignatura.php';
    const payload = { 
        nombre_asignatura: nombre, 
        clase_id: CLASE_ACTUAL_ID,
        color_asignatura: color,
        icono_asignatura: icono
    };
    if (isEdit) payload.id = id;

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const res = await response.json();
        if (res.status === 'success') {
            hideModal(isEdit ? 'modalEditarAsignatura' : 'modalNuevaAsignatura');
            cargarDatosClase();
        } else { alert("Error: " + res.message); }
    } catch (error) { console.error(error); }
}

async function enviarPeriodos(asignaturaId, arrayNombres) {
    try {
        const response = await fetch('controllers/crear_periodos.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombres_periodos: arrayNombres, asignatura_id: asignaturaId })
        });
        const res = await response.json();
        if (res.status === 'success') cargarDatosClase();
        else alert("Error: " + res.message);
    } catch (error) { console.error(error); }
}

function crearPeriodoPersonalizado(asignaturaId) {
    const nombre = prompt("Introduce el nombre del nuevo periodo:");
    if (nombre && nombre.trim() !== '') enviarPeriodos(asignaturaId, [nombre.trim()]);
}

async function eliminarPeriodo(idPeriodo, nombrePeriodo) {
    if (!confirm(`¿Eliminar el periodo "${nombrePeriodo}"?`)) return;
    try {
        const response = await fetch('controllers/eliminar_periodo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: idPeriodo })
        });
        const res = await response.json();
        if (res.status === 'success') cargarDatosClase();
        else alert("Error: " + res.message);
    } catch (error) { console.error(error); }
}

let gestionActual = { tipo: '', id: null };
async function abrirMatriculaAsig(asigId, nombre) {
    gestionActual = { tipo: 'asig', id: asigId };
    document.getElementById('tituloModalGestion').innerText = nombre;
    document.getElementById('subtituloModal').innerText = "Selecciona qué alumnos cursan esta asignatura:";
    document.getElementById('lista-checks-gestion').innerHTML = '<div class="p-3 text-center">Cargando...</div>';
    showModal('modalGestion');
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
    document.getElementById('lista-checks-gestion').innerHTML = '<div class="p-3 text-center">Cargando...</div>';
    showModal('modalGestion');
    const res = await fetch('controllers/gestion_matricula.php', {
        method: 'POST',
        body: JSON.stringify({ accion: 'get_asig_por_alumno', alumno_id: alumId, clase_id: CLASE_ACTUAL_ID })
    });
    const data = await res.json();
    renderizarChecksMatricula(data, 'id', 'nombre_asignatura');
}

function renderizarChecksMatricula(items, idKey, textKey) {
    document.getElementById('lista-checks-gestion').innerHTML = items.map(item => `
        <label class="list-group-item d-flex justify-content-between align-items-center cursor-pointer">
            ${item[textKey]}
            <input class="form-check-input me-1" type="checkbox" value="${item[idKey]}" ${item.matriculado > 0 ? 'checked' : ''}>
        </label>
    `).join('');
}

document.getElementById('btnGuardarGestion').onclick = async () => {
    const ids = Array.from(document.querySelectorAll('#lista-checks-gestion input:checked')).map(c => c.value);
    const body = { accion: 'guardar_matricula', lista_ids: ids };
    if (gestionActual.tipo === 'asig') body.asig_id = gestionActual.id;
    else body.alumno_id = gestionActual.id;
    const res = await fetch('controllers/gestion_matricula.php', { method: 'POST', body: JSON.stringify(body) });
    const data = await res.json();
    if (data.status === 'success') {
        hideModal('modalGestion');
        cargarDatosClase();
    }
};

function showModal(id) {
    const el = document.getElementById(id);
    if(el) { (bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el)).show(); }
}
function hideModal(id) {
    const el = document.getElementById(id);
    if(el) { const m = bootstrap.Modal.getInstance(el); if(m) m.hide(); }
}

window.abrirMiniEventoClase = function () {
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

document.addEventListener('DOMContentLoaded', () => {
    cargarDatosClase();

    // Inicializar mini-calendario para la clase
    if (window.MiniCalendar && typeof CLASE_ACTUAL_ID !== 'undefined') {
        window.miniCalendarClase = new MiniCalendar('#miniCalendarClaseContainer', {
            claseId: CLASE_ACTUAL_ID,
            onEventCreate: () => {
                console.log('Evento creado en la clase');
            }
        });
    }

    // Vincular formularios para evitar envíos duplicados (se eliminó onsubmit del HTML)
    const formA = document.getElementById('formAlumno');
    if(formA) formA.addEventListener('submit', guardarAlumno);
    
    const formE = document.getElementById('formEditarAlumno');
    if(formE) formE.addEventListener('submit', guardarEdicionAlumno);

    const formNA = document.getElementById('formNuevaAsignatura');
    if(formNA) formNA.addEventListener('submit', (e) => guardarAsignatura(e, false));

    const formEA = document.getElementById('formEditarAsignatura');
    if(formEA) formEA.addEventListener('submit', (e) => guardarAsignatura(e, true));
});