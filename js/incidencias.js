document.addEventListener('DOMContentLoaded', () => {
    const cursoSelect = document.getElementById('cursoSelect');
    const claseSelect = document.getElementById('claseSelect');
    const alumnoSelect = document.getElementById('alumnoSelect');
    const form = document.getElementById('formIncidencia');
    const exportBtn = document.getElementById('btnExportarPDF');

    cursoSelect.addEventListener('change', async () => {
        if (!cursoSelect.value) {
            claseSelect.innerHTML = '<option value="">Selecciona primero un curso</option>';
            claseSelect.disabled = true;
            alumnoSelect.innerHTML = '<option value="">Selecciona primero una clase</option>';
            alumnoSelect.disabled = true;
            return;
        }
        await cargarClases(cursoSelect.value);
    });

    claseSelect.addEventListener('change', async () => {
        if (!claseSelect.value) {
            alumnoSelect.innerHTML = '<option value="">Selecciona primero una clase</option>';
            alumnoSelect.disabled = true;
            return;
        }
        await cargarAlumnos(claseSelect.value);
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        await guardarIncidencia();
    });

    exportBtn.addEventListener('click', () => {
        exportarIncidenciaPDF();
    });

    cargarIncidencias();
});

async function cargarClases(cursoId) {
    const claseSelect = document.getElementById('claseSelect');
    claseSelect.disabled = true;
    claseSelect.innerHTML = '<option value="">Cargando clases...</option>';

    try {
        const response = await fetch(`controllers/get_clases_por_curso.php?curso_id=${cursoId}`);
        const data = await response.json();
        if (data.status === 'success') {
            claseSelect.innerHTML = '<option value="">Selecciona una clase</option>';
            data.clases.forEach(clase => {
                claseSelect.innerHTML += `<option value="${clase.id}">${clase.nombre_clase}</option>`;
            });
            claseSelect.disabled = false;
            document.getElementById('alumnoSelect').innerHTML = '<option value="">Selecciona primero una clase</option>';
            document.getElementById('alumnoSelect').disabled = true;
        } else {
            claseSelect.innerHTML = `<option value="">${data.message || 'Error cargando clases'}</option>`;
        }
    } catch (error) {
        claseSelect.innerHTML = '<option value="">Error al cargar clases</option>';
        console.error(error);
    }
}

async function cargarAlumnos(claseId) {
    const alumnoSelect = document.getElementById('alumnoSelect');
    alumnoSelect.disabled = true;
    alumnoSelect.innerHTML = '<option value="">Cargando alumnos...</option>';

    try {
        const response = await fetch(`controllers/get_alumnos_por_clase.php?clase_id=${claseId}`);
        const data = await response.json();
        if (data.status === 'success') {
            alumnoSelect.innerHTML = '<option value="">Selecciona un alumno (opcional)</option>';
            alumnoSelect.innerHTML += '<option value="">Sin alumno específico</option>';
            data.alumnos.forEach(alumno => {
                alumnoSelect.innerHTML += `<option value="${alumno.id}">${alumno.nombre_alumno}</option>`;
            });
            alumnoSelect.disabled = false;
        } else {
            alumnoSelect.innerHTML = `<option value="">${data.message || 'Error cargando alumnos'}</option>`;
        }
    } catch (error) {
        alumnoSelect.innerHTML = '<option value="">Error al cargar alumnos</option>';
        console.error(error);
    }
}

async function guardarIncidencia() {
    const cursoId = document.getElementById('cursoSelect').value;
    const claseId = document.getElementById('claseSelect').value;
    const alumnoId = document.getElementById('alumnoSelect').value;
    const tipo = document.getElementById('tipoIncidencia').value;
    const descripcion = document.getElementById('descripcionIncidencia').value.trim();

    if (!cursoId || !claseId || !descripcion) {
        alert('Completa el curso, la clase y la descripción.');
        return;
    }

    try {
        const response = await fetch('controllers/guardar_incidencia.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ curso_id: cursoId, clase_id: claseId, alumno_id: alumnoId || null, tipo, descripcion })
        });
        const data = await response.json();
        if (data.status === 'success') {
            alert('Incidencia guardada correctamente.');
            document.getElementById('descripcionIncidencia').value = '';
            cargarIncidencias();
        } else {
            alert(data.message || 'No se pudo guardar la incidencia.');
        }
    } catch (error) {
        console.error(error);
        alert('Error al guardar la incidencia.');
    }
}

async function cargarIncidencias() {
    try {
        const response = await fetch('controllers/get_incidencias.php');
        const data = await response.json();
        const tbody = document.getElementById('incidenciasTableBody');
        if (data.status === 'success' && Array.isArray(data.incidencias)) {
            if (data.incidencias.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No hay incidencias registradas.</td></tr>';
                return;
            }
            tbody.innerHTML = data.incidencias.map(inc => `
                <tr>
                    <td>${escapeHtml(inc.fecha_incidencia)}</td>
                    <td>${escapeHtml(inc.nombre_centro)}</td>
                    <td>${escapeHtml(inc.nombre_clase)}</td>
                    <td>${escapeHtml(inc.nombre_alumno || '---')}</td>
                    <td>${escapeHtml(inc.tipo)}</td>
                    <td>${escapeHtml(inc.descripcion)}</td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${data.message || 'Error al cargar incidencias.'}</td></tr>`;
        }
    } catch (error) {
        const tbody = document.getElementById('incidenciasTableBody');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Error al cargar incidencias.</td></tr>';
        console.error(error);
    }
}

function exportarIncidenciaPDF() {
    const cursoSelect = document.getElementById('cursoSelect');
    const claseSelect = document.getElementById('claseSelect');
    const alumnoSelect = document.getElementById('alumnoSelect');
    const tipoSelect = document.getElementById('tipoIncidencia');
    const descripcion = document.getElementById('descripcionIncidencia').value.trim();

    if (!cursoSelect.value || !claseSelect.value || !descripcion) {
        alert('Para exportar, completa el curso, la clase y la descripción.');
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ unit: 'pt', format: 'a4' });
    const fecha = new Date().toLocaleString();
    const alumnoTexto = alumnoSelect.value ? alumnoSelect.options[alumnoSelect.selectedIndex].text : 'Sin alumno específico';

    doc.setFontSize(16);
    doc.text('Incidencia Educattio', 40, 50);
    doc.setFontSize(11);
    doc.text(`Fecha: ${fecha}`, 40, 80);
    doc.text(`Curso: ${cursoSelect.options[cursoSelect.selectedIndex].text}`, 40, 100);
    doc.text(`Clase: ${claseSelect.options[claseSelect.selectedIndex].text}`, 40, 120);
    doc.text(`Alumno: ${alumnoTexto}`, 40, 140);
    doc.text(`Tipo: ${tipoSelect.value}`, 40, 160);
    doc.text('Descripción:', 40, 190);

    const splitDescripcion = doc.splitTextToSize(descripcion, 500);
    doc.text(splitDescripcion, 40, 210);
    doc.save('incidencia_educattio.pdf');
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString().replace(/[&<>"']/g, function(match) {
        const escape = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        return escape[match];
    });
}
