<?php
// 1. INICIO DEL "CEREBRO" (PHP)
session_start();

require_once 'conexion.php';  


// Seguridad: Si no hay un nombre de usuario en la sesión, el sistema te expulsa al login
if (!isset($_SESSION['nombre_usuario'])) {
    header("Location: login.php");
    exit();
}

// Guardamos el nombre en una variable para usarlo abajo
$nombre_usuario = $_SESSION['nombre_usuario'];

// Guardamos el nombre en una variable para usarlo abajo
$usuario_id = $_SESSION['usuario_id'];

// Obtener datos completos del usuario para el sidebar
try {
    $sql = "SELECT nombre_completo, nombre_usuario, foto_perfil FROM usuarios WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $usuario_id]);
    $datos_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $datos_usuario = null;
}

// Calcular año académico actual y contar cursos activos (igual que antes)
$anio_actual = date('Y');
$mes_actual = date('n');
if ($mes_actual >= 9) {
    $anio_academico = $anio_actual . '-' . ($anio_actual + 1);
} else {
    $anio_academico = ($anio_actual - 1) . '-' . $anio_actual;
}
try {
    $sql = "SELECT COUNT(*) FROM cursos WHERE usuario_id = :usuario_id AND anio_academico = :anio_academico";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id, ':anio_academico' => $anio_academico]);
    $total_cursos_activos = $stmt->fetchColumn();
} catch (PDOException $e) {
    $total_cursos_activos = 0;
}

