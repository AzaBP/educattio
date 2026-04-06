<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado']);
    exit;
}

try {
    // Gracias a "ON DELETE CASCADE" en tu BD, al borrar la clase
    // se borrarán automáticamente sus asignaturas, alumnos y notas.
    $stmt = $conexion->prepare("DELETE FROM clases WHERE id = ?");
    if ($stmt->execute([$data['id']])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>