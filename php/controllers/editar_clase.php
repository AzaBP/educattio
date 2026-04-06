<?php
header('Content-Type: application/json');
require_once '../conexion.php';
require_once '../dao/claseDAO.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['id'], $input['nombre_clase'], $input['materia_principal'], $input['color_clase'], $input['icono_clase'])) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

$id = $input['id'];
$nombre = $input['nombre_clase'];
$materia = $input['materia_principal'];
$color = $input['color_clase'];
$icono = $input['icono_clase'];

$dao = new ClaseDAO($conexion);
$result = $dao->actualizarClase($id, $nombre, $materia, $color, $icono);

if ($result) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar la clase']);
}
?>
