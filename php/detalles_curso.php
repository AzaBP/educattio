<?php
require_once __DIR__ . '/controllers/auth_check.php';
// Variables de usuario para el sidebar
$nombreUsuario = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Usuario';
$fotoUsuario = isset($_SESSION['foto']) ? $_SESSION['foto'] : '../imagenes/icons8-profesor-100.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - Detalles del Curso</title>
    
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
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
                                <strong>5 Clases</strong>
                                <span>Asignadas a tu perfil</span>
                            </div>
                        </li>
                        <li>
                            <div class="icon-box green"><i class="fas fa-users"></i></div>
                            <div>
                                <strong>124 Alumnos</strong>
                                <span>Total en tus clases</span>
                            </div>
                        </li>
                        <li>
                            <div class="icon-box red"><i class="fas fa-tasks"></i></div>
                            <div>
                                <strong>3 Evaluaciones</strong>
                                <span>Programadas este año</span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="course-calendar">
                    <div class="cal-header-small">
                        <span>Noviembre 2025</span>
                        <div class="cal-arrows">
                            <i class="fas fa-chevron-left"></i>
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                    <div class="cal-grid-small">
                        <div class="day-head">L</div><div class="day-head">M</div><div class="day-head">X</div><div class="day-head">J</div><div class="day-head">V</div><div class="day-head">S</div><div class="day-head">D</div>
                        <div class="empty"></div><div class="empty"></div><div>1</div><div>2</div><div>3</div><div class="weekend">4</div><div class="weekend">5</div>
                        <div>6</div><div>7</div><div class="today">8</div><div>9</div><div>10</div><div class="weekend">11</div><div class="weekend">12</div>
                        <div>13</div><div>14</div><div>15</div><div>16</div><div>17</div><div class="weekend">18</div><div class="weekend">19</div>
                    </div>
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
                            <div class="group-stats">
                                <span><i class="fas fa-user"></i> 24 Alumnos</span>
                            </div>
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
                            <div class="group-stats">
                                <span><i class="fas fa-user"></i> 18 Alumnos</span>
                            </div>
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
                            <div class="group-stats">
                                <span><i class="fas fa-user"></i> 5 Alumnos</span>
                            </div>
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
    <div id="modalClase" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="tituloModalClase">Añadir Nueva Clase</h3>
                <span class="close-btn" onclick="closeModalClase()">&times;</span>
            </div>
            <form id="formCrearClase">
                <input type="hidden" id="editClaseId" value="">
                <input type="hidden" id="cursoIdAsociado" value="<?php echo htmlspecialchars($_GET['id'] ?? ''); ?>">
                <div class="modal-body">
                    <input type="hidden" id="id_clase">
                    <div class="mb-3">
                        <label for="nombre_clase" class="form-label" style="font-weight: 600; font-size: 0.95rem; color: #333; margin-left: 5px;">Nombre del grupo</label>
                        <div class="d-flex align-items-center" style="border: 1px solid #d1d5db; border-radius: 50px; padding: 6px 15px; background: #fff;">
                            <div style="background-color: #f3f4f6; border-radius: 50%; min-width: 38px; height: 38px; display: flex; justify-content: center; align-items: center; margin-right: 12px;">
                                <i class="fas fa-users" style="color: #4b5563; font-size: 1rem;"></i>
                            </div>
                            <input type="text" id="nombre_clase" class="form-control" placeholder="Ej: 1º ESO A" style="border: none; box-shadow: none; padding: 0; background: transparent; width: 100%;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="materia_clase" class="form-label" style="font-weight: 600; font-size: 0.95rem; color: #333; margin-left: 5px;">Materia Principal / Rol</label>
                        <div class="d-flex align-items-center" style="border: 1px solid #d1d5db; border-radius: 50px; padding: 6px 15px; background: #fff;">
                            <div style="background-color: #f3f4f6; border-radius: 50%; min-width: 38px; height: 38px; display: flex; justify-content: center; align-items: center; margin-right: 12px;">
                                <i class="fas fa-book" style="color: #4b5563; font-size: 1rem;"></i>
                            </div>
                            <input type="text" id="materia_clase" class="form-control" placeholder="Ej: Matemáticas" style="border: none; box-shadow: none; padding: 0; background: transparent; width: 100%;">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="color_clase" class="form-label" style="font-weight: 600; font-size: 0.95rem; color: #333; margin-left: 5px;">Color de la tarjeta</label>
                        <div class="d-flex align-items-center" style="border: 1px solid #d1d5db; border-radius: 50px; padding: 6px 15px; background: #fff;">
                            <div style="background-color: #f3f4f6; border-radius: 50%; min-width: 38px; height: 38px; display: flex; justify-content: center; align-items: center; margin-right: 12px;">
                                <i class="fas fa-palette" style="color: #4b5563; font-size: 1rem;"></i>
                            </div>
                            <div style="flex-grow: 1; display: flex; align-items: center; justify-content: space-between;">
                                <span style="color: #6b7280; font-size: 0.95rem;">Selecciona un color</span>
                                <input type="color" id="color_clase" style="width: 32px; height: 32px; border: none; border-radius: 50%; cursor: pointer; padding: 0; background: transparent; overflow: hidden; -webkit-appearance: none;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: none; padding-bottom: 20px; justify-content: flex-end;">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: #f3f4f6; color: #4b5563; border-radius: 10px; font-weight: 600; padding: 10px 20px; border: none;">Cancelar</button>
                    <button type="button" class="btn" onclick="guardarClase()" style="background-color: #0d6efd; color: white; border-radius: 10px; font-weight: 600; padding: 10px 20px; border: none;">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    <div id="modalClase" class="modal">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: none; padding: 20px 20px 0;">
                <h5 class="modal-title fw-bold" id="modalClaseLabel" style="color: #1f2937;">Añadir Nueva Clase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" style="padding: 20px;">
                <input type="hidden" id="id_clase">
                <input type="hidden" id="color_clase" value="#3b82f6">
                <input type="hidden" id="icono_clase" value="fa-users">

                <div class="mb-4">
                    <label class="form-label fw-bold" style="color: #4b5563; font-size: 0.9rem;">Nombre del grupo</label>
                    <div class="d-flex align-items-center" style="border: 1px solid #d1d5db; border-radius: 12px; padding: 5px 10px; background: #fff;">
                        <div style="background-color: #f3f4f6; border-radius: 8px; min-width: 38px; height: 38px; display: flex; justify-content: center; align-items: center; margin-right: 12px;">
                            <i class="fas fa-users" style="color: #6b7280; font-size: 1.1rem;"></i>
                        </div>
                        <input type="text" id="nombre_clase" class="form-control" placeholder="Ej: 1º ESO A" style="border: none; box-shadow: none; padding: 0; background: transparent; width: 100%; font-size: 0.95rem;">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold" style="color: #4b5563; font-size: 0.9rem;">Materia principal / Rol</label>
                    <div class="d-flex align-items-center" style="border: 1px solid #d1d5db; border-radius: 12px; padding: 5px 10px; background: #fff;">
                        <div style="background-color: #f3f4f6; border-radius: 8px; min-width: 38px; height: 38px; display: flex; justify-content: center; align-items: center; margin-right: 12px;">
                            <i class="fas fa-book" style="color: #6b7280; font-size: 1.1rem;"></i>
                        </div>
                        <input type="text" id="materia_clase" class="form-control" placeholder="Ej: Matemáticas" style="border: none; box-shadow: none; padding: 0; background: transparent; width: 100%; font-size: 0.95rem;">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold" style="color: #4b5563; font-size: 0.9rem;">Icono</label>
                    <div class="d-flex gap-3 flex-wrap" id="contenedor-iconos">
                        <div class="icono-opcion seleccionable active" data-icon="fa-users" style="width: 45px; height: 45px; border-radius: 50%; background: #eff6ff; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid #3b82f6; color: #3b82f6; font-size: 1.2rem;"><i class="fas fa-users"></i></div>
                        <div class="icono-opcion seleccionable" data-icon="fa-book" style="width: 45px; height: 45px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid transparent; color: #6b7280; font-size: 1.2rem;"><i class="fas fa-book"></i></div>
                        <div class="icono-opcion seleccionable" data-icon="fa-flask" style="width: 45px; height: 45px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid transparent; color: #6b7280; font-size: 1.2rem;"><i class="fas fa-flask"></i></div>
                        <div class="icono-opcion seleccionable" data-icon="fa-laptop-code" style="width: 45px; height: 45px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid transparent; color: #6b7280; font-size: 1.2rem;"><i class="fas fa-laptop-code"></i></div>
                        <div class="icono-opcion seleccionable" data-icon="fa-globe" style="width: 45px; height: 45px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid transparent; color: #6b7280; font-size: 1.2rem;"><i class="fas fa-globe"></i></div>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold" style="color: #4b5563; font-size: 0.9rem;">Color</label>
                    <div class="d-flex gap-3 flex-wrap" id="contenedor-colores">
                        <div class="color-opcion seleccionable active" data-color="#3b82f6" style="width: 35px; height: 35px; border-radius: 50%; background-color: #3b82f6; cursor: pointer; border: 3px solid #fff; box-shadow: 0 0 0 2px #3b82f6;"></div>
                        <div class="color-opcion seleccionable" data-color="#ef4444" style="width: 35px; height: 35px; border-radius: 50%; background-color: #ef4444; cursor: pointer; border: 3px solid #fff; box-shadow: none;"></div>
                        <div class="color-opcion seleccionable" data-color="#10b981" style="width: 35px; height: 35px; border-radius: 50%; background-color: #10b981; cursor: pointer; border: 3px solid #fff; box-shadow: none;"></div>
                        <div class="color-opcion seleccionable" data-color="#f59e0b" style="width: 35px; height: 35px; border-radius: 50%; background-color: #f59e0b; cursor: pointer; border: 3px solid #fff; box-shadow: none;"></div>
                        <div class="color-opcion seleccionable" data-color="#8b5cf6" style="width: 35px; height: 35px; border-radius: 50%; background-color: #8b5cf6; cursor: pointer; border: 3px solid #fff; box-shadow: none;"></div>
                        <div class="color-opcion seleccionable" data-color="#ec4899" style="width: 35px; height: 35px; border-radius: 50%; background-color: #ec4899; cursor: pointer; border: 3px solid #fff; box-shadow: none;"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="border-top: none; padding: 0 20px 20px;">
                <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: #f3f4f6; color: #4b5563; border-radius: 8px; font-weight: 600; padding: 10px 20px; border: none;">Cancelar</button>
                <button type="button" class="btn" onclick="guardarClase()" style="background-color: #3b82f6; color: white; border-radius: 8px; font-weight: 600; padding: 10px 20px; border: none;">Guardar</button>
            </div>
        </div>
    </div>
    <script src="../js/detalles_curso.js"></script>
</body>
</html>
