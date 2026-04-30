<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nombre_centro = trim($data['nombre_centro'] ?? '');
$poblacion = trim($data['poblacion'] ?? '');
$provincia = trim($data['provincia'] ?? '');
$anio = trim($data['anio'] ?? '');
$color = trim($data['color'] ?? '#4a90e2');

if (empty($nombre_centro) || empty($poblacion) || empty($provincia) || empty($anio)) {
    echo json_encode(['success' => false, 'error' => 'Faltan campos obligatorios']);
    exit();
}

try {
    $sql = "INSERT INTO cursos (nombre_centro, poblacion, provincia, anio_academico, color, usuario_id) 
            VALUES (:centro, :poblacion, :provincia, :anio, :color, :usuario_id)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':centro' => $nombre_centro,
        ':poblacion' => $poblacion,
        ':provincia' => $provincia,
        ':anio' => $anio,
        ':color' => $color,
        ':usuario_id' => $usuario_id
    ]);
    echo json_encode(['success' => true, 'id' => $conexion->lastInsertId()]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>