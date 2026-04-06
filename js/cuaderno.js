    // --- EXPORTAR A EXCEL Y PDF ---
    // SheetJS y jsPDF deben estar incluidos en el HTML (CDN)
    document.addEventListener('DOMContentLoaded', function() {
        // ...existing code...

        // Exportar a Excel
        const btnExcel = document.getElementById('exportarExcel');
        if (btnExcel) {
            btnExcel.addEventListener('click', function() {
                exportarTablaExcel();
            });
        }
        // Exportar a PDF
        const btnPDF = document.getElementById('exportarPDF');
        if (btnPDF) {
            btnPDF.addEventListener('click', function() {
                exportarTablaPDF();
            });
        }
    });

    function exportarTablaExcel() {
        // Requiere SheetJS (xlsx.min.js)
        if (typeof XLSX === 'undefined') { alert('SheetJS no está cargado'); return; }
        const tabla = document.querySelector('.gradebook-table');
        const wb = XLSX.utils.table_to_book(tabla, {sheet: "Notas"});
        XLSX.writeFile(wb, 'cuaderno_notas.xlsx');
    }

    function exportarTablaPDF() {
        // Requiere jsPDF y autoTable
        if (typeof jsPDF === 'undefined' || typeof window.jspdfAutoTable === 'undefined') { alert('jsPDF o autoTable no están cargados'); return; }
        const doc = new jsPDF();
        doc.text('Cuaderno de Notas', 14, 16);
        window.jspdfAutoTable.autoTable(doc, { html: '.gradebook-table', startY: 22 });
        doc.save('cuaderno_notas.pdf');
    }
    // 7. Aplicar fórmulas a celdas seleccionadas
    document.querySelectorAll('.formula-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!selectedCells || selectedCells.length === 0) return;
            const formula = btn.dataset.formula;
            const valor = parseFloat(document.getElementById('valorFormula').value);
            let nuevoValor = null;
            if (formula === 'sumar') {
                selectedCells.forEach(inp => {
                    let v = parseFloat(inp.value) || 0;
                    if (!isNaN(valor)) v += valor;
                    v = Math.max(0, Math.min(10, v));
                    inp.value = v;
                    triggerNotaUpdate(inp, v);
                });
            } else if (formula === 'promedio') {
                let suma = 0, count = 0;
                selectedCells.forEach(inp => {
                    let v = parseFloat(inp.value);
                    if (!isNaN(v)) { suma += v; count++; }
                });
                if (count > 0) {
                    nuevoValor = (suma / count).toFixed(2);
                    selectedCells.forEach(inp => {
                        inp.value = nuevoValor;
                        triggerNotaUpdate(inp, nuevoValor);
                    });
                }
            } else if (formula === 'multiplicar') {
                selectedCells.forEach(inp => {
                    let v = parseFloat(inp.value) || 0;
                    if (!isNaN(valor)) v *= valor;
                    v = Math.max(0, Math.min(10, v));
                    inp.value = v;
                    triggerNotaUpdate(inp, v);
                });
            } else if (formula === 'dividir') {
                if (valor === 0) return;
                selectedCells.forEach(inp => {
                    let v = parseFloat(inp.value) || 0;
                    if (!isNaN(valor)) v /= valor;
                    v = Math.max(0, Math.min(10, v));
                    inp.value = v;
                    triggerNotaUpdate(inp, v);
                });
            } else if (formula === 'fijar') {
                if (isNaN(valor)) return;
                selectedCells.forEach(inp => {
                    let v = Math.max(0, Math.min(10, valor));
                    inp.value = v;
                    triggerNotaUpdate(inp, v);
                });
            }
            clearSelectedCells();
        });
    });

    function triggerNotaUpdate(input, valor) {
        const alumnoId = input.dataset.alumno;
        const itemId = input.dataset.item;
        calcularMedia(alumnoId);
        guardarNotaBD(alumnoId, itemId, valor, input);
    }
