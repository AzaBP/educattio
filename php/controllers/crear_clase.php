<?php
header('Content-Type: application/json');
require_once '../conexion.php';
require_once '../dao/claseDAO.php';
require_once '../vo/claseVO.php';

// 1. Recoger datos (enviados como JSON desde JS)
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['nombre_clase']) || !isset($data['curso_id']) || !isset($data['materia_principal']) || !isset($data['color_clase']) || !isset($data['icono_clase'])) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

try {
    $claseDAO = new ClaseDAO($conexion);

    // 2. Crear el objeto VO con los datos recibidos
    $nuevaClase = new ClaseVO(
        null,
        $data['nombre_clase'],
        $data['materia_principal'],
        $data['curso_id'],
        $data['color_clase'],
        $data['icono_clase']
    );

    // 3. Insertar en la BD
    if ($claseDAO->insertar($nuevaClase)) {
        echo json_encode(['status' => 'success', 'message' => 'Clase creada correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al insertar en la base de datos']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>