<?php
include 'conexion.php';
$data = json_decode(file_get_contents("php://input"), true);
$sql = "DELETE FROM items_evaluacion WHERE id = :id";
$stmt = $conexion->prepare($sql);
$success = $stmt->execute([':id' => $data['id']]);
echo json_encode(['status' => $success ? 'success' : 'error']);