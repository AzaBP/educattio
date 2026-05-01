<?php
require_once '../conexion.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['id'], $data['nombre_alumno'])) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

try {
    $foto = !empty($data['foto']) ? $data['foto'] : null;
    $datosPersonales = [
        'telefono' => trim($data['telefono'] ?? ''),
        'contacto' => trim($data['contacto'] ?? ''),
        'alergias' => trim($data['alergias'] ?? ''),
        'enfermedades' => trim($data['enfermedades'] ?? '')
    ];
    $datosPersonalesJson = json_encode($datosPersonales, JSON_UNESCAPED_UNICODE);

    $stmt = $conexion->prepare("UPDATE alumnos SET nombre_alumno = ?, datos_personales = ?, observaciones = ?, foto = ? WHERE id = ?");
    $stmt->execute([
        trim($data['nombre_alumno']),
        $datosPersonalesJson,
        $data['observaciones'] ?? '',
        $foto,
        $data['id']
    ]);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
