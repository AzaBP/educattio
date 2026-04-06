<?php
header('Content-Type: application/json');
require_once '../conexion.php';
require_once '../dao/cursoDAO.php';

$data = json_decode(file_get_contents("php://input"), true);

try {
    $dao = new CursoDAO($conexion);
    if ($dao->eliminar($data['id'])) {
        echo json_encode(['status' => 'success']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}