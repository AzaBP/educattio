<?php
require_once __DIR__ . '/controllers/auth_check.php';

// Obtener datos completos del usuario para el sidebar
$usuario_id = $_SESSION['usuario_id'];
try {
    require_once 'conexion.php';
    $sql = "SELECT nombre_completo, nombre_usuario, foto_perfil FROM usuarios WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $usuario_id]);
    $datos_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $datos_usuario = null;
}

$curso_id = $_GET['id'] ?? null;
if (!$curso_id) {
    die("Error: No se ha especificado ningún curso.");
}

// Obtener datos del curso
$stmt = $conexion->prepare("SELECT * FROM cursos WHERE id = :id AND usuario_id = :usuario_id");
$stmt->execute([':id' => $curso_id, ':usuario_id' => $usuario_id]);
$curso = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$curso) {
    die("Error: Curso no encontrado.");
}

// Obtener clases
$stmtClases = $conexion->prepare("SELECT * FROM clases WHERE curso_id = :curso_id");
$stmtClases->execute([':curso_id' => $curso_id]);
$clases = $stmtClases->fetchAll(PDO::FETCH_ASSOC);

// Conteos
$numClases = count($clases);
$stmtAlumnos = $conexion->prepare("SELECT COUNT(*) FROM alumnos a JOIN clases c ON a.clase_id = c.id WHERE c.curso_id = :curso_id");
$stmtAlumnos->execute([':curso_id' => $curso_id]);
$numAlumnos = $stmtAlumnos->fetchColumn();

$stmtEval = $conexion->prepare("SELECT COUNT(*) FROM eventos e JOIN clases c ON e.clase_id = c.id 
                               WHERE c.curso_id = :curso_id AND e.tipo_evento = 'Examen'");
$stmtEval->execute([':curso_id' => $curso_id]);
$numEvaluaciones = $stmtEval->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - <?php echo htmlspecialchars($curso['nombre_centro']); ?></title>
    
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css">
    <link rel="stylesheet" href="../css/detalles_curso.css">
    <link rel="stylesheet" href="../css/calendario.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <header class="course-page-header">
                <div class="header-top-row">
                    <a href="portal_inicio_usuario.php" class="back-link">
                        <i class="fas fa-arrow-left"></i> Volver al inicio
                    </a>
                    <button class="btn-settings" onclick="location.href='ajustes.php'">
                        <i class="fas fa-cog"></i> Ajustes del Curso
                    </button>
                </div>
                <div class="header-content">
                    <h1><?php echo htmlspecialchars($curso['nombre_centro']); ?></h1>
                    <div class="course-badges">
                        <span class="badge year"><?php echo htmlspecialchars($curso['anio_academico']); ?></span>
                        <span class="badge location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($curso['poblacion'] . ', ' . $curso['provincia']); ?></span>
                    </div>
                </div>
            </header>

            <section class="top-info-section">
                <div class="info-card">
                    <h3>Resumen del Centro</h3>
                    <ul class="info-list">
                        <li>
                            <div class="icon-box blue"><i class="fas fa-chalkboard-teacher"></i></div>
                            <div>
                                <strong><?php echo $numClases; ?> Clases</strong>
                                <span>Asignadas a tu perfil</span>
                            </div>
                        </li>
                        <li>
                            <div class="icon-box green"><i class="fas fa-users"></i></div>
                            <div>
                                <strong><?php echo $numAlumnos; ?> Alumnos</strong>
                                <span>Total en tus clases</span>
                            </div>
                        </li>
                        <li>
                            <div class="icon-box red"><i class="fas fa-tasks"></i></div>
                            <div>
                                <strong><?php echo $numEvaluaciones; ?> Evaluaciones</strong>
                                <span>Programadas este año</span>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <div class="overview-card calendar-card course-calendar">
                    <div class="calendar-card-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; gap:0.75rem;">
                        <div>
                            <span class="small-label">Calendario de eventos</span>
                            <h3 style="margin:0; font-size:1rem; color:#111827;">Eventos del curso</h3>
                        </div>
                    </div>
                    <div id="miniCalendarContainer"></div>
                </div>

                <div class="overview-card today-card">
                    <div class="today-label">Hoy</div>
                    <div class="today-day-name" id="current-day-name">--</div>
                    <div class="today-number" id="current-day-number">--</div>
                    <div class="today-month-year" id="current-month-year">--</div>
                    <div class="today-clock" id="real-time-clock">--:--:--</div>
                </div>
            </section>

            <hr class="section-divider">

            <section class="classes-grid-section">
                <div class="section-title-row">
                    <h2>Mis Clases</h2>
                    <p>Selecciona un grupo para ver sus alumnos y notas</p>
                </div>
                
                <div class="classes-grid">
                    <?php foreach ($clases as $clase): ?>
                        <a href="detalles_clase.php?id=<?php echo $clase['id']; ?>" class="premium-card" style="--accent-color: <?php echo $clase['color_clase'] ?? '#3b82f6'; ?>;">
                            <div class="card-banner">
                                <div class="card-icon"><i class="fas <?php echo $clase['icono_clase'] ?? 'fa-users'; ?>"></i></div>
                                <div class="card-badge"><?php echo htmlspecialchars($clase['materia_principal']); ?></div>
                            </div>
                            <div class="card-content">
                                <h3><?php echo htmlspecialchars($clase['nombre_clase']); ?></h3>
                                <p><?php echo htmlspecialchars($clase['materia_principal']); ?></p>
                                <div class="card-footer">
                                    <span>Ver clase</span>
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>

                    <div class="add-card-dashed" onclick="abrirModalNuevaClase()">
                        <div class="add-icon"><i class="fas fa-plus"></i></div>
                        <h3>Añadir Nueva Clase</h3>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <div id="modalClase" class="modal fade" tabindex="-1" aria-labelledby="modalClaseLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                <div class="modal-header" style="border-bottom: none; padding: 25px 0 0 16px;">
                    <h3 class="modal-title fw-bold" id="modalClaseLabel">Añadir Nueva Clase</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formCrearClase" action="guardar_clase.php" method="POST">
                    <input type="hidden" name="curso_id" value="<?php echo $curso_id; ?>">
                    <div class="modal-body" style="padding: 20px 30px;">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-4">
                                <label class="form-label fw-bold">Nombre del grupo</label>
                                <input type="text" name="nombre_clase" class="form-control" placeholder="Ej: 1º ESO A" required>
                            </div>
                            <div class="col-12 col-md-6 mb-4">
                                <label class="form-label fw-bold">Materia principal / Rol</label>
                                <input type="text" name="materia_principal" class="form-control" placeholder="Ej: Matemáticas" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: none; padding: 0 30px 25px;">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Clase</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/mini-calendar.js"></script>
    <script src="../js/detalles_curso.js"></script>
    <input type="hidden" id="cursoIdAsociado" value="<?php echo $curso_id; ?>">
</body>
</html>