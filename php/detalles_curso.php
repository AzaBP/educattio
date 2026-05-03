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
        .menu-dots {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .menu-dots:hover { background: rgba(255, 255, 255, 0.3); transform: scale(1.1); }
        
        .dropdown-menu-aislado {
            position: absolute;
            top: 40px;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            width: 160px;
            display: none;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
            z-index: 1000;
        }
        .dropdown-menu-aislado.show { display: flex; }
        .dropdown-menu-aislado a {
            padding: 12px 16px;
            font-size: 0.9rem;
            color: #374151;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s;
        }
        .dropdown-menu-aislado a:hover { background: #f9fafb; color: #3b82f6; }
        .dropdown-menu-aislado a.delete { color: #ef4444; }
        .dropdown-menu-aislado a.delete:hover { background: #fef2f2; }
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
                        <button class="settings-glass-btn" onclick="openAjustesModal()">
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
    <div id="modalAjustesCurso" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content premium-modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fw-bold">Ajustes del Curso</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="actualizar_curso.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $curso_id; ?>">
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Nombre del Centro</label>
                                <input type="text" name="nombre_centro" class="form-control" value="<?php echo htmlspecialchars($curso['nombre_centro']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Año Académico</label>
                                <input type="text" name="anio_academico" class="form-control" value="<?php echo htmlspecialchars($curso['anio_academico']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Provincia</label>
                                <input type="text" name="provincia" class="form-control" value="<?php echo htmlspecialchars($curso['provincia']); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
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
                const res = await fetch(`controllers/obtener_detalles_clase.php?id=${id}`);
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