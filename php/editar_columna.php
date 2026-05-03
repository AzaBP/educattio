<?php
include 'conexion.php';
$data = json_decode(file_get_contents("php://input"), true);
$sql = "UPDATE items_evaluacion SET titulo = :t, peso = :p, formula = :f WHERE id = :id";
$stmt = $conexion->prepare($sql);
$formula = isset($data['formula']) ? trim($data['formula']) : null;
$success = $stmt->execute([':t' => $data['titulo'], ':p' => $data['peso'], ':f' => $formula, ':id' => $data['id']]);
echo json_encode(['status' => $success ? 'success' : 'error']);