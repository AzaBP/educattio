// --- EXPORTAR A EXCEL Y PDF ---
// SheetJS y jsPDF deben estar incluidos en el HTML (CDN)
document.addEventListener('DOMContentLoaded', function () {
    // ...existing code...

    // Exportar a Excel
    const btnExcel = document.getElementById('exportarExcel');
    if (btnExcel) {
        btnExcel.addEventListener('click', function () {
            exportarTablaExcel();
        });
    }
    // Exportar a PDF
    const btnPDF = document.getElementById('exportarPDF');
    if (btnPDF) {
        btnPDF.addEventListener('click', function () {
            exportarTablaPDF();
        });
    }
});

function exportarTablaExcel() {
    // Requiere SheetJS (xlsx.min.js)
    if (typeof XLSX === 'undefined') { alert('SheetJS no está cargado'); return; }
    const tabla = document.querySelector('.gradebook-table');
    const wb = XLSX.utils.table_to_book(tabla, { sheet: "Notas" });
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
    btn.addEventListener('click', function () {
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
document.addEventListener('DOMContentLoaded', function () {
    // 1. Calcular medias iniciales (marcando como carga inicial)
    const alumnosIds = [...new Set(Array.from(document.querySelectorAll('.grade-input')).map(inp => inp.dataset.alumno))];
    alumnosIds.forEach(id => calcularMedia(id, true));

    // 2. Escuchar cambios en las notas
    document.querySelectorAll('.grade-input').forEach(input => {
        input.addEventListener('change', function () {
            const alumnoId = this.dataset.alumno;
            const itemId = this.dataset.item;
            const nota = this.value;
            calcularMedia(alumnoId);
            guardarNotaBD(alumnoId, itemId, nota, this);
        });

        // Limitar valores entre 0 y 10
        input.addEventListener('input', function () {
            if (this.value > 10) this.value = 10;
            if (this.value < 0) this.value = 0;
        });
    });

    // 3. Filtrado de alumnos en tiempo real
    const filtroInput = document.getElementById('filtroAlumno');
    if (filtroInput) {
        filtroInput.addEventListener('input', function () {
            const texto = this.value.toLowerCase();
            document.querySelectorAll('.gradebook-table tbody tr').forEach(tr => {
                const nombre = tr.querySelector('.student-name').textContent.toLowerCase();
                tr.style.display = nombre.includes(texto) ? '' : 'none';
            });
        });
    }

    // 4. Mostrar icono comentario solo al pasar el ratón
    document.querySelectorAll('.nota-celda').forEach(td => {
        td.addEventListener('mouseenter', function () {
            const btn = td.querySelector('.comentario-btn');
            if (btn) btn.style.display = 'block';
        });
        td.addEventListener('mouseleave', function () {
            const btn = td.querySelector('.comentario-btn');
            if (btn) btn.style.display = 'none';
        });
    });

    // 5. Abrir modal de comentario
    document.querySelectorAll('.comentario-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const alumnoId = btn.dataset.alumno;
            const itemId = btn.dataset.item;
            abrirModalComentario(alumnoId, itemId);
        });
    });

    // --- LÓGICA DE BARRA DE FÓRMULAS TIPO EXCEL ---
    let activeCell = null;
    const formulaInput = document.getElementById('excel-formula-input');
    const fxStatus = document.getElementById('fx-status');

    function updateFormulaBar(input) {
        activeCell = input;
        if (formulaInput) {
            if (input.dataset.formula) {
                formulaInput.value = "=" + input.dataset.formula;
                if (fxStatus) fxStatus.style.color = "#2563eb";
            } else {
                formulaInput.value = input.value;
                if (fxStatus) fxStatus.style.color = "#64748b";
            }
        }
    }

    // Al escribir en la barra, si empieza por =, cambiamos color del fx
    if (formulaInput) {
        formulaInput.addEventListener('input', function() {
            if (this.value.startsWith('=')) {
                if (fxStatus) fxStatus.style.color = "#2563eb";
            } else {
                if (fxStatus) fxStatus.style.color = "#64748b";
            }
        });

        formulaInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                aplicarFormulaDesdeBarra();
            }
        });
    }

    window.insertarEnFormula = function(titulo) {
        if (formulaInput) {
            // Si no empieza por =, lo añadimos nosotros para entrar en modo fórmula
            if (!formulaInput.value.startsWith('=')) {
                formulaInput.value = "=" + formulaInput.value;
            }
            formulaInput.value += `[${titulo}]`;
            formulaInput.focus();
            if (fxStatus) fxStatus.style.color = "#2563eb";
        }
    };

    window.aplicarFormulaDesdeBarra = function() {
        if (!activeCell || !formulaInput) return;
        let val = formulaInput.value.trim();
        
        if (val.startsWith('=')) {
            // Es una fórmula
            const formulaClean = val.substring(1);
            activeCell.dataset.formula = formulaClean;
            activeCell.classList.add('auto-grade');
            activeCell.readOnly = true;
            activeCell.tabIndex = -1;
            
            // Aplicar a la columna (BD)
            actualizarFormulaColumna(activeCell.dataset.item, formulaClean);
        } else {
            // Es un valor manual
            activeCell.dataset.formula = "";
            activeCell.classList.remove('auto-grade');
            activeCell.readOnly = false;
            activeCell.tabIndex = 0;
            activeCell.value = val;
            triggerNotaUpdate(activeCell, val);
        }
    };

    function actualizarFormulaColumna(itemId, formula) {
        ejecutarFetch('../php/editar_columna_simple.php', { id: itemId, formula: formula });
    }

    document.querySelectorAll('.grade-input').forEach(input => {
        input.addEventListener('focus', function() {
            updateFormulaBar(this);
        });

        input.addEventListener('mousedown', function (e) {
            // Si estamos escribiendo una fórmula, el mousedown en celdas inserta su nombre
            if (document.activeElement === formulaInput && formulaInput.value.startsWith('=')) {
                e.preventDefault();
                formulaInput.value += `[${this.dataset.titulo}]`;
                formulaInput.focus();
                return;
            }

            isSelecting = true;
            startCell = this;
            clearSelectedCells();
            this.classList.add('selected-cell');
            selectedCells = [this];
            updateSeleccionadasInfo();
            updateFormulaBar(this);
        });

        input.addEventListener('mouseenter', function (e) {
            if (isSelecting && startCell) {
                endCell = this;
                selectRange(startCell, endCell);
            }
        });
    });

    document.addEventListener('mouseup', () => {
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

    function updateSeleccionadasInfo() {
        const info = document.getElementById('seleccionadas-info');
        const toolbar = document.getElementById('floating-formula-bar');
        if (info && toolbar) {
            const count = selectedCells.length;
            info.textContent = count > 1 ? `${count} seleccionadas` : '0 seleccionadas';
            // Solo mostramos toolbar si hay más de 1 seleccionada (rango)
            toolbar.style.display = count > 1 ? 'block' : 'none';
        }
    }

    // Funciones globales para la toolbar
    window.clearSelectedCells = clearSelectedCells;

    window.aplicarOperacionRapida = function(tipo) {
        if (!selectedCells || selectedCells.length === 0) return;
        
        let valor = 0;
        if (tipo === 'sumar') {
            const v = prompt("¿Cuánto quieres sumar a cada nota?", "0.5");
            if (v === null) return;
            valor = parseFloat(v.replace(',', '.'));
        }

        selectedCells.forEach(inp => {
            let v = parseFloat(inp.value) || 0;
            if (tipo === 'sumar') v += valor;
            
            v = Math.max(0, Math.min(10, v));
            inp.value = v.toFixed(2);
            triggerNotaUpdate(inp, v);
        });
        
        if (tipo === 'promedio') {
            alert("Consejo: Para promedios dinámicos, usa el botón 'Crear Columna' de la derecha.");
        }
    };

    window.crearColumnaDesdeSeleccion = function() {
        if (!selectedCells || selectedCells.length === 0) return;
        
        // Obtener títulos únicos de las columnas seleccionadas
        const titulos = [...new Set(selectedCells.map(inp => inp.dataset.titulo))];
        if (titulos.length === 0) return;

        let formula = "";
        if (titulos.length > 1) {
            formula = "(" + titulos.map(t => `[${t}]`).join(" + ") + ") / " + titulos.length;
        } else {
            formula = `[${titulos[0]}]`;
        }

        abrirModalColumna();
        document.getElementById('nombreColumna').value = "Media " + (titulos.length > 1 ? "Selección" : titulos[0]);
        document.getElementById('formulaColumna').value = formula;
        document.getElementById('pesoColumna').value = "0.00"; 
    };

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
function evaluateFormula(formula, valuesMap) {
    if (!formula) return 0;
    let expression = formula;
    
    // Obtenemos los títulos y los ordenamos por longitud para evitar reemplazos parciales
    const titles = Object.keys(valuesMap).sort((a, b) => b.length - a.length);
    
    let allTokensReplaced = true;
    titles.forEach(title => {
        let escapedTitle = title.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        // Usamos el flag 'i' para que no importe si es mayúscula o minúscula
        let regex = new RegExp('\\[' + escapedTitle + '\\]', 'gi');
        
        if (regex.test(expression)) {
            let val = valuesMap[title];
            if (val === undefined || isNaN(val)) {
                allTokensReplaced = false;
                return;
            }
            expression = expression.replace(regex, val);
        }
    });

    // Si aún quedan corchetes, es que falta algún valor o hay un error de escritura
    if (expression.includes('[') || !allTokensReplaced) {
        console.warn("Fórmula incompleta o con errores:", expression);
        return NaN;
    }

    expression = expression.replace(/,/g, '.').trim();

    try {
        if (/[^0-9\.\+\-\*\/\(\)\s]/.test(expression)) {
            console.error("Caracteres prohibidos detectados:", expression);
            return 0;
        }
        // Usamos una evaluación más limpia
        const result = eval(expression); 
        return isFinite(result) ? result : 0;
    } catch (e) {
        console.error("Error al evaluar expresión:", expression, e);
        return 0;
    }
}

function calcularMedia(alumnoId, isInitialLoad = false) {
    const inputs = Array.from(document.querySelectorAll(`.grade-input[data-alumno="${alumnoId}"]`));
    let valuesMap = {};
    
    // 1. Cargar valores manuales primero
    inputs.forEach(input => {
        if (!input.dataset.formula) {
            // Soportar comas decimales
            const rawVal = input.value.toString().replace(',', '.');
            valuesMap[input.dataset.titulo] = parseFloat(rawVal) || 0;
        }
    });

    // 2. Resolver fórmulas (hasta 3 pasadas para permitir fórmulas de fórmulas)
    let changed = true;
    let passes = 0;
    while (changed && passes < 3) {
        changed = false;
        passes++;
        inputs.forEach(input => {
            if (input.dataset.formula) {
                const resultado = evaluateFormula(input.dataset.formula, valuesMap);
                if (!isNaN(resultado)) {
                    const newValue = resultado.toFixed(2);
                    if (input.value !== newValue) {
                        const oldValue = input.value;
                        input.value = newValue;
                        valuesMap[input.dataset.titulo] = resultado;
                        changed = true;
                        
                        // Guardar en BD solo si no es la carga inicial para no saturar
                        if (!isInitialLoad && oldValue !== newValue) {
                            guardarNotaBD(alumnoId, input.dataset.item, newValue, input);
                        }
                    } else if (valuesMap[input.dataset.titulo] === undefined) {
                        // Si el valor es igual pero no estaba en el mapa, lo añadimos
                        valuesMap[input.dataset.titulo] = resultado;
                    }
                }
            }
        });
    }

    // 3. Media Final Ponderada
    let sumaPonderada = 0;
    let sumaPesos = 0;
    inputs.forEach(input => {
        const nota = parseFloat(input.value) || 0;
        const peso = parseFloat(input.dataset.peso) || 0;
        if (peso > 0) {
            sumaPonderada += (nota * peso);
            sumaPesos += peso;
        }
    });

    const celdaMedia = document.getElementById(`media-${alumnoId}`);
    if (celdaMedia) {
        if (sumaPesos > 0) {
            const media = sumaPonderada / sumaPesos; 
            celdaMedia.textContent = media.toFixed(2);
            celdaMedia.style.color = media >= 5 ? '#27ae60' : '#e74c3c';
        } else {
            celdaMedia.textContent = "0.00";
            celdaMedia.style.color = '#e74c3c';
        }
    }
}

// --- GUARDAR NOTAS ---
function guardarNotaBD(alumnoId, itemId, valor, inputElement) {
    inputElement.style.backgroundColor = '#fff3cd';
    fetch('../php/guardar_nota.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            alumno_id: alumnoId, 
            item_id: itemId, 
            nota: valor,
            asignatura_id: typeof ASIGNATURA_ACTUAL_ID !== 'undefined' ? ASIGNATURA_ACTUAL_ID : 1
        })
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                inputElement.style.backgroundColor = '#d4edda';
                setTimeout(() => { inputElement.style.backgroundColor = 'transparent'; }, 500);
            }
        })
        .catch(() => { inputElement.style.backgroundColor = '#f8d7da'; });
}

