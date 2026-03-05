<?php
// 1. INICIO DEL "CEREBRO" (PHP)
session_start(); 

// Seguridad: Si no hay un nombre de usuario en la sesión, el sistema te expulsa al login
if (!isset($_SESSION['nombre_usuario'])) {
    header("Location: inicio_sesion.html");
    exit();
}

// Guardamos el nombre en una variable para usarlo abajo
$nombre_usuario = $_SESSION['nombre_usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - Portal Inicio</title>
    
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

    <div class="dashboard-layout">
        
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Educattio</h2>
            </div>

            <div class="sidebar-profile">
                <a href="perfil_usuario.html" class="profile-link">
                    <img src="../imagenes/icons8-profesor-100.png" alt="Perfil" class="sidebar-pic">
                    <span class="edit-icon"><i class="fas fa-pen"></i></span>
                </a>
                <p class="sidebar-user-name">
                    <?php echo htmlspecialchars($nombre_usuario); ?>
                </p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="portal_inicio_usuario.php" class="nav-item active"><i class="fas fa-home"></i> Inicio</a>
                <a href="portal_cursos.html" class="nav-item"><i class="fas fa-chalkboard-teacher"></i> Mis Cursos</a>
                <a href="ajustes.php" class="nav-item"><i class="fas fa-cog"></i> Ajustes</a>
            </nav>

            <div class="sidebar-footer">
                <a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </div>
        </aside>

        <main class="main-content">
            
            <header class="top-bar">
                <div class="welcome-text">
                    <h1>Hola, <?php echo htmlspecialchars($nombre_usuario); ?></h1>
                    <p>Hoy es un buen día para evaluar.</p>
                </div>
                
                <div class="user-profile">
                    <div class="notification-bell">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                </div>
            </header>

            <section class="dashboard-overview">
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #e1f5fe; color: #039be5;">
                            <i class="fas fa-chalkboard"></i>
                        </div>
                        <div class="stat-info">
                            <h3>3</h3>
                            <p>Cursos Activos</p>
                        </div>
                    </div>
                    </div>

                <div class="calendar-widget">
                    <div class="calendar-header"><span id="current-day-name">--</span></div>
                    <div class="calendar-body">
                        <span id="current-day-number">--</span>
                        <span id="current-month-year">--</span>
                    </div>
                    <div class="calendar-footer">
                        <i class="far fa-clock"></i>
                        <span id="real-time-clock">--:--:--</span>
                    </div>
                </div>
            </section>

            <section class="classes-section">
                <div class="section-header">
                    <h2>Mis Cursos</h2>
                    <button class="btn-add-class" onclick="openModal()"><i class="fas fa-plus"></i> Nueva Clase</button>
                </div>

                <div class="classes-grid">
                    <article class="class-card">
                        <div class="card-body">
                            <h3>CURSO DEMO</h3>
                            <p>Bienvenido al sistema, <?php echo htmlspecialchars($nombre_usuario); ?>.</p>
                        </div>
                    </article>
                </div>
            </section>
        </main>
    </div>

    <script src="../js/portal_inicio_usuario.js"></script>
</body>
</html>