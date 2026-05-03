<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID de alumno no proporcionado']);
    exit;
}

try {
    // La base de datos tiene ON DELETE CASCADE para las evaluaciones y matrículas vinculadas al alumno_id
    $stmt = $conexion->prepare("DELETE FROM alumnos WHERE id = ?");
    $stmt->execute([$data['id']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Alumno eliminado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'El alumno no existe o ya fue eliminado']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error de servidor: ' . $e->getMessage()]);
}
?>
