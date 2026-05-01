<?php
session_start();
include 'conexion.php';

// Verificación de seguridad básica
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener datos completos del usuario para el sidebar
$usuario_id = $_SESSION['usuario_id'];
try {
    $sql = "SELECT nombre_completo, nombre_usuario, foto_perfil FROM usuarios WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $usuario_id]);
    $datos_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $datos_usuario = null;
}

// Obtener IDs de la URL dinámica
$asignatura_id = $_GET['asig_id'] ?? null;
$periodo_id_actual = $_GET['periodo_id'] ?? null;

if (!$asignatura_id || !$periodo_id_actual) {
    die("Error: Faltan parámetros para cargar el cuaderno. Usa la navegación de la plataforma.");
}

try {
    // 1. OBTENER LOS PERIODOS DE ESTA ASIGNATURA (Para las pestañas dinámicas)
    $sql_periodos = "SELECT id, nombre_periodo FROM periodos_evaluacion WHERE asignatura_id = :asig_id ORDER BY id ASC";
    $stmt_periodos = $conexion->prepare($sql_periodos);
    $stmt_periodos->execute([':asig_id' => $asignatura_id]);
    $periodos_disponibles = $stmt_periodos->fetchAll(PDO::FETCH_ASSOC);

    // Obtener el nombre del periodo actual para mostrarlo en títulos/modales
    $nombre_periodo_actual = "Desconocido";
    foreach($periodos_disponibles as $p) {
        if($p['id'] == $periodo_id_actual) {
            $nombre_periodo_actual = $p['nombre_periodo'];
            break;
        }
    }

    // === INICIO LÓGICA EVALUACIÓN FINAL (CORREGIDO) ===
    $es_final = (strtolower(trim($nombre_periodo_actual)) === 'final');

    if ($es_final) {
        // Buscar los otros periodos (1ª Eval, 2ª Eval...)
        $sql_otros = "SELECT id, nombre_periodo FROM periodos_evaluacion WHERE asignatura_id = :asig_id AND id != :per_id";
        $stmt_otros = $conexion->prepare($sql_otros);
        $stmt_otros->execute([':asig_id' => $asignatura_id, ':per_id' => $periodo_id_actual]);
        $otros_periodos = $stmt_otros->fetchAll(PDO::FETCH_ASSOC);

        foreach ($otros_periodos as $op) {
            // Comprobamos si existe, añadiendo asignatura_id
            $sql_check = "SELECT id FROM items_evaluacion WHERE periodo_id = :per_id AND titulo = :nombre AND asignatura_id = :asig_id";
            $stmt_check = $conexion->prepare($sql_check);
            $stmt_check->execute([
                ':per_id' => $periodo_id_actual, 
                ':nombre' => $op['nombre_periodo'],
                ':asig_id' => $asignatura_id
            ]);
            
            if ($stmt_check->rowCount() == 0) {
                // Insertamos añadiendo asignatura_id para evitar el error 1452
                $sql_insert = "INSERT INTO items_evaluacion (titulo, peso, periodo_id, asignatura_id) VALUES (:nombre, 0, :per_id, :asig_id)";
                $stmt_insert = $conexion->prepare($sql_insert);
                $stmt_insert->execute([
                    ':nombre' => $op['nombre_periodo'], 
                    ':per_id' => $periodo_id_actual,
                    ':asig_id' => $asignatura_id
                ]);
            }
        }
    }

    function calcularMediaPeriodoAnterior($alumno_id, $periodo_id, $conexion) {
        // Usamos LEFT JOIN para incluir todos los items aunque no tengan nota (contarán como 0)
        $sql = "SELECT SUM(COALESCE(e.nota, 0) * i.peso) / SUM(i.peso) as media
                FROM items_evaluacion i
                LEFT JOIN evaluaciones e ON i.id = e.item_id AND e.alumno_id = :al_id
                WHERE i.periodo_id = :per_id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':al_id' => $alumno_id, ':per_id' => $periodo_id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si la suma de pesos es 0, evitamos división por cero
        return ($res['media'] !== null) ? round($res['media'], 2) : '0';
    }

    // 2. OBTENER LAS COLUMNAS SOLO DEL PERIODO ACTUAL
    // CAMBIO AQUÍ: Eliminamos 'asignatura_id' de la tabla items_evaluacion porque ya se filtra por periodo_id
    $sql_items = "SELECT id, titulo, peso FROM items_evaluacion WHERE periodo_id = :periodo_id";
    $stmt_items = $conexion->prepare($sql_items);
    $stmt_items->execute([':periodo_id' => $periodo_id_actual]);
    $items_evaluacion = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    // === FIN LÓGICA CORREGIDA ===

    // 2. OBTENER LAS COLUMNAS (ÍTEMS) SOLO DEL PERIODO ACTUAL
    $sql_items = "SELECT id, titulo, peso FROM items_evaluacion 
                  WHERE asignatura_id = :asig_id AND periodo_id = :periodo_id";
    $stmt_items = $conexion->prepare($sql_items);
    $stmt_items->execute([
        ':asig_id' => $asignatura_id,
        ':periodo_id' => $periodo_id_actual
    ]);
    $items_evaluacion = $stmt_items->fetchAll(PDO::FETCH_ASSOC);


    // --- NUEVO: OBTENER INFO DE LA ASIGNATURA Y LA CLASE PARA EL BOTÓN DE VOLVER ---
    $sql_info = "SELECT a.nombre_asignatura, a.clase_id, c.nombre_clase 
                 FROM asignaturas a 
                 JOIN clases c ON a.clase_id = c.id 
                 WHERE a.id = :asig_id";
    $stmt_info = $conexion->prepare($sql_info);
    $stmt_info->execute([':asig_id' => $asignatura_id]);
    $info_asig = $stmt_info->fetch(PDO::FETCH_ASSOC);

    // 3. OBTENER LOS ALUMNOS
    $sql_alumnos = "SELECT a.id, a.nombre_alumno, a.foto
                    FROM alumnos a
                    JOIN alumnos_asignaturas aa ON a.id = aa.alumno_id
                    WHERE aa.asignatura_id = :asig_id 
                    ORDER BY a.nombre_alumno ASC";
    $stmt_al = $conexion->prepare($sql_alumnos);
    $stmt_al->execute([':asig_id' => $asignatura_id]);
    $alumnos = $stmt_al->fetchAll(PDO::FETCH_ASSOC);

    // 4. OBTENER TODAS LAS NOTAS
    $sql_notas = "SELECT alumno_id, item_id, nota FROM evaluaciones";
    $stmt_notas = $conexion->prepare($sql_notas);
    $stmt_notas->execute();
    $todas_las_notas = $stmt_notas->fetchAll(PDO::FETCH_ASSOC);

    // Organizar notas en una matriz
    $matriz_notas = [];
    foreach ($todas_las_notas as $n) {
        $matriz_notas[$n['alumno_id']][$n['item_id']] = $n['nota'];
    }

} catch (PDOException $e) {
    die("Error crítico al cargar el cuaderno: " . $e->getMessage());
}

