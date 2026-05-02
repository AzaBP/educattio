<?php
// ==========================================================
// detalles_asignatura.php - Página dinámica de asignatura
// ==========================================================
require_once __DIR__ . '/controllers/auth_check.php';
require_once 'conexion.php';

// ----------------------------------------------------------
// 1. Obtener y validar el ID de la asignatura
// ----------------------------------------------------------
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: portal_inicio_usuario.php");
    exit();
}
$asignatura_id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// ----------------------------------------------------------
// 2. Cargar datos de la asignatura, clase, curso y permisos
// ----------------------------------------------------------
try {
    // Verificar que la asignatura pertenece al usuario (a través de curso -> usuario_id)
    $sql = "SELECT a.*, c.nombre_clase, c.curso_id, cu.nombre_centro, cu.anio_academico, cu.poblacion, cu.provincia
            FROM asignaturas a
            JOIN clases c ON a.clase_id = c.id
            JOIN cursos cu ON c.curso_id = cu.id
            WHERE a.id = :asignatura_id AND cu.usuario_id = :usuario_id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':asignatura_id' => $asignatura_id, ':usuario_id' => $usuario_id]);
    $asignatura = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$asignatura) {
        die("No tienes permiso para ver esta asignatura o no existe.");
    }
    
    $curso_id = $asignatura['curso_id'];
    $clase_id = $asignatura['clase_id'];
} catch (PDOException $e) {
    die("Error al cargar la asignatura: " . $e->getMessage());
}

// ----------------------------------------------------------
// 3. Manejo de acciones POST (añadir tema, periodos, editar/eliminar)
// ----------------------------------------------------------
// Auto-migración para añadir columna documento si no existe
try {
    $conexion->exec("ALTER TABLE temas_asignatura ADD COLUMN documento VARCHAR(255) NULL");
} catch (PDOException $e) {
    // Ignorar si la columna ya existe
}
// 3.1 Añadir tema
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_tema') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    if (!empty($titulo)) {
        // Obtener el último orden + 1
        $stmt = $conexion->prepare("SELECT MAX(orden) FROM temas_asignatura WHERE asignatura_id = ?");
        $stmt->execute([$asignatura_id]);
        $maxOrden = (int)$stmt->fetchColumn();
        $nuevoOrden = $maxOrden + 1;
        
        $documento_path = null;
        if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/temarios/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $filename = time() . '_' . basename($_FILES['documento']['name']);
            $target_file = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['documento']['tmp_name'], $target_file)) {
                $documento_path = $filename;
            }
        }
        
        $stmt = $conexion->prepare("INSERT INTO temas_asignatura (asignatura_id, titulo, descripcion, orden, documento) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$asignatura_id, $titulo, $descripcion, $nuevoOrden, $documento_path]);
    }
    header("Location: detalles_asignatura.php?id=" . $asignatura_id);
    exit();
}

// 3.2 Añadir periodos (varios separados por comas)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_periodos') {
    $nombres = explode(',', $_POST['nombres'] ?? '');
    $nombres = array_map('trim', $nombres);
    $nombres = array_filter($nombres);
    if (!empty($nombres)) {
        // Eliminar periodos existentes (si quieres reemplazar, o evitar duplicados)
        // Opcional: borrar todos y volver a crear
        $stmtDel = $conexion->prepare("DELETE FROM periodos_evaluacion WHERE asignatura_id = ?");
        $stmtDel->execute([$asignatura_id]);
        
        $stmtIns = $conexion->prepare("INSERT INTO periodos_evaluacion (nombre_periodo, asignatura_id) VALUES (?, ?)");
        foreach ($nombres as $nombre) {
            if (!empty($nombre)) {
                $stmtIns->execute([$nombre, $asignatura_id]);
            }
        }
    }
    header("Location: detalles_asignatura.php?id=" . $asignatura_id);
    exit();
}

// 3.3 Editar nombre de la asignatura (desde modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_asignatura') {
    $nuevoNombre = trim($_POST['nombre_asignatura'] ?? '');
    if (!empty($nuevoNombre)) {
        $stmt = $conexion->prepare("UPDATE asignaturas SET nombre_asignatura = ? WHERE id = ?");
        $stmt->execute([$nuevoNombre, $asignatura_id]);
    }
    header("Location: detalles_asignatura.php?id=" . $asignatura_id);
    exit();
}

// 3.4 Eliminar asignatura completa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_asignatura') {
    // Primero eliminar dependencias (alumnos_asignaturas, periodos, temas, items_evaluacion, evaluaciones...)
    // La BD con ON DELETE CASCADE ya lo hará automáticamente si las FK están bien definidas.
    $stmt = $conexion->prepare("DELETE FROM asignaturas WHERE id = ?");
    $stmt->execute([$asignatura_id]);
    header("Location: detalles_curso.php?id=" . $curso_id);
    exit();
}

