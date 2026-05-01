<?php
require_once 'conexion.php';

header('Content-Type: application/json');

try {
    $curso_id = $_GET['curso_id'] ?? null;
    
    if (!$curso_id) {
        echo json_encode(['status' => 'error', 'message' => 'ID de curso requerido']);
        exit;
    }
    
    // Obtener eventos del curso
    $stmt = $conexion->prepare("
        SELECT id, titulo, descripcion, fecha, DATE(fecha) as fecha_date
        FROM eventos_curso 
        WHERE curso_id = ? 
        ORDER BY fecha ASC
    ");
    $stmt->execute([$curso_id]);
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear para FullCalendar
    $events = [];
    foreach ($eventos as $evento) {
        $events[] = [
            'id' => $evento['id'],
            'title' => $evento['titulo'],
            'start' => $evento['fecha'],
            'description' => $evento['descripcion'],
            'allDay' => true
        ];
    }
    
    echo json_encode(['status' => 'success', 'events' => $events]);
    
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