// cuaderno.js - Versión unificada y sin duplicados
document.addEventListener('DOMContentLoaded', function() {
    // 1. Calcular medias iniciales
    const alumnosIds = [...new Set(Array.from(document.querySelectorAll('.grade-input')).map(inp => inp.dataset.alumno))];
    alumnosIds.forEach(id => calcularMedia(id));

    // 2. Escuchar cambios en las notas
    document.querySelectorAll('.grade-input').forEach(input => {
        input.addEventListener('change', function() {
            const alumnoId = this.dataset.alumno;
            const itemId = this.dataset.item;
            const nota = this.value;
            calcularMedia(alumnoId);
            guardarNotaBD(alumnoId, itemId, nota, this);
        });

        // Limitar valores entre 0 y 10
        input.addEventListener('input', function() {
            if (this.value > 10) this.value = 10;
            if (this.value < 0) this.value = 0;
        });
    });

    // 3. Filtrado de alumnos en tiempo real
    const filtroInput = document.getElementById('filtroAlumno');
    if (filtroInput) {
        filtroInput.addEventListener('input', function() {
            const texto = this.value.toLowerCase();
            document.querySelectorAll('.gradebook-table tbody tr').forEach(tr => {
                const nombre = tr.querySelector('.student-name').textContent.toLowerCase();
                tr.style.display = nombre.includes(texto) ? '' : 'none';
            });
        });
    }

    // 4. Mostrar icono comentario solo al pasar el ratón
    document.querySelectorAll('.nota-celda').forEach(td => {
        td.addEventListener('mouseenter', function() {
            const btn = td.querySelector('.comentario-btn');
            if (btn) btn.style.display = 'block';
        });
        td.addEventListener('mouseleave', function() {
            const btn = td.querySelector('.comentario-btn');
            if (btn) btn.style.display = 'none';
        });
    });

    // 5. Abrir modal de comentario
    document.querySelectorAll('.comentario-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const alumnoId = btn.dataset.alumno;
            const itemId = btn.dataset.item;
            abrirModalComentario(alumnoId, itemId);
        });
    });

    // 6. Selección múltiple de celdas tipo arrastrar
    let isSelecting = false;
    let startCell = null;
    let endCell = null;
    let selectedCells = [];

    document.querySelectorAll('.grade-input').forEach(input => {
        input.addEventListener('mousedown', function(e) {
            isSelecting = true;
            startCell = this;
            clearSelectedCells();
            this.classList.add('selected-cell');
            selectedCells = [this];
            updateSeleccionadasInfo();
        });
        input.addEventListener('mouseenter', function(e) {
            if (isSelecting && startCell) {
                endCell = this;
                selectRange(startCell, endCell);
            }
        });
    });
    document.addEventListener('mouseup', function() {
        isSelecting = false;
        startCell = null;
        endCell = null;
    });

    function selectRange(cell1, cell2) {
        clearSelectedCells();
        const allInputs = Array.from(document.querySelectorAll('.grade-input'));
        const idx1 = allInputs.indexOf(cell1);
        const idx2 = allInputs.indexOf(cell2);
        const [min, max] = [Math.min(idx1, idx2), Math.max(idx1, idx2)];
        selectedCells = allInputs.slice(min, max + 1);
        selectedCells.forEach(inp => inp.classList.add('selected-cell'));
        updateSeleccionadasInfo();
    }
    function clearSelectedCells() {
        document.querySelectorAll('.grade-input.selected-cell').forEach(inp => inp.classList.remove('selected-cell'));
        selectedCells = [];
        updateSeleccionadasInfo();
    }
    function updateSeleccionadasInfo() {
        const info = document.getElementById('seleccionadas-info');
        if (info) {
            info.textContent = selectedCells.length > 0 ? `Seleccionadas: ${selectedCells.length}` : '';
        }
    }
});
// CSS para celdas seleccionadas (puedes moverlo a tu CSS principal)
const style = document.createElement('style');
style.innerHTML = `.grade-input.selected-cell { outline: 2px solid #007bff !important; background: #e3f0ff !important; }`;
document.head.appendChild(style);