// ----------------------------------------------------------
// 4. Cargar datos para mostrar en la página
// ----------------------------------------------------------
// 4.1 Alumnos matriculados en esta asignatura
try {
    $sqlAlumnos = "SELECT a.id, a.nombre_alumno 
                   FROM alumnos a 
                   JOIN alumnos_asignaturas aa ON a.id = aa.alumno_id 
                   WHERE aa.asignatura_id = :asignatura_id
                   ORDER BY a.nombre_alumno";
    $stmtAlumnos = $conexion->prepare($sqlAlumnos);
    $stmtAlumnos->execute([':asignatura_id' => $asignatura_id]);
    $alumnos = $stmtAlumnos->fetchAll(PDO::FETCH_ASSOC);
    $totalAlumnos = count($alumnos);
} catch (PDOException $e) {
    $alumnos = [];
    $totalAlumnos = 0;
}

// 4.2 Periodos de evaluación
try {
    $sqlPeriodos = "SELECT * FROM periodos_evaluacion WHERE asignatura_id = :asignatura_id ORDER BY id";
    $stmtPeriodos = $conexion->prepare($sqlPeriodos);
    $stmtPeriodos->execute([':asignatura_id' => $asignatura_id]);
    $periodos = $stmtPeriodos->fetchAll(PDO::FETCH_ASSOC);
    $totalPeriodos = count($periodos);
} catch (PDOException $e) {
    $periodos = [];
    $totalPeriodos = 0;
}

// 4.3 Eventos próximos (para la clase o asignatura? Usaremos eventos de la clase)
try {
    $sqlEventos = "SELECT * FROM eventos 
                   WHERE asignatura_id = :asignatura_id 
                   ORDER BY fecha ASC";
    $stmtEventos = $conexion->prepare($sqlEventos);
    $stmtEventos->execute([':asignatura_id' => $asignatura_id]);
    $eventos = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);
    $totalEventos = count($eventos);
} catch (PDOException $e) {
    $eventos = [];
    $totalEventos = 0;
}

// 4.4 Temario (temas)
try {
    $sqlTemas = "SELECT * FROM temas_asignatura WHERE asignatura_id = :asignatura_id ORDER BY orden ASC";
    $stmtTemas = $conexion->prepare($sqlTemas);
    $stmtTemas->execute([':asignatura_id' => $asignatura_id]);
    $temas = $stmtTemas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $temas = [];
}

