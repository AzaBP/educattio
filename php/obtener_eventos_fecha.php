<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit();
}

$userId = $_SESSION['usuario_id'];
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Validar formato de fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit();
}

try {
    $stmt = $conexion->prepare("SELECT id, titulo, fecha, tipo_evento FROM eventos
                                WHERE usuario_id = :uid AND DATE(fecha) = :fecha
                                ORDER BY fecha ASC");
    $stmt->execute([':uid' => $userId, ':fecha' => $fecha]);
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($eventos);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([]);
}
?>