// Modal comentario
function abrirModalComentario(alumnoId, itemId) {
    document.getElementById('comentarioAlumnoId').value = alumnoId;
    document.getElementById('comentarioItemId').value = itemId;
    document.getElementById('comentarioTexto').value = '';
    document.getElementById('modalComentario').style.display = 'flex';
    // Cargar comentario existente
    fetch('../php/obtener_comentario.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ alumno_id: alumnoId, item_id: itemId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('comentarioTexto').value = data.comentario || '';
        }
    });
}
function cerrarModalComentario() {
    document.getElementById('modalComentario').style.display = 'none';
}
function guardarComentario(event) {
    event.preventDefault();
    const alumnoId = document.getElementById('comentarioAlumnoId').value;
    const itemId = document.getElementById('comentarioItemId').value;
    const comentario = document.getElementById('comentarioTexto').value;
    fetch('../php/guardar_comentario.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ alumno_id: alumnoId, item_id: itemId, comentario: comentario })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            cerrarModalComentario();
        }
    });
}

// --- FUNCIONES DE CÁLCULO ---
function calcularMedia(alumnoId) {
    const notasAlumno = document.querySelectorAll(`.grade-input[data-alumno="${alumnoId}"]`);
    let sumaPonderada = 0;
    let sumaPesos = 0;

    notasAlumno.forEach(input => {
        const nota = parseFloat(input.value);
        const peso = parseFloat(input.dataset.peso);
        if (!isNaN(nota)) {
            sumaPonderada += (nota * peso);
            sumaPesos += peso;
        }
    });

    const celdaMedia = document.getElementById(`media-${alumnoId}`);
    if (sumaPesos > 0) {
        const media = sumaPonderada / sumaPesos; 
        celdaMedia.textContent = media.toFixed(2);
        celdaMedia.style.color = media >= 5 ? '#27ae60' : '#e74c3c';
    } else {
        celdaMedia.textContent = "-";
    }
}

// --- GUARDAR NOTAS ---
function guardarNotaBD(alumnoId, itemId, valor, inputElement) {
    inputElement.style.backgroundColor = '#fff3cd'; 
    fetch('../php/guardar_nota.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ alumno_id: alumnoId, item_id: itemId, nota: valor })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            inputElement.style.backgroundColor = '#d4edda';
            setTimeout(() => { inputElement.style.backgroundColor = 'transparent'; }, 500);
        }
    })
    .catch(() => { inputElement.style.backgroundColor = '#f8d7da'; });
}

// --- GESTIÓN DE COLUMNAS (MODALES) ---
function abrirModalColumna() { document.getElementById('modalColumna').style.display = 'flex'; }
function cerrarModalColumna() { document.getElementById('modalColumna').style.display = 'none'; document.getElementById('formNuevaColumna').reset(); }

function abrirModalEditar(id, titulo, peso) {
    document.getElementById('editIdColumna').value = id;
    document.getElementById('editNombreColumna').value = titulo;
    document.getElementById('editPesoColumna').value = peso;
    document.getElementById('modalEditarColumna').style.display = 'flex';
}
function cerrarModalEditar() { document.getElementById('modalEditarColumna').style.display = 'none'; }

// Cerrar al hacer clic fuera
window.onclick = function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        cerrarModalColumna();
        cerrarModalEditar();
    }
}

// --- ACCIONES AJAX (CREAR, EDITAR, ELIMINAR) ---
function guardarNuevaColumna(event) {
    event.preventDefault();
    const datos = {
        asignatura_id: document.getElementById('asignaturaActualId').value,
        periodo_evaluacion: document.getElementById('periodoColumna').value,
        nombre_item: document.getElementById('nombreColumna').value,
        peso: document.getElementById('pesoColumna').value
    };
    ejecutarFetch('../php/crear_columna.php', datos);
}

function guardarEdicionColumna(event) {
    event.preventDefault();
    const datos = {
        id: document.getElementById('editIdColumna').value,
        titulo: document.getElementById('editNombreColumna').value,
        peso: document.getElementById('editPesoColumna').value
    };
    ejecutarFetch('../php/editar_columna.php', datos);
}

function borrarColumna(id) {
    if (confirm("⚠️ ¿Eliminar esta prueba y todas sus notas?")) {
        ejecutarFetch('../php/eliminar_columna.php', { id: id });
    }
}

function ejecutarFetch(url, datos) {
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(res => res.json())
    .then(data => data.status === 'success' ? window.location.reload() : alert(data.mensaje))
    .catch(err => console.error("Error:", err));
}