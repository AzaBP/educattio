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
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css?v=1.7">
    <link rel="stylesheet" href="../css/detalles_curso.css?v=1.7">
    <link rel="stylesheet" href="../css/calendario.css?v=1.7">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <!-- CABECERA PREMIUM -->
            <header class="course-page-header-modern">
                <div class="header-glass-overlay"></div>
                <div class="header-main-content">
                    <div class="header-left">
                        <a href="portal_inicio_usuario.php" class="back-pill">
                            <i class="fas fa-chevron-left"></i> Panel de Control
                        </a>
                        <h1 class="course-title-animate"><?php echo htmlspecialchars($curso['nombre_centro']); ?></h1>
                        <div class="header-badges-row">
                            <span class="modern-badge"><i class="far fa-calendar-alt"></i> <?php echo htmlspecialchars($curso['anio_academico']); ?></span>
                            <span class="modern-badge"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($curso['poblacion']); ?></span>
                        </div>
                    </div>
                    <div class="header-right">
                        <button class="settings-glass-btn" onclick="new bootstrap.Modal(document.getElementById('modalAjustesCurso')).show()">
                            <i class="fas fa-sliders-h"></i> Ajustes del Curso
                        </button>
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
                    <h2 class="serif-title">Mis Grupos</h2>
                    <p class="subtitle">Gestiona las clases y alumnos de este curso académico</p>
                </div>
                
                <div class="classes-grid">
                    <?php foreach ($clases as $clase): ?>
                        <div class="premium-card-wrapper">
                            <div class="card-options-container">
                                <button class="menu-dots-btn" onclick="toggleMenu(event, 'clase-<?php echo $clase['id']; ?>')">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="dropdown-clase-<?php echo $clase['id']; ?>" class="dropdown-options-menu">
                                    <a href="javascript:void(0)" onclick="editarClase(<?php echo $clase['id']; ?>)"><i class="fas fa-edit"></i> Modificar</a>
                                    <a href="javascript:void(0)" onclick="eliminarClase(<?php echo $clase['id']; ?>)" class="delete-option"><i class="fas fa-trash"></i> Eliminar</a>
                                </div>
                            </div>
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
                        </div>
                    <?php endforeach; ?>

                    <div class="add-card-dashed" onclick="abrirModalNuevaClase()">
                        <div class="add-icon"><i class="fas fa-plus"></i></div>
                        <h3>Añadir Nueva Clase</h3>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- MODAL AJUSTES DEL CURSO -->
    <div class="modal fade" id="modalAjustesCurso" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background: white; border-radius: 16px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                <div class="modal-header" style="border-bottom: none; padding: 25px 30px 0 30px; display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="modal-title fw-bold" style="font-family: 'Georgia', serif; font-size: 1.6rem; color: #1f2937; margin: 0;">Ajustes del Curso</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formAjustesCurso">
                    <input type="hidden" id="ajusteCursoId" value="<?php echo $curso_id; ?>">
                    
                    <div class="modal-body" style="padding: 20px 30px;">
                        <div class="row">
                            <div class="col-12 mb-4">
                                <label class="form-label fw-bold" style="color: #4b5563; font-size: 0.95rem; margin-bottom: 8px; display: block;">Centro Educativo</label>
                                <div class="d-flex align-items-center" style="display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 12px; padding: 6px 12px; background: #fff;">
                                    <div style="background-color: #f3f4f6; border-radius: 8px; min-width: 38px; height: 38px; display: flex; justify-content: center; align-items: center; margin-right: 12px;">
                                        <i class="fas fa-building" style="color: #6b7280; font-size: 1.1rem;"></i>
                                    </div>
                                    <input type="text" id="ajusteNombreCentro" class="form-control" value="<?php echo htmlspecialchars($curso['nombre_centro']); ?>" style="border: none !important; box-shadow: none !important; padding: 0 !important; background: transparent !important; width: 100%; font-size: 0.95rem; outline: none;" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6 mb-4">
                                <label class="form-label fw-bold" style="color: #4b5563; font-size: 0.95rem; margin-bottom: 8px; display: block;">Población</label>
                                <div class="d-flex align-items-center" style="display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 12px; padding: 6px 12px; background: #fff;">
                                    <div style="background-color: #f3f4f6; border-radius: 8px; min-width: 38px; height: 38px; display: flex; justify-content: center; align-items: center; margin-right: 12px;">
                                        <i class="fas fa-city" style="color: #6b7280; font-size: 1.1rem;"></i>
                                    </div>
                                    <input type="text" id="ajustePoblacion" class="form-control" value="<?php echo htmlspecialchars($curso['poblacion'] ?? ''); ?>" style="border: none !important; box-shadow: none !important; padding: 0 !important; background: transparent !important; width: 100%; font-size: 0.95rem; outline: none;" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 mb-4">
                                <label class="form-label fw-bold" style="color: #4b5563; font-size: 0.95rem; margin-bottom: 8px; display: block;">Provincia</label>
                                <div class="d-flex align-items-center" style="display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 12px; padding: 6px 12px; background: #fff;">
                                    <div style="background-color: #f3f4f6; border-radius: 8px; min-width: 38px; height: 38px; display: flex; justify-content: center; align-items: center; margin-right: 12px;">
                                        <i class="fas fa-map-marker-alt" style="color: #6b7280; font-size: 1.1rem;"></i>
                                    </div>
                                    <input type="text" id="ajusteProvincia" class="form-control" value="<?php echo htmlspecialchars($curso['provincia'] ?? ''); ?>" style="border: none !important; box-shadow: none !important; padding: 0 !important; background: transparent !important; width: 100%; font-size: 0.95rem; outline: none;" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6 mb-4">
                                <label class="form-label fw-bold" style="color: #4b5563; font-size: 0.95rem; margin-bottom: 8px; display: block;">Año Lectivo</label>
                                <div class="d-flex align-items-center" style="display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 12px; padding: 6px 12px; background: #fff;">
                                    <div style="background-color: #f3f4f6; border-radius: 8px; min-width: 38px; height: 38px; display: flex; justify-content: center; align-items: center; margin-right: 12px;">
                                        <i class="far fa-calendar-alt" style="color: #6b7280; font-size: 1.1rem;"></i>
                                    </div>
                                    <input type="text" id="ajusteAnio" class="form-control" value="<?php echo htmlspecialchars($curso['anio_academico']); ?>" style="border: none !important; box-shadow: none !important; padding: 0 !important; background: transparent !important; width: 100%; font-size: 0.95rem; outline: none;" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 mb-4">
                                <label class="form-label fw-bold" style="color: #4b5563; font-size: 0.95rem; margin-bottom: 12px; display: block;">Color Distintivo</label>
                                <div class="d-flex flex-wrap align-items-center gap-2" style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
                                    <div class="color-dot-ajuste" data-color="#ff7a59" style="background:#ff7a59; width: 32px; height: 32px; border-radius: 50%; cursor: pointer;"></div>
                                    <div class="color-dot-ajuste" data-color="#4a90e2" style="background:#4a90e2; width: 32px; height: 32px; border-radius: 50%; cursor: pointer;"></div>
                                    <div class="color-dot-ajuste" data-color="#47b39d" style="background:#47b39d; width: 32px; height: 32px; border-radius: 50%; cursor: pointer;"></div>
                                    <div class="color-dot-ajuste" data-color="#ffc107" style="background:#ffc107; width: 32px; height: 32px; border-radius: 50%; cursor: pointer;"></div>
                                    <div style="width: 1px; height: 24px; background: #d1d5db; margin: 0 8px;"></div>
                                    <input type="color" id="ajusteColor" value="<?php echo htmlspecialchars($curso['color'] ?? '#ff7a59'); ?>" style="width: 38px; height: 38px; padding: 0; border: none; background: transparent; cursor: pointer;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer" style="border-top: none; padding: 0 30px 25px; display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="background-color: #f3f4f6; color: #4b5563; border: none; border-radius: 10px; font-weight: 600; padding: 10px 20px; cursor: pointer;">Cancelar</button>
                        <button type="submit" class="btn btn-primary" style="background-color: #3b82f6; border: none; border-radius: 10px; color: white; font-weight: 600; padding: 10px 25px; cursor: pointer;">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL CLASE (EXISTENTE) -->
    <div id="modalClase" class="modal fade" tabindex="-1" aria-labelledby="modalClaseLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                <div class="modal-header" style="border-bottom: none; padding: 25px 30px 0 30px;">
                    <h3 class="modal-title fw-bold" id="modalClaseLabel">Añadir Nueva Clase</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formCrearClase">
                    <input type="hidden" id="cursoIdAsociado" value="<?php echo $curso_id; ?>">
                    <input type="hidden" id="inputIdClase">
                    <input type="hidden" id="inputColorClase" value="#3b82f6">
                    <input type="hidden" id="inputIconoClase" value="fa-users">

                    <div class="modal-body" style="padding: 20px 30px;">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-4" style="position: relative;">
                                <label class="form-label fw-bold" style="color: #4b5563; font-size: 0.9rem; display: block; margin-bottom: 8px;">Nombre del grupo</label>
                                <div class="d-flex align-items-center" style="border: 1px solid #d1d5db; border-radius: 12px; padding: 5px 10px; background: #fff;">
                                    <div style="background-color: #f3f4f6; border-radius: 8px; min-width: 38px; height: 38px; display: flex; justify-content: center; align-items: center; margin-right: 12px;">
                                        <i class="fas fa-users" style="color: #6b7280; font-size: 1.1rem;"></i>
                                    </div>
                                    <input type="text" id="inputNombreClase" class="form-control" placeholder="Ej: 1º ESO A" style="border: none !important; box-shadow: none !important; padding: 0 !important; background: transparent !important; width: 100%; font-size: 0.95rem; height: auto !important; margin: 0 !important;">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 mb-4" style="position: relative;">
                                <label class="form-label fw-bold" style="color: #4b5563; font-size: 0.9rem; display: block; margin-bottom: 8px;">Materia principal / Rol</label>
                                <div class="d-flex align-items-center" style="border: 1px solid #d1d5db; border-radius: 12px; padding: 5px 10px; background: #fff;">
                                    <div style="background-color: #f3f4f6; border-radius: 8px; min-width: 38px; height: 38px; display: flex; justify-content: center; align-items: center; margin-right: 12px;">
                                        <i class="fas fa-graduation-cap" style="color: #6b7280; font-size: 1.1rem;"></i>
                                    </div>
                                    <input type="text" id="inputMateria" class="form-control" placeholder="Ej: Matemáticas" style="border: none !important; box-shadow: none !important; padding: 0 !important; background: transparent !important; width: 100%; font-size: 0.95rem; height: auto !important; margin: 0 !important;">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 d-flex flex-column flex-md-row gap-4">
                                <div style="flex:1; min-width: 200px;">
                                    <label class="form-label fw-bold" style="color: #4b5563; font-size: 0.9rem; display: block; margin-bottom: 12px;">Color de la clase</label>
                                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; justify-items: center;" id="contenedor-colores">
                                        <div class="color-opcion active" data-color="#3b82f6" style="background-color: #3b82f6;"><i class="fas fa-check"></i></div>
                                        <div class="color-opcion" data-color="#ef4444" style="background-color: #ef4444;"><i class="fas fa-check"></i></div>
                                        <div class="color-opcion" data-color="#10b981" style="background-color: #10b981;"><i class="fas fa-check"></i></div>
                                        <div class="color-opcion" data-color="#f59e0b" style="background-color: #f59e0b;"><i class="fas fa-check"></i></div>
                                        <div class="color-opcion" data-color="#8b5cf6" style="background-color: #8b5cf6;"><i class="fas fa-check"></i></div>
                                        <div class="color-opcion" data-color="#ec4899" style="background-color: #ec4899;"><i class="fas fa-check"></i></div>
                                        <div class="color-opcion" data-color="#47b39d" style="background-color: #47b39d;"><i class="fas fa-check"></i></div>
                                        <input type="color" class="color-opcion color-input-visible" id="input-color-personalizado" value="#3b82f6" title="Color personalizado" style="background: none;">
                                    </div>
                                </div>
                                <div style="flex:1.5; min-width: 250px;">
                                    <label class="form-label fw-bold" style="color: #4b5563; font-size: 0.9rem; display: block; margin-bottom: 12px;">Icono representativo</label>
                                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; justify-items: center;" id="contenedor-iconos">
                                        <div class="icono-opcion active" data-icon="fa-users"><i class="fas fa-users"></i></div>
                                        <div class="icono-opcion" data-icon="fa-book"><i class="fas fa-book"></i></div>
                                        <div class="icono-opcion" data-icon="fa-flask"><i class="fas fa-flask"></i></div>
                                        <div class="icono-opcion" data-icon="fa-laptop-code"><i class="fas fa-laptop-code"></i></div>
                                        <div class="icono-opcion" data-icon="fa-globe"><i class="fas fa-globe"></i></div>
                                        <div class="icono-opcion" data-icon="fa-calculator"><i class="fas fa-calculator"></i></div>
                                        <div class="icono-opcion" data-icon="fa-palette"><i class="fas fa-palette"></i></div>
                                        <div class="icono-opcion" data-icon="fa-music"><i class="fas fa-music"></i></div>
                                        <div class="icono-opcion" data-icon="fa-basketball-ball"><i class="fas fa-basketball-ball"></i></div>
                                        <div class="icono-opcion" data-icon="fa-language"><i class="fas fa-language"></i></div>
                                        <div class="icono-opcion" data-icon="fa-atom"><i class="fas fa-atom"></i></div>
                                        <div class="icono-opcion" data-icon="fa-chart-line"><i class="fas fa-chart-line"></i></div>
                                        <div class="icono-opcion" data-icon="fa-history"><i class="fas fa-history"></i></div>
                                        <div class="icono-opcion" data-icon="fa-leaf"><i class="fas fa-leaf"></i></div>
                                        <div class="icono-opcion" data-icon="fa-heartbeat"><i class="fas fa-heartbeat"></i></div>
                                        <div class="icono-opcion" data-icon="fa-comments"><i class="fas fa-comments"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="modal-footer" style="border-top: none; padding: 0 30px 25px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 10px; font-weight: 600;">Cancelar</button>
                    <button type="submit" form="formCrearClase" class="btn btn-primary" style="border-radius: 10px; font-weight: 600; padding: 10px 25px;">Guardar Clase</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/mini-calendar.js?v=1.6"></script>
    <script src="../js/detalles_curso.js?v=1.9"></script>
    <input type="hidden" id="cursoIdAsociado" value="<?php echo $curso_id; ?>">

    <script>
        function toggleMenu(event, id) {
            event.preventDefault();
            event.stopPropagation();
            document.querySelectorAll('.dropdown-options-menu').forEach(m => {
                if (m.id !== `dropdown-${id}`) m.classList.remove('show');
            });
            const menu = document.getElementById(`dropdown-${id}`);
            if (menu) menu.classList.toggle('show');
        }
        document.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-options-menu').forEach(m => m.classList.remove('show'));
        });

        async function editarClase(id) {
            try {
                const res = await fetch(`controllers/get_clase_info.php?id=${id}`);
                const data = await res.json();
                if (data.status === 'success') {
                    const c = data.clase;
                    document.getElementById('inputIdClase').value = c.id;
                    document.getElementById('inputNombreClase').value = c.nombre_clase;
                    document.getElementById('inputMateria').value = c.materia_principal;
                    document.getElementById('inputColorClase').value = c.color_clase;
                    document.getElementById('inputIconoClase').value = c.icono_clase;
                    
                    // Actualizar UI del modal para modo edición
                    document.getElementById('modalClaseLabel').innerText = 'Modificar Clase';
                    
                    // Marcar color e icono activos
                    document.querySelectorAll('.color-opcion').forEach(opt => {
                        opt.classList.toggle('active', opt.dataset.color === c.color_clase);
                    });
                    document.querySelectorAll('.icono-opcion').forEach(opt => {
                        opt.classList.toggle('active', opt.dataset.icon === c.icono_clase);
                    });
                    
                    new bootstrap.Modal(document.getElementById('modalClase')).show();
                }
            } catch (e) { console.error(e); }
        }

        async function eliminarClase(id) {
            if (!confirm('¿Estás seguro de que quieres eliminar esta clase? Todos los alumnos y sus notas asociadas se perderán.')) return;
            try {
                const res = await fetch('controllers/eliminar_clase.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const data = await res.json();
                if (data.status === 'success') location.reload();
                else alert('Error: ' + data.message);
            } catch (e) { console.error(e); }
        }

        function openAjustesModal() {
            new bootstrap.Modal(document.getElementById('modalAjustesCurso')).show();
        }
        function abrirModalNuevaClase() {
            document.getElementById('formCrearClase').reset();
            document.getElementById('inputIdClase').value = '';
            document.getElementById('modalClaseLabel').innerText = 'Añadir Nueva Clase';
            new bootstrap.Modal(document.getElementById('modalClase')).show();
        }
    </script>
</body>
</html>