// ----------------------------------------------------------
// 5. Obtener datos del usuario para el sidebar (avatar, etc.)
// ----------------------------------------------------------
try {
    $sqlUser = "SELECT nombre_completo, nombre_usuario, foto_perfil FROM usuarios WHERE id = :id";
    $stmtUser = $conexion->prepare($sqlUser);
    $stmtUser->execute([':id' => $usuario_id]);
    $datos_usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $datos_usuario = null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($asignatura['nombre_asignatura']) ?> - Educattio</title>
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/detalles_asignatura.css">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css">
    <link rel="stylesheet" href="../css/calendario.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>
    <main class="main-content subject-detail-page">
        <header class="subject-header">
            <div class="subject-title-row">
                <a id="backToClassLink" href="detalles_curso.php?id=<?= $curso_id ?>" class="back-link">
                    <i class="fas fa-arrow-left"></i> Volver a <?= htmlspecialchars($asignatura['nombre_clase']) ?>
                </a>
                <div>
                    <button class="btn-settings" onclick="openSettingsModal()">
                        <i class="fas fa-cog"></i> Ajustes de Asignatura
                    </button>
                </div>
            </div>
            <div class="subject-top">
                <div>
                    <h1 id="asignaturaNombre"><?= htmlspecialchars($asignatura['nombre_asignatura']) ?></h1>
                    <p class="subject-meta" id="asignaturaMeta">
                        <?= htmlspecialchars($asignatura['anio_academico']) ?> · 
                        <?= htmlspecialchars($asignatura['nombre_clase']) ?> · 
                        <?= htmlspecialchars($asignatura['nombre_centro']) ?>
                    </p>
                </div>
            </div>
        </header>

        <section class="subject-summary-grid">
            <article class="summary-card blue">
                <h4>Alumnos</h4>
                <strong id="summaryAlumnos"><?= $totalAlumnos ?></strong>
                <p>Matriculados en esta clase</p>
            </article>
            <article class="summary-card green">
                <h4>Periodos</h4>
                <strong id="summaryPeriodos"><?= $totalPeriodos ?></strong>
                <p>Periodos de evaluación definidos</p>
            </article>
            <article class="summary-card orange">
                <h4>Eventos</h4>
                <strong id="summaryEventos"><?= $totalEventos ?></strong>
                <p>Próximos eventos relacionados</p>
            </article>
        </section>

        <section class="subject-detail-grid">
            <!-- Bloque de Eventos -->
            <div class="box box-events">
                <div class="box-header">
                    <div>
                        <h2>Calendario & Eventos</h2>
                        <p>Eventos próximos para esta clase</p>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="abrirMiniEventoAsignatura()">
                        <i class="fas fa-plus"></i> Añadir evento
                    </button>
                </div>
                <!-- Mini-calendario para crear eventos -->
                <div
                    id="miniCalendarAsignaturaContainer"
                    class="mini-calendar-container mb-4"
                    data-asignatura-id="<?= (int)$asignatura_id ?>"
                    data-clase-id="<?= (int)$clase_id ?>"
                ></div>
                <div id="eventosList" class="events-list">
                    <?php if (empty($eventos)): ?>
                        <div class="empty-state" id="eventosEmpty">No hay eventos programados para esta asignatura.</div>
                    <?php else: ?>
                        <?php foreach ($eventos as $evento): ?>
                            <div class="mini-event-item">
                                <div>
                                    <div class="mini-event-title"><?= htmlspecialchars($evento['titulo']) ?></div>
                                    <small style="color:#6b7280;"><?= date('d/m/Y H:i', strtotime($evento['fecha'])) ?></small>
                                    <p style="margin:4px 0 0; font-size:0.8rem; color:#4b5563;"><?= htmlspecialchars($evento['descripcion'] ?? '') ?></p>
                                </div>
                                <span class="mini-event-type <?= strtolower($evento['tipo_evento'] ?? '') ?>"><?= htmlspecialchars($evento['tipo_evento'] ?? '') ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bloque Temario -->
            <div class="box box-syllabus">
                <div class="box-header">
                    <div>
                        <h2>Temario</h2>
                        <p>Organiza los contenidos y objetivos de la asignatura.</p>
                    </div>
                    <button class="btn btn-outline-primary" onclick="toggleSection('nuevoTemaSection')">
                        <i class="fas fa-plus"></i> Añadir tema
                    </button>
                </div>
                <div id="temasContainer" class="temas-list">
                    <?php if (empty($temas)): ?>
                        <div class="empty-state" id="temasEmpty">Añade los primeros temas para compartir el temario.</div>
                    <?php else: ?>
                        <?php foreach ($temas as $tema): ?>
                            <div class="tema-card">
                                <h4><?= htmlspecialchars($tema['titulo']) ?></h4>
                                <p><?= nl2br(htmlspecialchars($tema['descripcion'])) ?></p>
                                <?php if (!empty($tema['documento'])): ?>
                                    <div class="mt-2">
                                        <a href="../uploads/temarios/<?= htmlspecialchars($tema['documento']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-paperclip"></i> Ver documento
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div id="nuevoTemaSection" class="new-item-card hidden">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_tema">
                        <div class="mb-3">
                            <label class="form-label">Título del tema</label>
                            <input type="text" name="titulo" class="form-control" placeholder="Ej: Ecosistemas" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3" placeholder="Describe el contenido del tema"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Documento adjunto (Opcional)</label>
                            <input type="file" name="documento" class="form-control">
                            <small class="form-text text-muted">Puedes subir archivos PDF, Word, Excel, imágenes, etc.</small>
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-outline-secondary" onclick="toggleSection('nuevoTemaSection')">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar tema</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bloque Evaluaciones -->
            <div class="box box-evaluation">
                <div class="box-header">
                    <div>
                        <h2>Evaluaciones</h2>
                        <p>Gestiona los periodos y accede al cuaderno.</p>
                    </div>
                </div>
                <div class="evaluation-panel">
                    <div class="evaluation-card">
                        <h3>Ir al cuaderno</h3>
                        <p>Usa el cuaderno de evaluación para registrar notas y organizar items.</p>
                        <?php if (empty($periodos)): ?>
                            <button id="abrirCuadernoBtn" class="btn btn-primary" onclick="alert('Crea al menos un periodo de evaluación para usar el cuaderno.')">
                                <i class="fas fa-book-open"></i> Abrir cuaderno
                            </button>
                        <?php else: ?>
                            <a id="abrirCuadernoBtn" class="btn btn-primary" href="cuaderno_evaluacion.php?asig_id=<?= $asignatura_id ?>&periodo_id=<?= $periodos[0]['id'] ?>">
                                <i class="fas fa-book-open"></i> Abrir cuaderno
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="periodos-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3>Periodos</h3>
                            <button class="btn btn-sm btn-outline-primary" onclick="toggleSection('nuevoPeriodoSection')">
                                <i class="fas fa-plus"></i> Añadir periodo
                            </button>
                        </div>
                        <div id="periodosList">
                            <?php if (empty($periodos)): ?>
                                <div id="periodosEmpty" class="empty-state">Crea periodos para estructurar las evaluaciones.</div>
                            <?php else: ?>
                                <ul class="list-group">
                                    <?php foreach ($periodos as $periodo): ?>
                                        <li class="list-group-item"><?= htmlspecialchars($periodo['nombre_periodo']) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div id="nuevoPeriodoSection" class="new-item-card hidden">
                            <form method="POST">
                                <input type="hidden" name="action" value="add_periodos">
                                <div class="mb-3">
                                    <label class="form-label">Nombres de periodos</label>
                                    <input type="text" name="nombres" class="form-control" placeholder="Ej: 1º Trimestre, 2º Trimestre" required>
                                    <small class="form-text text-muted">Sepáralos con comas para crear varios a la vez.</small>
                                </div>
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="button" class="btn btn-outline-secondary" onclick="toggleSection('nuevoPeriodoSection')">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Guardar periodos</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- Modal de Ajustes (editar nombre / eliminar) -->
<div id="settingsModal" class="modal-overlay" style="display: none;">
    <div class="modal-window">
        <div class="modal-header">
            <h3>Ajustes de la asignatura</h3>
            <button class="close-btn" onclick="closeSettingsModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit_asignatura">
            <div class="form-group mb-3">
                <label class="form-label">Nombre de la asignatura</label>
                <input type="text" name="nombre_asignatura" class="form-control" value="<?= htmlspecialchars($asignatura['nombre_asignatura']) ?>" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="closeSettingsModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
        <hr>
        <div class="danger-zone-modal">
            <div class="danger-info">
                <h4><i class="fas fa-exclamation-triangle"></i> Zona peligrosa</h4>
                <p>Esta acción eliminará la asignatura, todos sus temas, periodos, items de evaluación y notas asociadas. No se puede deshacer.</p>
            </div>
            <form method="POST" onsubmit="return confirm('¿Estás completamente seguro? Se perderán todos los datos de esta asignatura.');">
                <input type="hidden" name="action" value="delete_asignatura">
                <button type="submit" class="btn-delete-course">
                    <i class="fas fa-trash-alt"></i> Eliminar asignatura
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// Funciones simples para mostrar/ocultar secciones y modal
function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section.classList.contains('hidden')) {
        section.classList.remove('hidden');
    } else {
        section.classList.add('hidden');
    }
}

