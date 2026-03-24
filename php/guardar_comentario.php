<?php
session_start();
include 'conexion.php';
header('Content-Type: application/json');
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'mensaje' => 'No autorizado']);
    exit();
}
$datos = json_decode(file_get_contents("php://input"), true);
if (isset($datos['alumno_id']) && isset($datos['item_id']) && isset($datos['comentario'])) {
    $alumno_id = $datos['alumno_id'];
    $item_id = $datos['item_id'];
    $comentario = $datos['comentario'];
    try {
        $sql_check = "SELECT id FROM evaluaciones WHERE alumno_id = :alumno AND item_id = :item";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->execute([':alumno' => $alumno_id, ':item' => $item_id]);
        if ($stmt_check->rowCount() > 0) {
            $sql = "UPDATE evaluaciones SET comentarios = :comentario WHERE alumno_id = :alumno AND item_id = :item";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':comentario' => $comentario, ':alumno' => $alumno_id, ':item' => $item_id]);
            echo json_encode(['status' => 'success', 'accion' => 'actualizado']);
        } else {
            $sql = "INSERT INTO evaluaciones (alumno_id, item_id, comentarios) VALUES (:alumno, :item, :comentario)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':alumno' => $alumno_id, ':item' => $item_id, ':comentario' => $comentario]);
            echo json_encode(['status' => 'success', 'accion' => 'insertado']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'mensaje' => 'Error BD: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Datos incompletos']);
}
