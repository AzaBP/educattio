<?php
include 'conexion.php';
$data = json_decode(file_get_contents("php://input"), true);
$sql = "UPDATE items_evaluacion SET titulo = :t, peso = :p WHERE id = :id";
$stmt = $conexion->prepare($sql);
$success = $stmt->execute([':t' => $data['titulo'], ':p' => $data['peso'], ':id' => $data['id']]);
echo json_encode(['status' => $success ? 'success' : 'error']);