function openSettingsModal() {
    document.getElementById('settingsModal').style.display = 'flex';
}
function closeSettingsModal() {
    document.getElementById('settingsModal').style.display = 'none';
}
// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('settingsModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>

<style>
.hidden { display: none; }
.event-item {
    display: flex;
    gap: 15px;
    padding: 12px;
    border-bottom: 1px solid #eee;
}
.event-date {
    text-align: center;
    min-width: 55px;
}
.event-day {
    font-size: 1.5rem;
    font-weight: bold;
    display: block;
}
.event-month {
    text-transform: uppercase;
    font-size: 0.7rem;
}
.tema-card {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 12px;
}
.tema-card h4 {
    margin-bottom: 8px;
}
.new-item-card {
    margin-top: 20px;
    padding: 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 16px;
}
.modal-overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    justify-content: center;
    align-items: center;
    z-index: 2000;
}
.modal-window {
    background: white;
    border-radius: 24px;
    max-width: 550px;
    width: 90%;
    padding: 24px;
    box-shadow: 0 20px 35px rgba(0,0,0,0.2);
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.close-btn {
    background: none;
    border: none;
    font-size: 1.4rem;
    cursor: pointer;
}
.danger-zone-modal {
    margin-top: 24px;
    padding: 16px;
    background: #fff5f5;
    border-radius: 16px;
    border: 1px solid #ffcccc;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}
.btn-delete-course {
    background: white;
    border: 1px solid #d32f2f;
    color: #d32f2f;
    padding: 8px 20px;
    border-radius: 30px;
    font-weight: 600;
    transition: 0.2s;
}
.btn-delete-course:hover {
    background: #d32f2f;
    color: white;
}
@media (max-width: 700px) {
    .danger-zone-modal { flex-direction: column; align-items: flex-start; }
    .btn-delete-course { width: 100%; text-align: center; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/calendar-sync.js?v=1.3"></script>
<script src="../js/mini-calendar.js?v=1.3"></script>
<script src="../js/detalles_asignatura.js?v=1.3"></script>
</body>
</html>