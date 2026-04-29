<?php
session_start();
include 'conexion.php';

// Verificación de seguridad básica
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
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

    // Función auxiliar para las medias (Se queda igual)
    function calcularMediaPeriodoAnterior($alumno_id, $periodo_id, $conexion) {
        // 1. Obtener todos los items de ese periodo
        $sql_items = "SELECT id FROM items_evaluacion WHERE periodo_id = :per_id";
        $stmt_items = $conexion->prepare($sql_items);
        $stmt_items->execute([':per_id' => $periodo_id]);
        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        if (empty($items)) return '0';

        $suma = 0;
        $total = 0;

        foreach ($items as $it) {
            // CORRECCIÓN: Tabla 'evaluaciones' y columna 'nota'
            $sql_nota = "SELECT nota FROM evaluaciones WHERE alumno_id = :al_id AND item_id = :it_id";
            $stmt_nota = $conexion->prepare($sql_nota);
            $stmt_nota->execute([':al_id' => $alumno_id, ':it_id' => $it['id']]);
            $n = $stmt_nota->fetch(PDO::FETCH_ASSOC);

            // CORRECCIÓN: Usamos $n['nota']
            $nota_num = ($n && $n['nota'] !== '' && $n['nota'] !== null) ? floatval($n['nota']) : 0;
            $suma += $nota_num;
            $total++;
        }

        return ($total > 0) ? round($suma / $total, 2) : '0';
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
                    <button class="btn btn-secondary"><i class="fas fa-file-excel"></i> Exportar</button>
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
                <table class="gradebook-table">
                    <thead>
                        <tr>
                            <th class="sticky-col">Alumno</th>
                            
                            <?php foreach ($items_evaluacion as $item): ?>
                                <td class="nota-celda" style="position:relative;">
                                    <?php 
                                    $es_columna_automatica = false;
                                    $valor_mostrar = '';
                                    
                                    // 1. Lógica para Evaluación Final
                                    if (isset($es_final) && $es_final) {
                                        foreach ($periodos_disponibles as $pd) {
                                            if ($pd['nombre_periodo'] === $item['titulo'] && $pd['id'] != $periodo_id_actual) {
                                                $es_columna_automatica = true;
                                                // CORRECCIÓN: Usamos $al['id']
                                                $valor_mostrar = calcularMediaPeriodoAnterior($al['id'], $pd['id'], $conexion);
                                                break;
                                            }
                                        }
                                    }
                                    
                                    // 2. Si es una columna normal
                                    if (!$es_columna_automatica) {
                                        // CORRECCIÓN: Usamos $al['id']
                                        $nota = obtenerNotaActual($al['id'], $item['id'], $matriz_notas);
                                        $valor_mostrar = ($nota === '' || $nota === null) ? '0' : $nota;
                                    }
                                    ?>
                                    
                                    <input type="number" 
                                            class="grade-input <?php echo $es_columna_automatica ? 'auto-grade' : ''; ?>" 
                                            data-alumno="<?php echo $al['id']; ?>" 
                                            data-item="<?php echo $item['id']; ?>" 
                                            data-peso="<?php echo $item['peso']; ?>"
                                            value="<?php echo htmlspecialchars($valor_mostrar); ?>" 
                                            step="0.1" min="0" max="10" placeholder="0"
                                            <?php echo $es_columna_automatica ? 'readonly style="background-color: #f1f5f9; cursor: not-allowed; border: 1px dashed #cbd5e1; color: #475569; font-weight: bold;" title="Nota automática (No editable)"' : ''; ?>>
                                    
                                    <?php if (!$es_columna_automatica): ?>
                                        <button class="comentario-btn" 
                                                data-alumno="<?php echo $al['id']; ?>" 
                                                data-item="<?php echo $item['id']; ?>" 
                                                title="Ver/Editar observación"
                                                style="display:none; position:absolute; right:2px; top:2px; background:transparent; border:none; cursor:pointer; color:#888;">
                                            <i class="fas fa-comment-dots"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            
                            <?php
                                // Calcular suma de pesos
                                $suma_pesos = 0;
                                foreach ($items_evaluacion as $item) {
                                    $suma_pesos += floatval($item['peso']);
                                }
                                $suma_pesos_redondeada = round($suma_pesos, 2);
                                $color = ($suma_pesos_redondeada === 1.00) ? 'black' : '#e74c3c';
                                $texto = ($suma_pesos_redondeada === 1.00) ? ' (100%)' : ' ('.($suma_pesos_redondeada*100).'%)';
                            ?>
                            <th class="col-final">
                                Media Final<span style="color: <?php echo $color; ?>; font-weight:600;"><?php echo $texto; ?></span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($alumnos)): ?>
                            <tr>
                                <td colspan="100%" style="text-align: center; padding: 20px;">
                                    No hay alumnos matriculados en esta clase.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($alumnos as $alumno): ?>
                            <tr>
                                <td class="sticky-col student-cell">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div class="student-avatar cursor-pointer" onclick="abrirModalAvatar(<?php echo $alumno['id']; ?>, '<?php echo $alumno['foto'] ?? ''; ?>')" title="Cambiar foto de <?php echo htmlspecialchars($alumno['nombre_alumno']); ?>">
                                            <?php if (!empty($alumno['foto'])): ?>
                                                <img src="../icons/<?php echo htmlspecialchars($alumno['foto']); ?>" 
                                                    alt="Foto"
                                                    style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                                            <?php else: ?>
                                                <?php 
                                                    $nombres = explode(' ', $alumno['nombre_alumno']);
                                                    $iniciales = "";
                                                    foreach($nombres as $n) { if(!empty($n)) $iniciales .= strtoupper($n[0]); }
                                                    echo substr($iniciales, 0, 2); 
                                                ?>
                                            <?php endif; ?>
                                        </div>
                                        <span class="student-name" style="white-space:nowrap; font-size:1rem; color:#222;"><?php echo htmlspecialchars($alumno['nombre_alumno']); ?></span>
                                    </div>
                                </td>
                                
                                <?php foreach ($items_evaluacion as $item): ?>
                                    <td class="nota-celda" style="position:relative;">
                                        <input type="number" 
                                               class="grade-input" 
                                               data-alumno="<?php echo $alumno['id']; ?>" 
                                               data-item="<?php echo $item['id']; ?>" 
                                               data-peso="<?php echo $item['peso']; ?>"
                                               value="<?php $nota = obtenerNotaActual($alumno['id'], $item['id'], $matriz_notas); echo ($nota === '' || $nota === null) ? '0' : $nota; ?>" 
                                               step="0.1" min="0" max="10" placeholder="-">
                                        <button class="comentario-btn" 
                                                data-alumno="<?php echo $alumno['id']; ?>" 
                                                data-item="<?php echo $item['id']; ?>" 
                                                title="Ver/Editar observación"
                                                style="display:none; position:absolute; right:2px; top:2px; background:transparent; border:none; cursor:pointer; color:#888;">
                                            <i class="fas fa-comment-dots"></i>
                                        </button>
                                    </td>
                                <?php endforeach; ?>
                                    <!-- Modal de comentario -->
                                    <div id="modalComentario" class="modal-overlay" style="display:none;">
                                        <div class="modal-content" style="max-width:400px;">
                                            <div class="modal-header">
                                                <h3>Observación</h3>
                                                <button class="close-btn" onclick="cerrarModalComentario()"><i class="fas fa-times"></i></button>
                                            </div>
                                            <form id="formComentario" onsubmit="guardarComentario(event)">
                                                <input type="hidden" id="comentarioAlumnoId">
                                                <input type="hidden" id="comentarioItemId">
                                                <div class="form-group">
                                                    <label for="comentarioTexto">Observación para este alumno y prueba:</label>
                                                    <textarea id="comentarioTexto" rows="4" style="width:100%;"></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn-cancel" onclick="cerrarModalComentario()">Cancelar</button>
                                                    <button type="submit" class="btn-save">Guardar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                
                                <td class="final-grade" id="media-<?php echo $alumno['id']; ?>">0.00</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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

    <script src="../js/cuaderno.js"></script>
</body>
</html>