<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['asignatura_id'], $data['titulo']) || empty(trim($data['titulo']))) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos para guardar el tema']);
    exit;
}

$asignaturaId = intval($data['asignatura_id']);
$titulo = trim($data['titulo']);
$descripcion = trim($data['descripcion'] ?? '');

try {
    $stmt = $conexion->prepare('INSERT INTO temas_asignatura (asignatura_id, titulo, descripcion) VALUES (:asignatura_id, :titulo, :descripcion)');
    $stmt->execute([
        ':asignatura_id' => $asignaturaId,
        ':titulo' => $titulo,
        ':descripcion' => $descripcion
    ]);

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