try {
    $stmt = $conexion->prepare("SELECT id, nombre_centro, poblacion, provincia, anio_academico, color FROM cursos WHERE usuario_id = :usuario_id AND anio_academico = :anio_academico ORDER BY id DESC LIMIT 6");
    $stmt->execute([':usuario_id' => $usuario_id, ':anio_academico' => $anio_academico]);
    $cursos_activos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $cursos_activos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - Portal Inicio</title>
    
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css?v=1.2">
    <link rel="stylesheet" href="../css/calendario.css?v=1.2">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* =========================================
           SISTEMA DE OPCIONES EN TARJETAS (TRES PUNTOS)
           ========================================= */
        .premium-card-wrapper {
            position: relative;
            width: 100%;
        }

        .card-options-container {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 100;
        }

        .menu-dots-btn {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .menu-dots-btn:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .dropdown-options-menu {
            position: absolute;
            top: 48px;
            right: 0;
            width: 190px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            border: 1px solid rgba(0,0,0,0.08);
            display: none;
            flex-direction: column;
            padding: 8px;
            z-index: 1000;
            animation: menuFadeIn 0.2s cubic-bezier(0, 0, 0.2, 1);
        }

        @keyframes menuFadeIn {
            from { opacity: 0; transform: translateY(-10px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .dropdown-options-menu.show {
            display: flex;
        }

        .dropdown-options-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #475569 !important;
            text-decoration: none !important;
            font-size: 0.95rem;
            font-weight: 500;
            border-radius: 12px;
            transition: all 0.2s;
        }

        .dropdown-options-menu a:hover {
            background: #f1f5f9;
            color: #2563eb !important;
        }

        .dropdown-options-menu a i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
            opacity: 0.7;
        }

        .dropdown-options-menu a.delete-option {
            color: #dc2626 !important;
            margin-top: 4px;
            border-top: 1px solid #f1f5f9;
            padding-top: 15px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }

        .dropdown-options-menu a.delete-option:hover {
            background: #fef2f2;
            color: #b91c1c !important;
        }
    </style>
</head>
<body>

<div class="dashboard-layout">
    
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        
        <header class="top-bar">
            <div class="welcome-text">
                <h1>Hola, <?php echo htmlspecialchars($nombre_usuario); ?></h1>
                <p>Bienvenido de nuevo. ¿Qué vamos a evaluar hoy?</p>
            </div>
            
            <div class="user-profile">
                <div class="notification-bell" id="notificationBell">
                    <i class="fas fa-bell"></i>
                    <span class="badge" id="notificationCount">0</span>
                    <div class="notification-dropdown" id="notificationDropdown">
                        <h4>Próximos eventos (próximos 7 días)</h4>
                        <ul id="notificationList">
                            <li>Cargando...</li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <section class="dashboard-overview">
            <div class="overview-card courses-card">
                <div class="overview-card-header">
                    <div>
                        <span class="small-label">Cursos activos</span>
                        <h2><?php echo $total_cursos_activos; ?></h2>
                    </div>
                </div>

                <div class="courses-list">
                    <?php if (empty($cursos_activos)): ?>
                        <p class="empty-state">No tienes cursos activos para este año académico.</p>
                    <?php else: ?>
                        <?php foreach ($cursos_activos as $curso): ?>
                            <a href="detalles_curso.php?id=<?php echo $curso['id']; ?>" class="small-course-card">
                                <span class="course-dot" style="background: <?php echo htmlspecialchars($curso['color'] ?: '#ff7a59'); ?>;"></span>
                                <div class="course-info">
                                    <strong><?php echo htmlspecialchars($curso['nombre_centro']); ?></strong>
                                    <span><?php echo htmlspecialchars($curso['poblacion'] . ', ' . $curso['provincia']); ?></span>
                                </div>
                                <span class="course-badge"><?php echo htmlspecialchars($curso['anio_academico']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                </div>
            </div>

            <div class="overview-card calendar-card" id="miniCalendarContainer" style="padding:0; border:none; background:transparent; box-shadow:none;">
            </div>

            <div class="overview-card today-card">
                <div class="today-label">Hoy</div>
                <div class="today-day-name" id="current-day-name">--</div>
                <div class="today-number" id="current-day-number">--</div>
                <div class="today-month-year" id="current-month-year">--</div>
                <div class="today-clock" id="real-time-clock">--:--:--</div>
            </div>
        </section>

        <section class="classes-section">
            <div class="section-header">
                <h2>Mis cursos</h2>
            </div>

            <div class="classes-grid">
                <?php if (empty($cursos_activos)): ?>
                    <div class="empty-state">Aún no has creado ningún curso. Pulsa "Añadir curso" para empezar.</div>
                <?php else: ?>
                    <?php foreach ($cursos_activos as $curso): ?>
                        <div class="premium-card-wrapper" style="position: relative;">
                            <div class="card-options-container">
                                <button class="menu-dots-btn" onclick="toggleMenu(event, 'curso-<?php echo $curso['id']; ?>')">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="dropdown-curso-<?php echo $curso['id']; ?>" class="dropdown-options-menu">
                                    <a href="javascript:void(0)" onclick="editarCurso(<?php echo $curso['id']; ?>)"><i class="fas fa-edit"></i> Modificar</a>
                                    <a href="javascript:void(0)" onclick="eliminarCurso(<?php echo $curso['id']; ?>)" class="delete-option"><i class="fas fa-trash"></i> Eliminar</a>
                                </div>
                            </div>
                            <a href="detalles_curso.php?id=<?php echo $curso['id']; ?>" class="premium-card" style="--accent-color: <?php echo $curso['color'] ?? '#4facfe'; ?>;">
                                <div class="card-banner">
                                    <div class="card-icon"><i class="fas fa-university"></i></div>
                                    <div class="card-badge"><?php echo htmlspecialchars($curso['anio_academico']); ?></div>
                                </div>
                                <div class="card-content">
                                    <h3><?php echo htmlspecialchars($curso['nombre_centro']); ?></h3>
                                    <p><?php echo htmlspecialchars($curso['poblacion'] . ', ' . $curso['provincia']); ?></p>
                                    <div class="card-footer">
                                        <span>Ver curso</span>
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="add-card-dashed" onclick="openModalCurso()">
                    <div class="add-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <h3>Añadir Nuevo Curso</h3>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- MODAL PARA CREAR CURSO -->
<div id="modalCurso" class="modal-overlay">
    <div class="modal-window premium-modal">
        <div class="modal-header">
            <div class="modal-title-group">
                <div class="modal-icon-header"><i class="fas fa-university"></i></div>
                <h3>Crear nuevo curso</h3>
            </div>
            <button class="close-btn" onclick="closeModalCurso()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formCrearCurso" class="modal-form">
            <input type="hidden" id="editCursoId" value="">
            
            <div class="form-group">
                <label for="inputNombreCentro"><i class="fas fa-building"></i> Centro Educativo</label>
                <input type="text" id="inputNombreCentro" placeholder="Ej. IES Cervantes" required>
            </div>

            <div class="form-row">
                <div class="form-group flex-1">
                    <label for="inputPoblacion"><i class="fas fa-city"></i> Población</label>
                    <input type="text" id="inputPoblacion" placeholder="Ej. Zaragoza" required>
                </div>
                <div class="form-group flex-1">
                    <label for="inputProvincia"><i class="fas fa-map-marker-alt"></i> Provincia</label>
                    <input type="text" id="inputProvincia" placeholder="Ej. Zaragoza" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group flex-1">
                    <label for="inputAnio"><i class="far fa-calendar-alt"></i> Año Lectivo</label>
                    <input type="text" id="inputAnio" value="2025-2026" required>
                </div>
                <div class="form-group flex-1">
                    <label><i class="fas fa-palette"></i> Color Distintivo</label>
                    <div class="color-palette-container">
                        <div class="color-presets" id="colorPresets">
                            <div class="color-dot" data-color="#ff7a59" onclick="selectPresetColor('#ff7a59', this)" style="background:#ff7a59;"></div>
                            <div class="color-dot" data-color="#4a90e2" onclick="selectPresetColor('#4a90e2', this)" style="background:#4a90e2;"></div>
                            <div class="color-dot" data-color="#47b39d" onclick="selectPresetColor('#47b39d', this)" style="background:#47b39d;"></div>
                            <div class="color-dot" data-color="#ffc107" onclick="selectPresetColor('#ffc107', this)" style="background:#ffc107;"></div>
                        </div>
                        <div class="color-divider"></div>
                        <input type="color" id="inputColor" value="#ff7a59" oninput="deselectPresets()">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModalCurso()">Cancelar</button>
                <button type="submit" class="btn-save" id="btnGuardarCurso">
                    <span>Guardar Curso</span>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../js/portal_inicio_usuario.js?v=1.3"></script>
<script src="../js/notificaciones.js?v=1.3"></script>
<script src="../js/calendar-sync.js?v=1.3"></script>
<script src="../js/mini-calendar.js?v=1.3"></script>

<script>
    // ========================
    // MANEJO DEL MODAL DE CURSO
    // ========================
    const modalCurso = document.getElementById('modalCurso');
    const formCurso = document.getElementById('formCrearCurso');

    window.openModalCurso = function() {
        if (modalCurso) modalCurso.style.display = 'flex';
        formCurso.reset();
        document.getElementById('editCursoId').value = '';
        document.getElementById('inputColor').value = '#ff7a59';
        document.querySelectorAll('.color-dot').forEach(dot => dot.style.border = 'none');
    };

    window.closeModalCurso = function() {
        if (modalCurso) modalCurso.style.display = 'none';
    };

    window.selectPresetColor = function(color, element) {
        document.getElementById('inputColor').value = color;
        document.querySelectorAll('.color-dot').forEach(dot => dot.style.border = 'none');
        if (element) element.style.border = '2px solid #333';
    };

    window.deselectPresets = function() {
        document.querySelectorAll('.color-dot').forEach(dot => dot.style.border = 'none');
    };

    formCurso.addEventListener('submit', function(e) {
        e.preventDefault();
        const payload = {
            nombre_centro: document.getElementById('inputNombreCentro').value.trim(),
            poblacion: document.getElementById('inputPoblacion').value.trim(),
            provincia: document.getElementById('inputProvincia').value.trim(),
            anio: document.getElementById('inputAnio').value.trim(),
            color: document.getElementById('inputColor').value
        };
        fetch('../php/guardar_curso.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeModalCurso();
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'No se pudo guardar el curso'));
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error de conexión con el servidor');
        });
    });

    window.onclick = function(event) {
        if (event.target === modalCurso) closeModalCurso();
    };

    // Reloj
    function updateCalendarAndClock() {
        const now = new Date();
        const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        document.getElementById('current-day-name').textContent = days[now.getDay()];
        document.getElementById('current-day-number').textContent = now.getDate();
        document.getElementById('current-month-year').textContent = `${months[now.getMonth()]} ${now.getFullYear()}`;
        document.getElementById('real-time-clock').textContent = now.toLocaleTimeString('es-ES');
    }
    setInterval(updateCalendarAndClock, 1000);
    updateCalendarAndClock();

    // Notificaciones de campana
    function cargarNotificaciones() {
        fetch('../php/obtener_notificaciones.php')
            .then(res => res.json())
            .then(data => {
                const count = data.length;
                document.getElementById('notificationCount').innerText = count;
                const list = document.getElementById('notificationList');
                list.innerHTML = '';
                if (count === 0) {
                    list.innerHTML = '<li>No hay eventos próximos</li>';
                } else {
                    data.forEach(ev => {
                        const fecha = new Date(ev.fecha);
                        const fechaStr = fecha.toLocaleDateString('es-ES');
                        const li = document.createElement('li');
                        li.innerHTML = `<strong>${escapeHtml(ev.titulo)}</strong> <small>${fechaStr}</small><br><span style="font-size:0.75rem;">${ev.tipo_evento}</span>`;
                        list.appendChild(li);
                    });
                }
            })
            .catch(err => {
                console.error('Error cargando notificaciones:', err);
                document.getElementById('notificationList').innerHTML = '<li>Error al cargar</li>';
            });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

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

    async function editarCurso(id) {
        try {
            const res = await fetch(`controllers/get_detalles_curso.php?id=${id}`);
            const data = await res.json();
            if (data.status === 'success') {
                const c = data.curso;
                document.getElementById('editCursoId').value = c.id;
                document.getElementById('inputNombreCentro').value = c.nombre_centro;
                document.getElementById('inputPoblacion').value = c.poblacion;
                document.getElementById('inputProvincia').value = c.provincia;
                document.getElementById('inputAnio').value = c.anio_academico;
                document.getElementById('inputColor').value = c.color;
                
                const modalH3 = document.querySelector('#modalCurso h3');
                if(modalH3) modalH3.innerText = 'Modificar curso';
                const btnSave = document.getElementById('btnGuardarCurso');
                if(btnSave) btnSave.innerText = 'Actualizar';
                
                openModalCurso();
            }
        } catch (e) { console.error(e); }
    }

    async function eliminarCurso(id) {
        if (!confirm('¿Estás seguro de que quieres eliminar este curso? Se borrarán todas las clases, alumnos y datos asociados de forma permanente.')) return;
        try {
            const res = await fetch('controllers/eliminar_curso.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const data = await res.json();
            if (data.status === 'success') location.reload();
            else alert('Error: ' + data.message);
        } catch (e) { console.error(e); }
    }

    function openModalCurso() {
        const m = document.getElementById('modalCurso');
        if(m) m.style.display = 'flex';
    }

    function closeModalCurso() {
        const m = document.getElementById('modalCurso');
        if(m) m.style.display = 'none';
        document.getElementById('formCrearCurso').reset();
        document.getElementById('editCursoId').value = '';
        const modalH3 = document.querySelector('#modalCurso h3');
        if(modalH3) modalH3.innerText = 'Crear nuevo curso';
        const btnSave = document.getElementById('btnGuardarCurso');
        if(btnSave) btnSave.innerText = 'Guardar';
    }

    cargarNotificaciones();
    setInterval(cargarNotificaciones, 60000);
</script>

</body>
</html>