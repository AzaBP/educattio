<?php
require_once __DIR__ . '/controllers/auth_check.php';
require_once 'conexion.php';

$usuario_id = $_SESSION['usuario_id'];
$curso_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($curso_id <= 0) {
    header('Location: portal_cursos.php');
    exit();
}

try {
    // 1. Datos del usuario
    $sql = "SELECT nombre_completo, nombre_usuario, foto_perfil FROM usuarios WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $usuario_id]);
    $datos_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    $nombre_usuario = $datos_usuario['nombre_completo'] ?: ($datos_usuario['nombre_usuario'] ?: 'Usuario');

    // 2. Datos del curso
    $sql_curso = "SELECT * FROM cursos WHERE id = :curso_id AND id_usuario = :usuario_id";
    $stmt_curso = $conexion->prepare($sql_curso);
    $stmt_curso->execute([':curso_id' => $curso_id, ':usuario_id' => $usuario_id]);
    $curso = $stmt_curso->fetch(PDO::FETCH_ASSOC);

    if (!$curso) {
        header('Location: portal_cursos.php');
        exit();
    }
} catch (PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
}
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
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css?v=1.3">
    <link rel="stylesheet" href="../css/detalles_curso.css?v=1.1">
    <link rel="stylesheet" href="../css/calendario.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <header class="course-header-modern">
                <div class="header-background" style="background: linear-gradient(135deg, <?php echo $curso['color'] ?: '#ff7a59'; ?> 0%, rgba(255,255,255,0.1) 100%);"></div>
                <div class="header-top-row">
                    <a href="portal_cursos.php" class="back-pill">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <button class="settings-pill" onclick="openSettingsModal()">
                        <i class="fas fa-cog"></i> Ajustes
                    </button>
                </div>
                <div class="header-main-content">
                    <div class="course-title-group">
                        <span class="center-type">Centro Educativo</span>
                        <h1><?php echo htmlspecialchars($curso['nombre_centro']); ?></h1>
                    </div>
                    <div class="course-meta-pills">
                        <span class="meta-pill"><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($curso['anio_academico']); ?></span>
                        <span class="meta-pill"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($curso['poblacion'] . ', ' . $curso['provincia']); ?></span>
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
                                <strong id="numClases">0 Clases</strong>
                                <span>Asignadas a tu perfil</span>
                            </div>
                        </li>
                        <li>
                            <div class="icon-box green"><i class="fas fa-users"></i></div>
                            <div>
                                <strong id="numAlumnos">0 Alumnos</strong>
                                <span>Total en tus clases</span>
                            </div>
                        </li>
                        <li>
                            <div class="icon-box red"><i class="fas fa-tasks"></i></div>
                            <div>
                                <strong id="numEvaluaciones">0 Evaluaciones</strong>
                                <span>Programadas este año</span>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <div class="overview-card calendar-card course-calendar">
                    <div class="calendar-card-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; gap:0.75rem;">
                        <div>
                            <span class="small-label">Calendario de eventos</span>
                            <h3 style="margin:0; font-size:1rem; color:#111827;">Mini calendario del curso</h3>
                        </div>
                        <button class="btn-add-class" onclick="abrirMiniEventoCurso()">
                            <i class="fas fa-plus"></i> Añadir evento
                        </button>
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
                    <p>Gestiona los grupos y materias de este centro</p>
                </div>
                <div class="groups-grid">
                    <!-- Las clases se cargarán dinámicamente aquí -->
                    <div class="loading-placeholder">Cargando clases...</div>
                </div>
            </section>
        </main>
    </div>

    <!-- MODAL AJUSTES DEL CURSO -->
    <div id="modalAjustes" class="modal-overlay">
        <div class="modal-window">
            <div class="modal-header">
                <h3>Ajustes del Curso</h3>
                <button class="close-btn" onclick="closeSettingsModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="formAjustesCurso" class="modal-form">
                <input type="hidden" id="ajustesCursoId" value="<?php echo $curso_id; ?>">
                
                <div class="form-group">
                    <label for="ajustesNombreCentro">Nombre del Centro</label>
                    <input type="text" id="ajustesNombreCentro" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ajustesAnio">Año Académico</label>
                        <input type="text" id="ajustesAnio" required>
                    </div>
                    <div class="form-group">
                        <label for="ajustesColor">Color Distintivo</label>
                        <input type="color" id="ajustesColor" style="height: 45px;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ajustesPoblacion">Población</label>
                        <input type="text" id="ajustesPoblacion" required>
                    </div>
                    <div class="form-group">
                        <label for="ajustesProvincia">Provincia</label>
                        <input type="text" id="ajustesProvincia" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-danger-outline" onclick="eliminarCurso()">Eliminar Curso</button>
                    <div class="footer-right">
                        <button type="button" class="btn-cancel" onclick="closeSettingsModal()">Cancelar</button>
                        <button type="submit" class="btn-save">Guardar Cambios</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div id="modalClase" class="modal fade" tabindex="-1" aria-labelledby="modalClaseLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                <div class="modal-header" style="border-bottom: none; padding: 25px 0 0 16px;">
                    <h3 class="modal-title fw-bold" id="modalClaseLabel">Añadir Nueva Clase</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>


                <form id="formCrearClase">
                    <input type="hidden" id="cursoIdAsociado" value="<?php echo isset($_GET['id']) ? htmlspecialchars($_GET['id']) : ''; ?>">
                    <input type="hidden" id="inputIdClase">
                    <input type="hidden" id="inputColorClase" value="#3b82f6">
                    <input type="hidden" id="inputIconoClase" value="fa-users">

                    <div class="row">
                        <div class="col-12 col-md-6 mb-4" style="position: relative;">
                            <label class="form-label fw-bold" style="position: static !important; transform: none !important; color: #4b5563; font-size: 0.9rem; display: block; margin-bottom: 8px; pointer-events: auto;">Nombre del grupo</label>
                            <div class="d-flex align-items-center" style="border: 1px solid #d1d5db; border-radius: 12px; padding: 5px 10px; background: #fff;">
                                <div style="background-color: #f3f4f6; border-radius: 8px; min-width: 38px; height: 38px; display: flex; justify-content: center; align-items: center; margin-right: 12px;">
                                    <i class="fas fa-users" style="color: #6b7280; font-size: 1.1rem;"></i>
                                </div>
                                <input type="text" id="inputNombreClase" class="form-control" placeholder="Ej: 1º ESO A" style="border: none !important; box-shadow: none !important; padding: 0 !important; background: transparent !important; width: 100%; font-size: 0.95rem; height: auto !important; margin: 0 !important;">
                            </div>
                        </div>
                        <div class="col-12 col-md-6 mb-4" style="position: relative;">
                            <label class="form-label fw-bold" style="position: static !important; transform: none !important; color: #4b5563; font-size: 0.9rem; display: block; margin-bottom: 8px; pointer-events: auto;">Materia principal / Rol</label>
                            <div class="d-flex align-items-center" style="border: 1px solid #d1d5db; border-radius: 12px; padding: 5px 10px; background: #fff;">
                                <div style="background-color: #f3f4f6; border-radius: 8px; min-width: 38px; height: 38px; display: flex; justify-content: center; align-items: center; margin-right: 12px;">
                                    <i class="fas fa-book" style="color: #6b7280; font-size: 1.1rem;"></i>
                                </div>
                                <input type="text" id="inputMateria" class="form-control" placeholder="Ej: Matemáticas" style="border: none !important; box-shadow: none !important; padding: 0 !important; background: transparent !important; width: 100%; font-size: 0.95rem; height: auto !important; margin: 0 !important;">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-end">
                        <div class="col-12 d-flex flex-row gap-4">
                            <div style="flex:1; min-width: 0;">
                                <label class="form-label fw-bold" style="position: static !important; transform: none !important; color: #4b5563; font-size: 0.9rem; display: block; margin-bottom: 8px;">Color</label>
                                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; justify-items: center;" id="contenedor-colores">
                                    <div class="color-opcion seleccionable active" data-color="#3b82f6" style="background-color: #3b82f6;"><i class="fas fa-check"></i></div>
                                    <div class="color-opcion seleccionable" data-color="#ef4444" style="background-color: #ef4444;"><i class="fas fa-check"></i></div>
                                    <div class="color-opcion seleccionable" data-color="#10b981" style="background-color: #10b981;"><i class="fas fa-check"></i></div>
                                    <div class="color-opcion seleccionable" data-color="#f59e0b" style="background-color: #f59e0b;"><i class="fas fa-check"></i></div>
                                    <div class="color-opcion seleccionable" data-color="#8b5cf6" style="background-color: #8b5cf6;"><i class="fas fa-check"></i></div>
                                    <div class="color-opcion seleccionable" data-color="#ec4899" style="background-color: #ec4899;"><i class="fas fa-check"></i></div>
                                    <div class="color-opcion seleccionable" data-color="#47b39d" style="background-color: #47b39d;"><i class="fas fa-check"></i></div>
                                    <input type="color" class="color-opcion color-input-visible" id="input-color-personalizado" value="#000000" title="Color personalizado" style="background: none;">
                                </div>
                            </div>
                            <div style="flex:2; min-width: 0;">
                                <label class="form-label fw-bold" style="position: static !important; transform: none !important; color: #4b5563; font-size: 0.9rem; display: block; margin-bottom: 8px; padding-left: 42px;">Icono</label>
                                <div style="display: grid; grid-template-columns: repeat(4, 1fr); column-gap: 0px; row-gap: 10px; justify-items: center; padding-left: 32px;" id="contenedor-iconos">
                                    <div class="icono-opcion seleccionable active" data-icon="fa-users"><i class="fas fa-users"></i></div>
                                    <div class="icono-opcion seleccionable" data-icon="fa-book"><i class="fas fa-book"></i></div>
                                    <div class="icono-opcion seleccionable" data-icon="fa-flask"><i class="fas fa-flask"></i></div>
                                    <div class="icono-opcion seleccionable" data-icon="fa-laptop-code"><i class="fas fa-laptop-code"></i></div>
                                    <div class="icono-opcion seleccionable" data-icon="fa-globe"><i class="fas fa-globe"></i></div>
                                    <div class="icono-opcion seleccionable" data-icon="fa-calculator"><i class="fas fa-calculator"></i></div>
                                    <div class="icono-opcion seleccionable" data-icon="fa-palette"><i class="fas fa-palette"></i></div>
                                    <div class="icono-opcion seleccionable" data-icon="fa-music"><i class="fas fa-music"></i></div>
                                    <div class="icono-opcion seleccionable" data-icon="fa-basketball-ball"><i class="fas fa-basketball-ball"></i></div>
                                    <div class="icono-opcion seleccionable" data-icon="fa-language"><i class="fas fa-language"></i></div>
                                    <div class="icono-opcion seleccionable" data-icon="fa-atom"><i class="fas fa-atom"></i></div>
                                    <div class="icono-opcion seleccionable" data-icon="fa-chart-line"><i class="fas fa-chart-line"></i></div>
                                    <div class="icono-opcion seleccionable" data-icon="fa-history"><i class="fas fa-history"></i></div>
                                    <div class="icono-opcion seleccionable" data-icon="fa-leaf"><i class="fas fa-leaf"></i></div>
                                    <div class="icono-opcion seleccionable" data-icon="fa-heartbeat"><i class="fas fa-heartbeat"></i></div>
                                    <div class="icono-opcion seleccionable" data-icon="fa-comments"><i class="fas fa-comments"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="modal-footer" style="border-top: none; padding: 0 30px 25px;">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formCrearClase" class="btn-save">Guardar</button>
                </div>

            </div>
        </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/calendar-sync.js"></script>
    <script src="../js/mini-calendar.js"></script>
    <script src="../js/detalles_curso.js"></script>
</body>
</html>

<style>
.btn-danger-outline {
    background: transparent;
    border: 1px solid #ef4444;
    color: #ef4444;
    padding: 10px 20px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-danger-outline:hover {
    background: #ef4444;
    color: white;
}

.modal-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.footer-right {
    display: flex;
    gap: 12px;
}
</style>