<?php
session_start();
include 'conexion.php';

// Verificación de seguridad básica
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../interfaces/inicio_sesion.html");
    exit();
}

// SIMULACIÓN: Asumimos que hemos entrado a la Asignatura 1 (Matemáticas)
$asignatura_id = 1; 

// --- Detectar qué pestaña de evaluación estamos viendo ---
$evaluaciones_disponibles = ['1ª Evaluación', '2ª Evaluación', '3ª Evaluación', 'Final'];
$evaluacion_actual = $_GET['eval'] ?? '1ª Evaluación'; // Por defecto carga la 1ª

// Seguridad extra por si alguien modifica la URL a mano
if (!in_array($evaluacion_actual, $evaluaciones_disponibles)) {
    $evaluacion_actual = '1ª Evaluación';
}

try {
    // 1. OBTENER LAS COLUMNAS SOLO DE LA EVALUACIÓN ACTUAL
    $sql_items = "SELECT id, titulo, peso FROM items_evaluacion 
                  WHERE asignatura_id = :asig_id AND periodo_evaluacion = :periodo";
    $stmt_items = $conexion->prepare($sql_items);
    $stmt_items->execute([
        ':asig_id' => $asignatura_id,
        ':periodo' => $evaluacion_actual
    ]);
    $items_evaluacion = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

    // 2. OBTENER LOS ALUMNOS
    $sql_alumnos = "SELECT id, nombre_alumno AS nombre FROM alumnos 
                    WHERE clase_id = (SELECT clase_id FROM asignaturas WHERE id = :asig_id)";
    $stmt_alumnos = $conexion->prepare($sql_alumnos);
    $stmt_alumnos->execute([':asig_id' => $asignatura_id]);
    $alumnos = $stmt_alumnos->fetchAll(PDO::FETCH_ASSOC);

    // 3. OBTENER TODAS LAS NOTAS
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
?>

<!DOCTYPE html>
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
            <div class="page-header-flex">
                <div>
                    <h1>Cuaderno de Evaluación</h1>
                    <p>Matemáticas - 1º Bachillerato A</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-secondary"><i class="fas fa-file-excel"></i> Exportar</button>
                    <button class="btn btn-primary" onclick="abrirModalColumna()"><i class="fas fa-plus"></i> Añadir Prueba</button>
                </div>
            </div>

            <div class="eval-tabs">
                <?php foreach ($evaluaciones_disponibles as $eval): ?>
                    <a href="?eval=<?php echo urlencode($eval); ?>" 
                       class="eval-tab <?php echo ($eval == $evaluacion_actual) ? 'active' : ''; ?>">
                       <?php echo $eval; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="spreadsheet-container">
                <table class="gradebook-table">
                    <thead>
                        <tr>
                            <th class="sticky-col">Alumno</th>
                            
                            <?php foreach ($items_evaluacion as $item): ?>
                                <th class="col-item">
                                    <div class="header-content" style="display: flex; justify-content: space-between; align-items: center; padding: 0 5px;">
                                        <div>
                                            <div class="col-title"><?php echo htmlspecialchars($item['titulo']); ?></div>
                                            <div class="col-weight"><?php echo floatval($item['peso']); ?>%</div>
                                        </div>
                                        
                                        <div class="header-actions-col" style="display: flex; gap: 5px; flex-direction: column;">
                                            <button type="button" 
                                                    onclick="abrirModalEditar(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['titulo'])); ?>', <?php echo $item['peso']; ?>)" 
                                                    style="background: none; border: none; cursor: pointer; color: #0277bd; font-size: 0.8rem;" title="Editar">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            
                                            <button type="button" 
                                                    onclick="borrarColumna(<?php echo $item['id']; ?>)" 
                                                    style="background: none; border: none; cursor: pointer; color: #e74c3c; font-size: 0.8rem;" title="Borrar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                            
                            <th class="col-final">Media Final</th>
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
                                <td class="sticky-col student-name">
                                    <?php echo htmlspecialchars($alumno['nombre']); ?>
                                </td>
                                
                                <?php foreach ($items_evaluacion as $item): ?>
                                    <td class="nota-celda" style="position:relative;">
                                        <input type="number" 
                                               class="grade-input" 
                                               data-alumno="<?php echo $alumno['id']; ?>" 
                                               data-item="<?php echo $item['id']; ?>" 
                                               data-peso="<?php echo $item['peso']; ?>"
                                               value="<?php echo obtenerNotaActual($alumno['id'], $item['id'], $matriz_notas); ?>" 
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
                
                <div class="form-group">
                    <label>Evaluación</label>
                    <select id="periodoColumna" required style="width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 10px; outline: none;">
                        <option value="1ª Evaluación" <?php echo ($evaluacion_actual == '1ª Evaluación') ? 'selected' : ''; ?>>1ª Evaluación</option>
                        <option value="2ª Evaluación" <?php echo ($evaluacion_actual == '2ª Evaluación') ? 'selected' : ''; ?>>2ª Evaluación</option>
                        <option value="3ª Evaluación" <?php echo ($evaluacion_actual == '3ª Evaluación') ? 'selected' : ''; ?>>3ª Evaluación</option>
                        <option value="Final" <?php echo ($evaluacion_actual == 'Final') ? 'selected' : ''; ?>>Final</option>
                    </select>
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

    <script src="../js/cuaderno.js"></script>
</body>
</html>