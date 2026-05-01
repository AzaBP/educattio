<?php
require_once '../conexion.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $titulo = $data['titulo'] ?? '';
    $descripcion = $data['descripcion'] ?? '';
    $fecha = $data['fecha'] ?? '';
    $curso_id = $data['curso_id'] ?? '';
    
    if (empty($titulo) || empty($fecha) || empty($curso_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        exit;
    }
    
    // Insertar evento
    $stmt = $conexion->prepare("
        INSERT INTO eventos_curso (titulo, descripcion, fecha, curso_id) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$titulo, $descripcion, $fecha, $curso_id]);
    
    echo json_encode(['status' => 'success', 'id' => $conexion->lastInsertId()]);
    
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
