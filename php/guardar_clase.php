<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit();
}

$curso_id = (int)($data['curso_id'] ?? 0);
$clase_id = isset($data['id']) && $data['id'] !== '' ? intval($data['id']) : null;
$nombre_clase = trim($data['nombre_clase'] ?? '');
$materia = trim($data['materia_principal'] ?? '');
$color = trim($data['color'] ?? '#3b82f6');
$icono = trim($data['icono'] ?? 'fa-users');

if (empty($nombre_clase) || $curso_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos obligatorios']);
    exit();
}

try {
    if ($clase_id) {
        $sql = "UPDATE clases SET nombre_clase = :nombre, materia_principal = :materia, 
                color_clase = :color, icono_clase = :icono WHERE id = :clase_id AND curso_id = :curso_id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre_clase,
            ':materia' => $materia,
            ':color' => $color,
            ':icono' => $icono,
            ':clase_id' => $clase_id,
            ':curso_id' => $curso_id
        ]);
        echo json_encode(['success' => true, 'id' => $clase_id]);
    } else {
        $sql = "INSERT INTO clases (nombre_clase, materia_principal, color_clase, icono_clase, curso_id) 
                VALUES (:nombre, :materia, :color, :icono, :curso_id)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre_clase,
            ':materia' => $materia,
            ':color' => $color,
            ':icono' => $icono,
            ':curso_id' => $curso_id
        ]);
        echo json_encode(['success' => true, 'id' => $conexion->lastInsertId()]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>