<?php
header('Content-Type: application/json');
require_once '../conexion.php';
require_once '../dao/asignaturaDAO.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado']);
    exit;
}

try {
    $dao = new AsignaturaDAO($conexion);
    if ($dao->eliminar($data['id'])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>