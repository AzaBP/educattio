<?php
session_start();
include 'conexion.php';
header('Content-Type: application/json');
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'mensaje' => 'No autorizado']);
    exit();
}
$datos = json_decode(file_get_contents("php://input"), true);
if (isset($datos['alumno_id']) && isset($datos['item_id'])) {
    $alumno_id = $datos['alumno_id'];
    $item_id = $datos['item_id'];
    try {
        $sql = "SELECT comentarios FROM evaluaciones WHERE alumno_id = :alumno AND item_id = :item LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':alumno' => $alumno_id, ':item' => $item_id]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode(['status' => 'success', 'comentario' => $row['comentarios']]);
        } else {
            echo json_encode(['status' => 'success', 'comentario' => '']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'mensaje' => 'Error BD: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Datos incompletos']);
}