// --- GESTIÓN DE COLUMNAS (MODALES) ---
function abrirModalColumna() { document.getElementById('modalColumna').style.display = 'flex'; }
function cerrarModalColumna() { document.getElementById('modalColumna').style.display = 'none'; document.getElementById('formNuevaColumna').reset(); }

function abrirModalEditar(id, titulo, peso, formula) {
    document.getElementById('editIdColumna').value = id;
    document.getElementById('editNombreColumna').value = titulo;
    document.getElementById('editPesoColumna').value = peso;
    document.getElementById('editFormulaColumna').value = formula || '';
    document.getElementById('modalEditarColumna').style.display = 'flex';
}
function cerrarModalEditar() { document.getElementById('modalEditarColumna').style.display = 'none'; }

// Cerrar al hacer clic fuera
window.onclick = function (event) {
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
        peso: document.getElementById('pesoColumna').value,
        formula: document.getElementById('formulaColumna').value
    };
    ejecutarFetch('../php/crear_columna.php', datos);
}

function guardarEdicionColumna(event) {
    event.preventDefault();
    const datos = {
        id: document.getElementById('editIdColumna').value,
        titulo: document.getElementById('editNombreColumna').value,
        peso: document.getElementById('editPesoColumna').value,
        formula: document.getElementById('editFormulaColumna').value
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