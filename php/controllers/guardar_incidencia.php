<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$curso_id = isset($data['curso_id']) ? intval($data['curso_id']) : null;
$clase_id = isset($data['clase_id']) ? intval($data['clase_id']) : null;
$alumno_id = isset($data['alumno_id']) && $data['alumno_id'] !== '' ? intval($data['alumno_id']) : null;
$tipo = trim($data['tipo'] ?? 'General');
$descripcion = trim($data['descripcion'] ?? '');

if (!$curso_id || !$clase_id || !$descripcion) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos para guardar la incidencia']);
    exit;
}

try {
    $stmt = $conexion->prepare('INSERT INTO incidencias (usuario_id, curso_id, clase_id, alumno_id, tipo, descripcion, fecha_incidencia, creado_en) VALUES (:usuario_id, :curso_id, :clase_id, :alumno_id, :tipo, :descripcion, NOW(), NOW())');
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':curso_id' => $curso_id,
        ':clase_id' => $clase_id,
        ':alumno_id' => $alumno_id,
        ':tipo' => $tipo,
        ':descripcion' => $descripcion
    ]);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
