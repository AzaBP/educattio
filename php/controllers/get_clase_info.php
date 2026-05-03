<?php
require_once '../conexion.php';
header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado']);
    exit;
}

try {
    $stmt = $conexion->prepare("SELECT id, nombre_clase, materia_principal, color_clase, icono_clase FROM clases WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $clase = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($clase) {
        echo json_encode(['status' => 'success', 'clase' => $clase]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Clase no encontrada']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
