<?php
// 1. SEGURIDAD Y DATOS: Si no tenemos los datos del usuario, los buscamos para el sidebar
if (!isset($datos_usuario) || !isset($datos_usuario['foto_perfil'])) {
    if (isset($_SESSION['usuario_id'])) {
        try {
            // Asegurarnos de tener conexión
            if (!isset($conexion)) {
                include_once 'conexion.php';
            }
            $stmt_sidebar = $conexion->prepare("SELECT nombre_completo, nombre_usuario, foto_perfil FROM usuarios WHERE id = ?");
            $stmt_sidebar->execute([$_SESSION['usuario_id']]);
            $datos_usuario_sidebar = $stmt_sidebar->fetch(PDO::FETCH_ASSOC);
            
            // Si los encontramos, los usamos para el sidebar
            if ($datos_usuario_sidebar) {
                $datos_usuario = array_merge($datos_usuario ?? [], $datos_usuario_sidebar);
            }
        } catch (PDOException $e) {
            // Fallback silencioso
        }
    }
}

// 2. Detectar en qué página estamos para poner la clase "active" automáticamente
$pagina_actual = basename($_SERVER['PHP_SELF']);

// 3. Determinar qué nombre mostrar debajo de la foto
$nombre_sidebar = $_SESSION['nombre_usuario'] ?? 'Usuario';
if (isset($datos_usuario['nombre_completo']) && trim($datos_usuario['nombre_completo']) !== '') {
    $nombre_sidebar = $datos_usuario['nombre_completo'];
} elseif (isset($datos_usuario['nombre_usuario'])) {
    $nombre_sidebar = $datos_usuario['nombre_usuario'];
}

// 4. Determinar la foto de perfil
$foto_sidebar = '../uploads/perfil/default-avatar.png'; // Imagen por defecto
if (!empty($datos_usuario['foto_perfil'])) {
    $foto_sidebar = '../' . ltrim($datos_usuario['foto_perfil'], '/');
}
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <h2>Educattio</h2>
    </div>

    <div class="sidebar-profile">
        <a href="perfil_usuario.php" class="profile-link <?php echo ($pagina_actual == 'perfil_usuario.php') ? 'active' : ''; ?>">
            <img src="<?php echo htmlspecialchars($foto_sidebar); ?>" alt="Perfil" class="sidebar-pic">
            <span class="edit-icon"><i class="fas fa-pen"></i></span>
        </a>
        <p class="sidebar-user-name"><?php echo htmlspecialchars($nombre_sidebar); ?></p>
    </div>
    
    <nav class="sidebar-nav">
        <a href="portal_inicio_usuario.php" class="nav-item <?php echo ($pagina_actual == 'portal_inicio_usuario.php') ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> <span>Inicio</span>
        </a>
        
        <a href="portal_cursos.php" class="nav-item <?php echo ($pagina_actual == 'portal_cursos.php') ? 'active' : ''; ?>">
            <i class="fas fa-book"></i> <span>Mis Cursos</span>
        </a>

        <a href="calendario.php" class="nav-item <?php echo ($pagina_actual == 'calendario.php') ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> <span>Calendario</span>
        </a>
        
        <a href="incidencias.php" class="nav-item <?php echo ($pagina_actual == 'incidencias.php') ? 'active' : ''; ?>">
            <i class="fas fa-file-alt"></i> <span>Incidencias</span>
        </a>
        
        <a href="ajustes.php" class="nav-item <?php echo ($pagina_actual == 'ajustes.php') ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i> <span>Ajustes</span>
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </div>
</aside>