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

    const exportListBtn = document.getElementById('btnExportarListaPDF');

    exportListBtn.addEventListener('click', () => {
        exportarListaIncidenciasPDF();
    });

    cargarIncidencias();
});

let incidenciasActuales = []; // Para guardar los datos cargados

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
            incidenciasActuales = data.incidencias; // Guardamos para exportar
            if (data.incidencias.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No hay incidencias registradas.</td></tr>';
                return;
            }
            tbody.innerHTML = data.incidencias.map(inc => `
                <tr>
                    <td><span class="badge bg-light text-dark">${escapeHtml(inc.fecha_incidencia)}</span></td>
                    <td>${escapeHtml(inc.nombre_centro)}</td>
                    <td><span class="fw-bold text-primary">${escapeHtml(inc.nombre_clase)}</span></td>
                    <td>${escapeHtml(inc.nombre_alumno || '---')}</td>
                    <td><span class="badge bg-info text-white">${escapeHtml(inc.tipo)}</span></td>
                    <td class="small">${escapeHtml(inc.descripcion)}</td>
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
        alert('Para exportar, completa el curso, la clase y la descripción de la incidencia actual.');
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    const centro = cursoSelect.options[cursoSelect.selectedIndex].text;
    const clase = claseSelect.options[claseSelect.selectedIndex].text;
    const alumno = alumnoSelect.value ? alumnoSelect.options[alumnoSelect.selectedIndex].text : 'Sin alumno específico';
    const fecha = new Date().toLocaleDateString();

    // DISEÑO DEL PDF
    doc.setFillColor(15, 23, 42); 
    doc.rect(0, 0, 210, 45, 'F');
    
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(22);
    doc.setFont("helvetica", "bold");
    doc.text('EDUCATTIO', 105, 20, { align: 'center' });
    doc.setFontSize(14);
    doc.text('REPORTE DE INCIDENCIA', 105, 32, { align: 'center' });

    doc.setTextColor(30, 41, 59);
    doc.setFontSize(11);
    doc.text(`Generado el: ${fecha}`, 15, 60);

    const dataRows = [
        ['CENTRO / CURSO', centro],
        ['CLASE / GRUPO', clase],
        ['ALUMNO', alumno],
        ['TIPO', tipoSelect.value]
    ];

    doc.autoTable({
        startY: 65,
        body: dataRows,
        theme: 'plain',
        styles: { fontSize: 10, cellPadding: 6 },
        columnStyles: { 0: { fontStyle: 'bold', cellWidth: 50 } }
    });

    const currentY = doc.lastAutoTable.finalY + 15;
    doc.setFillColor(248, 250, 252);
    doc.rect(15, currentY, 180, 10, 'F');
    doc.setFontSize(11);
    doc.setFont("helvetica", "bold");
    doc.text('DESCRIPCIÓN DE LA INCIDENCIA:', 15, currentY + 7);
    
    doc.setFont("helvetica", "normal");
    doc.setFontSize(11);
    const splitText = doc.splitTextToSize(descripcion, 175);
    doc.text(splitText, 18, currentY + 20);

    doc.setFontSize(8);
    doc.setTextColor(148, 163, 184);
    doc.text('Documento oficial generado por la plataforma Educattio', 105, 285, { align: 'center' });

    doc.save(`incidencia_${clase.replace(/\s+/g, '_')}.pdf`);
}

function exportarListaIncidenciasPDF() {
    if (!window.incidenciasActuales || window.incidenciasActuales.length === 0) {
        alert('No hay historial de incidencias para exportar.');
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4'); 
    const fecha = new Date().toLocaleDateString();

    doc.setFillColor(30, 41, 59);
    doc.rect(0, 0, 297, 40, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(20);
    doc.setFont("helvetica", "bold");
    doc.text('EDUCATTIO - HISTORIAL DE INCIDENCIAS', 148.5, 25, { align: 'center' });

    const head = [['FECHA', 'CENTRO / CURSO', 'CLASE', 'ALUMNO', 'TIPO', 'DESCRIPCIÓN']];
    const body = window.incidenciasActuales.map(inc => [
        inc.fecha_incidencia,
        inc.nombre_centro,
        inc.nombre_clase,
        inc.nombre_alumno || 'General',
        inc.tipo,
        inc.descripcion
    ]);

    doc.autoTable({
        startY: 50,
        head: head,
        body: body,
        theme: 'striped',
        headStyles: { fillColor: [37, 99, 235], fontSize: 10, halign: 'center' },
        styles: { fontSize: 9, cellPadding: 4, valign: 'middle' },
        columnStyles: {
            5: { cellWidth: 80 }
        }
    });

    doc.save(`historial_incidencias_${fecha}.pdf`);
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString().replace(/[&<>"']/g, function(match) {
        const escape = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        return escape[match];
    });
}
