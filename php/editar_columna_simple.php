<?php
include 'conexion.php';
$data = json_decode(file_get_contents("php://input"), true);
if (isset($data['id']) && isset($data['formula'])) {
    $sql = "UPDATE items_evaluacion SET formula = :f WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $success = $stmt->execute([':f' => $data['formula'], ':id' => $data['id']]);
    echo json_encode(['status' => $success ? 'success' : 'error']);
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Datos incompletos']);
}
?>
