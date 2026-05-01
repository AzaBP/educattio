<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$claseId = isset($_GET['clase_id']) ? intval($_GET['clase_id']) : null;
if (!$claseId) {
    echo json_encode(['status' => 'error', 'message' => 'Falta el ID de la clase']);
    exit;
}

try {
    $stmt = $conexion->prepare('SELECT id, nombre_alumno FROM alumnos WHERE clase_id = :clase_id ORDER BY nombre_alumno');
    $stmt->execute([':clase_id' => $claseId]);
    $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'alumnos' => $alumnos]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
