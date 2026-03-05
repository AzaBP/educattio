<?php
session_start();
include '../php/conexion.php';

// 1. SEGURIDAD: Si no está logueado, lo echamos al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: inicio_sesion.html");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];

try {
    // 2. OBTENER DATOS DEL USUARIO (Para la barra lateral)
    $sql_user = "SELECT nombre_completo, nombre_usuario FROM usuarios WHERE id = :id";
    $stmt_user = $conexion->prepare($sql_user);
    $stmt_user->bindParam(':id', $id_usuario);
    $stmt_user->execute();
    $datos_usuario = $stmt_user->fetch(PDO::FETCH_ASSOC);

    // 3. OBTENER LOS CURSOS DE ESTE USUARIO
    $sql_cursos = "SELECT id, nombre_centro, anio_academico, poblacion, provincia FROM cursos WHERE usuario_id = :id ORDER BY id DESC";
    $stmt_cursos = $conexion->prepare($sql_cursos);
    $stmt_cursos->bindParam(':id', $id_usuario);
    $stmt_cursos->execute();
    
    // Guardamos todos los cursos encontrados en un array
    $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al cargar los datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - Mis Cursos</title>
    
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css"> 
    <link rel="stylesheet" href="../css/portal_cursos.css"> 
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

    <div class="dashboard-layout">
        
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="page-header-flex">
                <div>
                    <h1>Mis Cursos</h1>
                    <p>Gestiona tus centros y años académicos.</p>
                </div>
            </header>

            <div class="courses-grid">
                
                <div class="add-card-dashed" onclick="openModal()">
                    <div class="add-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <p>Añadir Nuevo</p>
                </div>

                <?php if (count($cursos) > 0): ?>
                    <?php foreach ($cursos as $curso): ?>
                        <article class="course-card" onclick="window.location.href='detalles_curso.html?id=<?php echo $curso['id']; ?>'">
                            
                            <div class="card-header-blue">
                                <div class="course-icon">
                                    <i class="fas fa-school"></i>
                                </div>
                                <div class="card-options" onclick="event.stopPropagation();">
                                    <button class="menu-btn" onclick="toggleMenu('menu-<?php echo $curso['id']; ?>')">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div id="menu-<?php echo $curso['id']; ?>" class="dropdown-menu">
                                        <a href="#"><i class="fas fa-pen"></i> Modificar</a>
                                        <a href="#" class="delete-option"><i class="fas fa-trash"></i> Eliminar</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <h3><?php echo htmlspecialchars($curso['nombre_centro']); ?></h3>
                                <p class="course-year"><?php echo htmlspecialchars($curso['anio_academico']); ?></p>
                                <p class="course-location" style="color: #666; font-size: 0.9rem; margin-top: 5px;">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($curso['poblacion'] . ', ' . $curso['provincia']); ?>
                                </p>
                                
                                <div class="card-footer" style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px;">
                                    <span class="tasks-pending" style="font-size: 0.85rem; color: #888;">Ver detalles</span>
                                    <i class="fas fa-chevron-right text-muted"></i>
                                </div>
                            </div>

                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <div id="modalNuevoDestino" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Añadir Nuevo Destino</h3>
                <button class="close-btn" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="../php/crear_curso.php" method="POST" class="modal-form">
                <div class="form-group">
                    <label>Centro Educativo</label>
                    <input type="text" name="nombre_centro" placeholder="Ej. IES Cervantes" required>
                </div>

                <div class="form-group">
                    <label>Año del Curso Lectivo</label>
                    <input type="text" name="anio_academico" placeholder="Ej. 2025 - 2026" value="2025-2026" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Población</label>
                        <input type="text" name="poblacion" placeholder="Ej. Alcalá de Henares" required>
                    </div>
                    <div class="form-group">
                        <label>Provincia</label>
                        <input type="text" name="provincia" placeholder="Ej. Madrid" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-save">Guardar</button>
                </div>
            </form>

        </div>
    </div>

    <script src="../js/portal_inicio_usuario.js"></script>
    <script>
        // Función para los submenús de las tarjetas
        function toggleMenu(menuId) {
            // Cerramos todos primero
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if(menu.id !== menuId) menu.classList.remove('show');
            });
            // Abrimos el actual
            document.getElementById(menuId).classList.toggle('show');
        }

        // Cierra los menús si haces clic fuera
        window.onclick = function(event) {
            if (!event.target.matches('.menu-btn') && !event.target.matches('.fa-ellipsis-v')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        }
    </script>
</body>
</html>