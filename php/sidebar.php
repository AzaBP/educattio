<?php
// 1. Detectar en qué página estamos para poner la clase "active" automáticamente
$pagina_actual = basename($_SERVER['PHP_SELF']);

// 2. Determinar qué nombre mostrar debajo de la foto
$nombre_sidebar = $_SESSION['nombre_usuario'] ?? 'Usuario';
if (isset($datos_usuario['nombre_completo']) && trim($datos_usuario['nombre_completo']) !== '') {
    $nombre_sidebar = $datos_usuario['nombre_completo'];
} elseif (isset($datos_usuario['nombre_usuario'])) {
    $nombre_sidebar = $datos_usuario['nombre_usuario'];
}
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <h2>Educattio</h2>
    </div>

    <div class="sidebar-profile">
        <a href="perfil_usuario.php" class="profile-link <?php echo ($pagina_actual == 'perfil_usuario.php') ? 'active' : ''; ?>">
            <img src="../imagenes/icons8-profesor-100.png" alt="Perfil" class="sidebar-pic">
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
        
        <a href="ajustes.php" class="nav-item <?php echo ($pagina_actual == 'ajustes.php') ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i> <span>Ajustes</span>
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </div>
</aside>