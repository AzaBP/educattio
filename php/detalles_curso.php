<?php
require_once __DIR__ . '/controllers/auth_check.php';

$usuario_id = $_SESSION['usuario_id'];
$curso_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($curso_id <= 0) {
    header("Location: portal_cursos.php");
    exit();
}

try {
    require_once 'conexion.php';
    // Obtener datos del curso
    $sql_curso = "SELECT nombre_centro, anio_academico, poblacion, provincia, color FROM cursos WHERE id = :curso_id AND usuario_id = :usuario_id";
    $stmt = $conexion->prepare($sql_curso);
    $stmt->execute([':curso_id' => $curso_id, ':usuario_id' => $usuario_id]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$curso) {
        header("Location: portal_cursos.php");
        exit();
    }

    // Obtener estadísticas: número de clases, alumnos, evaluaciones (opcional)
    $sql_clases = "SELECT COUNT(*) FROM clases WHERE curso_id = :curso_id";
    $stmt_clases = $conexion->prepare($sql_clases);
    $stmt_clases->execute([':curso_id' => $curso_id]);
    $num_clases = $stmt_clases->fetchColumn();

    $sql_alumnos = "SELECT COUNT(DISTINCT a.id) FROM alumnos a JOIN clases c ON a.clase_id = c.id WHERE c.curso_id = :curso_id";
    $stmt_alumnos = $conexion->prepare($sql_alumnos);
    $stmt_alumnos->execute([':curso_id' => $curso_id]);
    $num_alumnos = $stmt_alumnos->fetchColumn();

    // Opcional: contar evaluaciones (notas) en las asignaturas de las clases del curso
    $sql_evaluaciones = "SELECT COUNT(*) FROM evaluaciones e 
                         JOIN asignaturas asig ON e.asignatura_id = asig.id 
                         JOIN clases c ON asig.clase_id = c.id 
                         WHERE c.curso_id = :curso_id";
    $stmt_eval = $conexion->prepare($sql_evaluaciones);
    $stmt_eval->execute([':curso_id' => $curso_id]);
    $num_evaluaciones = $stmt_eval->fetchColumn();

} catch (PDOException $e) {
    die("Error al cargar datos del curso: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Educattio - <?php echo htmlspecialchars($curso['nombre_centro']); ?></title>
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css">
    <link rel="stylesheet" href="../css/detalles_curso.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Ajustes adicionales para que el widget calendario encaje bien */
        .course-calendar {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            min-width: 280px;
        }
        .custom-calendar-widget {
            background: transparent;
            padding: 0;
            box-shadow: none;
        }
        .custom-calendar-widget .calendar-grid {
            gap: 6px;
        }
        .custom-calendar-widget .calendar-day {
            border-radius: 12px;
        }
    </style>
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
                <h1 style="background-color: <?php echo htmlspecialchars($curso['color']); ?>; display: inline-block; padding: 0.2rem 1rem; border-radius: 30px;">
                    <?php echo htmlspecialchars($curso['nombre_centro']); ?>
                </h1>
                <div class="course-badges">
                    <span class="badge year"><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($curso['anio_academico']); ?></span>
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
                            <strong><?php echo $num_clases; ?> Clases</strong>
                            <span>Asignadas a tu perfil</span>
                        </div>
                    </li>
                    <li>
                        <div class="icon-box green"><i class="fas fa-users"></i></div>
                        <div>
                            <strong><?php echo $num_alumnos; ?> Alumnos</strong>
                            <span>Total en tus clases</span>
                        </div>
                    </li>
                    <li>
                        <div class="icon-box red"><i class="fas fa-tasks"></i></div>
                        <div>
                            <strong><?php echo $num_evaluaciones; ?> Evaluaciones</strong>
                            <span>Programadas este año</span>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Widget de calendario dinámico para este curso -->
            <div class="course-calendar">
                <?php
                // Usamos el widget pero le pasamos el curso_id para filtrar eventos
                $widget_show_events = true;
                $widget_title = 'Eventos del curso';
                $widget_curso_id = $curso_id; // Variable especial para el widget
                include 'calendar_widget.php';
                ?>
            </div>
        </section>

        <hr class="section-divider">

        <section class="classes-grid-section">
            <div class="section-title-row">
                <h2>Mis Clases</h2>
                <p>Selecciona un grupo para ver sus alumnos y notas</p>
            </div>
            <div class="groups-grid" id="groupsGrid">
                <!-- Aquí se cargarán las clases dinámicamente con JS -->
                <div class="loading">Cargando clases...</div>
            </div>
        </section>
    </main>
</div>

<!-- Modal Añadir Clase (se mantiene igual, pero adapto el form) -->
<div id="modalClase" class="modal fade" tabindex="-1" aria-labelledby="modalClaseLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title fw-bold" id="modalClaseLabel">Añadir Nueva Clase</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCrearClase">
                <input type="hidden" id="cursoIdAsociado" value="<?php echo $curso_id; ?>">
                <input type="hidden" id="inputIdClase">
                <input type="hidden" id="inputColorClase" value="#3b82f6">
                <input type="hidden" id="inputIconoClase" value="fa-users">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombre del grupo</label>
                            <input type="text" id="inputNombreClase" class="form-control" placeholder="Ej: 1º ESO A" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Materia principal / Rol</label>
                            <input type="text" id="inputMateria" class="form-control" placeholder="Ej: Matemáticas">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Color</label>
                            <div class="d-flex gap-2 flex-wrap" id="contenedor-colores">
                                <div class="color-opcion active" data-color="#3b82f6" style="background:#3b82f6;"></div>
                                <div class="color-opcion" data-color="#ef4444" style="background:#ef4444;"></div>
                                <div class="color-opcion" data-color="#10b981" style="background:#10b981;"></div>
                                <div class="color-opcion" data-color="#f59e0b" style="background:#f59e0b;"></div>
                                <div class="color-opcion" data-color="#8b5cf6" style="background:#8b5cf6;"></div>
                                <div class="color-opcion" data-color="#ec4899" style="background:#ec4899;"></div>
                                <input type="color" id="input-color-personalizado" value="#000000" title="Personalizado">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Icono</label>
                            <div class="d-flex gap-2 flex-wrap" id="contenedor-iconos">
                                <div class="icono-opcion active" data-icon="fa-users"><i class="fas fa-users"></i></div>
                                <div class="icono-opcion" data-icon="fa-book"><i class="fas fa-book"></i></div>
                                <div class="icono-opcion" data-icon="fa-flask"><i class="fas fa-flask"></i></div>
                                <div class="icono-opcion" data-icon="fa-laptop-code"><i class="fas fa-laptop-code"></i></div>
                                <div class="icono-opcion" data-icon="fa-globe"><i class="fas fa-globe"></i></div>
                                <div class="icono-opcion" data-icon="fa-calculator"><i class="fas fa-calculator"></i></div>
                                <div class="icono-opcion" data-icon="fa-palette"><i class="fas fa-palette"></i></div>
                                <div class="icono-opcion" data-icon="fa-music"><i class="fas fa-music"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-save">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/detalles_curso.js"></script>
<script>
    // Cargar clases del curso desde un endpoint (debes crearlo: obtener_clases_curso.php)
    function cargarClases() {
        const grid = document.getElementById('groupsGrid');
        fetch(`obtener_clases_curso.php?curso_id=<?php echo $curso_id; ?>`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'error') {
                    grid.innerHTML = `<div class="error">Error: ${data.message}</div>`;
                    return;
                }
                const clases = data.data || [];
                if (clases.length === 0) {
                    grid.innerHTML = '<div class="empty-state">No hay clases aún. Crea la primera usando el botón "Añadir Nueva Clase".</div>';
                    return;
                }
                grid.innerHTML = '';
                clases.forEach(clase => {
                    const card = document.createElement('a');
                    card.href = `detalles_clase.php?id=${clase.id}`;
                    card.className = 'group-card';
                    card.style.textDecoration = 'none';
                    card.style.color = 'inherit';
                    card.innerHTML = `
                        <div class="group-header color-${clase.color_class}" style="background-color: ${clase.color};">
                            <span class="group-icon"><i class="fas ${clase.icono}"></i></span>
                            <div class="menu-dots"><i class="fas fa-ellipsis-v"></i></div>
                        </div>
                        <div class="group-body">
                            <h3>${escapeHtml(clase.nombre_clase)}</h3>
                            <p class="subtitle">${escapeHtml(clase.materia_principal || 'Clase')}</p>
                        </div>
                    `;
                    grid.appendChild(card);
                });
                // Añadir tarjeta "Añadir nueva clase"
                const addCard = document.createElement('div');
                addCard.className = 'add-card-dashed';
                addCard.setAttribute('onclick', 'openModalClase()');
                addCard.innerHTML = `
                    <div class="add-icon"><i class="fas fa-plus"></i></div>
                    <h3>Añadir Nueva Clase</h3>
                `;
                grid.appendChild(addCard);
            })
            .catch(err => {
                console.error('Error:', err);
                grid.innerHTML = '<div class="error">Error al cargar las clases</div>';
            });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;'})[m]);
    }

    document.addEventListener('DOMContentLoaded', cargarClases);
    // Asegurar que la función openModalClase esté disponible (si está en detalles_curso.js, no es necesario)
    window.openModalClase = function() {
        const modal = new bootstrap.Modal(document.getElementById('modalClase'));
        modal.show();
    };
</script>
</body>
</html>