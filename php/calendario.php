<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener datos completos del usuario para el sidebar
$usuario_id = $_SESSION['usuario_id'];
try {
    include 'conexion.php';
    $sql = "SELECT nombre_completo, nombre_usuario, foto_perfil FROM usuarios WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $usuario_id]);
    $datos_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $datos_usuario = null;
}

$nombre_usuario = $_SESSION['nombre_usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calendario - Educattio</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css">
    <link rel="stylesheet" href="../css/calendario.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/locales/es.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="dashboard-layout">
    <?php include 'sidebar.php'; ?> <!-- tu sidebar existente -->
    <main class="main-content">
        <header class="page-header">
            <h1><i class="fas fa-calendar-alt"></i> Calendario General</h1>
            <p>Gestiona tus eventos: exámenes, festivos, excursiones, reuniones.</p>
        </header>
        
        <div class="calendar-container">
            <div id="calendar"></div>
        </div>
    </main>
</div>

<!-- Modal para crear/editar evento -->
<div id="eventModal" class="modal-overlay">
    <div class="modal-window" style="max-width: 550px;">
        <div class="modal-header">
            <h3 id="modalTitle">Nuevo Evento</h3>
            <button class="close-btn" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <form id="eventForm">
            <input type="hidden" id="eventId" value="">
            <div class="form-group">
                <label>Título</label>
                <input type="text" id="eventTitle" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Fecha y hora</label>
                <input type="datetime-local" id="eventDate" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Tipo</label>
                <select id="eventType" class="form-control" required>
                    <option value="Examen">Examen</option>
                    <option value="Festivo">Festivo</option>
                    <option value="Excursión">Excursión</option>
                    <option value="Reunión">Reunión</option>
                </select>
            </div>
            <div class="form-group">
                <label>Clase (opcional, si es específica de una clase)</label>
                <select id="eventClass" class="form-control">
                    <option value="">General (todas las clases)</option>
                    <!-- Aquí cargar las clases del usuario via AJAX -->
                </select>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea id="eventDesc" rows="3" class="form-control"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel btn-delete" id="deleteEventBtn" style="display:none;" onclick="deleteEvent(document.getElementById('eventId').value)">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
                <div style="flex:1;"></div>
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script src="../js/calendar-sync.js"></script>
<script src="../js/calendario.js"></script>
<script>
    // Cargar las clases del usuario para el select
    fetch('../php/obtener_clases.php')
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById('eventClass');
            data.forEach(clase => {
                const option = document.createElement('option');
                option.value = clase.id;
                option.textContent = clase.nombre_clase;
                select.appendChild(option);
            });
        });
</script>
</body>
</html>