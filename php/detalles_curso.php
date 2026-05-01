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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - Detalles del Curso</title>
    
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css">
    <link rel="stylesheet" href="../css/detalles_curso.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <header class="course-page-header">
                <div class="header-top-row">
                    <a href="portal_cursos.php" class="back-link">
                        <i class="fas fa-arrow-left"></i> Volver a mis cursos
                    </a>
                    <button class="btn-settings" onclick="openSettingsModal()">
                        <i class="fas fa-cog"></i> Ajustes del Curso
                    </button>
                </div>
                <div class="header-content">
                    <h1>CEIP CERVANTES</h1>
                    <div class="course-badges">
                        <span class="badge year">2025 - 2026</span>
                        <span class="badge location"><i class="fas fa-map-marker-alt"></i> Pedrola, Zaragoza</span>
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
                
                <div class="overview-card calendar-card">
                    <div class="calendar-header-row">
                        <button class="calendar-nav" id="prevMonth"><i class="fas fa-chevron-left"></i></button>
                        <div class="calendar-title">
                            <span id="bigCalendarMonth">-- --</span>
                        </div>
                        <button class="calendar-nav" id="nextMonth"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <div class="small-calendar-grid" id="calendarGrid">
                        <div class="day-name">Lun</div>
                        <div class="day-name">Mar</div>
                        <div class="day-name">Mié</div>
                        <div class="day-name">Jue</div>
                        <div class="day-name">Vie</div>
                        <div class="day-name">Sáb</div>
                        <div class="day-name">Dom</div>
                    </div>
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
                <div class="groups-grid">
                    <a href="detalles_clase.html" class="group-card">
                        <div class="group-header color-1">
                            <span class="group-icon"><i class="fas fa-book-reader"></i></span>
                            <div class="menu-dots"><i class="fas fa-ellipsis-v"></i></div>
                        </div>
                        <div class="group-body">
                            <h3>4º Primaria A</h3>
                            <p class="subtitle">Tutoría</p>
                        </div>
                    </a>
                    <a href="detalles_clase.html" class="group-card">
                        <div class="group-header color-2">
                            <span class="group-icon"><i class="fas fa-shapes"></i></span>
                            <div class="menu-dots"><i class="fas fa-ellipsis-v"></i></div>
                        </div>
                        <div class="group-body">
                            <h3>1º Primaria B</h3>
                            <p class="subtitle">Matemáticas</p>
                        </div>
                    </a>
                    <a href="detalles_clase.html" class="group-card">
                        <div class="group-header color-3">
                            <span class="group-icon"><i class="fas fa-puzzle-piece"></i></span>
                            <div class="menu-dots"><i class="fas fa-ellipsis-v"></i></div>
                        </div>
                        <div class="group-body">
                            <h3>Aula PT</h3>
                            <p class="subtitle">Pedagogía Terapéutica</p>
                        </div>
                    </a>
                    <div class="add-card-dashed" onclick="openModalClase()">
                        <div class="add-icon">
                            <i class="fas fa-plus"></i>
                        </div>
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
    <script src="../js/detalles_curso.js"></script>
</body>
</html>