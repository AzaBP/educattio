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
$curso_id = isset($data['id']) && $data['id'] !== '' ? intval($data['id']) : null;
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
    if ($curso_id) {
        $sql = "UPDATE cursos SET nombre_centro = :centro, poblacion = :poblacion, provincia = :provincia, 
                anio_academico = :anio, color = :color WHERE id = :curso_id AND usuario_id = :usuario_id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':centro' => $nombre_centro,
            ':poblacion' => $poblacion,
            ':provincia' => $provincia,
            ':anio' => $anio,
            ':color' => $color,
            ':curso_id' => $curso_id,
            ':usuario_id' => $usuario_id
        ]);
        echo json_encode(['success' => true, 'id' => $curso_id]);
    } else {
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
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>