function obtenerNotaActual($alumno_id, $item_id, $matriz_notas) {
    if (isset($matriz_notas[$alumno_id][$item_id])) {
        return $matriz_notas[$alumno_id][$item_id];
    }
    return ""; 
}

?> <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - Cuaderno de Notas</title>
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css">
    <link rel="stylesheet" href="../css/cuaderno.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- SheetJS (Excel) -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <!-- jsPDF y autoTable (PDF) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
</head>
<body>

    <div class="dashboard-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="header-top-row mb-3">
                <a href="detalles_clase.php?id=<?php echo $info_asig['clase_id']; ?>" class="back-link" style="text-decoration: none; color: #64748b; font-weight: 600;">
                    <i class="fas fa-arrow-left"></i> Volver a <?php echo htmlspecialchars($info_asig['nombre_clase']); ?>
                </a>
            </div>

            <div class="page-header-flex">
                <div>
                    <h1>Cuaderno de Evaluación</h1>
                    <p><?php echo htmlspecialchars($info_asig['nombre_asignatura']); ?> - <?php echo htmlspecialchars($info_asig['nombre_clase']); ?></p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-secondary" onclick="descargarCSV(); return false;">
                        <i class="fas fa-file-excel"></i> Exportar
                    </button>
                    <button class="btn btn-primary" onclick="abrirModalColumna()"><i class="fas fa-plus"></i> Añadir Prueba</button>
                </div>
            </div>

            <div class="eval-tabs">
                <?php foreach ($periodos_disponibles as $per): ?>
                    <a href="?asig_id=<?php echo $asignatura_id; ?>&periodo_id=<?php echo $per['id']; ?>" 
                       class="eval-tab <?php echo ($per['id'] == $periodo_id_actual) ? 'active' : ''; ?>">
                       <?php echo htmlspecialchars($per['nombre_periodo']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="spreadsheet-container">
                <?php
                // Primero calculamos la suma total de pesos para saber el color del encabezado final
                $suma_total_pesos = 0;
                foreach ($items_evaluacion as $item) {
                    $suma_total_pesos += $item['peso'];
                }
                $clase_peso_total = (abs($suma_total_pesos - 1.0) > 0.001) ? 'weight-warning' : 'weight-ok';
                ?>

                <?php
                // Calculamos la suma total de pesos para colorear la Media Final
                $suma_total_pesos = 0;
                foreach ($items_evaluacion as $item) {
                    $suma_total_pesos += $item['peso'];
                }
                // Si la suma no es 1 (100%), activamos la clase de error en rojo
                $clase_peso_total = (abs($suma_total_pesos - 1.0) > 0.001) ? 'weight-warning' : 'weight-ok';
                ?>

                <table class="gradebook-table">
                    <thead>
                        <tr>
                            <th class="sticky-col">Alumno</th>
                            <?php foreach ($items_evaluacion as $item): ?>
                                <th>
                                    <div class="header-content" style="display: flex; flex-direction: column; align-items: center;">
                                        
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span class="item-title" style="font-weight: 600;">
                                                <?php echo htmlspecialchars($item['titulo']); ?>
                                            </span>
                                            <span class="header-weight" style="font-size: 0.85em; color: #64748b;">
                                                (<?php echo round($item['peso'] * 100, 1); ?>%)
                                            </span>
                                        </div>
                                        
                                        <div class="header-actions">
                                            <button class="icon-btn" onclick="abrirModalEditar(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['titulo'], ENT_QUOTES); ?>', <?php echo $item['peso']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="icon-btn delete-btn" onclick="borrarColumna(<?php echo $item['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                            <th>
                                <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                    <span>Media Final</span>
                                    <span class="header-weight <?php echo $clase_peso_total; ?>" style="font-size: 0.85em;">
                                        (<?php echo round($suma_total_pesos * 100, 1); ?>%)
                                    </span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <?php foreach ($alumnos as $alumno): ?>
                        <tr>
                            <td class="sticky-col student-cell">
                                <div class="student-info">
                                    <div class="student-avatar" onclick="abrirModalAvatar(<?php echo $alumno['id']; ?>, '<?php echo htmlspecialchars($alumno['foto'] ?? ''); ?>')">
                                        <?php if (!empty($alumno['foto'])): ?>
                                            <img src="../icons/<?php echo htmlspecialchars($alumno['foto']); ?>" alt="">
                                        <?php else: ?>
                                            <div class="avatar-placeholder"><?php 
                                                $n = explode(' ', $alumno['nombre_alumno']);
                                                echo strtoupper(substr($n[0], 0, 1) . (isset($n[1]) ? substr($n[1], 0, 1) : '')); 
                                            ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="student-name"><?php echo htmlspecialchars($alumno['nombre_alumno']); ?></span>
                                </div>
                            </td>
                            
                            <?php foreach ($items_evaluacion as $item): ?>
                                <td class="nota-celda">
                                    <?php 
                                    $es_auto = false;
                                    $val = 0;
                                    if (isset($es_final) && $es_final) {
                                        foreach ($periodos_disponibles as $pd) {
                                            if (trim(mb_strtolower($pd['nombre_periodo'])) === trim(mb_strtolower($item['titulo'])) && $pd['id'] != $periodo_id_actual) {
                                                $es_auto = true;
                                                $val = calcularMediaPeriodoAnterior($alumno['id'], $pd['id'], $conexion);
                                                break;
                                            }
                                        }
                                    }
                                    if (!$es_auto) {
                                        $n = obtenerNotaActual($alumno['id'], $item['id'], $matriz_notas);
                                        $val = ($n === '' || $n === null) ? 0 : $n;
                                    }
                                    // Forzamos 2 decimales en el valor del input
                                    $val_formateado = number_format((float)$val, 2, '.', '');
                                    ?>
                                    <input type="number" 
                                        class="grade-input <?php echo $es_auto ? 'auto-grade' : ''; ?>" 
                                        data-alumno="<?php echo $alumno['id']; ?>" 
                                        data-item="<?php echo $item['id']; ?>" 
                                        data-peso="<?php echo $item['peso']; ?>"
                                        value="<?php echo $val_formateado; ?>" 
                                        step="0.01" min="0" max="10"
                                        <?php echo $es_auto ? 'readonly tabindex="-1"' : ''; ?>>
                                </td>
                            <?php endforeach; ?>
                            <td class="final-grade" id="media-<?php echo $alumno['id']; ?>" style="font-weight: bold; background: #f8fafc; color: #1e293b; text-align: center;">0.00</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof actualizarMedia === 'function') {
                        const alumnosIds = [...new Set([...document.querySelectorAll('.grade-input')].map(i => i.dataset.alumno))];
                        alumnosIds.forEach(id => actualizarMedia(id));
                    }
                });
                </script>
            </div>
        </main>
    </div>

    <div id="modalColumna" class="modal-overlay" style="display: none;">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3>Nueva Métrica de Evaluación</h3>
                <button class="close-btn" onclick="cerrarModalColumna()"><i class="fas fa-times"></i></button>
            </div>
            
            <form id="formNuevaColumna" onsubmit="guardarNuevaColumna(event)">
                <input type="hidden" id="asignaturaActualId" value="<?php echo $asignatura_id; ?>">
                
                <input type="hidden" id="periodoColumna" value="<?php echo $periodo_id_actual; ?>">

                <div class="form-group">
                    <label>Se añadirá en el periodo:</label>
                    <input type="text" value="<?php echo htmlspecialchars($nombre_periodo_actual); ?>" disabled style="width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 10px; outline: none; background-color: #f8f9fa; color: #6c757d;">
                </div>

                <div class="form-group">
                    <label>Nombre de la prueba (Ej: Examen Tema 2)</label>
                    <input type="text" id="nombreColumna" required placeholder="Escribe el nombre...">
                </div>
                
                <div class="form-group">
                    <label>Peso / Porcentaje</label>
                    <input type="number" id="pesoColumna" step="0.05" min="0.05" max="1.00" required placeholder="Ej: 0.20 para 20%">
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="cerrarModalColumna()">Cancelar</button>
                    <button type="submit" class="btn-save">Añadir</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEditarColumna" class="modal-overlay" style="display: none;">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3>Editar Métrica</h3>
                <button class="close-btn" onclick="cerrarModalEditar()"><i class="fas fa-times"></i></button>
            </div>
            
            <form id="formEditarColumna" onsubmit="guardarEdicionColumna(event)">
                <input type="hidden" id="editIdColumna">
                
                <div class="form-group">
                    <label>Nombre de la prueba</label>
                    <input type="text" id="editNombreColumna" required>
                </div>
                
                <div class="form-group">
                    <label>Peso / Porcentaje (Ej: 0.20)</label>
                    <input type="number" id="editPesoColumna" step="0.01" min="0.01" max="1.00" required>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="cerrarModalEditar()">Cancelar</button>
                    <button type="submit" class="btn-save">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalAvatar" class="modal-overlay" style="display: none;">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header">
                <h3>Seleccionar Foto</h3>
                <button class="close-btn" onclick="cerrarModalAvatar()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body text-center">
                <p class="text-muted small mb-3">Elige un icono para el alumno</p>
                <input type="hidden" id="editAvatarAlumnoId">
                <input type="hidden" id="editAvatarSeleccionado" value="">
                <div class="d-flex justify-content-center flex-wrap gap-2" id="lista-iconos-cuaderno">
                    </div>
            </div>
            <div class="modal-footer mt-4">
                <button class="btn btn-secondary" onclick="cerrarModalAvatar()">Cancelar</button>
                <button class="btn btn-primary" onclick="guardarFotoCuaderno()">Guardar Cambios</button>
            </div>
        </div>
    </div>

    <script>
        const iconosDisponibles = [
            'alumna_01.png', 'alumna_02.png', 'alumna_03.png', 'alumna_04.png', 'alumna_05.png', 'alumna_06.png',
            'alumno_01.png', 'alumno_02.png', 'alumno_03.png', 'alumno_04.png'
        ];

        function abrirModalAvatar(alumnoId, fotoActual) {
            document.getElementById('editAvatarAlumnoId').value = alumnoId;
            document.getElementById('editAvatarSeleccionado').value = fotoActual;
            document.getElementById('modalAvatar').style.display = 'flex';
            renderizarIconos('lista-iconos-cuaderno', 'editAvatarSeleccionado');
        }
        function cerrarModalAvatar() { document.getElementById('modalAvatar').style.display = 'none'; }

        function renderizarIconos(contenedorId, inputId) {
            const seleccionado = document.getElementById(inputId).value;
            document.getElementById(contenedorId).innerHTML = iconosDisponibles.map(icono => `
                <img src="../icons/${icono}" 
                     class="avatar-option ${icono === seleccionado ? 'selected' : ''}" 
                     onclick="seleccionarIcono('${icono}', '${contenedorId}', '${inputId}')" alt="Icono">
            `).join('');
        }

        function seleccionarIcono(icono, contenedorId, inputId) {
            document.getElementById(inputId).value = icono;
            renderizarIconos(contenedorId, inputId); // Refresca para pintar el borde azul
        }

        async function guardarFotoCuaderno() {
            const id = document.getElementById('editAvatarAlumnoId').value;
            const foto = document.getElementById('editAvatarSeleccionado').value;
            try {
                const res = await fetch('controllers/actualizar_foto_alumno.php', {
                    method: 'POST', body: JSON.stringify({id: id, foto: foto})
                });
                const data = await res.json();
                if(data.status === 'success') location.reload();
                else alert("Error al guardar: " + data.message);
            } catch(e) { console.error(e); }
        }
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sobrescribimos cualquier función previa para garantizar el cálculo exacto
        window.actualizarMedia = window.calcularMedia = function(alumnoId) {
            const inputs = document.querySelectorAll(`.grade-input[data-alumno="${alumnoId}"]`);
            let notaFinal = 0;

            inputs.forEach(input => {
                // Reemplazamos coma por punto para evitar fallos de cálculo de JS
                const notaStr = (input.value || "0").replace(',', '.');
                const pesoStr = (input.dataset.peso || "0").replace(',', '.');
                
                const nota = parseFloat(notaStr);
                const peso = parseFloat(pesoStr);
                
                // Aquí forzamos la ponderación: Nota multiplicada por su peso %
                if (!isNaN(nota) && !isNaN(peso)) {
                    notaFinal += (nota * peso);
                }
            });

            // Escribimos la nota final con 2 decimales en su respectiva celda
            const celdaMedia = document.getElementById(`media-${alumnoId}`);
            if (celdaMedia) {
                const notaRedondeada = notaFinal.toFixed(2);
                celdaMedia.textContent = notaRedondeada;
                
                // Lógica de colores (Verde si es >= 5, Rojo si es < 5)
                if (parseFloat(notaRedondeada) >= 5.00) {
                    celdaMedia.classList.add('nota-aprobada');
                    celdaMedia.classList.remove('nota-suspensa');
                } else {
                    celdaMedia.classList.add('nota-suspensa');
                    celdaMedia.classList.remove('nota-aprobada');
                }
            }
        };

        // Aplicar los cálculos pasados unos milisegundos para anular el JS antiguo
        setTimeout(() => {
            const inputs = document.querySelectorAll('.grade-input');
            const alumnosIds = [...new Set(Array.from(inputs).map(i => i.dataset.alumno))];
            alumnosIds.forEach(id => window.calcularMedia(id));
        }, 150);
        
        // Y cuando el profe escriba una nota, se vuelve a calcular la media
        document.querySelectorAll('.grade-input').forEach(input => {
            input.addEventListener('input', function() {
                window.calcularMedia(this.dataset.alumno);
            });
        });

        // --- FUNCIÓN PARA EXPORTAR A CSV ---
        window.descargarCSV = function() {
            let csvContent = "data:text/csv;charset=utf-8,";
            const table = document.querySelector('.gradebook-table');
            const rows = table.querySelectorAll('tr');

            rows.forEach((row, index) => {
                let rowData = [];
                
                if (index === 0) { // Fila de Cabeceras
                    const cols = row.querySelectorAll('th');
                    cols.forEach(col => {
                        // Limpiamos el texto (quitamos saltos de línea y el texto de los botones ocultos)
                        let text = col.innerText.replace(/(\r\n|\n|\r)/gm, " ").trim();
                        text = text.replace("Editar Eliminar", "").trim(); 
                        rowData.push('"' + text + '"');
                    });
                } else { // Filas de Alumnos
                    const studentCell = row.querySelector('.student-name');
                    if (studentCell) {
                        rowData.push('"' + studentCell.innerText.trim() + '"'); // Nombre alumno
                        
                        const inputs = row.querySelectorAll('.grade-input');
                        inputs.forEach(input => {
                            rowData.push('"' + input.value + '"'); // Notas de cada columna
                        });
                        
                        const finalGrade = row.querySelector('.final-grade');
                        if (finalGrade) {
                            rowData.push('"' + finalGrade.innerText.trim() + '"'); // Media final
                        }
                    }
                }
                if (rowData.length > 0) {
                    // Separamos por punto y coma (;) para que Excel en español lo abra bien por defecto en columnas
                    csvContent += rowData.join(";") + "\r\n"; 
                }
            });

            // Crear enlace invisible y simular clic para descargar
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "notas_evaluacion.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };
    });
    </script>

    <script src="../js/cuaderno.js"></script>
</body>
</html>