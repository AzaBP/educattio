<?php
header('Content-Type: application/json');
require_once '../conexion.php';
require_once '../dao/asignaturaDAO.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['nombre_asignatura'])) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

try {
    $dao = new AsignaturaDAO($conexion);
    $color = $data['color_asignatura'] ?? null;
    $icono = $data['icono_asignatura'] ?? null;
    if ($dao->actualizar($data['id'], $data['nombre_asignatura'], $color, $icono)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>