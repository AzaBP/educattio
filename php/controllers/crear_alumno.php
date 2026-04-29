<?php
require_once '../conexion.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['nombre_alumno'], $data['clase_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

try {
    // Recoge la foto, o si viene vacía le pone null
    $foto = !empty($data['foto']) ? $data['foto'] : null;
    $stmt = $conexion->prepare("INSERT INTO alumnos (nombre_alumno, observaciones, foto, clase_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        trim($data['nombre_alumno']),
        $data['observaciones'] ?? '',
        $foto,
        $data['clase_id']
    ]);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>